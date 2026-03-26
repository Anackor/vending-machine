# Phase 0 / Block C: Symfony Project Bootstrap

## Purpose

Initialize the Symfony project inside the Docker-based development environment and make its basic runtime paths operable.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-a-runtime-and-tooling.md`
- `docs/planning/phases/phase-0/block-b-docker-workspace.md`

## Tasks

- [ ] P0-025: initialize the repository as a Symfony project on the selected stable version
- [ ] P0-026: commit or document the exact bootstrap command needed to recreate the Symfony skeleton
- [ ] P0-027: verify `composer.json`, `composer.lock`, and Symfony baseline files are present
- [ ] P0-028: configure `.env` defaults needed for containerized local execution
- [ ] P0-029: define the strategy for local overrides in `.env.local` without committing secrets
- [ ] P0-030: ensure `var/` and cache directories are writable inside the container
- [ ] P0-031: run a basic Symfony console command inside the application container
- [ ] P0-032: run a basic bootstrap check to confirm the Symfony kernel starts without errors
- [ ] P0-033: confirm that source-code changes are visible immediately through the bind mount
- [ ] P0-034: decide whether the first runnable interface is the built-in server, PHP-FPM, or console-only for this phase

## Output contract

The block is expected to leave behind:

- a Symfony skeleton in the repository root
- environment defaults aligned with the Docker stack
- a runnable `bin/console` baseline

## Exit condition

Symfony boots successfully inside Docker and can be operated with documented commands only.
