# Reviewer UI

This frontend is a small reviewer-facing companion for the vending machine API.

## Purpose

- expose the current vending machine flows through a visual interface
- keep the raw request and response payloads visible
- stay thin on top of the existing backend API

## Technology choices

- `Vite`: fast local development server and simple build path
- `TypeScript`: explicit API contracts and safer UI wiring
- plain DOM rendering: enough for this size without pulling a heavy framework
- `Vitest` with `jsdom`: enough coverage for API client behavior and the main
  reviewer interaction flow

## Deliberate constraints

- no heavy monorepo tooling
- no frontend framework that would dominate the exercise
- no duplication of business rules from the backend

The backend remains the single source of truth. The frontend only orchestrates
calls, renders the current snapshot, and surfaces the latest HTTP exchange.
