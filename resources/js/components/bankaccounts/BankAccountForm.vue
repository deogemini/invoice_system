<template>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">{{ isEditing ? 'Edit Bank Account' : 'Add New Bank Account' }}</h1>

        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ error }}</span>
            <ul v-if="Object.keys(errors).length > 0" class="list-disc list-inside mt-2">
                <li v-for="(fieldErrors, field) in errors" :key="field">
                    {{ fieldErrors[0] }}
                </li>
            </ul>
        </div>

        <form @submit.prevent="submitForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 max-w-lg mx-auto">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="bank_name">
                    Bank Name
                </label>
                <input v-model="form.bank_name" type="text" id="bank_name" placeholder="e.g. Chase Bank" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="account_name">
                    Account Name
                </label>
                <input v-model="form.account_name" type="text" id="account_name" placeholder="e.g. John Doe" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="account_number">
                    Account Number
                </label>
                <input v-model="form.account_number" type="text" id="account_number" placeholder="123456789" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="swift_code">
                    SWIFT/BIC Code (Optional)
                </label>
                <input v-model="form.swift_code" type="text" id="swift_code"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="currency">
                    Currency
                </label>
                <input v-model="form.currency" type="text" id="currency" placeholder="USD" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="flex items-center justify-between">
                <button :disabled="loading"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
                    type="submit">
                    {{ loading ? 'Saving...' : (isEditing ? 'Update Account' : 'Add Account') }}
                </button>
                <router-link :to="{ name: 'bankaccounts.index' }"
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

const route = useRoute();
const router = useRouter();

const props = defineProps({
    id: {
        type: String,
        default: null
    }
});

const form = ref({
    bank_name: '',
    account_name: '',
    account_number: '',
    swift_code: '',
    currency: 'USD'
});

const loading = ref(false);
const error = ref(null);
const errors = ref({});

const isEditing = computed(() => !!props.id);

const fetchAccount = async () => {
    if (!isEditing.value) return;

    loading.value = true;
    try {
        const response = await axios.get(`/api/bankaccounts/${props.id}`);
        form.value = response.data.bank_account;
    } catch (err) {
        error.value = 'Failed to load bank account details.';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const submitForm = async () => {
    loading.value = true;
    error.value = null;
    errors.value = {};

    try {
        if (isEditing.value) {
            await axios.put(`/api/bankaccounts/${props.id}`, form.value);
        } else {
            await axios.post('/api/bankaccounts', form.value);
        }
        router.push({ name: 'bankaccounts.index' });
    } catch (err) {
        if (err.response && err.response.status === 422) {
             errors.value = err.response.data.errors;
             error.value = err.response.data.message || 'Please fix the errors above.';
        } else if (err.response && err.response.data && err.response.data.message) {
             error.value = err.response.data.message;
        } else {
            error.value = `An error occurred while saving the bank account. (Status: ${err.response?.status || 'Unknown'})`;
        }
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchAccount();
});
</script>
