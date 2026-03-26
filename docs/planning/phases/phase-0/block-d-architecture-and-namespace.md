# Phase 0 / Block D: Architecture and Namespace Baseline

## Purpose

Define the initial source tree and dependency direction so later implementation work starts from explicit architectural boundaries.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-c-symfony-bootstrap.md`

## Tasks

- [x] P0-035: choose the bounded context name for the vending machine core
- [x] P0-036: choose whether shared cross-context primitives live in `Shared`, `Common`, or an equivalent namespace
- [x] P0-037: define the top-level source directories for domain, application, and infrastructure code
- [x] P0-038: define the location for Symfony-specific adapters
- [x] P0-039: define the location for persistence-specific adapters
- [x] P0-040: configure Composer autoloading for the selected namespaces
- [x] P0-041: create the initial directory skeleton that expresses the chosen Hexagonal Architecture
- [x] P0-042: add placeholder files when needed so the intended directory layout is visible in Git
- [x] P0-043: write the first dependency direction rules in documentation
- [x] P0-044: confirm that the planned structure allows framework code to depend on the core, not the reverse

## Architecture decision

Phase 0 will use exactly one bounded context: `VendingMachine`.

Rationale:

- YAGNI: the challenge models one cohesive business capability, not multiple subdomains that justify several bounded contexts
- KISS: a single bounded context keeps naming, folders, and dependency rules easy to understand for reviewers
- SOLID: responsibilities are separated by layer, not by speculative contexts
- clean code: the namespace communicates the business core explicitly without inventing abstractions we do not need yet

This means:

- the business core starts in `VendingMachine\`
- Symfony bootstrap code remains under `App\`
- no second bounded context is introduced until a real language or lifecycle boundary appears

## Shared code decision

No `Shared` or `Common` namespace will be created in Phase 0.

Rationale:

- there is only one bounded context today
- introducing a shared namespace now would be speculative
- if a reusable concept appears before a second bounded context exists, it should stay inside `VendingMachine` until duplication proves otherwise

## Selected source tree

The baseline source tree is:

- `src/Kernel.php`
- `src/VendingMachine/Domain/`
- `src/VendingMachine/Application/`
- `src/VendingMachine/Infrastructure/Symfony/`
- `src/VendingMachine/Infrastructure/Persistence/MongoDB/`

Namespace mapping:

- `App\Kernel` for Symfony bootstrap
- `VendingMachine\Domain\...` for domain model and domain contracts
- `VendingMachine\Application\...` for use cases and orchestration
- `VendingMachine\Infrastructure\Symfony\...` for Symfony-facing adapters
- `VendingMachine\Infrastructure\Persistence\MongoDB\...` for MongoDB-facing adapters

## Dependency direction rules

The first dependency rules are:

- `VendingMachine\Domain` must not depend on Symfony, MongoDB, `Application`, or `Infrastructure`
- `VendingMachine\Application` may depend on `VendingMachine\Domain`
- `VendingMachine\Application` must not depend directly on Symfony or MongoDB packages
- `VendingMachine\Infrastructure\Symfony` may depend on `VendingMachine\Application` and `VendingMachine\Domain`
- `VendingMachine\Infrastructure\Persistence\MongoDB` may depend on `VendingMachine\Application` and `VendingMachine\Domain`
- `App\Kernel` may bootstrap Symfony and wire the framework around the core, but it must not pull business rules upward into framework classes

## Implementation notes

- Composer now autoloads `VendingMachine\` from `src/VendingMachine/`
- Symfony service discovery excludes `src/VendingMachine/Domain/` from automatic container registration
- `VendingMachine\Application\` is registered for automatic discovery
- `VendingMachine\Infrastructure\Symfony\` is registered for automatic discovery
- persistence adapters are intentionally left out of automatic discovery until they exist and their wiring needs become explicit
- placeholder files were added so the intended tree is visible in Git before business classes exist

## Validation snapshot

Validated in Phase 0 after the structural change:

- `composer dump-autoload`: successful
- `php bin/console about`: successful
- the source tree expresses one bounded context and three core layers clearly
- Symfony bootstrap remains isolated in `App\Kernel`
- the planned structure keeps framework code outside the domain model

## Output contract

The block is expected to leave behind:

- an explicit source tree
- namespace conventions aligned with that tree
- a first dependency-direction rule set ready to be enforced later

## Exit condition

The source tree shows the intended architecture before any business code is added.
