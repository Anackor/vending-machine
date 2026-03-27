# Phase 3 / Block C: Integration Fixtures and Tests

## Purpose

Protect the persistence behavior with repeatable MongoDB-backed integration
tests and a clear fixture lifecycle.

## Depends on

- `docs/planning/phases/phase-3/README.md`
- `docs/planning/phases/phase-3/block-b-mongodb-repository-and-mapper.md`
- `docs/planning/phases/phase-0/block-h-developer-workflow.md`

## Tasks

- [x] P3-022: define the folder and naming baseline for MongoDB-backed integration tests
- [x] P3-023: decide the fixture reset strategy used between persistence integration runs
- [x] P3-024: add the helper or base class that clears the machine collection predictably
- [x] P3-025: add the helper or fixture path that seeds the default machine when a test needs it
- [x] P3-026: add an integration test for repository lookup when the machine document does not exist
- [x] P3-027: add an integration test for saving and then reloading the machine aggregate
- [x] P3-028: add an integration test for updating an existing persisted machine document
- [x] P3-029: add an integration test that proves persisted data reconstructs valid domain invariants
- [x] P3-030: add at least one application-level integration test that uses a handler on top of the MongoDB repository
- [x] P3-031: document the fixture lifecycle and local execution path for persistence integration tests

## Test baseline

The MongoDB-backed persistence suite now lives under:

- `tests/VendingMachine/Infrastructure/Persistence/MongoDB/Machine/Document/`
- `tests/VendingMachine/Infrastructure/Persistence/MongoDB/Machine/Mapper/`
- `tests/VendingMachine/Infrastructure/Persistence/MongoDB/Machine/Integration/`

The suite covers:

- direct document validation
- mapper round-trip behavior
- repository not-found behavior
- repository save and reload round-trips
- repository overwrite behavior
- persisted-domain reconstruction
- one application-level handler flow on top of the MongoDB repository

## Fixture lifecycle

Phase 3 uses one explicit fixture path:

- `DefaultMachineFixture` builds either a default domain machine or a raw MongoDB document shape
- `MongoDBIntegrationTestCase` clears the `machines` collection before and after each test
- repository-backed seeding uses `save('default', ...)`
- raw document seeding is used only when the test wants to exercise reconstruction from persisted arrays directly

## Local execution path

The current local execution path is intentionally simple:

- `make bootstrap` to ensure Docker, Symfony, and MongoDB are ready
- `make test` to run the full suite, including MongoDB-backed integration tests
- `make coverage` to measure the current protected scope, now including MongoDB persistence code

Phase 3 does not yet split unit and integration suites into separate commands.
That workflow hardening remains intentionally deferred to Phase 5.

## Output contract

The block is expected to leave behind:

- repeatable MongoDB fixture helpers
- persistence integration coverage
- documented local commands for the integration path

## Exit condition

Persistence behavior can be exercised repeatedly against MongoDB without manual
state cleanup between tests.
