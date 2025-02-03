<?php

return [

    'loan_plans' => [
        'Arawan'=>[
            'month' => 2,
            'penalty'=>5,
            'payment_schedules'=>56
        ],
        'Weekly'=>[
            'month'=>3,
            'penalty'=>5,
            'payment_schedules' => 12
        ],
        'Bi-Monthly'=>[
            'month' => null,
            'paument_schedules' => null,
            'penalty' => 5
        ]
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
