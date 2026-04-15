# HTTP API Boundary

This document explains the shape of the Symfony HTTP adapter after the request
and response boundary refactor. It is intentionally explicit because this
repository is used as a portfolio sample: the goal is to make the architectural
decisions visible without forcing reviewers to infer them from code alone.

## Boundary Flow

```text
HTTP Request
  -> MachineController
  -> JsonPayload
  -> request normalizers
  -> passive HTTP request DTO
  -> MachineJsonRequestMapper
  -> Application command/query
  -> Application handler
  -> Application result/failure
  -> JSON presenter
  -> MachineJsonResponder
  -> JsonResponse
```

The important rule is dependency direction:

- Infrastructure may depend on Application.
- Application must not depend on Symfony, HTTP, JSON, or MongoDB.
- Domain must not depend on Application or Infrastructure.

This follows the hexagonal architecture idea of keeping adapters outside the
core while letting adapters translate external protocols into use-case input.

## Controller

### `MachineController`

Role:

- Owns Symfony route attributes.
- Receives `Request` objects.
- Calls the request mapper.
- Calls application handlers.
- Delegates response creation to the responder.

Why this shape:

- It keeps the controller thin without making it a pass-through script that
  still hides parsing and serialization details inline.
- It protects the application layer from Symfony types.
- It gives one visible adapter entry point for reviewers.

SOLID and architecture:

- SRP: route orchestration only.
- DIP: depends on boundary services instead of parsing details.
- Hexagonal: acts as an inbound adapter.

Possible discussion:

- "Could the controller receive commands directly through Symfony argument
  resolvers?"

Resolution:

- Yes, Symfony argument resolvers would be a standard option in a larger app.
  Here, explicit mapping is preferred because the project is small, easier to
  test without framework magic, and clearer as a portfolio exercise.

## Request Mapping

### `MachineJsonRequestMapper`

Role:

- Translates validated HTTP payloads into application commands and queries.
- Coordinates `JsonPayload`, request DTOs, and normalizers.

Why this shape:

- A mapper is a more precise name than factory because the class translates
  between two boundary models: JSON request data and application input.
- It keeps commands free from raw arrays and Symfony requests.

SOLID and architecture:

- SRP: mapping from HTTP request contract to application contract.
- DIP: Application remains independent of transport.
- Hexagonal: adapter translation is outside the use case.

Possible discussion:

- "Should each endpoint have its own mapper?"

Resolution:

- That would be valid if endpoint-specific logic grows. For this size, one
  mapper keeps the adapter discoverable. The DTOs and normalizers already split
  the responsibilities that are likely to change independently.

### `JsonPayload`

Role:

- Parses the raw request body.
- Ensures the JSON body is either empty or an object.
- Exposes typed accessors for required JSON fields.

Why this shape:

- JSON object validation is a transport concern, not an application rule.
- It avoids repeating low-level body parsing in every endpoint.
- It converts parser errors into `InvalidMachineJsonRequest`, so controllers do
  not catch generic JSON exceptions.

SOLID and architecture:

- SRP: low-level JSON object contract only.
- Hexagonal: external protocol validation stays in the adapter.

Possible discussion:

- "Why not use Symfony Validator or request objects with attributes?"

Resolution:

- Symfony Validator is a solid standard for richer APIs. This project keeps the
  validation explicit because there are few endpoints and the current rules are
  mostly structural. The design can move to Symfony Validator later without
  changing Application or Domain.

### `InsertCoinJsonRequest`

Role:

- Passive DTO for the normalized insert-coin HTTP request.
- Carries `coinCents` after transport normalization.

Why this shape:

- The DTO is deliberately passive. It does not parse payloads and does not know
  about Symfony.
- It documents the adapter-level request shape before creating
  `InsertCoinCommand`.

SOLID and architecture:

- SRP: data carrier for one HTTP request contract.
- ISP: no shared base request abstraction is introduced without need.

Possible discussion:

- "Is this DTO too small?"

Resolution:

- It is small, but it gives the mapper a named boundary object and keeps future
  endpoint changes local. In a tiny script it could be skipped; in a portfolio
  architecture sample, explicit contracts are a useful signal.

### `SelectProductJsonRequest`

Role:

- Passive DTO for the product-selection HTTP request.
- Carries the raw selector string after the JSON field has been checked.

Why this shape:

- The HTTP adapter validates that the field exists and is a string.
- The domain/application path still validates selector semantics through the
  `Selector` value object.

SOLID and architecture:

- SRP: transport shape only.
- DDD: business meaning stays in the domain value object.

Possible discussion:

- "Why not normalize the selector here?"

Resolution:

- The selector is a domain concept. The HTTP DTO checks transport type; the
  domain value object owns semantic normalization and validation.

### `ServiceMachineJsonRequest`

Role:

- Passive DTO for the service-machine HTTP request.
- Carries product quantities and available change counts after JSON object
  validation and denomination key normalization.

Why this shape:

- The service endpoint has the richest payload shape, so naming it keeps the
  mapper readable.
- JSON denomination keys such as `"0.25"` are adapter concerns.

SOLID and architecture:

- SRP: transport-level service request contract.
- DDD: stock and change invariants remain in Application/Domain.
- Hexagonal: external JSON key formats do not leak inward.

Possible discussion:

- "Should this be an Application DTO instead?"

Resolution:

- No, because the JSON shape is not the use-case contract. The Application
  command should receive normalized data and value objects, not reviewer-facing
  JSON key formats.

