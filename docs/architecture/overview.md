# Architecture Overview

This project is a small vending machine backend built to demonstrate clear
domain modeling, use-case orchestration, infrastructure adapters, and automated
quality gates.

## Shape

```text
HTTP / CLI
   -> Infrastructure
      -> Application
         -> Domain
      -> Persistence adapter
```

Dependency rule:

- Domain does not depend on Application or Infrastructure.
- Application depends on Domain and defines use-case boundaries.
- Infrastructure depends inward and adapts Symfony, MongoDB, and JSON.

## Main Decisions

## Money

Money is modeled as integer cents inside Application and Domain to avoid
floating-point precision issues during balance checks, price comparisons, and
change allocation.

The HTTP API exposes `coins` only at the boundary for reviewer readability.

## Domain

The `Machine` aggregate owns:

- product stock
- inserted customer coins
- available change
- purchase/refund/service behavior

Machine-specific value objects live under `Domain/Machine/ValueObject`.

## Application

The Application layer exposes explicit commands, queries, handlers, results,
failures, and repository ports. It orchestrates use cases without knowing about
Symfony, MongoDB, or HTTP JSON.

## Persistence

The machine aggregate is persisted as one MongoDB document. The repository port
hides storage details from Application, and the MongoDB adapter maps documents
to domain objects at the infrastructure boundary.

## HTTP Boundary

The controller stays thin. Request mapping, transport validation, response
presentation, and HTTP status mapping are separated into infrastructure classes.

Detailed HTTP boundary trade-offs live in `http-api-boundary.md`.

## Portfolio Context

This repository intentionally keeps some architecture and planning notes visible
because it is a portfolio sample. In a regular product repository, some of that
context would usually live in ADRs, an internal wiki, or issue tracker.
