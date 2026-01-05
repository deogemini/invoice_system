<template>
    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Customers</h1>
            <router-link :to="{ name: 'customers.create' }" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add Customer
            </router-link>
        </div>

        <div v-if="loading" class="text-center py-4">
            <p>Loading customers...</p>
        </div>

        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ error }}</span>
        </div>

        <div v-else class="bg-white shadow-md rounded my-6 overflow-x-auto">
            <table class="min-w-full w-full table-auto">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Email</th>
                        <th class="py-3 px-6 text-left">Tax Identification Number (TIN)</th>
                        <th class="py-3 px-6 text-left">Phone</th>
                        <th class="py-3 px-6 text-left">Address</th>
                        <th class="py-3 px-6 text-left">P.O. Box</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm font-light">
                    <tr v-for="customer in customers" :key="customer.id" class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-3 px-6 text-left whitespace-nowrap">{{ customer.id }}</td>
                        <td class="py-3 px-6 text-left">{{ customer.name }}</td>
                        <td class="py-3 px-6 text-left">{{ customer.email }}</td>
                        <td class="py-3 px-6 text-left">{{ customer.tin || '-' }}</td>
                        <td class="py-3 px-6 text-left">{{ customer.phone || '-' }}</td>
                        <td class="py-3 px-6 text-left">{{ customer.address || '-' }}</td>
                        <td class="py-3 px-6 text-left">{{ customer.p_o_box || '-' }}</td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <router-link :to="{ name: 'customers.edit', params: { id: customer.id } }" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </router-link>
                                <button @click="deleteCustomer(customer.id)" class="w-4 transform hover:text-red-500 hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="customers.length === 0">
                        <td colspan="8" class="py-3 px-6 text-center">No customers found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const customers = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchCustomers = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/api/customers');
        customers.value = response.data.customers;
    } catch (err) {
        error.value = 'Failed to load customers.';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const deleteCustomer = async (id) => {
    if (!confirm('Are you sure you want to delete this customer?')) return;

    try {
        await axios.delete(`/api/customers/${id}`);
        customers.value = customers.value.filter(c => c.id !== id);
    } catch (err) {
        alert('Failed to delete customer.');
        console.error(err);
    }
};

onMounted(() => {
    fetchCustomers();
});
</script>
