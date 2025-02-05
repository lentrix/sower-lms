<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    payees: Array,
    selectedPayee: Object,
    payablePenalties: Object,
    unPaidSchedules: Object,
    balance: Number,
    payments: Array
})

const money = Intl.NumberFormat('en-PH',{style: 'currency', currency:"php"})

const searchResults = ref([])

const payee = ref('')

const getCurrentDate = () => {
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

const form = useForm({
    date: getCurrentDate(),
    or_number: '',
    amount_paid: '',
    loan_id: props.selectedPayee ? props.selectedPayee.activeLoan.id : ''
})

const submitPayment = () => {
    form.post('/payments')
}

const searchPayee = () => {
    if(payee.value.length>=3) {
        searchResults.value = props.payees.filter(p=>{
            const searchItem = payee.value.toLowerCase()
            console.log("Searching for " + searchItem + "...")

            return (
                p.last_name?.toLowerCase().includes(searchItem) ||
                p.first_name?.toLowerCase().includes(searchItem) ||
                p.middle_name?.toLowerCase().includes(searchItem)
            )
        })
    }
}

</script>

<template>
    <Head title="Payments" />

    <AuthenticatedLayout>
       <div class="flex gap-2">
            <PageContent class="w-2/6">
                <h2 class="text-2xl pb-2">Payment Entry</h2>
                <hr>
                <div class="my-3 flex flex-col">
                    <div v-if="props.selectedPayee==null">
                        <label for="payee">Search Payee</label>
                        <input type="text" @keyup="searchPayee" v-model="payee" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600">
                        <div class="p-2 shadow border border-gray-200 mt-1 flex flex-col" v-if="searchResults.length>0">
                            <Link :href="'/payments/payee/' + sr.id" v-for="sr in searchResults" :key="sr.id"
                                    class="border-b border-gray-300 p-2 cursor-pointer hover:bg-gray-200">
                                {{ sr.last_name }}, {{ sr.first_name }} {{ sr.middle_name }}
                            </Link>
                        </div>
                    </div>
                    <div v-else>
                        <div class="flex gap-1 justify-end">
                            <Link class="bg-red-600 text-white rounded px-2" href="/payments">
                                <font-awesome-icon icon="fa-solid fa-xmark"></font-awesome-icon>
                            </Link>
                            <Link class="bg-blue-600 text-white rounded px-2" :href="'/borrowers/' + props.selectedPayee.id">
                                <font-awesome-icon :icon="['fas', 'square-arrow-up-right']" />
                            </Link>
                        </div>
                        <table class="m-2 w-full">
                            <tbody>
                                <tr>
                                    <th>Name: </th>
                                    <td>{{ selectedPayee.last_name }}, {{ selectedPayee.first_name }} {{ selectedPayee.middle_name }}</td>
                                </tr>
                                <tr>
                                    <th>Address: </th>
                                    <td>{{ selectedPayee.address }}</td>
                                </tr>
                                <tr>
                                    <th>Loan: </th>
                                    <td>
                                        {{ selectedPayee.activeLoan.loan_plan.categoryName }}
                                        {{ selectedPayee.activeLoan.loan_plan.planText }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Principal: </th>
                                    <td>{{ money.format(selectedPayee.activeLoan.amount) }}</td>
                                </tr>
                                <tr>
                                    <th>Due Payable:</th>
                                    <td>
                                        {{ money.format(unPaidSchedules.total) }}
                                        ({{ props.unPaidSchedules.count }} count{{ unPaidSchedules.count>1 ? 's' : '' }})
                                    </td>
                                </tr>
                                <tr>
                                    <th>Balance:</th>
                                    <td>{{ money.format(balance) }}</td>
                                </tr>
                                <tr>
                                    <th>Penalty Payable:</th>
                                    <td>{{ money.format(payablePenalties.total) }}  ({{ payablePenalties.count }} count{{ payablePenalties.count>1 ? 's' : '' }})</td>
                                </tr>
                            </tbody>
                        </table>
                        <form @submit.prevent="submitPayment" class="mx-2 my-3" v-if="balance>0">
                            <div class="mb-3 flex flex-col">
                                <label for="date">Date::</label>
                                <input v-model="form.date" type="date" id="date" :class="form.errors.date ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                            </div>
                            <div class="mb-3 flex flex-col">
                                <label for="or_number">O.R. Number:</label>
                                <input v-model="form.or_number" ref="orno" type="text" id="or_number" :class="form.errors.or_number ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                            </div>
                            <div class="mb-3 flex flex-col">
                                <label for="amount_paid">Amount Paid:</label>
                                <input v-model="form.amount_paid" type="text" id="amount_paid" :class="form.errors.amount_paid ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                            </div>
                            <div class="my-3 flex">
                                <button class="bg-blue-700 text-white px-8 py-2 rounded border border-blue-300" type="submit">
                                    <font-awesome-icon icon="fa-solid fa-money-bill-1"></font-awesome-icon>
                                    Save Payment Entry
                                </button>
                            </div>
                        </form>
                        <div v-else class="py-8">
                            <Link :href="'/loans/set-status/3/' + selectedPayee.activeLoan.id" method="post" class="bg-green-800 text-white px-8 py-4 rounded my-8">Complete Loan</Link>
                        </div>
                    </div>
                </div>
            </PageContent>
            <PageContent class="w-4/6">
                <h2 class="text-2xl pb-2">Payment History</h2>
                <hr>
                <table class="mt-3 w-full">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>OR Number</th>
                            <th>Payee</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment in payments" :key="payment.id">
                            <td>{{ payment.date }}</td>
                            <td>{{ payment.orno }}</td>
                            <td>{{ payment.payee }}</td>
                            <td class="text-right">{{ money.format(payment.amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </PageContent>
       </div>
    </AuthenticatedLayout>
</template>


<style>

th {
    text-align: left;
}

td, th {
    vertical-align: top;
    border-bottom: 1px solid #ddd;
    padding: 8px;
}

</style>
