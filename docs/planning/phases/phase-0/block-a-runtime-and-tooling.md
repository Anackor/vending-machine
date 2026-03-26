# Phase 0 / Block A: Runtime and Tooling Decisions

## Purpose

Freeze the runtime, image, extension, and local-tooling decisions that every other Phase 0 block depends on.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `README.md`
- `docs/challenges/backend/README.md`

## Tasks

- [x] P0-001: target Symfony `8.0.x`; as of 2026-03-26 the latest stable release is `8.0.7`
- [x] P0-002: use PHP `8.4` because Symfony `8.0` requires PHP `8.4.0` or higher
- [x] P0-003: use `php:8.4-cli-bookworm` as the local application base image for Phase 0
- [x] P0-004: use `mongo:8.0.19-noble` as the local MongoDB image for Phase 0
- [x] P0-005: require the Symfony baseline extensions `ctype`, `iconv`, `json`, `pcre`, `session`, `simplexml`, and `tokenizer`; include `intl`, `mbstring`, `opcache`, and `zip` as project-level development essentials
- [x] P0-006: require the `mongodb` PHP extension for the selected MongoDB integration direction
- [x] P0-007: run Composer inside Docker only; do not require host-level Composer
- [x] P0-008: do not require Symfony CLI locally; standardize on `bin/console` inside the application container
- [x] P0-009: postpone production-oriented Docker files and image hardening to Phase 6 unless a later phase creates an earlier delivery need
- [x] P0-010: record the selected versions and bootstrap decisions in committed documentation

## Frozen decisions

Frozen on 2026-03-26:

- Symfony target: `8.0.*`
- PHP runtime target: `8.4`
- PHP application image: `php:8.4-cli-bookworm`
- MongoDB image: `mongo:8.0.19-noble`
- Composer strategy: install and run Composer inside the application container only
- Symfony CLI strategy: not required locally for Phase 0
- Docker delivery strategy: one development-oriented Docker path now; production-oriented Docker work later
- Workflow direction: adopt a `Makefile` in Block H as the preferred entry point for recurring local commands

## Output contract

Subsequent blocks must assume:

- PHP `8.4`
- Symfony `8.0.*`
- a Docker-first local workflow
- no dependency on host Composer or host Symfony CLI

## Exit condition

Runtime versions, extension list, and local-tooling strategy are frozen.
