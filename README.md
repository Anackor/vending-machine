# vending-machine

This repository is intended to host a PHP backend solution for a vending machine technical challenge.

## Goal

Build a small but well-structured backend that models a vending machine, its inventory, its available change, and the purchase flow from coin insertion to product delivery and change return.

## Current scope

The first delivery is backend-only and should focus on:

- correct vending machine behavior
- clear domain modeling
- automated tests
- reproducible execution with Docker
- concise documentation for reviewers

## Recommended implementation direction

- Model money as integer cents, not floating point values.
- Keep the business rules independent from the delivery mechanism.
- Start with a domain-first design and add a thin interface later.
- Treat service operations as explicit administrative actions.
- Prefer a complete and polished MVP over a broad but shallow implementation.

## Documentation map

- `docs/challenges/backend/README.md`: backend challenge brief, extracted requirements, assumptions, and design guidance
- `docs/planning/backend-implementation-plan.md`: implementation roadmap for the backend
- `docs/development/conventions.md`: project conventions, class placement rules, and tool responsibilities
- `docs/development/ai-development-guide.md`: short development guide for future AI-assisted changes

## Delivery expectations

The exercise explicitly points toward:

- PHP as the implementation language
- Docker support as a strong plus
- automated tests as an expected quality signal
- a public-ready repository with a clear README and no company references

## Repository status

The repository now includes the Dockerized Symfony baseline, MongoDB
foundation, quality toolchain, and the documented developer workflow for
Phase 0. Phase 1 is complete, so the core domain model is now implemented,
tested, and validated. Phase 2 completed the application contracts and
orchestration layer. Phase 3 added the MongoDB-backed persistence adapter,
mapping strategy, and persistence integration tests. Phase 4 is now complete,
so the project also exposes a thin reviewer-facing HTTP JSON interface on top
of the persisted application layer. The next step is Phase 5, where tests and
quality gates can be hardened further.

## Local prerequisites

- Docker Desktop with Docker Compose support
- GNU Make
- a terminal capable of running `docker compose` and `make`

Local PHP, Composer, Symfony CLI, and MongoDB installations are not required.

## First run

The preferred bootstrap flow is:

```bash
make bootstrap
```

This command:

- builds the application image
- starts the Docker environment
- installs Composer dependencies inside the `app` container
- runs the setup checks
- seeds the default machine if it does not exist yet

After `make bootstrap`, the reviewer-facing HTTP interface is available at:

```text
http://localhost:8000
```

## Daily workflow

Common commands:

- `make up`
- `make down`
- `make status`
- `make shell`
- `make console cmd="about"`
- `make mongodb-smoke`
- `make lint`
- `make analyse`
- `make quality`
- `make test`
- `make coverage`
- `make coverage-html`
- `make rector-fix`
- `make ecs-fix`

## Command mapping

The `Makefile` is the preferred local entry point.

It wraps the main containerized commands:

- `make install` -> `docker compose exec -T app composer install`
- `make setup` -> `docker compose exec -T app composer run project:setup`
- `make lint` -> `docker compose exec -T app composer run lint`
- `make analyse` -> `docker compose exec -T app composer run analyse`
- `make test` -> `docker compose exec -T app composer run test`
- `make coverage` -> `docker compose exec -T app composer run test:coverage`
- `make coverage-html` -> `docker compose exec -T app composer run test:coverage:html`
- `make rector-fix` -> `docker compose exec -T app composer run analyse:rector:fix`
- `make ecs-fix` -> `docker compose exec -T app composer run lint:ecs:fix`
- `make console cmd="..."` -> `docker compose exec -T app php bin/console ...`

## HTTP interface

The first reviewer-facing interface is a thin HTTP JSON layer served by the
`app` container on `localhost:8000`.

Available endpoints:

- `GET /api/machine`
- `POST /api/machine/insert-coin`
- `POST /api/machine/select-product`
- `POST /api/machine/return-coin`
- `POST /api/machine/service`

Example flows:

```bash
curl http://localhost:8000/api/machine
```

```bash
curl -X POST http://localhost:8000/api/machine/insert-coin \
  -H "Content-Type: application/json" \
  -d '{"coinCents":100}'
```

```bash
curl -X POST http://localhost:8000/api/machine/select-product \
  -H "Content-Type: application/json" \
  -d '{"selector":"water"}'
```

```bash
curl -X POST http://localhost:8000/api/machine/return-coin
```

```bash
curl -X POST http://localhost:8000/api/machine/service \
  -H "Content-Type: application/json" \
  -d '{
    "productQuantities":{"water":10,"juice":8,"soda":5},
    "availableChangeCounts":{"5":20,"10":20,"25":20,"100":10}
  }'
```

## Development docs

When changing the core or adding a feature, start here:

- `docs/development/conventions.md`
- `docs/development/ai-development-guide.md`
- `src/VendingMachine/README.md`
