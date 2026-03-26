# Phase 1: Domain Design and Core Model

## Objective

Design and implement the vending machine domain model and its business rules independently from Symfony and persistence concerns.

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

## Tasks

- confirm all business assumptions that are not fully specified in the prompt
- define value objects for money and accepted coins
- define entities or aggregates for products and machine state
- define how inserted money is tracked
- define how available change is tracked
- implement purchase behavior
- implement refund behavior
- implement service replenishment behavior
- protect invariant rules with unit tests

## Mandatory business cases

- buy with the exact amount
- request a refund before purchase
- buy and receive change
- reject unsupported coins
- reject out-of-stock products
- reject purchases when exact change is not possible
- keep machine state consistent after failed operations

## Validations

- domain code has no framework dependency
- money calculations are deterministic
- business rules are covered by unit tests
- failed operations do not corrupt machine state

## Deliverables

- documented domain assumptions
- domain model
- unit test coverage for critical rules

## Exit criteria

This phase is complete when the domain behavior is reliable, explicit, and ready to be orchestrated by the application layer.
