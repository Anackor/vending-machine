# Phase 4: Initial Interface

## Objective

Expose the backend through a thin Symfony-driven interface that demonstrates the main use cases without bloating the first delivery.

## Scope

- console command or minimal HTTP layer
- vend, refund, and failure outputs
- documented usage examples

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`
- `docs/planning/phases/phase-2-application-layer-and-ports.md`
- `docs/planning/phases/phase-3-infrastructure-and-persistence.md`

## Tasks

- choose the initial interaction mode
- connect the interface to the application layer only
- implement the main user actions through Symfony adapters
- keep framework-specific details out of the domain and application core
- document a few representative usage flows

## Recommended option

Start with a Symfony console command or a minimal command-driven interaction layer. This gives reviewers a direct way to exercise the machine behavior while keeping the architecture clean.

## Validations

- the interface can trigger the main use cases successfully
- outputs are clear for success and failure paths
- interface code depends on application contracts, not domain internals
- the main challenge flows can be exercised end to end

## Deliverables

- runnable initial interface
- documented example flows

## Exit criteria

This phase is complete when a reviewer can exercise the machine behavior through a thin interface without needing to inspect internal code paths.
