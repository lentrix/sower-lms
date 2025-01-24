<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    borrowers: []
})

</script>

<template>

    <Head title="Borrowers" />
    <AuthenticatedLayout>

        <div class="py-2">
            <div class="mx-auto sm:px-2 lg:px-4">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between">
                            <h2 class="text-2xl">List of Borrowers</h2>
                            <form @submit.prevent="searchBorrower">
                                <input type="text" class="py bg-gray-600 rounded border-gray-300" placeholder="Search...">
                            </form>
                        </div>
                        <table class="w-full my-4">
                            <thead>
                                <tr class="bg-gray-900 text-white">
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Active Loans</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="b in borrowers" :key="b.id">
                                    <td>{{ b.last_name }}, {{ b.first_name }} {{ b.middle_name }}</td>
                                    <td>{{ b.address }}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>

</template>

<style>
td, th {
    border: 1px solid #777;
    padding: 4px;
}
</style>
