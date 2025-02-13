<script setup>
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    planTypes: Array
})

const form = useForm({
    type: null,
    barangay: '',
    town: '',
})

const submit = () => {
    form.get('/dashboard')
}

</script>

<template>
    <Modal :show="show" max-width="md">
        <div class="p-8">
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-xl">Filter Dues List</h3>
                <button @click="$emit('close')">
                    <font-awesome-icon icon="fa-solid fa-xmark"></font-awesome-icon>
                </button>
            </div>
            <hr class="mb-3">
            <form @submit.prevent="submit">
                <div class="mb-3">
                    <label for="type" class="block text-sm text-gray-600 font-medium dark:text-gray-400">Plan</label>
                    <select v-model="form.type" type="text" id="type" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600">
                        <option v-for="type, plan_type in planTypes" :value="plan_type">{{ type }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="barangay" class="block text-sm text-gray-600 font-medium dark:text-gray-400">Barangay</label>
                    <input v-model="form.barangay" type="text" id="barangay" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                </div>
                <div class="mb-3">
                    <label for="town" class="block text-sm text-gray-600 font-medium dark:text-gray-400">Town</label>
                    <input v-model="form.town" type="text" id="town" class="block w-full border-gray-500 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                </div>
                <div class="my-3">
                    <PrimaryButton type="submit">
                        <font-awesome-icon icon="fa-solid fa-filter"></font-awesome-icon>
                        &nbsp; Filter Dues Today
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
