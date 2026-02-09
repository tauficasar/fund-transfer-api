# Fund Transfer API (Symfony)

A secure, production-ready API for transferring funds between accounts, built with **Symfony**, **MySQL**, and **Redis**.

This project focuses on **correctness, reliability, and clean architecture** rather than building a full payment system. It demonstrates how to safely handle money movements, validation, idempotency, and error handling in a high-load environment.

---

## ✨ Features

- Create accounts with balance and currency
- Transfer funds between accounts
- Strong validation using DTOs
- Atomic transfers using database transactions
- Pessimistic locking to prevent race conditions
- Idempotent transfer support (Redis + DB fallback)
- Consistent JSON error responses
- Centralized exception handling
- Integration tests covering success and failure cases

---

## 🛠️ Tech Stack

- **PHP 8.5**
- **Symfony 8**
- **MySQL 9.6**
- **Redis**
- **Doctrine ORM**
- **PHPUnit**

---

## 📦 Installation

```bash
git clone https://github.com/tauficasar/fund-transfer.git
cd fund-transfer
composer install
```

Configure `.env.local`:

```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/fund_transfer"
REDIS_URL=redis://localhost:6379
```

Create database:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

---

## ▶️ Run App

```bash
symfony server:start
or
php -S localhost:8000 -t public
```

---

## 📌 API Endpoints

Create Account
POST /api/v1/accounts
Body
{
  "balance": "1000.00",
  "currency": "INR"
}

Transfer Funds
POST /api/v1/transfers
{
  "fromAccountId": "uuid",
  "toAccountId": "uuid",
  "currency": "INR",
  "amount": "200.00",
  "idempotencyKey": "unique-key"
}

Example Success Response
{
  "id": "uuid",
  "fromAccount": "uuid",
  "toAccount": "uuid",
  "amount": "200.00",
  "currency": "INR",
  "status": "completed",
  "createdAt": "2026-02-09T13:11:28+00:00",
  "failureReason": null
}

---

## ❌ Error Handling

All errors are returned in a consistent JSON format.

Validation Error (422)
{
  "errors": {
    "fromAccountId": ["This is not a valid UUID."],
    "toAccountId": ["This is not a valid UUID."]
  }
}

Business Rule Error (422)
{
    "error": {
        "code": "INSUFFICIENT_BALANCE",
        "message": "Insufficient balance. Available 9790.00, requested 100000.00"
    }
}

Server Error (500)
{
  "error": {
    "code": "INTERNAL_ERROR",
    "message": "Internal server error"
  }
}

---

🔒 Reliability & Safety

Transactions ensure atomic fund movement

Pessimistic locking prevents concurrent overdrafts

Idempotency keys avoid duplicate transfers

Redis + DB fallback ensures idempotency safety

Central exception subscriber guarantees consistent API responses

---

## 🧪 Tests

```bash
php bin/phpunit
```
Covered scenarios include:

Successful transfers

Validation failures

Insufficient balance

Idempotent replay

Account creation errors

---

🧠 Architecture Notes

DTOs (src/Dto) separate validation from controllers

Business logic lives in services (thin controllers)

Domain exceptions represent business rules

Single global exception subscriber for API consistency

Tests interact with real database state (integration tests)

---

🚀 Possible Improvements

If extended further, I would add:

Rate limiting per account

Currency conversion support

Async processing with message queues

Account-level daily transfer limits

Read replicas for scaling reads

OpenAPI / Swagger documentation

---

## ⏱ Time Spent

~6–8 hours

(Includes design, implementation, debugging, testing, and documentation)

---

## 🤖 AI Usage

AI tools (ChatGPT) were used for architecture discussion, edge-case analysis, and refactoring guidance. All code was reviewed and understood.

---

## 👤 Author

Taufic Asarudeen
