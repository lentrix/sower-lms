<script setup>

const props = defineProps({
    borrower: Object,
    loanHistory: Array
})

const money = Intl.NumberFormat('en-PH',{style: 'currency', currency:"php"})

</script>

<template>
    <h3 class="mb-3 text-3xl">Borrower Details</h3>
    <div class="px-4 pt-2 pb-8 bg-green-100 rounded border border-green-300 shadow dark:bg-green-800">
        <h4 class="mb-2 text-2xl">Personal Information</h4>
        <hr>
        <table class="mt-3">
            <tbody>
                <tr>
                    <th class="text-xl text-bold">Name:</th>
                    <td class="text-xl">
                        {{ borrower.last_name }}, {{ borrower.first_name }} {{ borrower.middle_name }}
                    </td>
                </tr>
                <tr>
                    <th class="text-bold">Address:</th>
                    <td>
                        {{ borrower.barangay }}, {{ borrower.town }}, {{ borrower.province }}
                    </td>
                </tr>
                <tr>
                    <th class="text-bold">Contact Number:</th>
                    <td>
                        {{ borrower.contact_no }}
                    </td>
                </tr>
                <tr>
                    <th class="text-bold">Email Address:</th>
                    <td>
                        {{ borrower.email }}
                    </td>
                </tr>
                <tr>
                    <th class="text-bold">Tax ID:</th>
                    <td>
                        {{ borrower.tax_id }}
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="mt-6">
            <Link :href="'/borrowers/edit/' + props.borrower.id" class="px-3 py-2 text-sm text-white bg-indigo-500 rounded">
                <font-awesome-icon icon="fa-solid fa-edit"></font-awesome-icon>
                Edit Profile
            </Link>
        </div>
    </div>

    <div class="px-4 pt-2 pb-8 mt-4 bg-green-100 rounded border border-green-300 shadow dark:bg-green-800">
        <h4 class="mb-2 text-2xl">Loan History</h4>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>Plan</th>
                    <th class="text-right">Principal</th>
                    <th class="text-right">Status</th>
                    <th>
                        <font-awesome-icon icon="fa-solid fa-gear"></font-awesome-icon>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="hist in loanHistory" :key="hist.id">
                    <td>{{ hist.loan_plan.planText }}</td>
                    <td class="text-right">{{ money.format(hist.amount) }}</td>
                    <td class="text-right">{{ hist.statusText }}</td>
                    <td>
                        <Link v-if="hist.status==3" :href="'/borrowers/' + props.borrower.id + '/completed/' + hist.id">
                            <font-awesome-icon icon="fa-solid fa-square-arrow-up-right"></font-awesome-icon>
                        </Link>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
table {
    width: 100%;
}

tr {
    @apply even:bg-green-200 dark:even:bg-green-700;
}

th {
    text-align: left;
    border-bottom: 1px solid rgb(60, 165, 60);
    @apply py-2 px-3;
}

td {
    @apply py-2 px-3;
    vertical-align: top;
    border: 0;
    border-bottom: 1px solid rgb(60, 165, 60);
}
</style>
