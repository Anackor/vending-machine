# Phase 5 / Block A: Local Safety Net Hardening

## Purpose

Stabilize the current local safety net, make the test workflow explicit, and
turn the current coverage and quality baseline into a documented contract.

## Depends on

- `docs/planning/phases/phase-5/README.md`
- `docs/planning/phases/phase-3/block-c-integration-fixtures-and-tests.md`
- `docs/planning/phases/phase-4/block-a-thin-reviewer-interface.md`

## Current findings carried into this block

The Phase 5 audit already confirmed:

- integration fixtures and MongoDB-backed tests exist
- coverage reporting already exists
- quality tooling is already wired locally

The unresolved issues now driving Block A are:

- one failing HTTP integration test in the Phase 4 adapter layer
- ECS drift in recently added Symfony-facing files
- one mixed PHPUnit suite with no explicit unit/integration split
- no enforced minimum coverage threshold yet

## Tasks

- [x] P5-001: audit the real repository state against the original Phase 5 objective
- [x] P5-002: record which safety-net pieces were already delivered during Phases 1 to 4
- [x] P5-003: resolve the current failing HTTP integration regression and return `make test` to green
- [x] P5-004: remove the current ECS drift and return `make quality` to green
- [x] P5-005: define the official split between unit and integration suites
- [x] P5-006: implement explicit PHPUnit suite definitions for unit and integration work
- [x] P5-007: add dedicated Composer entry points for unit and integration suites
- [x] P5-008: add matching `Makefile` targets for the split suites
- [x] P5-009: decide which suite composition backs the default `make test` command
- [x] P5-010: decide and document the first enforced coverage threshold
- [x] P5-011: extend regression or edge-case tests only where the audit still shows meaningful gaps
- [x] P5-012: tighten PHPStan and deptrac only where the added strictness improves signal without excessive noise

## Recommended direction

Keep the split simple:

- `unit`: pure domain, application, and mapper-style fast tests
- `integration`: MongoDB-backed repository, handler, and HTTP adapter tests

The default workflow should remain reviewer-friendly:

- `make test` should stay as the broad safety-net entry point
- narrower suite commands should exist for day-to-day development speed

Coverage should also stay pragmatic:

- keep the first enforced threshold simple and explicit
- prefer one threshold value shared by classes, methods, and lines when the
  project already exceeds it comfortably
- revisit the threshold only if future interface-heavy changes add more noise
  than signal

Implemented Block A decisions:

- `Unit` now covers fast domain, application, mapper, command, and thin
  adapter tests
- `Integration` now covers MongoDB-backed repository and HTTP tests
- `make test` runs both suites sequentially as the broad local safety-net path
- `make coverage` now enforces `90%` minimum coverage on classes, methods, and
  lines through `bin/check-coverage-threshold.php`
- PHPStan and deptrac were intentionally left at their current strictness in
  Block A because the current signal is still good and tightening them now
  would add noise faster than value

## Validation snapshot

Validated at the end of Block A:

- `make test`: successful with `165` tests and `699` assertions
- `make quality`: successful
- `make coverage`: successful
- enforced coverage threshold:
  - classes: `92.31%`
  - methods: `96.73%`
  - lines: `98.65%`

## Output contract

The block is expected to leave behind:

- green local `test`, `quality`, and `coverage` commands
- explicit unit and integration suite commands
- a documented first coverage threshold
- a cleaner handoff into CI automation

## Exit condition

The local safety net is green, explicit, and fast enough to be trusted during
regular development.
