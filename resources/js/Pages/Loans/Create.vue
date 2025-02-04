<script setup>
import PageContent from '@/Components/PageContent.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';


const props = defineProps({
    borrower: Object,
    loan_plans: Object,
    categories: Object
})

const form = useForm({
    ref_no: '',
    borrower_id: props.borrower.id,
    purpose: '',
    amount: '',
    transaction_fee: '100',
    released_at: '',
    plan: {
        plan_type:'',
        month: '',
        penalty: '',
        payment_schedules: '',
    },
    category_id: ''
})

const submit = () => {
    form.post('/loans/')
}

const selectedCategory = ref(null)

watch(()=>form.category_id, (value, oldValue) => {
    selectedCategory.value = props.categories.filter((cat)=>cat.id==value)[0];
})

</script>

<template>
    <Head title="Create Loan" />

    <AuthenticatedLayout>
        <PageContent width="60">
            <h2 class="text-2xl mb-5">Create Loan</h2>
            <div class="flex items-start gap-4">
                <div class="flex-1">
                    <h3 class="text-xl mb-3">Borrower Details</h3>
                    <hr>
                    <div v-if="borrower" class="bg-green-400">
                        <table class="mt-2">
                            <tbody>
                                <tr><th>Name</th><td>{{ borrower.last_name }}, {{ borrower.first_name }}</td></tr>
                                <tr><th>Address</th><td>{{ borrower.address }}</td></tr>
                                <tr><th>Phone</th><td>{{ borrower.phone }}</td></tr>
                                <tr><th>Email Address</th><td>{{ borrower.email }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl mb-3">Loan Details</h3>
                    <hr>
                    <form @submit.prevent="submit" class="mt-3">
                        <input type="hidden" v-model="form.borrower_id">
                        <div class="mb-4">
                            <label for="ref_no" class="block text-sm text-gray-600 font-medium dark:text-gray-400">Reference No.:</label>
                            <input v-model="form.ref_no" type="text" id="ref_no" :class="form.errors.ref_no ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                            <i v-if="form.errors.ref_no" class="text-red-600 text-sm">{{ form.errors.ref_no }}</i>
                        </div>

                        <div class="flex gap-4">
                            <div class="flex-1 mb-4">
                                <label for="category_id" class="block text-sm text-gray-600 font-medium dark:text-gray-400">Category: {{ form.category_id }}</label>
                                <select id="category_id" v-model="form.category_id" :class="form.errors.category_id ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600">
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <!-- <input v-model="form.interest_rate" type="text" id="interest_rate" :class="form.errors.interest_rate ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" /> -->
                                <i v-if="form.errors.category_id" class="text-red-600 text-sm">{{ form.errors.category_id }}</i>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm text-gray-600 font-medium dark:text-gray-400">Interest Rate:</label>
                                <div>{{ (selectedCategory?.interest_rate) }}%</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="loan_plan" class="block text-sm text-gray-600 font-medium dark:text-gray-400">Loan Plan: {{ form.plan.plan_type }}</label>
                            <select id="loan_plan" v-model="form.plan" :class="form.errors.loan_plan ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600">
                                <option v-for="plan, name in loan_plans" :key="plan" :value="plan">
                                    {{ name }}
                                </option>
                            </select>
                            <!-- <input v-model="form.loan_plan" type="text" id="loan_plan" :class="form.errors.loan_plan ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" /> -->
                            <i v-if="form.errors.loan_plan" class="text-red-600 text-sm">{{ form.errors.loan_plan }}</i>
                        </div>
                        <div class="flex gap-4 mb-4">
                            <div class="flex-1">
                                <label for="loan_plan_month" class="block text-sm text-gray-600 font-medium dark:text-gray-400">Months:</label>
                                <div v-if="form.plan.name=='Bi-Monthly'">{{ form.plan.month }}</div>
                                <input v-else v-model="form.plan.month" type="text" id="loan_plan_month"
                                        @change="form.plan.payment_schedules = form.plan.month*2"
                                        :class="form.errors.plan?.month ? 'border-red-400' :''"
                                        class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                            </div>
                            <div class="flex-1">
                                <label for="loan_plan_penalty" class="block text-sm text-gray-600 font-medium dark:text-gray-400">Penalty:</label>
                                <input v-model="form.plan.penalty" type="text" id="loan_plan_penalty"
                                        :readonly="true"
                                        :class="form.errors.plan?.penalty ? 'border-red-400' :''"
                                        class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                            </div>
                            <div class="flex-1">
                                <label for="loan_plan_payment_schedules" class="block text-sm text-gray-600 font-medium dark:text-gray-400">No. Payments:</label>
                                <input v-model="form.plan.payment_schedules" type="number" id="loan_plan_payment_schedules" :class="form.errors.plan?.payment_schedules ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                            </div>
                        </div>
                        <div class="flex gap-4 mb-4">
                            <div class="flex-1">
                                <label for="amount" class="block text-sm text-gray-600 font-medium dark:text-gray-400">Amount:</label>
                                <input v-model="form.amount" type="text" id="amount" :class="form.errors.amount ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                                <i v-if="form.errors.amount" class="text-red-600 text-sm">{{ form.errors.amount }}</i>
                            </div>
                            <div class="flex-1">
                                <label for="transaction_fee" class="block text-sm text-gray-600 font-medium dark:text-gray-400">Transaction Fee:</label>
                                <input v-model="form.transaction_fee" type="text" id="transaction_fee" :class="form.errors.transaction_fee ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                                <i v-if="form.errors.transaction_fee" class="text-red-600 text-sm">{{ form.errors.transaction_fee }}</i>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="purpose" class="block text-sm text-gray-600 font-medium dark:text-gray-400">Purpose:</label>
                            <input v-model="form.purpose" type="text" id="purpose" :class="form.errors.purpose ? 'border-red-400' :''" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                            <i v-if="form.errors.purpose" class="text-red-600 text-sm">{{ form.errors.purpose }}</i>
                        </div>
                        <button class="bg-green-700 text-white px-8 py-4 rounded" type="submit">
                            Create Loan
                        </button>
                    </form>
                </div>
            </div>
        </PageContent>
    </AuthenticatedLayout>
</template>

<style>
table {
    width: 100%;
}

th {
    text-align: left;
}

th, td {
    @apply border-green-900 border-b p-2 text-gray-700
}
</style>
