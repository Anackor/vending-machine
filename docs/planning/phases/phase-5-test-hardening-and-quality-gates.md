# Phase 5: Test Hardening and Quality Gates

## Objective

Strengthen behavioral confidence and architectural protection after the main feature path is in place.

## Scope

- integration test expansion
- edge-case coverage
- stronger analysis and architecture gates
- stable maintenance workflow
- GitHub Actions pull-request quality checks

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`
- `docs/planning/phases/phase-1-domain-design-and-core-model.md`
- `docs/planning/phases/phase-3-infrastructure-and-persistence.md`
- `docs/planning/phases/phase-4-initial-interface.md`

## Tasks

- add integration tests for complete purchase flows
- verify state changes after successful operations
- verify state integrity after failed operations
- cover edge cases around change calculation
- validate service setup and replenishment behavior
- tighten PHPStan and deptrac rules where useful
- ensure Rector and ECS are part of the regular maintenance workflow
- define GitHub Actions as the CI path for pull-request quality checks
- add a GitHub Actions workflow triggered on pull requests
- run ECS in the pull-request workflow
- run Rector in dry-run mode in the pull-request workflow
- run PHPStan in the pull-request workflow
- run deptrac in the pull-request workflow
- document the expected PR quality checks and their local command equivalents

## Validations

- critical flows are covered end to end
- edge cases around money and change are explicitly tested
- architecture rules are enforced by tooling
- the regular quality workflow is easy to run and trusted by the team
- pull requests trigger the agreed quality checks automatically
- CI quality commands stay aligned with the documented local workflow

## Deliverables

- stronger integration coverage
- stricter static-analysis and architecture checks
- stable quality workflow
- GitHub Actions workflow for PR quality checks

## Exit criteria

This phase is complete when the project has a trustworthy safety net for behavioral regressions and architectural drift.
