# Phase 0 / Block B: Docker Workspace Bootstrap

## Purpose

Create and validate the Docker-based local environment that will host Symfony and MongoDB work during the rest of the project.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-a-runtime-and-tooling.md`

## Tasks

- [x] P0-011: create the Docker asset directory structure for the application image
- [x] P0-012: create the PHP application Dockerfile with the selected PHP version
- [x] P0-013: install the required system packages in the application image
- [x] P0-014: install and enable the required PHP extensions in the application image
- [x] P0-015: install Composer in the application image
- [x] P0-016: define the container working directory used by the application service
- [x] P0-017: create `docker-compose.yml` with an application service
- [x] P0-018: add a MongoDB service to `docker-compose.yml`
- [x] P0-019: define the shared network used by the application and database services
- [x] P0-020: define the source-code bind mount for local development
- [x] P0-021: define the MongoDB data volume for persistent local data
- [x] P0-022: decide whether a separate Composer cache volume is needed for developer speed
- [x] P0-023: define the default startup command for the application container
- [x] P0-024: start the Docker environment and confirm both services reach a healthy running state

## Implementation notes

- Docker assets live under `docker/php/` with a dedicated PHP config override in `docker/php/conf.d/app.ini`
- the application container is built from `php:8.4-cli-bookworm` and includes Composer plus the required Phase 0 extensions
- the application service uses `/app` as its working directory and keeps the container alive with `sleep infinity` for console-driven development
- the repository root is bind-mounted into `/app`
- MongoDB state is stored in the named volume `mongo-data`
- Composer cache is stored in the named volume `composer-cache`
- both services share the `platform` bridge network

## Validation snapshot

Validated on 2026-03-26:

- `docker compose config`: valid
- `docker compose build app`: successful
- `docker compose up -d`: `app` and `mongo` healthy
- required PHP extensions loaded in `app`
- app-to-Mongo TCP connectivity confirmed
- bind-mount visibility confirmed inside `/app`

## Output contract

Subsequent blocks can assume:

- a working `app` container with Composer installed
- a working `mongo` container
- a bind-mounted repository at `/app`
- a named Composer cache volume

## Exit condition

`docker compose up` boots the app and MongoDB reliably from a clean clone.
