# Great Food PHP Client
A minimal, framework‑free PHP client library for interacting with the fictional **Great Food Ltd REST API**.
This project demonstrates:

- API authentication using a client‑credentials token
- Fetching menus and products
- Parsing API responses
- Updating a product
- Running locally via Docker

## AI Disclosure
I used AI (**M365 Copilot**) **only** to help draft this README and to quickly scaffold the initial project structure (folders, boilerplate files).
**All application logic, adaptations to real API responses, Docker/Swarm setup, tests, and examples were written and validated by me.**

## Features
- Pure PHP 8.1+
- Token-based authentication
- Menu and product retrieval
- Simple architecture
- Example scripts for test scenarios
- Docker dev environment

## Project Structure
```
src/
  Api/
  Auth/
  Exceptions/
  Http/
examples/
  scenario1.php
  scenario2.php
tests/
  fixtures/
Dockerfile
docker-compose.dev.yml
stack.yml
README.md
```

## Requirements
- PHP 8.1+
- Composer
- Docker + Docker Compose
- Docker Swarm (optional)

# Running the Project Locally

## Option 1 — Docker Compose (Development)

Rename env.example to .env and fill with your values
For real api provide BASE_API_URL otherwise keep it blank to use mocked

```bash
docker compose up -d --build
docker stack deploy -c ./stack.yml greatfood
docker exec -it greatfood-php composer install
```

Run examples:
```bash
docker exec -it greatfood-php php examples/scenario1.php
docker exec -it greatfood-php php examples/scenario2.php
```

Run tests:
```bash
docker exec -it greatfood-php ./vendor/bin/phpunit
```
---

## How This Could Be Improved With More Time
If I had more time, I would:

1. **Response handling & validation**
   - Centralize and strengthen error handling for all endpoints
   - Validate response shapes (e.g., JSON Schema) to detect API changes early

2. **DTOs for entities**
   - Introduce typed DTOs (e.g., `Menu`, `Product`) for safer, self‑documenting data flow

3. **Logging**
   - Add structured logging (PSR‑3) for observability

4. **Distribute as a Composer package**
   - Extract the client into a reusable Composer package

5. **Support alternative HTTP transports**
   - Instead of relying solely on cURL, the HTTP layer could be abstracted further to allow plugging in different transports (e.g., Guzzle, Symfony HttpClient).
---