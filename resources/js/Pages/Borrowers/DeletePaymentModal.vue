<script setup>
import Modal from '@/Components/Modal.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    payment: Object,
    show: Boolean
})

const emits = defineEmits(['close'])

const money = Intl.NumberFormat('en-PH',{style: 'currency', currency:"php"})

const formattedDate = (dateStr) => {
    const formatter = new Intl.DateTimeFormat("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric"
    })
    return formatter.format(new Date(dateStr))
}

const onDelete = () => {
    router.delete('/payments/' + props.payment.id, {
        preserveScroll: true,
        onSuccess: () => emits('close')
    })
}

</script>

<template>
    <Modal maxWidth="lg" :show="show" @close="$emit('close')">
        <div class="p-8" v-if="payment">
            <h3 class="text-xl">Delete Payment?</h3>
            <hr class="mb-4">

            <div class="p-4 text-red-800 bg-red-200 rounded">
                You are about to permanently delete the payment dated
                <strong>{{ formattedDate(payment.date) }}</strong>
                <span v-if="payment.or_number"> under OR# <strong>{{ payment.or_number }}</strong></span>
                in the amount of <strong>{{ money.format(payment.amount) }}</strong>.
                The remaining payments of this loan will be re-applied against the
                payment schedule and the loan balance will increase by this amount.
                This action is recorded in the system log.
            </div>

            <div class="flex justify-between mt-6">
                <button class="px-8 py-2 text-white bg-red-700 rounded" @click="onDelete()">
                    <font-awesome-icon icon="fa-solid fa-trash-can"></font-awesome-icon>
                    Delete Payment
                </button>
                <button class="px-8 py-2 bg-gray-100 rounded" @click="$emit('close')">
                    Cancel
                </button>
            </div>
        </div>
    </Modal>
</template>
