<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { defineProps, onMounted } from 'vue';
import { useToast } from "vue-toastification";

const props = defineProps({
    borrower: null,
    payment_schedules: null,
    alerts: null
})

const toast = useToast();

if(props.alerts.success) toast.success(props.alerts.success);

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
    <Head title="Dashboard" />

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
                <div class="w-2/6 px-4 py-2 rounded bg-green-100 dark:bg-green-800">
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
                <div class="w-4/6 px-4 py-2 rounded bg-green-100 dark:bg-green-800">
                    <h4 class="text-2xl">Loan Summary</h4>
                    <hr>
                    <div v-if="borrower.activeLoan" class="flex flex-row gap-4">
                        <div>
                            <table>
                                <tbody>
                                    <tr>
                                        <th>Category</th>
                                        <td>
                                            {{ borrower.activeLoan.plan.name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Loan Plan</th>
                                        <td>{{ borrower.activeLoan.loan_plan.planText }}</td>
                                    </tr>
                                    <tr>
                                        <th>Principal</th>
                                        <td>{{ money.format(borrower.activeLoan.amount) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Interest Rate</th>
                                        <td>{{ borrower.activeLoan.loan_plan.interest }}%</td>
                                    </tr>
                                    <tr>
                                        <th>Amortization</th>
                                        <td>{{ money.format(borrower.activeLoan.amortization) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Purpose</th>
                                        <td>{{ borrower.activeLoan.purpose }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>{{ borrower.activeLoan.status }} | {{ borrower.activeLoan.statusText }}</td>
                                    </tr>
                                    <tr v-if="borrower.activeLoan.status==2">
                                        <th>Release Date</th>
                                        <td>{{ formattedDate(borrower.activeLoan.released_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex-1">
                            <h5 class="text-xl">Payment Schedule</h5>
                            <table>
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
                                        <td class="text-right">{{ money.format(borrower.activeLoan.amortization) }}</td>
                                        <td class="text-right text-red-700">{{ money.format(psched.penaltyAmount) }}</td>
                                        <td class="text-right">
                                            {{ money.format(psched.totalPayments) }}
                                        </td>
                                        <td class="text-right">{{ money.format(psched.penaltyPayment) }}</td>
                                        <td class="text-right">
                                            {{ money.format(psched.penaltyPayment+psched.totalPayments) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else>
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
