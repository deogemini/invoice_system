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

        <div v-else class="bg-white shadow-md rounded my-6">
            <div class="flex flex-col gap-4 p-4 border-b border-gray-200 md:flex-row md:items-center md:justify-between">
                <div class="w-full md:max-w-sm">
                    <label for="invoice-customer-search" class="sr-only">Search by customer name</label>
                    <input
                        id="invoice-customer-search"
                        v-model="customerSearch"
                        type="search"
                        placeholder="Search by customer name..."
                        class="w-full rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                </div>
                <div class="flex items-center gap-3 text-sm text-gray-600">
                    <span>Rows per page</span>
                    <select
                        v-model.number="perPage"
                        class="rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full w-full table-auto">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Invoice #</th>
                            <th class="py-3 px-6 text-left">Date</th>
                            <th class="py-3 px-6 text-left">Customer</th>
                            <th class="py-3 px-6 text-left">Reference</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            <th class="py-3 px-6 text-center">TRA Status</th>
                            <th class="py-3 px-6 text-right">Total</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="invoice in paginatedInvoices" :key="invoice.id" class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="py-3 px-6 text-left whitespace-nowrap font-bold">{{ invoice.number }}</td>
                            <td class="py-3 px-6 text-left">{{ invoice.date }}</td>
                            <td class="py-3 px-6 text-left">{{ invoice.customer ? invoice.customer.name : 'Unknown' }}</td>
                            <td class="py-3 px-6 text-left">{{ invoice.reference || '-' }}</td>
                            <td class="py-3 px-6 text-center">
                                <span :class="{
                                    'bg-green-200 text-green-600': invoice.status === 'paid',
                                    'bg-red-200 text-red-600': invoice.status === 'unpaid',
                                    'bg-yellow-200 text-yellow-600': invoice.status === 'partial'
                                }" class="py-1 px-3 rounded-full text-xs font-bold cursor-pointer" @click="toggleStatus(invoice)">
                                    {{ invoice.status ? invoice.status.toUpperCase() : 'UNPAID' }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <span :class="{'bg-blue-200 text-blue-600': invoice.tra_status === 'generated', 'bg-gray-200 text-gray-600': invoice.tra_status !== 'generated'}" class="py-1 px-3 rounded-full text-xs font-bold cursor-pointer" @click="toggleTraStatus(invoice)">
                                    {{ invoice.tra_status === 'generated' ? 'GENERATED' : 'NOT GEN' }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-right">
                                <div class="font-bold">{{ formatCurrency(invoice.total) }}</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    <div>Paid: <span class="font-medium">{{ formatCurrency(invoice.paid_amount || 0) }}</span></div>
                                    <div>Unpaid: <span class="font-medium">{{ formatCurrency(Math.max(0, (invoice.total || 0) - (invoice.paid_amount || 0))) }}</span></div>
                                </div>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center space-x-2">
                                    <router-link :to="{ name: 'invoices.print', params: { id: invoice.id } }" class="w-5 transform hover:text-green-500 hover:scale-110" title="Print Invoice">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </router-link>
                                    <button @click="recordPayment(invoice)" class="w-5 transform hover:text-yellow-500 hover:scale-110" title="Record Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M4 3a2 2 0 00-2 2v2a1 1 0 102 0V5h12v10H4v-2a1 1 0 10-2 0v2a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteInvoice(invoice.id)" class="w-5 transform hover:text-red-500 hover:scale-110" title="Delete Invoice">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredInvoices.length === 0">
                            <td colspan="8" class="py-3 px-6 text-center">No invoices found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-gray-200 px-4 py-3 text-sm text-gray-600 md:flex-row md:items-center md:justify-between">
                <div>
                    Showing {{ paginationStart }} to {{ paginationEnd }} of {{ filteredInvoices.length }} invoices
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="currentPage--"
                        :disabled="currentPage === 1"
                        class="rounded border border-gray-300 px-3 py-1 font-medium disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Previous
                    </button>
                    <span class="px-2">Page {{ currentPage }} of {{ totalPages }}</span>
                    <button
                        type="button"
                        @click="currentPage++"
                        :disabled="currentPage === totalPages"
                        class="rounded border border-gray-300 px-3 py-1 font-medium disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Next
                    </button>
                </div>
            </div>

            <div class="mt-4 flex justify-end space-x-6">
                <div class="text-sm text-gray-700">
                    <div class="text-xs text-gray-500">Total Invoices</div>
                    <div class="font-bold">{{ formatCurrency(totalSum) }}</div>
                </div>
                <div class="text-sm text-gray-700">
                    <div class="text-xs text-gray-500">Total Paid</div>
                    <div class="font-bold text-green-600">{{ formatCurrency(totalPaid) }}</div>
                </div>
                <div class="text-sm text-gray-700">
                    <div class="text-xs text-gray-500">Total Unpaid</div>
                    <div class="font-bold text-red-600">{{ formatCurrency(totalUnpaid) }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

const invoices = ref([]);
const loading = ref(true);
const error = ref(null);
const customerSearch = ref('');
const perPage = ref(10);
const currentPage = ref(1);

const filteredInvoices = computed(() => {
    const search = customerSearch.value.trim().toLowerCase();

    if (!search) {
        return invoices.value;
    }

    return invoices.value.filter((invoice) => {
        return (invoice.customer?.name || '').toLowerCase().includes(search);
    });
});

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredInvoices.value.length / perPage.value));
});

