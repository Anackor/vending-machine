# Phase 2 / Block B: Application Contracts

## Purpose

Define the stable command, query, result, snapshot, and failure contracts that
future interfaces and infrastructure will consume.

## Depends on

- `docs/planning/phases/phase-2/README.md`
- `docs/planning/phases/phase-2/block-a-use-cases-and-boundaries.md`

## Tasks

- [x] P2-009: define the input model for inserting a coin
- [x] P2-010: define the input model for selecting a product
- [x] P2-011: define the input model for refunding inserted money
- [x] P2-012: define the input model for servicing the machine
- [x] P2-013: define the input model for retrieving the current machine state if a query use case is adopted
- [x] P2-014: define the stable machine snapshot contract shared by application results
- [x] P2-015: define the output contract for the insert-coin use case
- [x] P2-016: define the output contract for the select-product use case
- [x] P2-017: define the output contract for the refund use case
- [x] P2-018: define the output contract for the service use case
- [x] P2-019: define the application failure model that upper layers will consume

## Contract baseline

Block B freezes the first framework-agnostic application contracts for the
`Machine` module:

- commands for `InsertCoin`, `SelectProduct`, `ReturnInsertedMoney`, and `ServiceMachine`
- a query contract for `GetMachineState`
- a shared `MachineSnapshot` plus `ProductSnapshot`
- one explicit `Result` type per use case

The contracts normalize caller-facing identifiers and reject invalid shapes
early so future handlers and Symfony adapters can rely on stable inputs.

## Failure boundary baseline

Upper layers will consume an application-facing failure model instead of raw
domain exceptions:

- `MachineFailureCode`
- `MachineFailure`
- `MachineOperationFailed`

This keeps the domain failure vocabulary translatable without coupling future
interfaces directly to the domain exception classes.

## Validation snapshot

Validated in Block B after implementing the contracts:

- `make test`: successful with `87` tests and `236` assertions
- `make quality`: successful
- `make coverage`: successful

Coverage snapshot after Block B:

- classes: `96.00%`
- methods: `99.32%`
- lines: `99.76%`

Quality result summary:

- ECS: successful
- PHPStan: successful
- deptrac: successful with `0` violations and `0` errors
- Rector dry-run: successful

## Notes

- `machineId` remains normalized to the single-instance identifier `default`
- application contracts intentionally carry primitive values, not domain objects
- the next block can now focus on handler orchestration and repository ports instead of redefining inputs or outputs

## Output contract

The block is expected to leave behind:

- stable input contracts for all selected use cases
- stable output and snapshot contracts
- an application-facing failure model

## Exit condition

The application layer exposes contracts that are framework-agnostic and stable
enough for later Symfony and persistence adapters.
