<?php

return [

    'loan_plans' => [
        'Arawan'=>[
            'plan_type' => 1,
            'month' => 2,
            'penalty'=>5,
            'payment_schedules'=>56
        ],
        'Weekly'=>[
            'plan_type' => 2,
            'month'=>3,
            'penalty'=>5,
            'payment_schedules' => 12
        ],
        'Bi-Monthly'=>[
            'plan_type' => 3,
            'month' => null,
            'paument_schedules' => null,
            'penalty' => 5
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
