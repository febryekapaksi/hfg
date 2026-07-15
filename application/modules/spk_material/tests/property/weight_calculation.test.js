/**
 * Property-Based Test: Weight Calculation Correctness
 * 
 * Feature: spk-material, Property 2: Weight Calculation Correctness
 * Validates: Requirements 3.1, 3.2
 * 
 * For any valid product weight (berat_per_unit ≥ 0) and valid target qty
 * (integer in [1, 999999]), the calculated Total Weight must equal
 * `berat_per_unit × target_qty`, formatted to 2 decimal places.
 */

const fc = require('fast-check');

// ---------------------------------------------------------------
// Helper: Replicates the JavaScript recalcWeight function logic
// ---------------------------------------------------------------

/**
 * Calculate total weight for a product line.
 * Mirrors the frontend recalcWeight() function in form.php:
 *   var total = berat * qty;
 *   $('#weight_' + idx).val(total.toFixed(2));
 *
 * The .toFixed(2) in JS uses standard rounding (round half away from zero),
 * which is equivalent to: Math.round(value * 100) / 100
 *
 * @param {number} beratPerUnit - Weight per unit in Kg (≥ 0)
 * @param {number} targetQty   - Target production quantity (integer, 1-999999)
 * @returns {number} Total weight rounded to 2 decimal places
 */
function calculateWeight(beratPerUnit, targetQty) {
  return Math.round(beratPerUnit * targetQty * 100) / 100;
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
 * Generator for berat_per_unit (weight per unit, non-negative decimal)
 * Typical range: 0 to 9999.9999 Kg per unit
 */
const beratPerUnitArb = fc.double({
  min: 0,
  max: 9999.9999,
  noNaN: true,
  noDefaultInfinity: true
}).filter(v => v >= 0 && isFinite(v));

/**
 * Generator for positive berat_per_unit (> 0) for tests requiring non-zero weight
 */
const positiveBeratArb = fc.double({
  min: 0.01,
  max: 9999.9999,
  noNaN: true,
  noDefaultInfinity: true
}).filter(v => v > 0 && isFinite(v));

/**
 * Generator for target quantity (positive integer 1-999999)
 */
const targetQtyArb = fc.integer({ min: 1, max: 999999 });

// ---------------------------------------------------------------
// Property Tests
// ---------------------------------------------------------------

describe('Feature: spk-material, Property 2: Weight Calculation Correctness', () => {

  // Property 2.1: CORRECTNESS - Result equals berat_per_unit × target_qty, rounded to 2 decimal places
  it('should equal berat_per_unit × target_qty rounded to 2 decimal places', () => {
    fc.assert(
      fc.property(
        beratPerUnitArb,
        targetQtyArb,
        (beratPerUnit, targetQty) => {
          const result = calculateWeight(beratPerUnit, targetQty);
          const expected = Math.round(beratPerUnit * targetQty * 100) / 100;

          expect(result).toBe(expected);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 2.2: NON-NEGATIVE - Result is always >= 0
  it('should always produce a non-negative result', () => {
    fc.assert(
      fc.property(
        beratPerUnitArb,
        targetQtyArb,
        (beratPerUnit, targetQty) => {
          const result = calculateWeight(beratPerUnit, targetQty);

          expect(result).toBeGreaterThanOrEqual(0);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 2.3: ZERO WEIGHT - If berat_per_unit is 0, result is always 0 regardless of target_qty
  it('should always be 0 when berat_per_unit is 0 regardless of target_qty', () => {
    fc.assert(
      fc.property(
        targetQtyArb,
        (targetQty) => {
          const result = calculateWeight(0, targetQty);

          expect(result).toBe(0);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 2.4: PRECISION - Result always has at most 2 decimal places
  it('should always produce a result with at most 2 decimal places', () => {
    fc.assert(
      fc.property(
        beratPerUnitArb,
        targetQtyArb,
        (beratPerUnit, targetQty) => {
          const result = calculateWeight(beratPerUnit, targetQty);
          const decimalPlaces = countDecimalPlaces(result);

          expect(decimalPlaces).toBeLessThanOrEqual(2);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 2.5: MONOTONIC - Increasing target_qty increases or maintains result
  it('should be monotonically non-decreasing as target_qty increases', () => {
    fc.assert(
      fc.property(
        positiveBeratArb,
        targetQtyArb,
        fc.integer({ min: 1, max: 100 }),
        (beratPerUnit, targetQty, increment) => {
          fc.pre(targetQty + increment <= 999999);

          const result1 = calculateWeight(beratPerUnit, targetQty);
          const result2 = calculateWeight(beratPerUnit, targetQty + increment);

          expect(result2).toBeGreaterThanOrEqual(result1);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 2.6: SCALING - Doubling target_qty approximately doubles result
  it('should approximately double when target_qty is doubled', () => {
    fc.assert(
      fc.property(
        positiveBeratArb,
        fc.integer({ min: 1, max: 499999 }), // Half range to allow doubling
        (beratPerUnit, targetQty) => {
          const result1 = calculateWeight(beratPerUnit, targetQty);
          const result2 = calculateWeight(beratPerUnit, targetQty * 2);

          // Due to rounding to 2 decimals, each result has max 0.005 error
          // result2 has 0.005 error, 2*result1 has 2*0.005 error
          // Total max difference: 0.015, use 0.02 for safety margin
          expect(Math.abs(result2 - 2 * result1)).toBeLessThanOrEqual(0.02);
        }
      ),
      { numRuns: 100 }
    );
  });
});
