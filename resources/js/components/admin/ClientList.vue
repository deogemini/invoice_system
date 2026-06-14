<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h1 class="text-2xl font-bold">Clients</h1>
            <router-link :to="{ name: 'admin.clients.create' }" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Register Client
            </router-link>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="py-3 px-4 text-left">Name</th>
                        <th class="py-3 px-4 text-left">Email</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="client in clients" :key="client.id" class="border-t">
                        <td class="py-3 px-4">{{ client.name }}</td>
                        <td class="py-3 px-4">{{ client.email }}</td>
                        <td class="py-3 px-4">
                            <span :class="client.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700'" class="text-xs font-semibold px-2 py-1 rounded">
                                {{ client.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <router-link :to="{ name: 'admin.clients.edit', params: { id: client.id } }" class="text-blue-600 hover:underline">
                                Edit
                            </router-link>
                        </td>
                    </tr>
                    <tr v-if="clients.length === 0">
                        <td colspan="4" class="py-6 px-4 text-center text-gray-500">No clients found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const clients = ref([]);

const fetchClients = async () => {
    const response = await axios.get('/api/admin/clients');
    clients.value = response.data.clients;
};

onMounted(fetchClients);
</script>