### `CoinInputNormalizer`

Role:

- Converts reviewer-facing coin literals into integer cents.
- Supports compatibility with legacy cent-based inputs.
- Normalizes coin-count object keys for service requests.

Why this shape:

- Money in the core is integer cents.
- The HTTP API accepts reviewer-friendly coin values such as `0.25`.
- This class isolates the translation between those two representations.

SOLID and architecture:

- SRP: transport money representation normalization.
- DDD: core money rules remain in `Money`, `Coin`, and coin inventories.
- Hexagonal: adapter-specific decimal formatting stays outside the core.

Possible discussion:

- "Should coin parsing live in a domain value object?"

Resolution:

- Supported denominations are domain knowledge, but decimal JSON literals are
  not. The domain already validates denominations through `Coin`. This class
  only translates HTTP literals into cents before the domain rule is applied.

Possible discussion:

- "Should this be split into inserted-coin and coin-count normalizers?"

Resolution:

- That split is reasonable if the class grows. Today both operations normalize
  the same HTTP money representation, so keeping them together avoids a
  premature abstraction.

## Request Errors

### `InvalidMachineJsonRequest`

Role:

- Represents invalid HTTP JSON input before Application is called.

Why this shape:

- The controller should not catch broad `InvalidArgumentException` or
  `JsonException`, because those can come from unrelated code paths.
- A transport-specific exception makes the boundary explicit.

SOLID and architecture:

- SRP: one error category for HTTP request contract violations.
- Hexagonal: adapter errors are separated from application failures.

Possible discussion:

- "Should invalid requests be represented as Application failures?"

Resolution:

- No. Invalid JSON is not a machine use-case failure. It is an adapter contract
  problem and should be handled before invoking Application.

## Response Presentation

### `MachineJsonResponder`

Role:

- Builds Symfony `JsonResponse` objects.
- Owns HTTP status mapping.
- Delegates payload shape to presenters.

Why this shape:

- Presenters return arrays; the responder wraps them in Symfony responses.
- This keeps Symfony-specific response objects out of presenters.

SOLID and architecture:

- SRP: HTTP response construction and status mapping.
- Hexagonal: outbound HTTP adapter concern.

Possible discussion:

- "Why not have presenters return `JsonResponse` directly?"

Resolution:

- Returning arrays keeps presenters framework-light and focused on payload
  shape. `JsonResponse` belongs to the Symfony responder.

### `CoinJsonPresenter`

Role:

- Converts integer cents to reviewer-facing coin values and coin-count keys.

Why this shape:

- API responses should use readable coin values.
- Application and Domain should keep integer cents.

SOLID and architecture:

- SRP: one reusable output transformation.
- DDD: presentation formatting does not change the core money model.

Possible discussion:

- "Does returning floats reintroduce money precision problems?"

Resolution:

- No business logic uses these floats. They are presentation values at the HTTP
  boundary after all calculations have completed in integer cents.

### `MachineSnapshotJsonPresenter`

Role:

- Presents the application `MachineSnapshot` as the stable JSON response shape.

Why this shape:

- Application snapshots are use-case read models, not JSON contracts.
- The presenter lets HTTP expose names such as `priceCoins` while Application
  keeps `priceCents`.

SOLID and architecture:

- SRP: one result type to one JSON shape.
- Hexagonal: maps internal use-case output to adapter output.

Possible discussion:

- "Could `MachineSnapshot` implement `JsonSerializable`?"

Resolution:

- That would couple Application to one adapter's serialization format. The
  presenter keeps Application transport-neutral.

### `MachineFailureJsonPresenter`

Role:

- Presents application failures as JSON errors.
- Converts structured context fields ending in `Cents` to `Coins`.

Why this shape:

- Application failures are transport-neutral.
- HTTP clients should not see cent-based internals when the public contract uses
  coin values.
- Messages are no longer parsed or rewritten; money amounts must arrive through
  structured context.

SOLID and architecture:

- SRP: failure payload presentation only.
- DDD: failure meaning stays in Application; JSON field naming stays in
  Infrastructure.

Possible discussion:

- "Why not let the presenter parse monetary values from messages?"

Resolution:

- Parsing messages is brittle and not a standard boundary pattern. Structured
  context is clearer, testable, and avoids hidden coupling to message wording.

Possible discussion:

- "Should there be one presenter per failure code?"

Resolution:

- That would be useful if failure payloads diverge significantly. Today the
  shape is uniform: code, message, and context. A single presenter is simpler
  and still explicit.

## Application Failure Context

Application handlers add structured context when a domain exception does not
carry all data needed by adapters. For example, product selection failures can
include:

- `selector`
- `insertedBalanceCents`
- `productPriceCents`
- `requiredChangeCents`

This keeps the domain exceptions simple while avoiding string parsing in the
HTTP adapter.

Possible discussion:

- "Should domain exceptions carry typed context instead?"

Resolution:

- That is a valid future improvement if domain failures become richer. For the
  current scope, handlers already know the use-case context and can enrich the
  application failure without making domain exceptions stateful.

## Summary

The current boundary is intentionally conventional:

- controllers orchestrate
- mappers translate input
- passive DTOs document request contracts
- normalizers handle adapter-specific representation changes
- application commands and results stay transport-neutral
- presenters translate application output to JSON
- responder owns Symfony responses and HTTP status codes

This gives a thin controller without pushing HTTP concerns inward, while still
avoiding framework-heavy abstractions that would be disproportionate for the
size of the project.
