<script setup>
import { Link } from '@inertiajs/vue3';


defineProps({
    loan: Object
})

const money = Intl.NumberFormat('en-PH',{style: 'currency', currency:"php"})

const formattedDate = (dateStr, monthFormat="long") => {
    const date = new Date(dateStr)
    const formatter = new Intl.DateTimeFormat("en-US", {
        month: monthFormat,
        day: "numeric",
        year: "numeric"
    })
    return formatter.format(date)
}

</script>

<template>
    <table>
        <tbody>
            <tr>
                <th>Category</th>
                <td>
                    {{ loan.loan_plan.categoryName }}
                </td>
            </tr>
            <tr>
                <th>Loan Plan</th>
                <td>{{ loan.loan_plan.planText }}</td>
            </tr>
            <tr>
                <th>Principal</th>
                <td>{{ money.format(loan.amount) }}</td>
            </tr>
            <tr>
                <th>Interest Rate</th>
                <td>{{ loan.loan_plan.interest }}%</td>
            </tr>
            <tr>
                <th>Amortization</th>
                <td>{{ money.format(loan.amortization) }}</td>
            </tr>
            <tr>
                <th>Total Payable</th>
                <td>{{ money.format(loan.totalLoanPayable) }}</td>
            </tr>
            <tr>
                <th>Balance</th>
                <td>{{ money.format(loan.balance) }}</td>
            </tr>
            <tr>
                <th>No. of Payments</th>
                <td>{{ loan.loan_plan.payment_schedules }}</td>
            </tr>
            <tr>
                <th>Purpose</th>
                <td>{{ loan.purpose }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ loan.status }} | {{ loan.statusText }}</td>
            </tr>
            <tr v-if="loan.status==2">
                <th>Release Date</th>
                <td>{{ formattedDate(loan.released_at) }}</td>
            </tr>
        </tbody>
    </table>
    <div
        v-if="loan.balance <= 0"
        class="mt-4"
    >
        <Link
            :href="'/loans/set-status/3/' + loan.id"
            method="post"
            class="px-4 py-2 bg-green-500 text-white rounded-md"
        >
            <font-awesome-icon icon="fa-solid fa-check"></font-awesome-icon>
            &nbsp;Completed
        </Link>
    </div>
</template>
