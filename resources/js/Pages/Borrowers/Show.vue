<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { defineProps, onMounted } from 'vue';
import { useToast } from "vue-toastification";

const props = defineProps({
    borrower: null,
    alerts: null
})

const toast = useToast();

if(props.alerts.success) toast.success(props.alerts.success);


</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
       <PageContent>
            <div class="flex justify-between items-center">
                <h3 class="text-2xl mb-3">Borrower Details</h3>
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
                <div class="w-2/5 px-4 py-2 rounded bg-green-100 dark:bg-green-800">
                    <h4 class="text-xl mb-2">Personal Information</h4>
                    <hr>
                    <table class="mt-3">
                        <tbody>
                            <tr>
                                <td class="text-bold text-xl">Name:</td>
                                <td class="text-xl">
                                    {{ borrower.last_name }}, {{ borrower.first_name }} {{ borrower.middle_name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-bold">Address:</td>
                                <td>
                                    {{ borrower.address }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-bold">Contact Number:</td>
                                <td>
                                    {{ borrower.contact_no }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-bold">Email Address:</td>
                                <td>
                                    {{ borrower.email }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-bold">Tax ID:</td>
                                <td>
                                    {{ borrower.tax_id }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="w-3/5 px-4 py-2 rounded bg-green-100 dark:bg-green-800">
                    <h4 class="text-xl">Loan Summary</h4>
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

td {
    @apply py-2 px-3;
    vertical-align: top;
    border: 0;
    width: 4rem;
    border-bottom: 1px solid rgb(60, 165, 60);
}
</style>
