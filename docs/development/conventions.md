# Development Conventions

## Purpose

This document captures the project conventions that are not fully enforced by
automated tools.

Use it together with:

- `src/VendingMachine/README.md`
- `docs/architecture/http-api-boundary.md`
- `docs/planning/phases/phase-0/block-d-architecture-and-namespace.md`
- `docs/planning/phases/phase-0/block-e-ddd-naming.md`

## Tool responsibilities

- `ECS` enforces formatting and coding-style mechanics
- `PHPStan` checks static correctness and type-related issues
- `deptrac` protects dependency direction between layers
- `Rector` helps with automated refactors and baseline modernization in dry-run

These tools do not replace semantic naming decisions or class-placement rules.

## Core architecture rules

- `App\Kernel` is the only Symfony bootstrap class under `App\`
- `VendingMachine\Domain\...` contains domain rules and must not depend on Symfony or MongoDB code
- `VendingMachine\Application\...` orchestrates use cases and may depend on `Domain`
- `VendingMachine\Infrastructure\Symfony\...` contains Symfony-facing adapters such as commands or controllers
- `VendingMachine\Infrastructure\Persistence\MongoDB\...` contains MongoDB-specific persistence code

## Naming rules

- use singular business names for aggregates and entities
- do not use `Aggregate`, `Entity`, `ValueObject`, or `Interface` suffixes as a default naming strategy
- reserve the `Handler` suffix for application use cases
- do not use `Handler` for Symfony console commands or controllers
- name repository contracts after the aggregate they persist, for example `MachineRepository`
- name MongoDB adapters with the technology prefix, for example `MongoDBMachineRepository`

## Module baseline

- the bounded context is `VendingMachine`
- the first and only business module for now is `Machine`
- do not introduce `Shared` or `Common` unless the codebase proves the need

## Where rules live

- architecture and namespace decisions: `docs/planning/phases/phase-0/block-d-architecture-and-namespace.md`
- HTTP adapter boundary decisions: `docs/architecture/http-api-boundary.md`
- naming decisions: `docs/planning/phases/phase-0/block-e-ddd-naming.md`
- core-facing summary: `src/VendingMachine/README.md`
- enforceable dependency rules: `deptrac.yaml`
- quality command entry points: `composer.json` and `Makefile`

## Maintenance rule

If a rule can be enforced automatically, prefer tool configuration over prose.

If a rule is semantic or contextual, document it here briefly and keep it close
to the code or planning decision it depends on.
