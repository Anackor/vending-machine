# Phase 2 / Block C: Handlers and Ports

## Purpose

Define the repository ports and handler orchestration needed to execute the
Phase 2 use cases against the validated domain model.

## Depends on

- `docs/planning/phases/phase-2/README.md`
- `docs/planning/phases/phase-2/block-a-use-cases-and-boundaries.md`
- `docs/planning/phases/phase-2/block-b-application-contracts.md`

## Tasks

- [x] P2-020: define the repository port needed to load the current machine state
- [x] P2-021: define the repository port needed to persist the updated machine state
- [x] P2-022: decide whether the repository contract should appear atomic from the handler perspective
- [x] P2-023: define the in-memory repository test double contract for Phase 2 tests
- [x] P2-024: implement the insert-coin handler skeleton
- [x] P2-025: implement the select-product handler skeleton
- [x] P2-026: implement the refund handler skeleton
- [x] P2-027: implement the service handler skeleton
- [x] P2-028: implement the machine-state query handler if the query path was adopted
- [x] P2-029: map domain results and failures into the selected application contracts
- [x] P2-030: confirm that the defined ports are sufficient for Phase 3 persistence adapters

## Port baseline

Block C introduces the first persistence-facing application port:

- `Repository/MachineRepository.php`

The repository remains intentionally small in Phase 2:

- `find(string $machineId): ?Machine`
- `save(string $machineId, Machine $machine): void`

From the handler perspective, `save()` is treated as one logical replacement of
the aggregate. Phase 2 does not introduce a separate transaction or unit-of-work
contract because the next persistence step still targets a single aggregate and
one logical machine identifier.

## Handler baseline

The application layer is now executable through one handler per selected use
case:

- `InsertCoinHandler`
- `SelectProductHandler`
- `ReturnInsertedMoneyHandler`
- `ServiceMachineHandler`
- `GetMachineStateHandler`

Handlers now follow one explicit orchestration path:

- load the machine through `MachineRepository`
- invoke one domain behavior
- persist the updated aggregate when the use case mutates state
- translate domain results into application contracts
- translate rejected flows into `MachineOperationFailed`

## Testing baseline

Block C also introduces the in-memory repository double used by application
tests:

- `tests/VendingMachine/Application/Machine/Double/InMemoryMachineRepository.php`

This keeps Phase 2 tests focused on orchestration behavior without coupling the
application layer to MongoDB or Symfony infrastructure.

## Validation snapshot

Validated in Block C after implementing the handlers and ports:

- `make test`: successful with `106` tests and `333` assertions
- `make quality`: successful
- `make coverage`: successful
- `php bin/console about`: successful inside Docker

Coverage snapshot after Block C:

- classes: `96.88%`
- methods: `99.40%`
- lines: `99.82%`

Quality result summary:

- ECS: successful
- PHPStan: successful
- deptrac: successful with `0` violations and `0` errors
- Rector dry-run: successful

## Notes

- `MachineRepository` is sufficient for the planned single-machine MongoDB adapter in Phase 3
- failed application operations do not persist state changes
- handlers remain free of business-rule recalculation and only orchestrate domain behavior
- Block D can now focus on hardening the application-layer suite and closing Phase 2

## Output contract

The block is expected to leave behind:

- executable application handlers
- persistence-facing repository ports
- a clear translation path between domain behavior and application contracts

## Exit condition

The application layer can orchestrate the domain model through ports without
depending on infrastructure or interface details.
