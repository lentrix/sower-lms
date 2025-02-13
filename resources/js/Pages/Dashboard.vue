<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
       <PageContent>
            <div class="">
                <h2 class="text-2xl">Dashboard</h2>
                <div class="w-[600px] p-8 bg-green-500 text-white rounded">
                    <h3 class="text-xl mb-3">Development Updates</h3>
                    <table>
                        <tbody>
                            <tr>
                                <th>February 12, 2025</th>
                                <td>
                                    <ul class="ps-8">
                                        <li class="list-disc">
                                            Added an initial list of Borrowers in the Borrowers page based on the latest
                                            loan released.
                                        </li>
                                        <li class="list-disc">
                                            Separated the address data into Barangay, Town, & Province
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
       </PageContent>
    </AuthenticatedLayout>
</template>

<style>
table {
    width: 100%;
}

th, td {
    vertical-align: top;
}

tr {
    border-bottom: 1px solid rgb(138, 222, 138);
}
</style>
