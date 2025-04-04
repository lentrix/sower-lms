<script setup>
import BorrowerDetails from '@/Components/BorrowerDetails.vue';
import LoanTable from '@/Components/LoanTable.vue';
import PageContent from '@/Components/PageContent.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';


const props = defineProps({
    loan: Object,
    loanHistory: Array
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
            <div class="flex gap-4 items-start">
                <div class="4/9">
                    <BorrowerDetails :borrower="loan.borrower" :loanHistory="loanHistory" />
                </div>
                <div class="flex-1">
                    <div class="flex gap-2 justify-end mb-3" v-if="loan.borrower.activeLoan">
                        <a :href="'/loans/export/' + loan.borrower.activeLoan.id"
                            class="px-8 py-1 text-white bg-red-900 rounded border border-maroon-500"
                            target="_blank" rel="noopener noreferrer"
                        >
                            <font-awesome-icon icon="fa-solid fa-file-pdf"></font-awesome-icon>&nbsp;
                            Export PDF
                        </a>
                        <Link :href="'/borrowers/' + loan.borrower_id" class="px-8 py-1 text-white bg-indigo-700 rounded border border-indigo-500">
                            <font-awesome-icon icon="fa-solid fa-file-invoice"></font-awesome-icon>
                            Loan Summary
                        </Link>
                    </div>

                    <div class="px-4 py-2 bg-green-100 rounded border border-green-300 shadow dark:bg-green-800">
                        <h4 class="text-2xl">Payment History</h4>
                        <hr>
                        <div class="flex gap-8">
                            <div>
                                <LoanTable :loan="loan"></LoanTable>
                            </div>
                            <table class="flex-1 mb-4">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>OR Number</th>
                                        <td class="font-bold text-right">Amount</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="pmt in loan.payments">
                                        <td>{{ formattedDate(pmt.date) }}</td>
                                        <td>{{ pmt.or_number }}</td>
                                        <td class="text-right">{{ money.format(pmt.amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </PageContent>
    </AuthenticatedLayout>

</template>

<style scoped>
table {
    width: 100%;
}

tr {
    @apply even:bg-green-200 dark:even:bg-green-700;
}

th {
    text-align: left;
    border-bottom: 1px solid rgb(60, 165, 60);
    @apply py-2 px-3;
}

td {
    @apply py-2 px-3;
    vertical-align: top;
    border: 0;
    border-bottom: 1px solid rgb(60, 165, 60);
}

</style>
