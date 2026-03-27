# Phase 3 / Block C: Integration Fixtures and Tests

## Purpose

Protect the persistence behavior with repeatable MongoDB-backed integration
tests and a clear fixture lifecycle.

## Depends on

- `docs/planning/phases/phase-3/README.md`
- `docs/planning/phases/phase-3/block-b-mongodb-repository-and-mapper.md`
- `docs/planning/phases/phase-0/block-h-developer-workflow.md`

## Tasks

- [ ] P3-022: define the folder and naming baseline for MongoDB-backed integration tests
- [ ] P3-023: decide the fixture reset strategy used between persistence integration runs
- [ ] P3-024: add the helper or base class that clears the machine collection predictably
- [ ] P3-025: add the helper or fixture path that seeds the default machine when a test needs it
- [ ] P3-026: add an integration test for repository lookup when the machine document does not exist
- [ ] P3-027: add an integration test for saving and then reloading the machine aggregate
- [ ] P3-028: add an integration test for updating an existing persisted machine document
- [ ] P3-029: add an integration test that proves persisted data reconstructs valid domain invariants
- [ ] P3-030: add at least one application-level integration test that uses a handler on top of the MongoDB repository
- [ ] P3-031: document the fixture lifecycle and local execution path for persistence integration tests

## Output contract

The block is expected to leave behind:

- repeatable MongoDB fixture helpers
- persistence integration coverage
- documented local commands for the integration path

## Exit condition

Persistence behavior can be exercised repeatedly against MongoDB without manual
state cleanup between tests.
