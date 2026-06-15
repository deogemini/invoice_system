<template>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">{{ isEditMode ? 'Edit Invoice' : 'Create New Invoice' }}</h1>

        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ error }}</span>
            <ul v-if="Object.keys(errors).length > 0" class="list-disc list-inside mt-2">
                <li v-for="(fieldErrors, field) in errors" :key="field">
                    {{ fieldErrors[0] }}
                </li>
            </ul>
        </div>

        <form @submit.prevent="submitForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <!-- Header Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="customer">
                        Customer
                    </label>
                    <select v-model="form.customer_id" id="customer" required
                        class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="" disabled>Select Customer</option>
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                            {{ customer.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="date">
                        Date
                    </label>
                    <input v-model="form.date" type="date" id="date" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="due_date">
                        Due Date
                    </label>
                    <input v-model="form.due_date" type="date" id="due_date"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="reference">
                        Reference (Optional)
                    </label>
                    <input v-model="form.reference" type="text" id="reference" placeholder="PO Number etc."
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Invoice Number will be auto-generated.</p>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="bank_account">
                        Bank Account to Use
                    </label>
                    <select v-model="form.bank_account_id" id="bank_account"
                        class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">No bank account selected</option>
                        <option v-for="bank in bankAccounts" :key="bank.id" :value="bank.id">
                            {{ bank.bank_name }} - {{ bank.account_name }} ({{ bank.currency }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Items Section -->
            <div class="mb-6">
                <h3 class="text-lg font-bold mb-2">Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b text-left">Product</th>
                                <th class="py-2 px-4 border-b text-left">Details</th>
                                <th class="py-2 px-4 border-b text-right w-24">Price</th>
                                <th class="py-2 px-4 border-b text-center w-24">Qty</th>
                                <th class="py-2 px-4 border-b text-right w-32">Total</th>
                                <th class="py-2 px-4 border-b text-center w-16"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in form.items" :key="index">
                                <td class="py-2 px-4 border-b">
                                    <select v-model="item.product_id" @change="onProductChange(item)" required
                                        class="shadow border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        <option value="" disabled>Select Product</option>
                                        <option v-for="product in products" :key="product.id" :value="product.id">
                                            {{ product.item_code }} - {{ product.description }}
                                        </option>
                                    </select>
                                </td>
                                <td class="py-2 px-4 border-b">
                                    <textarea v-model="item.description" rows="2"
                                        class="shadow border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm"
                                        placeholder="Add item details or description"></textarea>
                                </td>
                                <td class="py-2 px-4 border-b text-right">
                                    <input v-model.number="item.unit_price" type="number" step="0.01" min="0" required
                                        class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-right">
                                </td>
                                <td class="py-2 px-4 border-b text-center">
                                    <input v-model.number="item.quantity" type="number" min="1" required
                                        class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-center">
                                </td>
                                <td class="py-2 px-4 border-b text-right font-bold">
                                    {{ (item.unit_price * item.quantity).toFixed(2) }}
                                </td>
                                <td class="py-2 px-4 border-b text-center">
                                    <button @click.prevent="removeItem(index)" class="text-red-500 hover:text-red-700 font-bold">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button @click.prevent="addItem" class="mt-2 bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                    + Add Item
                </button>
            </div>

            <!-- Totals Section -->
            <div class="flex justify-end mb-6">
                <div class="w-full md:w-1/3">
                    <div class="flex justify-between mb-2">
                        <span class="font-bold">Sub Total:</span>
                        <span>{{ subTotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between mb-2 items-center">
                        <span class="font-bold">Discount:</span>
                        <input v-model.number="form.discount" type="number" step="0.01" min="0"
                            class="shadow appearance-none border rounded w-24 py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-right">
                    </div>
                    <label class="flex items-center justify-between mb-2 gap-3">
                        <span class="font-bold">Generate with VAT (18%):</span>
                        <input v-model="form.include_vat" type="checkbox" class="h-4 w-4 rounded">
                    </label>
                    <div v-if="form.include_vat" class="flex justify-between mb-2">
                        <span class="font-bold">Price Before VAT:</span>
                        <span>{{ taxableAmount.toFixed(2) }}</span>
                    </div>
                    <div v-if="form.include_vat" class="flex justify-between mb-2">
                        <span class="font-bold">VAT Amount:</span>
                        <span>{{ vatAmount.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2 mt-2">
                        <span class="font-bold text-xl">Grand Total:</span>
                        <span class="font-bold text-xl">{{ grandTotal.toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Terms -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="terms">
                    Terms and Conditions
                </label>
                <textarea v-model="form.terms_and_conditions" id="terms" rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>

            <div class="flex items-center justify-between">
                <button :disabled="loading"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
                    type="submit">
                    {{ loading ? 'Saving...' : (isEditMode ? 'Update Invoice' : 'Save Invoice') }}
                </button>
                <router-link :to="{ name: 'invoices.index' }"
                    class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Cancel
                </router-link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const router = useRouter();
const route = useRoute();

const customers = ref([]);
const products = ref([]);
const bankAccounts = ref([]);
const loading = ref(false);
const error = ref(null);
const errors = ref({});
const isEditMode = computed(() => !!route.params.id);

const form = ref({
    customer_id: '',
    bank_account_id: '',
    date: new Date().toISOString().slice(0, 10),
    due_date: '',
    reference: '',
    discount: 0,
    include_vat: false,
    terms_and_conditions: '',
    items: [
        { product_id: '', description: '', unit_price: 0, quantity: 1 }
    ]
});

const fetchCustomers = async () => {
    try {
        const response = await axios.get('/api/customers');
        customers.value = response.data.customers;
    } catch (err) {
        console.error('Failed to load customers', err);
    }
};

const fetchProducts = async () => {
    try {
        const response = await axios.get('/api/products');
        products.value = response.data.products;
    } catch (err) {
        console.error('Failed to load products', err);
    }
};

const fetchBankAccounts = async () => {
    try {
        const response = await axios.get('/api/bankaccounts');
        bankAccounts.value = response.data.bank_accounts;
    } catch (err) {
        console.error('Failed to load bank accounts', err);
    }
};

const fetchInvoice = async () => {
    if (!isEditMode.value) {
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(`/api/invoices/${route.params.id}`);
        const invoice = response.data.invoice;

        form.value = {
            customer_id: invoice.customer_id,
            bank_account_id: invoice.bank_account_id || '',
            date: invoice.date,
            due_date: invoice.due_date || '',
            reference: invoice.reference || '',
            discount: Number(invoice.discount || 0),
            include_vat: Boolean(invoice.include_vat),
            terms_and_conditions: invoice.terms_and_conditions || '',
            items: invoice.items && invoice.items.length
                ? invoice.items.map((item) => ({
                    product_id: item.product_id,
                    description: item.description || '',
                    unit_price: Number(item.unit_price || 0),
                    quantity: Number(item.quantity || 1),
                }))
                : [{ product_id: '', description: '', unit_price: 0, quantity: 1 }],
        };
    } catch (err) {
        error.value = 'Failed to load invoice.';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const onProductChange = (item) => {
    const product = products.value.find(p => p.id === item.product_id);
    if (product) {
        item.description = `${product.item_code} - ${product.description}`;
        item.unit_price = product.unit_price;
    }
};

const addItem = () => {
    form.value.items.push({ product_id: '', description: '', unit_price: 0, quantity: 1 });
};

const removeItem = (index) => {
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1);
    }
};

const subTotal = computed(() => {
    return form.value.items.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
});

const grandTotal = computed(() => {
    return taxableAmount.value + vatAmount.value;
});

const taxableAmount = computed(() => {
    return Math.max(0, subTotal.value - Number(form.value.discount || 0));
});

const vatAmount = computed(() => {
    return form.value.include_vat ? taxableAmount.value * 0.18 : 0;
});

const submitForm = async () => {
    loading.value = true;
    error.value = null;
    errors.value = {};

    try {
        if (isEditMode.value) {
            await axios.put(`/api/invoices/${route.params.id}`, form.value);
        } else {
            await axios.post('/api/invoices', form.value);
        }

        router.push({ name: 'invoices.index' });
    } catch (err) {
        if (err.response && err.response.status === 422) {
            errors.value = err.response.data.errors;
            error.value = err.response.data.message || 'Please fix the errors above.';
        } else if (err.response && err.response.data && err.response.data.message) {
            error.value = err.response.data.message;
        } else {
            error.value = 'An error occurred while saving the invoice.';
        }
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchCustomers();
    fetchProducts();
    fetchBankAccounts();
    fetchInvoice();
});
</script>
