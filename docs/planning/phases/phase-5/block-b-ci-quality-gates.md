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

## Tasks

- [ ] P5-013: freeze the initial PR CI scope and execution strategy
- [ ] P5-014: create the `.github/workflows` baseline for pull requests
- [ ] P5-015: install PHP, Composer dependencies, and cache the vendor path appropriately in CI
- [ ] P5-016: run ECS in the PR workflow
- [ ] P5-017: run Rector in dry-run mode in the PR workflow
- [ ] P5-018: run PHPStan in the PR workflow
- [ ] P5-019: run deptrac in the PR workflow
- [ ] P5-020: decide whether unit tests join the first PR workflow or a companion workflow
- [ ] P5-021: document the local command equivalent for each CI check
- [ ] P5-022: validate the final Phase 5 gate with local commands plus the new CI baseline

## Output contract

The block is expected to leave behind:

- one clear pull-request quality workflow
- explicit local-to-CI parity for each automated check
- a final validation snapshot that closes Phase 5 cleanly

## Exit condition

Pull requests automatically run the agreed quality baseline and the workflow is
easy to map back to local commands.
