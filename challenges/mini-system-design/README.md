# Mini System Design: Wallet & Funding Platform

## Timebox

30 minutes.

## Format

Live session (video call or in person). You'll design the system out loud, on a
whiteboard, or in a shared doc. This round is not part of the async coding submission;
your interviewer will run it with you in real time.

## Scenario

TransactX needs a wallet platform: users hold a balance, fund it through a third-party
payment provider's webhook, and transfer money to other users' wallets. The provider
retries webhooks on timeout, so the same funding event can arrive more than once.
Traffic is bursty around promotions, and two transfers can legitimately hit the same
wallet at the same moment.

Keep the design practical. You don't need to design a multi-region, multi-currency
banking platform, but you should design something that would survive real production
traffic, not just a happy-path demo.

## What We'll Cover

The conversation will move through these areas at the pace your design dictates. It's
fine to cover them in whatever order makes sense as you talk:

- API surface for funding, transfers, wallet lookup, and transaction history.
- Data model for wallets and transactions/ledger entries, and how balances are kept
  correct.
- Preventing a retried funding webhook from crediting a wallet twice.
- Concurrency between transfers that touch the same wallet.
- What happens if the system crashes partway through an operation.
- Webhook handling: verification and replays.
- Which work should be synchronous versus pushed to a queue.
- A consistent error shape, and which failures are client errors versus ours.
- How you'd audit and verify a wallet's balance after the fact.
- How you'd test correctness under real concurrency.

## Expected Output

Diagrams, bullet points, pseudocode, or a short written design: whatever gets the
thinking across fastest. Depth and tradeoffs matter far more than polish.