# Penalty subsystem — known issues & deferred decisions

Recorded 2026-08-31, alongside the penalty correctness/audit fixes.

These were identified during a review of the penalty and penalty-payment path and
**deliberately not fixed**, either because they need a decision from the owner or
because they are separate pieces of work. They are written down here so they stay
visible rather than being rediscovered later.

---

## 1. A partial payment exempts a schedule from penalty, permanently

**What it is.** All three branches of `InspectForPenalty` filter with
`whereDoesntHave('loanPayments')`. That tests for the *presence* of an allocation,
not its *sufficiency*. A borrower who pays ₱1 against a ₱2,000 schedule creates a
`LoanPayment` row, and that schedule is then excluded from penalty assessment
forever — even though it is effectively unpaid.

Note this can also happen without the borrower intending it: `Payment::allocate()`
spreads a payment across schedules in due-date order, so a small payment naturally
lands a partial allocation on the oldest unpaid schedule.

**Why deferred.** This is a policy question, not a bug in the mechanical sense.
Whether "underpaid" counts as "late" is the lender's call.

**What deciding it requires.** Confirmation from the owner on the rule. If partial
payments should *not* exempt, the fix is to replace the `whereDoesntHave` filter
with a balance comparison (`sum(loanPayments.amount) < amount_due`), which also
raises a follow-on question: should the penalty be 5% of the full `amount_due`, or
5% of the remaining balance? The filter now lives in one place -
`PenaltyAssessor::candidates()` - so the change is a single edit.

---

## 2. Retroactive penalty allocation on payment edit/delete

**What it is.** `Payment::reapplyForLoan()` deletes every `PenaltyPayment` and
`LoanPayment` for a loan and replays all payments through `allocate()` in date
order. Because `allocate()` always settles penalties first against *whatever
penalties exist right now*, replaying can divert an old payment to a penalty that
did not exist when that payment was made — e.g. editing a March payment can push
part of it onto a penalty imposed in June.

**Why deferred.** The resulting totals are internally consistent, and the behavior
is arguably correct from a "current state of the account" view. The problem is
external: a receipt reprinted after an edit will not match the one already handed
to the borrower, because the principal/interest/penalty split has changed
retroactively.

**What deciding it requires.** A decision on whether allocation should be
constrained by date — i.e. a payment may only settle penalties imposed on or
before its own payment date. That is a meaningful change to `allocate()` and would
need to be reasoned through against how the branch office actually reconciles
receipts.

---

## 3. `double` used for money columns

**What it is.** `loans.amount`, `payment_schedules.amount_due`, `payments.amount`,
`loan_payments.amount`/`interest`/`principal`, `penalties.amount` and
`penalty_payments.amount` are all `double`. Binary floating point cannot represent
decimal currency exactly.

Individual errors are tiny, but they compound: `allocate()` rounds interest and
principal on every run, and `reapplyForLoan()` re-runs that arithmetic across the
loan's whole payment history each time a payment is edited or deleted.

**Why deferred.** The correct fix — `decimal(12,2)` — is a schema migration plus a
backfill across every existing row, and it needs its own testing pass. It does not
belong folded into a bug-fix change.

**What deciding it requires.** A scheduled maintenance window and a verified
backup, since it rewrites every financial column in the database.

---

## 4. Legacy accounts are not assessed until a human says so

**Status: handled, but requires an owner decision to switch on.**

Automatic assessment is gated on `PENALTY_ASSESSMENT_START_DATE` (blank by default)
and filters on `loans.released_at >= cutover`. Every currently active loan was
released by 2026-06-27, so **no existing loan is ever assessed automatically** - the
6,308-schedule / PHP 41,141.42 backlog will not be imposed by the system on its own.

Legacy accounts are assessed through the **Assess Penalties** button on the borrower
page, which lists candidate schedules for per-row review before charging anything.

**What the owner still needs to decide:**

- The cutover date, which enables automatic assessment for new loans.
- Whether the historical backlog should be worked through account by account at all,
  given the payment import problem below.

---

## 5. No automated test coverage

**What it is.** `tests/` contains only stock Laravel scaffolding
(`ExampleTest.php`, the auth and profile tests). There are no tests for
`InspectForPenalty`, `Payment::allocate()`, or `Payment::reapplyForLoan()`.

**Why it matters.** Every defect found in this review was of the form "the rule
fires on the wrong set of rows" — precisely what a feature test pins down. The
`|| true` in particular would have been caught immediately by a test asserting
that a bi-monthly schedule one day overdue is not penalized.

**Suggested follow-up.** A feature-test harness covering, at minimum:

- each plan type's grace boundary (not penalized at N days, penalized at N+1)
- re-running the command does not double-penalize
- `allocate()` settles penalties before schedules, and handles partial settlement
- `reapplyForLoan()` reproduces the same allocation totals when nothing changed
- a penalty with payments against it cannot be removed

---

## 6. PHP 69,592 of legacy penalty payments missing from the import

**What it is.** `payment_and_penalty.csv` contains 18,735 rows, of which **1,443
carry a penalty totalling PHP 69,592.13**. The database holds **0 penalty_payments**.
`DataTransferPayment` does create a `Penalty` + `PenaltyPayment` pair when a row's
penalty column is non-zero, so either it was run against `payment.csv` instead, or
the run did not complete.

The wider import is incomplete too: `payment_transfer_log.txt` records foreign key
failures, and only 5,988 payments landed against 16-18k rows in the source files.
39% of payment schedules (6,366) have no payment recorded at all.

**Why it matters.** Two separate consequences:

1. Historical reporting understates penalty income by roughly PHP 69.6k.
2. A schedule that looks unpaid is frequently a dropped import row rather than a
   late borrower. This is the reason automatic assessment excludes pre-cutover
   loans, and the reason the staff backfill reviews schedules one at a time.

**Why deferred.** Reconciling the import is a data project in its own right - it
needs the source system, a decision on which CSV is authoritative, and a re-run
strategy that does not double-post the payments already present.

**What deciding it requires.** Owner confirmation of which export is authoritative,
and whether historical penalty income needs to be restored for reporting or simply
noted as a known gap in the pre-2026-07 figures.
