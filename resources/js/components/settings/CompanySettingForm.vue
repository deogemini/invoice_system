<template>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-6">Company Settings</h1>

        <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ successMessage }}</span>
        </div>

        <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ error }}</span>
        </div>

        <form @submit.prevent="submitForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 max-w-2xl mx-auto" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="company_name">
                    Company Name
                </label>
                <input v-model="form.company_name" type="text" id="company_name" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="tin_number">
                        TIN Number
                    </label>
                    <input v-model="form.tin_number" type="text" id="tin_number"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                        Phone
                    </label>
                    <input v-model="form.phone" type="text" id="phone"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email
                </label>
                <input v-model="form.email" type="email" id="email"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="p_o_box">
                    P.O. Box
                </label>
                <input v-model="form.p_o_box" type="text" id="p_o_box"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                    Address / Location
                </label>
                <textarea v-model="form.address" id="address" rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="logo">
                        Company Logo
                    </label>
                    <input @change="handleLogoUpload" type="file" id="logo" accept="image/*"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <div v-if="currentLogo" class="mt-2">
                        <p class="text-xs text-gray-500 mb-1">Current Logo:</p>
                        <img :src="currentLogo" alt="Current Logo" class="h-16 object-contain">
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="stamp">
                        Company Stamp
                    </label>
                    <input @change="handleStampUpload" type="file" id="stamp" accept="image/*"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                     <div v-if="currentStamp" class="mt-2">
                        <p class="text-xs text-gray-500 mb-1">Current Stamp:</p>
                        <img :src="currentStamp" alt="Current Stamp" class="h-16 object-contain">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6">
                <button :disabled="loading"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
                    type="submit">
                    {{ loading ? 'Saving...' : 'Save Settings' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const form = ref({
    company_name: '',
    tin_number: '',
    p_o_box: '',
    address: '',
    phone: '',
    email: '',
    logo: null,
    stamp: null
});

const currentLogo = ref(null);
const currentStamp = ref(null);
const loading = ref(false);
const error = ref(null);
const successMessage = ref(null);

const fetchSettings = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/company-settings');
        const settings = response.data;
        form.value.company_name = settings.company_name || '';
        form.value.tin_number = settings.tin_number || '';
        form.value.p_o_box = settings.p_o_box || '';
        form.value.address = settings.address || '';
        form.value.phone = settings.phone || '';
        form.value.email = settings.email || '';
        
        if (settings.logo_path) {
            currentLogo.value = `/storage/${settings.logo_path}`;
        }
        if (settings.stamp_path) {
            currentStamp.value = `/storage/${settings.stamp_path}`;
        }
    } catch (err) {
        console.error(err);
        error.value = 'Failed to load settings.';
    } finally {
        loading.value = false;
    }
};

const handleLogoUpload = (event) => {
    form.value.logo = event.target.files[0];
};

const handleStampUpload = (event) => {
    form.value.stamp = event.target.files[0];
};

const submitForm = async () => {
    loading.value = true;
    error.value = null;
    successMessage.value = null;

    const formData = new FormData();
    formData.append('company_name', form.value.company_name);
    if (form.value.tin_number) formData.append('tin_number', form.value.tin_number);
    if (form.value.p_o_box) formData.append('p_o_box', form.value.p_o_box);
    if (form.value.address) formData.append('address', form.value.address);
    if (form.value.phone) formData.append('phone', form.value.phone);
    if (form.value.email) formData.append('email', form.value.email);
    if (form.value.logo) formData.append('logo', form.value.logo);
    if (form.value.stamp) formData.append('stamp', form.value.stamp);

    try {
        const response = await axios.post('/api/company-settings', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        successMessage.value = 'Settings saved successfully!';
        
        // Update current images previews
        if (response.data.settings.logo_path) {
            currentLogo.value = `/storage/${response.data.settings.logo_path}`;
        }
        if (response.data.settings.stamp_path) {
            currentStamp.value = `/storage/${response.data.settings.stamp_path}`;
        }
        
        // Clear file inputs (optional, but good for UX if they want to re-upload)
        form.value.logo = null;
        form.value.stamp = null;
        
    } catch (err) {
        console.error(err);
        error.value = 'Failed to save settings. Please check your inputs.';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchSettings();
});
</script>
