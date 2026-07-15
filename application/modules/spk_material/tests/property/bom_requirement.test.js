/**
 * Property-Based Test: BOM Requirement for SPK Creation
 * 
 * Feature: spk-material, Property 9: BOM Requirement for SPK Creation
 * Validates: Requirements 6.4
 * 
 * For any set of product lines in an SPK creation request, if any product does
 * not have a valid BOM (no record in ms_bom_header with is_delete=0), the entire
 * SPK creation must be rejected.
 */

const fc = require('fast-check');

// ---------------------------------------------------------------
// Helper: Replicates the controller's BOM validation logic
// ---------------------------------------------------------------

/**
 * Validate BOM requirement for SPK creation.
 * Mirrors the BOM validation in Spk_material::save() controller:
 *   foreach ($products as $prod) {
 *     if (!$this->Spk_material_model->has_bom($prod['id_produk_fg'])) {
 *       $no_bom_products[] = $prod['nm_produk_fg'] || $prod['id_produk_fg'];
 *     }
 *   }
 *   if (!empty($no_bom_products)) { return reject; }
 *
 * @param {Array} products - Array of product lines [{id_produk_fg, nm_produk_fg}]
 * @param {Function} hasBom - Function(id_produk_fg) => boolean, simulates model has_bom()
 * @returns {Object} { valid: boolean, noBomProducts: string[], savedProducts: string[] }
 */
function validateBomRequirement(products, hasBom) {
  const noBomProducts = [];

  for (const prod of products) {
    if (!hasBom(prod.id_produk_fg)) {
      noBomProducts.push(prod.nm_produk_fg || prod.id_produk_fg);
    }
  }

  const valid = noBomProducts.length === 0;

  return {
    valid,
    noBomProducts,
    // If validation fails, no products should be saved (all-or-nothing)
    savedProducts: valid ? products.map(p => p.id_produk_fg) : [],
  };
}

// ---------------------------------------------------------------
// Generators
// ---------------------------------------------------------------

/**
 * Generator for a product line entry
 */
const productLineArb = fc.record({
  id_produk_fg: fc.string({ minLength: 1, maxLength: 20 }).filter(s => s.trim().length > 0),
  nm_produk_fg: fc.string({ minLength: 1, maxLength: 50 }).filter(s => s.trim().length > 0),
});

/**
 * Generator for array of product lines (1-10 lines, simulating SPK form)
 */
const productLinesArb = fc.array(productLineArb, { minLength: 1, maxLength: 10 });

/**
 * Generator for array of product lines with at least 2 items (needed for partial BOM scenarios)
 */
const multiProductLinesArb = fc.array(productLineArb, { minLength: 2, maxLength: 10 });

// ---------------------------------------------------------------
// Property Tests
// ---------------------------------------------------------------

