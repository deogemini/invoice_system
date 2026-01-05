<template>
    <div v-if="loading" class="text-center py-10">
        <p>Loading invoice...</p>
    </div>
    <div v-else-if="error" class="text-center py-10 text-red-600">
        <p>{{ error }}</p>
    </div>
    <div v-else class="bg-gray-100 min-h-screen p-8">
        <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden print:shadow-none print:max-w-full">
            <!-- Header -->
            <div class="flex justify-between items-start p-8 border-b border-gray-200">
                <!-- Left: Logo -->
                <div class="w-1/4">
                    <img v-if="settings.logo_path" :src="`/storage/${settings.logo_path}`" alt="Company Logo" class="w-full object-contain max-h-32">
                    <h1 v-else class="text-4xl font-bold text-blue-800">LOGO</h1>
                </div>

                <!-- Center: Company Details -->
                <div class="w-1/2 text-center px-4">
                    <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-wide mb-2">{{ settings.company_name }}</h2>
                    <p class="text-gray-600 text-sm mb-1" v-if="settings.tin_number">TIN Number {{ settings.tin_number }}</p>
                    <p class="text-gray-600 text-sm mb-1" v-if="settings.p_o_box">{{ settings.p_o_box }}</p>
                    <p class="text-gray-600 text-sm mb-1" v-if="settings.address">{{ settings.address }}</p>
                    <p class="text-gray-600 text-sm mb-1" v-if="settings.phone">{{ settings.phone }}</p>
                    <a v-if="settings.email" :href="`mailto:${settings.email}`" class="text-blue-600 text-sm underline">{{ settings.email }}</a>
                </div>

                <!-- Right: Invoice Info -->
                <div class="w-1/4 text-right">
                    <h3 class="text-3xl font-light text-gray-400 mb-4">INVOICE</h3>
                    <p class="text-gray-600 font-bold mb-1">INV# {{ invoice.number || invoice.id }}</p>
                    <p class="text-gray-500 text-sm">DATE</p>
                    <p class="text-gray-800 font-semibold">{{ formatDate(invoice.date) }}</p>
                </div>
            </div>

            <!-- Bill To Section -->
            <div class="p-8">
                <p class="text-gray-500 text-sm uppercase tracking-wider mb-2">BILL TO</p>
                <h3 class="text-xl font-bold text-gray-800 mb-1 uppercase">{{ invoice.customer.name }}</h3>
                <p class="text-gray-600 mb-1" v-if="invoice.customer.tin">TIN: {{ invoice.customer.tin }}</p>
                <p class="text-gray-600 mb-1" v-if="invoice.customer.p_o_box">{{ invoice.customer.p_o_box }}</p>
                <p class="text-gray-600 mb-1" v-if="invoice.customer.address">{{ invoice.customer.address }}</p>
                <p class="text-blue-600 underline text-sm" v-if="invoice.customer.email">{{ invoice.customer.email }}</p>
            </div>

            <!-- Items Table -->
            <div class="p-8">
                <table class="w-full border-collapse border border-black">
                    <thead>
                        <tr class="bg-white">
                            <th class="border border-black px-4 py-2 text-left w-12">S/N</th>
                            <th class="border border-black px-4 py-2 text-left">DESCRIPTION</th>
                            <th class="border border-black px-4 py-2 text-left w-32">PRICE</th>
                            <th class="border border-black px-4 py-2 text-left w-20">QTY</th>
                            <th class="border border-black px-4 py-2 text-left w-40">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in invoice.items" :key="item.id">
                            <td class="border border-black px-4 py-2">{{ index + 1 }}</td>
                            <td class="border border-black px-4 py-2">{{ item.product.description }}</td>
                            <td class="border border-black px-4 py-2">{{ formatCurrency(item.unit_price) }}</td>
                            <td class="border border-black px-4 py-2">{{ item.quantity }}</td>
                            <td class="border border-black px-4 py-2">{{ formatCurrency(item.unit_price * item.quantity) }}</td>
                        </tr>
                        <!-- Empty rows filler if needed, or just total row -->
                        <tr v-if="invoice.discount > 0">
                            <td colspan="4" class="border border-black px-4 py-2 text-right font-bold">SUB TOTAL</td>
                            <td class="border border-black px-4 py-2 font-bold">{{ formatCurrency(invoice.sub_total) }}</td>
                        </tr>
                        <tr v-if="invoice.discount > 0">
                            <td colspan="4" class="border border-black px-4 py-2 text-right font-bold">DISCOUNT</td>
                            <td class="border border-black px-4 py-2 font-bold">{{ formatCurrency(invoice.discount) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="border border-black px-4 py-2 text-right font-bold">TOTAL</td>
                            <td class="border border-black px-4 py-2 font-bold">{{ formatCurrency(invoice.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Section -->
            <div class="p-8 mt-4">
                <p class="text-sm font-bold mb-8">NB: "Payment is due within 7 days of receiving this invoice. We sincerely appreciate your business."</p>

                <div class="flex justify-between items-end">
                    <!-- Bank Details -->
                    <div class="w-1/2 text-sm text-gray-700">
                        <div v-if="bankAccounts.length > 0">
                            <div v-for="bank in bankAccounts" :key="bank.id" class="mb-4">
                                <p><span class="font-bold">BANK:</span> {{ bank.bank_name }}</p>
                                <p><span class="font-bold">BRANCH:</span> {{ bank.account_name }}</p> <!-- Assuming account name might hold branch or generic name, or just use bank name context. The user form has Bank Name and Account Name. Image has Branch. I will just list what we have. -->
                                <p><span class="font-bold">ACCOUNT NUMBER:</span> {{ bank.account_number }}</p>
                                <p><span class="font-bold">CURRENCY:</span> {{ bank.currency }}</p>
                                <p v-if="bank.swift_code"><span class="font-bold">SWIFT CODE:</span> {{ bank.swift_code }}</p>
                            </div>
                        </div>
                        <p v-else class="italic text-gray-500">No bank details available.</p>
                    </div>

                    <!-- Stamp -->
                    <div class="w-1/2 flex justify-end">
                        <div v-if="settings.stamp_path" class="border-2 border-blue-900 p-2 rounded transform rotate-[-2deg] opacity-80">
                             <img :src="`/storage/${settings.stamp_path}`" alt="Company Stamp" class="h-32 object-contain">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Button (Hidden when printing) -->
            <div class="p-8 border-t border-gray-200 text-center print:hidden">
                <button @click="printInvoice" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-3 px-8 rounded-full shadow-lg transition duration-300 flex items-center justify-center mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Invoice
                </button>
                <router-link :to="{ name: 'invoices.index' }" class="block mt-4 text-gray-500 hover:underline">Back to Invoices</router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';

const route = useRoute();
const invoice = ref(null);
const settings = ref({});
const bankAccounts = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchData = async () => {
    loading.value = true;
    try {
        // Fetch Invoice
        const invoiceResponse = await axios.get(`/api/invoices/${route.params.id}`);
        invoice.value = invoiceResponse.data.invoice;

        // Fetch Company Settings
        const settingsResponse = await axios.get('/api/company-settings');
        settings.value = settingsResponse.data;

        // Fetch Bank Accounts (Get all and display first one or all)
        const bankResponse = await axios.get('/api/bankaccounts');
        bankAccounts.value = bankResponse.data.bank_accounts;

    } catch (err) {
        console.error(err);
        error.value = 'Failed to load invoice data.';
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'decimal', minimumFractionDigits: 2 }).format(value) + '/=';
};

const printInvoice = () => {
    window.print();
};

onMounted(() => {
    fetchData();
});
</script>

<style>
@media print {
    body {
        background-color: white;
    }
    .container {
        width: 100%;
        max-width: none;
        padding: 0;
        margin: 0;
    }
    /* Hide navigation and other UI elements */
    nav, .print\:hidden {
        display: none !important;
    }
}
</style>
