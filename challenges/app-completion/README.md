# App Completion: Concurrency-Safe Wallet Transfer

## Timebox

Self-timed: 60 minutes of focused work. Start any time within the submission window
your interviewer gave you, but note your actual start and end time in `SUBMISSION.md`.

## Scenario

TransactX's wallet service lets users transfer money to other wallets. Money is stored
as an integer in minor units (e.g. kobo). The Laravel app, database schema, routes, and
tests are already in place. The transfer logic itself is not.

Two transfers can arrive for the same wallet(s) at nearly the same time (a user
double-tapping "send", or two independent transfers between the same pair of wallets in
opposite directions). The implementation needs to be correct under that concurrency, not
just correct when called once at a time.

## Candidate Task

Complete `App\Challenges\AppCompletion\Services\WalletTransferService::transfer()` so
that it:

- Rejects a transfer amount that is not a positive integer.
- Rejects a transfer where the source and destination wallet are the same.
- Rejects a transfer where the source or destination wallet does not exist.
- Rejects a transfer from a wallet whose status is not `active`.
- Rejects a transfer where the source wallet has insufficient balance.
- Debits the source wallet and credits the destination wallet atomically.
- Locks both wallet rows for the duration of the check-and-update, in a deterministic
  order, so two transfers moving money in opposite directions between the same pair of
  wallets cannot deadlock or race each other.
- Records a `WalletTransfer` and returns a result the controller can serialize.

The relevant files are under `app/Challenges/AppCompletion/`. The DTOs
(`TransferData`, `TransferResult`) already define the shape of the input and output,
so you shouldn't need to change them.

## Commands

From the repository root:

```bash
composer install
composer test:app-completion
```

The tests are expected to fail before you complete the `TODO`.

## Submission

Fill in `SUBMISSION.md` (in this directory) once you're done. See the root README for
how to submit your work (private GitHub repo, commit as you go) and what happens after
you share it.

## Notes

- Keep money as integer minor units. No floats.
- `DB::transaction()` and `->lockForUpdate()` are your friends here.
- The automated tests run against an in-memory SQLite database, which does not enforce
  real row-level locking, so the tests can't tell whether you locked or not. Make your
  locking reasoning easy to see in your code and cover it in the `SUBMISSION.md`
  debrief. Be ready to talk through what you locked, in what order, and why.
- Prefer meaningful, specific error codes over a single generic failure.

## Expected error codes

The tests assert exact codes, so a correct implementation still fails unless it uses
these. (This is intended so candidates don't need to reverse-engineer string literals.)

| Case                                  | Code                    |
| ------------------------------------- | ----------------------- |
| Amount not a positive integer         | `INVALID_AMOUNT`        |
| Source and destination are the same   | `SAME_WALLET_TRANSFER`  |
| Unknown source or destination wallet  | `WALLET_NOT_FOUND`      |
| Source wallet is not `active`         | `WALLET_NOT_ACTIVE`     |
| Insufficient source balance           | `INSUFFICIENT_BALANCE`  |
