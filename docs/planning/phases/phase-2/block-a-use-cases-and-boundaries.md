# Phase 2 / Block A: Use Cases and Boundaries

## Purpose

Freeze the application-layer responsibilities and define the use-case map that
will sit on top of the Phase 1 domain model.

## Depends on

- `docs/planning/phases/phase-2/README.md`
- `docs/planning/phases/phase-1/README.md`
- `docs/planning/phases/phase-1/block-d-domain-test-suite-and-phase-gate.md`

## Tasks

- [ ] P2-001: enumerate the primary application use cases needed for the MVP
- [ ] P2-002: decide whether the application layer exposes commands only or commands plus queries
- [ ] P2-003: decide how the current machine instance is identified across handlers and persistence
- [ ] P2-004: define what orchestration belongs in handlers versus what must stay in the domain
- [ ] P2-005: define the allowed dependency direction for the application layer
- [ ] P2-006: define the naming baseline for handlers, commands, queries, and result models
- [ ] P2-007: define the boundary where domain failures stop and application failures begin
- [ ] P2-008: confirm that the Phase 1 domain API is sufficient without reopening the core model

## Output contract

The block is expected to leave behind:

- a frozen application use-case map
- clear orchestration boundaries
- a machine identity direction for later persistence work
- explicit dependency rules for the application layer

## Exit condition

The team can start defining application contracts without ambiguity about what
the application layer is allowed to own.
