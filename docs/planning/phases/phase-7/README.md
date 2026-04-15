# Phase 7: Reviewer UI Monorepo Follow-up

## Objective

Add a small, reviewer-friendly frontend inside the same repository so the
vending machine API can be exercised visually through a pleasant local UI.

## Scope

- lightweight monorepo-style frontend folder inside the existing repository
- Dockerized frontend service wired into the current local stack
- visual flows for all reviewer-facing API operations
- raw request and response inspection for reviewer clarity
- frontend-focused tests and handoff documentation

## Inputs

- `README.md`
- `docs/README.md`
- `docs/planning/phases/phase-4/README.md`
- `docs/planning/phases/phase-5/README.md`
- `docs/planning/phases/phase-6-packaging-and-delivery.md`
- `docker-compose.yml`
- `Makefile`

## Canonical structure

This `README.md` is the canonical index for Phase 7.

Use it to understand:

- the phase objective and simplified scope
- the execution order between blocks
- the status of each block
- the expected reviewer-facing outcome

Use the block files for task-level execution and block-local decisions:

- `block-a-frontend-foundation.md`
- `block-b-reviewer-flows.md`
- `block-c-ui-tests-docker-and-handoff.md`

## Planning note

This phase is intentionally not a second full challenge inside the repository.

The `frontend/reviewer-ui/README.md` file captures the implemented UI scope,
technology choices, and reviewer intent. The goal stays intentionally simple:
build a small visual client for the existing vending machine API.

The implementation should therefore stay pragmatic:

- one frontend application
- one additional Docker service
- one clear reviewer entry point
- no heavy monorepo tooling such as Nx or Turborepo
- no framework choice that adds more ceremony than value

Estimated effort:

- difficulty: medium-low
- phases: 1
- blocks: 3
- atomic tasks: 34

## Recommended execution order

1. Block A: frontend foundation
2. Block B: reviewer flows
3. Block C: UI tests, Docker, and handoff

## Loading rule for Phase 7 work

For any Phase 7 task, always load:

- this `README.md`
- the Phase 7 block file you are actively executing

Load Phase 6 only when:

- the reviewer bootstrap path or final handoff rules must stay aligned
- Docker delivery expectations must remain unchanged

Load Phase 4 only when:

- API routes, HTTP payloads, or reviewer-facing responses must be checked

Load Phase 5 only when:

- frontend tests, coverage, or CI parity decisions must align with the current
  project safety-net approach

Load `frontend/reviewer-ui/README.md` when:

- visual quality, UX expectations, or frontend testing goals need to stay aligned

## Block index

### A. Frontend foundation

- file: `docs/planning/phases/phase-7/block-a-frontend-foundation.md`
- task ids: `P7-001` to `P7-011`
- status: complete
- outputs: frontend app baseline, Docker wiring, API client contract, visual
  foundation

### B. Reviewer flows

- file: `docs/planning/phases/phase-7/block-b-reviewer-flows.md`
- task ids: `P7-012` to `P7-024`
- status: complete
- outputs: complete visual interaction flows, response inspector, polished
  reviewer path

### C. UI tests, Docker, and handoff

- file: `docs/planning/phases/phase-7/block-c-ui-tests-docker-and-handoff.md`
- task ids: `P7-025` to `P7-034`
- status: complete
- outputs: frontend safety net, Dockerized local workflow, reviewer-facing UI
  documentation, final phase gate

## Mandatory Phase 7 behaviors

- show the current machine state without asking the reviewer to use `curl`
- expose all Phase 4 API capabilities through a visual interface
- make it easy to understand both the human-friendly result and the raw API
  payload
- keep the frontend as a thin client over the existing API, not a second source
  of business logic
- fit naturally into the existing Docker and `make` workflow

## Phase-wide validations

- the frontend can be bootstrapped from the same repository without hidden
  local setup steps
- the reviewer can exercise insert, select, refund, and service flows visually
- the raw API payloads remain inspectable for debugging and challenge review
- frontend tests protect the API client and main UI flows
- the new UI does not force backend architecture changes unrelated to delivery

## Deliverables

- frontend application inside the repository
- Dockerized reviewer UI service
- visual API explorer for the vending machine flows
- frontend test baseline
- updated handoff documentation

## Validation snapshot

Validated at the end of Phase 7:

- `make bootstrap`: successful with `app`, `mongo`, and `frontend`
- `docker compose exec -T frontend npm run test`: successful with `6` tests
- `docker compose exec -T frontend npm run build`: successful
- `make test`: successful
- `make review`: successful
- `http://localhost:4173`: available from the local Dockerized stack

Phase 7 result summary:

- the repository now exposes a small visual reviewer UI in addition to the raw
  HTTP API
- the frontend remains a thin client over the existing backend contract
- reviewers can inspect both the friendly UI state and the exact latest API
  payloads

## Exit criteria

This phase is complete when a reviewer can bootstrap the repository and use a
small visual client to understand and test the vending machine API end to end.
