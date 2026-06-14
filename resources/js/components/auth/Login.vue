<template>
    <div class="min-h-[70vh] flex items-center justify-center">
        <div class="w-full max-w-md border border-gray-200 rounded-lg bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Sign in</h1>
            <p class="text-sm text-gray-600 mb-6">Use your account to continue.</p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" type="email" autocomplete="email" required class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p v-if="errors.email" class="text-sm text-red-600 mt-1">{{ errors.email[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="current-password"
                            required
                            class="w-full border rounded px-3 py-2 pr-11 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-gray-500 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'"
                            @click="showPassword = !showPassword"
                        >
                            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.58 10.58A2.98 2.98 0 0 0 9 13a3 3 0 0 0 4.42 2.65" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.36 7.36C4.2 9.13 2.25 12 2.25 12s3.75 6.75 9.75 6.75c1.55 0 2.94-.45 4.16-1.12" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.12 5.5C18.82 6.55 21.75 12 21.75 12a17.5 17.5 0 0 1-2.44 3.2" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="errors.password" class="text-sm text-red-600 mt-1">{{ errors.password[0] }}</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input v-model="form.remember" type="checkbox" class="rounded">
                    Remember me
                </label>

                <RouterLink :to="{ name: 'forgot-password' }" class="block text-right text-sm font-medium text-blue-600 hover:text-blue-800">
                    Forgot password?
                </RouterLink>

                <button type="submit" :disabled="loading" class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold py-2 px-4 rounded">
                    {{ loading ? 'Signing in...' : 'Sign in' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { login, isAdministrator } from '../../auth';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const errors = ref({});
const showPassword = ref(false);
const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const submit = async () => {
    loading.value = true;
    errors.value = {};

    try {
        await login(form);
        router.push(route.query.redirect || { name: isAdministrator() ? 'admin.dashboard' : 'client.dashboard' });
    } catch (error) {
        errors.value = error.response?.data?.errors || {
            email: [error.response?.data?.message || 'Unable to sign in. Please try again.'],
        };
    } finally {
        loading.value = false;
    }
};
</script>
