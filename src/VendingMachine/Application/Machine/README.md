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

The reviewer-facing baseline still starts from one logical machine named
`default`, but the application boundary now carries that key as a `MachineId`
value object.

Commands, queries, snapshots, factories, and the repository port should use
`MachineId` instead of repeating string normalization. HTTP and MongoDB adapters
convert it back to a string at their own serialization/persistence boundary.

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

## Contract baseline

Phase 2 Block B now freezes the first application-facing contract set:

- `Command/InsertCoinCommand.php`
- `Command/SelectProductCommand.php`
- `Command/ReturnInsertedMoneyCommand.php`
- `Command/ServiceMachineCommand.php`
- `Query/GetMachineStateQuery.php`
- `Result/MachineSnapshot.php`
- `Result/ProductSnapshot.php`
- one `Result` class per use case

These contracts normalize external string identifiers through `MachineId` and
continue exposing primitive transport payloads from adapters.

## Failure boundary

Phase 2 will keep domain exceptions inside the application layer boundary.

Handlers may catch domain failures and re-express them as application-facing
exceptions or failure contracts, but upper layers should not depend directly on
the domain exception classes.

The initial application failure contract set is:

- `Failure/MachineFailureCode.php`
- `Failure/MachineFailure.php`
- `Exception/MachineOperationFailed.php`

## Handler and port baseline

Phase 2 Block C adds the first executable application orchestration layer:

- `Repository/MachineRepository.php`
- `Handler/InsertCoinHandler.php`
- `Handler/SelectProductHandler.php`
- `Handler/ReturnInsertedMoneyHandler.php`
- `Handler/ServiceMachineHandler.php`
- `Handler/GetMachineStateHandler.php`

The repository contract stays intentionally compact with `find()` and `save()`.
For Phase 2, `save()` should be treated as one logical aggregate replacement
from the handler perspective.

Handlers are expected to:

- load the machine through the repository port
- invoke one domain behavior only once
- save the updated aggregate when state changes
- translate domain results into application contracts
- translate rejected flows into `MachineOperationFailed`
