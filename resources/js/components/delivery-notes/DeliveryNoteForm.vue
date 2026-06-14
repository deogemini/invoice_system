<template>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">{{ isEditMode ? 'Edit Delivery Note' : 'Create Delivery Note' }}</h1>

        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ error }}</span>
            <ul v-if="Object.keys(errors).length > 0" class="list-disc list-inside mt-2">
                <li v-for="(fieldErrors, field) in errors" :key="field">{{ fieldErrors[0] }}</li>
            </ul>
        </div>

        <form @submit.prevent="submitForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="customer">Customer</label>
                    <select v-model="form.customer_id" id="customer" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="" disabled>Select Customer</option>
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="date">Date</label>
                    <input v-model="form.date" type="date" id="date" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="reference">Reference (Optional)</label>
                    <input v-model="form.reference" type="text" id="reference" placeholder="Order number, vehicle number, etc." class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-xs text-gray-500 mt-1">Delivery note number will be auto-generated.</p>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-bold mb-2">Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b text-left">Description</th>
                                <th class="py-2 px-4 border-b text-center w-24">Qty</th>
                                <th class="py-2 px-4 border-b text-left w-56">Sign of Supplier</th>
                                <th class="py-2 px-4 border-b text-center w-16"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in form.items" :key="index">
                                <td class="py-2 px-4 border-b">
                                    <textarea v-model="item.description" rows="2" required placeholder="Write item description" class="shadow border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-sm"></textarea>
                                </td>
                                <td class="py-2 px-4 border-b text-center">
                                    <input v-model.number="item.quantity" type="number" min="1" required class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-center">
                                </td>
                                <td class="py-2 px-4 border-b">
                                    <input v-model="item.supplier_signature" type="text" placeholder="Name or signature" class="shadow border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </td>
                                <td class="py-2 px-4 border-b text-center">
                                    <button @click.prevent="removeItem(index)" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button @click.prevent="addItem" class="mt-2 bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                    + Add Item
                </button>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="terms">Terms and Conditions</label>
                <textarea v-model="form.terms_and_conditions" id="terms" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>

            <div class="flex items-center justify-between">
                <button :disabled="loading" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50" type="submit">
                    {{ loading ? 'Saving...' : (isEditMode ? 'Update Delivery Note' : 'Save Delivery Note') }}
                </button>
                <router-link :to="{ name: 'delivery-notes.index' }" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
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
const loading = ref(false);
const error = ref(null);
const errors = ref({});
const isEditMode = computed(() => !!route.params.id);

const form = ref({
    customer_id: '',
    date: new Date().toISOString().slice(0, 10),
    reference: '',
    terms_and_conditions: '',
    items: [
        { description: '', quantity: 1, supplier_signature: '' },
    ],
});

const fetchCustomers = async () => {
    try {
        const response = await axios.get('/api/customers');
        customers.value = response.data.customers;
    } catch (err) {
        console.error('Failed to load customers', err);
    }
};

const fetchDeliveryNote = async () => {
    if (!isEditMode.value) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(`/api/delivery-notes/${route.params.id}`);
        const note = response.data.delivery_note;

        form.value = {
            customer_id: note.customer_id,
            date: note.date,
            reference: note.reference || '',
            terms_and_conditions: note.terms_and_conditions || '',
            items: note.items && note.items.length
                ? note.items.map((item) => ({
                    description: item.description || '',
                    quantity: Number(item.quantity || 1),
                    supplier_signature: item.supplier_signature || '',
                }))
                : [{ description: '', quantity: 1, supplier_signature: '' }],
        };
    } catch (err) {
        error.value = 'Failed to load delivery note.';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const addItem = () => {
    form.value.items.push({ description: '', quantity: 1, supplier_signature: '' });
};

const removeItem = (index) => {
    if (form.value.items.length > 1) {
        form.value.items.splice(index, 1);
    }
};

const submitForm = async () => {
    loading.value = true;
    error.value = null;
    errors.value = {};

    try {
        if (isEditMode.value) {
            await axios.put(`/api/delivery-notes/${route.params.id}`, form.value);
        } else {
            await axios.post('/api/delivery-notes', form.value);
        }

        router.push({ name: 'delivery-notes.index' });
    } catch (err) {
        if (err.response && err.response.status === 422) {
            errors.value = err.response.data.errors;
            error.value = err.response.data.message || 'Please fix the errors above.';
        } else {
            error.value = err.response?.data?.message || 'An error occurred while saving the delivery note.';
        }
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchCustomers();
    fetchDeliveryNote();
});
</script>
