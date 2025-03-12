<script setup>
import Modal from '@/Components/Modal.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    paymentSchedule: Object,
    show: false
})

const emits = defineEmits(['close'])

const formattedDate = (dateStr, monthFormat="long") => {
    const date = new Date(dateStr)
    const formatter = new Intl.DateTimeFormat("en-US", {
        month: monthFormat,
        day: "numeric",
        year: "numeric"
    })
    return formatter.format(date)
}
const money = Intl.NumberFormat('en-PH',{style: 'currency', currency:"php"})

const onRemove = () => {
    router.delete('/penalty/' + props.paymentSchedule.id)
    emits('close')
}

</script>

<template>
    <Modal maxWidth="lg" :show="show">
        <div class="p-8">
            <h3 class="text-xl">Remove Penalty</h3>
            <hr class="mb-4">

            <div class="bg-yellow-200 p-4 rounded">
                You are about to remove the penalty of this account's
                payment schedule dated {{ formattedDate(paymentSchedule.due_date) }}
                in the amount of {{ money.format(paymentSchedule.penaltyAmount) }}
            </div>

            <div class="flex justify-between mt-3">
                <button class="bg-red-600 text-white px-4 py-2 rounded" @click="onRemove()">
                    Remove Penalty
                </button>
                <button class="bg-gray-100 px-4 py-2 rounded" @click="$emit('close')">
                    Close
                </button>
            </div>
        </div>
    </Modal>
</template>
