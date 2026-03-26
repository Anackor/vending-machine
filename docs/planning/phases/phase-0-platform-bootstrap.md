# Phase 0: Platform Bootstrap

## Objective

Create the engineering foundation that allows backend development to start immediately with Symfony, Docker, MongoDB, architectural boundaries, and quality tooling already in place.

## Scope

- latest stable Symfony version
- Docker-based local environment
- MongoDB container and connectivity
- Hexagonal Architecture baseline
- DDD-oriented project structure
- SOLID-driven design constraints
- PHPStan, deptrac, Rector, and ECS
- Makefile-based command entry point for recurring local operations
- repeatable local developer workflow

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`

## Recommended execution order

1. freeze runtime and tooling decisions
2. bootstrap Docker and Symfony
3. freeze architecture and naming conventions
4. prepare MongoDB connectivity
5. install and wire the quality toolchain
6. document the developer workflow
7. run the full phase gate from scratch

Each block below is intended to be executed and reviewed independently. Do not start the next block until the current block exit condition is satisfied.

## Atomic task backlog

### A. Runtime and tooling decisions

- [x] P0-001: target Symfony `8.0.x`; as of 2026-03-26 the latest stable release is `8.0.7`
- [x] P0-002: use PHP `8.4` because Symfony `8.0` requires PHP `8.4.0` or higher
- [x] P0-003: use `php:8.4-cli-bookworm` as the local application base image for Phase 0
- [x] P0-004: use `mongo:8.0.19-noble` as the local MongoDB image for Phase 0
- [x] P0-005: require the Symfony baseline extensions `ctype`, `iconv`, `json`, `pcre`, `session`, `simplexml`, and `tokenizer`; include `intl`, `mbstring`, `opcache`, and `zip` as project-level development essentials
- [x] P0-006: require the `mongodb` PHP extension for the selected MongoDB integration direction
- [x] P0-007: run Composer inside Docker only; do not require host-level Composer
- [x] P0-008: do not require Symfony CLI locally; standardize on `bin/console` inside the application container
- [x] P0-009: postpone production-oriented Docker files and image hardening to Phase 6 unless a later phase creates an earlier delivery need
- [x] P0-010: record the selected versions and bootstrap decisions in committed documentation

Frozen decisions for Block A on 2026-03-26:

- Symfony target: `8.0.*`
- PHP runtime target: `8.4`
- PHP application image: `php:8.4-cli-bookworm`
- MongoDB image: `mongo:8.0.19-noble`
- Composer strategy: install and run Composer inside the application container only
- Symfony CLI strategy: not required locally for Phase 0
- Docker delivery strategy: one development-oriented Docker path now; production-oriented Docker work later
- Workflow direction: adopt a `Makefile` in Block H as the preferred entry point for recurring local commands

Block exit condition: runtime versions, extension list, and local-tooling strategy are frozen.

### B. Docker workspace bootstrap

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

Block B implementation notes:

- Docker assets live under `docker/php/` with a dedicated PHP config override in `docker/php/conf.d/app.ini`
- the application container is built from `php:8.4-cli-bookworm` and includes Composer plus the required Phase 0 extensions
- the application service uses `/app` as its working directory and keeps the container alive with `sleep infinity` for console-driven development
- the repository root is bind-mounted into `/app`
- MongoDB state is stored in the named volume `mongo-data`
- Composer cache is stored in the named volume `composer-cache`
- both services share the `platform` bridge network
- the stack was validated on 2026-03-26 with healthy `app` and `mongo` containers, PHP extension checks, app-to-Mongo TCP connectivity, and bind-mount visibility

Block exit condition: `docker compose up` boots the app and MongoDB reliably from a clean clone.

### C. Symfony project bootstrap

- [ ] P0-025: initialize the repository as a Symfony project on the selected stable version
- [ ] P0-026: commit or document the exact bootstrap command needed to recreate the Symfony skeleton
- [ ] P0-027: verify `composer.json`, `composer.lock`, and Symfony baseline files are present
- [ ] P0-028: configure `.env` defaults needed for containerized local execution
- [ ] P0-029: define the strategy for local overrides in `.env.local` without committing secrets
- [ ] P0-030: ensure `var/` and cache directories are writable inside the container
- [ ] P0-031: run a basic Symfony console command inside the application container
- [ ] P0-032: run a basic bootstrap check to confirm the Symfony kernel starts without errors
- [ ] P0-033: confirm that source-code changes are visible immediately through the bind mount
- [ ] P0-034: decide whether the first runnable interface is the built-in server, PHP-FPM, or console-only for this phase

Block exit condition: Symfony boots successfully inside Docker and can be operated with documented commands only.

### D. Architecture and namespace baseline

- [ ] P0-035: choose the bounded context name for the vending machine core
- [ ] P0-036: choose whether shared cross-context primitives live in `Shared`, `Common`, or an equivalent namespace
- [ ] P0-037: define the top-level source directories for domain, application, and infrastructure code
- [ ] P0-038: define the location for Symfony-specific adapters
- [ ] P0-039: define the location for persistence-specific adapters
- [ ] P0-040: configure Composer autoloading for the selected namespaces
- [ ] P0-041: create the initial directory skeleton that expresses the chosen Hexagonal Architecture
- [ ] P0-042: add placeholder files when needed so the intended directory layout is visible in Git
- [ ] P0-043: write the first dependency direction rules in documentation
- [ ] P0-044: confirm that the planned structure allows framework code to depend on the core, not the reverse

Block exit condition: the source tree shows the intended architecture before any business code is added.

### E. DDD naming baseline

- [ ] P0-045: choose the first module name inside the vending machine bounded context
- [ ] P0-046: define naming conventions for aggregates
- [ ] P0-047: define naming conventions for entities
- [ ] P0-048: define naming conventions for value objects
- [ ] P0-049: define naming conventions for repositories and repository interfaces
- [ ] P0-050: define naming conventions for domain services
- [ ] P0-051: define naming conventions for application services or use-case handlers
- [ ] P0-052: record the naming conventions in a short architecture note or README section

Block exit condition: future phases can add classes without reopening naming and module-structure debates.

### F. MongoDB foundation

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

Block exit condition: MongoDB connectivity and a trivial round-trip work inside the local environment.

### G. Quality toolchain bootstrap

- [ ] P0-063: add PHPStan as a development dependency
- [ ] P0-064: add deptrac as a development dependency
- [ ] P0-065: add Rector as a development dependency
- [ ] P0-066: add ECS as a development dependency
- [ ] P0-067: create the initial PHPStan configuration file
- [ ] P0-068: set the first PHPStan level and analysed paths
- [ ] P0-069: create the initial deptrac configuration file
- [ ] P0-070: encode the first architecture layers and forbidden dependency rules in deptrac
- [ ] P0-071: create the initial Rector configuration file
- [ ] P0-072: select the first Rector rule sets that are safe for the project baseline
- [ ] P0-073: create the initial ECS configuration file
- [ ] P0-074: select the first coding-standard sets to enforce
- [ ] P0-075: run PHPStan successfully against the current codebase
- [ ] P0-076: run deptrac successfully against the current codebase
- [ ] P0-077: run Rector in dry-run mode successfully
- [ ] P0-078: run ECS in check mode successfully

Block exit condition: every agreed quality tool is executable and already protects the empty or near-empty baseline.

### H. Developer workflow and documentation

- [ ] P0-079: define the Composer script for project setup
- [ ] P0-080: define the Composer script for linting and style checks
- [ ] P0-081: define the Composer script for static analysis
- [ ] P0-082: define the Composer script for tests or reserve the placeholder that later phases will fill
- [ ] P0-083: adopt a `Makefile` as the preferred controlled entry point for recurring local commands
- [ ] P0-084: implement the first `Makefile` targets for bootstrap, environment control, console access, lint, analysis, and tests
- [ ] P0-085: document the `Makefile` usage and the command mapping it abstracts
- [ ] P0-086: document the standard environment startup flow
- [ ] P0-087: document the standard environment shutdown flow
- [ ] P0-088: document the first-run bootstrap flow for a clean machine
- [ ] P0-089: document the minimum local prerequisites for contributors, including GNU Make
- [ ] P0-090: document where architecture rules and quality commands live
- [ ] P0-091: validate the documented workflow from the perspective of a new developer with no hidden steps

Block exit condition: a reviewer can reach a working local platform by following the committed documentation only.

### I. Final phase gate

- [ ] P0-092: run the full documented bootstrap flow from scratch
- [ ] P0-093: run the full documented quality flow from scratch
- [ ] P0-094: re-run the MongoDB smoke check after the quality setup is in place
- [ ] P0-095: verify the source tree still respects the intended dependency direction
- [ ] P0-096: confirm that Phase 1 can start without reopening platform, tooling, or directory-layout decisions

## Validations

### Stack validation

- Symfony installs on the selected PHP version
- required PHP extensions are available in the application container

### Docker validation

- application container starts successfully
- MongoDB container starts successfully
- services can communicate over the configured network
- source changes are visible during development

### Symfony validation

- the Symfony application boots without errors
- environment variables resolve correctly
- Symfony console commands run from the container

### Architecture validation

- the source tree reflects Hexagonal Architecture clearly
- the domain has no dependency on Symfony or MongoDB code
- deptrac detects forbidden dependencies when rules are violated

### MongoDB validation

- the application can connect to MongoDB
- a simple smoke test can write and read data
- the chosen persistence direction supports the intended domain model

### Quality validation

- PHPStan runs successfully
- deptrac runs successfully
- Rector runs in dry-run mode
- ECS can check formatting successfully

### Workflow validation

- the project can be started with documented commands only
- the preferred `Makefile` commands are documented and runnable
- the lint and analysis workflow is documented and runnable
- a new developer can reach a working local environment without hidden steps

## Deliverables

- Symfony project bootstrapped on the latest stable version
- Docker environment for the application and MongoDB
- initial Hexagonal and DDD-oriented project structure
- configured PHPStan, deptrac, Rector, and ECS
- Makefile-based local command entry point
- documented local developer workflow

## Exit criteria

This phase is complete when the platform is stable enough to begin domain implementation without revisiting setup decisions immediately.
