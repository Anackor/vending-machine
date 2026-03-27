# Phase 3 / Block D: Persistence Phase Gate

## Purpose

Validate the MongoDB persistence baseline and confirm that Phase 4 can build on
stable application contracts plus persistence adapters.

## Depends on

- `docs/planning/phases/phase-3/README.md`
- `docs/planning/phases/phase-3/block-c-integration-fixtures-and-tests.md`
- `docs/planning/phases/phase-0/block-g-quality-toolchain.md`
- `docs/planning/phases/phase-0/block-h-developer-workflow.md`

## Tasks

- [x] P3-032: run the persistence integration suite successfully
- [x] P3-033: run the full project test suite successfully after persistence changes
- [x] P3-034: run static analysis and architecture checks successfully against the new infrastructure code
- [x] P3-035: run coverage and confirm no meaningful regression in the protected scopes
- [x] P3-036: rerun the MongoDB smoke path if useful after the repository implementation
- [x] P3-037: confirm that fixture reset remains deterministic across repeated runs
- [x] P3-038: confirm that `MachineRepository` contracts remained stable through the persistence work
- [x] P3-039: confirm that Phase 4 can start without MongoDB details leaking beyond infrastructure
- [x] P3-040: confirm that Phase 5 can harden integration gates without redesigning the Phase 3 baseline

## Phase gate decision

Phase 4 can now start without reopening the application or persistence
contracts.

The rationale is:

- the application still depends only on `MachineRepository`
- the infrastructure adapter satisfies that contract without changing handler code
- MongoDB specifics stay inside `Infrastructure/Persistence/MongoDB`
- the persistence-backed path is now protected by direct mapper tests plus real MongoDB integration tests

Phase 5 can also build on this baseline because:

- the project already has real integration tests
- coverage now includes persistence infrastructure
- the main remaining hardening work is workflow separation and tighter gates, not architectural redesign

## Validation snapshot

Validated at the end of Phase 3:

- `make bootstrap`: successful
- `make test`: successful with `126` tests and `455` assertions
- `make quality`: successful
- `make coverage`: successful
- `make mongodb-smoke`: successful

Quality result summary:

- ECS: successful
- PHPStan: successful
- deptrac: successful with `0` violations and `0` errors
- Rector dry-run: successful

Coverage summary for the current scope:

- classes: `91.67%`
- methods: `97.89%`
- lines: `99.30%`

## Notes

- the repository now uses one-document-per-machine persistence with `_id = default`
- Phase 3 kept a pragmatic approach with direct MongoDB library usage and no ODM
- deptrac now reports `14` uncovered and `30` allowed dependencies; there are still no violations or errors
- the integration tests deliberately use real MongoDB through Docker and the real repository adapter
- the next step is Phase 4, where a thin Symfony-facing interface can consume the now-persisted application layer

## Output contract

The block is expected to leave behind:

- a validated MongoDB persistence baseline
- explicit handoff confirmation for interface work
- explicit handoff confirmation for future test hardening

## Exit condition

Persistence behavior is trustworthy enough to support the first reviewer-facing
interface without reopening its contracts immediately.
