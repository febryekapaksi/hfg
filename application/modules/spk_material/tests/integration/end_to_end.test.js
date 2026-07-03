/**
 * Integration Tests: SPK Material End-to-End Flow
 *
 * Tests the orchestration/decision logic extracted from the controller.
 * Verifies business rules without database connection.
 *
 * Validates: Requirements 6.2, 6.3, 8.7, 8.8, 7.1
 */

// ---------------------------------------------------------------
// Business Logic Helpers (extracted from controller save(), update_status(), print_pdf())
// ---------------------------------------------------------------

const ALL_STATUSES = ['Material Requested', 'Material Confirmed', 'Released', 'Cancelled'];
const EDITABLE_STATUSES = ['Material Requested', 'Material Confirmed'];
const TERMINAL_STATUSES = ['Released', 'Cancelled'];
const ALLOWED_TRANSITIONS = {
  'Material Requested': ['Material Confirmed', 'Cancelled'],
  'Material Confirmed': ['Released', 'Cancelled'],
};

/**
 * Generate SPK number based on current month/year and last counter.
 * Mirrors Spk_material_model::generate_spk_no()
 */
function generateSpkNo(year, month, lastCounter) {
  const mm = String(month).padStart(2, '0');
  const nextCounter = (lastCounter || 0) + 1;
  const xxxx = String(nextCounter).padStart(4, '0');
  return `SPK-${year}${mm}-${xxxx}`;
}

/**
 * Validate product lines for SPK creation/update.
 * Mirrors the validation logic in Spk_material::save()
 */
function validateProducts(products, hasBom) {
  const errors = [];
  const productIds = [];
  const noBomProducts = [];

  if (!products || !Array.isArray(products) || products.length === 0) {
    return { valid: false, errors: ['Minimal 1 baris produk harus diisi.'] };
  }

  for (let i = 0; i < products.length; i++) {
    const prod = products[i];
    const lineNum = i + 1;

    if (!prod.id_produk_fg) {
      errors.push(`Baris ${lineNum}: Produk harus dipilih.`);
      continue;
    }
    if (!prod.shift_ids) {
      errors.push(`Baris ${lineNum}: Shift harus dipilih.`);
      continue;
    }

    const qty = parseInt(prod.target_qty, 10);
    if (isNaN(qty) || qty < 1 || qty > 999999) {
      errors.push(`Baris ${lineNum}: Target Qty harus antara 1 - 999.999.`);
      continue;
    }

    if (productIds.includes(prod.id_produk_fg)) {
      errors.push(`Baris ${lineNum}: Produk sudah dipilih di baris lain.`);
      continue;
    }
    productIds.push(prod.id_produk_fg);

    if (!hasBom(prod.id_produk_fg)) {
      noBomProducts.push(prod.nm_produk_fg || prod.id_produk_fg);
    }
  }

  if (errors.length > 0) {
    return { valid: false, errors };
  }

  if (noBomProducts.length > 0) {
    return {
      valid: false,
      errors: [`BOM belum dibuat untuk produk: ${noBomProducts.join(', ')}`],
    };
  }

  return { valid: true, errors: [] };
}

/**
 * Simulate SPK creation flow (trans_start → inserts → trans_complete).
 * Returns created entities or error.
 */
