# Phase 1 / Block A: Assumptions and Language

## Purpose

Freeze the business assumptions and ubiquitous language that the domain model
will rely on before implementing the first core classes.

## Depends on

- `docs/planning/phases/phase-1/README.md`
- `docs/README.md`
- `docs/planning/phases/phase-0/block-d-architecture-and-namespace.md`
- `docs/planning/phases/phase-0/block-e-ddd-naming.md`

## Tasks

- [x] P1-001: restate the explicit vending-machine rules from the challenge in domain language
- [x] P1-002: resolve the business ambiguities that the prompt leaves unspecified
- [x] P1-003: freeze the ubiquitous language for machine, product, selector, change, refund, and service
- [x] P1-004: freeze the decision that all money calculations use integer cents
- [x] P1-005: freeze the accepted coin set and the unsupported-coin rejection rule
- [x] P1-006: freeze the initial catalog definition for Water, Juice, and Soda with selectors and prices
- [x] P1-007: define the `Machine` aggregate boundary and the state it owns
- [x] P1-008: define the invariant list and failure rules the domain must protect

## Restated domain rules

For Phase 1, the challenge is interpreted as the following domain behavior:

- a machine accepts customer coin insertion
- a customer may request a refund of all currently inserted money
- a customer may select one of the vendable catalog products
- a successful purchase vends one product and may also return change
- a service operation configures the machine stock and available change
- the machine must keep track of product availability, available change, and currently inserted money

## Frozen assumptions

The prompt leaves several operational details unspecified. Phase 1 freezes them
as follows:

- all money is represented in integer cents
- the supported and returnable coin denominations are `5`, `10`, `25`, and `100` cents
- the omission of `1` from the prompt's "valid responses" list is treated as an inconsistency, not as a business rule
- the machine tracks the exact multiset of inserted coins, not only the inserted total
- `refund` returns the exact inserted coin multiset and clears the current customer balance
- a successful purchase commits inserted coins into the machine cash and then dispenses exact change from the resulting available coin inventory
- a purchase fails if exact change cannot be made for the required amount
- the product catalog for Phase 1 is fixed to `Water`, `Juice`, and `Soda` with selectors `water`, `juice`, and `soda`
- prices are fixed for Phase 1 at `65`, `100`, and `150` cents and are not modified by service operations
- service configures stock counts and available coin counts explicitly; it does not alter the catalog or prices
- service is only valid when no customer balance is currently pending
- the domain models a single active customer interaction at a time

## Ubiquitous language

Use the following language consistently in code and tests:

- `Machine`: the aggregate root that owns the operational state
- `Product`: the vendable item definition identified by a selector and a price
- `Selector`: the stable identifier used to request a product
- `Coin`: a supported denomination accepted and returned by the machine
- `Inserted coins`: the exact customer coins currently pending in the machine
- `Available change`: the machine-controlled reserve of coins that can be dispensed
- `Purchase`: the attempt to vend a selected product using the current inserted balance
- `Refund`: the return of all currently inserted coins without vending a product
- `Service`: the administrative operation that sets stock and available change counts

Avoid mixing these terms with broader or storage-oriented alternatives such as
`cashbox`, `inventory document`, `transaction record`, or `payment`.

## Catalog baseline

The initial catalog is frozen as:

- `water`: `Water`, price `65`
- `juice`: `Juice`, price `100`
- `soda`: `Soda`, price `150`

## Aggregate boundary

Phase 1 uses a single aggregate root: `Machine`.

`Machine` owns:

- the fixed catalog definitions needed for selection and price lookup
- the current stock count for each product
- the machine's available coin reserve
- the currently inserted customer coins

Phase 1 does not introduce separate aggregates for catalog, inventory, cash, or
customer session state. The challenge does not yet justify that split.

## Invariants and failure rules

The domain model must protect these invariants:

- every monetary value is a non-negative integer number of cents
- only supported coin denominations may appear in inserted coins or available change
- selectors are unique inside the catalog
- product prices are positive and fixed to the frozen catalog values
- product stock counts are never negative
- available coin counts are never negative
- inserted coin counts are never negative
- unsupported coin insertion is rejected without mutating machine state
- unknown selector selection is rejected without mutating machine state
- out-of-stock selection is rejected without mutating machine state
- insufficient-balance purchase attempts are rejected without mutating machine state
- impossible-exact-change purchase attempts are rejected without mutating machine state
- a successful purchase decrements stock for the selected product exactly once
- a successful purchase clears the inserted customer balance
- a successful purchase updates available change consistently with inserted coins and dispensed change
- a refund clears the inserted customer balance and does not alter stock or catalog state
- a service operation may change stock and available change only when no inserted customer balance exists

## Notes

- the frozen assumptions are intentionally simple and challenge-driven; they should not be generalized prematurely
- these assumptions are also summarized close to the domain code in `src/VendingMachine/Domain/Machine/README.md`

## Output contract

The block is expected to leave behind:

- written domain assumptions
- stable ubiquitous language
- a clear aggregate boundary
- a list of invariants to protect in code and tests

## Exit condition

The rest of Phase 1 can proceed without reopening core business assumptions
during implementation.
