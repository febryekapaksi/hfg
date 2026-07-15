/**
 * Property-Based Tests: State Transitions
 * 
 * Feature: spk-material
 * Property 6: Valid State Transitions
 * Property 7: Invalid State Transitions Rejected
 * Property 8: Editability Determined by Status
 * 
 * Validates: Requirements 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 8.8, 8.9
 */

const fc = require('fast-check');

// ---------------------------------------------------------------
// State Machine Logic (replicates controller's transition logic)
// ---------------------------------------------------------------

const ALL_STATUSES = ['Material Requested', 'Material Confirmed', 'Released', 'Cancelled'];
const EDITABLE_STATUSES = ['Material Requested', 'Material Confirmed'];
const TERMINAL_STATUSES = ['Released', 'Cancelled'];
const ALLOWED_TRANSITIONS = {
  'Material Requested': ['Material Confirmed', 'Cancelled'],
  'Material Confirmed': ['Released', 'Cancelled'],
};

/**
 * Attempt a status transition. Mirrors controller update_status() logic.
 * 
 * @param {string} currentStatus - Current SPK status
 * @param {string} newStatus - Target status to transition to
 * @returns {{ success: boolean, newStatus?: string, message?: string }}
 */
function attemptTransition(currentStatus, newStatus) {
  if (TERMINAL_STATUSES.includes(currentStatus)) {
    return { success: false, message: 'Terminal status' };
  }
  const allowed = ALLOWED_TRANSITIONS[currentStatus] || [];
  if (!allowed.includes(newStatus)) {
    return { success: false, message: 'Invalid transition' };
  }
  return { success: true, newStatus };
}

/**
 * Determine if an SPK is editable based on its status.
 * Mirrors controller is_editable() logic.
 * 
 * @param {string} status - Current SPK status
 * @returns {boolean}
 */
function isEditable(status) {
  return EDITABLE_STATUSES.includes(status);
}

// ---------------------------------------------------------------
// Generators
// ---------------------------------------------------------------

/** Generate any valid SPK status */
const statusArb = fc.constantFrom(...ALL_STATUSES);

/** Generate an editable status */
const editableStatusArb = fc.constantFrom(...EDITABLE_STATUSES);

/** Generate a terminal status */
const terminalStatusArb = fc.constantFrom(...TERMINAL_STATUSES);

// ---------------------------------------------------------------
// Property 6: Valid State Transitions
// Validates: Requirements 8.2, 8.3, 8.4
// ---------------------------------------------------------------

