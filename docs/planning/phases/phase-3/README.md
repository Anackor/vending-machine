# Phase 3: Infrastructure and Persistence

## Objective

Implement MongoDB-backed infrastructure adapters that satisfy the application
ports while preserving domain and application boundaries.

## Scope

- MongoDB repository adapters
- persistence document shape
- mapping between documents and the domain model
- MongoDB-backed integration fixtures
- persistence integration tests

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`
- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-1/README.md`
- `docs/planning/phases/phase-2/README.md`

## Canonical structure

This `README.md` is the canonical index for Phase 3.

Use it to understand:

- the overall objective and scope
- the execution order between blocks
- the status of each block
- the phase-wide validations and deliverables

Use the block files for task-level execution and block-local decisions:

- `block-a-persistence-boundaries-and-document-shape.md`
- `block-b-mongodb-repository-and-mapper.md`
- `block-c-integration-fixtures-and-tests.md`
- `block-d-persistence-phase-gate.md`

## Planning note

Phase 3 stays compact because the work is still centered on one persistence
concern: storing and reconstructing the `Machine` aggregate without leaking
MongoDB details outside infrastructure.

The tasks remain atomic, but the blocks stay broad enough to preserve the
through-line between document shape, repository behavior, integration fixtures,
and the final persistence gate.

## Recommended execution order

1. Block A: persistence boundaries and document shape
2. Block B: MongoDB repository and mapper
3. Block C: integration fixtures and tests
4. Block D: persistence phase gate

## Loading rule for Phase 3 work

For any Phase 3 task, always load:

- this `README.md`
- the block file you are actively executing

Load previous Phase 3 block files only if:

- they contain frozen persistence contracts the current block depends on
- they contain validated outputs the current block builds on

Load Phase 2 block files only if:

- repository-port expectations or application contracts must be checked
- handler orchestration outputs must be verified against persistence behavior

Load Phase 1 block files only if:

- domain invariants or aggregate reconstruction rules must be checked
- mapping decisions must be verified against the model

Load Phase 0 block files only if:

- MongoDB wiring, container behavior, or tooling commands must be confirmed

## Block index

### A. Persistence boundaries and document shape

- file: `docs/planning/phases/phase-3/block-a-persistence-boundaries-and-document-shape.md`
- task ids: `P3-001` to `P3-009`
- status: pending
- outputs: frozen persistence shape, collection naming, identifier direction, serialization rules

### B. MongoDB repository and mapper

- file: `docs/planning/phases/phase-3/block-b-mongodb-repository-and-mapper.md`
- task ids: `P3-010` to `P3-021`
- status: pending
- outputs: MongoDB repository adapter, document mappers, Symfony service wiring

### C. Integration fixtures and tests

- file: `docs/planning/phases/phase-3/block-c-integration-fixtures-and-tests.md`
- task ids: `P3-022` to `P3-031`
- status: pending
- outputs: repeatable fixture helpers, repository integration tests, persistence-backed application checks

### D. Persistence phase gate

- file: `docs/planning/phases/phase-3/block-d-persistence-phase-gate.md`
- task ids: `P3-032` to `P3-040`
- status: pending
- outputs: validated persistence baseline, stable Phase 4 handoff, confirmed Phase 5 hardening path

## Mandatory persistence behaviors for Phase 3

- save the default machine through `MachineRepository`
- load the default machine through `MachineRepository`
- reconstruct a valid `Machine` aggregate from persisted data
- preserve inserted money, available change, and product stock across round-trips
- keep MongoDB details inside infrastructure only

## Phase-wide validations

- MongoDB adapters satisfy the application ports without changing their contracts
- persisted documents can be reconstructed into valid domain objects
- repository behavior is consistent across read and write operations
- MongoDB fixtures can be reset predictably between integration runs
- Phase 4 can consume the application layer without learning MongoDB details

## Deliverables

- MongoDB repository implementation
- persistence document and mapper strategy
- repeatable MongoDB fixture baseline
- persistence integration coverage

## Exit criteria

This phase is complete when the application layer can execute its
persistence-dependent use cases reliably through MongoDB adapters.
