# Phase 0 / Block E: DDD Naming Baseline

## Purpose

Freeze the naming conventions that will keep the domain and application code consistent once implementation begins.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-d-architecture-and-namespace.md`

## Tasks

- [x] P0-045: choose the first module name inside the vending machine bounded context
- [x] P0-046: define naming conventions for aggregates
- [x] P0-047: define naming conventions for entities
- [x] P0-048: define naming conventions for value objects
- [x] P0-049: define naming conventions for repositories and repository interfaces
- [x] P0-050: define naming conventions for domain services
- [x] P0-051: define naming conventions for application services or use-case handlers
- [x] P0-052: record the naming conventions in a short architecture note or README section

## Module decision

The first business module inside `VendingMachine` is `Machine`.

Rationale:

- YAGNI: the challenge describes one cohesive stateful machine, not several modules that must evolve independently today
- KISS: one module keeps the first aggregate, use cases, and persistence boundary easy to locate
- clean code: `Machine` is the clearest name for the operational state that holds inserted money, available change, item stock, and service operations
- SOLID: we keep a small, focused module boundary now and defer splits until the domain pushes us there

Modules that stay intentionally postponed:

- `Inventory`
- `Cash`
- `Catalog`
- `Transactions`

If one of these areas becomes large enough later, it can become a second module
inside the same bounded context without invalidating the current baseline.

## Naming conventions

The naming baseline for the core is:

- aggregates use singular business nouns without the `Aggregate` suffix
- the first aggregate should be named `Machine`
- entities use singular business nouns without the `Entity` suffix
- value objects use singular nouns or noun phrases without the `ValueObject` suffix
- repository contracts are named after the aggregate, for example `MachineRepository`
- repository contracts do not use the `Interface` suffix
- infrastructure repository adapters add the technology prefix, for example `MongoDBMachineRepository`
- domain services are allowed only when behavior does not fit an aggregate or value object, and they should use capability names such as `ChangeCalculator`
- application orchestration classes should be modeled as use-case handlers named with an imperative verb phrase plus `Handler`
- handler inputs and outputs, when needed, should reuse the use-case name with `Command` and `Result`
- Symfony-facing adapters should stay under `Infrastructure/Symfony/` and use framework-standard subnamespaces such as `Command`, `Controller`, `EventSubscriber`, or `Messenger`
- the `Handler` suffix is reserved for application use cases, not for Symfony controllers or console commands

## Naming note location

The living naming note for the core now lives in:

- `src/VendingMachine/README.md`

This keeps the conventions close to the code tree that will use them.

## Implementation notes

- the source tree now shows the first business module under `Domain`, `Application`, and MongoDB persistence
- `Infrastructure/Symfony` stays adapter-oriented for now because there is no concrete delivery adapter to group yet
- the naming baseline intentionally avoids extra suffixes such as `Aggregate`, `Entity`, `ValueObject`, or `Interface`

## Validation snapshot

Validated in Phase 0 after the naming decision:

- the source tree now expresses the first business module explicitly
- the first class names for Phase 1 can be added without reopening naming debates
- the module split remains intentionally minimal and aligned with the challenge scope
- `php bin/console about`: successful

## Output contract

The block is expected to leave behind:

- a bounded-context naming decision
- a first module name for the vending machine core
- class-type naming rules for domain and application layers

## Exit condition

Future phases can add classes without reopening naming and module-structure debates.
