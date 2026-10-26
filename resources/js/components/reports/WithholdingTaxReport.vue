<template>
    <div class="container mx-auto p-4">
        <div class="flex flex-col gap-3 mb-6 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold">Tax Reports</h1>
                <p class="text-sm text-gray-600">Withholding tax deducted and VAT charged on invoices.</p>
            </div>
            <button type="button" @click="printReport" class="rounded bg-gray-700 px-4 py-2 font-bold text-white hover:bg-gray-800 print:hidden">
                Print Report
            </button>
        </div>

        <form @submit.prevent="fetchReport" class="mb-6 flex flex-col gap-4 rounded bg-white p-4 shadow md:flex-row md:items-end print:hidden">
            <div>
                <label for="report-from" class="mb-1 block text-sm font-medium text-gray-700">From date</label>
                <input id="report-from" v-model="filters.from" type="date" class="rounded border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label for="report-to" class="mb-1 block text-sm font-medium text-gray-700">To date</label>
                <input id="report-to" v-model="filters.to" type="date" class="rounded border border-gray-300 px-3 py-2">
            </div>
            <button type="submit" :disabled="loading" class="rounded bg-blue-600 px-4 py-2 font-bold text-white hover:bg-blue-700 disabled:opacity-50">
                {{ loading ? 'Loading...' : 'Apply Dates' }}
            </button>
        </form>

        <div v-if="error" class="mb-6 rounded border border-red-300 bg-red-100 px-4 py-3 text-red-700" role="alert">
            {{ error }}
        </div>

        <div v-if="loading && !report" class="py-10 text-center text-gray-500">Loading withholding tax report...</div>

        <template v-else-if="report">
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded bg-white p-5 shadow">
                    <p class="text-sm text-gray-500">Total Withholding Tax</p>
                    <p class="mt-1 text-2xl font-bold text-blue-800">{{ formatCurrency(report.summary.total_withholding) }}</p>
                </div>
                <div class="rounded bg-white p-5 shadow">
                    <p class="text-sm text-gray-500">Invoices With Withholding</p>
                    <p class="mt-1 text-2xl font-bold">{{ report.summary.invoice_count }}</p>
                </div>
                <div class="rounded bg-white p-5 shadow">
                    <p class="text-sm text-gray-500">Total VAT Charged</p>
                    <p class="mt-1 text-2xl font-bold text-green-700">{{ formatCurrency(report.vat_summary.total_vat) }}</p>
                </div>
                <div class="rounded bg-white p-5 shadow">
                    <p class="text-sm text-gray-500">Invoices With VAT</p>
                    <p class="mt-1 text-2xl font-bold">{{ report.vat_summary.invoice_count }}</p>
                </div>
            </div>

            <div class="mb-8 overflow-x-auto rounded bg-white shadow">
                <div class="flex flex-col gap-1 border-b border-gray-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                    <h2 class="font-bold">Withholding Tax by Invoice</h2>
                    <p class="text-sm text-gray-600">{{ periodLabel }}</p>
                </div>
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100 text-left text-xs uppercase tracking-wide text-gray-600">
                            <th class="px-4 py-3">Invoice #</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3 text-right">Invoice Subtotal</th>
                            <th class="px-4 py-3 text-right">Withholding Tax Cut</th>
                            <th class="px-4 py-3 text-right">Invoice Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                        <tr v-for="invoice in report.invoices" :key="invoice.id">
                            <td class="whitespace-nowrap px-4 py-3 font-medium">{{ invoice.number || `INV-${invoice.id}` }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ invoice.date }}</td>
                            <td class="px-4 py-3">{{ invoice.customer?.name || 'Unknown' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ formatCurrency(invoice.sub_total) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-bold text-red-700">{{ formatCurrency(invoice.discount) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ formatCurrency(invoice.total) }}</td>
                        </tr>
                        <tr v-if="report.invoices.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No invoices with withholding tax were found for this date range.</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="report.invoices.length" class="border-t-2 border-gray-300 bg-gray-50">
                        <tr class="font-bold">
                            <td colspan="4" class="px-4 py-3 text-right">Total Withholding Tax Cut</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-red-700">{{ formatCurrency(report.summary.total_withholding) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="overflow-x-auto rounded bg-white shadow">
                <div class="flex flex-col gap-1 border-b border-gray-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
                    <h2 class="font-bold">VAT (18%) by Invoice</h2>
                    <p class="text-sm text-gray-600">{{ periodLabel }}</p>
                </div>
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100 text-left text-xs uppercase tracking-wide text-gray-600">
                            <th class="px-4 py-3">Invoice #</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3 text-right">VAT Exclusive Amount</th>
                            <th class="px-4 py-3 text-right">VAT Rate</th>
                            <th class="px-4 py-3 text-right">VAT Charged</th>
                            <th class="px-4 py-3 text-right">Invoice Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                        <tr v-for="invoice in report.vat_invoices" :key="invoice.id">
                            <td class="whitespace-nowrap px-4 py-3 font-medium">{{ invoice.number || `INV-${invoice.id}` }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ invoice.date }}</td>
                            <td class="px-4 py-3">{{ invoice.customer?.name || 'Unknown' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ formatCurrency(Number(invoice.sub_total || 0) - Number(invoice.discount || 0)) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ Number(invoice.vat_rate || 18).toFixed(0) }}%</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-bold text-green-700">{{ formatCurrency(invoice.vat_amount) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">{{ formatCurrency(invoice.total) }}</td>
                        </tr>
                        <tr v-if="report.vat_invoices.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No VAT invoices were found for this date range.</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="report.vat_invoices.length" class="border-t-2 border-gray-300 bg-gray-50">
                        <tr class="font-bold">
                            <td colspan="5" class="px-4 py-3 text-right">Total VAT Charged</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-green-700">{{ formatCurrency(report.vat_summary.total_vat) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import axios from 'axios';

const filters = reactive({ from: '', to: '' });
const appliedFilters = ref({ from: '', to: '' });
const report = ref(null);
const loading = ref(false);
const error = ref(null);

const periodLabel = computed(() => {
    if (appliedFilters.value.from && appliedFilters.value.to) return `${appliedFilters.value.from} to ${appliedFilters.value.to}`;
    if (appliedFilters.value.from) return `From ${appliedFilters.value.from}`;
    if (appliedFilters.value.to) return `Through ${appliedFilters.value.to}`;
    return 'All dates';
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value || 0)) + '/=';
};

const fetchReport = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get('/api/reports/withholding-tax', {
            params: {
                ...(filters.from ? { from: filters.from } : {}),
                ...(filters.to ? { to: filters.to } : {}),
            },
        });
        report.value = response.data;
        appliedFilters.value = { ...filters };
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load tax reports.';
    } finally {
        loading.value = false;
    }
};

const printReport = () => window.print();

onMounted(fetchReport);
</script>
