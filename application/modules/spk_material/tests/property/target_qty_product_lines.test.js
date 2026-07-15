/**
 * Property-Based Tests: Target Qty Validation, Product Line Uniqueness, Renumbering
 * 
 * Feature: spk-material
 * Property 1: Target Qty Validation
 * Property 5: Product Line Uniqueness
 * Property 10: Product Line Renumbering Invariant
 * 
 * Validates: Requirements 2.2, 2.3, 5.6, 5.3
 */

const fc = require('fast-check');

// ---------------------------------------------------------------
// Validation Functions (mirror client-side and server-side logic)
// ---------------------------------------------------------------

/**
 * Target Qty validation.
 * Accepts only positive integers in the range [1, 999999].
 * Rejects non-integer values, decimals, zero, negative numbers, and out-of-range values.
 * 
 * @param {*} value - The input value to validate
 * @returns {boolean} true if valid, false otherwise
 */
function isValidTargetQty(value) {
  if (typeof value !== 'number') return false;
  if (!Number.isInteger(value)) return false;
  if (!isFinite(value)) return false;
  return value >= 1 && value <= 999999;
}

/**
 * Product line uniqueness validation.
 * Checks that no two product lines reference the same id_produk_fg.
 * 
 * @param {Array<{id_produk_fg: string}>} productLines - Array of product lines
 * @returns {boolean} true if all products are unique, false if duplicates exist
 */
function hasNoDuplicateProducts(productLines) {
  const ids = productLines.map(l => l.id_produk_fg);
  return new Set(ids).size === ids.length;
}

/**
 * Renumber product lines after add/remove operations.
 * Assigns contiguous sequence starting from 1 to all remaining lines.
 * 
 * @param {Array<Object>} lines - Array of product line objects
 * @returns {Array<Object>} New array with `urut` field set to 1-based index
 */
function renumberLines(lines) {
  return lines.map((line, index) => ({ ...line, urut: index + 1 }));
}

// ---------------------------------------------------------------
// Generators
// ---------------------------------------------------------------

/** Generate valid target qty values: integers in [1, 999999] */
const validTargetQtyArb = fc.integer({ min: 1, max: 999999 });

/** Generate invalid target qty: zero */
const zeroArb = fc.constant(0);

/** Generate invalid target qty: negative integers */
const negativeIntArb = fc.integer({ min: -1000000, max: -1 });

/** Generate invalid target qty: values above the max */
const aboveMaxArb = fc.integer({ min: 1000000, max: 10000000 });

/** Generate invalid target qty: decimal/float numbers */
const decimalArb = fc.double({ min: 0.01, max: 999999, noNaN: true, noDefaultInfinity: true })
  .filter(v => !Number.isInteger(v));

/** Generate non-numeric values */
const nonNumericArb = fc.oneof(
  fc.string(),
  fc.boolean(),
  fc.constant(null),
  fc.constant(undefined),
  fc.constant(NaN),
  fc.constant(Infinity),
  fc.constant(-Infinity)
);

/** Generate a product ID (simulates id_produk_fg) */
const productIdArb = fc.stringMatching(/^[A-Z0-9]{3,10}$/);

/** Generate a product line with a given id */
function productLineArb(idArb) {
  return fc.record({
    id_produk_fg: idArb || productIdArb,
    nm_produk_fg: fc.string({ minLength: 1, maxLength: 50 }),
    target_qty: validTargetQtyArb,
    urut: fc.integer({ min: 1, max: 100 })
  });
}

/** Generate a list of product lines with unique IDs */
const uniqueProductLinesArb = fc.uniqueArray(productIdArb, { minLength: 1, maxLength: 20 })
  .chain(ids => fc.tuple(
    ...ids.map(id => fc.record({
      id_produk_fg: fc.constant(id),
      nm_produk_fg: fc.string({ minLength: 1, maxLength: 50 }),
      target_qty: validTargetQtyArb,
      urut: fc.integer({ min: 1, max: 100 })
    }))
  ));

