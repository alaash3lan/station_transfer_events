# Station Transfer Events Service

A service that ingests station transfer events (idempotent, concurrency-safe) and exposes a reconciliation summary per station.

## Tech Stack

- **PHP 8.4** / **Laravel 13**
- **SQLite** (lightweight, file-based — swappable via repository interface)
- **Docker** (multi-stage build, Alpine-based)
- **PHPUnit 12** for testing

## Requirements

### Local
- PHP 8.4+ with extensions: `pdo_sqlite`, `pcntl`
- Composer 2

### Docker
- Docker & Docker Compose v2

---

## How to Run

### Local

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
make run
```

### Docker

```bash
docker compose up --build
```

The app starts on **http://localhost:8000**. Migrations run automatically on container start.

---

## How to Run Tests

### Local

```bash
make test
```

### Docker

```bash
docker compose run --rm app php artisan test
```

---

## API Examples

### POST /api/transfers

Ingest a batch of transfer events:

```bash
curl -X POST http://localhost:8000/api/transfers \
  -H "Content-Type: application/json" \
  -d '{
    "events": [
      {
        "event_id": "E1",
        "station_id": "S1",
        "amount": 100.50,
        "status": "approved",
        "created_at": "2026-02-19T10:00:00Z"
      },
      {
        "event_id": "E2",
        "station_id": "S1",
        "amount": 200.00,
        "status": "pending",
        "created_at": "2026-02-19T11:00:00Z"
      }
    ]
  }'
```

Response (201):
```json
{
  "inserted": 2,
  "duplicates": 0
}
```

### GET /api/stations/{stationId}/summary

Get reconciliation summary for a station:

```bash
curl http://localhost:8000/api/stations/S1/summary
```

Response (200):
```json
{
  "station_id": "S1",
  "total_approved_amount": 100.5,
  "events_count": 2
}
```

---

## Design Notes

### Architecture

The project follows a **layered architecture** with clear separation of concerns:

```
app/
├── Domain/Transfer/          # DTOs, interfaces (zero framework dependencies)
├── Application/Transfer/     # Action classes (orchestration + logging)
├── Infrastructure/Transfer/  # SQLite repository implementation
├── Http/                     # Controllers, FormRequests (thin HTTP layer)
└── Models/                   # Eloquent model
```

**Why this structure:** The domain layer has no framework imports. The repository interface (`TransferEventRepositoryInterface`) is the architectural boundary — swap the implementation in `AppServiceProvider` to switch from SQLite to Postgres, MySQL, or an in-memory store without touching any other layer.

### Idempotency Strategy

- **Mechanism:** `UNIQUE` constraint on `event_id` column + `INSERT OR IGNORE` (via Laravel's `insertOrIgnore()`)
- **Behavior:** If an event with the same `event_id` already exists, the insert is silently skipped — no error, no overwrite
- **Why:** Database-level enforcement is simpler and more reliable than application-level checks. The unique constraint is the single source of truth for deduplication

### Concurrency Strategy

- **Mechanism:** Same `UNIQUE` constraint + `INSERT OR IGNORE`
- **How it works:** SQLite serializes all writes with a single-writer lock. Even if two concurrent requests arrive with the same `event_id`, the unique constraint prevents double-insertion at the database level. No application-level mutex or locking is needed
- **Portability:** This pattern maps directly to `ON CONFLICT DO NOTHING` (PostgreSQL) or `INSERT IGNORE` (MySQL) — the strategy is database-agnostic

### Error Handling: Fail-Fast

- **Choice:** If any event in the batch fails validation, the **entire batch is rejected** with a 422 response
- **Why:** Partial accept creates ambiguity (which succeeded? which failed?) and requires a more complex response schema. Fail-fast is predictable, and since the endpoint is idempotent, the caller can fix invalid events and safely retry the whole batch

### events_count

- **Choice:** `events_count` includes events of **all statuses**, not just "approved"
- **Why:** `events_count` and `total_approved_amount` serve different purposes. The count gives visibility into total ingestion activity for a station. The amount filters to approved events only. This provides a more complete reconciliation picture

### Batch Processing

- Events are inserted in **chunks of 100** within a database transaction to avoid SQLite's 999-variable binding limit (5 columns x 100 rows = 500 bindings per statement)
- Maximum batch size is **1000 events** per request (enforced by validation)

---

## OpenAPI Specification

Available at [`docs/openapi.yaml`](docs/openapi.yaml). Import into Swagger Editor, Postman, or any OpenAPI-compatible tool.

---

## Running Contract

| Action | Local | Docker |
|--------|-------|--------|
| Run server | `make run` | `docker compose up --build` |
| Run tests | `make test` | `docker compose run --rm app php artisan test` |
| Run migrations | `make migrate` | Automatic on container start |
| Fresh migrations | `make fresh` | — |
