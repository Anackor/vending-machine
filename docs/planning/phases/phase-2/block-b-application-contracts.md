# Phase 2 / Block B: Application Contracts

## Purpose

Define the stable command, query, result, snapshot, and failure contracts that
future interfaces and infrastructure will consume.

## Depends on

- `docs/planning/phases/phase-2/README.md`
- `docs/planning/phases/phase-2/block-a-use-cases-and-boundaries.md`

## Tasks

- [ ] P2-009: define the input model for inserting a coin
- [ ] P2-010: define the input model for selecting a product
- [ ] P2-011: define the input model for refunding inserted money
- [ ] P2-012: define the input model for servicing the machine
- [ ] P2-013: define the input model for retrieving the current machine state if a query use case is adopted
- [ ] P2-014: define the stable machine snapshot contract shared by application results
- [ ] P2-015: define the output contract for the insert-coin use case
- [ ] P2-016: define the output contract for the select-product use case
- [ ] P2-017: define the output contract for the refund use case
- [ ] P2-018: define the output contract for the service use case
- [ ] P2-019: define the application failure model that upper layers will consume

## Output contract

The block is expected to leave behind:

- stable input contracts for all selected use cases
- stable output and snapshot contracts
- an application-facing failure model

## Exit condition

The application layer exposes contracts that are framework-agnostic and stable
enough for later Symfony and persistence adapters.
