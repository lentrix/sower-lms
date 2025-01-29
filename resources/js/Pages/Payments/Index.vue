<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
       <PageContent>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl">Payments</h2>
            </div>
       </PageContent>
    </AuthenticatedLayout>
</template>
