<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

const page = usePage()

const props = defineProps({
    borrowers: null,
    errors: Object
})

const searchForm = useForm({
    search: ''
})

const searchBorrower = () => {
    searchForm.get('/borrowers/search')
}

const showFilterModal = ref(false)

const form = useForm({
    barangay: '',
    town: ''
})

const filter = () => {
    form.get('/borrowers/filter')
}

</script>

<template>

    <Head title="Borrowers" />
    <AuthenticatedLayout>

        <PageContent>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl">List of Borrowers</h2>
                <div>
                    <Link href="/borrowers/create" class="px-2 py-1 text-white bg-green-700 rounded py">
                        <font-awesome-icon icon="fa-solid fa-user-plus"></font-awesome-icon>
                        Add Borrower
                    </Link>
                </div>
                <div class="flex gap-4">
                    <button
                        class="px-4 py-2 bg-gray-100 rounded border border-gray-300 hover:bg-white"
                        @click="showFilterModal=true"
                    >
                        <font-awesome-icon icon="fa-solid fa-filter"></font-awesome-icon>
                        Filter Borrowers
                    </button>
                    <form @submit.prevent="searchBorrower">
                        <input type="text" v-model="searchForm.search" class="rounded border-gray-300 py dark:bg-gray-600" placeholder="Search...">
                        <div v-if="errors" class="text-xs italic text-red-600">{{ errors.search }}</div>
                    </form>
                </div>
            </div>
            <table class="my-4 w-full">
                <thead>
                    <tr class="bg-gray-200 dark:bg-green-900 dark:text-white">
                        <th>ID#</th>
                        <th>Name</th>
                        <th>Address & Contacts</th>
                        <th>Active Loans</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="b in borrowers" :key="b.id" class="even:bg-green-100 dark:even:bg-gray-900" >
                        <td>{{ String(b.id).padStart(8,'0') }}</td>
                        <td>{{ b.last_name }}, {{ b.barangay }} {{ b.middle_name }}</td>
                        <td>
                            <div class="flex flex-col">
                                <div>{{ b.address }}</div>
                                <div>{{ b.contact_no }}</div>
                            </div>
                        </td>
                        <td>
                            <div v-if="b.activeLoan" class="flex flex-col">
                                <div>Category: {{ b.activeLoan.loan_plan.categoryName }}</div>
                                <div>Plan: {{ b.activeLoan.loan_plan.planText }}</div>
                                <div>Amount: ₱ {{ b.activeLoan.formattedAmount }}</div>
                            </div>
                            <div v-else class="italic text-green-600">
                                No active loan.
                            </div>
                        </td>
                        <td>
                            <div class="flex gap-2 justify-center">
                                <Link :href="'/payments/payee/' + b.id" class="text-xl text-green-500" v-if="b.activeLoan">
                                    <font-awesome-icon icon="fa-solid fa-hand-holding-dollar" title="Record Loan Payment"></font-awesome-icon>
                                </Link>
                                <Link v-else :href="'/loans/create/' + b.id" class="text-xl text-green-500">
                                    <font-awesome-icon icon="fa-solid fa-file-invoice-dollar" title="Create Loan"></font-awesome-icon>
                                </Link>
                                <Link :href="'/borrowers/' + b.id" class="text-xl text-green-500">
                                    <font-awesome-icon icon="fa-solid fa-eye" title="View Borrower Profile"></font-awesome-icon>
                                </Link>
                                <Link :href="'/borrowers/edit/' + b.id" class="text-xl text-green-500">
                                    <font-awesome-icon icon="fa-solid fa-edit" title="Edit Borrower Profile"></font-awesome-icon>
                                </Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </PageContent>

    </AuthenticatedLayout>

    <Modal maxWidth="lg" :show="showFilterModal" @close="showFilterModal=false">
        <div class="p-8">
            <h3 class="text-xl">Filter Borrowers</h3>
            <form @submit.prevent="filter" class="my-3">

                <div class="mb-4">
                    <label for="barangay" class="block text-sm font-medium text-gray-600 dark:text-gray-400">Barangay</label>
                    <input v-model="form.barangay" type="text" id="barangay" :class="form.errors.barangay ? 'border-red-400' :''" class="block w-full rounded-md border-gray-500 focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                    <i v-if="form.errors.barangay" class="text-sm text-red-600">{{ form.errors.barangay }}</i>
                </div>

                <div class="mb-4">
                    <label for="town" class="block text-sm font-medium text-gray-600 dark:text-gray-400">Town</label>
                    <input v-model="form.town" type="text" id="town" :class="form.errors.town ? 'border-red-400' :''" class="block w-full rounded-md border-gray-500 focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                    <i v-if="form.errors.town" class="text-sm text-red-600">{{ form.errors.town }}</i>
                </div>

                <button
                    class="px-4 py-2 text-white bg-green-700 rounded hover:bg-green-600"
                    type="submit"
                >
                    Filter Borrowers
                </button>

            </form>
        </div>
    </Modal>

</template>

<style>
td, th {
    @apply px-4 py-2 border-b border-gray-300;
}
</style>
