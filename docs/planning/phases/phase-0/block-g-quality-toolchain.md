# Phase 0 / Block G: Quality Toolchain Bootstrap

## Purpose

Install and wire the baseline static-analysis and code-quality tools that will enforce architecture and maintenance rules from the start.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-d-architecture-and-namespace.md`

## Tasks

- [x] P0-063: add PHPStan as a development dependency
- [x] P0-064: add deptrac as a development dependency
- [x] P0-065: add Rector as a development dependency
- [x] P0-066: add ECS as a development dependency
- [x] P0-067: create the initial PHPStan configuration file
- [x] P0-068: set the first PHPStan level and analysed paths
- [x] P0-069: create the initial deptrac configuration file
- [x] P0-070: encode the first architecture layers and forbidden dependency rules in deptrac
- [x] P0-071: create the initial Rector configuration file
- [x] P0-072: select the first Rector rule sets that are safe for the project baseline
- [x] P0-073: create the initial ECS configuration file
- [x] P0-074: select the first coding-standard sets to enforce
- [x] P0-075: run PHPStan successfully against the current codebase
- [x] P0-076: run deptrac successfully against the current codebase
- [x] P0-077: run Rector in dry-run mode successfully
- [x] P0-078: run ECS in check mode successfully

## Tooling decision

Phase 0 installs the following quality tools:

- `phpstan/phpstan`
- `deptrac/deptrac`
- `rector/rector`
- `symplify/easy-coding-standard`

The baseline aims for immediate usefulness without introducing aggressive rules
that would create noise before the domain model exists.

## Configuration baseline

The initial configuration files are:

- `phpstan.neon.dist`
- `deptrac.yaml`
- `rector.php`
- `ecs.php`

Selected baseline:

- PHPStan analyses `src/` at `level: max`
- deptrac encodes the initial architectural layers and forbidden internal dependencies
- Rector uses `withPhpSets()` to keep the code aligned with the declared PHP target without forcing broader refactors yet
- ECS enforces a conservative `psr12` baseline

## Architecture enforcement direction

The deptrac layer model currently covers:

- `Kernel`
- `Domain`
- `Application`
- `InfrastructureSymfony`
- `InfrastructurePersistence`

Encoded dependency direction:

- `Domain` is the innermost layer
- `Application` may depend on `Domain`
- `InfrastructureSymfony` may depend on `Application` and `Domain`
- `InfrastructurePersistence` may depend on `Application` and `Domain`
- `Kernel` may depend on every project layer

Phase 0 keeps deptrac focused on forbidden internal dependencies. It does not
yet fail the build on uncovered framework or library dependencies; that
tightening is deferred to a later hardening phase.

## Validation snapshot

Validated in Phase 0 after the quality-tooling bootstrap:

- `vendor/bin/phpstan analyse --configuration=phpstan.neon.dist`: successful
- `vendor/bin/deptrac analyse --config-file=deptrac.yaml`: successful with `0` violations
- `vendor/bin/rector process --dry-run --config=rector.php`: successful
- `vendor/bin/ecs check --config=ecs.php`: successful

## Notes

- the first Rector baseline intentionally avoids broad code-quality refactors
- the first ECS baseline intentionally stays close to Symfony-friendly PSR-12 formatting
- deptrac already protects forbidden layer crossings, while stricter uncovered-dependency handling is postponed

## Output contract

The block is expected to leave behind:

- four runnable quality tools
- initial configuration files for each tool
- a first enforceable architecture rule set

## Exit condition

Every agreed quality tool is executable and already protects the empty or near-empty baseline.
