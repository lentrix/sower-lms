<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    borrower: null
})

const form = useForm({
    first_name: props.borrower.first_name,
    middle_name: props.borrower.middle_name,
    last_name: props.borrower.last_name,
    address: props.borrower.address,
    contact_no: props.borrower.contact_no,
    email: props.borrower.email,
    tax_id: props.borrower.tax_id,
})


const submit = () => {
    form.put('/borrowers/' + props.borrower.id)
}

</script>

<template>
    <Head title="Create Borrower" />

    <AuthenticatedLayout>

        <PageContent width="40">
            <div class="w-md mx-auto">
                <h2 class="text-2xl">Edit Borrower</h2>
                <form @submit.prevent="submit">
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <div class="mb-4">
                                <label for="first_name" class="block text-sm font-medium text-green-900 dark:text-gray-400">First Name</label>
                                <input v-model="form.first_name" type="text" id="first_name" :class="form.errors.first_name ? 'border-red-400' :''" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                                <i v-if="form.errors.first_name" class="text-red-600 text-sm">{{ form.errors.first_name }}</i>
                            </div>

                            <div class="mb-4">
                                <label for="middle_name" class="block text-sm font-medium text-green-900 dark:text-gray-400">Middle Name</label>
                                <input v-model="form.middle_name" type="text" id="middle_name" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                                <i v-if="form.errors.middle_name" class="text-red-600 text-sm">{{ form.errors.middle_name }}</i>
                            </div>

                            <div class="mb-4">
                                <label for="last_name" class="block text-sm font-medium text-green-900 dark:text-gray-400">Last Name</label>
                                <input v-model="form.last_name" type="text" id="last_name" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                                <i v-if="form.errors.last_name" class="text-red-600 text-sm">{{ form.errors.last_name }}</i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="mb-4">
                                <label for="contact_no" class="block text-sm font-medium text-green-900 dark:text-gray-400">Contact Number</label>
                                <input v-model="form.contact_no" type="text" id="contact_no" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                                <i v-if="form.errors.contact_no" class="text-red-600 text-sm">{{ form.errors.contact_no }}</i>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="block text-sm font-medium text-green-900 dark:text-gray-400">Email</label>
                                <input v-model="form.email" type="email" id="email" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                                <i v-if="form.errors.email" class="text-red-600 text-sm">{{ form.errors.email }}</i>
                            </div>

                            <div class="mb-4">
                                <label for="tax_id" class="block text-sm font-medium text-green-900 dark:text-gray-400">Tax ID</label>
                                <input v-model="form.tax_id" type="text" id="tax_id" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                                <i v-if="form.errors.tax_id" class="text-red-600 text-sm">{{ form.errors.tax_id }}</i>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="address" class="block text-sm font-medium text-green-900 dark:text-gray-400">Address</label>
                        <input v-model="form.address" type="text" id="address" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                        <i v-if="form.errors.address" class="text-red-600 text-sm">{{ form.errors.address }}</i>
                    </div>

                    <div class="flex">
                        <button type="submit" class="w-50 py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <font-awesome-icon icon="fa-solid fa-floppy-disk"></font-awesome-icon>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </PageContent>

    </AuthenticatedLayout>
</template>
