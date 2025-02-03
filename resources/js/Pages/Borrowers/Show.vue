<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { defineProps, ref, computed, watch, onMounted } from 'vue';
import { useToast } from "vue-toastification";
import LoanTable from '@/Components/LoanTable.vue';

const props = defineProps({
    borrower: null,
    payment_schedules: null,
    alerts: null,
    pending_loan: null,
    totalAmountDue: null,
    totalPenalty: null,
    totalLoanPayment: null,
    totalPenaltyPayment: null
})

const toast = useToast();

const alerts = props.alerts

watch(()=>props.alerts, () => {
    toast.success(props.alerts.success)
})


const formattedDate = (dateStr, monthFormat="long") => {
    const date = new Date(dateStr)
    const formatter = new Intl.DateTimeFormat("en-US", {
        month: monthFormat,
        day: "numeric",
        year: "numeric"
    })
    return formatter.format(date)
}

const money = Intl.NumberFormat('en-PH',{style: 'currency', currency:"php"})

</script>

<template>
    <Head title="View Borrower" />

    <AuthenticatedLayout>
       <PageContent>
            <div class="flex justify-between items-center">
                <h3 class="text-3xl mb-3">Borrower Details</h3>
                <div class="flex gap-2">
                    <Link :href="'/borrowers/edit/' + props.borrower.id" class="px-3 py-1 rounded bg-indigo-500 text-white text-sm">
                        <font-awesome-icon icon="fa-solid fa-edit"></font-awesome-icon>
                        Edit Profile
                    </Link>
                    <Link href="#" class="px-3 py-1 rounded bg-red-900 text-white text-sm">
                        <font-awesome-icon icon="fa-solid fa-file-pdf"></font-awesome-icon>
                        Export
                    </Link>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-2/7 px-4 py-2 rounded bg-green-100 dark:bg-green-800">
                    <h4 class="text-2xl mb-2">Personal Information</h4>
                    <hr>
                    <table class="mt-3">
                        <tbody>
                            <tr>
                                <th class="text-bold text-xl">Name:</th>
                                <td class="text-xl">
                                    {{ borrower.last_name }}, {{ borrower.first_name }} {{ borrower.middle_name }}
                                </td>
                            </tr>
                            <tr>
                                <th class="text-bold">Address:</th>
                                <td>
                                    {{ borrower.address }}
                                </td>
                            </tr>
                            <tr>
                                <th class="text-bold">Contact Number:</th>
                                <td>
                                    {{ borrower.contact_no }}
                                </td>
                            </tr>
                            <tr>
                                <th class="text-bold">Email Address:</th>
                                <td>
                                    {{ borrower.email }}
                                </td>
                            </tr>
                            <tr>
                                <th class="text-bold">Tax ID:</th>
                                <td>
                                    {{ borrower.tax_id }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex-1 px-4 py-2 rounded bg-green-100 dark:bg-green-800">
                    <h4 class="text-2xl">Loan Summary</h4>
                    <hr>
                    <div v-if="borrower.activeLoan" class="flex flex-row gap-4">
                        <div>
                            <LoanTable :loan="borrower.activeLoan"></LoanTable>
                            <div class="py-8 flex flex-col gap-2">
                                <Link :href="'/loans/edit/' + borrower.activeLoan.id" class="px-8 py-2 rounded bg-indigo-700 text-white border border-indigo-500">
                                    <font-awesome-icon icon="fa-solid fa-edit"></font-awesome-icon>
                                    Edit Loan
                                </Link>
                                <Link :href="'/payments/payee/' + borrower.id" class="px-8 py-2 rounded bg-teal-700 text-white border border-indigo-500">
                                    <font-awesome-icon icon="fa-solid fa-money-bill-1"></font-awesome-icon>
                                    Payment
                                </Link>
                            </div>
                        </div>
                        <div class="flex-1">
                                <h5 class="text-xl">Payment Schedule</h5>
                            <table class="mb-4">
                                <thead>
                                    <tr>
                                        <th>Due Date</th>
                                        <th>Amount Due</th>
                                        <th>Penalty</th>
                                        <th>Loan Payment</th>
                                        <th>Penalty Payment</th>
                                        <th>Total Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="psched in payment_schedules" :key="psched.id">
                                        <td>{{ formattedDate(psched.due_date,"short") }}</td>
                                        <td class="text-right">{{ money.format(psched.amount_due) }}</td>
                                        <td class="text-right text-red-700">{{ money.format(psched.penaltyAmount) }}</td>
                                        <td class="text-right">
                                            {{ money.format(psched.totalPayments) }}
                                        </td>
                                        <td class="text-right">{{ money.format(psched.penaltyPayment) }}</td>
                                        <td class="text-right">
                                            {{ money.format(psched.penaltyPayment+psched.totalPayments) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>TOTAL</td>
                                        <td class="text-right">{{ money.format(totalAmountDue) }}</td>
                                        <td class="text-right">{{ money.format(totalPenalty) }}</td>
                                        <td class="text-right">{{ money.format(totalLoanPayment) }}</td>
                                        <td class="text-right">{{ money.format(totalPenaltyPayment) }}</td>
                                        <td class="text-right">{{ money.format(totalPenaltyPayment + totalLoanPayment) }}</td>
                                    </tr>
                                </tbody>
                                <!-- <Link method="post" :href="'/loans/set-status/0/' + borrower.activeLoan.id">Reset</Link> -->
                            </table>
                        </div>
                    </div>
                    <div v-if="pending_loan">
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <h4 class="text-xl my-3">Pending Loan</h4>
                                <LoanTable :loan="pending_loan"></LoanTable>
                            </div>
                            <div class="flex-1">
                                <div class="flex flex-col p-8 w-[20rem]">
                                    <Link method="post" v-if="pending_loan.status==0" :href="'/loans/set-status/1/' + pending_loan.id" class="bg-teal-700 text-white rounded px-8 py-4 text-center mb-3">Confirm</Link>
                                    <Link method="post" :href="'/loans/set-status/2/' + pending_loan.id" class="bg-blue-700 text-white rounded px-8 py-4 text-center mb-3">Release</Link>
                                    <Link method="post" :href="'/loans/set-status/4/' + pending_loan.id" class="bg-red-700 text-white rounded px-8 py-4 text-center mb-3">Denied</Link>
                                    <Link method="post" :href="'/loans/set-status/5/' + pending_loan.id" class="bg-purple-700 text-white rounded px-8 py-4 text-center mb-3">Incomplete</Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="!borrower.activeLoan && !pending_loan">
                        <div class="text-green-600 italic">No Active Loan</div>
                        <div class="flex items-center">
                            <Link :href="'/loans/create/' + borrower.id" class="bg-green-700 text-white px-4 py-2 rounded my-4">
                                <font-awesome-icon icon="fa-solid fa-hand-holding-dollar"></font-awesome-icon>
                                Create New Loan
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
       </PageContent>
    </AuthenticatedLayout>
</template>

<style>
table {
    width: 100%;
}

tr {
    @apply even:bg-green-200 dark:even:bg-green-700;
}

th {
    text-align: left;
    border-bottom: 1px solid rgb(60, 165, 60);
}

td {
    @apply py-2 px-3;
    vertical-align: top;
    border: 0;
    border-bottom: 1px solid rgb(60, 165, 60);
}
</style>
