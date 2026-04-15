# Development Workflow

The `Makefile` is the preferred local entry point. Commands run inside Docker so
local PHP, Composer, Symfony CLI, and MongoDB installations are not required.

## Common Commands

- `make bootstrap`: build, start, install dependencies, run setup
- `make up`: start the Docker environment
- `make down`: stop the Docker environment
- `make status`: show Docker Compose service status
- `make shell`: open a shell inside the app container
- `make console cmd="about"`: run a Symfony console command
- `make mongodb-smoke`: run the MongoDB smoke command

## Quality Commands

- `make lint`: run ECS
- `make analyse`: run PHPStan, Deptrac, and Rector dry-run
- `make quality`: run lint and analysis
- `make test`: run unit and integration suites
- `make test-unit`: run the fast unit-oriented suite
- `make test-integration`: run MongoDB-backed integration tests
- `make coverage`: run coverage and enforce the threshold
- `make review`: run the reviewer-facing validation baseline

## Frontend Commands

- `make ui-install`: install frontend dependencies inside the frontend container
- `make ui-shell`: open a shell inside the frontend container
- `make ui-test`: run the frontend test suite
- `make ui-build`: run the frontend production build

## Command Mapping

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
- `make ui-test` -> `docker compose exec -T frontend npm run test`
- `make ui-build` -> `docker compose exec -T frontend npm run build`

## Test Suite Split

- `make test-unit`: domain, application, mappers, in-memory adapters, and transport factories
- `make test-integration`: MongoDB-backed repository and HTTP integration suite

The default `make test` command runs both suites sequentially.

## Coverage

`make coverage` enforces a minimum `90%` threshold for:

- classes
- methods
- lines

The threshold is checked from a generated Cobertura report through
`bin/check-coverage-threshold.php`.

## Pull Request CI

Pull requests run `.github/workflows/pull-request-quality.yml`.

The baseline includes:

- ECS
- Rector dry-run
- PHPStan
- Deptrac
- unit tests

MongoDB-backed integration tests remain part of the local safety net through
`make test` and `make test-integration`.
