<template>
    <div v-if="loading" class="text-center py-10">
        <p>Loading delivery note...</p>
    </div>
    <div v-else-if="error" class="text-center py-10 text-red-600">
        <p>{{ error }}</p>
    </div>
    <div v-else class="bg-gray-100 min-h-screen p-8 print:p-0 print:bg-white">
        <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden print:shadow-none print:max-w-full print:rounded-none">
            <div class="flex justify-between items-start p-8 border-b border-gray-200 print:p-2">
                <div class="w-1/4">
                    <img v-if="settings.logo_path" :src="`/storage/${settings.logo_path}`" alt="Company Logo" class="w-full object-contain max-h-32 print:max-h-16">
                    <h1 v-else class="text-4xl font-bold text-blue-800 print:text-xl">LOGO</h1>
                </div>

                <div class="w-1/2 text-center px-4">
                    <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-wide mb-2 print:text-lg print:mb-0">{{ settings.company_name }}</h2>
                    <p class="text-gray-600 text-sm mb-1 print:text-[10px] print:leading-tight" v-if="settings.tin_number">TIN Number {{ settings.tin_number }}</p>
                    <p class="text-gray-600 text-sm mb-1 print:text-[10px] print:leading-tight" v-if="settings.p_o_box">{{ settings.p_o_box }}</p>
                    <p class="text-gray-600 text-sm mb-1 print:text-[10px] print:leading-tight" v-if="settings.address">{{ settings.address }}</p>
                    <p class="text-gray-600 text-sm mb-1 print:text-[10px] print:leading-tight" v-if="settings.phone">{{ settings.phone }}</p>
                    <a v-if="settings.email" :href="`mailto:${settings.email}`" class="text-blue-600 text-sm underline print:text-[10px] print:leading-tight">{{ settings.email }}</a>
                </div>

                <div class="w-1/4 text-right">
                    <h3 class="text-3xl font-light text-gray-400 mb-4 print:text-xl print:mb-1">DELIVERY NOTE</h3>
                    <p class="text-gray-600 font-bold mb-1 print:text-xs">DN# {{ deliveryNote.number || deliveryNote.id }}</p>
                    <p class="text-gray-500 text-sm print:text-[10px]">DATE</p>
                    <p class="text-gray-800 font-semibold print:text-xs">{{ formatDate(deliveryNote.date) }}</p>
                </div>
            </div>

            <div class="p-8 print:p-4 print:py-2">
                <p class="text-gray-500 text-sm uppercase tracking-wider mb-2 print:mb-1 print:text-xs">DELIVER TO</p>
                <h3 class="text-xl font-bold text-gray-800 mb-1 uppercase print:text-lg">{{ deliveryNote.customer.name }}</h3>
                <p class="text-gray-600 mb-1 print:text-sm print:mb-0" v-if="deliveryNote.customer.tin">TIN: {{ deliveryNote.customer.tin }}</p>
                <p class="text-gray-600 mb-1 print:text-sm print:mb-0" v-if="deliveryNote.customer.p_o_box">{{ deliveryNote.customer.p_o_box }}</p>
                <p class="text-gray-600 mb-1 print:text-sm print:mb-0" v-if="deliveryNote.customer.address">{{ deliveryNote.customer.address }}</p>
                <p class="text-blue-600 underline text-sm print:text-xs" v-if="deliveryNote.customer.email">{{ deliveryNote.customer.email }}</p>
                <p class="text-gray-600 mt-2 print:text-sm" v-if="deliveryNote.reference"><span class="font-bold">Reference:</span> {{ deliveryNote.reference }}</p>
            </div>

            <div class="p-8 print:p-4">
                <table class="delivery-note-items-table w-full border-collapse border border-black print:text-sm">
                    <thead>
                        <tr class="bg-white">
                            <th class="border border-black px-4 py-2 text-left w-12 print:px-2 print:py-1">S/N</th>
                            <th class="border border-black px-4 py-2 text-left print:px-2 print:py-1">DESCRIPTION</th>
                            <th class="border border-black px-4 py-2 text-left w-20 print:px-2 print:py-1">QTY</th>
                            <th class="border border-black px-4 py-2 text-left w-48 print:px-2 print:py-1">SIGN OF SUPPLIER</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in deliveryNote.items" :key="item.id">
                            <td class="border border-black px-4 py-2 print:px-2 print:py-1">{{ index + 1 }}</td>
                            <td class="border border-black px-4 py-2 print:px-2 print:py-1 whitespace-pre-line">{{ item.description }}</td>
                            <td class="border border-black px-4 py-2 print:px-2 print:py-1">{{ item.quantity }}</td>
                            <td class="border border-black px-4 py-2 print:px-2 print:py-1">{{ item.supplier_signature || '' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-8 mt-4 print:p-4 print:mt-0">
                <p v-if="deliveryNote.terms_and_conditions" class="text-sm font-bold mb-8 print:mb-4 print:text-xs whitespace-pre-line">{{ deliveryNote.terms_and_conditions }}</p>

                <div class="flex justify-between items-end">
                    <div class="w-1/2 text-sm text-gray-700 print:text-xs">
                        <div class="mt-12 border-t border-black pt-2 w-64">Received By</div>
                    </div>
                    <div class="w-1/2 flex justify-end">
                        <div v-if="settings.stamp_path" class="border-2 border-blue-900 p-2 rounded transform rotate-[-2deg] opacity-80 print:border-none print:p-0">
                            <img :src="`/storage/${settings.stamp_path}`" alt="Company Stamp" class="h-32 object-contain print:h-20">
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8 border-t border-gray-200 text-center print:hidden">
                <button @click="printDeliveryNote" class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-3 px-8 rounded-full shadow-lg transition duration-300 mx-auto">
                    Print Delivery Note
                </button>
                <router-link :to="{ name: 'delivery-notes.index' }" class="block mt-4 text-gray-500 hover:underline">Back to Delivery Notes</router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { nextTick, ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';

const route = useRoute();
const deliveryNote = ref(null);
const settings = ref({});
const loading = ref(true);
const error = ref(null);

const fetchData = async () => {
    loading.value = true;

    try {
        const noteResponse = await axios.get(`/api/delivery-notes/${route.params.id}`);
        deliveryNote.value = noteResponse.data.delivery_note;

        const settingsResponse = await axios.get('/api/company-settings');
        settings.value = settingsResponse.data;
    } catch (err) {
        console.error(err);
        error.value = 'Failed to load delivery note data.';
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
};

const printDeliveryNote = () => {
    window.print();
};

const hideLegacyPriceColumn = () => {
    const table = document.querySelector('.delivery-note-items-table');
    const headers = Array.from(table?.querySelectorAll('thead th') || []);
    const priceIndex = headers.findIndex((header) => header.textContent.trim().toLowerCase() === 'price per unit');

    if (priceIndex === -1) {
        return;
    }

    table.querySelectorAll('tr').forEach((row) => {
        row.children[priceIndex]?.remove();
    });
};

onMounted(async () => {
    await fetchData();
    nextTick(hideLegacyPriceColumn);
});
</script>

<style>
@media print {
    @page {
        margin: 0.5cm;
        size: auto;
    }
    body {
        background-color: white;
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    nav, .print\:hidden {
        display: none !important;
    }
}
</style>
