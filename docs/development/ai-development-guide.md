# AI Development Guide

## Purpose

This guide helps future AI-assisted changes stay aligned with the current
project structure and working agreements.

## Minimum context to load

For most backend implementation work, load:

- `README.md`
- `docs/README.md`
- the active phase document
- `docs/development/conventions.md`
- `src/VendingMachine/README.md`

For Phase 0 work, also load:

- `docs/planning/phases/phase-0/README.md`
- only the active `block-*.md` file

## Class placement guide

- Symfony bootstrap only: `App\Kernel`
- domain rules, invariants, and value objects: `src/VendingMachine/Domain/Machine/`
- use-case orchestration and application contracts: `src/VendingMachine/Application/Machine/`
- Symfony adapters: `src/VendingMachine/Infrastructure/Symfony/`
- MongoDB persistence code: `src/VendingMachine/Infrastructure/Persistence/MongoDB/Machine/`

## Current project assumptions

- one bounded context: `VendingMachine`
- one initial module: `Machine`
- no `Shared` or `Common` namespace yet
- no ODM baseline in Phase 0
- MongoDB integration is explicit infrastructure wiring, not a domain concern

## Tooling expectations

- use `make` as the preferred entry point for local project commands
- use Composer scripts from inside the `app` container, not from the host
- use `make lint` for style checks
- use `make analyse` for static analysis and architecture checks
- use `make mongodb-smoke` when touching MongoDB wiring

## Before finalizing a change

- confirm the new class lives in the correct layer
- confirm naming follows the current module and suffix rules
- avoid introducing framework or persistence concerns into the domain
- run the smallest relevant documented command set before closing the task

## Escalation rule

If a change would introduce a new module, a new bounded context, or a shared
namespace, stop and document the reason before applying it. Those are not
default moves in the current project.
