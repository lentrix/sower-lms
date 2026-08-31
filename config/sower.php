<?php

return [

    /**
     * Loans released before this date are never assessed for penalties
     * automatically.
     *
     * Leave it BLANK to disable automatic assessment entirely. That is the
     * default: the payment data imported from the previous system is known to
     * be incomplete (many payments failed to import on foreign key errors), so
     * a schedule that looks unpaid is often a dropped import row rather than a
     * late borrower. Assessing those automatically would charge people for
     * payments they actually made.
     *
     * Legacy accounts are instead assessed deliberately, one at a time, through
     * the staff-run backfill on the borrower page, where each schedule is
     * reviewed before anything is charged.
     */
    'penalty_assessment_start_date' => env('PENALTY_ASSESSMENT_START_DATE'),

    /**
     * 'penalty' is the percentage of the amount due charged as a penalty.
     *
     * 'penalty_after_days' is how long a payment may go unsettled before the
     * inspect-for-penalty command imposes that penalty. NOTE that the three
     * values are NOT measured from the same anchor:
     *
     *   Weekly and Bi-Monthly  - days past the payment schedule's due_date.
     *   Arawan                 - days past the LOAN's released_at, i.e. the
     *                            full 56-day term lapsing. It is not a
     *                            per-schedule grace period.
     *
     * Do not "normalize" the Arawan value onto due_date - that would silently
     * change when daily loans get penalized.
     */
    'loan_plans' => [
        'Arawan'=>[
            'plan_type' => 1,
            'month' => 2,
            'penalty'=>5,
            'penalty_after_days' => 56,
            'payment_schedules'=>56
        ],
        'Weekly'=>[
            'plan_type' => 2,
            'month'=>3,
            'penalty'=>5,
            'penalty_after_days' => 14,
            'payment_schedules' => 12
        ],
        'Bi-Monthly'=>[
            'plan_type' => 3,
            'month' => null,
            'paument_schedules' => null,
            'penalty' => 5,
            'penalty_after_days' => 5
        ]
    ],
    'plan_types' => [
        1 => 'Arawan',
        2 => 'Weekly',
        3 => 'Bi-Monthly'
    ],
    'interest_rates' => [
        'Member' => 3,
        'Non-Member' => 6,
        'Discounted' => 4
    ],
    'status_names' => [
        0 => 'Request',
        1 => 'Confirmed',
        2 => 'Released',
        3 => 'Completed',
        4 => 'Denied',
        5 => 'Incomplete'
    ]

];
