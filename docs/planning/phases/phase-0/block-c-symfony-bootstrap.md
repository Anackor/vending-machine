# Phase 0 / Block C: Symfony Project Bootstrap

## Purpose

Initialize the Symfony project inside the Docker-based development environment and make its basic runtime paths operable.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-a-runtime-and-tooling.md`
- `docs/planning/phases/phase-0/block-b-docker-workspace.md`

## Tasks

- [x] P0-025: initialize the repository as a Symfony project on the selected stable version
- [x] P0-026: commit or document the exact bootstrap command needed to recreate the Symfony skeleton
- [x] P0-027: verify `composer.json`, `composer.lock`, and Symfony baseline files are present
- [x] P0-028: configure `.env` defaults needed for containerized local execution
- [x] P0-029: define the strategy for local overrides in `.env.local` without committing secrets
- [x] P0-030: ensure `var/` and cache directories are writable inside the container
- [x] P0-031: run a basic Symfony console command inside the application container
- [x] P0-032: run a basic bootstrap check to confirm the Symfony kernel starts without errors
- [x] P0-033: confirm that source-code changes are visible immediately through the bind mount
- [x] P0-034: decide whether the first runnable interface is the built-in server, PHP-FPM, or console-only for this phase

## Implementation notes

- the Symfony skeleton was created on 2026-03-26 inside the running `app` container using Composer
- to avoid conflicts with the non-empty repository root, the skeleton was first created in `/tmp/symfony-bootstrap` and then copied into `/app`
- the repository root `README.md` was intentionally preserved instead of replacing it with the default Symfony README
- `composer.json`, `composer.lock`, `symfony.lock`, `bin/console`, `config/`, `public/`, `src/`, `var/`, and `vendor/` are now present in the repository root
- `.env` now includes a deterministic development `APP_SECRET` for containerized local execution
- local machine-specific overrides should live in uncommitted `.env.local`; no committed secrets are required for this block
- the Phase 0 interface decision is `console-only` for now; no built-in web server or PHP-FPM path is required before later phases

## Bootstrap command reference

The bootstrap was executed from the `app` container with the following strategy:

1. create the Symfony skeleton in a temporary container path
2. copy the generated project files into `/app`
3. preserve the repository-level `README.md`

Equivalent command shape:

```sh
composer create-project symfony/skeleton:^8.0 /tmp/symfony-bootstrap
```

## Validation snapshot

Validated on 2026-03-26:

- `php bin/console about`: successful
- `php bin/console cache:clear --no-warmup`: successful
- Symfony kernel booted in `dev`
- `var/` was writable inside the container
- generated Symfony files were visible on the host through the bind mount

## Output contract

The block is expected to leave behind:

- a Symfony skeleton in the repository root
- environment defaults aligned with the Docker stack
- a runnable `bin/console` baseline

## Exit condition

Symfony boots successfully inside Docker and can be operated with documented commands only.
