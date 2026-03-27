# Phase 3 / Block A: Persistence Boundaries and Document Shape

## Purpose

Freeze the first MongoDB persistence shape for the `Machine` aggregate before
implementing the repository adapter.

## Depends on

- `docs/planning/phases/phase-3/README.md`
- `docs/planning/phases/phase-2/block-c-handlers-and-ports.md`
- `docs/planning/phases/phase-0/block-f-mongodb-foundation.md`

## Tasks

- [x] P3-001: define the concrete infrastructure class names for the MongoDB machine adapter
- [x] P3-002: choose the MongoDB collection name for the persisted machine aggregate
- [x] P3-003: define the root document shape for machine state persistence
- [x] P3-004: define how the fixed `default` machine identifier is stored in MongoDB
- [x] P3-005: define how product stock is represented inside the persisted document
- [x] P3-006: define how inserted coins and available change are represented inside the persisted document
- [x] P3-007: define the serialization boundary between domain objects and persistence documents
- [x] P3-008: define not-found, overwrite, and logical upsert expectations for the repository
- [x] P3-009: confirm that MongoDB-specific types remain inside infrastructure only

## Persistence decision

The Phase 3 persistence direction is now frozen around one explicit adapter:

- repository class: `MongoDBMachineRepository`
- mapper class: `MachineDocumentMapper`
- persistence artifacts: `MachineDocument` and `ProductStockDocument`
- MongoDB collection: `machines`

## Document shape

The persisted machine state now uses one document per aggregate:

- `_id`: the application-level machine identifier, currently the fixed string `default`
- `products`: a list of product-stock documents with `selector`, `name`, `priceCents`, and `quantity`
- `availableChange`: a denomination-count map
- `insertedCoins`: a denomination-count map

The persisted document does not duplicate derived values such as inserted
balance because the aggregate already derives them safely from inserted coins.

## Boundary decision

MongoDB concerns remain inside infrastructure:

- the application still depends only on `MachineRepository`
- the domain still knows nothing about MongoDB, BSON, collections, or document ids
- MongoDB-specific arrays and collection behavior are translated at the mapper or repository boundary only

Repository behavior is now explicit:

- `find()` returns `null` when no `Machine` document exists for the given id
- `save()` performs one logical replacement through MongoDB `replaceOne(..., upsert: true)`
- the repository keeps the fixed `default` identifier strategy adopted in Phase 2

## Output contract

The block is expected to leave behind:

- a frozen document shape for the `Machine` aggregate
- collection and identifier naming decisions
- explicit serialization and repository expectations

## Exit condition

The persistence shape is explicit enough to implement the MongoDB adapter
without reopening application contracts or domain boundaries.
