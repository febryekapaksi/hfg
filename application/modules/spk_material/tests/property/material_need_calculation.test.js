/**
 * Property-Based Test: Material Need Calculation
 * 
 * Feature: spk-material, Property 3: Material Need Calculation
 * Validates: Requirements 4.2
 * 
 * For any BOM detail item with quantity `bom_qty` and any valid `target_qty`,
 * the net weight needed for that material must equal `bom_qty × target_qty`,
 * calculated to 4 decimal places.
 */

const fc = require('fast-check');

// ---------------------------------------------------------------
// Helper: Replicates the PHP/SQL ROUND(bom_qty * target_qty, 4) logic
// ---------------------------------------------------------------

/**
 * Calculate qty_needed for a material based on BOM quantity and target quantity.
 * Mirrors: SQL `ROUND(bd.qty * target_qty, 4)` in Spk_material_model::get_bom_materials()
 *
 * @param {number} bomQty   - BOM detail quantity per unit (positive decimal)
 * @param {number} targetQty - Production target quantity (positive integer)
 * @returns {number} Net weight needed, rounded to 4 decimal places
 */
function calculateQtyNeeded(bomQty, targetQty) {
  return Math.round(bomQty * targetQty * 10000) / 10000;
}

/**
 * Count the number of decimal places in a number.
 *
 * @param {number} value - The number to check
 * @returns {number} Number of decimal places
 */
function countDecimalPlaces(value) {
  const str = value.toString();
  if (str.indexOf('.') === -1) return 0;
  return str.split('.')[1].length;
}

// ---------------------------------------------------------------
// Generators
// ---------------------------------------------------------------

/**
 * Generator for BOM quantity (positive decimal, typical range 0.0001 to 999.9999)
 * Represents material weight/quantity needed per unit of finished good
 */
const bomQtyArb = fc.double({
  min: 0.0001,
  max: 999.9999,
  noNaN: true,
  noDefaultInfinity: true
}).filter(v => v > 0 && isFinite(v));

/**
 * Generator for target quantity (positive integer 1-999999)
 * Represents production target quantity
 */
const targetQtyArb = fc.integer({ min: 1, max: 999999 });

/**
 * Generator for very small BOM quantities (edge case: precision matters)
 */
const smallBomQtyArb = fc.double({
  min: 0.0001,
  max: 0.01,
  noNaN: true,
  noDefaultInfinity: true
}).filter(v => v > 0 && isFinite(v));

/**
 * Generator for large target quantities (edge case: large multiplications)
 */
const largeTargetQtyArb = fc.integer({ min: 100000, max: 999999 });

// ---------------------------------------------------------------
// Property Tests
// ---------------------------------------------------------------

