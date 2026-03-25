# Phase 5: Test Hardening and Quality Gates

## Objective

Strengthen behavioral confidence and architectural protection after the main feature path is in place.

## Scope

- integration test expansion
- edge-case coverage
- stronger analysis and architecture gates
- stable maintenance workflow

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

## Validations

- critical flows are covered end to end
- edge cases around money and change are explicitly tested
- architecture rules are enforced by tooling
- the regular quality workflow is easy to run and trusted by the team

## Deliverables

- stronger integration coverage
- stricter static-analysis and architecture checks
- stable quality workflow

## Exit criteria

This phase is complete when the project has a trustworthy safety net for behavioral regressions and architectural drift.
