# Phase 2: Application Layer and Ports

## Objective

Expose the domain model through use cases and ports so infrastructure can depend on stable contracts instead of coupling directly to business objects.

## Scope

- application services or command handlers
- input and output models
- persistence-facing ports
- clear separation between domain failures and infrastructure concerns

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`
- `docs/planning/phases/phase-1-domain-design-and-core-model.md`

## Tasks

- define use cases for inserting coins, refunding, selecting products, and servicing the machine
- define stable input models for each use case
- define stable output or result models for each use case
- define repository ports needed by the application layer
- define error translation boundaries between domain and infrastructure
- keep orchestration logic in the application layer when it does not belong in the domain

## Validations

- use cases can orchestrate the domain without infrastructure details
- ports are sufficient for persistence and adapters
- outputs are stable enough for future interfaces
- business failures remain understandable outside the domain layer

## Deliverables

- application services
- repository and infrastructure-facing ports
- stable input and output contracts

## Exit criteria

This phase is complete when upper layers can invoke business behavior through application contracts without direct knowledge of internal domain implementation details.
