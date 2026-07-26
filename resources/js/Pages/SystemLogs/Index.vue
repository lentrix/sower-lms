<script setup>
import PageContent from '@/Components/PageContent.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    logs: Object
})

const expanded = ref([])

const toggle = (id) => {
    if(expanded.value.includes(id)) expanded.value = expanded.value.filter(i => i!=id)
    else expanded.value.push(id)
}

</script>

<template>

    <Head title="System Logs" />

    <AuthenticatedLayout>
        <PageContent>
            <h4 class="mb-3 text-2xl">System Logs</h4>

            <table>
                <thead>
                    <tr>
                        <th>Date &amp; Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th class="text-center">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="logs.data.length==0">
                        <td colspan="5" class="italic text-center text-gray-500">No system logs recorded yet.</td>
                    </tr>
                    <template v-for="log in logs.data" :key="log.id">
                        <tr>
                            <td class="whitespace-nowrap">{{ log.created_at }}</td>
                            <td>{{ log.user }}</td>
                            <td><span class="px-2 py-1 font-mono text-xs bg-gray-200 rounded dark:bg-gray-700">{{ log.action }}</span></td>
                            <td>{{ log.description }}</td>
                            <td class="text-center">
                                <button v-if="log.properties" type="button"
                                    class="px-3 py-1 text-white bg-green-800 rounded"
                                    @click="toggle(log.id)">
                                    {{ expanded.includes(log.id) ? 'Hide' : 'View' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="log.properties && expanded.includes(log.id)">
                            <td colspan="5">
                                <pre class="overflow-x-auto p-3 text-xs bg-gray-100 rounded dark:bg-gray-700">{{ JSON.stringify(log.properties, null, 2) }}</pre>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div class="flex flex-wrap gap-1 mt-4" v-if="logs.links.length > 3">
                <template v-for="(link, i) in logs.links" :key="i">
                    <Link v-if="link.url" :href="link.url"
                        class="px-3 py-1 rounded border"
                        :class="link.active ? 'bg-green-800 text-white border-green-800' : 'bg-white text-gray-700 border-gray-300'"
                        v-html="link.label" />
                    <span v-else class="px-3 py-1 text-gray-400 rounded border border-gray-200" v-html="link.label"></span>
                </template>
            </div>
        </PageContent>
    </AuthenticatedLayout>

</template>

<style scoped>
table {
    width: 100%;
}

tr {
    @apply even:bg-green-100 dark:even:bg-green-900;
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
