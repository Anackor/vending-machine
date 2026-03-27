# Phase 3 / Block B: MongoDB Repository and Mapper

## Purpose

Implement the MongoDB-backed repository adapter and the mapping path required to
persist and reconstruct the `Machine` aggregate.

## Depends on

- `docs/planning/phases/phase-3/README.md`
- `docs/planning/phases/phase-3/block-a-persistence-boundaries-and-document-shape.md`
- `docs/planning/phases/phase-2/block-c-handlers-and-ports.md`

## Tasks

- [ ] P3-010: create the MongoDB persistence artifact that represents the stored machine document
- [ ] P3-011: create the mapper path from `Machine` domain objects to the persistence document
- [ ] P3-012: create the mapper path from persisted documents back to the `Machine` aggregate
- [ ] P3-013: create the `MongoDBMachineRepository` skeleton that implements `MachineRepository`
- [ ] P3-014: inject the required MongoDB database or collection dependency into the repository
- [ ] P3-015: implement repository lookup for the configured machine identifier
- [ ] P3-016: implement repository save with logical replacement or upsert semantics
- [ ] P3-017: keep the repository result shape free of MongoDB-specific types at the application boundary
- [ ] P3-018: place the new persistence classes under the agreed infrastructure path
- [ ] P3-019: wire the repository into the Symfony container as the `MachineRepository` implementation
- [ ] P3-020: validate that application handlers can consume the adapter without any contract change
- [ ] P3-021: confirm the adapter behavior is aligned with the single-machine `default` identifier strategy

## Output contract

The block is expected to leave behind:

- a working MongoDB repository adapter
- document mappers in both directions
- Symfony wiring that exposes the adapter through the application port

## Exit condition

The infrastructure layer can store and load the `Machine` aggregate through the
existing application repository contract.
