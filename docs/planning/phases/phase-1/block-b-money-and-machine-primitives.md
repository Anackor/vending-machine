# Phase 1 / Block B: Money and Machine Primitives

## Purpose

Implement the first domain primitives and the `Machine` aggregate skeleton that
will hold the vending-machine state.

## Depends on

- `docs/planning/phases/phase-1/README.md`
- `docs/planning/phases/phase-1/block-a-assumptions-and-language.md`

## Tasks

- [ ] P1-009: create the domain value object that represents money in cents
- [ ] P1-010: create the domain representation for supported coin denominations
- [ ] P1-011: create the value object that represents product selectors
- [ ] P1-012: create the domain model that represents a product definition and price
- [ ] P1-013: create the domain model that represents product stock
- [ ] P1-014: create the domain model that represents available change
- [ ] P1-015: create the domain model that represents currently inserted money
- [ ] P1-016: create the `Machine` aggregate root skeleton
- [ ] P1-017: compose the `Machine` aggregate from the selected primitives
- [ ] P1-018: protect primitive construction invariants from invalid state

## Output contract

The block is expected to leave behind:

- the first domain value objects and entities
- an explicit `Machine` aggregate skeleton
- state primitives that model money, stock, and change without framework coupling

## Exit condition

The domain has enough structure to implement machine behaviors without inventing
new core concepts ad hoc.
