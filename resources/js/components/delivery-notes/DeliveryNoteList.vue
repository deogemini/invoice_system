<template>
    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Delivery Notes</h1>
            <router-link :to="{ name: 'delivery-notes.create' }" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                New Delivery Note
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-4">Loading delivery notes...</div>
        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ error }}</div>

        <div v-else class="bg-white shadow-md rounded my-6 overflow-x-auto">
            <table class="min-w-full w-full table-auto">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Delivery Note #</th>
                        <th class="py-3 px-6 text-left">Date</th>
                        <th class="py-3 px-6 text-left">Customer</th>
                        <th class="py-3 px-6 text-left">Reference</th>
                        <th class="py-3 px-6 text-left">Owner</th>
                        <th class="py-3 px-6 text-left">Updated By</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <tr v-for="note in deliveryNotes" :key="note.id" class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 font-bold">{{ note.number }}</td>
                        <td class="py-3 px-6">{{ note.date }}</td>
                        <td class="py-3 px-6">{{ note.customer?.name || 'Unknown' }}</td>
                        <td class="py-3 px-6">{{ note.reference || '-' }}</td>
                        <td class="py-3 px-6">{{ note.owner?.name || '-' }}</td>
                        <td class="py-3 px-6">{{ note.updater?.name || '-' }}</td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center space-x-2">
                                <router-link :to="{ name: 'delivery-notes.edit', params: { id: note.id } }" class="w-5 transform hover:text-blue-500 hover:scale-110" title="Edit Delivery Note">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 13H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </router-link>
                                <router-link :to="{ name: 'delivery-notes.print', params: { id: note.id } }" class="w-5 transform hover:text-green-500 hover:scale-110" title="Print Delivery Note">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                </router-link>
                                <button @click="deleteDeliveryNote(note.id)" class="w-5 transform hover:text-red-500 hover:scale-110" title="Delete Delivery Note">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="deliveryNotes.length === 0">
                        <td colspan="7" class="py-3 px-6 text-center">No delivery notes found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const deliveryNotes = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchDeliveryNotes = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get('/api/delivery-notes');
        deliveryNotes.value = response.data.delivery_notes;
    } catch (err) {
        error.value = 'Failed to load delivery notes.';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const deleteDeliveryNote = async (id) => {
    if (!confirm('Are you sure you want to delete this delivery note?')) return;

    try {
        await axios.delete(`/api/delivery-notes/${id}`);
        deliveryNotes.value = deliveryNotes.value.filter((note) => note.id !== id);
    } catch (err) {
        alert('Failed to delete delivery note.');
        console.error(err);
    }
};

onMounted(() => {
    fetchDeliveryNotes();
});
</script>
