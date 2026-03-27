# Phase 1 / Block C: Core Machine Behaviors

## Purpose

Implement the business behaviors that make the machine usable while preserving
the invariants defined in Block A.

## Depends on

- `docs/planning/phases/phase-1/README.md`
- `docs/planning/phases/phase-1/block-a-assumptions-and-language.md`
- `docs/planning/phases/phase-1/block-b-money-and-machine-primitives.md`

## Tasks

- [x] P1-019: implement coin insertion behavior in the `Machine` aggregate
- [x] P1-020: reject unsupported coin insertion attempts
- [x] P1-021: expose inserted balance and purchase availability from the domain model
- [x] P1-022: implement product selection behavior
- [x] P1-023: reject unknown selectors and out-of-stock products
- [x] P1-024: reject purchases with insufficient inserted balance
- [x] P1-025: calculate and dispense change on successful overpayment
- [x] P1-026: reject purchases when exact change cannot be returned
- [x] P1-027: keep machine state consistent after failed purchase attempts
- [x] P1-028: implement refund behavior for all currently inserted money
- [x] P1-029: implement service setup and replenishment behavior for stock and available change

## Behavior baseline

Block C makes the `Machine` aggregate executable for the challenge-critical
flows:

- insert supported coins and expose the inserted balance
- determine whether a selector is currently purchasable
- purchase a product with exact payment or overpayment
- commit inserted coins before calculating and dispensing change
- refund the exact inserted coin multiset
- service stock and available change through an explicit administrative action

The purchase and refund flows now return explicit result objects:

- `PurchaseResult`
- `RefundResult`

## Failure handling baseline

Block C also introduces explicit domain failures for the main rejected flows:

- `ProductNotFound`
- `ProductOutOfStock`
- `InsufficientBalance`
- `ExactChangeNotAvailable`
- `PendingBalanceDuringService`
- `InvalidServiceConfiguration`

The aggregate remains immutable, so failed operations do not corrupt the
original machine state.

## Validation snapshot

Validated in Block C after implementing the behaviors:

- `make test`: successful with `28` tests and `74` assertions
- `make quality`: successful

Quality result summary:

- ECS: successful
- PHPStan: successful
- deptrac: successful with `0` violations and `0` errors
- Rector dry-run: successful

## Notes

- exact change allocation uses the committed machine coin state, including newly inserted coins
- refund returns the currently inserted coins without mutating available change
- service remains a replacement-style setup operation and still requires no pending customer balance

## Output contract

The block is expected to leave behind:

- the main vending-machine behaviors implemented in the domain
- deterministic change and refund behavior
- explicit failure handling that does not corrupt machine state

## Exit condition

The domain can express the mandatory challenge flows without relying on
Symfony, MongoDB, or application-layer orchestration.
