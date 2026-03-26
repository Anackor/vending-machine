# Phase 0: Platform Bootstrap

## Objective

Create the engineering foundation that allows backend development to start immediately with Symfony, Docker, MongoDB, architectural boundaries, and quality tooling already in place.

## Scope

- latest stable Symfony version
- Docker-based local environment
- MongoDB container and connectivity
- Hexagonal Architecture baseline
- DDD-oriented project structure
- SOLID-driven design constraints
- PHPStan, deptrac, Rector, and ECS
- Makefile-based command entry point for recurring local operations
- repeatable local developer workflow

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`

## Canonical structure

This `README.md` is the canonical index for Phase 0.

Use it to understand:

- the overall objective and scope
- the execution order between blocks
- the status of each block
- the phase-wide validations and deliverables

Use the block files for task-level execution and block-local decisions:

- `block-a-runtime-and-tooling.md`
- `block-b-docker-workspace.md`
- `block-c-symfony-bootstrap.md`
- `block-d-architecture-and-namespace.md`
- `block-e-ddd-naming.md`
- `block-f-mongodb-foundation.md`
- `block-g-quality-toolchain.md`
- `block-h-developer-workflow.md`
- `block-i-final-phase-gate.md`

## Recommended execution order

1. Block A: runtime and tooling decisions
2. Block B: Docker workspace bootstrap
3. Block C: Symfony project bootstrap
4. Block D: architecture and namespace baseline
5. Block E: DDD naming baseline
6. Block F: MongoDB foundation
7. Block G: quality toolchain bootstrap
8. Block H: developer workflow and documentation
9. Block I: final phase gate

## Loading rule for Phase 0 work

For any Phase 0 task, always load:

- this `README.md`
- the block file you are actively executing

Load previous block files only if:

- they contain frozen decisions the current block depends on
- they contain validated outputs the current block builds on

## Block index

### A. Runtime and tooling decisions

- file: `docs/planning/phases/phase-0/block-a-runtime-and-tooling.md`
- task ids: `P0-001` to `P0-010`
- status: complete
- outputs: runtime versions, extension list, local tooling strategy

### B. Docker workspace bootstrap

- file: `docs/planning/phases/phase-0/block-b-docker-workspace.md`
- task ids: `P0-011` to `P0-024`
- status: complete
- outputs: PHP application image, Docker Compose stack, network, volumes, validated container baseline

### C. Symfony project bootstrap

- file: `docs/planning/phases/phase-0/block-c-symfony-bootstrap.md`
- task ids: `P0-025` to `P0-034`
- status: complete
- outputs: Symfony skeleton, environment defaults, runnable console baseline

### D. Architecture and namespace baseline

- file: `docs/planning/phases/phase-0/block-d-architecture-and-namespace.md`
- task ids: `P0-035` to `P0-044`
- status: pending
- outputs: namespace layout, source tree boundaries, initial dependency rules

### E. DDD naming baseline

- file: `docs/planning/phases/phase-0/block-e-ddd-naming.md`
- task ids: `P0-045` to `P0-052`
- status: pending
- outputs: bounded-context naming, module naming, class naming conventions

### F. MongoDB foundation

- file: `docs/planning/phases/phase-0/block-f-mongodb-foundation.md`
- task ids: `P0-053` to `P0-062`
- status: pending
- outputs: MongoDB integration direction, connection config, persistence baseline

### G. Quality toolchain bootstrap

- file: `docs/planning/phases/phase-0/block-g-quality-toolchain.md`
- task ids: `P0-063` to `P0-078`
- status: pending
- outputs: PHPStan, deptrac, Rector, and ECS executable from the local environment

### H. Developer workflow and documentation

- file: `docs/planning/phases/phase-0/block-h-developer-workflow.md`
- task ids: `P0-079` to `P0-091`
- status: pending
- outputs: Composer scripts, Makefile strategy, contributor-facing runbook

### I. Final phase gate

- file: `docs/planning/phases/phase-0/block-i-final-phase-gate.md`
- task ids: `P0-092` to `P0-096`
- status: pending
- outputs: full validation pass and phase-completion confirmation

## Phase-wide validations

### Stack validation

- Symfony installs on the selected PHP version
- required PHP extensions are available in the application container

### Docker validation

- application container starts successfully
- MongoDB container starts successfully
- services can communicate over the configured network
- source changes are visible during development

### Symfony validation

- the Symfony application boots without errors
- environment variables resolve correctly
- Symfony console commands run from the container

### Architecture validation

- the source tree reflects Hexagonal Architecture clearly
- the domain has no dependency on Symfony or MongoDB code
- deptrac detects forbidden dependencies when rules are violated

### MongoDB validation

- the application can connect to MongoDB
- a simple smoke test can write and read data
- the chosen persistence direction supports the intended domain model

### Quality validation

- PHPStan runs successfully
- deptrac runs successfully
- Rector runs in dry-run mode
- ECS can check formatting successfully

### Workflow validation

- the project can be started with documented commands only
- the preferred `Makefile` commands are documented and runnable
- the lint and analysis workflow is documented and runnable
- a new developer can reach a working local environment without hidden steps

## Deliverables

- Symfony project bootstrapped on the latest stable version
- Docker environment for the application and MongoDB
- initial Hexagonal and DDD-oriented project structure
- configured PHPStan, deptrac, Rector, and ECS
- Makefile-based local command entry point
- documented local developer workflow

## Exit criteria

This phase is complete when the platform is stable enough to begin domain implementation without revisiting setup decisions immediately.
