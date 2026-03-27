# Phase 2 / Block D: Application Tests and Phase Gate

## Purpose

Protect the application orchestration rules with focused tests and confirm that
Phase 3 can build on stable handler and port contracts.

## Depends on

- `docs/planning/phases/phase-2/README.md`
- `docs/planning/phases/phase-2/block-c-handlers-and-ports.md`
- `docs/planning/phases/phase-0/block-g-quality-toolchain.md`
- `docs/planning/phases/phase-0/block-h-developer-workflow.md`

## Tasks

- [ ] P2-031: add unit tests for successful insert-coin orchestration
- [ ] P2-032: add unit tests for successful product-selection orchestration
- [ ] P2-033: add unit tests for successful refund orchestration
- [ ] P2-034: add unit tests for successful service orchestration
- [ ] P2-035: add unit tests for translated failure paths exposed by the application layer
- [ ] P2-036: add unit tests for repository interaction expectations and state persistence intent
- [ ] P2-037: add unit tests for application snapshot and output mapping stability
- [ ] P2-038: run the Phase 2 application-layer test suite successfully
- [ ] P2-039: run static analysis and architecture checks successfully against the new application code
- [ ] P2-040: confirm that Phase 3 and Phase 4 can start without reopening application contracts

## Output contract

The block is expected to leave behind:

- application-layer unit coverage for handler orchestration
- validated repository-port expectations
- explicit handoff confirmation for persistence and interface work

## Exit condition

The application layer is trustworthy enough to support infrastructure and
interface adapters without reopening its contracts immediately.
