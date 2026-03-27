# Phase 1 / Block A: Assumptions and Language

## Purpose

Freeze the business assumptions and ubiquitous language that the domain model
will rely on before implementing the first core classes.

## Depends on

- `docs/planning/phases/phase-1/README.md`
- `docs/challenges/backend/README.md`
- `docs/planning/phases/phase-0/block-d-architecture-and-namespace.md`
- `docs/planning/phases/phase-0/block-e-ddd-naming.md`

## Tasks

- [ ] P1-001: restate the explicit vending-machine rules from the challenge in domain language
- [ ] P1-002: resolve the business ambiguities that the prompt leaves unspecified
- [ ] P1-003: freeze the ubiquitous language for machine, product, selector, change, refund, and service
- [ ] P1-004: freeze the decision that all money calculations use integer cents
- [ ] P1-005: freeze the accepted coin set and the unsupported-coin rejection rule
- [ ] P1-006: freeze the initial catalog definition for Water, Juice, and Soda with selectors and prices
- [ ] P1-007: define the `Machine` aggregate boundary and the state it owns
- [ ] P1-008: define the invariant list and failure rules the domain must protect

## Output contract

The block is expected to leave behind:

- written domain assumptions
- stable ubiquitous language
- a clear aggregate boundary
- a list of invariants to protect in code and tests

## Exit condition

The rest of Phase 1 can proceed without reopening core business assumptions
during implementation.
