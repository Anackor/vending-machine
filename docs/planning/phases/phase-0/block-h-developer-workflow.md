# Phase 0 / Block H: Developer Workflow and Documentation

## Purpose

Define the controlled entry points, scripts, and contributor-facing instructions needed to operate the local environment predictably.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-b-docker-workspace.md`
- `docs/planning/phases/phase-0/block-g-quality-toolchain.md`

## Tasks

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
- [ ] P0-090: document where architecture rules, naming conventions, AI/developer guidance, and quality commands live
- [ ] P0-091: validate the documented workflow and guidance from the perspective of a new developer with no hidden steps

## Output contract

The block is expected to leave behind:

- a preferred local entry point based on `Makefile`
- documented startup, shutdown, and first-run flows
- clear contributor prerequisites, command mapping, and location of non-automated conventions

## Exit condition

A reviewer can reach a working local platform by following the committed documentation only.
