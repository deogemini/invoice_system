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

        <div class="mt-10 bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Invoices Created Per Day</h2>
            <div v-if="loading" class="text-gray-500">Loading chart...</div>
            <div v-else-if="error" class="text-red-500">{{ error }}</div>
            <div v-else class="relative h-96">
                <Bar :data="chartData" :options="chartOptions" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const loading = ref(true);
const error = ref(null);
const chartData = ref({
    labels: [],
    datasets: []
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Daily Invoice Stats (Last 30 Days)'
        }
    }
};

const fetchStats = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/api/dashboard/stats');
        const stats = response.data.stats;

        chartData.value = {
            labels: stats.map(s => s.date),
            datasets: [
                {
                    label: 'Number of Invoices',
                    backgroundColor: '#8b5cf6', // purple-500
                    data: stats.map(s => s.count),
                    yAxisID: 'y'
                },
                {
                    label: 'Total Amount',
                    backgroundColor: '#10b981', // green-500
                    data: stats.map(s => s.total_amount),
                    yAxisID: 'y1'
                }
            ]
        };

        // Add dual axis support to options dynamically if needed,
        // but for now let's keep it simple or configure scales:
        chartOptions.scales = {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: { display: true, text: 'Count' }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false, // only want the grid lines for one axis to show up
                },
                title: { display: true, text: 'Amount' }
            },
        };

    } catch (err) {
        error.value = 'Failed to load dashboard stats.';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStats();
});
</script>
