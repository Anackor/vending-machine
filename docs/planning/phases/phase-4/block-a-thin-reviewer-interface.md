# Phase 4 / Block A: Thin Reviewer Interface

## Purpose

Implement the first reviewer-facing interface on top of the persisted
application layer without leaking framework concerns into the core.

## Depends on

- `docs/planning/phases/phase-4/README.md`
- `docs/planning/phases/phase-2/block-c-handlers-and-ports.md`
- `docs/planning/phases/phase-3/block-d-persistence-phase-gate.md`

## Recommended interface direction

Phase 4 should prefer one minimal HTTP JSON interface implemented with thin
Symfony controllers.

Why this is the recommended path:

- it is easier for an external reviewer to exercise with `curl` or an API
  client than an interactive console flow
- it stays thin because the application layer already exposes stable commands,
  query models, results, and failures
- it keeps Phase 4 focused on transport and presentation, not on new business
  behavior

What Phase 4 should avoid:

- HTML or templating work
- API Platform or other heavy abstractions
- authentication or user management
- interface-side business logic

## Tasks

- [x] P4-001: freeze the initial interface mode as one minimal Symfony HTTP JSON layer
- [x] P4-002: define the exact endpoint surface for machine state and the main mutation flows
- [x] P4-003: define the request payload for inserting a coin
- [x] P4-004: define the request payload for selecting a product
- [x] P4-005: define the request payload for returning inserted money
- [x] P4-006: define the request payload for servicing the machine stock and available change
- [x] P4-007: define the response shape for machine state snapshots
- [x] P4-008: define the response shape for successful mutation outcomes, including vended product and returned change
- [x] P4-009: define the transport mapping for application failures and their HTTP status codes
- [x] P4-010: implement the thin Symfony controller or action layer without business logic
- [x] P4-011: wire the controllers to Phase 2 handlers and query services only
- [x] P4-012: expose the persisted machine state through a reviewer-friendly read endpoint
- [x] P4-013: expose the main challenge actions through mutation endpoints backed by real persistence
- [x] P4-014: document representative request and response flows in reviewer-facing documentation
- [x] P4-015: validate the end-to-end interface path with tests, quality checks, and real example executions

## Implemented interface surface

Phase 4 now exposes one thin HTTP JSON layer under `/api/machine`:

- `GET /api/machine`
- `POST /api/machine/insert-coin`
- `POST /api/machine/select-product`
- `POST /api/machine/return-coin`
- `POST /api/machine/service`

The transport shape stays deliberately small:

- successful reads return `machine`
- successful mutations return `event` plus `machine`
- failures return `error` with `code`, `message`, and `context`

HTTP failure mapping is now explicit:

- `400`: invalid request payloads, unsupported coin, invalid service configuration
- `404`: machine not found, product not found
- `409`: insufficient balance, exact change unavailable, product out of stock, pending balance during service

## Implementation notes

The Symfony-facing adapter stays thin and depends only on the application
handlers and contracts.

The main infrastructure pieces are:

- `Controller/Api/MachineController.php`
- `Controller/Api/MachineJsonRequestMapper.php`
- `Controller/Api/MachineJsonResponder.php`

To keep the reviewer path usable from a clean environment, setup now also
seeds the default machine if it does not exist yet through:

- `Command/SeedDefaultMachineCommand.php`
- `composer run project:setup`

This preserves the application contracts while avoiding hidden manual setup for
reviewers.

## Validation snapshot

Validated during Phase 4 completion:

- `make bootstrap`
- `docker compose exec -T app php bin/console debug:router`
- `make test`
- `make quality`
- `make coverage`
- real HTTP requests against `localhost:8000`

## Output contract

The block is expected to leave behind:

- one thin reviewer-facing Symfony interface
- explicit transport contracts for success and failure outputs
- documented example flows for the challenge use cases
- a validated handoff to Phase 5 hardening

## Exit condition

A reviewer can interact with the machine through the chosen interface and the
transport layer remains a thin adapter on top of the application contracts.
