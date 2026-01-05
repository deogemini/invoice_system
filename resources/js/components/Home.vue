<template>
    <div class="container mx-auto p-4 text-center">
        <div class="bg-white shadow-md rounded-lg p-10 mt-10">
            <h1 class="text-4xl font-bold text-gray-800 mb-6">Welcome to Eport Solutions Limited Invoice System</h1>
            <p class="text-xl text-gray-600 mb-8">
                This is a comprehensive system designed to manage your customers, products, and invoices efficiently.
            </p>
            <div class="flex justify-center space-x-4">
                <router-link :to="{ name: 'customers.index' }" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow transition duration-300">
                    Manage Customers
                </router-link>
                <router-link :to="{ name: 'products.index' }" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow transition duration-300">
                    Manage Products
                </router-link>
                <router-link :to="{ name: 'invoices.index' }" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg shadow transition duration-300">
                    Manage Invoices
                </router-link>
                <router-link :to="{ name: 'bankaccounts.index' }" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded-lg shadow transition duration-300">
                    Manage Bank Accounts
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
// Home component logic
</script>
