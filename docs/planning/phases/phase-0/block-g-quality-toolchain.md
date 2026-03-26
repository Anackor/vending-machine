# Phase 0 / Block G: Quality Toolchain Bootstrap

## Purpose

Install and wire the baseline static-analysis and code-quality tools that will enforce architecture and maintenance rules from the start.

## Depends on

- `docs/planning/phases/phase-0/README.md`
- `docs/planning/phases/phase-0/block-d-architecture-and-namespace.md`

## Tasks

- [ ] P0-063: add PHPStan as a development dependency
- [ ] P0-064: add deptrac as a development dependency
- [ ] P0-065: add Rector as a development dependency
- [ ] P0-066: add ECS as a development dependency
- [ ] P0-067: create the initial PHPStan configuration file
- [ ] P0-068: set the first PHPStan level and analysed paths
- [ ] P0-069: create the initial deptrac configuration file
- [ ] P0-070: encode the first architecture layers and forbidden dependency rules in deptrac
- [ ] P0-071: create the initial Rector configuration file
- [ ] P0-072: select the first Rector rule sets that are safe for the project baseline
- [ ] P0-073: create the initial ECS configuration file
- [ ] P0-074: select the first coding-standard sets to enforce
- [ ] P0-075: run PHPStan successfully against the current codebase
- [ ] P0-076: run deptrac successfully against the current codebase
- [ ] P0-077: run Rector in dry-run mode successfully
- [ ] P0-078: run ECS in check mode successfully

## Output contract

The block is expected to leave behind:

- four runnable quality tools
- initial configuration files for each tool
- a first enforceable architecture rule set

## Exit condition

Every agreed quality tool is executable and already protects the empty or near-empty baseline.
