# Phase 5 / Block B: CI Quality Gates

## Purpose

Mirror the agreed local quality baseline in pull-request automation so the
project stops depending on manual validation only.

## Depends on

- `docs/planning/phases/phase-5/README.md`
- `docs/planning/phases/phase-5/block-a-local-safety-net-hardening.md`
- `docs/planning/phases/phase-0/block-h-developer-workflow.md`

## Recommended CI direction

The initial pull-request workflow should stay small and fast.

Recommended first path:

- use GitHub Actions on `pull_request`
- install PHP directly in CI rather than running the whole Docker stack
- reuse the existing Composer scripts so local and CI commands stay aligned

This keeps the first CI baseline focused on quality checks:

- ECS
- Rector in dry-run mode
- PHPStan
- deptrac

Unit tests may join the same workflow once Block A defines the final suite
split. MongoDB-backed integration tests can remain a follow-up job or a later
extension if they would slow down the first PR baseline too much.

Implemented Block B decisions:

- the first PR workflow now lives in `.github/workflows/pull-request-quality.yml`
- GitHub Actions installs PHP `8.4` directly on `ubuntu-latest`
- CI restores cached `vendor` and tool caches before running the checks
- the workflow reuses existing Composer scripts for every automated check
- `Unit` joins the first PR workflow because it is fast and does not require
  MongoDB
- `Integration` intentionally stays outside the first PR workflow to keep the
  baseline quick and DB-free while remaining available locally through
  `make test-integration`

Local-to-CI parity:

- ECS -> `make ecs`
- Rector -> `make rector`
- PHPStan -> `make phpstan`
- deptrac -> `make deptrac`
- unit tests -> `make test-unit`

## Tasks

- [x] P5-013: freeze the initial PR CI scope and execution strategy
- [x] P5-014: create the `.github/workflows` baseline for pull requests
- [x] P5-015: install PHP, Composer dependencies, and cache the vendor path appropriately in CI
- [x] P5-016: run ECS in the PR workflow
- [x] P5-017: run Rector in dry-run mode in the PR workflow
- [x] P5-018: run PHPStan in the PR workflow
- [x] P5-019: run deptrac in the PR workflow
- [x] P5-020: decide whether unit tests join the first PR workflow or a companion workflow
- [x] P5-021: document the local command equivalent for each CI check
- [x] P5-022: validate the final Phase 5 gate with local commands plus the new CI baseline

## Validation snapshot

Validated at the end of Block B:

- `make test-unit`: successful with `153` tests and `551` assertions
- `make test-integration`: successful with `12` tests and `148` assertions
- `make test`: successful with `165` tests and `699` assertions
- `make quality`: successful
- `make coverage`: successful

The CI baseline is intentionally mirrored from existing local commands rather
than introducing one-off CI-only commands.

## Output contract

The block is expected to leave behind:

- one clear pull-request quality workflow
- explicit local-to-CI parity for each automated check
- a final validation snapshot that closes Phase 5 cleanly

## Exit condition

Pull requests automatically run the agreed quality baseline and the workflow is
easy to map back to local commands.
