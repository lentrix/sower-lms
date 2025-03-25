<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import Modal from './Modal.vue';
import { ref } from 'vue';
import InputLabel from './InputLabel.vue';
import TextInput from './TextInput.vue';


const props = defineProps({
    loan: Object
})

const form = useForm({
    loan_id: props.loan.id,
    released_at: props.loan.released_at
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

const showUpdateReleaseDataModal = ref(false)


const submit = () => {
    form.patch('/loans/' + props.loan.id)
    showUpdateReleaseDataModal.value = false
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
                <td class="flex justify-between">
                    <div>{{ formattedDate(loan.released_at) }}</div>
                    <button class="bg-gray-300-p-1 rounded text-blue-600" @click="showUpdateReleaseDataModal=true">
                        <font-awesome-icon icon="fa-solid fa-edit"></font-awesome-icon>
                    </button>
                </td>
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
    <Modal maxWidth="md" :show="showUpdateReleaseDataModal" @close="showUpdateReleaseDataModal=false" >
        <div class="p-8 flex flex-col gap-4 border border-gray-300">
            <h3 class="text-xl">Update Release Date</h3>
            <form @submit.prevent="submit">
                <div class="mb-3">
                    <InputLabel for="release_date">Release Date:</InputLabel>
                    <TextInput id="release_date" type="date" v-model="form.released_at" class="block"></TextInput>
                </div>
                <button class="bg-blue-700 text-white px-8 py-2 rounded" type="submit">
                    Update
                </button>
            </form>
        </div>
    </Modal>
</template>
