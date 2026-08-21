# Bug Hunt: Duplicate Funding Under Retry

## Timebox

Self-timed: 60 minutes of focused work. Start any time within the submission window
your interviewer gave you, but note your actual start and end time in `SUBMISSION.md`.

## Scenario

TransactX credits wallets when a payment provider's funding webhook fires. In
production, some users are being credited twice. The payment provider retries a webhook
whenever it doesn't get a fast, clean response, which means the same funding event,
with the same `reference`, can hit this endpoint more than once.

The code has a mix of an application-logic bug and a schema gap around this. Your task
is to find and fix them while keeping the implementation simple.

## Candidate Task

Fix `App\Challenges\BugHunt\Services\WalletFundingService::fund()` (and anything else
that needs to change) so that:

- A retried request with the same `reference` does not credit the wallet a second time.
- A retried request with the same `reference` does not create a second funding record.
- A retried request with the same `reference` returns the already-recorded funding
  result instead of failing, even if a concurrent request won the race to record it
  first (no raw 500 from the unique constraint).
- A zero or negative funding amount is rejected.
- An unknown wallet is rejected.
- A successful funding request updates the balance exactly once.
- The database itself, not just the application code, refuses to store two funding
  records with the same `reference`.

## Commands

From the repository root:

```bash
composer install
composer test:bug-hunt
```

The suite is a mix of passing and failing tests before the bugs are fixed. The failing
ones point at the pieces you need to fix; the passing ones (for example, an unknown
wallet) are already handled by the starter code.

## Submission

Fill in `SUBMISSION.md` (in this directory) once you're done. See the root README for
how to submit your work (private GitHub repo, commit as you go) and what happens after
you share it.

## Notes

- Keep money as integer minor units. No floats.
- `reference` is a globally unique payment-provider event ID, not scoped to a wallet. If
  the same event is ever replayed against a different wallet, treat it as the duplicate
  it is: return the original result and do not credit either wallet again. The unique
  constraint should enforce that at the database level.
- No `reference` collision should be able to slip through in the gap between checking
  whether a record exists and creating it. A check followed by a separate write is not
  atomic on its own. Think about what backs it up if two requests land at the same
  instant. When one of them loses the race to insert, it should still respond
  gracefully, not with a raw database error.
- Expected error codes (the tests assert these): `WALLET_NOT_FOUND` (404) for an
  unknown wallet, `INVALID_AMOUNT` (400) for a zero or negative amount.
- Be ready to explain why retry behavior matters in fintech systems, and what you'd add
  next (beyond what the tests check) if this endpoint saw real concurrent traffic.
