# Phase 7 / Block C: UI Tests, Docker, and Handoff

## Purpose

Close the reviewer UI with a small but trustworthy test baseline, stable Docker
integration, and explicit handoff documentation.

## Depends on

- `docs/planning/phases/phase-7/README.md`
- `docs/planning/phases/phase-7/block-b-reviewer-flows.md`
- `docs/planning/phases/phase-5/README.md`

## Tasks

- [x] P7-025: decide the minimum frontend test levels for this phase
- [x] P7-026: add tests for the frontend API client success and failure paths
- [x] P7-027: add tests for the main state rendering and interaction wiring
- [x] P7-028: add at least one smoke-like frontend flow that proves the reviewer path works end to end
- [x] P7-029: add the frontend service to `docker-compose.yml` with a stable local access port
- [x] P7-030: add `make` targets for the frontend reviewer workflow
- [x] P7-031: keep the backend bootstrap and review flow compatible with the new frontend service
- [x] P7-032: document how a reviewer should start, open, and use the frontend UI
- [x] P7-033: document the frontend technology choices and any deliberate constraints
- [x] P7-034: run the final phase gate and record any intentionally deferred frontend enhancements

## Test strategy note

The goal is not to build a huge frontend testing pyramid.

The useful baseline for this phase is:

- API-client protection
- DOM or component-level interaction coverage
- one reviewer-oriented smoke path

That baseline is now implemented through:

- `frontend/reviewer-ui/src/api/machineApiClient.test.ts`
- `frontend/reviewer-ui/src/app/reviewerApp.test.ts`
- `make ui-test`
- `make ui-build`
- the updated `make review` flow

## Output contract

The block is expected to leave behind:

- a tested frontend baseline
- Docker and `make` commands that include the UI cleanly
- reviewer documentation that covers both API and visual usage
- a final validation snapshot for the optional frontend follow-up

## Validation snapshot

Validated in this block:

- frontend unit and DOM tests: successful
- frontend production build: successful
- `make bootstrap`: successful with the `frontend` service included
- `make review`: successful with backend and frontend checks together

## Exit condition

The reviewer UI is runnable, testable, documented, and integrated into the
existing repository workflow without destabilizing the backend delivery path.
