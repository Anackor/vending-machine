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

- [x] P2-031: add unit tests for successful insert-coin orchestration
- [x] P2-032: add unit tests for successful product-selection orchestration
- [x] P2-033: add unit tests for successful refund orchestration
- [x] P2-034: add unit tests for successful service orchestration
- [x] P2-035: add unit tests for translated failure paths exposed by the application layer
- [x] P2-036: add unit tests for repository interaction expectations and state persistence intent
- [x] P2-037: add unit tests for application snapshot and output mapping stability
- [x] P2-038: run the Phase 2 application-layer test suite successfully
- [x] P2-039: run static analysis and architecture checks successfully against the new application code
- [x] P2-040: confirm that Phase 3 and Phase 4 can start without reopening application contracts

## Test-suite baseline

By the end of Phase 2, the application test suite protects the orchestration
layer under `tests/VendingMachine/Application/Machine/`.

The suite now covers:

- successful insert, select, refund, service, and state-query orchestration
- translated failure paths exposed as `MachineOperationFailed`
- repository save or no-save expectations per use case
- stable output mapping through `MachineSnapshotFactory`
- stable failure translation through `MachineFailureFactory`

## Phase gate decision

Phase 3 and Phase 4 can start without reopening the application contracts.

The rationale is:

- `MachineRepository` is already sufficient for the planned single-machine persistence adapter
- handlers expose one stable orchestration path per use case without embedding domain rules
- upper layers can depend on commands, queries, results, snapshots, and failure codes instead of domain internals
- no Symfony or MongoDB concern leaked into the application layer during Phase 2

## Validation snapshot

Validated at the end of Phase 2:

- `make test`: successful with `113` tests and `395` assertions
- `make quality`: successful
- `make coverage`: successful

Quality result summary:

- ECS: successful
- PHPStan: successful
- deptrac: successful with `0` violations and `0` errors
- Rector dry-run: successful

Coverage summary for the current scope:

- classes: `96.88%`
- methods: `99.40%`
- lines: `99.82%`

## Notes

- Block D mainly hardens and validates the application baseline created in Blocks B and C
- dedicated factory tests now protect both snapshot mapping and failure translation directly
- deptrac still reports `12` uncovered elements and `16` allowed dependencies, but the gate is clean because there are no violations or errors
- ECS and Rector normalized a small formatting drift during the gate before the final successful run
- the next step is Phase 3, where MongoDB-backed adapters can satisfy the existing application ports

## Output contract

The block is expected to leave behind:

- application-layer unit coverage for handler orchestration
- validated repository-port expectations
- explicit handoff confirmation for persistence and interface work

## Exit condition

The application layer is trustworthy enough to support infrastructure and
interface adapters without reopening its contracts immediately.
