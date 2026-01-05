<template>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Create New Invoice</h1>

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
            </div>

            <!-- Items Section -->
            <div class="mb-6">
                <h3 class="text-lg font-bold mb-2">Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b text-left">Product</th>
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
                    {{ loading ? 'Saving...' : 'Save Invoice' }}
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
import { useRouter } from 'vue-router';

const router = useRouter();

const customers = ref([]);
const products = ref([]);
const loading = ref(false);
const error = ref(null);
const errors = ref({});

const form = ref({
    customer_id: '',
    date: new Date().toISOString().slice(0, 10),
    due_date: '',
    reference: '',
    discount: 0,
    terms_and_conditions: '',
    items: [
        { product_id: '', unit_price: 0, quantity: 1 }
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

const onProductChange = (item) => {
    const product = products.value.find(p => p.id === item.product_id);
    if (product) {
        item.unit_price = product.unit_price;
    }
};

const addItem = () => {
    form.value.items.push({ product_id: '', unit_price: 0, quantity: 1 });
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
    return subTotal.value - form.value.discount;
});

const submitForm = async () => {
    loading.value = true;
    error.value = null;
    errors.value = {};

    try {
        await axios.post('/api/invoices', form.value);
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
});
</script>
