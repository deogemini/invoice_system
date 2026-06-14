<template>
    <div class="flex flex-col min-h-screen bg-gray-50">
        <div class="container mx-auto p-4 flex-grow">
            <nav v-if="isAuthenticated()" class="bg-gray-800 p-4 mb-6 rounded text-white flex flex-wrap gap-4 justify-between items-center print:hidden">
                 <div>
                    <router-link :to="{ name: dashboardRoute }" class="font-bold text-xl">Invoice System</router-link>
                    <span class="ml-3 text-xs uppercase tracking-wide text-gray-300">{{ auth.user.role }}</span>
                 </div>
                 <div class="flex flex-wrap items-center gap-4">
                    <router-link v-if="isAdministrator()" :to="{ name: 'admin.clients.index' }" class="hover:text-gray-300">Clients</router-link>
                    <router-link :to="{ name: 'customers.index' }" class="hover:text-gray-300">Customers</router-link>
                    <router-link :to="{ name: 'products.index' }" class="hover:text-gray-300">Products</router-link>
                    <router-link :to="{ name: 'invoices.index' }" class="hover:text-gray-300">Invoices</router-link>
                    <router-link :to="{ name: 'bankaccounts.index' }" class="hover:text-gray-300">Bank Accounts</router-link>
                    <router-link :to="{ name: 'settings.edit' }" class="hover:text-gray-300">Settings</router-link>
                    <button @click="handleLogout" class="bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded">Logout</button>
                </div>
           </nav>
    
            <router-view></router-view>
        </div>
        
        <footer class="bg-gray-800 text-white text-center p-4 print:hidden">
            <p>EPORT SOLUTIONS LIMITED &copy; 2026</p>
            <p class="text-xs text-gray-400 mt-1">Version 2</p>
        </footer>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { auth, isAuthenticated, isAdministrator, logout } from './auth';

const router = useRouter();

const dashboardRoute = computed(() => isAdministrator() ? 'admin.dashboard' : 'client.dashboard');

const handleLogout = async () => {
    await logout();
    router.push({ name: 'login' });
};
</script>
