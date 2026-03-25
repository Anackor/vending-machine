# Phase 0: Platform Bootstrap

## Objective

Create the engineering foundation that allows backend development to start immediately with Symfony, Docker, MongoDB, architectural boundaries, and quality tooling already in place.

## Scope

- latest stable Symfony version
- Docker-based local environment
- MongoDB container and connectivity
- Hexagonal Architecture baseline
- DDD-oriented project structure
- SOLID-driven design constraints
- PHPStan, deptrac, Rector, and ECS
- repeatable local developer workflow

## Inputs

- `README.md`
- `docs/challenges/backend/README.md`

## Tasks

### Stack baseline

- choose the PHP version compatible with the latest stable Symfony version
- choose the MongoDB image version
- define required PHP extensions
- define the Composer and Symfony CLI strategy for local development

### Docker environment

- create the application container
- create the MongoDB container
- define networking between services
- define mounted volumes for development
- decide whether separate development and production Docker files are needed now or later

### Symfony bootstrap

- initialize the project with the latest stable Symfony version
- configure local environment variables
- ensure Symfony commands can run inside the container
- prepare the initial source and configuration layout

### Architecture foundation

- define domain, application, and infrastructure boundaries
- make framework code depend on the core, not the reverse
- define where Symfony adapters live
- document the first dependency rules

### DDD structure

- define the bounded context naming
- define the first module structure for the vending machine domain
- define naming conventions for aggregates, value objects, repositories, domain services, and application services

### MongoDB foundation

- choose the Symfony-compatible MongoDB integration approach
- define the database connection configuration
- decide how machine state and inventory state will be stored
- create the first persistence structure aligned with ports and adapters

### Quality toolchain

- install PHPStan
- install deptrac
- install Rector
- install ECS
- define the first executable rules and configs

### Developer workflow

- define Composer scripts for setup, test, lint, and analysis
- define the standard boot and shutdown flow for the environment
- decide whether a `Makefile` improves the workflow
- document the minimum local prerequisites

## Validations

### Stack validation

- Symfony installs on the selected PHP version
- required PHP extensions are available in the application container

### Docker validation

- application container starts successfully
- MongoDB container starts successfully
- services can communicate over the configured network
- source changes are visible during development

### Symfony validation

- the Symfony application boots without errors
- environment variables resolve correctly
- Symfony console commands run from the container

### Architecture validation

- the source tree reflects Hexagonal Architecture clearly
- the domain has no dependency on Symfony or MongoDB code
- deptrac detects forbidden dependencies when rules are violated

### MongoDB validation

- the application can connect to MongoDB
- a simple smoke test can write and read data
- the chosen persistence direction supports the intended domain model

### Quality validation

- PHPStan runs successfully
- deptrac runs successfully
- Rector runs in dry-run mode
- ECS can check formatting successfully

### Workflow validation

- the project can be started with documented commands only
- the lint and analysis workflow is documented and runnable
- a new developer can reach a working local environment without hidden steps

## Deliverables

- Symfony project bootstrapped on the latest stable version
- Docker environment for the application and MongoDB
- initial Hexagonal and DDD-oriented project structure
- configured PHPStan, deptrac, Rector, and ECS
- documented local developer workflow

## Exit criteria

This phase is complete when the platform is stable enough to begin domain implementation without revisiting setup decisions immediately.
