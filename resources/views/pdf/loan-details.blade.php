<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sower Lending - Loan Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
        }

        table.styled {
            width: 100%;
            border-collapse: collapse;
        }

        table.styled th,
        table.styled td {
            border: 1px solid #000;
            padding: 2px 5px;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

    </style>
</head>
<body>
    @php $computations = $loan->computations() @endphp

    <h1>Sower Lending Corp. | Loan Profile</h1>
    <hr />

    <table style="width: 100%">
        <tr>
            <td style="width: 28%" valign="top">
                <h2>Personal Information</h2>
                <table class="styled">
                    <tr>
                        <th class="text-left">Name</th>
                        <td>{{ $loan->borrower->last_name }}, {{ $loan->borrower->first_name }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">Address</th>
                        <td>{{ $loan->borrower->barangay }}, {{ $loan->borrower->town }}, {{ $loan->borrower->province }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">Contact No.</th>
                        <td>{{ $loan->borrower->contact_no }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">Email</th>
                        <td>{{ $loan->borrower->email }}</td>
                    </tr>
                </table>
                <br>
                <h3>Loan Summary</h3>
                <table class="styled">
                    <tr>
                        <th class="text-left">Category</th>
                        <td>{{ $loan->loanPlan->category->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">Loan Plan</th>
                        <td>{{ $loan->loanPlan->plan_text }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">Principal</th>
                        <td class="text-right">{{ number_format($loan->amount,2) }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">Interest Rate</th>
                        <td class="text-right">{{ $loan->loanPlan->interest }}%</td>
                    </tr>
                    <tr>
                        <th class="text-left">Amortization</th>
                        <td class="text-right">{{ number_format($computations['amortization'],2) }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">Total Payable</th>
                        <td class="text-right">{{ number_format($computations['totalPayable'],2) }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">Balance</th>
                        <td class="text-right">{{ number_format($loan->balance) }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">No. of Payments</th>
                        <td class="text-right">{{ $loan->loanPlan->payment_schedules }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">Purpose</th>
                        <td>{{ $loan->purpose }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">Status</th>
                        <td>{{ $loan->status }} | {{ $loan->statusText }}</td>
                    </tr>
                    <tr>
                        <th class="text-left">Release Date</th>
                        <td>{{ $loan->released_at->format('M d Y g:i A') }}</td>
                    </tr>
                </table>
            </td>
            <td style="3%">&nbsp;</td>
            <td style="width: 69%" valign="top">
                <h2>Payment Schedules</h2>
                <table class="styled">
                    <tr>
                        <th>Due Date</th>
                        <th class="text-right">Amount Due</th>
                        <th class="text-right">Penalty</th>
                        <th class="text-right">Loan Payment</th>
                        <th class="text-right">Penalty Payment</th>
                        <th class="text-right">Total Payment</th>
                    </tr>
                    @foreach($loan->paymentSchedules as $pmtSched)
                    <tr>
                        <td>{{ $pmtSched->due_date->format('M d, Y') }}</td>
                        <td class='text-right'>{{ number_format($pmtSched->amount_due,2) }}</td>
                        <td class="text-right">{{ number_format($pmtSched->penaltyAmount,2) }}</td>
                        <td class="text-right">{{ number_format($pmtSched->totalPayments,2) }}</td>
                        <td class="text-right">{{ number_format($pmtSched->penaltyPayment,2) }}</td>
                        <td class="text-right">{{ number_format($pmtSched->totalPayments,2) }}</td>

                    </tr>
                    @endforeach
                </table>

            </td>
        </tr>

    </table>

</body>
</html>
