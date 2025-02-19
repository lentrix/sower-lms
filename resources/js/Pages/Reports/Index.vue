<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    paymentReport: Array,
    cashFlowReport: Object,
    year: Number,
    month: Number
})

const money = Intl.NumberFormat('en-PH',{style: 'currency', currency:"php"})

const form = useForm({
    month: props.month,
    year: props.year,
})

const months = [
    { number: 1, name: 'January' },
    { number: 2, name: 'February' },
    { number: 3, name: 'March' },
    { number: 4, name: 'April' },
    { number: 5, name: 'May' },
    { number: 6, name: 'June' },
    { number: 7, name: 'July' },
    { number: 8, name: 'August' },
    { number: 9, name: 'September' },
    { number: 10, name: 'October' },
    { number: 11, name: 'November' },
    { number: 12, name: 'December' },
];

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 6 }, (_, i) => currentYear - 3 + i);

const submit = () => {
    form.get('/reports')
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
       <PageContent>
            <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-3">
                <h2 class="text-4xl">Reports</h2>
                <form @submit.prevent="submit">
                    <select v-model="form.month" id="month" class="mr-2 rounded-lg border border-gray-200 ps-4 pe-8 py-2">
                        <option v-for="month in months" :key="month.number" :value="month.number">
                            {{ month.name }}
                        </option>
                    </select>

                    <select v-model="form.year" id="year" class="mr-2 rounded-lg border border-gray-200 ps-4 pe-8 py-2">
                        <option v-for="year in years" :key="year" :value="year">
                            {{ year }}
                        </option>
                    </select>

                    <button type="submit" class="px-8 py-2 rounded-lg bg-green-700 hover:bg-green-600 transition text-white">
                        Generate Report
                    </button>
                </form>
            </div>
            <div class="grid grid-cols-5 gap-4">
                <div class="col-span-3">
                    <div class="p-6 rounded-lg bg-white shadow-md border border-gray-200">
                        <h3 class="text-2xl pb-3 border-b border-gray-200">
                            Payments
                        </h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>OR/FR No</th>
                                    <th>PAYOR</th>
                                    <th class="text-right">AMOUNT</th>
                                    <th class="text-right">PRINCIPAL</th>
                                    <th class="text-right">INTEREST</th>
                                    <th class="text-right">PENALTY</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="pmt in paymentReport" :key="pmt.id">
                                    <td>{{ pmt.date }}</td>
                                    <td>{{ pmt.or_number }}</td>
                                    <td>{{ pmt.payor }}</td>
                                    <td class="text-right">{{ money.format(pmt.amount) }}</td>
                                    <td class="text-right">{{ money.format(pmt.principal) }}</td>
                                    <td class="text-right">{{ money.format(pmt.interest) }}</td>
                                    <td class="text-right">{{ money.format(pmt.penalty) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="p-6 rounded-lg bg-white shadow-md border border-gray-200">
                        <h3 class="text-2xl pb-3 border-b border-gray-200">
                            Cash Flow
                        </h3>
                        <div>
                            <table>
                                <tbody>
                                    <tr>
                                        <th class="py-2 text-left">
                                            LOAN PAYMENTS
                                        </th>
                                        <td class="text-right">
                                            {{ money.format(cashFlowReport.loanPayment) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="py-2 text-left ps-8">
                                            PRINCIPAL
                                        </th>
                                        <td class="text-right">
                                            {{ money.format(cashFlowReport.principalPayment) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="py-2 text-left ps-8">
                                            INTEREST
                                        </th>
                                        <td class="text-right">
                                            {{ money.format(cashFlowReport.interestPayment) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="py-2 text-left ps-8">
                                            PENALTY
                                        </th>
                                        <td class="text-right">
                                            {{ money.format(cashFlowReport.penaltyPayment) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="py-2 text-left">
                                            PROCESSING FEE
                                        </th>
                                        <td class="text-right">
                                            {{ money.format(cashFlowReport.processing) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="py-2 text-left">
                                            INSURANCE FEE
                                        </th>
                                        <td class="text-right">
                                            {{ money.format(cashFlowReport.insurance) }}
                                        </td>
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

thead>tr {
    @apply bg-green-700 text-white font-semibold border-b border-green-300
}

thead>tr>th {
    @apply p-2
}

tbody>tr>td,
tbody>tr>th {
    @apply px-2 py-1
}

tbody>tr:nth-child(odd) {
    @apply bg-gray-200;
}

</style>
