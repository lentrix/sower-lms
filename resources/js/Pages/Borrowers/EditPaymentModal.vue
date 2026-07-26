<script setup>
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    payment: Object,
    show: Boolean
})

const emits = defineEmits(['close'])

const form = useForm({
    amount: '',
    or_number: '',
    date: ''
})

//date arrives as an ISO datetime string, the date input needs YYYY-MM-DD
const dateOnly = (dateStr) => dateStr ? dateStr.substring(0,10) : ''

//re-seed every time the modal opens, not just when a different payment is picked
watch([()=>props.payment, ()=>props.show], ([payment, show]) => {
    if(!payment || !show) return;
    form.clearErrors()
    form.amount = payment.amount
    form.or_number = payment.or_number
    form.date = dateOnly(payment.date)
})

const submit = () => {
    form.put('/payments/' + props.payment.id, {
        preserveScroll: true,
        onSuccess: () => emits('close')
    })
}

const onClose = () => {
    form.clearErrors()
    emits('close')
}

</script>

<template>
    <Modal maxWidth="md" :show="show" @close="onClose()">
        <div class="p-8" v-if="payment">
            <h3 class="text-xl">Edit Payment</h3>
            <hr class="mb-4">

            <div class="p-4 mb-4 text-yellow-900 bg-yellow-200 rounded">
                Changing the amount or the date will re-apply every payment of this
                loan against its payment schedule. This action is recorded in the system log.
            </div>

            <form @submit.prevent="submit">
                <div class="my-4">
                    <label for="date" class="block text-sm font-medium text-green-900 dark:text-gray-400">Date</label>
                    <input v-model="form.date" type="date" id="date"
                        class="block w-full bg-gray-100 rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                    <i v-if="form.errors.date" class="text-sm text-red-600">{{ form.errors.date }}</i>
                </div>

                <div class="my-4">
                    <label for="or_number" class="block text-sm font-medium text-green-900 dark:text-gray-400">OR Number</label>
                    <input v-model="form.or_number" type="text" id="or_number"
                        class="block w-full bg-gray-100 rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                    <i v-if="form.errors.or_number" class="text-sm text-red-600">{{ form.errors.or_number }}</i>
                </div>

                <div class="my-4">
                    <label for="amount" class="block text-sm font-medium text-green-900 dark:text-gray-400">Amount</label>
                    <input v-model="form.amount" type="text" id="amount"
                        class="block w-full text-right bg-gray-100 rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500 sm:text-sm dark:bg-gray-600" />
                    <i v-if="form.errors.amount" class="text-sm text-red-600">{{ form.errors.amount }}</i>
                </div>

                <div class="flex gap-4 justify-between mt-6">
                    <button class="px-8 py-2 text-white bg-blue-700 rounded" type="submit" :disabled="form.processing">
                        <font-awesome-icon icon="fa-solid fa-floppy-disk"></font-awesome-icon>
                        Update Payment
                    </button>
                    <button class="px-8 py-2 bg-gray-100 rounded" type="button" @click="onClose()">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>
