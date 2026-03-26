# Phase 0 / Block F: MongoDB Foundation

## Purpose

Decide and wire the initial MongoDB integration direction without yet implementing the full persistence layer.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-a-runtime-and-tooling.md`
- `docs/planning/phases/phase-0/block-b-docker-workspace.md`
- `docs/planning/phases/phase-0/block-c-symfony-bootstrap.md`

## Tasks

- [x] P0-053: choose the Symfony-compatible MongoDB integration approach
- [x] P0-054: add the PHP driver or library packages required by that approach
- [x] P0-055: add the Symfony bundle or configuration package required by that approach, if any
- [x] P0-056: define environment variables for MongoDB host, port, database, and credentials strategy
- [x] P0-057: create the initial MongoDB-related Symfony configuration files
- [x] P0-058: decide the first persistence boundary for machine state and inventory state
- [x] P0-059: define where persistence mappers, documents, or collections will live in the infrastructure layer
- [x] P0-060: add a minimal connectivity check that proves the application can reach MongoDB from inside Docker
- [x] P0-061: add a minimal smoke path that writes and reads a trivial document without introducing domain coupling
- [x] P0-062: confirm the chosen persistence direction will not force Symfony or MongoDB concerns into the domain layer

## Integration decision

Phase 0 uses the official MongoDB PHP library, `mongodb/mongodb`, on top of the
already-installed `ext-mongodb` PHP extension.

The selected approach is:

- direct Symfony service wiring for `MongoDB\Client` and `MongoDB\Database`
- no ODM
- no extra Symfony bundle in Phase 0

Rationale:

- YAGNI: the challenge does not need an ODM baseline before the domain model exists
- KISS: the application only needs connectivity and a trivial round-trip in this phase
- clean architecture: persistence stays explicit in infrastructure instead of leaking framework mapping concerns into the core

## Environment and configuration

Committed defaults now exist for:

- `MONGODB_HOST`
- `MONGODB_PORT`
- `MONGODB_DATABASE`
- `MONGODB_USERNAME`
- `MONGODB_PASSWORD`
- `MONGODB_AUTH_SOURCE`
- `MONGODB_URI`

Credential strategy for Phase 0:

- the local Docker MongoDB service runs without authentication
- credentials stay modeled as env vars so the connection strategy can evolve later without touching domain code
- if authentication is introduced later, `MONGODB_URI` should be overridden in `.env.local` or a deployment-specific environment

Symfony MongoDB wiring now lives in:

- `config/services/mongodb.yaml`

## First persistence boundary

The first persistence boundary is the `Machine` module.

Decision:

- the first persisted state will be the machine state as one persistence boundary
- inventory state belongs to that same boundary for now
- Phase 0 does not split inventory and machine state into separate persistence modules

This keeps the first repository and document model aligned with the current
single-module domain baseline.

## Infrastructure placement

MongoDB persistence classes will live under:

- `src/VendingMachine/Infrastructure/Persistence/MongoDB/Machine/Document/`
- `src/VendingMachine/Infrastructure/Persistence/MongoDB/Machine/Mapper/`

The smoke command lives separately under:

- `src/VendingMachine/Infrastructure/Symfony/Command/`

This keeps persistence-specific code out of Symfony adapters and out of the
domain layer.

## Validation path

The connectivity and round-trip validation path is:

- `app:mongodb:smoke`

The command:

- resolves the configured `MongoDB\Database` service
- inserts a trivial document into a smoke collection
- reads that document back
- removes it again before finishing

## Validation snapshot

Validated in Phase 0 after the MongoDB foundation work:

- `php bin/console about`: successful
- `php bin/console app:mongodb:smoke`: successful
- MongoDB connectivity works from the Symfony container
- a trivial write and read path works without touching domain code
- the chosen direction keeps MongoDB concerns in infrastructure only

## Output contract

The block is expected to leave behind:

- a chosen integration approach
- connection defaults and configuration files
- a trivial validated MongoDB round-trip path

## Exit condition

MongoDB connectivity and a trivial round-trip work inside the local environment.
