# Phase 5: Test Hardening and Quality Gates

## Objective

Turn the current safety net into a stable, explicit, reviewer-trustworthy
quality baseline for local work and pull requests.

## Scope

- local test workflow hardening
- explicit unit and integration suite separation
- coverage threshold decision
- remaining edge-case and regression protection
- stable CI quality gates for pull requests

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`
- `docs/planning/phases/phase-1/README.md`
- `docs/planning/phases/phase-3/README.md`
- `docs/planning/phases/phase-4/README.md`

## Canonical structure

This `README.md` is the canonical index for Phase 5.

Use it to understand:

- the phase objective and scope
- the current audit snapshot
- the execution order
- the block status
- the remaining deliverables

Use the block files for task-level execution:

- `block-a-local-safety-net-hardening.md`
- `block-b-ci-quality-gates.md`

## Planning note

Phase 5 is partially advanced already.

Earlier phases introduced:

- domain, application, persistence, and HTTP integration tests
- repeatable MongoDB fixtures for integration work
- local `make test`, `make quality`, and `make coverage` commands
- PHPStan, deptrac, Rector, and ECS in the regular workflow

What remains is not to invent a safety net from scratch, but to harden the
existing one, separate the test suites clearly, fix the current drift, and add
the missing PR automation.

The phase is therefore kept in two blocks:

1. stabilize and sharpen the local safety net
2. mirror that baseline in pull-request CI

## Recommended execution order

1. Block A: local safety net hardening
2. Block B: CI quality gates

## Loading rule for Phase 5 work

For any Phase 5 task, always load:

- this `README.md`
- the active Phase 5 block file

Load Phase 4 documents when:

- HTTP interface regressions or transport tests are being hardened
- the reviewer-facing API contract must stay aligned with the challenge

Load Phase 3 documents when:

- MongoDB fixtures, repository integration tests, or persistence expectations
  are being changed

Load Phase 2 documents when:

- handler orchestration or application failure mapping must be checked

Load Phase 0 documents only when:

- local workflow commands or quality-tool configuration must be updated

## Block index

### A. Local safety net hardening

- file: `docs/planning/phases/phase-5/block-a-local-safety-net-hardening.md`
- task ids: `P5-001` to `P5-012`
- status: complete
- outputs: stabilized local test and quality flow, explicit suite split,
  documented coverage strategy

### B. CI quality gates

- file: `docs/planning/phases/phase-5/block-b-ci-quality-gates.md`
- task ids: `P5-013` to `P5-022`
- status: pending
- outputs: pull-request workflow, local-to-CI parity, final Phase 5 gate

## Current audit snapshot

Audit date: `2026-03-27`

Already available in the repository:

- MongoDB-backed integration tests and repeatable fixtures exist
- `make coverage` is already available and produces repeatable reports
- PHPStan, deptrac, Rector, and ECS are already wired into the local workflow
- the reviewer-facing HTTP layer is already covered by dedicated tests

Resolved in Block A:

- local `make test`, `make quality`, and `make coverage` are green again
- PHPUnit now exposes separate `Unit` and `Integration` suites
- the local workflow now exposes explicit split-suite commands
- the first minimum coverage threshold is enforced locally at `90%` for
  classes, methods, and lines

Still open for Phase 5:

- no `.github/workflows` directory exists yet, so PR quality checks are still
  manual only

Current coverage snapshot from `make coverage`:

- classes: `92.31%`
- methods: `96.73%`
- lines: `98.65%`

## Phase-wide validations

- local test and quality commands are green again
- unit and integration suites can run independently
- the chosen coverage threshold is enforced and documented
- edge cases and regressions around money, change, and HTTP transport are
  explicitly protected
- pull requests trigger the agreed quality checks automatically
- CI commands stay aligned with documented local commands

## Deliverables

- stabilized local safety net
- explicit unit and integration workflow
- documented coverage strategy and threshold
- PR CI workflow for agreed quality checks
- final Phase 5 validation snapshot

## Exit criteria

This phase is complete when the project safety net is green, explicit, and
trusted both locally and on pull requests.
