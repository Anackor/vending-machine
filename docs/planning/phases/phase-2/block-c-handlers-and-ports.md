# Phase 2 / Block C: Handlers and Ports

## Purpose

Define the repository ports and handler orchestration needed to execute the
Phase 2 use cases against the validated domain model.

## Depends on

- `docs/planning/phases/phase-2/README.md`
- `docs/planning/phases/phase-2/block-a-use-cases-and-boundaries.md`
- `docs/planning/phases/phase-2/block-b-application-contracts.md`

## Tasks

- [ ] P2-020: define the repository port needed to load the current machine state
- [ ] P2-021: define the repository port needed to persist the updated machine state
- [ ] P2-022: decide whether the repository contract should appear atomic from the handler perspective
- [ ] P2-023: define the in-memory repository test double contract for Phase 2 tests
- [ ] P2-024: implement the insert-coin handler skeleton
- [ ] P2-025: implement the select-product handler skeleton
- [ ] P2-026: implement the refund handler skeleton
- [ ] P2-027: implement the service handler skeleton
- [ ] P2-028: implement the machine-state query handler if the query path was adopted
- [ ] P2-029: map domain results and failures into the selected application contracts
- [ ] P2-030: confirm that the defined ports are sufficient for Phase 3 persistence adapters

## Output contract

The block is expected to leave behind:

- executable application handlers
- persistence-facing repository ports
- a clear translation path between domain behavior and application contracts

## Exit condition

The application layer can orchestrate the domain model through ports without
depending on infrastructure or interface details.
