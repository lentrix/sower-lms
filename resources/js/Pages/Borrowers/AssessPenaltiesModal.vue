<script setup>
import Modal from '@/Components/Modal.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    loan: Object,
    show: Boolean
})

const emits = defineEmits(['close'])

const money = Intl.NumberFormat('en-PH',{style: 'currency', currency:"php"})

const candidates = ref([])
const selected = ref([])
const loading = ref(false)
const submitting = ref(false)
const error = ref(null)

const load = async () => {
    loading.value = true
    error.value = null
    try {
        const { data } = await axios.get('/loans/' + props.loan.id + '/penalty-candidates')
        candidates.value = data.candidates
        // Default to all selected - staff deselect the ones they know are wrong.
        selected.value = data.candidates.map(c => c.schedule_id)
    } catch (e) {
        error.value = 'Could not load the payment schedules for this loan.'
    } finally {
        loading.value = false
    }
}

watch(() => props.show, (isShown) => {
    if(isShown) load()
})

const total = computed(() =>
    candidates.value
        .filter(c => selected.value.includes(c.schedule_id))
        .reduce((sum, c) => sum + Number(c.penalty_amount), 0)
)

const allSelected = computed(() =>
    candidates.value.length > 0 && selected.value.length === candidates.value.length
)

const toggleAll = () => {
    selected.value = allSelected.value ? [] : candidates.value.map(c => c.schedule_id)
}

const submit = () => {
    if(selected.value.length === 0) return
    submitting.value = true
    router.post('/loans/' + props.loan.id + '/assess-penalties',
        { schedule_ids: selected.value },
        {
            onFinish: () => {
                submitting.value = false
                emits('close')
            }
        }
    )
}
</script>

<template>
    <Modal maxWidth="2xl" :show="show" @close="$emit('close')">
        <div class="p-8">
            <h3 class="text-xl">Assess Penalties</h3>
            <hr class="mb-4">

            <div class="p-4 mb-4 text-red-800 bg-red-200 rounded">
                <strong>This charges the borrower.</strong>
                Payment records imported from the previous system are incomplete,
                so a schedule showing no payment is not proof the borrower was late.
                Check each row against the borrower's records and un-tick anything
                that was actually paid.
            </div>

            <div v-if="loading" class="py-8 italic text-center text-gray-500">
                Loading payment schedules&hellip;
            </div>

            <div v-else-if="error" class="p-4 text-red-700 bg-red-100 rounded">
                {{ error }}
            </div>

            <div v-else-if="candidates.length === 0" class="py-8 italic text-center text-gray-500">
                There are no payment schedules on this loan that can be penalized.
            </div>

            <div v-else>
                <div class="overflow-y-auto max-h-80">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="w-8">
                                    <input type="checkbox" :checked="allSelected" @change="toggleAll()" />
                                </th>
                                <th class="text-left">Due Date</th>
                                <th class="text-right">Amount Due</th>
                                <th class="text-right">Penalty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in candidates" :key="c.schedule_id">
                                <td class="text-center">
                                    <input type="checkbox" :value="c.schedule_id" v-model="selected" />
                                </td>
                                <td>{{ c.due_date }}</td>
                                <td class="text-right">{{ money.format(c.amount_due) }}</td>
                                <td class="text-right text-red-700">{{ money.format(c.penalty_amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between items-center px-2 py-3 mt-3 font-medium bg-gray-100 rounded">
                    <div>{{ selected.length }} of {{ candidates.length }} selected</div>
                    <div class="text-lg text-red-700">{{ money.format(total) }}</div>
                </div>
            </div>

            <div class="flex justify-between mt-4">
                <button
                    class="px-4 py-2 text-white bg-red-600 rounded disabled:opacity-50"
                    :disabled="selected.length === 0 || submitting"
                    @click="submit()"
                >
                    Impose {{ selected.length }} Penalt{{ selected.length === 1 ? 'y' : 'ies' }}
                </button>
                <button class="px-4 py-2 bg-gray-100 rounded" @click="$emit('close')">
                    Cancel
                </button>
            </div>
        </div>
    </Modal>
</template>
