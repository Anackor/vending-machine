# Phase 0 / Block F: MongoDB Foundation

## Purpose

Decide and wire the initial MongoDB integration direction without yet implementing the full persistence layer.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-a-runtime-and-tooling.md`
- `docs/planning/phases/phase-0/block-b-docker-workspace.md`
- `docs/planning/phases/phase-0/block-c-symfony-bootstrap.md`

## Tasks

- [ ] P0-053: choose the Symfony-compatible MongoDB integration approach
- [ ] P0-054: add the PHP driver or library packages required by that approach
- [ ] P0-055: add the Symfony bundle or configuration package required by that approach, if any
- [ ] P0-056: define environment variables for MongoDB host, port, database, and credentials strategy
- [ ] P0-057: create the initial MongoDB-related Symfony configuration files
- [ ] P0-058: decide the first persistence boundary for machine state and inventory state
- [ ] P0-059: define where persistence mappers, documents, or collections will live in the infrastructure layer
- [ ] P0-060: add a minimal connectivity check that proves the application can reach MongoDB from inside Docker
- [ ] P0-061: add a minimal smoke path that writes and reads a trivial document without introducing domain coupling
- [ ] P0-062: confirm the chosen persistence direction will not force Symfony or MongoDB concerns into the domain layer

## Output contract

The block is expected to leave behind:

- a chosen integration approach
- connection defaults and configuration files
- a trivial validated MongoDB round-trip path

## Exit condition

MongoDB connectivity and a trivial round-trip work inside the local environment.
