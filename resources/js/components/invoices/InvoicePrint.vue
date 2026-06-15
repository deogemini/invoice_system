<template>
    <div v-if="loading" class="text-center py-10">
        <p>Loading invoice...</p>
    </div>
    <div v-else-if="error" class="text-center py-10 text-red-600">
        <p>{{ error }}</p>
    </div>
    <div v-else class="bg-gray-100 min-h-screen p-8 print:p-0 print:bg-white">
        <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden print:shadow-none print:max-w-full print:rounded-none">
            <!-- Header -->
            <div class="flex justify-between items-start p-8 border-b border-gray-200 print:p-2">
                <!-- Left: Logo -->
                <div class="w-1/4">
                    <img v-if="settings.logo_path" :src="`/storage/${settings.logo_path}`" alt="Company Logo" class="w-full object-contain max-h-32 print:max-h-16">
                    <h1 v-else class="text-4xl font-bold text-blue-800 print:text-xl">LOGO</h1>
                </div>

                <!-- Center: Company Details -->
                <div class="w-1/2 text-center px-4">
                    <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-wide mb-2 print:text-lg print:mb-0">{{ settings.company_name }}</h2>
                    <p class="text-gray-600 text-sm mb-1 print:text-[10px] print:leading-tight" v-if="settings.tin_number">TIN Number {{ settings.tin_number }}</p>
                    <p class="text-gray-600 text-sm mb-1 print:text-[10px] print:leading-tight" v-if="settings.p_o_box">{{ settings.p_o_box }}</p>
                    <p class="text-gray-600 text-sm mb-1 print:text-[10px] print:leading-tight" v-if="settings.address">{{ settings.address }}</p>
                    <p class="text-gray-600 text-sm mb-1 print:text-[10px] print:leading-tight" v-if="settings.phone">{{ settings.phone }}</p>
                    <a v-if="settings.email" :href="`mailto:${settings.email}`" class="text-blue-600 text-sm underline print:text-[10px] print:leading-tight">{{ settings.email }}</a>
                </div>

                <!-- Right: Invoice Info -->
                <div class="w-1/4 text-right">
                    <h3 class="text-3xl font-light text-gray-400 mb-4 print:text-xl print:mb-1">INVOICE</h3>
                    <p class="text-gray-600 font-bold mb-1 print:text-xs">INV# {{ invoice.number || invoice.id }}</p>
                    <p class="text-gray-500 text-sm print:text-[10px]">DATE</p>
                    <p class="text-gray-800 font-semibold print:text-xs">{{ formatDate(invoice.date) }}</p>
                </div>
            </div>

            <!-- Bill To Section -->
            <div class="p-8 print:p-4 print:py-2">
                <p class="text-gray-500 text-sm uppercase tracking-wider mb-2 print:mb-1 print:text-xs">BILL TO</p>
                <h3 class="text-xl font-bold text-gray-800 mb-1 uppercase print:text-lg">{{ invoice.customer.name }}</h3>
                <p class="text-gray-600 mb-1 print:text-sm print:mb-0" v-if="invoice.customer.tin">TIN: {{ invoice.customer.tin }}</p>
                <p class="text-gray-600 mb-1 print:text-sm print:mb-0" v-if="invoice.customer.p_o_box">{{ invoice.customer.p_o_box }}</p>
                <p class="text-gray-600 mb-1 print:text-sm print:mb-0" v-if="invoice.customer.address">{{ invoice.customer.address }}</p>
                <p class="text-blue-600 underline text-sm print:text-xs" v-if="invoice.customer.email">{{ invoice.customer.email }}</p>
            </div>

            <!-- Items Table -->
            <div class="p-8 print:p-4">
                <table class="w-full border-collapse border border-black print:text-sm">
                    <thead>
                        <tr class="bg-white">
                            <th class="border border-black px-4 py-2 text-left w-12 print:px-2 print:py-1">S/N</th>
                            <th class="border border-black px-4 py-2 text-left print:px-2 print:py-1">DESCRIPTION</th>
                            <th class="border border-black px-4 py-2 text-left w-32 print:px-2 print:py-1">PRICE</th>
                            <th class="border border-black px-4 py-2 text-left w-20 print:px-2 print:py-1">QTY</th>
                            <th class="border border-black px-4 py-2 text-left w-40 print:px-2 print:py-1">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in invoice.items" :key="item.id">
                            <td class="border border-black px-4 py-2 print:px-2 print:py-1">{{ index + 1 }}</td>
                            <td class="border border-black px-4 py-2 print:px-2 print:py-1">{{ item.description || item.product.description }}</td>
                            <td class="border border-black px-4 py-2 print:px-2 print:py-1">{{ formatCurrency(item.unit_price) }}</td>
                            <td class="border border-black px-4 py-2 print:px-2 print:py-1">{{ item.quantity }}</td>
                            <td class="border border-black px-4 py-2 print:px-2 print:py-1">{{ formatCurrency(item.unit_price * item.quantity) }}</td>
                        </tr>
                        <!-- Empty rows filler if needed, or just total row -->
                        <tr v-if="invoice.discount > 0">
                            <td colspan="4" class="border border-black px-4 py-2 text-right font-bold print:px-2 print:py-1">SUB TOTAL</td>
                            <td class="border border-black px-4 py-2 font-bold print:px-2 print:py-1">{{ formatCurrency(invoice.sub_total) }}</td>
                        </tr>
                        <tr v-if="invoice.discount > 0">
                            <td colspan="4" class="border border-black px-4 py-2 text-right font-bold print:px-2 print:py-1">DISCOUNT</td>
                            <td class="border border-black px-4 py-2 font-bold print:px-2 print:py-1">{{ formatCurrency(invoice.discount) }}</td>
                        </tr>
                        <tr v-if="invoice.include_vat">
                            <td colspan="4" class="border border-black px-4 py-2 text-right font-bold print:px-2 print:py-1">PRICE BEFORE VAT</td>
                            <td class="border border-black px-4 py-2 font-bold print:px-2 print:py-1">{{ formatCurrency(priceBeforeVat) }}</td>
                        </tr>
                        <tr v-if="invoice.include_vat">
                            <td colspan="4" class="border border-black px-4 py-2 text-right font-bold print:px-2 print:py-1">VAT ({{ Number(invoice.vat_rate || 18).toFixed(0) }}%)</td>
                            <td class="border border-black px-4 py-2 font-bold print:px-2 print:py-1">{{ formatCurrency(invoice.vat_amount) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="border border-black px-4 py-2 text-right font-bold print:px-2 print:py-1">TOTAL</td>
                            <td class="border border-black px-4 py-2 font-bold print:px-2 print:py-1">{{ formatCurrency(invoice.total) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-4 border border-black px-4 py-3 text-sm print:text-xs">
                    <span class="font-bold">Amount in words:</span>
                    <span class="uppercase">{{ amountInWords }}</span>
                </div>
            </div>

            <!-- Footer Section -->
            <div class="p-8 mt-4 print:p-4 print:mt-0">
                <p class="text-sm font-bold mb-8 print:mb-4 print:text-xs">NB: "Payment is due within 7 days of receiving this invoice. We sincerely appreciate your business."</p>

                <div class="flex justify-between items-end">
                    <!-- Bank Details -->
                    <div class="w-1/2 text-sm text-gray-700 print:text-xs">
                        <div v-if="selectedBankAccount" class="mb-4 print:mb-2">
                            <p><span class="font-bold">BANK:</span> {{ selectedBankAccount.bank_name }}</p>
                            <p><span class="font-bold">ACCOUNT NAME:</span> {{ selectedBankAccount.account_name }}</p>
                            <p><span class="font-bold">ACCOUNT NUMBER:</span> {{ selectedBankAccount.account_number }}</p>
                            <p><span class="font-bold">CURRENCY:</span> {{ selectedBankAccount.currency }}</p>
                            <p v-if="selectedBankAccount.swift_code"><span class="font-bold">SWIFT CODE:</span> {{ selectedBankAccount.swift_code }}</p>
                        </div>
                        <p v-else class="italic text-gray-500">No bank account selected.</p>
                    </div>

                    <!-- Stamp -->
                    <div class="w-1/2 flex justify-end">
                        <div v-if="settings.stamp_path" class="border-2 border-blue-900 p-2 rounded transform rotate-[-2deg] opacity-80 print:border-none print:p-0">
                             <img :src="`/storage/${settings.stamp_path}`" alt="Company Stamp" class="h-32 object-contain print:h-20">
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
import { computed, ref, onMounted } from 'vue';
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

        bankAccounts.value = invoice.value.bank_account ? [invoice.value.bank_account] : [];

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

const selectedBankAccount = computed(() => invoice.value?.bank_account || bankAccounts.value[0] || null);

const priceBeforeVat = computed(() => {
    return Math.max(0, Number(invoice.value?.sub_total || 0) - Number(invoice.value?.discount || 0));
});

const amountInWords = computed(() => {
    return `${numberToWords(Number(invoice.value?.total || 0))} only`;
});

const ones = [
    '',
    'one',
    'two',
    'three',
    'four',
    'five',
    'six',
    'seven',
    'eight',
    'nine',
    'ten',
    'eleven',
    'twelve',
    'thirteen',
    'fourteen',
    'fifteen',
    'sixteen',
    'seventeen',
    'eighteen',
    'nineteen',
];

const tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

const convertHundreds = (value) => {
    const words = [];
    const hundreds = Math.floor(value / 100);
    const remainder = value % 100;

    if (hundreds) {
        words.push(`${ones[hundreds]} hundred`);
    }

    if (remainder >= 20) {
        const ten = Math.floor(remainder / 10);
        const one = remainder % 10;
        words.push(one ? `${tens[ten]} ${ones[one]}` : tens[ten]);
    } else if (remainder > 0) {
        words.push(ones[remainder]);
    }

    return words.join(' ');
};

const integerToWords = (value) => {
    if (value === 0) {
        return 'zero';
    }

    const scales = [
        { value: 1000000000000, label: 'trillion' },
        { value: 1000000000, label: 'billion' },
        { value: 1000000, label: 'million' },
        { value: 1000, label: 'thousand' },
        { value: 1, label: '' },
    ];
    const words = [];
    let remaining = value;

    scales.forEach((scale) => {
        const chunk = Math.floor(remaining / scale.value);

        if (!chunk) {
            return;
        }

        words.push(`${convertHundreds(chunk)} ${scale.label}`.trim());
        remaining %= scale.value;
    });

    return words.join(' ');
};

const numberToWords = (value) => {
    const safeValue = Math.max(0, Number(value || 0));
    const integerPart = Math.floor(safeValue);
    const cents = Math.round((safeValue - integerPart) * 100);
    const words = [integerToWords(integerPart)];

    if (cents > 0) {
        words.push(`and ${integerToWords(cents)} cents`);
    }

    return words.join(' ');
};

const sanitizeFilenamePart = (value) => {
    return String(value || '')
        .replace(/[<>:"/\\|?*]+/g, '')
        .replace(/\s+/g, ' ')
        .trim();
};

const invoiceTitle = () => {
    const number = sanitizeFilenamePart(invoice.value?.number || invoice.value?.id);
    const customer = sanitizeFilenamePart(invoice.value?.customer?.name);

    return ['Invoice', number, customer].filter(Boolean).join(' - ');
};

const updateDocumentTitle = () => {
    const title = invoiceTitle();

    if (title) {
        document.title = title;
    }
};

const printInvoice = () => {
    updateDocumentTitle();
    window.print();
};

onMounted(async () => {
    await fetchData();
    updateDocumentTitle();
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