describe('Feature: spk-material, Property 6: Valid State Transitions', () => {

  // Property 6.1: All explicitly allowed transitions must succeed
  it('should allow transition from "Material Requested" to "Material Confirmed"', () => {
    fc.assert(
      fc.property(
        fc.constant(['Material Requested', 'Material Confirmed']),
        ([from, to]) => {
          const result = attemptTransition(from, to);
          expect(result.success).toBe(true);
          expect(result.newStatus).toBe(to);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 8.3**
  it('should allow transition from "Material Confirmed" to "Released"', () => {
    fc.assert(
      fc.property(
        fc.constant(['Material Confirmed', 'Released']),
        ([from, to]) => {
          const result = attemptTransition(from, to);
          expect(result.success).toBe(true);
          expect(result.newStatus).toBe(to);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 8.4**
  it('should allow transition from "Material Requested" to "Cancelled"', () => {
    fc.assert(
      fc.property(
        fc.constant(['Material Requested', 'Cancelled']),
        ([from, to]) => {
          const result = attemptTransition(from, to);
          expect(result.success).toBe(true);
          expect(result.newStatus).toBe(to);
        }
      ),
      { numRuns: 100 }
    );
  });

  // **Validates: Requirements 8.4**
  it('should allow transition from "Material Confirmed" to "Cancelled"', () => {
    fc.assert(
      fc.property(
        fc.constant(['Material Confirmed', 'Cancelled']),
        ([from, to]) => {
          const result = attemptTransition(from, to);
          expect(result.success).toBe(true);
          expect(result.newStatus).toBe(to);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 6.5: For any editable status and any allowed target, transition succeeds
  it('should succeed for any randomly chosen valid transition pair', () => {
    // Generate valid (from, to) pairs from the ALLOWED_TRANSITIONS map
    const validTransitionArb = fc.oneof(
      fc.constant({ from: 'Material Requested', to: 'Material Confirmed' }),
      fc.constant({ from: 'Material Requested', to: 'Cancelled' }),
      fc.constant({ from: 'Material Confirmed', to: 'Released' }),
      fc.constant({ from: 'Material Confirmed', to: 'Cancelled' })
    );

    fc.assert(
      fc.property(
        validTransitionArb,
        ({ from, to }) => {
          const result = attemptTransition(from, to);
          expect(result.success).toBe(true);
          expect(result.newStatus).toBe(to);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 6.6: Only the defined transitions are allowed — no others from editable states
  it('should reject transitions not in the allowed set from editable statuses', () => {
    fc.assert(
      fc.property(
        editableStatusArb,
        statusArb,
        (currentStatus, targetStatus) => {
          const allowed = ALLOWED_TRANSITIONS[currentStatus] || [];
          const result = attemptTransition(currentStatus, targetStatus);

          if (allowed.includes(targetStatus)) {
            expect(result.success).toBe(true);
            expect(result.newStatus).toBe(targetStatus);
          } else {
            expect(result.success).toBe(false);
            expect(result.message).toBeDefined();
          }
        }
      ),
      { numRuns: 100 }
    );
  });
});

// ---------------------------------------------------------------
// Property 7: Invalid State Transitions Rejected
// Validates: Requirements 8.5, 8.6, 8.9
// ---------------------------------------------------------------

describe('Feature: spk-material, Property 7: Invalid State Transitions Rejected', () => {

  // Property 7.1: Any transition from "Released" must be rejected
  // **Validates: Requirements 8.5**
  it('should reject any transition attempt from "Released" status', () => {
    fc.assert(
      fc.property(
        statusArb,
        (targetStatus) => {
          const result = attemptTransition('Released', targetStatus);
          expect(result.success).toBe(false);
          expect(result.message).toBe('Terminal status');
          expect(result.newStatus).toBeUndefined();
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 7.2: Any transition from "Cancelled" must be rejected
  // **Validates: Requirements 8.6**
  it('should reject any transition attempt from "Cancelled" status', () => {
    fc.assert(
      fc.property(
        statusArb,
        (targetStatus) => {
          const result = attemptTransition('Cancelled', targetStatus);
          expect(result.success).toBe(false);
          expect(result.message).toBe('Terminal status');
          expect(result.newStatus).toBeUndefined();
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 7.3: For any terminal status and ANY target status, rejection is guaranteed
  // **Validates: Requirements 8.5, 8.6, 8.9**
  it('should reject all transitions from any terminal status to any target', () => {
    fc.assert(
      fc.property(
        terminalStatusArb,
        statusArb,
        (terminalStatus, targetStatus) => {
          const result = attemptTransition(terminalStatus, targetStatus);

          // Must be rejected
          expect(result.success).toBe(false);
          // Status must remain unchanged (no newStatus in result)
          expect(result.newStatus).toBeUndefined();
          // Error must be returned
          expect(result.message).toBeDefined();
          expect(result.message).toBe('Terminal status');
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 7.4: Self-transitions are also rejected from terminal statuses
  it('should reject self-transitions from terminal statuses', () => {
    fc.assert(
      fc.property(
        terminalStatusArb,
        (terminalStatus) => {
          const result = attemptTransition(terminalStatus, terminalStatus);
          expect(result.success).toBe(false);
          expect(result.message).toBe('Terminal status');
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 7.5: Invalid transitions from editable statuses are also rejected
  // **Validates: Requirements 8.9**
  it('should reject invalid transitions from editable statuses (not in allowed set)', () => {
    // Generate pairs where target is NOT in the allowed list for the source
    fc.assert(
      fc.property(
        editableStatusArb,
        statusArb,
        (currentStatus, targetStatus) => {
          const allowed = ALLOWED_TRANSITIONS[currentStatus] || [];
          fc.pre(!allowed.includes(targetStatus));

          const result = attemptTransition(currentStatus, targetStatus);
          expect(result.success).toBe(false);
          expect(result.message).toBe('Invalid transition');
          expect(result.newStatus).toBeUndefined();
        }
      ),
      { numRuns: 100 }
    );
  });
});

// ---------------------------------------------------------------
// Property 8: Editability Determined by Status
// Validates: Requirements 8.7, 8.8
// ---------------------------------------------------------------

describe('Feature: spk-material, Property 8: Editability Determined by Status', () => {

  // Property 8.1: SPK with editable status must be editable
  // **Validates: Requirements 8.7**
  it('should allow editing when status is "Material Requested" or "Material Confirmed"', () => {
    fc.assert(
      fc.property(
        editableStatusArb,
        (status) => {
          expect(isEditable(status)).toBe(true);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 8.2: SPK with terminal status must NOT be editable
  // **Validates: Requirements 8.8**
  it('should reject editing when status is "Released" or "Cancelled"', () => {
    fc.assert(
      fc.property(
        terminalStatusArb,
        (status) => {
          expect(isEditable(status)).toBe(false);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 8.3: Editability is a function of status classification only
  it('should return true if and only if status is in EDITABLE_STATUSES', () => {
    fc.assert(
      fc.property(
        statusArb,
        (status) => {
          const editable = isEditable(status);
          const expectedEditable = EDITABLE_STATUSES.includes(status);
          expect(editable).toBe(expectedEditable);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 8.4: Editability and terminality are mutually exclusive
  it('should never have a status that is both editable and terminal', () => {
    fc.assert(
      fc.property(
        statusArb,
        (status) => {
          const editable = isEditable(status);
          const terminal = TERMINAL_STATUSES.includes(status);

          // A status cannot be both editable and terminal
          expect(editable && terminal).toBe(false);
          // Every status must be either editable or terminal (complete partition)
          expect(editable || terminal).toBe(true);
        }
      ),
      { numRuns: 100 }
    );
  });

  // Property 8.5: Editability aligns with transition possibility
  it('should be editable if and only if the status has possible outgoing transitions', () => {
    fc.assert(
      fc.property(
        statusArb,
        (status) => {
          const editable = isEditable(status);
          const hasTransitions = (ALLOWED_TRANSITIONS[status] || []).length > 0;

          // Editable statuses should have allowed transitions
          // Terminal statuses should not have allowed transitions
          expect(editable).toBe(hasTransitions);
        }
      ),
      { numRuns: 100 }
    );
  });
});
