<script setup>
// import ApplicationLogo from '@/Components/ApplicationLogo.vue';
// import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-green-200 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900"
    >
        <div>
            <img src="/images/sower_logo.png" class="w-[220px] border-2 border-green-300 rounded-full" alt="Sower Logo">
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800"
        >
            <slot />
        </div>

        <div>
            <p class="mt-8 text-center text-xs text-gray-600 dark:text-gray-400">
                &copy; 2025 All rights reserved.
            </p>
        </div>
    </div>
</template>
