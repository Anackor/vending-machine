# AI Phase Index

This document is designed to minimize context usage during implementation.

Load only the documents needed for the phase you are executing.

## Common loading rule

For every phase, always load:

- `README.md`
- `docs/challenges/backend/README.md`
- the current phase document

Load previous phase documents only if:

- the current phase depends on unresolved decisions from them
- you need to verify a validation gate or output contract

## Phase map

### Phase 0

- file: `docs/planning/phases/phase-0/README.md`
- block files:
  - `docs/planning/phases/phase-0/block-a-runtime-and-tooling.md`
  - `docs/planning/phases/phase-0/block-b-docker-workspace.md`
  - `docs/planning/phases/phase-0/block-c-symfony-bootstrap.md`
  - `docs/planning/phases/phase-0/block-d-architecture-and-namespace.md`
  - `docs/planning/phases/phase-0/block-e-ddd-naming.md`
  - `docs/planning/phases/phase-0/block-f-mongodb-foundation.md`
  - `docs/planning/phases/phase-0/block-g-quality-toolchain.md`
  - `docs/planning/phases/phase-0/block-h-developer-workflow.md`
  - `docs/planning/phases/phase-0/block-i-final-phase-gate.md`
- purpose: bootstrap Symfony, Docker, MongoDB, architecture boundaries, and quality tooling
- depends on: challenge brief only
- loading rule:
  - always load `docs/planning/phases/phase-0/README.md`
  - load only the block file you are actively executing
  - load previous Phase 0 block files only when their frozen decisions or validated outputs are required
- main outputs:
  - Docker local environment
  - Symfony project skeleton
  - MongoDB local setup
  - PHPStan, deptrac, Rector, ECS
  - base architectural structure

### Phase 1

- file: `docs/planning/phases/phase-1/README.md`
- block files:
  - `docs/planning/phases/phase-1/block-a-assumptions-and-language.md`
  - `docs/planning/phases/phase-1/block-b-money-and-machine-primitives.md`
  - `docs/planning/phases/phase-1/block-c-core-machine-behaviors.md`
  - `docs/planning/phases/phase-1/block-d-domain-test-suite-and-phase-gate.md`
- purpose: design and implement the domain model and business rules
- depends on:
  - Phase 0 outputs
- loading rule:
  - always load `docs/planning/phases/phase-1/README.md`
  - load only the block file you are actively executing
  - load previous Phase 1 block files only when their assumptions or validated outputs are required
- main outputs:
  - domain assumptions
  - aggregates and value objects
  - implemented machine behaviors
  - unit tests for core rules

### Phase 2

- file: `docs/planning/phases/phase-2/README.md`
- block files:
  - `docs/planning/phases/phase-2/block-a-use-cases-and-boundaries.md`
  - `docs/planning/phases/phase-2/block-b-application-contracts.md`
  - `docs/planning/phases/phase-2/block-c-handlers-and-ports.md`
  - `docs/planning/phases/phase-2/block-d-application-tests-and-phase-gate.md`
- purpose: implement use cases and persistence-facing ports
- depends on:
  - Phase 1 outputs
- loading rule:
  - always load `docs/planning/phases/phase-2/README.md`
  - load only the block file you are actively executing
  - load previous Phase 2 block files only when their contracts or validated outputs are required
- main outputs:
  - application services
  - ports
  - stable input and output models

### Phase 3

- file: `docs/planning/phases/phase-3/README.md`
- block files:
  - `docs/planning/phases/phase-3/block-a-persistence-boundaries-and-document-shape.md`
  - `docs/planning/phases/phase-3/block-b-mongodb-repository-and-mapper.md`
  - `docs/planning/phases/phase-3/block-c-integration-fixtures-and-tests.md`
  - `docs/planning/phases/phase-3/block-d-persistence-phase-gate.md`
- purpose: implement MongoDB adapters and persistence integration
- depends on:
  - Phase 0 outputs
  - Phase 1 outputs
  - Phase 2 ports
