# Phase 1 / Block C: Core Machine Behaviors

## Purpose

Implement the business behaviors that make the machine usable while preserving
the invariants defined in Block A.

## Depends on

- `docs/planning/phases/phase-1/README.md`
- `docs/planning/phases/phase-1/block-a-assumptions-and-language.md`
- `docs/planning/phases/phase-1/block-b-money-and-machine-primitives.md`

## Tasks

- [ ] P1-019: implement coin insertion behavior in the `Machine` aggregate
- [ ] P1-020: reject unsupported coin insertion attempts
- [ ] P1-021: expose inserted balance and purchase availability from the domain model
- [ ] P1-022: implement product selection behavior
- [ ] P1-023: reject unknown selectors and out-of-stock products
- [ ] P1-024: reject purchases with insufficient inserted balance
- [ ] P1-025: calculate and dispense change on successful overpayment
- [ ] P1-026: reject purchases when exact change cannot be returned
- [ ] P1-027: keep machine state consistent after failed purchase attempts
- [ ] P1-028: implement refund behavior for all currently inserted money
- [ ] P1-029: implement service setup and replenishment behavior for stock and available change

## Output contract

The block is expected to leave behind:

- the main vending-machine behaviors implemented in the domain
- deterministic change and refund behavior
- explicit failure handling that does not corrupt machine state

## Exit condition

The domain can express the mandatory challenge flows without relying on
Symfony, MongoDB, or application-layer orchestration.
