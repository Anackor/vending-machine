# Phase 4: Initial Interface

## Objective

Expose the persisted application layer through one thin Symfony-facing
interface that a reviewer can execute without inspecting internal code paths.

## Scope

- one minimal reviewer-facing interface mode
- application-driven request handling
- clear success and failure outputs
- documented example flows

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`
- `docs/planning/phases/phase-2/README.md`
- `docs/planning/phases/phase-3/README.md`

## Canonical structure

This `README.md` is the canonical index for Phase 4.

Use it to understand:

- the phase objective and scope
- the execution order
- the block status
- the phase-wide validations and outputs

Use the block file for task-level execution:

- `block-a-thin-reviewer-interface.md`

## Planning note

Phase 4 is intentionally kept in one block.

At this point the domain, application, and persistence layers are already
validated. The remaining work is one cohesive slice: add a thin interface on
top of the existing handlers and document how a reviewer can exercise it.

Splitting the phase further would create more coordination overhead than real
delivery value.

## Recommended execution order

1. Block A: thin reviewer interface

## Loading rule for Phase 4 work

For any Phase 4 task, always load:

- this `README.md`
- `docs/planning/phases/phase-4/block-a-thin-reviewer-interface.md`

Load Phase 3 documents only if:

- persistence-backed flows or fixture assumptions must be checked
- the interface behavior must be verified against the stored machine state

Load Phase 2 documents only if:

- handler contracts, application failures, or result shapes must be confirmed

Load Phase 1 documents only if:

- output rendering must be checked against domain invariants or terminology

Load Phase 0 documents only if:

- Symfony adapter placement or local workflow commands must be confirmed

## Block index

### A. Thin reviewer interface

- file: `docs/planning/phases/phase-4/block-a-thin-reviewer-interface.md`
- task ids: `P4-001` to `P4-015`
- status: complete
- outputs: initial interface surface, Symfony adapter wiring, documented usage examples, validated end-to-end reviewer flow

## Mandatory interface behaviors for Phase 4

- expose the current machine state through a reviewer-friendly read path
- expose the main mutation flows for insert coin, select product, return coin,
  and service
- route requests through application handlers only
- keep framework-specific details out of domain and application code
- document at least the main challenge flows end to end

## Phase-wide validations

- the interface exercises persisted application behavior successfully
- outputs are clear for both success and failure paths
- Symfony-facing code depends on application contracts, not domain internals
- a reviewer can follow documented examples without hidden local knowledge

## Deliverables

- runnable initial interface
- documented request and response examples
- validated end-to-end reviewer flow on top of persistence

## Validation snapshot

Validated at the end of Phase 4:

- `make bootstrap`: successful
- `docker compose exec -T app php bin/console debug:router`: successful
- `make test`: successful with `154` tests and `651` assertions
- `make quality`: successful
- `make coverage`: successful
- representative HTTP calls against `localhost:8000`: successful

Interface result summary:

- Symfony serves a thin JSON HTTP layer from the `app` container
- the default machine is seeded during setup if it does not exist yet
- the main challenge flows can be exercised through HTTP on top of real MongoDB persistence
- the next step is Phase 5

## Exit criteria

This phase is complete when a reviewer can operate the vending machine through
the chosen thin interface and observe the main challenge flows clearly.
