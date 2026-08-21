# Submission: Bug Hunt

Fill this in and include it with your solution.

## Time & tools

- Start time: 2026-08-21 10:53 WAT
- End time:
- Tools/resources you used (docs, Stack Overflow, an AI assistant, etc., name them
  plainly; this is informational, not a trick question):
- If you used an AI tool, roughly how did you use it (e.g. "asked it to explain a
  stack trace", "generated a first draft and rewrote most of it", "wrote it myself and
  asked it to review"):

## Debrief

Answer these about the code you're submitting, not in the abstract.

1. Walk through what happens, step by step, when `fund()` is called twice in immediate
   succession with the same `reference`, using your fixed code.

2. The unique constraint on `reference` is a safety net for true concurrent requests,
   not just sequential retries. Given your implementation, is there a scenario where
   two near-simultaneous requests would still cause one of them to receive a raw 500
   error instead of a graceful "already processed" response? If so, what would you
   change to close that gap?

3. Without running it, what does
   `test_rolls_back_the_wallet_balance_if_the_funding_record_fails_to_persist` actually
   simulate, and why does it use a model event hook instead of a real network or
   database failure?
