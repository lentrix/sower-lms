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
    planTypes: null
})

const typeName = ['','Arawan','Weekly','Bi-Monthly']

const showFilterModal=ref(false)

const dateStr = new Date().toLocaleDateString('en-PH', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
})


console.log(props.filter)

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

                        <div class="p-4 bg-purple-800 text-white rounded text-xs mt-4 max-h-[500px] overflow-scroll">
                            <h3 class="text-xl mb-3">Development Updates</h3>
                            <div>
                                <h4>March 25, 2025</h4>
                                <ul class="ps-8 text-xs">
                                    <li class="list-disc">
                                        Fixed bug: Everytime a the Release button is clicked to release a Loan,
                                        the Loan's release date is now set to the current date of the server.
                                    </li>
                                    <li class="list-disc">
                                        For active loans, the release date can be updated by clicking a small button beside
                                        the release date and select a new release date and clicking on update.
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h4>March 12, 2025</h4>
                                <ul class="ps-8 text-xs">
                                    <li class="list-disc">
                                        Added automation implementation of penalty
                                        on delinquent accounts on a per payment schedule basis
                                        with different rules per loan type
                                    </li>
                                    <li class="list-disc">
                                        Added an 'X' that is visible on hover on each penalty
                                        amount that will show a dialog box for removing a penalty
                                        as an implementation workflow for approved penalty condonation
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h4>February 19, 2025</h4>
                                <ul class="ps-8 text-xs">
                                    <li class="list-disc">
                                        Added reports with two types: Cash Flow and Payments
                                        in a monthly basis.
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h4>February 18, 2025</h4>
                                <ul class="ps-8 text-xs">
                                    <li class="list-disc">
                                        Added a button to export the list of loan dues
                                        into a PDF file: fit for printing that is reactive of the
                                        filter.
                                    </li>
                                    <li class="list-disc">
                                        Add a button to set a loan as completed when
                                        the balance is zero or less.
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h4>February 17, 2025</h4>
                                <ul class="ps-8 text-xs">
                                    <li class="list-disc">
                                        Added a button to export the active loan of
                                        a borrower into a PDF file: fit for printing.
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h4>February 15, 2025</h4>
                                <ul class="ps-8 text-xs">
                                    <li class="list-disc">
                                        Developed backend command to fix
                                        proportional distribution of principal
                                        and interest in each Loan Payment.
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h4>February 14, 2025</h4>
                                <ul class="ps-8 text-xs">
                                    <li class="list-disc">
                                        Added ID Number to Borrower List
                                    </li>
                                    <li class="list-disc">
                                        Viewing of previously completed loan
                                    </li>
                                </ul>
                            </div>
                            <div>
                                <h4>February 13, 2025</h4>
                                <ul class="ps-8 text-xs">
                                    <li class="list-disc">
                                        Added Summary of Loan counts in the Dashboard
                                    </li>
                                    <li class="list-disc">
                                        Added a list of loans that are due arranged in alphabetical order
                                        with filtration based on Loan Type, Barangay, and Town
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
                                <ul class="ps-8 text-xs">
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
                    <div class="px-8 py-4 md:col-span-3 rounded shadow-sm border">
                        <div class="flex justify-between items-start">
                            <h3 class="text-xl">
                                Due Today: {{ dateStr }} ({{ dueToday.length }} accounts)
                                {{ filter.filter ? "Filtered "  + filter.filter + ":" : '' }}
                                {{ filter.value ? filter.value : '' }}
                            </h3>
                            <div class="flex gap-3">
                                <a :href="'/due-today?barangay=' + (filter.barangay?filter.barangay:'') + '&town=' + (filter.town?filter.town:'') + '&type=' + (filter.type?filter.type:'')"
                                    class="bg-red-900 text-white px-2 py-1 rounded"
                                    target="_blank"
                                >
                                    <font-awesome-icon icon="fa-solid fa-file-pdf"></font-awesome-icon>
                                </a>
                                <SecondaryButton @click="showFilterModal=true">
                                    <font-awesome-icon icon="fa-solid fa-filter"></font-awesome-icon>
                                    &nbsp; Filter
                                </SecondaryButton>
                            </div>
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
