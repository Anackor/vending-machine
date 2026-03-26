# VendingMachine Core

## First module

The first business module is `Machine`.

It owns the operational state required by the challenge:

- inserted money
- available change
- vendable items
- service updates

Do not split this into `Inventory`, `Cash`, `Catalog`, or `Transactions`
until the model proves that a second module reduces complexity instead of
adding it.

## Baseline structure

The current baseline for the core is:

- `Domain/Machine/`
- `Application/Machine/`
- `Infrastructure/Persistence/MongoDB/Machine/`

`Infrastructure/Symfony/` stays organized by adapter type first and will only
mirror business modules when a concrete adapter needs that split.

## Naming rules

### Aggregates

- use a singular business noun
- do not use the `Aggregate` suffix
- the first aggregate should be named `Machine`

### Entities

- use a singular business noun
- do not use the `Entity` suffix
- keep entity names tied to the language of the machine, not the storage model

### Value objects

- use a singular noun or noun phrase that expresses meaning or measurement
- do not use the `ValueObject` suffix
- prefer names such as `Money`, `Coin`, `Selector`, or `Quantity`

### Repository contracts

- name repository contracts after the aggregate they load and save
- do not use the `Interface` suffix
- the baseline repository contract name is `MachineRepository`
- concrete infrastructure adapters should add the technology prefix, for example
  `MongoDBMachineRepository`

### Domain services

- introduce them only when behavior does not belong inside an aggregate or a
  value object
- prefer explicit capability names such as `ChangeCalculator` or
  `AcceptedCoinPolicy`
- avoid generic names ending in `Service` unless no stronger domain term exists

### Application handlers

- use one class per use case
- name handlers with an imperative verb phrase plus the `Handler` suffix
- prefer names such as `InsertCoinHandler`, `ReturnInsertedMoneyHandler`,
  `SelectProductHandler`, and `ServiceMachineHandler`
- when input and output models are needed, pair handlers with `Command` and
  `Result` classes that reuse the same use-case name

## Symfony alignment

- keep `App\Kernel` as the only Symfony bootstrap class under `App\`
- keep PSR-4 namespaces and folders mirrored one-to-one
- inside `Infrastructure/Symfony/`, group classes by Symfony adapter type first,
  using names such as `Command`, `Controller`, `EventSubscriber`, or `Messenger`
- reserve the `Handler` suffix for application use cases, not for Symfony
  controllers or console commands
