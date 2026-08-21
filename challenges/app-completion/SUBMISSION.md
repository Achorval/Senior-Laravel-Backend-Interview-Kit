# Submission: App Completion

Fill this in and include it with your solution.

## Time & tools

- Start time: 2026-08-21 07:57 WAT
- End time:
- Tools/resources you used (docs, Stack Overflow, an AI assistant, etc., name them
  plainly; this is informational, not a trick question):
- If you used an AI tool, roughly how did you use it (e.g. "asked it to explain
  `lockForUpdate`", "generated a first draft and rewrote most of it", "wrote it myself
  and asked it to review"):

## Debrief

Answer these about the code you're submitting, not in the abstract.

1. Without running it, what does `test_rejects_transfer_from_a_suspended_wallet`
   expect your code to do, and which lines make that pass?

2. Why did you lock wallet rows in the order you chose? What would you observe in
   production if you'd locked them in request order (`from_wallet_id` then
   `to_wallet_id`) instead?

3. Suppose `WalletTransfer::create()` threw an exception right after you'd already
   debited the source wallet, but `DB::transaction()` wasn't there to wrap the whole
   operation. What state would the database be left in? None of the automated tests
   force this failure directly. Why not, and how would you test for it if you had
   more time?
