# Phase 0 / Block D: Architecture and Namespace Baseline

## Purpose

Define the initial source tree and dependency direction so later implementation work starts from explicit architectural boundaries.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-c-symfony-bootstrap.md`

## Tasks

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

## Output contract

The block is expected to leave behind:

- an explicit source tree
- namespace conventions aligned with that tree
- a first dependency-direction rule set ready to be enforced later

## Exit condition

The source tree shows the intended architecture before any business code is added.
