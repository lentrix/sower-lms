<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    borrowers: null
})

const searchForm = useForm({
    search: ''
})

const searchBorrower = () => {
    searchForm.post('/borrowers/search')
}

</script>

<template>

    <Head title="Borrowers" />
    <AuthenticatedLayout>

        <PageContent>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl">List of Borrowers</h2>
                <div>
                    <Link href="/borrowers/create" class="px-2 py bg-green-700 py-1 text-white rounded">
                        <font-awesome-icon icon="fa-solid fa-user-plus"></font-awesome-icon>
                        Add Borrower
                    </Link>
                </div>
                <form @submit.prevent="searchBorrower">
                    <input type="text" v-model="searchForm.search" class="py rounded border-gray-300 dark:bg-gray-600" placeholder="Search...">
                </form>
            </div>
            <table class="w-full my-4">
                <thead>
                    <tr class="bg-green-900 text-white">
                        <th>Name</th>
                        <th>Address & Contacts</th>
                        <th>Active Loans</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="b in borrowers.data" :key="b.id" class="even:bg-green-100 dark:even:bg-gray-900" >
                        <td>{{ b.last_name }}, {{ b.first_name }} {{ b.middle_name }}</td>
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
                            <div v-else class="text-green-600 italic">
                                No active loan.
                            </div>
                        </td>
                        <td>
                            <div class="flex justify-center gap-2">
                                <Link :href="'/borrowers/' + b.id" class="text-green-500">
                                    <font-awesome-icon icon="fa-solid fa-eye" title="View Borrower Profile"></font-awesome-icon>
                                </Link>
                                <Link :href="'/borrowers/edit/' + b.id" class="text-green-500">
                                    <font-awesome-icon icon="fa-solid fa-edit" title="Edit Borrower Profile"></font-awesome-icon>
                                </Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </PageContent>

    </AuthenticatedLayout>

</template>

<style>
td, th {
    @apply px-4 py-2 border-b border-gray-300;
}
</style>
