<h1 align="center">MediVault Core</h1>
<p align="center">Medical Report Management API built with Laravel</p>
<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-red?style=flat-square&logo=laravel" />
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=flat-square&logo=php" />
  <img src="https://img.shields.io/badge/Database-SQLite%20%2B%20MongoDB-green?style=flat-square" />
  <img src="https://img.shields.io/badge/Cache-Redis-red?style=flat-square&logo=redis" />
  <img src="https://img.shields.io/badge/OCR-Docker%20Service-orange?style=flat-square" />
  <img src="https://img.shields.io/badge/License-MIT-yellow?style=flat-square" />
</p>

---

## Table of Contents

1. [Introduction](#introduction)
2. [Features](#features)
3. [Tech Stack](#tech-stack)
4. [Project Structure](#project-structure)
5. [Getting Started](#getting-started)
6. [Configuration](#configuration)
7. [Run The Application](#run-the-application)
8. [Authentication Model](#authentication-model)
9. [API Endpoints](#api-endpoints)
10. [Testing](#testing)
11. [Troubleshooting](#troubleshooting)
12. [Useful Commands](#useful-commands)
13. [License](#license)

---

## Introduction

MediVault Core is a backend API for managing patients and medical reports.
It supports:

- User registration and login
- Cookie-based access token validation
- Patient record CRUD
- Report upload, OCR extraction, and structured report storage

This project uses SQLite for relational entities (`users`, `patients`), MongoDB for report documents, and Redis for token session storage.

---

## Features

- User registration and login endpoints
- Cookie-based protected routes
- Patient CRUD operations
- Report upload (`jpg`, `jpeg`, `png`, `pdf`)
- OCR service integration
- Gemini API integration for OCR to structured JSON conversion
- Docker Compose for local dependencies (MongoDB, Redis, OCR)

---

## Tech Stack

| Tool | Description |
| --- | --- |
| [Laravel 12](https://laravel.com/docs/12.x) | API framework |
| [PHP 8.2+](https://www.php.net) | Runtime |
| [SQLite](https://www.sqlite.org) | Relational data (`users`, `patients`) |
| [MongoDB](https://www.mongodb.com) | Report document storage |
| [Redis](https://redis.io) | Access token state storage |
| [Docker Compose](https://docs.docker.com/compose/) | Local infra services |
| [Gemini API](https://ai.google.dev) | OCR text structuring |

---

## Project Structure

```bash
.
├── app
│   ├── DTOs
│   ├── Http
│   │   ├── Controllers
│   │   ├── Middleware
│   │   └── Resources
│   ├── Models
│   ├── Repositories
│   ├── Services
│   └── Providers
├── bootstrap
├── config
├── database
│   └── migrations
├── routes
│   ├── auth.php
│   ├── patient.php
│   └── report.php
├── docker-compose.yml
├── .env.example
└── README.md
```

---

## Getting Started

### 1. Clone The Repository

```bash
git clone <your-repo-url>
cd MediVault-Core-dev
```

### 2. Prerequisites

- PHP `8.2+`
- Composer `2+`
- Docker and Docker Compose
- PHP extension: `ext-mongodb`

Quick checks:

```bash
php -v
composer --version
php -m | grep -i mongodb
docker --version
docker compose version
```

### 3. Install PHP Dependencies

```bash
cp .env.example .env
composer install
```

### 4. Start Infrastructure Services (Docker)

This repository includes `docker-compose.yml` for:

- `mongodb` on `27017`
- `redis` on `6379`
- `ocr` on `8989`

```bash
docker compose up -d
```

OCR image notes:

- Default image is:
  `ghcr.io/yogesh-xcode/docker-images_healthai_medivault/ocr_service:tag`
- Replace `tag` with a valid image tag.
- Or override using:

```bash
export OCR_IMAGE=ghcr.io/yogesh-xcode/docker-images_healthai_medivault/ocr_service:<real-tag>
docker compose up -d
```

---

## Configuration

Set or verify these values in `.env`:

```env
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
DB_DATABASE=./database/database.sqlite

MONGODB_URI=mongodb://127.0.0.1:27017
MONGODB_DATABASE=medical_report_hub

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

OCR_MODEL_URL=http://127.0.0.1:8989

GEMINI_API_KEY=your_api_key_here
GEMINI_MODEL_URL=https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent
```

---

## Run The Application

### 1. Bootstrap Laravel

```bash
touch database/database.sqlite
php artisan key:generate
php artisan migrate
php artisan storage:link
```

### 2. Run API Server

```bash
php artisan serve
```

Base URL:

`http://127.0.0.1:8000`

---

## Authentication Model

- `POST /auth/login` sets an `access_token` cookie.
- Protected routes validate this cookie with `ValidateUserToken` middleware.

Protected route groups:

- `/auth/sec-check`
- `/patient/*`
- `/report/*`

---

## API Endpoints

Response format:

```json
{
  "data": {},
  "status": "success|error",
  "message": "..."
}
```

### Auth APIs

| Method | Endpoint | Description |
| --- | --- | --- |
| `POST` | `/auth/register` | Register user |
| `POST` | `/auth/login` | Login and set cookie |
| `GET` | `/auth/sec-check` | Validate cookie token |

#### `POST /auth/register`

Request body:

```json
{
  "user_id": "USR1001",
  "username": "doctor001",
  "email": "doctor001@example.com",
  "password": "StrongPass@123",
  "role": "doctor"
}
```

#### `POST /auth/login`

Request body:

```json
{
  "username": "doctor001",
  "password": "StrongPass@123"
}
```

Curl:

```bash
curl -i -X POST http://127.0.0.1:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"doctor001","password":"StrongPass@123"}'
```

### Patient APIs

All endpoints require `access_token` cookie.

| Method | Endpoint | Description |
| --- | --- | --- |
| `POST` | `/patient/` | Create patient |
| `GET` | `/patient/` | List all patients |
| `GET` | `/patient/{patient_id}` | Get one patient |
| `PATCH` | `/patient/{patient_id}` | Update patient |
| `DELETE` | `/patient/{patient_id}` | Delete patient |

#### `POST /patient/`

Request body:

```json
{
  "patient_id": "PAT1001",
  "patient_name": "John Doe",
  "dob": "1998-04-17"
}
```

#### `PATCH /patient/{patient_id}`

Request body:

```json
{
  "patient_id": "PAT1001",
  "field": "patient_name",
  "new_value": "John A Doe"
}
```

### Report APIs

All endpoints require `access_token` cookie.

| Method | Endpoint | Description |
| --- | --- | --- |
| `POST` | `/report/upload/{patient_id}` | Upload and process report |
| `GET` | `/report/retrive/{patient_id}/{report_type?}` | Fetch reports |

Note:

- The route path in code is currently `retrive` (not `retrieve`).

#### `POST /report/upload/{patient_id}`

- Content type: `multipart/form-data`
- File key: `medical-report`
- Allowed file types: `jpg`, `jpeg`, `png`, `pdf`
- Max file size: `10MB`

Curl:

```bash
curl -X POST http://127.0.0.1:8000/report/upload/PAT1001 \
  --cookie "access_token=<token>" \
  -F "medical-report=@/absolute/path/to/report.pdf"
```

#### `GET /report/retrive/{patient_id}/{report_type?}`

Allowed `report_type` values:

- `blood_test`
- `urine_test`
- `ecg_summary`
- `xray_summary`
- `ct_scan_summary`
- `mri_scan_summary`
- `discharge_summary`

Optional query parameters:

- `from_date` (date)
- `to_date` (date)

Curl:

```bash
curl "http://127.0.0.1:8000/report/retrive/PAT1001/blood_test?from_date=2025-01-01&to_date=2025-12-31" \
  --cookie "access_token=<token>"
```

### Utility/Generated Routes

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/docs` | Scribe docs page |
| `GET` | `/docs.openapi` | OpenAPI spec |
| `GET` | `/docs.postman` | Postman collection |

---

## Testing

Run tests:

```bash
php artisan test
```

Current note:

- Default Laravel feature test expects `GET /` to return `200`.
- This API-only app has no `/` route and returns `404`.
- Update `tests/Feature/ExampleTest.php` if you want green test runs by default.

---

## Troubleshooting

- `vendor/autoload.php` missing
  - Run `composer install`

- Composer error about `ext-mongodb`
  - Install and enable MongoDB PHP extension

- Redis or MongoDB connection errors
  - Check `docker compose ps`
  - Verify `.env` host/port values

- OCR request failures
  - Check OCR service logs: `docker compose logs -f ocr`
  - Ensure `OCR_MODEL_URL` is reachable from Laravel runtime

- Gemini failures
  - Check `GEMINI_API_KEY`
  - Validate `GEMINI_MODEL_URL`

---

## Useful Commands

```bash
php artisan route:list
php artisan migrate:status
php artisan config:clear
php artisan test

docker compose up -d
docker compose ps
docker compose logs -f mongodb
docker compose logs -f redis
docker compose logs -f ocr
docker compose down
```

---

## License

This project is distributed under the MIT License.
