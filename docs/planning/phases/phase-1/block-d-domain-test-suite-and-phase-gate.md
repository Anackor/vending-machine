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

- [ ] P1-030: add unit tests for buying with the exact amount
- [ ] P1-031: add unit tests for refunding before purchase
- [ ] P1-032: add unit tests for buying and receiving change
- [ ] P1-033: add unit tests for rejecting unsupported coins
- [ ] P1-034: add unit tests for rejecting unknown selectors and out-of-stock products
- [ ] P1-035: add unit tests for rejecting insufficient balance and impossible exact change
- [ ] P1-036: add unit tests for service setup and replenishment behavior
- [ ] P1-037: run the Phase 1 domain unit suite successfully
- [ ] P1-038: run static analysis and architecture checks successfully against the new domain code
- [ ] P1-039: confirm that Phase 2 can start without reopening domain assumptions or model boundaries

## Output contract

The block is expected to leave behind:

- unit tests that protect the mandatory business cases
- a validated domain baseline
- explicit confirmation that the application layer can build on the model

## Exit condition

The domain model is trustworthy enough to be orchestrated through application
use cases without first revisiting its core rules.
