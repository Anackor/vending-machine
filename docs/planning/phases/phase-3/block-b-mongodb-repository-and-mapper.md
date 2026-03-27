# Phase 3 / Block B: MongoDB Repository and Mapper

## Purpose

Implement the MongoDB-backed repository adapter and the mapping path required to
persist and reconstruct the `Machine` aggregate.

## Depends on

- `docs/planning/phases/phase-3/README.md`
- `docs/planning/phases/phase-3/block-a-persistence-boundaries-and-document-shape.md`
- `docs/planning/phases/phase-2/block-c-handlers-and-ports.md`

## Tasks

- [x] P3-010: create the MongoDB persistence artifact that represents the stored machine document
- [x] P3-011: create the mapper path from `Machine` domain objects to the persistence document
- [x] P3-012: create the mapper path from persisted documents back to the `Machine` aggregate
- [x] P3-013: create the `MongoDBMachineRepository` skeleton that implements `MachineRepository`
- [x] P3-014: inject the required MongoDB database or collection dependency into the repository
- [x] P3-015: implement repository lookup for the configured machine identifier
- [x] P3-016: implement repository save with logical replacement or upsert semantics
- [x] P3-017: keep the repository result shape free of MongoDB-specific types at the application boundary
- [x] P3-018: place the new persistence classes under the agreed infrastructure path
- [x] P3-019: wire the repository into the Symfony container as the `MachineRepository` implementation
- [x] P3-020: validate that application handlers can consume the adapter without any contract change
- [x] P3-021: confirm the adapter behavior is aligned with the single-machine `default` identifier strategy

## Implementation baseline

Phase 3 now implements the first real persistence adapter under:

- `src/VendingMachine/Infrastructure/Persistence/MongoDB/Machine/MongoDBMachineRepository.php`
- `src/VendingMachine/Infrastructure/Persistence/MongoDB/Machine/Mapper/MachineDocumentMapper.php`
- `src/VendingMachine/Infrastructure/Persistence/MongoDB/Machine/Document/MachineDocument.php`
- `src/VendingMachine/Infrastructure/Persistence/MongoDB/Machine/Document/ProductStockDocument.php`

## Wiring decision

Symfony now resolves the application port through MongoDB infrastructure wiring:

- `config/services/mongodb.yaml` keeps `MongoDB\Client` and `MongoDB\Database`
- `MachineDocumentMapper` is registered as an infrastructure service
- `MachineRepository` is now aliased to `MongoDBMachineRepository`

This means application handlers can keep depending on `MachineRepository`
without learning anything about MongoDB, document shape, or collection access.

## Mapping direction

The mapper baseline is now explicit:

- domain to document: `Machine` becomes one `MachineDocument`
- document to domain: persisted arrays are validated and reconstructed into a valid `Machine`
- persistence encoding: MongoDB receives primitive arrays only

This keeps BSON or collection details out of the domain and keeps the
repository contract stable for Phase 4.

## Output contract

The block is expected to leave behind:

- a working MongoDB repository adapter
- document mappers in both directions
- Symfony wiring that exposes the adapter through the application port

## Exit condition

The infrastructure layer can store and load the `Machine` aggregate through the
existing application repository contract.
