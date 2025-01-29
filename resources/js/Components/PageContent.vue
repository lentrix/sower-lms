<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    width: String
})

</script>

<template>

<div class="py-4">
    <div class="mx-auto sm:px-2 lg:px-4">
        <div
            class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800 mx-auto" :style="{ width: width + 'rem' }"
        >
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <slot />
            </div>
        </div>
    </div>
</div>

</template>
