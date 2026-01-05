<template>
    <div class="container mx-auto p-4">
        <nav class="bg-gray-800 p-4 mb-6 rounded text-white flex justify-between">
             <div>
                <router-link :to="{ name: 'home' }" class="font-bold text-xl">Invoice System</router-link>
             </div>
             <div>
                 <router-link :to="{ name: 'customers.index' }" class="hover:text-gray-300 mr-4">Customers</router-link>
                 <router-link :to="{ name: 'products.index' }" class="hover:text-gray-300 mr-4">Products</router-link>
                 <router-link :to="{ name: 'invoices.index' }" class="hover:text-gray-300 mr-4">Invoices</router-link>
                <router-link :to="{ name: 'bankaccounts.index' }" class="hover:text-gray-300 mr-4">Bank Accounts</router-link>
                <router-link :to="{ name: 'settings.edit' }" class="hover:text-gray-300">Settings</router-link>
            </div>
       </nav>

        <router-view></router-view>
    </div>
</template>

<script setup>
</script>