/** Generate a list of product lines with at least one duplicate ID */
const duplicateProductLinesArb = fc.tuple(
  productIdArb,
  fc.integer({ min: 2, max: 10 })
).chain(([duplicateId, count]) => {
  // Create at least 2 lines with the same id_produk_fg
  const duplicateLines = fc.array(
    fc.record({
      id_produk_fg: fc.constant(duplicateId),
      nm_produk_fg: fc.string({ minLength: 1, maxLength: 50 }),
      target_qty: validTargetQtyArb,
      urut: fc.integer({ min: 1, max: 100 })
    }),
    { minLength: 2, maxLength: Math.min(count, 5) }
  );
  // Optionally add some unique lines
  const extraLines = fc.array(
    fc.record({
      id_produk_fg: productIdArb.filter(id => id !== duplicateId),
      nm_produk_fg: fc.string({ minLength: 1, maxLength: 50 }),
      target_qty: validTargetQtyArb,
      urut: fc.integer({ min: 1, max: 100 })
    }),
    { minLength: 0, maxLength: 5 }
  );
  return fc.tuple(duplicateLines, extraLines).map(([dupes, extras]) => [...dupes, ...extras]);
});

// ---------------------------------------------------------------
// Property 1: Target Qty Validation
// Validates: Requirements 2.2, 2.3
// ---------------------------------------------------------------