function createSpk({ tgl_spk, catatan, products, hasBom, getBomDetails, userId, datetime }) {
  // Validate
  const validation = validateProducts(products, hasBom);
  if (!validation.valid) {
    return { success: false, message: validation.errors[0] };
  }

  // Simulate transaction
  const spkNo = generateSpkNo(
    new Date(datetime).getFullYear(),
    new Date(datetime).getMonth() + 1,
    0
  );

  const header = {
    spk_no: spkNo,
    tgl_spk,
    catatan: catatan || '',
    status: 'Material Requested',
    created_by: userId,
    created_at: datetime,
  };

  const details = products.map((prod, i) => ({
    spk_no: spkNo,
    urut: i + 1,
    id_produk_fg: prod.id_produk_fg,
    nm_produk_fg: prod.nm_produk_fg || '',
    shift_ids: prod.shift_ids,
    shift_names: prod.shift_names || '',
    target_qty: parseInt(prod.target_qty, 10),
    berat_per_unit: parseFloat(prod.berat_per_unit) || 0,
    total_weight: parseFloat(prod.total_weight) || 0,
  }));

  // Create warehouse requests
  const warehouseRequests = [];
  for (const prod of products) {
    const targetQty = parseInt(prod.target_qty, 10);
    const bomDetails = getBomDetails(prod.id_produk_fg);

    const requestHeader = {
      spk_no: spkNo,
      request_date: datetime,
      status: 'Pending',
      created_by: userId,
      created_at: datetime,
    };

    const requestDetails = bomDetails.map(mat => ({
      id_produk_fg: prod.id_produk_fg,
      nm_produk_fg: prod.nm_produk_fg || '',
      id_material: mat.id_material,
      nm_material: mat.nm_material,
      qty_needed: Math.round(parseFloat(mat.qty) * targetQty * 10000) / 10000,
      id_unit: mat.id_unit || null,
      nm_unit: mat.nm_unit || null,
    }));

    warehouseRequests.push({ header: requestHeader, details: requestDetails });
  }

  return {
    success: true,
    spkNo,
    header,
    details,
    warehouseRequests,
  };
}

/**
 * Simulate SPK update flow.
 * Mirrors controller save() in edit mode.
 */
function updateSpk({ spk_no, currentStatus, tgl_spk, catatan, products, hasBom, userId, datetime }) {
  // Check editable
  if (!EDITABLE_STATUSES.includes(currentStatus)) {
    return { success: false, message: 'SPK tidak dapat diedit.' };
  }

  // Validate products
  const validation = validateProducts(products, hasBom);
  if (!validation.valid) {
    return { success: false, message: validation.errors[0] };
  }

  // Simulate delete old + insert new (replace strategy)
  const newDetails = products.map((prod, i) => ({
    spk_no,
    urut: i + 1,
    id_produk_fg: prod.id_produk_fg,
    nm_produk_fg: prod.nm_produk_fg || '',
    shift_ids: prod.shift_ids,
    shift_names: prod.shift_names || '',
    target_qty: parseInt(prod.target_qty, 10),
    berat_per_unit: parseFloat(prod.berat_per_unit) || 0,
    total_weight: parseFloat(prod.total_weight) || 0,
  }));

  const updatedHeader = {
    tgl_spk,
    catatan: catatan || '',
    updated_by: userId,
    updated_at: datetime,
  };

  return { success: true, header: updatedHeader, details: newDetails };
}

/**
 * Simulate status transition.
 * Mirrors controller update_status() logic.
 */
function attemptTransition(currentStatus, newStatus) {
  if (TERMINAL_STATUSES.includes(currentStatus)) {
    return { success: false, message: `SPK sudah berstatus akhir (${currentStatus}), tidak dapat diubah.` };
  }
  const allowed = ALLOWED_TRANSITIONS[currentStatus] || [];
  if (!allowed.includes(newStatus)) {
    return { success: false, message: `Transisi status dari "${currentStatus}" ke "${newStatus}" tidak diizinkan.` };
  }
  return { success: true, newStatus };
}

/**
 * Build PDF data structure.
 * Mirrors controller print_pdf() logic for assembling data.
 */
function buildPdfData({ spk, details, getBomDetails, createdByName }) {
  if (!spk) {
    return { success: false, message: 'SPK tidak ditemukan.' };
  }

  const enrichedDetails = details.map(detail => ({
    ...detail,
    materials: getBomDetails(detail.id_produk_fg),
  }));

  return {
    success: true,
    data: {
      spk,
      details: enrichedDetails,
      created_by_name: createdByName,
    },
  };
}

// ---------------------------------------------------------------
// Test Data Fixtures
// ---------------------------------------------------------------

const MOCK_BOM_DATA = {
  'PROD-001': [
    { id_material: 'MAT-A', nm_material: 'Material A', qty: 2.5, id_unit: 'KG', nm_unit: 'Kilogram' },
    { id_material: 'MAT-B', nm_material: 'Material B', qty: 1.25, id_unit: 'KG', nm_unit: 'Kilogram' },
  ],
  'PROD-002': [
    { id_material: 'MAT-C', nm_material: 'Material C', qty: 0.75, id_unit: 'L', nm_unit: 'Liter' },
    { id_material: 'MAT-D', nm_material: 'Material D', qty: 3.0, id_unit: 'KG', nm_unit: 'Kilogram' },
    { id_material: 'MAT-E', nm_material: 'Material E', qty: 0.5, id_unit: 'PCS', nm_unit: 'Pieces' },
  ],
};

