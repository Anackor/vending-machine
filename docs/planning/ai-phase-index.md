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

- file: `docs/planning/phases/phase-1-domain-design-and-core-model.md`
- purpose: design and implement the domain model and business rules
- depends on:
  - Phase 0 outputs
- main outputs:
  - domain assumptions
  - aggregates and value objects
  - unit tests for core rules

### Phase 2

- file: `docs/planning/phases/phase-2-application-layer-and-ports.md`
- purpose: implement use cases and persistence-facing ports
- depends on:
  - Phase 1 outputs
- main outputs:
  - application services
  - ports
  - stable input and output models

### Phase 3

- file: `docs/planning/phases/phase-3-infrastructure-and-persistence.md`
- purpose: implement MongoDB adapters and persistence integration
- depends on:
  - Phase 0 outputs
  - Phase 2 ports
- main outputs:
  - MongoDB repositories
  - persistence mapping
  - persistence integration tests

### Phase 4

- file: `docs/planning/phases/phase-4-initial-interface.md`
- purpose: expose the backend through a thin Symfony-driven interface
- depends on:
  - Phase 2 outputs
  - Phase 3 outputs
- main outputs:
  - console or minimal HTTP interface
  - documented usage examples

### Phase 5

- file: `docs/planning/phases/phase-5-test-hardening-and-quality-gates.md`
- purpose: harden tests and tighten architecture and analysis rules
- depends on:
  - Phases 1 to 4 outputs
- main outputs:
  - stronger integration coverage
  - stricter PHPStan and deptrac gates
  - stable maintenance workflow

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
- Phase 1
- Phase 0 only if architecture or tooling decisions must be checked

### Application orchestration work

Load:

- common context
- Phase 2
- Phase 1 if domain contracts are needed

### MongoDB or repository work

Load:

- common context
- Phase 3
- Phase 2 for ports
- Phase 1 for domain mapping rules

### Interface work

Load:

- common context
- Phase 4
- Phase 2 for use cases
- Phase 3 if persistence-backed flows are exercised

### Quality hardening work

Load:

- common context
- Phase 5
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
