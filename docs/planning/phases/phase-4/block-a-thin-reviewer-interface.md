# Phase 4 / Block A: Thin Reviewer Interface

## Purpose

Implement the first reviewer-facing interface on top of the persisted
application layer without leaking framework concerns into the core.

## Depends on

- `docs/planning/phases/phase-4/README.md`
- `docs/planning/phases/phase-2/block-c-handlers-and-ports.md`
- `docs/planning/phases/phase-3/block-d-persistence-phase-gate.md`

## Recommended interface direction

Phase 4 should prefer one minimal HTTP JSON interface implemented with thin
Symfony controllers.

Why this is the recommended path:

- it is easier for an external reviewer to exercise with `curl` or an API
  client than an interactive console flow
- it stays thin because the application layer already exposes stable commands,
  query models, results, and failures
- it keeps Phase 4 focused on transport and presentation, not on new business
  behavior

What Phase 4 should avoid:

- HTML or templating work
- API Platform or other heavy abstractions
- authentication or user management
- interface-side business logic

## Tasks

- [ ] P4-001: freeze the initial interface mode as one minimal Symfony HTTP JSON layer
- [ ] P4-002: define the exact endpoint surface for machine state and the main mutation flows
- [ ] P4-003: define the request payload for inserting a coin
- [ ] P4-004: define the request payload for selecting a product
- [ ] P4-005: define the request payload for returning inserted money
- [ ] P4-006: define the request payload for servicing the machine stock and available change
- [ ] P4-007: define the response shape for machine state snapshots
- [ ] P4-008: define the response shape for successful mutation outcomes, including vended product and returned change
- [ ] P4-009: define the transport mapping for application failures and their HTTP status codes
- [ ] P4-010: implement the thin Symfony controller or action layer without business logic
- [ ] P4-011: wire the controllers to Phase 2 handlers and query services only
- [ ] P4-012: expose the persisted machine state through a reviewer-friendly read endpoint
- [ ] P4-013: expose the main challenge actions through mutation endpoints backed by real persistence
- [ ] P4-014: document representative request and response flows in reviewer-facing documentation
- [ ] P4-015: validate the end-to-end interface path with tests, quality checks, and real example executions

## Output contract

The block is expected to leave behind:

- one thin reviewer-facing Symfony interface
- explicit transport contracts for success and failure outputs
- documented example flows for the challenge use cases
- a validated handoff to Phase 5 hardening

## Exit condition

A reviewer can interact with the machine through the chosen interface and the
transport layer remains a thin adapter on top of the application contracts.