describe('Feature: spk-material, Property 1: Target Qty Validation', () => {

  // **Validates: Requirements 2.2**
  it('should accept any positive integer in the range [1, 999999]', () => {
    fc.assert(
      fc.property(
        validTargetQtyArb,
        (value) => {
          expect(isValidTargetQty(value)).toBe(true);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 2.3**
  it('should reject zero', () => {
    fc.assert(
      fc.property(
        zeroArb,
        (value) => {
          expect(isValidTargetQty(value)).toBe(false);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 2.3**
  it('should reject negative numbers', () => {
    fc.assert(
      fc.property(
        negativeIntArb,
        (value) => {
          expect(isValidTargetQty(value)).toBe(false);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 2.3**
  it('should reject values above 999999', () => {
    fc.assert(
      fc.property(
        aboveMaxArb,
        (value) => {
          expect(isValidTargetQty(value)).toBe(false);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 2.3**
  it('should reject decimal/float values', () => {
    fc.assert(
      fc.property(
        decimalArb,
        (value) => {
          expect(isValidTargetQty(value)).toBe(false);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 2.3**
  it('should reject non-numeric values (strings, booleans, null, undefined, NaN, Infinity)', () => {
    fc.assert(
      fc.property(
        nonNumericArb,
        (value) => {
          expect(isValidTargetQty(value)).toBe(false);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 2.2, 2.3**
  it('should accept value if and only if it is a positive integer in [1, 999999]', () => {
    // Comprehensive: test arbitrary numbers (both valid and invalid)
    fc.assert(
      fc.property(
        fc.oneof(
          validTargetQtyArb,
          zeroArb,
          negativeIntArb,
          aboveMaxArb,
          fc.integer({ min: -10000000, max: 10000000 })
        ),
        (value) => {
          const expected = Number.isInteger(value) && value >= 1 && value <= 999999;
          expect(isValidTargetQty(value)).toBe(expected);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 2.2**
  it('should accept boundary values: 1 and 999999', () => {
    expect(isValidTargetQty(1)).toBe(true);
    expect(isValidTargetQty(999999)).toBe(true);
  });

  // **Validates: Requirements 2.3**
  it('should reject boundary-adjacent invalid values: 0 and 1000000', () => {
    expect(isValidTargetQty(0)).toBe(false);
    expect(isValidTargetQty(1000000)).toBe(false);
  });
});

// ---------------------------------------------------------------
// Property 5: Product Line Uniqueness
// Validates: Requirements 5.6
// ---------------------------------------------------------------

describe('Feature: spk-material, Property 5: Product Line Uniqueness', () => {

  // **Validates: Requirements 5.6**
  it('should accept submissions where all product lines have unique id_produk_fg', () => {
    fc.assert(
      fc.property(
        uniqueProductLinesArb,
        (lines) => {
          expect(hasNoDuplicateProducts(lines)).toBe(true);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 5.6**
  it('should reject submissions where any two lines reference the same id_produk_fg', () => {
    fc.assert(
      fc.property(
        duplicateProductLinesArb,
        (lines) => {
          expect(hasNoDuplicateProducts(lines)).toBe(false);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 5.6**
  it('should detect duplicates regardless of position in the array', () => {
    fc.assert(
      fc.property(
        productIdArb,
        fc.integer({ min: 0, max: 8 }),
        fc.integer({ min: 1, max: 9 }),
        (duplicateId, insertPos1, insertPos2) => {
          // Create a list with exactly 2 lines sharing the same id
          const lines = [];
          for (let i = 0; i < 10; i++) {
            lines.push({
              id_produk_fg: `PROD${i}`,
              nm_produk_fg: `Product ${i}`,
              target_qty: 100,
              urut: i + 1
            });
          }
          // Insert duplicate at two positions
          const pos1 = Math.min(insertPos1, 9);
          const pos2 = Math.min(insertPos2, 9);
          lines[pos1].id_produk_fg = duplicateId;
          lines[pos2].id_produk_fg = duplicateId;

          // If positions are same, it's not actually a duplicate (same element)
          if (pos1 === pos2) {
            // Only one element has that ID — check uniqueness among all
            const ids = lines.map(l => l.id_produk_fg);
            const hasDups = new Set(ids).size !== ids.length;
            expect(hasNoDuplicateProducts(lines)).toBe(!hasDups);
          } else {
            // Two different positions share the same id — must reject
            expect(hasNoDuplicateProducts(lines)).toBe(false);
          }
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 5.6**
  it('should accept a single product line (no possibility of duplicates)', () => {
    fc.assert(
      fc.property(
        productIdArb,
        (id) => {
          const lines = [{ id_produk_fg: id, nm_produk_fg: 'Test', target_qty: 1, urut: 1 }];
          expect(hasNoDuplicateProducts(lines)).toBe(true);
        }
      ),
      { numRuns: 100 }
    );
  });
});

// ---------------------------------------------------------------
// Property 10: Product Line Renumbering Invariant
// Validates: Requirements 5.3
// ---------------------------------------------------------------

describe('Feature: spk-material, Property 10: Product Line Renumbering Invariant', () => {

  // **Validates: Requirements 5.3**
  it('should produce contiguous sequence [1..N] after renumbering any array of lines', () => {
    fc.assert(
      fc.property(
        fc.array(
          fc.record({
            id_produk_fg: productIdArb,
            nm_produk_fg: fc.string({ minLength: 1, maxLength: 50 }),
            target_qty: validTargetQtyArb,
            urut: fc.integer({ min: 1, max: 1000 }) // arbitrary initial urut
          }),
          { minLength: 1, maxLength: 100 }
        ),
        (lines) => {
          const renumbered = renumberLines(lines);

          // Length must be preserved
          expect(renumbered.length).toBe(lines.length);

          // urut must form [1, 2, 3, ..., N]
          for (let i = 0; i < renumbered.length; i++) {
            expect(renumbered[i].urut).toBe(i + 1);
          }
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 5.3**
  it('should maintain contiguous sequence after simulated add operations', () => {
    fc.assert(
      fc.property(
        // Start with some lines, then add N more
        fc.integer({ min: 1, max: 20 }),
        fc.integer({ min: 1, max: 20 }),
        (initialCount, addCount) => {
          // Build initial lines
          let lines = [];
          for (let i = 0; i < initialCount; i++) {
            lines.push({ id_produk_fg: `PROD${i}`, urut: i + 1 });
          }

          // Simulate adding more lines (with arbitrary urut values)
          for (let i = 0; i < addCount; i++) {
            lines.push({ id_produk_fg: `NEW${i}`, urut: 999 });
          }

          // Renumber
          const renumbered = renumberLines(lines);
          const N = initialCount + addCount;

          expect(renumbered.length).toBe(N);
          for (let i = 0; i < N; i++) {
            expect(renumbered[i].urut).toBe(i + 1);
          }
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 5.3**
  it('should maintain contiguous sequence after simulated remove operations', () => {
    fc.assert(
      fc.property(
        // Start with N lines, remove some indices
        fc.integer({ min: 2, max: 30 }),
        fc.array(fc.integer({ min: 0, max: 29 }), { minLength: 1, maxLength: 10 }),
        (initialCount, removeIndices) => {
          // Build initial lines
          let lines = [];
          for (let i = 0; i < initialCount; i++) {
            lines.push({ id_produk_fg: `PROD${i}`, urut: i + 1 });
          }

          // Filter valid removal indices and make unique
          const validIndices = [...new Set(removeIndices.filter(idx => idx < initialCount))];

          // Don't remove all lines (must keep at least 1)
          if (validIndices.length >= initialCount) return;

          // Remove lines at those indices (from end to preserve indices)
          const sortedDesc = [...validIndices].sort((a, b) => b - a);
          for (const idx of sortedDesc) {
            lines.splice(idx, 1);
          }

          // Renumber remaining
          const renumbered = renumberLines(lines);
          const N = lines.length;

          expect(renumbered.length).toBe(N);
          expect(N).toBeGreaterThan(0);
          for (let i = 0; i < N; i++) {
            expect(renumbered[i].urut).toBe(i + 1);
          }
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 5.3**
  it('should maintain contiguous sequence after arbitrary add/remove operations sequence', () => {
    // Arbitrarily generate a sequence of operations (add/remove) and verify invariant
    const operationArb = fc.oneof(
      fc.record({ type: fc.constant('add'), id: productIdArb }),
      fc.record({ type: fc.constant('remove'), index: fc.nat({ max: 99 }) })
    );

    fc.assert(
      fc.property(
        fc.array(operationArb, { minLength: 1, maxLength: 50 }),
        (operations) => {
          let lines = [];

          for (const op of operations) {
            if (op.type === 'add') {
              lines.push({ id_produk_fg: op.id, urut: 0 });
            } else if (op.type === 'remove' && lines.length > 1) {
              const idx = op.index % lines.length;
              lines.splice(idx, 1);
            }
          }

          // Skip if no lines remain (shouldn't happen since we guard remove)
          if (lines.length === 0) return;

          // Apply renumbering
          const renumbered = renumberLines(lines);

          // Invariant: urut forms [1..N]
          expect(renumbered.length).toBe(lines.length);
          for (let i = 0; i < renumbered.length; i++) {
            expect(renumbered[i].urut).toBe(i + 1);
          }
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 5.3**
  it('should preserve all other fields of each line during renumbering', () => {
    fc.assert(
      fc.property(
        fc.array(
          fc.record({
            id_produk_fg: productIdArb,
            nm_produk_fg: fc.string({ minLength: 1, maxLength: 50 }),
            target_qty: validTargetQtyArb,
            urut: fc.integer({ min: 1, max: 1000 })
          }),
          { minLength: 1, maxLength: 20 }
        ),
        (lines) => {
          const renumbered = renumberLines(lines);

          for (let i = 0; i < lines.length; i++) {
            // Other fields must be preserved
            expect(renumbered[i].id_produk_fg).toBe(lines[i].id_produk_fg);
            expect(renumbered[i].nm_produk_fg).toBe(lines[i].nm_produk_fg);
            expect(renumbered[i].target_qty).toBe(lines[i].target_qty);
            // Only urut changes
            expect(renumbered[i].urut).toBe(i + 1);
          }
        }
      ),
      { numRuns: 100 }
    );
  });
});
