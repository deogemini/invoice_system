<template>
    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold mb-6">{{ isEditMode ? 'Edit Client' : 'Register Client' }}</h1>

        <form @submit.prevent="submit" class="bg-white border border-gray-200 rounded-lg p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input v-model="form.name" type="text" required class="w-full border rounded px-3 py-2">
                <p v-if="errors.name" class="text-sm text-red-600 mt-1">{{ errors.name[0] }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input v-model="form.email" type="email" required class="w-full border rounded px-3 py-2">
                <p v-if="errors.email" class="text-sm text-red-600 mt-1">{{ errors.email[0] }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input v-model="form.password" type="password" :required="!isEditMode" autocomplete="new-password" class="w-full border rounded px-3 py-2">
                <p v-if="errors.password" class="text-sm text-red-600 mt-1">{{ errors.password[0] }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" :required="!isEditMode" autocomplete="new-password" class="w-full border rounded px-3 py-2">
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.is_active" type="checkbox" class="rounded">
                Active
            </label>

            <div class="flex gap-3">
                <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white px-4 py-2 rounded">
                    {{ loading ? 'Saving...' : 'Save Client' }}
                </button>
                <router-link :to="{ name: 'admin.clients.index' }" class="border px-4 py-2 rounded">Cancel</router-link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const errors = ref({});
const isEditMode = computed(() => Boolean(route.params.id));
const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    is_active: true,
});

const fetchClient = async () => {
    const response = await axios.get(`/api/admin/clients/${route.params.id}`);
    Object.assign(form, {
        name: response.data.client.name,
        email: response.data.client.email,
        is_active: response.data.client.is_active,
        password: '',
        password_confirmation: '',
    });
};

const submit = async () => {
    loading.value = true;
    errors.value = {};

    const payload = { ...form };
    if (isEditMode.value && !payload.password) {
        delete payload.password;
        delete payload.password_confirmation;
    }

    try {
        if (isEditMode.value) {
            await axios.put(`/api/admin/clients/${route.params.id}`, payload);
        } else {
            await axios.post('/api/admin/clients', payload);
        }

        router.push({ name: 'admin.clients.index' });
    } catch (error) {
        errors.value = error.response?.data?.errors || {};
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (isEditMode.value) {
        fetchClient();
    }
});
</script>
