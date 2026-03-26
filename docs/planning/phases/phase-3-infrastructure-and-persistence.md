# Phase 3: Infrastructure and Persistence

## Objective

Implement MongoDB-backed infrastructure adapters that satisfy the application ports while preserving domain boundaries.

## Scope

- MongoDB repository adapters
- persistence mapping
- serialization boundaries
- persistence integration tests

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`
- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-2-application-layer-and-ports.md`

## Tasks

- implement MongoDB adapters for the required repository ports
- define mapping between persisted documents and the domain model
- define serialization and deserialization boundaries
- validate storage of machine state, inserted money, available change, and product stock
- add integration tests for persistence-backed flows

## Validations

- repository adapters satisfy the application ports
- persisted data can be reconstructed into valid domain objects
- storage behavior is consistent across read and write operations
- persistence tests run against the Docker-based local environment

## Deliverables

- MongoDB repository implementations
- persistence mapping strategy
- integration coverage for persistence behavior

## Exit criteria

This phase is complete when the application layer can execute its persistence-dependent use cases reliably through MongoDB adapters.
