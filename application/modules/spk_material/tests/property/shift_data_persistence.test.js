/**
 * Property-Based Test: Shift Data Persistence Round-Trip
 * 
 * Feature: spk-material, Property 11: Shift Data Persistence Round-Trip
 * Validates: Requirements 1.2, 1.6
 * 
 * For any SPK with product lines containing valid shift selections (one or more
 * shift IDs per line), saving the SPK and then loading it for editing must produce
 * the exact same shift IDs and shift names for each product line.
 */

const fc = require('fast-check');

// ---------------------------------------------------------------
// Helpers: Replicate the save/load logic for shift data
// ---------------------------------------------------------------

/**
 * Simulate SAVE: Convert array of selected shift IDs to comma-separated string.
 * Mirrors form.php: ($('#shift_' + idx).val() || []).join(',')
 * 
 * @param {string[]} ids - Array of shift ID strings from Select2 .val()
 * @returns {string} Comma-separated shift IDs (e.g. "1,3,5")
 */
function serializeShiftIds(ids) {
  return ids.join(',');
}

/**
 * Simulate SAVE: Convert array of selected shift names to comma-separated string.
 * Mirrors form.php: .find('option:selected').map(fn).get().join(', ')
 * Note: Names are joined with ', ' (comma + space)
 * 
 * @param {string[]} names - Array of shift name strings from selected options
 * @returns {string} Comma-separated shift names (e.g. "Shift 1, Shift 3, Shift 5")
 */
function serializeShiftNames(names) {
  return names.join(', ');
}

/**
 * Simulate LOAD: Parse stored comma-separated shift IDs back to array.
 * Mirrors form.php: selectedIds.split(',')
 * This is used to pre-select shift options in Select2 on edit.
 * 
 * @param {string} str - Comma-separated shift IDs from database
 * @returns {string[]} Array of shift ID strings
 */
function deserializeShiftIds(str) {
  if (!str) return [];
  return str.split(',');
}

/**
 * Simulate LOAD: Parse stored comma-separated shift names back to array.
 * Mirrors the display of shift names in the edit form.
 * Names are stored with ', ' separator, so we split on ', '.
 * 
 * @param {string} str - Comma-separated shift names from database
 * @returns {string[]} Array of shift name strings
 */
function deserializeShiftNames(str) {
  if (!str) return [];
  return str.split(', ');
}

/**
 * Simulate Select2 re-selection on edit: Given the stored shift_ids string
 * and the master shift list, determine which shifts get pre-selected.
 * Mirrors form.php initShiftSelect2():
 *   var selectedArr = selectedIds.split(',');
 *   selectedArr.indexOf(String(s.id)) > -1
 * 
 * @param {string} storedIds - Comma-separated shift IDs from DB
 * @param {Array<{id: number, nama_shift: string}>} masterShifts - Available shifts
 * @returns {{ids: string[], names: string[]}} Re-selected shift data
 */
function simulateSelect2Reselection(storedIds, masterShifts) {
  if (!storedIds) return { ids: [], names: [] };
  var selectedArr = storedIds.split(',');
  var reselectedIds = [];
  var reselectedNames = [];

  masterShifts.forEach(function(s) {
    if (selectedArr.indexOf(String(s.id)) > -1) {
      reselectedIds.push(String(s.id));
      reselectedNames.push(s.nama_shift);
    }
  });

  return { ids: reselectedIds, names: reselectedNames };
}

// ---------------------------------------------------------------
// Generators
// ---------------------------------------------------------------

/**
 * Generator for a valid shift ID (positive integer, typical DB auto-increment)
 */
const shiftIdArb = fc.integer({ min: 1, max: 100 });

/**
 * Generator for a shift name (non-empty string without commas to avoid ambiguity,
 * since names are joined with ', ')
 */
const shiftNameArb = fc.stringOf(
  fc.constantFrom(
    'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
    'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
    'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
    'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
    '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ' ', '-', '&'
  ),
  { minLength: 1, maxLength: 30 }
).filter(name => name.trim().length > 0);

/**
 * Generator for a master shift entry (simulates a row from master_shift table)
 */
const masterShiftEntryArb = fc.record({
  id: shiftIdArb,
  nama_shift: shiftNameArb
});

/**
 * Generator for a list of master shifts (unique IDs, like actual DB records)
 */
const masterShiftListArb = fc.uniqueArray(masterShiftEntryArb, {
  minLength: 1,
  maxLength: 10,
  selector: (entry) => entry.id
});

// ---------------------------------------------------------------
// Property Tests
// ---------------------------------------------------------------

