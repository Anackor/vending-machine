# Machine Application Notes

This file summarizes the frozen Phase 2 Block A decisions for the application
layer on top of the `Machine` domain module.

## Primary use cases

The MVP application layer will expose:

- `InsertCoin`
- `SelectProduct`
- `ReturnInsertedMoney`
- `ServiceMachine`
- `GetMachineState`

## Application responsibilities

Handlers are responsible for:

- loading the current machine through an application port
- invoking the appropriate domain behavior
- persisting the updated machine state when the operation changes it
- translating domain results into stable application results
- translating domain failures into application-facing failures

Handlers are not responsible for:

- recalculating business rules that already belong to the domain
- deciding accepted coins, change rules, or stock invariants
- dealing with Symfony or MongoDB implementation details directly

## Machine identity direction

Phase 2 keeps the machine as a single logical instance for the challenge.

The application layer will use one fixed machine identifier, `default`, when a
repository contract needs an explicit key.

This keeps persistence practical without forcing a `MachineId` concept into the
domain before the model asks for it.

## Naming baseline

- commands use the `Command` suffix
- queries use the `Query` suffix
- successful outputs use the `Result` suffix
- handlers keep the `Handler` suffix
- the shared read model should be named `MachineSnapshot`

Preferred examples:

- `InsertCoinCommand`, `InsertCoinResult`, `InsertCoinHandler`
- `SelectProductCommand`, `SelectProductResult`, `SelectProductHandler`
- `ReturnInsertedMoneyCommand`, `ReturnInsertedMoneyResult`, `ReturnInsertedMoneyHandler`
- `ServiceMachineCommand`, `ServiceMachineResult`, `ServiceMachineHandler`
- `GetMachineStateQuery`, `GetMachineStateResult`, `GetMachineStateHandler`

## Failure boundary

Phase 2 will keep domain exceptions inside the application layer boundary.

Handlers may catch domain failures and re-express them as application-facing
exceptions or failure contracts, but upper layers should not depend directly on
the domain exception classes.
