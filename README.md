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
of the persisted application layer. Phase 5 is now complete as well, so the
local safety net is explicit and pull requests run the agreed GitHub Actions
quality baseline. Phase 6 is now complete as well, so the repository is ready
for reviewer handoff with one documented Docker execution path.

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
- resets the default machine to the documented reviewer baseline

After `make bootstrap`, the reviewer-facing HTTP interface is available at:

```text
http://localhost:8000
```

No extra local database setup is required for reviewers. The bootstrap flow
already creates the local environment and resets the default machine to the
documented baseline.

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
- `make review`
- `make test`
- `make test-unit`
- `make test-integration`
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
- `make test-unit` -> `docker compose exec -T app composer run test:unit`
- `make test-integration` -> `docker compose exec -T app composer run test:integration`
- `make coverage` -> `docker compose exec -T app composer run test:coverage`
- `make coverage-html` -> `docker compose exec -T app composer run test:coverage:html`
- `make rector-fix` -> `docker compose exec -T app composer run analyse:rector:fix`
- `make ecs-fix` -> `docker compose exec -T app composer run lint:ecs:fix`
- `make console cmd="..."` -> `docker compose exec -T app php bin/console ...`

## Final delivery path

The development Docker workflow is also the delivery workflow for reviewers.

The intended handoff path is:

1. `make bootstrap`
2. `make review`
3. exercise the HTTP API on `http://localhost:8000`

This keeps the delivery simple:

- no local PHP installation is required
- no local Composer installation is required
- no local MongoDB installation is required
- no hidden seed or setup steps are required

## Pull request CI

Pull requests now run the GitHub Actions workflow:

- `.github/workflows/pull-request-quality.yml`

The first PR baseline intentionally stays small and fast:

- ECS
- Rector in dry-run mode
- PHPStan
- deptrac
- unit test suite

Local-to-CI command parity:

- ECS -> `make ecs`
- Rector -> `make rector`
- PHPStan -> `make phpstan`
- deptrac -> `make deptrac`
- unit tests -> `make test-unit`

MongoDB-backed integration tests remain outside this first PR workflow on
purpose. They are still part of the local safety net through `make test` and
can be run directly with `make test-integration`.

## Challenge alignment snapshot

The current delivery covers the expected challenge behaviors:

- accepted coins at the HTTP boundary: `0.05`, `0.10`, `0.25`, and `1`
- products: `water` (`65`), `juice` (`100`), and `soda` (`150`)
- supported flows: insert coin, select product, return coin, and service
- persisted state: available items, available change, and inserted money
- automated checks: unit tests, integration tests, static analysis, formatting,
  architecture checks, and pull-request CI

## Author note

The application and domain layers model money in integer cents to avoid
floating-point precision issues in balances, price comparisons, and change
calculation.

To stay aligned with the challenge wording, the HTTP adapter accepts both:

- `coins`: reviewer-friendly decimal input such as `0.25` or `1`
- `coinCents`: integer cents such as `25` or `100`

`coins` is the preferred frontend-facing field. The adapter converts it to
integer cents immediately before the value reaches the application layer.

When `coins` is used, the adapter only accepts the exact challenge coin values:

- `0.05`
- `0.10`
- `0.25`
- `1`

Values such as `0.249` are rejected instead of being rounded.

## Reviewer validation guide

The first reviewer-facing interface is a thin HTTP JSON layer served by the
`app` container on `localhost:8000`.

Available endpoints:

- `GET /api/machine`
- `POST /api/machine/insert-coin`
- `POST /api/machine/select-product`
- `POST /api/machine/return-coin`
- `POST /api/machine/service`

Recommended validation flow:

1. Bootstrap the local environment:

```bash
make bootstrap
```

2. Confirm the containers are up:

```bash
make status
```

3. Inspect the current machine state:

```bash
curl http://localhost:8000/api/machine
```

4. Insert a `1` coin:

```bash
curl -X POST http://localhost:8000/api/machine/insert-coin \
  -H "Content-Type: application/json" \
  -d '{"coins":1}'
```

5. Buy a product, for example `water`:

```bash
curl -X POST http://localhost:8000/api/machine/select-product \
  -H "Content-Type: application/json" \
  -d '{"selector":"water"}'
```

6. Return the currently inserted money:

```bash
curl -X POST http://localhost:8000/api/machine/return-coin
```

7. Reset the machine state with a service call when needed:

```bash
curl -X POST http://localhost:8000/api/machine/service \
  -H "Content-Type: application/json" \
  -d '{
    "productQuantities":{"water":10,"juice":8,"soda":5},
    "availableChangeCounts":{"5":20,"10":20,"25":20,"100":10}
  }'
```

The seeded default machine uses the selectors `water`, `juice`, and `soda`.

To validate the automated checks as part of the review:

```bash
make review
```

## Test suite split

The project now exposes two explicit test entry points:

- `make test-unit`: fast unit-oriented suite for domain, application, mappers,
  in-memory adapters, and transport factories
- `make test-integration`: MongoDB-backed repository and HTTP integration suite

The default `make test` command runs both suites sequentially because it is the
main local safety-net command.

`make review` is the preferred final delivery check because it runs:

- `make quality`
- `make coverage`

## Coverage threshold

`make coverage` now enforces a minimum coverage threshold of `90%` for:

- classes
- methods
- lines

The threshold is checked from a generated Cobertura report through
`bin/check-coverage-threshold.php`.

## Development docs

When changing the core or adding a feature, start here:

- `docs/development/conventions.md`
- `docs/development/ai-development-guide.md`
- `src/VendingMachine/README.md`