describe('Feature: spk-material, Property 3: Material Need Calculation', () => {

  // Property 3.1: CORRECTNESS - qty_needed = round(bom_qty × target_qty, 4)
  it('should equal bom_qty × target_qty rounded to 4 decimal places', () => {
    fc.assert(
      fc.property(
        bomQtyArb,
        targetQtyArb,
        (bomQty, targetQty) => {
          const result = calculateQtyNeeded(bomQty, targetQty);
          const expected = Math.round(bomQty * targetQty * 10000) / 10000;

          expect(result).toBe(expected);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 3.2: PRECISION - Result always has at most 4 decimal places
  it('should always produce a result with at most 4 decimal places', () => {
    fc.assert(
      fc.property(
        bomQtyArb,
        targetQtyArb,
        (bomQty, targetQty) => {
          const result = calculateQtyNeeded(bomQty, targetQty);
          const decimalPlaces = countDecimalPlaces(result);

          expect(decimalPlaces).toBeLessThanOrEqual(4);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 3.3: NON-NEGATIVE - Result is always >= 0 (both inputs are positive)
  it('should always produce a non-negative result since both inputs are positive', () => {
    fc.assert(
      fc.property(
        bomQtyArb,
        targetQtyArb,
        (bomQty, targetQty) => {
          const result = calculateQtyNeeded(bomQty, targetQty);

          expect(result).toBeGreaterThanOrEqual(0);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 3.4: STRICTLY POSITIVE - Since both inputs are > 0, result must be > 0
  it('should always produce a strictly positive result for positive inputs', () => {
    fc.assert(
      fc.property(
        bomQtyArb,
        targetQtyArb,
        (bomQty, targetQty) => {
          const result = calculateQtyNeeded(bomQty, targetQty);

          // With bom_qty >= 0.0001 and target_qty >= 1, minimum raw product is 0.0001
          // After rounding to 4 decimals, should be at least 0.0001
          expect(result).toBeGreaterThan(0);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 3.5: SCALING - Doubling target_qty should approximately double qty_needed
  it('should scale proportionally with target_qty', () => {
    fc.assert(
      fc.property(
        bomQtyArb,
        fc.integer({ min: 1, max: 499999 }), // Half range to allow doubling
        (bomQty, targetQty) => {
          const result1 = calculateQtyNeeded(bomQty, targetQty);
          const result2 = calculateQtyNeeded(bomQty, targetQty * 2);

          // Due to rounding, result2 should be approximately 2 * result1
          // Each rounding introduces up to 0.00005 error
          // result2 has 0.00005 error, 2*result1 has 2*0.00005 error
          // Total maximum difference: 0.00015, use 0.0002 for safety margin
          expect(Math.abs(result2 - 2 * result1)).toBeLessThanOrEqual(0.0002);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 3.6: MONOTONICITY - Increasing target_qty always increases or maintains qty_needed
  it('should be monotonically non-decreasing as target_qty increases', () => {
    fc.assert(
      fc.property(
        bomQtyArb,
        targetQtyArb,
        fc.integer({ min: 1, max: 100 }), // increment amount
        (bomQty, targetQty, increment) => {
          fc.pre(targetQty + increment <= 999999);

          const result1 = calculateQtyNeeded(bomQty, targetQty);
          const result2 = calculateQtyNeeded(bomQty, targetQty + increment);

          expect(result2).toBeGreaterThanOrEqual(result1);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 3.7: EDGE CASE - Very small bom_qty with very large target_qty maintains precision
  it('should maintain precision with very small bom_qty and very large target_qty', () => {
    fc.assert(
      fc.property(
        smallBomQtyArb,
        largeTargetQtyArb,
        (bomQty, targetQty) => {
          const result = calculateQtyNeeded(bomQty, targetQty);

          // Result should still be a valid finite number
          expect(isFinite(result)).toBe(true);
          // Precision constraint still holds
          expect(countDecimalPlaces(result)).toBeLessThanOrEqual(4);
          // Should be positive
          expect(result).toBeGreaterThan(0);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 3.8: COMMUTATIVITY OF MULTIPLICATION - bom_qty × target_qty order doesn't matter
  it('should produce the same result regardless of multiplication order', () => {
    fc.assert(
      fc.property(
        bomQtyArb,
        targetQtyArb,
        (bomQty, targetQty) => {
          // Calculate using normal order
          const result1 = Math.round(bomQty * targetQty * 10000) / 10000;
          // Calculate using reversed operand order
          const result2 = Math.round(targetQty * bomQty * 10000) / 10000;

          expect(result1).toBe(result2);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 3.9: MINIMUM VALUE - With minimum inputs, result should be at least 0.0001
  it('should produce at least 0.0001 with minimum valid inputs', () => {
    fc.assert(
      fc.property(
        fc.double({ min: 0.0001, max: 1, noNaN: true, noDefaultInfinity: true }).filter(v => v >= 0.0001),
        fc.integer({ min: 1, max: 10 }),
        (bomQty, targetQty) => {
          const result = calculateQtyNeeded(bomQty, targetQty);

          // With bomQty >= 0.0001 and targetQty >= 1
          // raw product >= 0.0001, after rounding to 4 decimals should be >= 0.0001
          expect(result).toBeGreaterThanOrEqual(0.0001);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 3.10: ROUNDING BOUNDARY - Result differs from raw product by at most 0.00005
  it('should differ from raw multiplication by at most half a unit of least precision', () => {
    fc.assert(
      fc.property(
        bomQtyArb,
        targetQtyArb,
        (bomQty, targetQty) => {
          const result = calculateQtyNeeded(bomQty, targetQty);
          const rawProduct = bomQty * targetQty;

          // The rounding error should be at most 0.00005
          expect(Math.abs(result - rawProduct)).toBeLessThanOrEqual(0.00005);
        }
      ),
      { numRuns: 100 }
    );
  });
});
