# Submission: Bug Hunt

Fill this in and include it with your solution.

## Time & tools

- Start time: 2026-08-21 10:53 WAT
- End time: 2026-08-21 11:50 WAT
- Tools/resources you used (docs, Stack Overflow, an AI assistant, etc., name them
  plainly; this is informational, not a trick question): Online research (Laravel docs)
  and an AI assistant.
- If you used an AI tool, roughly how did you use it (e.g. "asked it to explain a
  stack trace", "generated a first draft and rewrote most of it", "wrote it myself and
  asked it to review"): Used it alongside my own research to organize the work and
  validate my approach — walking through the original bug order (credit before
  duplicate check), confirming the unique-constraint recovery path actually closes the
  concurrency gap by checking Laravel's own exception-handling source, and keeping the
  process structured with incremental commits.

## Debrief

Answer these about the code you're submitting, not in the abstract.

1. Walk through what happens, step by step, when `fund()` is called twice in immediate
   succession with the same `reference`, using your fixed code.

   First call (reference `ref-001`, wallet balance starts at 1000):
   1. Amount and wallet checks pass.
   2. I look up `WalletFunding::where('reference', 'ref-001')->first()` — nothing
      exists yet, so `$existing` is `null`.
   3. I enter `DB::transaction()`: increment the wallet balance (1000 → 1500),
      create the `WalletFunding` row, dispatch the receipt job.
   4. The transaction commits. The result is returned with balance 1500.

   Second call (same reference `ref-001`):
   1. Amount and wallet checks pass again.
   2. The same lookup this time finds the row created in the first call.
   3. I return `FundingResult::fromModels($existing, $wallet)` immediately —
      no increment, no transaction, no second insert, no second job dispatch.
   4. The balance stays 1500, and only one `WalletFunding` row exists.

   The key fix is checking for an existing reference *before* touching the
   balance at all, instead of the original buggy order (credit first, check
   second) — the original code credited on every call because it never
   looked before acting.

2. The unique constraint on `reference` is a safety net for true concurrent requests,
   not just sequential retries. Given your implementation, is there a scenario where
   two near-simultaneous requests would still cause one of them to receive a raw 500
   error instead of a graceful "already processed" response? If so, what would you
   change to close that gap?

   Walking through the true-concurrency case: two requests for the same
   reference both run the "does this reference already exist?" lookup at
   nearly the same instant, before either has inserted anything, so both find
   nothing and both proceed. Both open their own `DB::transaction()`, both
   increment the wallet, both attempt to insert a `WalletFunding` row with
   the same reference. Whichever commits first wins; the unique index (added
   in this fix) makes the second insert fail with
   `Illuminate\Database\UniqueConstraintViolationException`.

   In my implementation, that exception is caught around the transaction. The
   loser's transaction rolls back automatically (undoing its own increment),
   the `catch` block re-queries `WalletFunding` by reference to get the
   winner's row, and re-fetches the wallet with `->fresh()` so the returned
   balance reflects the winner's committed credit, not stale data. So the
   loser gets the same graceful "already processed" result the winner got,
   not a 500.

   I don't see a gap here: `UniqueConstraintViolationException` is Laravel's
   dedicated exception for this exact case (checked in
   `Connection::runQueryCallback()`, with each database driver — SQLite,
   MySQL, Postgres, SQL Server — implementing its own detection of what a
   unique-constraint error looks like for that engine), so catching it isn't
   driver-specific or fragile. And because a unique constraint is only
   violated once the winning row is actually committed (or locked and about
   to commit), the loser's recovery read is guaranteed to see the winner's
   data by the time it runs.

3. Without running it, what does
   `test_rolls_back_the_wallet_balance_if_the_funding_record_fails_to_persist` actually
   simulate, and why does it use a model event hook instead of a real network or
   database failure?

   This test fakes a failure that happens right when we try to save the
   funding record.

   `WalletFunding::creating(...)` runs some code just before any
   `WalletFunding` gets saved to the database. The test uses this to say: "if
   the reference is `force-failure`, throw an error instead of saving." So
   when the service tries to fund a wallet with that reference, it first
   increases the wallet balance, then tries to save the funding record — and
   that save fails on purpose.

   Because everything is inside `DB::transaction()`, the failed save cancels
   the whole thing. The balance increase gets undone too. That's why the test
   checks that the wallet balance is back to 1000 and no funding record was
   created — it proves the rollback really works.

   It uses this hook instead of a real network or database failure because
   real failures are hard to control in a test. You can't easily force a
   database to crash at the exact right moment, and doing so would make the
   test slow and unreliable. The model event hook lets us cause a failure at
   the exact point we want, every time, without needing anything external to
   actually break. It tests our own code's behavior (does the rollback work),
   not whether a real database can fail.
