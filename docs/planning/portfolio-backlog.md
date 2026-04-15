# Portfolio Backlog

This backlog collects improvements that make the repository easier to review as
a portfolio project. Some planning and handoff documents are intentionally kept
in the repository to show decision-making, architecture evolution, and delivery
discipline in a public sample. In a normal product repository, some of these
notes could live in an internal tracker instead.

## Reviewer Experience

- Make the root `README.md` more recruiter- and tech-lead-friendly:
  - lead with the value proposition and architecture snapshot
  - add CI/status badges
  - add a screenshot of the reviewer UI
  - move deep planning details behind links
- Fix `make review` so it is reproducible from a fresh checkout or clearly split
  it into `review` and `review-full`.
- Add a formal OpenAPI contract for the HTTP API.

## Documentation Structure

- Separate documentation by audience:
  - portfolio overview
  - architecture decisions
  - API contract
  - development workflow
  - planning/history
- Add a short note in the README explaining that planning documents are kept on
  purpose because this repository is meant to demonstrate process as well as
  code.
- Remove or restore stale references to challenge documents that no longer
  exist in the repository.

## Delivery Signals

- Extend CI beyond pull requests when useful:
  - run the quality baseline on pushes to `master`
  - add integration tests with MongoDB as a CI service
  - add frontend test/build checks when the UI changes
- Add a pull request template with quality and architecture checklists.
- Configure branch protection so failing checks cannot be merged accidentally.
