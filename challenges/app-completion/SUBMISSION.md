# Submission: App Completion

Fill this in and include it with your solution.

## Time & tools

- Start time: 2026-08-21 07:57 WAT
- End time: 2026-08-21 08:55 WAT
- Tools/resources you used (docs, Stack Overflow, an AI assistant, etc., name them
  plainly; this is informational, not a trick question): Online research
  (Laravel docs) and an AI assistant.
- If you used an AI tool, roughly how did you use it (e.g. "asked it to explain
  `lockForUpdate`", "generated a first draft and rewrote most of it", "wrote it myself
  and asked it to review"): Used it alongside my own research to organize the work and
  validate my approach — confirming reasoning like lock ordering to avoid deadlocks and
  the check-after-lock ordering, and to keep the process structured. I understood the 
  problem and the solution before it went in.

## Debrief

Answer these about the code you're submitting, not in the abstract.

1. Without running it, what does `test_rejects_transfer_from_a_suspended_wallet`
   expect your code to do, and which lines make that pass?

   The test creates a source wallet with `status = suspended` (and plenty of
   balance, so balance isn't the reason for the rejection), then expects
   `transfer()` to throw a `DomainException` with error code `WALLET_NOT_ACTIVE`.

   In `WalletTransferService::transfer()`, after both wallet rows are locked
   with `lockForUpdate()`, this check makes the test pass:

   ```php
   if (! $fromWallet->isActive()) {
       throw new DomainException('WALLET_NOT_ACTIVE', 'Source wallet is not active.');
   }
   ```

   `isActive()` is a helper already on the `Wallet` model that just compares
   `status` to the `active` constant. I check status right after locking and
   before checking the balance, in that order on purpose: lock first so the
   data can't change under us mid-check, then confirm the wallet is even
   allowed to send money at all before bothering to check if it has enough of
   it. Checking balance on a suspended wallet would be pointless — it can't
   transact regardless of balance.

2. Why did you lock wallet rows in the order you chose? What would you observe in
   production if you'd locked them in request order (`from_wallet_id` then
   `to_wallet_id`) instead?

   I lock whichever wallet has the lower ID first, always, no matter which one
   is `from` and which is `to`. The rule is: for any two wallets, everyone who
   locks both of them must lock them in the same order every time.

   If I'd locked in request order instead (`from` first, `to` second), two
   opposite transfers between the same pair of wallets can deadlock. Say
   Transfer A sends wallet 1 to wallet 2, and Transfer B sends wallet 2 to
   wallet 1, and both run at the same instant:

   - A locks wallet 1, then waits for wallet 2.
   - B locks wallet 2, then waits for wallet 1.

   Neither can finish, because each is waiting on a lock the other is
   holding. In production the database eventually detects this deadlock and
   kills one of the two transactions, so that transfer fails and the user has
   to retry. Under real traffic this would show up as random, intermittent
   failed transfers whenever two transfers cross the same wallet pair in
   opposite directions at nearly the same time.

   Locking by ascending ID avoids this because both transfers agree on the
   order: A locks 1 then 2, B also locks 1 then 2. B simply waits for A to
   finish and release wallet 1, then proceeds. No deadlock, just one transfer
   briefly waiting for the other, which is normal and safe.

   Note: the "ascending ID" part isn't the important bit — any fixed,
   consistent ordering works (e.g. if wallets used UUIDs instead of
   auto-increment IDs, comparing the UUID strings would work the same way).
   What actually matters is that every place in the code that locks two
   wallets picks their lock order the same way, every time.

3. Suppose `WalletTransfer::create()` threw an exception right after you'd already
   debited the source wallet, but `DB::transaction()` wasn't there to wrap the whole
   operation. What state would the database be left in? None of the automated tests
   force this failure directly. Why not, and how would you test for it if you had
   more time?

   Without the transaction, each write commits to the database the moment it
   runs, one at a time. So if `WalletTransfer::create()` throws right after
   the source wallet was debited, that debit is already saved — permanently.
   The money would just disappear: taken out of the source wallet, with no
   `WalletTransfer` record to show why, and possibly never credited to the
   destination wallet either. In a real money system that's the worst kind of
   bug: silent, unexplained money loss.

   `DB::transaction()` prevents this. It groups the debit, the credit, and
   the `WalletTransfer::create()` into one unit: if anything inside throws,
   Laravel rolls back every write in that unit, so it's all-or-nothing. Either
   the whole transfer happens, or none of it does.

   The automated tests don't force this failure because doing so means
   deliberately making `WalletTransfer::create()` throw partway through (for
   example, by mocking the model), which tests whether Laravel's own
   transaction/rollback mechanism works rather than testing our business
   logic. That's reasonable to trust the framework for. The tests we do have
   focus on things unique to our code instead: validation rules, correct
   balances, and that exactly one transfer record gets created.

   If I had more time, I'd add a test that mocks `WalletTransfer` to throw on
   `create()`, run the transfer, and then assert both wallet balances are
   completely unchanged afterward. That would directly prove the rollback
   works, instead of just trusting that wrapping the code in
   `DB::transaction()` is enough.
