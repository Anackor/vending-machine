# HTTP API Examples

The HTTP adapter exposes a small JSON API on top of the application layer.
The core model uses integer cents; the public HTTP contract uses reviewer-facing
`coins` values such as `0.25` and `1`.

## Base URL

```text
http://localhost:8000/api/machine
```

## Endpoints

- `GET /api/machine`
- `POST /api/machine/insert-coin`
- `POST /api/machine/select-product`
- `POST /api/machine/return-coin`
- `POST /api/machine/service`

## Inspect Machine State

```bash
curl http://localhost:8000/api/machine
```

## Insert Coin

Preferred reviewer-facing payload:

```bash
curl -X POST http://localhost:8000/api/machine/insert-coin \
  -H "Content-Type: application/json" \
  -d '{"coins":1}'
```

Compatibility payload:

```bash
curl -X POST http://localhost:8000/api/machine/insert-coin \
  -H "Content-Type: application/json" \
  -d '{"coinCents":100}'
```

Accepted `coins` values:

- `0.05`
- `0.10`
- `0.25`
- `1`

Near matches such as `0.249` are rejected instead of rounded.

## Select Product

```bash
curl -X POST http://localhost:8000/api/machine/select-product \
  -H "Content-Type: application/json" \
  -d '{"selector":"water"}'
```

## Return Inserted Money

```bash
curl -X POST http://localhost:8000/api/machine/return-coin
```

## Service Machine

```bash
curl -X POST http://localhost:8000/api/machine/service \
  -H "Content-Type: application/json" \
  -d '{
    "productQuantities":{"water":10,"juice":8,"soda":5},
    "availableChangeCounts":{"0.05":20,"0.10":20,"0.25":20,"1":10}
  }'
```

## Error Shape

Errors use a stable object:

```json
{
  "error": {
    "code": "invalid_request",
    "message": "Field \"coins\" must be one of 0.05, 0.10, 0.25, or 1.",
    "context": {}
  }
}
```

Application failures include structured context where useful. The HTTP
presenter converts `*Cents` context fields to `*Coins` in responses.