const paginatedInvoices = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;

    return filteredInvoices.value.slice(start, start + perPage.value);
});

const paginationStart = computed(() => {
    if (filteredInvoices.value.length === 0) {
        return 0;
    }

    return (currentPage.value - 1) * perPage.value + 1;
});

const paginationEnd = computed(() => {
    return Math.min(currentPage.value * perPage.value, filteredInvoices.value.length);
});

const totalSum = computed(() => {
    return filteredInvoices.value.reduce((s, inv) => s + Number(inv.total || 0), 0);
});

const totalPaid = computed(() => {
    return filteredInvoices.value.reduce((s, inv) => s + Number(inv.paid_amount || 0), 0);
});

const totalUnpaid = computed(() => Math.max(0, totalSum.value - totalPaid.value));

watch([customerSearch, perPage], () => {
    currentPage.value = 1;
});

watch(totalPages, (pages) => {
    if (currentPage.value > pages) {
        currentPage.value = pages;
    }
});

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

const toggleTraStatus = async (invoice) => {
    const newStatus = invoice.tra_status === 'generated' ? 'not_generated' : 'generated';
    try {
        await axios.put(`/api/invoices/${invoice.id}`, { tra_status: newStatus });
        invoice.tra_status = newStatus;
    } catch (err) {
        alert('Failed to update TRA status.');
        console.error(err);
    }
};

const recordPayment = async (invoice) => {
    const input = prompt(`Enter payment amount for invoice ${invoice.number} (remaining: ${formatCurrency((invoice.total || 0) - (invoice.paid_amount || 0))}):`);
    if (input === null) return; // cancelled
    const amount = parseFloat(input.replace(/,/g, ''));
    if (isNaN(amount) || amount <= 0) {
        alert('Invalid amount.');
        return;
    }

    try {
        const resp = await axios.post(`/api/invoices/${invoice.id}/payment`, { amount });
        // update local invoice with response
        if (resp.data && resp.data.invoice) {
            invoice.paid_amount = resp.data.invoice.paid_amount;
            invoice.status = resp.data.invoice.status;
        }
        alert('Payment recorded successfully.');
    } catch (err) {
        alert('Failed to record payment.');
        console.error(err);
    }
};

const formatCurrency = (value) => {
    return Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

onMounted(() => {
    fetchInvoices();
});
</script>

<style scoped>
/* small styling for totals summary */
.totals-row { background: #f9fafb; border-top: 1px solid #e5e7eb; }
</style>