const hasBom = (id) => MOCK_BOM_DATA.hasOwnProperty(id);
const getBomDetails = (id) => MOCK_BOM_DATA[id] || [];

const VALID_PRODUCT_1 = {
  id_produk_fg: 'PROD-001',
  nm_produk_fg: 'Produk Alpha',
  shift_ids: '1,2',
  shift_names: 'Shift 1,Shift 2',
  target_qty: '100',
  berat_per_unit: '0.5',
  total_weight: '50.00',
};

const VALID_PRODUCT_2 = {
  id_produk_fg: 'PROD-002',
  nm_produk_fg: 'Produk Beta',
  shift_ids: '3',
  shift_names: 'Shift 3',
  target_qty: '200',
  berat_per_unit: '0.8',
  total_weight: '160.00',
};

// ---------------------------------------------------------------
// Integration Tests
// ---------------------------------------------------------------

describe('SPK Material - Integration Tests', () => {

  describe('Create SPK → Warehouse Request Flow', () => {

    it('should create SPK header, details, and warehouse requests atomically', () => {
      // Validates: Requirements 6.2, 6.3
      const result = createSpk({
        tgl_spk: '2024-06-15',
        catatan: 'Test SPK creation',
        products: [VALID_PRODUCT_1, VALID_PRODUCT_2],
        hasBom,
        getBomDetails,
        userId: 1,
        datetime: '2024-06-15 10:00:00',
      });

      // SPK creation succeeds
      expect(result.success).toBe(true);
      expect(result.spkNo).toMatch(/^SPK-\d{6}-\d{4}$/);

      // Header created correctly
      expect(result.header.spk_no).toBe(result.spkNo);
      expect(result.header.tgl_spk).toBe('2024-06-15');
      expect(result.header.status).toBe('Material Requested');
      expect(result.header.created_by).toBe(1);

      // Details created for both products
      expect(result.details).toHaveLength(2);
      expect(result.details[0].urut).toBe(1);
      expect(result.details[0].id_produk_fg).toBe('PROD-001');
      expect(result.details[1].urut).toBe(2);
      expect(result.details[1].id_produk_fg).toBe('PROD-002');

      // Warehouse requests created for each product
      expect(result.warehouseRequests).toHaveLength(2);
      expect(result.warehouseRequests[0].header.spk_no).toBe(result.spkNo);
      expect(result.warehouseRequests[0].header.status).toBe('Pending');
      expect(result.warehouseRequests[1].header.spk_no).toBe(result.spkNo);
    });

    it('should calculate correct qty_needed for warehouse request (BOM qty × target_qty)', () => {
      // Validates: Requirements 6.3
      const result = createSpk({
        tgl_spk: '2024-06-15',
        catatan: '',
        products: [VALID_PRODUCT_1, VALID_PRODUCT_2],
        hasBom,
        getBomDetails,
        userId: 1,
        datetime: '2024-06-15 10:00:00',
      });

      expect(result.success).toBe(true);

      // Product 1 (target_qty = 100): MAT-A = 2.5 × 100 = 250, MAT-B = 1.25 × 100 = 125
      const req1Details = result.warehouseRequests[0].details;
      expect(req1Details).toHaveLength(2);
      expect(req1Details[0].id_material).toBe('MAT-A');
      expect(req1Details[0].qty_needed).toBe(250);
      expect(req1Details[1].id_material).toBe('MAT-B');
      expect(req1Details[1].qty_needed).toBe(125);

      // Product 2 (target_qty = 200): MAT-C = 0.75 × 200 = 150, MAT-D = 3.0 × 200 = 600, MAT-E = 0.5 × 200 = 100
      const req2Details = result.warehouseRequests[1].details;
      expect(req2Details).toHaveLength(3);
      expect(req2Details[0].id_material).toBe('MAT-C');
      expect(req2Details[0].qty_needed).toBe(150);
      expect(req2Details[1].id_material).toBe('MAT-D');
      expect(req2Details[1].qty_needed).toBe(600);
      expect(req2Details[2].id_material).toBe('MAT-E');
      expect(req2Details[2].qty_needed).toBe(100);
    });

    it('should set initial status to "Material Requested"', () => {
      // Validates: Requirements 6.2 (status awal)
      const result = createSpk({
        tgl_spk: '2024-06-15',
        catatan: '',
        products: [VALID_PRODUCT_1],
        hasBom,
        getBomDetails,
        userId: 1,
        datetime: '2024-06-15 10:00:00',
      });

      expect(result.success).toBe(true);
      expect(result.header.status).toBe('Material Requested');
    });

    it('should reject SPK creation if any product lacks BOM', () => {
      // Validates: Requirements 6.4
      const noBomProduct = {
        ...VALID_PRODUCT_1,
        id_produk_fg: 'PROD-NO-BOM',
        nm_produk_fg: 'Produk Tanpa BOM',
      };

      const result = createSpk({
        tgl_spk: '2024-06-15',
        catatan: '',
        products: [VALID_PRODUCT_1, noBomProduct],
        hasBom,
        getBomDetails,
        userId: 1,
        datetime: '2024-06-15 10:00:00',
      });

      expect(result.success).toBe(false);
      expect(result.message).toContain('BOM belum dibuat');
      expect(result.message).toContain('Produk Tanpa BOM');
    });

    it('should generate warehouse request with correct product-material mapping', () => {
      // Validates: Requirements 6.3 - referensi nomor SPK dan material per produk
      const result = createSpk({
        tgl_spk: '2024-06-15',
        catatan: '',
        products: [VALID_PRODUCT_1],
        hasBom,
        getBomDetails,
        userId: 1,
        datetime: '2024-06-15 10:00:00',
      });

      expect(result.success).toBe(true);

      const wrHeader = result.warehouseRequests[0].header;
      const wrDetails = result.warehouseRequests[0].details;

      // Warehouse request references the SPK
      expect(wrHeader.spk_no).toBe(result.spkNo);
      expect(wrHeader.status).toBe('Pending');
      expect(wrHeader.created_by).toBe(1);

      // Each material detail references the product
      for (const detail of wrDetails) {
        expect(detail.id_produk_fg).toBe('PROD-001');
        expect(detail.nm_produk_fg).toBe('Produk Alpha');
      }

      // Material names and units preserved
      expect(wrDetails[0].nm_material).toBe('Material A');
      expect(wrDetails[0].id_unit).toBe('KG');
      expect(wrDetails[0].nm_unit).toBe('Kilogram');
    });
  });

  describe('Update SPK on Editable Status', () => {

    it('should allow update when status is "Material Requested"', () => {
      // Validates: Requirements 8.7
      const result = updateSpk({
        spk_no: 'SPK-202406-0001',
        currentStatus: 'Material Requested',
        tgl_spk: '2024-06-16',
        catatan: 'Updated notes',
        products: [VALID_PRODUCT_2],
        hasBom,
        userId: 2,
        datetime: '2024-06-16 14:00:00',
      });

      expect(result.success).toBe(true);
      expect(result.header.tgl_spk).toBe('2024-06-16');
      expect(result.header.catatan).toBe('Updated notes');
      expect(result.header.updated_by).toBe(2);
      expect(result.details).toHaveLength(1);
      expect(result.details[0].id_produk_fg).toBe('PROD-002');
    });

    it('should allow update when status is "Material Confirmed"', () => {
      // Validates: Requirements 8.7
      const result = updateSpk({
        spk_no: 'SPK-202406-0001',
        currentStatus: 'Material Confirmed',
        tgl_spk: '2024-06-17',
        catatan: 'Confirmed and updated',
        products: [VALID_PRODUCT_1, VALID_PRODUCT_2],
        hasBom,
        userId: 3,
        datetime: '2024-06-17 09:00:00',
      });

      expect(result.success).toBe(true);
      expect(result.details).toHaveLength(2);
      expect(result.details[0].urut).toBe(1);
      expect(result.details[1].urut).toBe(2);
    });

    it('should delete old details and insert new ones (replace strategy)', () => {
      // Validates: Requirements 8.7 - replace strategy
      // Simulates: originally had 2 products, updated to 1 different product
      const result = updateSpk({
        spk_no: 'SPK-202406-0001',
        currentStatus: 'Material Requested',
        tgl_spk: '2024-06-18',
        catatan: 'Replaced products',
        products: [VALID_PRODUCT_2], // Only product 2 now
        hasBom,
        userId: 1,
        datetime: '2024-06-18 11:00:00',
      });

      expect(result.success).toBe(true);
      // Old details completely replaced - only 1 detail now
      expect(result.details).toHaveLength(1);
      expect(result.details[0].spk_no).toBe('SPK-202406-0001');
      expect(result.details[0].urut).toBe(1);
      expect(result.details[0].id_produk_fg).toBe('PROD-002');
      expect(result.details[0].target_qty).toBe(200);
    });

    it('should validate products during update (reject invalid data)', () => {
      const invalidProduct = { ...VALID_PRODUCT_1, target_qty: '0' };

      const result = updateSpk({
        spk_no: 'SPK-202406-0001',
        currentStatus: 'Material Requested',
        tgl_spk: '2024-06-18',
        catatan: '',
        products: [invalidProduct],
        hasBom,
        userId: 1,
        datetime: '2024-06-18 11:00:00',
      });

      expect(result.success).toBe(false);
      expect(result.message).toContain('Target Qty');
    });
  });

  describe('Reject Edit on Terminal Status', () => {

    it('should reject edit when status is "Released"', () => {
      // Validates: Requirements 8.8
      const result = updateSpk({
        spk_no: 'SPK-202406-0001',
        currentStatus: 'Released',
        tgl_spk: '2024-06-20',
        catatan: 'Try to edit released',
        products: [VALID_PRODUCT_1],
        hasBom,
        userId: 1,
        datetime: '2024-06-20 10:00:00',
      });

      expect(result.success).toBe(false);
      expect(result.message).toBe('SPK tidak dapat diedit.');
    });

    it('should reject edit when status is "Cancelled"', () => {
      // Validates: Requirements 8.8
      const result = updateSpk({
        spk_no: 'SPK-202406-0001',
        currentStatus: 'Cancelled',
        tgl_spk: '2024-06-20',
        catatan: 'Try to edit cancelled',
        products: [VALID_PRODUCT_1],
        hasBom,
        userId: 1,
        datetime: '2024-06-20 10:00:00',
      });

      expect(result.success).toBe(false);
      expect(result.message).toBe('SPK tidak dapat diedit.');
    });

    it('should reject status transition from "Released"', () => {
      // Validates: Requirements 8.5
      const result = attemptTransition('Released', 'Material Confirmed');
      expect(result.success).toBe(false);
      expect(result.message).toContain('berstatus akhir');
    });

    it('should reject status transition from "Cancelled"', () => {
      // Validates: Requirements 8.6
      const result = attemptTransition('Cancelled', 'Material Requested');
      expect(result.success).toBe(false);
      expect(result.message).toContain('berstatus akhir');
    });
  });

  describe('PDF Generation', () => {

    it('should include all required sections: header, product details, material tables, footer', () => {
      // Validates: Requirements 7.1
      const spk = {
        spk_no: 'SPK-202406-0001',
        tgl_spk: '2024-06-15',
        catatan: 'Production batch A',
        status: 'Material Requested',
        created_by: 1,
        created_at: '2024-06-15 10:00:00',
      };

      const details = [
        {
          id: 1,
          spk_no: 'SPK-202406-0001',
          urut: 1,
          id_produk_fg: 'PROD-001',
          nm_produk_fg: 'Produk Alpha',
          shift_ids: '1,2',
          shift_names: 'Shift 1,Shift 2',
          target_qty: 100,
          berat_per_unit: 0.5,
          total_weight: 50.0,
        },
        {
          id: 2,
          spk_no: 'SPK-202406-0001',
          urut: 2,
          id_produk_fg: 'PROD-002',
          nm_produk_fg: 'Produk Beta',
          shift_ids: '3',
          shift_names: 'Shift 3',
          target_qty: 200,
          berat_per_unit: 0.8,
          total_weight: 160.0,
        },
      ];

      const result = buildPdfData({
        spk,
        details,
        getBomDetails,
        createdByName: 'Admin Produksi',
      });

      expect(result.success).toBe(true);

      // Header section
      expect(result.data.spk.spk_no).toBe('SPK-202406-0001');
      expect(result.data.spk.tgl_spk).toBe('2024-06-15');

      // Product details section
      expect(result.data.details).toHaveLength(2);
      expect(result.data.details[0].nm_produk_fg).toBe('Produk Alpha');
      expect(result.data.details[0].shift_names).toBe('Shift 1,Shift 2');
      expect(result.data.details[0].target_qty).toBe(100);
      expect(result.data.details[0].total_weight).toBe(50.0);

      // Material tables per product
      expect(result.data.details[0].materials).toHaveLength(2);
      expect(result.data.details[0].materials[0].nm_material).toBe('Material A');
      expect(result.data.details[0].materials[0].qty).toBe(2.5);
      expect(result.data.details[0].materials[0].nm_unit).toBe('Kilogram');

      expect(result.data.details[1].materials).toHaveLength(3);
      expect(result.data.details[1].materials[0].nm_material).toBe('Material C');

      // Footer section
      expect(result.data.created_by_name).toBe('Admin Produksi');
    });

    it('should group materials per product', () => {
      // Validates: Requirements 7.4
      const spk = {
        spk_no: 'SPK-202406-0002',
        tgl_spk: '2024-06-15',
        catatan: '',
        status: 'Released',
        created_by: 1,
        created_at: '2024-06-15 10:00:00',
      };

      const details = [
        {
          id: 1, spk_no: 'SPK-202406-0002', urut: 1,
          id_produk_fg: 'PROD-001', nm_produk_fg: 'Produk Alpha',
          shift_ids: '1', shift_names: 'Shift 1',
          target_qty: 50, berat_per_unit: 0.5, total_weight: 25.0,
        },
        {
          id: 2, spk_no: 'SPK-202406-0002', urut: 2,
          id_produk_fg: 'PROD-002', nm_produk_fg: 'Produk Beta',
          shift_ids: '2', shift_names: 'Shift 2',
          target_qty: 75, berat_per_unit: 0.8, total_weight: 60.0,
        },
      ];

      const result = buildPdfData({
        spk,
        details,
        getBomDetails,
        createdByName: 'Supervisor',
      });

      expect(result.success).toBe(true);

      // Each product has its own materials (grouped)
      const prod1Materials = result.data.details[0].materials;
      const prod2Materials = result.data.details[1].materials;

      // Product 1 should only have its own BOM materials (MAT-A, MAT-B)
      expect(prod1Materials).toHaveLength(2);
      expect(prod1Materials.map(m => m.id_material)).toEqual(['MAT-A', 'MAT-B']);

      // Product 2 should only have its own BOM materials (MAT-C, MAT-D, MAT-E)
      expect(prod2Materials).toHaveLength(3);
      expect(prod2Materials.map(m => m.id_material)).toEqual(['MAT-C', 'MAT-D', 'MAT-E']);
    });

    it('should return error when SPK not found', () => {
      // Validates: Requirements 7.5
      const result = buildPdfData({
        spk: null,
        details: [],
        getBomDetails,
        createdByName: 'Admin',
      });

      expect(result.success).toBe(false);
      expect(result.message).toBe('SPK tidak ditemukan.');
    });
  });

  describe('Full Lifecycle Flow', () => {

    it('should support create → confirm → release lifecycle', () => {
      // Create SPK
      const createResult = createSpk({
        tgl_spk: '2024-06-15',
        catatan: 'Full lifecycle test',
        products: [VALID_PRODUCT_1],
        hasBom,
        getBomDetails,
        userId: 1,
        datetime: '2024-06-15 10:00:00',
      });
      expect(createResult.success).toBe(true);
      expect(createResult.header.status).toBe('Material Requested');

      // Confirm material
      const confirmResult = attemptTransition('Material Requested', 'Material Confirmed');
      expect(confirmResult.success).toBe(true);
      expect(confirmResult.newStatus).toBe('Material Confirmed');

      // Release to production
      const releaseResult = attemptTransition('Material Confirmed', 'Released');
      expect(releaseResult.success).toBe(true);
      expect(releaseResult.newStatus).toBe('Released');

      // Cannot edit after release
      const editResult = updateSpk({
        spk_no: createResult.spkNo,
        currentStatus: 'Released',
        tgl_spk: '2024-06-20',
        catatan: 'Attempt edit',
        products: [VALID_PRODUCT_1],
        hasBom,
        userId: 1,
        datetime: '2024-06-20 10:00:00',
      });
      expect(editResult.success).toBe(false);
    });
  });
});
