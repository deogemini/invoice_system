<template>
    <div class="flex flex-col min-h-screen">
        <div class="container mx-auto p-4 flex-grow">
            <nav class="bg-gray-800 p-4 mb-6 rounded text-white flex justify-between print:hidden">
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
        
        <footer class="bg-gray-800 text-white text-center p-4 print:hidden">
            <p>EPORT SOLUTIONS LIMITED &copy; 2026</p>
            <p class="text-xs text-gray-400 mt-1">Version 1</p>
        </footer>
    </div>
</template>

<script setup>
</script>
