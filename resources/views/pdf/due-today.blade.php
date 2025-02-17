<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Due Today | Filter: Barangay={{ $barangay }}, Town={{ $town }}, Type={{ $type ? config('sower.plan_types')[$type] : 'All' }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 2px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            padding: 4px 2px;
        }
    </style>
</head>
<body>

    <h1 style="margin:0; padding:0">Due Today {{ \Carbon\Carbon::now('Asia/Manila')->format('l F d, Y') }} ({{ count($dueToday) }})</h1>
    <div style="margin-bottom: 12px">Filter: Barangay={{ $barangay }}, Town={{ $town }}, Type={{ $type ? config('sower.plan_types')[$type] : 'All' }}</div>

    <table>
        <thead>
            <tr>
                <th>Loan ID#</th>
                <th>Full Name</th>
                <th>Address</th>
                <th>Contact No</th>
                <th>Amount</th>
                <th>Loan Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dueToday as $loanDue)
                <tr>
                    <td>{{ sprintf('%08d',$loanDue['loan_id']) }}</td>
                    <td>
                        {{ $loanDue['borrower'] }}
                    </td>
                    <td>{{ $loanDue['address'] }}</td>
                    <td>{{ $loanDue['contact_no'] }}</td>
                    <td>{{ $loanDue['due'] }}</td>
                    <td>{{ config('sower.plan_types')[$loanDue['type']] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
