# Phase 1 / Block B: Money and Machine Primitives

## Purpose

Implement the first domain primitives and the `Machine` aggregate skeleton that
will hold the vending-machine state.

## Depends on

- `docs/planning/phases/phase-1/README.md`
- `docs/planning/phases/phase-1/block-a-assumptions-and-language.md`

## Tasks

- [x] P1-009: create the domain value object that represents money in cents
- [x] P1-010: create the domain representation for supported coin denominations
- [x] P1-011: create the value object that represents product selectors
- [x] P1-012: create the domain model that represents a product definition and price
- [x] P1-013: create the domain model that represents product stock
- [x] P1-014: create the domain model that represents available change
- [x] P1-015: create the domain model that represents currently inserted money
- [x] P1-016: create the `Machine` aggregate root skeleton
- [x] P1-017: compose the `Machine` aggregate from the selected primitives
- [x] P1-018: protect primitive construction invariants from invalid state

## Primitive baseline

Block B introduces the first executable `Machine` domain primitives:

- `Money`
- `Coin`
- `Selector`
- `Product`
- `ProductStock`
- `CoinInventory`
- `AvailableChange`
- `InsertedCoins`
- `Machine`

The aggregate remains intentionally small at this stage. It stores composed
state and exposes read-only access to the first domain primitives, but it does
not yet implement purchase, refund, or service behavior. Those remain in Block
C.

## Testing baseline

Because Phase 1 now requires executable tests for domain changes, Block B also
introduces the first PHPUnit baseline:

- `phpunit/phpunit` as a development dependency
- `bin/phpunit`, `phpunit.dist.xml`, and `tests/bootstrap.php`
- focused primitive tests under `tests/VendingMachine/Domain/Machine/`

This keeps `make test` real from this point forward instead of leaving it as a
placeholder.

## Validation snapshot

Validated in Block B after implementing the primitives:

- `make test`: successful
- `make quality`: successful

Quality result summary:

- ECS: successful
- PHPStan: successful
- deptrac: successful with `0` violations and `0` errors
- Rector dry-run: successful

## Notes

- `CoinInventory` is an internal reusable primitive for coin-count state
- `AvailableChange` and `InsertedCoins` stay explicit wrappers so the domain language remains clear
- mandatory purchase and refund behaviors are intentionally deferred to Block C

## Output contract

The block is expected to leave behind:

- the first domain value objects and entities
- an explicit `Machine` aggregate skeleton
- state primitives that model money, stock, and change without framework coupling
- initial unit coverage for primitive invariants

## Exit condition

The domain has enough structure to implement machine behaviors without inventing
new core concepts ad hoc.
