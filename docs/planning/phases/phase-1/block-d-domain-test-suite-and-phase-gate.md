# Phase 1 / Block D: Domain Test Suite and Phase Gate

## Purpose

Protect the core rules with focused unit tests and validate that the domain is
ready to hand off to the application layer.

## Depends on

- `docs/planning/phases/phase-1/README.md`
- `docs/planning/phases/phase-1/block-c-core-machine-behaviors.md`
- `docs/planning/phases/phase-0/block-g-quality-toolchain.md`
- `docs/planning/phases/phase-0/block-h-developer-workflow.md`

## Tasks

- [x] P1-030: add unit tests for buying with the exact amount
- [x] P1-031: add unit tests for refunding before purchase
- [x] P1-032: add unit tests for buying and receiving change
- [x] P1-033: add unit tests for rejecting unsupported coins
- [x] P1-034: add unit tests for rejecting unknown selectors and out-of-stock products
- [x] P1-035: add unit tests for rejecting insufficient balance and impossible exact change
- [x] P1-036: add unit tests for service setup and replenishment behavior
- [x] P1-037: run the Phase 1 domain unit suite successfully
- [x] P1-038: run static analysis and architecture checks successfully against the new domain code
- [x] P1-039: confirm that Phase 2 can start without reopening domain assumptions or model boundaries

## Test-suite baseline

By the end of Phase 1, the domain test suite protects the mandatory challenge
scenarios and the main aggregate invariants through focused unit tests under
`tests/VendingMachine/Domain/Machine/`.

The suite now covers:

- exact-amount purchase
- refund before purchase
- purchase with returned change
- unsupported coin rejection
- unknown selector and out-of-stock rejection
- insufficient balance and impossible exact change rejection
- service setup and replenishment behavior
- service configuration validation
- immutability of the aggregate after failed or alternative flows

## Phase gate decision

Phase 2 can start without reopening the core domain assumptions or the model
boundaries.

The rationale is:

- `Machine` remains the single aggregate root and still owns the full machine state
- the money, stock, change, and service rules are explicit and executable
- the public domain API is already stable enough to be orchestrated by application use cases
- no Symfony or MongoDB concern leaked into the domain layer during Phase 1

## Validation snapshot

Validated at the end of Phase 1:

- `make test`: successful with `47` tests and `127` assertions
- `make quality`: successful
- `make coverage`: successful

Quality result summary:

- ECS: successful
- PHPStan: successful
- deptrac: successful with `0` violations and `0` errors
- Rector dry-run: successful

Coverage summary for the unit-test scope:

- classes: `90.91%`
- methods: `98.82%`
- lines: `99.59%`

## Notes

- Block D mainly hardens and validates the suite built across Blocks B and C rather than introducing new domain concepts
- deptrac still reports `12` uncovered elements, but the Phase 1 gate is clean because there are no violations or errors
- the next step is Phase 2, where the application layer can orchestrate the validated domain model

## Output contract

The block is expected to leave behind:

- unit tests that protect the mandatory business cases
- a validated domain baseline
- explicit confirmation that the application layer can build on the model

## Exit condition

The domain model is trustworthy enough to be orchestrated through application
use cases without first revisiting its core rules.