- loading rule:
  - always load `docs/planning/phases/phase-3/README.md`
  - load only the block file you are actively executing
  - load previous Phase 3 block files only when their persistence contracts or validated outputs are required
- main outputs:
  - MongoDB repositories
  - persistence mapping
  - MongoDB fixture baseline
  - persistence integration tests

### Phase 4

- file: `docs/planning/phases/phase-4/README.md`
- block files:
  - `docs/planning/phases/phase-4/block-a-thin-reviewer-interface.md`
- purpose: expose the backend through a thin Symfony-driven interface
- depends on:
  - Phase 2 outputs
  - Phase 3 outputs
- loading rule:
  - always load `docs/planning/phases/phase-4/README.md`
  - load only `docs/planning/phases/phase-4/block-a-thin-reviewer-interface.md`
  - load Phase 3 only when persistence-backed behavior must be checked
  - load Phase 2 only when handler contracts or application failures must be checked
- main outputs:
  - thin reviewer-facing interface
  - documented usage examples

### Phase 5

- file: `docs/planning/phases/phase-5/README.md`
- block files:
  - `docs/planning/phases/phase-5/block-a-local-safety-net-hardening.md`
  - `docs/planning/phases/phase-5/block-b-ci-quality-gates.md`
- purpose: harden tests and tighten architecture and analysis rules
- depends on:
  - Phases 1 to 4 outputs
- loading rule:
  - always load `docs/planning/phases/phase-5/README.md`
  - load only the active Phase 5 block file
  - load Phase 4 when HTTP adapter regressions or interface-level tests are being hardened
  - load Phase 3 when MongoDB fixtures or integration expectations are being updated
- main outputs:
  - stronger integration coverage
  - explicit unit/integration/coverage workflow
  - stricter PHPStan and deptrac gates
  - stable maintenance workflow
  - GitHub Actions PR quality checks with the fast unit suite

### Phase 6

- file: `docs/planning/phases/phase-6-packaging-and-delivery.md`
- purpose: finalize packaging and reviewer-facing delivery
- depends on:
  - all previous phases
- main outputs:
  - final Docker path
  - reviewer-ready setup instructions
  - delivery-ready repository baseline

## Recommended loading strategy by task type

### Environment and tooling work

Load:

- common context
- `docs/planning/phases/phase-0/README.md`
- the relevant `docs/planning/phases/phase-0/block-*.md` file

### Domain implementation work

Load:

- common context
- `docs/planning/phases/phase-1/README.md`
- the relevant `docs/planning/phases/phase-1/block-*.md` file
- Phase 0 only if architecture or tooling decisions must be checked

### Application orchestration work

Load:

- common context
- `docs/planning/phases/phase-2/README.md`
- the relevant `docs/planning/phases/phase-2/block-*.md` file
- Phase 1 if domain contracts are needed

### MongoDB or repository work

Load:

- common context
- `docs/planning/phases/phase-3/README.md`
- the relevant `docs/planning/phases/phase-3/block-*.md` file
- Phase 2 for ports
- Phase 1 for domain mapping rules

### Interface work

Load:

- common context
- `docs/planning/phases/phase-4/README.md`
- `docs/planning/phases/phase-4/block-a-thin-reviewer-interface.md`
- Phase 2 for use cases
- Phase 3 if persistence-backed flows are exercised

### Quality hardening work

Load:

- common context
- `docs/planning/phases/phase-5/README.md`
- the relevant Phase 5 block file
- whichever phase is being hardened

### Delivery and handoff work

Load:

- common context
- Phase 6
- Phase 0 if Docker or workflow decisions must be confirmed

## Rule for future updates

If a new task only affects one phase, update only that phase document and keep the other phase files untouched unless a dependency contract changes.

For Phase 0 specifically:

- update the relevant block file first
- update `docs/planning/phases/phase-0/README.md` only if block status, outputs, or loading guidance change
