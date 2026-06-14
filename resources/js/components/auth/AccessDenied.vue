<template>
    <div class="max-w-xl mx-auto border border-red-200 bg-red-50 rounded-lg p-6">
        <h1 class="text-2xl font-bold text-red-900 mb-2">Access denied</h1>
        <p class="text-red-800 mb-4">You do not have permission to view this page.</p>
        <router-link :to="{ name: fallbackRoute }" class="inline-block bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded">
            Go to dashboard
        </router-link>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { isAdministrator } from '../../auth';

const fallbackRoute = computed(() => isAdministrator() ? 'admin.dashboard' : 'client.dashboard');
</script>
