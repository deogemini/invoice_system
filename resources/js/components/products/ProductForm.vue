<template>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">{{ isEditing ? 'Edit Product' : 'Add Product' }}</h1>

        <form @submit.prevent="submitForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 max-w-lg">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="item_code">
                    Item Code
                </label>
                <input v-model="form.item_code" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.item_code }" id="item_code" type="text" placeholder="Item Code" required>
                <p v-if="errors.item_code" class="text-red-500 text-xs italic">{{ errors.item_code[0] }}</p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                    Description
                </label>
                <textarea v-model="form.description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.description }" id="description" placeholder="Description" required></textarea>
                <p v-if="errors.description" class="text-red-500 text-xs italic">{{ errors.description[0] }}</p>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="unit_price">
                    Unit Price
                </label>
                <input v-model="form.unit_price" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" :class="{ 'border-red-500': errors.unit_price }" id="unit_price" type="number" step="0.01" placeholder="Unit Price" required>
                <p v-if="errors.unit_price" class="text-red-500 text-xs italic">{{ errors.unit_price[0] }}</p>
            </div>

            <div class="flex items-center justify-between">
                <button :disabled="loading" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    {{ isEditing ? 'Update' : 'Create' }}
                </button>
                <router-link :to="{ name: 'products.index' }" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
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
    item_code: '',
    description: '',
    unit_price: ''
});

const loading = ref(false);
const error = ref(null);
const errors = ref({});

const isEditing = computed(() => !!props.id);

const fetchProduct = async () => {
    if (!isEditing.value) return;
    
    loading.value = true;
    try {
        const response = await axios.get(`/api/products/${props.id}`);
        form.value = response.data.product;
    } catch (err) {
        error.value = 'Failed to load product details.';
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
            await axios.put(`/api/products/${props.id}`, form.value);
        } else {
            await axios.post('/api/products', form.value);
        }
        router.push({ name: 'products.index' });
    } catch (err) {
        if (err.response && err.response.status === 422) {
             errors.value = err.response.data.errors;
             error.value = err.response.data.message || 'Please fix the errors above.';
        } else if (err.response && err.response.data && err.response.data.message) {
             error.value = err.response.data.message;
        } else {
            error.value = `An error occurred while saving the product. (Status: ${err.response?.status || 'Unknown'})`;
        }
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchProduct();
});
</script>
