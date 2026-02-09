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
```

---

## 🧪 Tests

```bash
php bin/phpunit
```

---

## ⏱ Time Spent

~6–8 hours

---

## 🤖 AI Usage

AI tools (ChatGPT) were used for architecture discussion, edge-case analysis, and refactoring guidance. All code was reviewed and understood.

---

## 👤 Author

Taufic Asarudeen
