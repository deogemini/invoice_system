<template>
    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Invoices</h1>
            <router-link :to="{ name: 'invoices.create' }" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                New Invoice
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-4">
            <p>Loading invoices...</p>
        </div>

        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ error }}</span>
        </div>

        <div v-else class="bg-white shadow-md rounded my-6 overflow-x-auto">
            <table class="min-w-full w-full table-auto">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Invoice #</th>
                        <th class="py-3 px-6 text-left">Date</th>
                        <th class="py-3 px-6 text-left">Customer</th>
                        <th class="py-3 px-6 text-left">Reference</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-right">Total</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <tr v-for="invoice in invoices" :key="invoice.id" class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left whitespace-nowrap font-bold">{{ invoice.number }}</td>
                        <td class="py-3 px-6 text-left">{{ invoice.date }}</td>
                        <td class="py-3 px-6 text-left">{{ invoice.customer ? invoice.customer.name : 'Unknown' }}</td>
                        <td class="py-3 px-6 text-left">{{ invoice.reference || '-' }}</td>
                        <td class="py-3 px-6 text-center">
                            <span :class="{'bg-green-200 text-green-600': invoice.status === 'paid', 'bg-red-200 text-red-600': invoice.status === 'unpaid'}" class="py-1 px-3 rounded-full text-xs font-bold cursor-pointer" @click="toggleStatus(invoice)">
                                {{ invoice.status ? invoice.status.toUpperCase() : 'UNPAID' }}
                            </span>
                        </td>
                        <td class="py-3 px-6 text-right font-bold">{{ Number(invoice.total).toFixed(2) }}</td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center space-x-2">
                                <router-link :to="{ name: 'invoices.print', params: { id: invoice.id } }" class="w-5 transform hover:text-green-500 hover:scale-110" title="Print Invoice">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                </router-link>
                                <button @click="deleteInvoice(invoice.id)" class="w-5 transform hover:text-red-500 hover:scale-110" title="Delete Invoice">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="invoices.length === 0">
                        <td colspan="7" class="py-3 px-6 text-center">No invoices found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const invoices = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchInvoices = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/api/invoices');
        invoices.value = response.data.invoices;
    } catch (err) {
        error.value = 'Failed to load invoices.';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const deleteInvoice = async (id) => {
    if (!confirm('Are you sure you want to delete this invoice?')) return;

    try {
        await axios.delete(`/api/invoices/${id}`);
        invoices.value = invoices.value.filter(i => i.id !== id);
    } catch (err) {
        alert('Failed to delete invoice.');
        console.error(err);
    }
};

const toggleStatus = async (invoice) => {
    const newStatus = invoice.status === 'paid' ? 'unpaid' : 'paid';
    try {
        await axios.put(`/api/invoices/${invoice.id}`, { status: newStatus });
        invoice.status = newStatus;
    } catch (err) {
        alert('Failed to update status.');
        console.error(err);
    }
};

onMounted(() => {
    fetchInvoices();
});
</script>
