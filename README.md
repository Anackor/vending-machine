# Vending Machine

Small PHP/Symfony backend that models a vending machine with DDD-style domain
modeling, hexagonal boundaries, MongoDB persistence, automated quality gates,
and a lightweight reviewer UI.

## Highlights

- Domain-first vending machine model
- Application use cases isolated behind commands, queries, handlers, and results
- Symfony HTTP adapter kept outside the core
- MongoDB persistence hidden behind a repository port
- Integer-cents money model with reviewer-friendly `coins` at the HTTP boundary
- Machine-specific value objects for ids, selectors, coins, money, stock, and product names
- Unit tests, integration tests, static analysis, architecture checks, and coverage gate
- Docker-first workflow with no required local PHP, Composer, Symfony CLI, or MongoDB install
- Optional reviewer UI for exercising the same API visually

## Quick Start

```bash
make bootstrap
```

After bootstrap:

- HTTP API: `http://localhost:8000/api/machine`
- Reviewer UI: `http://localhost:4173`

## Validation

```bash
make quality
make test
make coverage
```

Reviewer handoff check:

```bash
make review
```

## Architecture

```text
HTTP / CLI
   -> Infrastructure
      -> Application
         -> Domain
      -> Persistence adapter
```

Core rule: Domain and Application do not depend on Symfony, MongoDB, or JSON.

## API

Main endpoints:

- `GET /api/machine`
- `POST /api/machine/insert-coin`
- `POST /api/machine/select-product`
- `POST /api/machine/return-coin`
- `POST /api/machine/service`

Detailed examples: `docs/api/http-examples.md`

## Documentation

Start with `docs/README.md`.

Focused guides:

- `docs/reviewer-guide.md`: run and review the project locally
- `docs/api/http-examples.md`: HTTP endpoints and curl examples
- `docs/development/workflow.md`: Makefile commands, tests, coverage, and CI
- `docs/architecture/overview.md`: system shape and main decisions
- `docs/architecture/http-api-boundary.md`: HTTP boundary design notes and SOLID/DDD/hexagonal trade-offs
- `docs/planning/portfolio-backlog.md`: public portfolio polish backlog

## Portfolio Note

This repository intentionally keeps some planning and architecture notes visible
because it is meant to show both implementation and decision-making. Some
classes also include short `Tip:` docblocks as learning signposts for reviewers
who open files directly in GitHub. In a product team, that context would usually
move to ADRs, onboarding docs, an internal wiki, or PR discussion once the
patterns are familiar.
