<template>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">{{ isEditing ? 'Edit Customer' : 'Add Customer' }}</h1>

        <form @submit.prevent="submitForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 max-w-lg">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Name
                </label>
                <input v-model="form.name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.name }" id="name" type="text" placeholder="Name" required>
                <p v-if="errors.name" class="text-red-500 text-xs italic">{{ errors.name[0] }}</p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email
                </label>
                <input v-model="form.email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.email }" id="email" type="email" placeholder="Email" required>
                <p v-if="errors.email" class="text-red-500 text-xs italic">{{ errors.email[0] }}</p>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                    Address
                </label>
                <textarea v-model="form.address" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.address }" id="address" placeholder="Address"></textarea>
            <p v-if="errors.address" class="text-red-500 text-xs italic">{{ errors.address[0] }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="p_o_box">
                P.O. Box
            </label>
            <input v-model="form.p_o_box" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.p_o_box }" id="p_o_box" type="text" placeholder="P.O. Box">
            <p v-if="errors.p_o_box" class="text-red-500 text-xs italic">{{ errors.p_o_box[0] }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="tin">
                Tax Identification Number (TIN)
            </label>
            <input v-model="form.tin" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.tin }" id="tin" type="text" placeholder="TIN">
            <p v-if="errors.tin" class="text-red-500 text-xs italic">{{ errors.tin[0] }}</p>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                Phone Number
            </label>
            <input v-model="form.phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.phone }" id="phone" type="text" placeholder="Phone">
            <p v-if="errors.phone" class="text-red-500 text-xs italic">{{ errors.phone[0] }}</p>
        </div>

            <div class="flex items-center justify-between">
                <button :disabled="loading" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    {{ isEditing ? 'Update' : 'Create' }}
                </button>
                <router-link :to="{ name: 'customers.index' }" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Cancel
                </router-link>
            </div>

            <p v-if="error" class="text-red-500 text-xs italic mt-4">{{ error }}</p>
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
    name: '',
    email: '',
    address: '',
    p_o_box: '',
    tin: '',
    phone: ''
});

const loading = ref(false);
const error = ref(null);
const errors = ref({});

const isEditing = computed(() => !!props.id);

const fetchCustomer = async () => {
    if (!isEditing.value) return;

    loading.value = true;
    try {
        const response = await axios.get(`/api/customers/${props.id}`);
        form.value = response.data.customer;
    } catch (err) {
        error.value = 'Failed to load customer details.';
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
            await axios.put(`/api/customers/${props.id}`, form.value);
        } else {
            await axios.post('/api/customers', form.value);
        }
        router.push({ name: 'customers.index' });
    } catch (err) {
        if (err.response && err.response.status === 422) {
             errors.value = err.response.data.errors;
             error.value = err.response.data.message || 'Please fix the errors above.';
        } else if (err.response && err.response.data && err.response.data.message) {
             error.value = err.response.data.message;
        } else {
            error.value = `An error occurred while saving the customer. (Status: ${err.response?.status || 'Unknown'})`;
        }
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchCustomer();
});
</script>
