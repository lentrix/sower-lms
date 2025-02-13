<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head, Link } from '@inertiajs/vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DuesFilterModal from './DuesFilterModal.vue';
import { ref } from 'vue';

const props = defineProps({
    summary: Object,
    filter: Object,
    dueToday: Array,
    planTypes: Array
})

const typeName = ['','Arawan','Weekly','Bi-Monthly']

const showFilterModal=ref(false)

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
       <PageContent>
            <div class="">
                <h2 class="text-2xl mb-4">Dashboard</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <div class="px-8 py-4 rounded shadow-sm border">
                            <h3 class="text-xl">Summary</h3>
                            <div class="flex justify-between mt-3">
                                <span class="text-2xl">Active Loans</span>
                                <span class="text-2xl">{{ summary.arawan + summary.weekly + summary.biMonthly }}</span>
                            </div>
                            <div class="flex justify-between mt-3">
                                <span class="text-xl">Arawan</span>
                                <span class="text-xl">{{ summary.arawan }}</span>
                            </div>
                            <div class="flex justify-between mt-3">
                                <span class="text-xl">Weekly</span>
                                <span class="text-xl">{{ summary.weekly }}</span>
                            </div>
                            <div class="flex justify-between mt-3">
                                <span class="text-xl">Bi-Monthly</span>
                                <span class="text-xl">{{ summary.biMonthly }}</span>
                            </div>
                        </div>

                        <div class="p-4 bg-purple-800 text-white rounded mt-4">
                            <h3 class="text-xl mb-3">Development Updates</h3>
                            <div>
                                <h4>February 13, 2025</h4>
                                <ul class="ps-8 text-sm">
                                    <li class="list-disc">
                                        Added Summary of Loan counts in the Dashboard with filtration
                                        based on Loan Type, Barangay, and Town
                                    </li>
                                    <li class="list-disc">
                                        Added a list of loans that are due arranged in alphabetical order.
                                    </li>
                                    <li class="list-disc">
                                        Implement self-signed SSL certificate for
                                        Ecryption and Security
                                    </li>
                                    <li class="list-disc">
                                        Added a backend command to fix the amortization of Arawan Loans
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h4>February 12, 2025</h4>
                                <ul class="ps-8 text-sm">
                                    <li class="list-disc">
                                        Added an initial list of Borrowers in the Borrowers page based on the latest
                                        loan released.
                                    </li>
                                    <li class="list-disc">
                                        Separated the address data into Barangay, Town, & Province
                                    </li>
                                </ul>
                            </div>

                        </div>

                    </div>
                    <div class="px-8 py-4 md:col-span-3 rounded shadow-sm border h-[500px] overflow-y-scroll">
                        <div class="flex justify-between items-start">
                            <h3 class="text-xl">
                                Due Today ({{ dueToday.length }} accounts)
                                {{ filter.filter ? "Filtered "  + filter.filter + ":" : '' }}
                                {{ filter.value ? filter.value : '' }}
                            </h3>
                            <SecondaryButton @click="showFilterModal=true">
                                <font-awesome-icon icon="fa-solid fa-filter"></font-awesome-icon>
                                &nbsp; Filter
                            </SecondaryButton>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th class="text-left">Borrower</th>
                                    <th class="text-left">Address</th>
                                    <th class="text-left">Type</th>
                                    <th class="text-right">Amount Due</th>
                                    <th><font-awesome-icon icon="fa-solid fa-cog"></font-awesome-icon></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="due in dueToday" :key="due.id">
                                    <td class="flex flex-col">
                                        <span>{{ due.borrower }}</span>
                                        <span class="text-sm italic">{{ due.contact_no }}</span>
                                    </td>
                                    <td class="capitalize">{{ due.address }}</td>
                                    <td>{{ typeName[due.type] }}</td>
                                    <td class="text-right">{{ due.due }}</td>
                                    <td>
                                        <Link class="text-gray-600 mx-2" :href="'/borrowers/' + due.id">
                                            <font-awesome-icon icon="fa-solid fa-square-arrow-up-right"></font-awesome-icon>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
       </PageContent>
    </AuthenticatedLayout>

    <DuesFilterModal :show="showFilterModal" @close="showFilterModal=false" :planTypes="planTypes" />
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
