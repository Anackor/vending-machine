# Phase 0 / Block I: Final Phase Gate

## Purpose

Run the full end-to-end validation of the platform bootstrap before Phase 1 starts.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- all previous Phase 0 block files

## Tasks

- [ ] P0-092: run the full documented bootstrap flow from scratch
- [ ] P0-093: run the full documented quality flow from scratch
- [ ] P0-094: re-run the MongoDB smoke check after the quality setup is in place
- [ ] P0-095: verify the source tree still respects the intended dependency direction
- [ ] P0-096: confirm that Phase 1 can start without reopening platform, tooling, or directory-layout decisions

## Output contract

The block is expected to leave behind:

- a complete validation pass
- a stable starting point for Phase 1
- no unresolved platform decisions that would block domain work

## Exit condition

Phase 1 can begin without revisiting setup decisions immediately.
