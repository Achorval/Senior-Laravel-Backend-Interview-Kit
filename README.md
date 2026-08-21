# Senior Backend Engineer (Laravel/PHP) Interview Kit

This repository contains three practical backend interview rounds for shortlisting a
**Senior Backend Developer (Laravel/PHP)** candidate at TransactX. It's a real,
runnable Laravel application (wallets, transfers, and funding, backed by SQLite), not a
slide deck. The difficulty, scope, and bar are set for a senior hire:
real database transactions, row locking, idempotency under concurrency, and
architectural judgment, not just CRUD correctness.

## Challenges

Every candidate gets the same assignment: both coding rounds, in this one repository,
plus a separate live system-design conversation.

1. `challenges/app-completion`: complete a concurrency-safe wallet transfer service.
2. `challenges/bug-hunt`: find and fix a production bug causing duplicate wallet
   funding under webhook retries.
3. `challenges/mini-system-design`: design a wallet and funding platform out loud, in a
   live call (no code, and not part of the async submission below).

Budget roughly 2 hours total for the two coding rounds (60 minutes each, self-timed).
Candidates can split that across two sittings within the submission window.

## Requirements

- PHP 8.3+ (CLI)
- Composer 2.x
- The PHP extensions this kit and Laravel rely on: `pdo_sqlite`, `sqlite3`, `mbstring`,
  `xml`, `curl`, `fileinfo`, `openssl`, `tokenizer`, `ctype`, `json`, `bcmath`. Check
  what's loaded with `php -m`. If only `sqlite3`/`pdo_sqlite` are missing, that's the
  pair that matters most for this kit.

No external database is required. Tests run against an in-memory SQLite database.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Prefer the explicit commands above, or run them in one shot with `composer setup`, which
also creates the local SQLite file so `php artisan serve` works.

## Run Tests

Run everything:

```bash
composer test
```

Run one coding round:

```bash
composer test:app-completion
composer test:bug-hunt
```

**Before you change anything, both suites are expected to report a mix of passing and
failing tests.** Some tests pass on the starter code on purpose (for example, an unknown
wallet is already rejected), and some fail because the piece you need to write or fix is
missing. The failures are the goal; you don't need to chase the green ones.

## How Candidates Should Use This Repo

- Read this README and the README inside each challenge (`challenges/app-completion`,
  `challenges/bug-hunt`).
- Install dependencies with `composer install`.
- Run the relevant tests before making changes.
- Create a **private** GitHub repository, push this code to it, and commit as you go: a
  handful of commits showing your process, not one final dump. We look at commit
  history as part of review.
- Invite the reviewer account your interviewer names in their email as a collaborator,
  then reply to the email you received this kit in with the repo link. Keep the repo
  private.
- Keep the solution simple, readable, and well-tested.
- Fill in `SUBMISSION.md` in each challenge directory before sharing your repo. It asks
  how long you took, what you used, and a few questions about your own code.
- After you share your repo, expect a couple of short follow-up questions about your
  solutions. Answer them yourself, in your own words.