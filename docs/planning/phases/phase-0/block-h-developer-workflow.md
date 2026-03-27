# Phase 0 / Block H: Developer Workflow and Documentation

## Purpose

Define the controlled entry points, scripts, and contributor-facing instructions needed to operate the local environment predictably.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-b-docker-workspace.md`
- `docs/planning/phases/phase-0/block-g-quality-toolchain.md`

## Tasks

- [x] P0-079: define the Composer script for project setup
- [x] P0-080: define the Composer script for linting and style checks
- [x] P0-081: define the Composer script for static analysis
- [x] P0-082: define the Composer script for tests or reserve the placeholder that later phases will fill
- [x] P0-083: adopt a `Makefile` as the preferred controlled entry point for recurring local commands
- [x] P0-084: implement the first `Makefile` targets for bootstrap, environment control, console access, lint, analysis, and tests
- [x] P0-085: document the `Makefile` usage and the command mapping it abstracts
- [x] P0-086: document the standard environment startup flow
- [x] P0-087: document the standard environment shutdown flow
- [x] P0-088: document the first-run bootstrap flow for a clean machine
- [x] P0-089: document the minimum local prerequisites for contributors, including GNU Make
- [x] P0-090: document where architecture rules, naming conventions, AI/developer guidance, and quality commands live
- [x] P0-091: validate the documented workflow and guidance from the perspective of a new developer with no hidden steps

## Workflow direction

Phase 0 adopts two controlled entry layers:

- Composer scripts define the container-internal quality and setup entry points
- `Makefile` is the preferred host-facing command surface for recurring local work

This keeps the runtime aligned with Docker while still giving contributors a
small and memorable command set.

## Composer script baseline

The baseline scripts added in Phase 0 are:

- `project:setup`
- `lint`
- `lint:ecs`
- `analyse`
- `analyse:phpstan`
- `analyse:deptrac`
- `analyse:rector`
- `test`
- `test:placeholder`

Current intent:

- `project:setup` performs the minimum environment checks after install
- `lint` and `analyse` group the quality tools behind stable command names
- `test` is intentionally a placeholder until the real automated test suites land

## Makefile baseline

The first `Makefile` covers:

- environment lifecycle: `build`, `up`, `down`, `restart`, `status`
- dependency and setup flow: `install`, `setup`, `bootstrap`
- container access: `shell`, `composer`, `console`
- quality and verification: `lint`, `analyse`, `quality`, `test`
- direct tool targets: `phpstan`, `deptrac`, `rector`, `ecs`, `mongodb-smoke`

The `Makefile` is the preferred local entry point because it keeps Docker and
Composer usage consistent and avoids leaking container details into everyday
instructions.

## Documentation baseline

Phase 0 documents the workflow in these locations:

- `README.md`: local prerequisites, first run, daily workflow, and command mapping
- `docs/development/conventions.md`: non-automated architecture and naming conventions
- `docs/development/ai-development-guide.md`: short guide for future AI-assisted feature work

## Validation snapshot

Validated in Phase 0 using only the documented entry points:

- `make help`: successful
- `make bootstrap`: successful
- `make quality`: successful
- `make test`: successful with the expected placeholder message
- `make down`: successful

## Notes

- local PHP, Composer, Symfony CLI, and MongoDB installations are not required
- the host-facing contract is `make`; the container-facing contract is Composer scripts
- the placeholder `test` entry point exists to keep the workflow stable before real suites are introduced

## Output contract

The block is expected to leave behind:

- a preferred local entry point based on `Makefile`
- documented startup, shutdown, and first-run flows
- clear contributor prerequisites, command mapping, and location of non-automated conventions

## Exit condition

A reviewer can reach a working local platform by following the committed documentation only.
