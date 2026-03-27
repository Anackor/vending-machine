# Phase 2 / Block A: Use Cases and Boundaries

## Purpose

Freeze the application-layer responsibilities and define the use-case map that
will sit on top of the Phase 1 domain model.

## Depends on

- `docs/planning/phases/phase-2/README.md`
- `docs/planning/phases/phase-1/README.md`
- `docs/planning/phases/phase-1/block-d-domain-test-suite-and-phase-gate.md`

## Tasks

- [x] P2-001: enumerate the primary application use cases needed for the MVP
- [x] P2-002: decide whether the application layer exposes commands only or commands plus queries
- [x] P2-003: decide how the current machine instance is identified across handlers and persistence
- [x] P2-004: define what orchestration belongs in handlers versus what must stay in the domain
- [x] P2-005: define the allowed dependency direction for the application layer
- [x] P2-006: define the naming baseline for handlers, commands, queries, and result models
- [x] P2-007: define the boundary where domain failures stop and application failures begin
- [x] P2-008: confirm that the Phase 1 domain API is sufficient without reopening the core model

## Frozen use-case map

Phase 2 will expose five application use cases for the MVP:

- `InsertCoin`
- `SelectProduct`
- `ReturnInsertedMoney`
- `ServiceMachine`
- `GetMachineState`

The first four are commands. `GetMachineState` is the only query introduced at
this stage so future interfaces can render the current machine state without
reaching into domain internals.

## Boundary decisions

Handlers own orchestration, not business rules.

That means the application layer is responsible for:

- loading the current machine through a repository port
- invoking the correct domain method
- persisting the updated machine when the operation changes state
- translating domain results into stable application results
- translating domain exceptions into application-facing failures

That means the application layer is not responsible for:

- recalculating accepted-coin rules
- recalculating change or stock invariants
- embedding Symfony or MongoDB details into handlers

## Dependency direction

The allowed direction for Phase 2 is:

- `Application -> Domain`
- `Infrastructure -> Application`
- `Infrastructure -> Domain` only where mapping or persistence reconstruction requires it

The application layer must not depend directly on:

- Symfony framework code
- MongoDB client or document code
- infrastructure adapters

## Naming baseline

Phase 2 will use:

- `Command` for write-side inputs
- `Query` for read-side inputs
- `Result` for successful outputs
- `Handler` for use-case orchestration classes
- `MachineSnapshot` for the shared machine-state read model

Examples frozen for the MVP:

- `InsertCoinCommand`, `InsertCoinResult`, `InsertCoinHandler`
- `SelectProductCommand`, `SelectProductResult`, `SelectProductHandler`
- `ReturnInsertedMoneyCommand`, `ReturnInsertedMoneyResult`, `ReturnInsertedMoneyHandler`
- `ServiceMachineCommand`, `ServiceMachineResult`, `ServiceMachineHandler`
- `GetMachineStateQuery`, `GetMachineStateResult`, `GetMachineStateHandler`

## Machine identity direction

For this challenge, the application layer will treat the vending machine as a
single logical instance.

When an explicit repository key is required, the fixed identifier will be
`default`.

This keeps persistence practical without forcing a `MachineId` concept into the
domain before there is real pressure for it.

## Domain sufficiency check

Phase 1 is sufficient for Phase 2 without reopening the core model.

The current domain already gives the application layer what it needs:

- explicit mutation entry points on `Machine`
- explicit result objects for purchase and refund
- typed domain failures for the main rejected flows
- stable state primitives for money, change, and stock

No additional bounded contexts, aggregates, or domain services are required at
this point.

## Notes

- the application layer will expose both commands and one query
- domain exceptions stop at the application boundary, even if their exact replacements are defined in Block B
- the first repository port should still be named after the aggregate: `MachineRepository`

## Output contract

The block is expected to leave behind:

- a frozen application use-case map
- clear orchestration boundaries
- a machine identity direction for later persistence work
- explicit dependency rules for the application layer

## Exit condition

The team can start defining application contracts without ambiguity about what
the application layer is allowed to own.
