/**
 * Property-Based Test: SPK Number Generation Format and Sequentiality
 * 
 * Feature: spk-material, Property 4: SPK Number Generation
 * Validates: Requirements 6.1
 * 
 * For any sequence of SPK creations within the same month, each generated SPK number
 * must match the format SPK-YYYYMM-XXXX where YYYY is the current year, MM is the
 * current month, and XXXX is a zero-padded counter that increments sequentially
 * from the last used number.
 */

const fc = require('fast-check');

// ---------------------------------------------------------------
// Helper: Replicates the PHP generate_spk_no() logic in JavaScript
// ---------------------------------------------------------------

/**
 * Generate SPK number given a yearMonth prefix and the last SPK number in DB.
 * Mirrors: Spk_material_model::generate_spk_no()
 *
 * @param {string} yearMonth - Format YYYYMM (e.g. "202506")
 * @param {string|null} lastSpkNo - Last SPK number from DB for the same month prefix, or null
 * @returns {string} Next SPK number in format SPK-YYYYMM-XXXX
 */
function generateSpkNo(yearMonth, lastSpkNo) {
  const prefix = `SPK-${yearMonth}-`;
  let nextCounter = 1;

  if (lastSpkNo && lastSpkNo.startsWith(prefix)) {
    const parts = lastSpkNo.split('-');
    nextCounter = parseInt(parts[parts.length - 1], 10) + 1;
  }

  return prefix + String(nextCounter).padStart(4, '0');
}

/**
 * Extract the counter (integer) from an SPK number.
 * 
 * @param {string} spkNo - SPK number in format SPK-YYYYMM-XXXX
 * @returns {number} The numeric counter value
 */
function extractCounter(spkNo) {
  const parts = spkNo.split('-');
  return parseInt(parts[parts.length - 1], 10);
}

/**
 * Extract the yearMonth portion from an SPK number.
 * 
 * @param {string} spkNo - SPK number in format SPK-YYYYMM-XXXX
 * @returns {string} The YYYYMM portion
 */
function extractYearMonth(spkNo) {
  const parts = spkNo.split('-');
  return parts[1];
}

// ---------------------------------------------------------------
// Generators
// ---------------------------------------------------------------

/** Generate a valid year (2020-2099) */
const yearArb = fc.integer({ min: 2020, max: 2099 });

/** Generate a valid month (1-12) */
const monthArb = fc.integer({ min: 1, max: 12 });

/** Generate a valid YYYYMM string */
const yearMonthArb = fc.tuple(yearArb, monthArb).map(
  ([year, month]) => `${year}${String(month).padStart(2, '0')}`
);

/** Generate a valid counter (1-9998, leaving room for next) */
const counterArb = fc.integer({ min: 1, max: 9998 });

/** Generate a starting counter including 0 (no previous SPK) */
const startCounterArb = fc.integer({ min: 0, max: 9998 });

// ---------------------------------------------------------------
// Property Tests
// ---------------------------------------------------------------

