# Phase 3 / Block A: Persistence Boundaries and Document Shape

## Purpose

Freeze the first MongoDB persistence shape for the `Machine` aggregate before
implementing the repository adapter.

## Depends on

- `docs/planning/phases/phase-3/README.md`
- `docs/planning/phases/phase-2/block-c-handlers-and-ports.md`
- `docs/planning/phases/phase-0/block-f-mongodb-foundation.md`

## Tasks

- [ ] P3-001: define the concrete infrastructure class names for the MongoDB machine adapter
- [ ] P3-002: choose the MongoDB collection name for the persisted machine aggregate
- [ ] P3-003: define the root document shape for machine state persistence
- [ ] P3-004: define how the fixed `default` machine identifier is stored in MongoDB
- [ ] P3-005: define how product stock is represented inside the persisted document
- [ ] P3-006: define how inserted coins and available change are represented inside the persisted document
- [ ] P3-007: define the serialization boundary between domain objects and persistence documents
- [ ] P3-008: define not-found, overwrite, and logical upsert expectations for the repository
- [ ] P3-009: confirm that MongoDB-specific types remain inside infrastructure only

## Output contract

The block is expected to leave behind:

- a frozen document shape for the `Machine` aggregate
- collection and identifier naming decisions
- explicit serialization and repository expectations

## Exit condition

The persistence shape is explicit enough to implement the MongoDB adapter
without reopening application contracts or domain boundaries.