describe('Feature: spk-material, Property 11: Shift Data Persistence Round-Trip', () => {

  // Property 11.1: Shift IDs round-trip is lossless (simple serialization)
  it('should produce exact same shift IDs after serialize and deserialize', () => {
    fc.assert(
      fc.property(
        fc.uniqueArray(shiftIdArb.map(String), { minLength: 1, maxLength: 10 }),
        (ids) => {
          const serialized = serializeShiftIds(ids);
          const deserialized = deserializeShiftIds(serialized);

          expect(deserialized).toEqual(ids);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 11.2: Shift Names round-trip is lossless (names without commas)
  it('should produce exact same shift names after serialize and deserialize when names contain no commas', () => {
    fc.assert(
      fc.property(
        fc.array(shiftNameArb, { minLength: 1, maxLength: 10 }),
        (names) => {
          const serialized = serializeShiftNames(names);
          const deserialized = deserializeShiftNames(serialized);

          expect(deserialized).toEqual(names);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 11.3: Full round-trip with Select2 reselection (save → DB → load → pre-select)
  it('should reselect the exact same shifts when loading for edit', () => {
    fc.assert(
      fc.property(
        masterShiftListArb,
        fc.nat(),
        (masterShifts, seed) => {
          // Pick a non-empty subset of shifts (simulates user selection)
          const numToSelect = (seed % masterShifts.length) + 1;
          const selectedShifts = masterShifts.slice(0, numToSelect);

          // Simulate SAVE: extract IDs and names from selected shifts
          const originalIds = selectedShifts.map(s => String(s.id));
          const originalNames = selectedShifts.map(s => s.nama_shift);

          // Serialize (form → controller → DB)
          const storedIds = serializeShiftIds(originalIds);
          const storedNames = serializeShiftNames(originalNames);

          // Simulate LOAD: DB → form pre-selection via Select2
          const reselected = simulateSelect2Reselection(storedIds, masterShifts);

          // The reselected IDs must match the original (order may differ based on master list order)
          expect(reselected.ids.sort()).toEqual(originalIds.sort());
          expect(reselected.names.sort()).toEqual(originalNames.sort());
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 11.4: Single shift selection round-trip
  it('should correctly persist and reload a single shift selection', () => {
    fc.assert(
      fc.property(
        masterShiftEntryArb,
        (shift) => {
          // Single shift selection
          const ids = [String(shift.id)];
          const names = [shift.nama_shift];

          // Save
          const storedIds = serializeShiftIds(ids);
          const storedNames = serializeShiftNames(names);

          // Load
          const loadedIds = deserializeShiftIds(storedIds);
          const loadedNames = deserializeShiftNames(storedNames);

          expect(loadedIds).toEqual(ids);
          expect(loadedNames).toEqual(names);
          expect(storedIds).not.toContain(','); // single ID has no comma
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 11.5: Stored format is correct comma-separated string
  it('should store shift IDs as comma-separated string without spaces', () => {
    fc.assert(
      fc.property(
        fc.uniqueArray(shiftIdArb.map(String), { minLength: 1, maxLength: 10 }),
        (ids) => {
          const stored = serializeShiftIds(ids);

          // No leading/trailing commas
          expect(stored).not.toMatch(/^,/);
          expect(stored).not.toMatch(/,$/);
          // No spaces in ID string
          expect(stored).not.toContain(' ');
          // Number of commas = number of IDs - 1
          const commaCount = (stored.match(/,/g) || []).length;
          expect(commaCount).toBe(ids.length - 1);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 11.6: Multiple product lines each have independent shift persistence
  it('should persist shifts independently per product line', () => {
    fc.assert(
      fc.property(
        masterShiftListArb,
        fc.integer({ min: 2, max: 5 }),
        fc.nat(),
        (masterShifts, numLines, seed) => {
          // Simulate multiple product lines each with different shift selections
          const lines = [];
          for (let i = 0; i < numLines; i++) {
            const numToSelect = ((seed + i) % masterShifts.length) + 1;
            const selectedShifts = masterShifts.slice(0, numToSelect);
            lines.push({
              shift_ids: serializeShiftIds(selectedShifts.map(s => String(s.id))),
              shift_names: serializeShiftNames(selectedShifts.map(s => s.nama_shift))
            });
          }

          // Simulate loading each line independently
          lines.forEach((line, i) => {
            const reselected = simulateSelect2Reselection(line.shift_ids, masterShifts);
            const expectedIds = deserializeShiftIds(line.shift_ids);

            // Each line's loaded IDs must match its saved IDs
            expect(reselected.ids.sort()).toEqual(expectedIds.sort());
          });
        }
      ),
      { numRuns: 100 }
    );
  });
});
