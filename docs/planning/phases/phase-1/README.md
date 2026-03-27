# Phase 1: Domain Design and Core Model

## Objective

Design and implement the vending machine domain model and its business rules
independently from Symfony and persistence concerns.

## Scope

- domain assumptions
- money representation in cents
- products, selectors, stock, and change
- purchase, refund, and service behavior
- unit tests for core business rules

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`
- `docs/planning/phases/phase-0/README.md`

## Canonical structure

This `README.md` is the canonical index for Phase 1.

Use it to understand:

- the overall objective and scope
- the execution order between blocks
- the status of each block
- the phase-wide validations and deliverables

Use the block files for task-level execution and block-local decisions:

- `block-a-assumptions-and-language.md`
- `block-b-money-and-machine-primitives.md`
- `block-c-core-machine-behaviors.md`
- `block-d-domain-test-suite-and-phase-gate.md`

## Planning note

Phase 1 intentionally uses fewer blocks than Phase 0.

The domain work is centered on a single `VendingMachine/Machine` core, so
splitting it too aggressively would add planning overhead without giving better
execution control.

The tasks remain atomic, but the blocks stay broader and more cohesive.

## Recommended execution order

1. Block A: assumptions and language
2. Block B: money and machine primitives
3. Block C: core machine behaviors
4. Block D: domain test suite and phase gate

## Loading rule for Phase 1 work

For any Phase 1 task, always load:

- this `README.md`
- the block file you are actively executing

Load previous Phase 1 block files only if:

- they contain frozen assumptions the current block depends on
- they contain validated outputs the current block builds on

Load Phase 0 block files only if:

- architecture or naming decisions must be checked
- workflow or tooling commands must be confirmed

## Block index

### A. Assumptions and language

- file: `docs/planning/phases/phase-1/block-a-assumptions-and-language.md`
- task ids: `P1-001` to `P1-008`
- status: complete
- outputs: domain assumptions, ubiquitous language, aggregate boundary, invariant list

### B. Money and machine primitives

- file: `docs/planning/phases/phase-1/block-b-money-and-machine-primitives.md`
- task ids: `P1-009` to `P1-018`
- status: complete
- outputs: money and selector value objects, stock and change primitives, machine aggregate skeleton, primitive unit-test baseline

### C. Core machine behaviors

- file: `docs/planning/phases/phase-1/block-c-core-machine-behaviors.md`
- task ids: `P1-019` to `P1-029`
- status: complete
- outputs: insert, select, refund, and service domain behaviors with protected invariants

### D. Domain test suite and phase gate

- file: `docs/planning/phases/phase-1/block-d-domain-test-suite-and-phase-gate.md`
- task ids: `P1-030` to `P1-039`
- status: pending
- outputs: unit coverage for mandatory business cases and phase-completion confirmation

## Mandatory business cases

- buy with the exact amount
- request a refund before purchase
- buy and receive change
- reject unsupported coins
- reject out-of-stock products
- reject purchases when exact change is not possible
- keep machine state consistent after failed operations

## Phase-wide validations

- domain code has no Symfony or MongoDB dependency
- money calculations are deterministic and use integer cents
- failed operations do not corrupt machine state
- mandatory business cases are covered by unit tests
- Phase 2 can start without reopening the core domain assumptions

## Deliverables

- documented domain assumptions
- machine aggregate and domain primitives
- implemented core machine rules
- unit test coverage for critical rules

## Exit criteria

This phase is complete when the domain behavior is reliable, explicit, and
ready to be orchestrated by the application layer.
