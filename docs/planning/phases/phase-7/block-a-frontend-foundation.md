# Phase 7 / Block A: Frontend Foundation

## Purpose

Create the minimum frontend baseline needed to add a reviewer-facing UI without
overcomplicating the repository.

## Depends on

- `docs/planning/phases/phase-7/README.md`
- `docs/planning/phases/phase-6-packaging-and-delivery.md`
- `docs/planning/phases/phase-4/README.md`

## Tasks

- [ ] P7-001: choose the frontend folder path inside the repository
- [ ] P7-002: choose the frontend toolchain and justify the no-framework or low-framework direction
- [ ] P7-003: decide whether the frontend uses a dedicated package manager path without extra monorepo tooling
- [ ] P7-004: define the browser-to-backend communication strategy and avoid unnecessary backend CORS changes
- [ ] P7-005: define the Docker service shape for the frontend and how it joins the current stack
- [ ] P7-006: scaffold the frontend project with development, test, and build scripts
- [ ] P7-007: define the frontend folder structure for app shell, API client, state, UI pieces, and styles
- [ ] P7-008: create typed API models aligned with the current vending machine HTTP contract
- [ ] P7-009: create a thin frontend API client for machine state, insert, select, refund, and service operations
- [ ] P7-010: establish the initial visual language, layout direction, and responsive baseline
- [ ] P7-011: expose the initial frontend service through Docker and `make` without breaking the current backend flow

## Direction to freeze

This block should freeze:

- the frontend directory layout
- the frontend runtime strategy inside Docker
- the API access strategy from the browser
- the initial visual and structural baseline

## Output contract

The block is expected to leave behind:

- a runnable frontend skeleton
- one explicit API client boundary
- a reproducible Dockerized startup path
- a visual baseline ready to receive reviewer flows

## Exit condition

The project can start a frontend container and render a baseline reviewer shell
that is ready to consume the vending machine API.
