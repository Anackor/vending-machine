# Reviewer Guide

This guide is for someone who wants to run the project and exercise the vending
machine behavior without installing local PHP, Composer, Symfony CLI, or MongoDB.

## Prerequisites

- Docker Desktop with Docker Compose support
- GNU Make
- a terminal capable of running `docker compose` and `make`

## First Run

```bash
make bootstrap
```

This command:

- builds the application image
- starts the Docker environment
- installs Composer dependencies inside the `app` container
- runs setup checks
- resets the default machine to the reviewer baseline

After bootstrap:

- HTTP API: `http://localhost:8000/api/machine`
- Reviewer UI: `http://localhost:4173`

## Recommended Review Path

1. Bootstrap the environment:

```bash
make bootstrap
```

2. Confirm containers are running:

```bash
make status
```

3. Open the reviewer UI:

```text
http://localhost:4173
```

4. Exercise the machine:

- inspect the current snapshot
- insert supported coins
- buy products
- return inserted money
- service the machine
- inspect the latest request and response payloads

5. Run the final validation:

```bash
make review
```

## Reviewer Baseline

The default machine uses:

- `water`: `65` cents, selector `water`
- `juice`: `100` cents, selector `juice`
- `soda`: `150` cents, selector `soda`

Supported HTTP coin values:

- `0.05`
- `0.10`
- `0.25`
- `1`

The core stores money in integer cents. The HTTP adapter exposes reviewer-facing
`coins` values only at the boundary.

## Delivery Notes

The Docker development workflow is also the delivery workflow:

- no local PHP installation is required
- no local Composer installation is required
- no local MongoDB installation is required
- no hidden seed or setup steps are required
