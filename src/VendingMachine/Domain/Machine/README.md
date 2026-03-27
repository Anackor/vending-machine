# Machine Domain Notes

This file summarizes the frozen Phase 1 assumptions for the `Machine` domain.

## Catalog

The Phase 1 catalog is fixed to:

- `water`: `Water`, `65` cents
- `juice`: `Juice`, `100` cents
- `soda`: `Soda`, `150` cents

## Money rules

- all money uses integer cents
- the supported coin set is `5`, `10`, `25`, and `100`
- the same coin set is considered returnable for refund and change

## Aggregate ownership

`Machine` is the single aggregate root for now.

It owns:

- catalog definitions
- product stock counts
- available coin counts
- the currently inserted customer coins

## Behavioral rules

- refund returns the exact inserted coin multiset
- successful purchase commits inserted coins and then dispenses exact change
- purchase fails if exact change cannot be returned
- failed operations must not corrupt machine state
- service sets stock and available coin counts and requires no pending customer balance
