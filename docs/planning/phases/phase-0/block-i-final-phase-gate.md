# Phase 0 / Block I: Final Phase Gate

## Purpose

Run the full end-to-end validation of the platform bootstrap before Phase 1 starts.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- all previous Phase 0 block files

## Tasks

- [x] P0-092: run the full documented bootstrap flow from scratch
- [x] P0-093: run the full documented quality flow from scratch
- [x] P0-094: re-run the MongoDB smoke check after the quality setup is in place
- [x] P0-095: verify the source tree still respects the intended dependency direction
- [x] P0-096: confirm that Phase 1 can start without reopening platform, tooling, or directory-layout decisions

## Validation snapshot

Validated on `2026-03-27` using the documented Phase 0 entry points:

- `make down`: successful
- `make bootstrap`: successful from a clean Docker state
- `make quality`: successful
- `make test`: successful with the expected placeholder output
- `make mongodb-smoke`: successful after the quality flow

## Architecture confirmation

The final gate confirmed that the source tree still reflects the intended
dependency direction:

- `Domain` remains isolated from Symfony and MongoDB-specific code
- `Application` remains separate from delivery and persistence concerns
- `Infrastructure/Symfony` and `Infrastructure/Persistence/MongoDB` remain explicit outer adapters
- deptrac completed with `0` violations and `0` errors during the final quality run

## Readiness decision

Phase 1 can start without reopening:

- runtime or container choices
- Symfony bootstrap decisions
- namespace or layer boundaries
- MongoDB integration direction
- local workflow and quality entry points

## Notes

- the final gate surfaced a real formatting drift in `ecs.php`, `rector.php`, and `src/VendingMachine/Infrastructure/Symfony/Command/MongoDBSmokeCommand.php`
- the drift was corrected through ECS and the full quality flow was rerun successfully
- deptrac still reports uncovered dependencies, but that hardening remains intentionally deferred from Phase 0 as documented in Block G

## Output contract

The block is expected to leave behind:

- a complete validation pass
- a stable starting point for Phase 1
- no unresolved platform decisions that would block domain work

## Exit condition

Phase 1 can begin without revisiting setup decisions immediately.
