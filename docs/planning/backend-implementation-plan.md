# Backend Implementation Plan

This document is now the entry point for the implementation plan.

The plan has been split into atomic phase documents so each implementation step can be reviewed and executed with minimal context.

## Planning structure

- `docs/planning/ai-phase-index.md`: AI-oriented index with the recommended loading strategy
- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-a-runtime-and-tooling.md`
- `docs/planning/phases/phase-0/block-b-docker-workspace.md`
- `docs/planning/phases/phase-0/block-c-symfony-bootstrap.md`
- `docs/planning/phases/phase-0/block-d-architecture-and-namespace.md`
- `docs/planning/phases/phase-0/block-e-ddd-naming.md`
- `docs/planning/phases/phase-0/block-f-mongodb-foundation.md`
- `docs/planning/phases/phase-0/block-g-quality-toolchain.md`
- `docs/planning/phases/phase-0/block-h-developer-workflow.md`
- `docs/planning/phases/phase-0/block-i-final-phase-gate.md`
- `docs/planning/phases/phase-1/README.md`
- `docs/planning/phases/phase-1/block-a-assumptions-and-language.md`
- `docs/planning/phases/phase-1/block-b-money-and-machine-primitives.md`
- `docs/planning/phases/phase-1/block-c-core-machine-behaviors.md`
- `docs/planning/phases/phase-1/block-d-domain-test-suite-and-phase-gate.md`
- `docs/planning/phases/phase-2/README.md`
- `docs/planning/phases/phase-2/block-a-use-cases-and-boundaries.md`
- `docs/planning/phases/phase-2/block-b-application-contracts.md`
- `docs/planning/phases/phase-2/block-c-handlers-and-ports.md`
- `docs/planning/phases/phase-2/block-d-application-tests-and-phase-gate.md`
- `docs/planning/phases/phase-3/README.md`
- `docs/planning/phases/phase-3/block-a-persistence-boundaries-and-document-shape.md`
- `docs/planning/phases/phase-3/block-b-mongodb-repository-and-mapper.md`
- `docs/planning/phases/phase-3/block-c-integration-fixtures-and-tests.md`
- `docs/planning/phases/phase-3/block-d-persistence-phase-gate.md`
- `docs/planning/phases/phase-4/README.md`
- `docs/planning/phases/phase-4/block-a-thin-reviewer-interface.md`
- `docs/planning/phases/phase-5/README.md`
- `docs/planning/phases/phase-5/block-a-local-safety-net-hardening.md`
- `docs/planning/phases/phase-5/block-b-ci-quality-gates.md`
- `docs/planning/phases/phase-6-packaging-and-delivery.md`
- `docs/planning/phases/phase-7/README.md`
- `docs/planning/phases/phase-7/block-a-frontend-foundation.md`
- `docs/planning/phases/phase-7/block-b-reviewer-flows.md`
- `docs/planning/phases/phase-7/block-c-ui-tests-docker-and-handoff.md`

## Global objective

Deliver a production-minded backend foundation for the vending machine challenge using the latest Symfony version, MongoDB, Hexagonal Architecture, DDD, SOLID principles, and a strong local developer platform from day one.

## Execution order

1. Phase 0: platform bootstrap
2. Phase 1: domain design and core model
3. Phase 2: application layer and ports
4. Phase 3: infrastructure and persistence
5. Phase 4: initial interface
6. Phase 5: test hardening and quality gates
7. Phase 6: packaging and delivery
8. Optional Phase 7: reviewer UI monorepo follow-up

## Always-relevant context

Before executing any phase, the minimum common context should be:

- the root `README.md`
- `docs/README.md`
- the target phase document

For Phase 0 specifically:

- always load `docs/planning/phases/phase-0/README.md`
- load only the block file you are actively executing
- load previous Phase 0 block files only when their decisions or outputs are required

For Phase 1 specifically:

- always load `docs/planning/phases/phase-1/README.md`
- load only the block file you are actively executing
- load previous Phase 1 block files only when their assumptions or outputs are required

For Phase 2 specifically:

- always load `docs/planning/phases/phase-2/README.md`
- load only the block file you are actively executing
- load previous Phase 2 block files only when their contracts or outputs are required

For Phase 3 specifically:

- always load `docs/planning/phases/phase-3/README.md`
- load only the block file you are actively executing
- load previous Phase 3 block files only when their persistence decisions or outputs are required

Additional documents should only be loaded when the phase explicitly depends on them.

## MVP definition

The backend MVP is done when:

- the development platform is reproducible through Docker
- the Symfony project structure reflects Hexagonal Architecture clearly
- the domain logic satisfies the challenge requirements
- the core flows are covered by tests
- MongoDB persists the required machine state
- quality tools protect code quality and architectural boundaries
- the repository is understandable and runnable by a reviewer
- an optional reviewer UI can be added without reopening the backend architecture

## Optional follow-up work

- add a public HTTP API on top of the same application layer
- add more advanced logging and metrics
- extend CI beyond pull-request quality checks if needed
- add deployment-oriented Docker optimizations
- add an optional reviewer-facing frontend UI inside the same repository