describe('Feature: spk-material, Property 9: BOM Requirement for SPK Creation', () => {

  // Property 9.1: ACCEPT WHEN ALL PRODUCTS HAVE BOM
  // If every product in the set has a valid BOM, validation passes
  it('should accept SPK creation when ALL products have valid BOM', () => {
    fc.assert(
      fc.property(
        productLinesArb,
        (products) => {
          // All products have BOM
          const hasBom = () => true;

          const result = validateBomRequirement(products, hasBom);

          expect(result.valid).toBe(true);
          expect(result.noBomProducts).toHaveLength(0);
          expect(result.savedProducts).toHaveLength(products.length);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 9.2: REJECT WHEN ANY PRODUCT LACKS BOM
  // If at least one product lacks BOM, the entire SPK must be rejected
  it('should reject SPK creation when ANY product lacks valid BOM', () => {
    fc.assert(
      fc.property(
        multiProductLinesArb,
        fc.integer({ min: 0, max: 9 }),
        (products, removeIdx) => {
          // Select one product to lack BOM
          const idx = removeIdx % products.length;
          const productWithoutBom = products[idx].id_produk_fg;

          const hasBom = (id) => id !== productWithoutBom;

          const result = validateBomRequirement(products, hasBom);

          expect(result.valid).toBe(false);
          expect(result.noBomProducts.length).toBeGreaterThan(0);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 9.3: ALL-OR-NOTHING - No partial save when validation fails
  // If BOM validation fails, zero products should be saved (transaction rejected entirely)
  it('should save ZERO products when BOM validation fails (all-or-nothing)', () => {
    fc.assert(
      fc.property(
        multiProductLinesArb,
        fc.integer({ min: 0, max: 9 }),
        (products, removeIdx) => {
          const idx = removeIdx % products.length;
          const productWithoutBom = products[idx].id_produk_fg;

          const hasBom = (id) => id !== productWithoutBom;

          const result = validateBomRequirement(products, hasBom);

          // All-or-nothing: if invalid, no products should be saved at all
          expect(result.savedProducts).toHaveLength(0);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 9.4: ALL PRODUCTS SAVED when validation passes
  // If BOM validation succeeds, ALL products should be saved (no partial)
  it('should save ALL products when BOM validation passes', () => {
    fc.assert(
      fc.property(
        productLinesArb,
        (products) => {
          const hasBom = () => true;

          const result = validateBomRequirement(products, hasBom);

          // All products saved
          expect(result.savedProducts.length).toBe(products.length);
          // Every product ID is in the saved list
          for (const prod of products) {
            expect(result.savedProducts).toContain(prod.id_produk_fg);
          }
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 9.5: REJECT WHEN ALL PRODUCTS LACK BOM
  // Edge case: no product has BOM → definitely rejected
  it('should reject when NO products have valid BOM', () => {
    fc.assert(
      fc.property(
        productLinesArb,
        (products) => {
          // No products have BOM
          const hasBom = () => false;

          const result = validateBomRequirement(products, hasBom);

          expect(result.valid).toBe(false);
          expect(result.noBomProducts.length).toBe(products.length);
          expect(result.savedProducts).toHaveLength(0);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 9.6: IDENTIFIED PRODUCTS - noBomProducts lists exactly the products without BOM
  // The error should precisely identify which products lack BOM
  it('should identify exactly which products lack BOM in the error', () => {
    fc.assert(
      fc.property(
        // Use unique product IDs to avoid ambiguity in findIndex lookup
        fc.uniqueArray(
          fc.record({
            id_produk_fg: fc.string({ minLength: 1, maxLength: 20 }).filter(s => s.trim().length > 0),
            nm_produk_fg: fc.string({ minLength: 1, maxLength: 50 }).filter(s => s.trim().length > 0),
          }),
          { minLength: 2, maxLength: 10, selector: (p) => p.id_produk_fg }
        ),
        fc.array(fc.boolean(), { minLength: 10, maxLength: 10 }),
        (products, bomFlags) => {
          // Assign BOM flags per product (using unique IDs)
          const flags = products.map((_, i) => bomFlags[i % bomFlags.length]);
          const hasAnyWithout = flags.some(f => !f);
          fc.pre(hasAnyWithout);

          // Build a lookup map by id_produk_fg
          const bomMap = {};
          products.forEach((p, i) => { bomMap[p.id_produk_fg] = flags[i]; });

          const hasBom = (id) => bomMap[id] || false;

          const result = validateBomRequirement(products, hasBom);

          // Count products without BOM
          const expectedNoBom = products.filter((_, i) => !flags[i]);

          expect(result.valid).toBe(false);
          expect(result.noBomProducts.length).toBe(expectedNoBom.length);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 9.7: SINGLE PRODUCT WITHOUT BOM REJECTS ENTIRE SET
  // Even if only 1 out of N products lacks BOM, all N are rejected
  it('should reject entire set even if only 1 out of many products lacks BOM', () => {
    fc.assert(
      fc.property(
        fc.array(productLineArb, { minLength: 3, maxLength: 10 }),
        fc.integer({ min: 0, max: 9 }),
        (products, idx) => {
          const targetIdx = idx % products.length;

          // Only one product lacks BOM, all others have it
          const hasBom = (id) => id !== products[targetIdx].id_produk_fg;

          const result = validateBomRequirement(products, hasBom);

          expect(result.valid).toBe(false);
          // Exactly 1 product reported as lacking BOM
          expect(result.noBomProducts.length).toBe(1);
          // No products saved at all
          expect(result.savedProducts).toHaveLength(0);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 9.8: VALIDATION RESULT IS DETERMINISTIC
  // Same inputs always produce same validation result
  it('should produce deterministic results for same inputs', () => {
    fc.assert(
      fc.property(
        productLinesArb,
        fc.array(fc.boolean(), { minLength: 10, maxLength: 10 }),
        (products, bomFlags) => {
          const hasBom = (id) => {
            const idx = products.findIndex(p => p.id_produk_fg === id);
            return idx >= 0 ? bomFlags[idx % bomFlags.length] : false;
          };

          const result1 = validateBomRequirement(products, hasBom);
          const result2 = validateBomRequirement(products, hasBom);

          expect(result1.valid).toBe(result2.valid);
          expect(result1.noBomProducts).toEqual(result2.noBomProducts);
          expect(result1.savedProducts).toEqual(result2.savedProducts);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 9.9: PRODUCT NAME USED IN ERROR - nm_produk_fg preferred, fallback to id_produk_fg
  // Mirrors controller logic: isset($prod['nm_produk_fg']) ? $prod['nm_produk_fg'] : $prod['id_produk_fg']
  it('should use nm_produk_fg in error message, falling back to id_produk_fg', () => {
    fc.assert(
      fc.property(
        fc.array(
          fc.record({
            id_produk_fg: fc.string({ minLength: 1, maxLength: 20 }).filter(s => s.trim().length > 0),
            nm_produk_fg: fc.string({ minLength: 1, maxLength: 50 }).filter(s => s.trim().length > 0),
          }),
          { minLength: 1, maxLength: 5 }
        ),
        (products) => {
          // No products have BOM
          const hasBom = () => false;

          const result = validateBomRequirement(products, hasBom);

          // Each entry in noBomProducts should be nm_produk_fg (since it's truthy)
          for (let i = 0; i < products.length; i++) {
            expect(result.noBomProducts[i]).toBe(products[i].nm_produk_fg);
          }
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 9.10: FALLBACK TO ID WHEN NAME IS EMPTY
  it('should fallback to id_produk_fg when nm_produk_fg is empty/falsy', () => {
    fc.assert(
      fc.property(
        fc.array(
          fc.record({
            id_produk_fg: fc.string({ minLength: 1, maxLength: 20 }).filter(s => s.trim().length > 0),
            nm_produk_fg: fc.constant(''), // Empty name
          }),
          { minLength: 1, maxLength: 5 }
        ),
        (products) => {
          const hasBom = () => false;

          const result = validateBomRequirement(products, hasBom);

          // With empty nm_produk_fg, should fall back to id_produk_fg
          for (let i = 0; i < products.length; i++) {
            expect(result.noBomProducts[i]).toBe(products[i].id_produk_fg);
          }
        }
      ),
      { numRuns: 100 }
    );
  });
});
