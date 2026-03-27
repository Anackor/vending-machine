# Phase 2: Application Layer and Ports

## Objective

Expose the domain model through application use cases and stable ports so
interfaces and infrastructure can depend on contracts instead of coupling
directly to domain internals.

## Scope

- application use cases and orchestration rules
- input and output contracts
- repository and infrastructure-facing ports
- application-facing failure translation
- tests for handler orchestration and contract stability

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`
- `docs/planning/phases/phase-1/README.md`

## Canonical structure

This `README.md` is the canonical index for Phase 2.

Use it to understand:

- the overall objective and scope
- the execution order between blocks
- the status of each block
- the phase-wide validations and deliverables

Use the block files for task-level execution and block-local decisions:

- `block-a-use-cases-and-boundaries.md`
- `block-b-application-contracts.md`
- `block-c-handlers-and-ports.md`
- `block-d-application-tests-and-phase-gate.md`

## Planning note

Phase 2 stays compact because the work is centered on one cohesive concern:
turning the validated `Machine` domain into callable application contracts.

The tasks remain atomic, but the blocks stay broad enough to preserve the
through-line between use cases, contracts, ports, and tests.

## Recommended execution order

1. Block A: use cases and boundaries
2. Block B: application contracts
3. Block C: handlers and ports
4. Block D: application tests and phase gate

## Loading rule for Phase 2 work

For any Phase 2 task, always load:

- this `README.md`
- the block file you are actively executing

Load previous Phase 2 block files only if:

- they contain frozen contracts the current block depends on
- they contain validated outputs the current block builds on

Load Phase 1 block files only if:

- domain assumptions or result semantics must be checked
- aggregate boundaries or invariants must be confirmed

Load Phase 0 block files only if:

- architecture or workflow decisions must be checked
- tooling or command conventions must be confirmed

## Block index

### A. Use cases and boundaries

- file: `docs/planning/phases/phase-2/block-a-use-cases-and-boundaries.md`
- task ids: `P2-001` to `P2-008`
- status: pending
- outputs: application use-case map, handler boundaries, machine identity direction, dependency rules

### B. Application contracts

- file: `docs/planning/phases/phase-2/block-b-application-contracts.md`
- task ids: `P2-009` to `P2-019`
- status: pending
- outputs: command/query inputs, stable outputs, snapshot contract, application failure model

### C. Handlers and ports

- file: `docs/planning/phases/phase-2/block-c-handlers-and-ports.md`
- task ids: `P2-020` to `P2-030`
- status: pending
- outputs: repository ports, handler orchestration baseline, contract-to-domain mapping direction

### D. Application tests and phase gate

- file: `docs/planning/phases/phase-2/block-d-application-tests-and-phase-gate.md`
- task ids: `P2-031` to `P2-040`
- status: pending
- outputs: application-layer unit coverage, validated orchestration baseline, Phase 3 handoff confirmation

## Mandatory use cases for Phase 2

- insert a supported coin through an application contract
- select a product through an application contract
- refund inserted money through an application contract
- service the machine through an application contract
- expose a machine-state contract suitable for future interfaces

## Phase-wide validations

- application code depends on domain contracts, not Symfony or MongoDB details
- handlers orchestrate domain objects without embedding business rules that belong in the domain
- ports are sufficient for future persistence adapters
- failures can be understood outside the domain layer
- Phase 3 and Phase 4 can build on stable application contracts

## Deliverables

- application use cases or handlers
- repository and infrastructure-facing ports
- stable command, query, and result contracts
- application-layer tests for orchestration behavior

## Exit criteria

This phase is complete when upper layers can invoke vending-machine behavior
through application contracts without direct knowledge of internal domain
implementation details.