describe('Feature: spk-material, Property 4: SPK Number Generation', () => {

  // Property 4.1: FORMAT - Any generated SPK number matches the required regex
  it('should always produce a valid SPK number format (SPK-YYYYMM-XXXX)', () => {
    fc.assert(
      fc.property(
        yearMonthArb,
        startCounterArb,
        (yearMonth, counter) => {
          const lastNo = counter > 0
            ? `SPK-${yearMonth}-${String(counter).padStart(4, '0')}`
            : null;
          const result = generateSpkNo(yearMonth, lastNo);

          // Must match exact format: SPK-6digits-4digits
          expect(result).toMatch(/^SPK-\d{6}-\d{4}$/);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 4.2: SEQUENTIALITY - Given any starting counter, next number increments by 1
  it('should increment counter by exactly 1 from last used number', () => {
    fc.assert(
      fc.property(
        yearMonthArb,
        counterArb,
        (yearMonth, counter) => {
          const lastNo = `SPK-${yearMonth}-${String(counter).padStart(4, '0')}`;
          const result = generateSpkNo(yearMonth, lastNo);

          const resultCounter = extractCounter(result);
          expect(resultCounter).toBe(counter + 1);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 4.3: MONTH RESET - When no previous SPK exists, counter starts at 0001
  it('should start at 0001 when no previous SPK exists for the month', () => {
    fc.assert(
      fc.property(
        yearMonthArb,
        (yearMonth) => {
          const result = generateSpkNo(yearMonth, null);

          expect(result).toBe(`SPK-${yearMonth}-0001`);
          expect(extractCounter(result)).toBe(1);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 4.4: MONTH RESET - When last SPK is from a DIFFERENT month, counter resets to 0001
  it('should reset to 0001 when last SPK is from a different month', () => {
    fc.assert(
      fc.property(
        yearMonthArb,
        yearMonthArb,
        counterArb,
        (currentYm, otherYm, counter) => {
          // Only test when months are actually different
          fc.pre(currentYm !== otherYm);

          const lastNo = `SPK-${otherYm}-${String(counter).padStart(4, '0')}`;
          const result = generateSpkNo(currentYm, lastNo);

          // Should reset to 0001 since the prefix doesn't match
          expect(result).toBe(`SPK-${currentYm}-0001`);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 4.5: COUNTER EXTRACTION - extractCounter is inverse of padding
  it('should correctly extract counter from any valid SPK number', () => {
    fc.assert(
      fc.property(
        yearMonthArb,
        fc.integer({ min: 1, max: 9999 }),
        (yearMonth, counter) => {
          const spkNo = `SPK-${yearMonth}-${String(counter).padStart(4, '0')}`;
          expect(extractCounter(spkNo)).toBe(counter);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 4.6: YEAR-MONTH PRESERVATION - generated number preserves the yearMonth
  it('should preserve the yearMonth in the generated SPK number', () => {
    fc.assert(
      fc.property(
        yearMonthArb,
        startCounterArb,
        (yearMonth, counter) => {
          const lastNo = counter > 0
            ? `SPK-${yearMonth}-${String(counter).padStart(4, '0')}`
            : null;
          const result = generateSpkNo(yearMonth, lastNo);

          expect(extractYearMonth(result)).toBe(yearMonth);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 4.7: SEQUENTIAL BATCH - Generating N SPKs in sequence produces contiguous counters
  it('should produce contiguous sequential numbers when generating multiple SPKs', () => {
    fc.assert(
      fc.property(
        yearMonthArb,
        fc.integer({ min: 0, max: 9979 }),  // max 9979 so startCounter + batchSize(max 20) <= 9999
        fc.integer({ min: 2, max: 20 }),
        (yearMonth, startCounter, batchSize) => {
          // Ensure we won't exceed 4-digit counter limit (9999)
          fc.pre(startCounter + batchSize <= 9999);

          let lastNo = startCounter > 0
            ? `SPK-${yearMonth}-${String(startCounter).padStart(4, '0')}`
            : null;

          const generated = [];
          for (let i = 0; i < batchSize; i++) {
            const newNo = generateSpkNo(yearMonth, lastNo);
            generated.push(newNo);
            lastNo = newNo;
          }

          // All numbers should be sequential
          const expectedStart = startCounter > 0 ? startCounter + 1 : 1;
          for (let i = 0; i < generated.length; i++) {
            expect(extractCounter(generated[i])).toBe(expectedStart + i);
          }

          // All should have valid format
          for (const spkNo of generated) {
            expect(spkNo).toMatch(/^SPK-\d{6}-\d{4}$/);
          }
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 4.8: ZERO-PADDING - Counter is always 4 digits, zero-padded
  it('should always zero-pad the counter to exactly 4 digits', () => {
    fc.assert(
      fc.property(
        yearMonthArb,
        fc.integer({ min: 0, max: 9998 }),
        (yearMonth, counter) => {
          const lastNo = counter > 0
            ? `SPK-${yearMonth}-${String(counter).padStart(4, '0')}`
            : null;
          const result = generateSpkNo(yearMonth, lastNo);

          // Extract raw counter string (last segment after final '-')
          const parts = result.split('-');
          const counterStr = parts[parts.length - 1];

          // Must be exactly 4 characters
          expect(counterStr).toHaveLength(4);
          // Must be all digits
          expect(counterStr).toMatch(/^\d{4}$/);
        }
      ),
      { numRuns: 100 }
    );
  });
});
