# vending-machine

This repository is intended to host a PHP backend solution for a vending machine technical challenge.

## Goal

Build a small but well-structured backend that models a vending machine, its inventory, its available change, and the purchase flow from coin insertion to product delivery and change return.

## Current scope

The first delivery is backend-only and should focus on:

- correct vending machine behavior
- clear domain modeling
- automated tests
- reproducible execution with Docker
- concise documentation for reviewers

## Recommended implementation direction

- Model money as integer cents, not floating point values.
- Keep the business rules independent from the delivery mechanism.
- Start with a domain-first design and add a thin interface later.
- Treat service operations as explicit administrative actions.
- Prefer a complete and polished MVP over a broad but shallow implementation.

## Documentation map

- `docs/challenges/backend/README.md`: backend challenge brief, extracted requirements, assumptions, and design guidance
- `docs/planning/backend-implementation-plan.md`: implementation roadmap for the backend

## Delivery expectations

The exercise explicitly points toward:

- PHP as the implementation language
- Docker support as a strong plus
- automated tests as an expected quality signal
- a public-ready repository with a clear README and no company references

## Repository status

At the moment this repository contains the project brief and the backend implementation plan. The next step is to bootstrap the PHP project structure and start with the vending machine domain model.
