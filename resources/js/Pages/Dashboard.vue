<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref({
    totalRequests: 0,
    totalCost: 0,
    activeKeys: 0,
    usagePercentage: 0,
});

const loading = ref(true);

onMounted(async () => {
    try {
        const response = await axios.get('/api/dashboard/stats');
        stats.value = response.data;
    } catch (error) {
        console.error('Failed to load stats:', error);
        // Hata durumunda placeholder data göster
        stats.value = {
            totalRequests: 0,
            totalCost: 0,
            activeKeys: 0,
            usagePercentage: 0,
        };
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Dashboard</h2>
                    <p class="mt-1 text-sm text-gray-500">Welcome back! Here's what's happening with your API usage.</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                </div>

                <!-- Stats Cards -->
                <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <!-- Total API Calls Card -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow duration-300">
                        <div class="p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wide">Total API Calls</p>
                                    <p class="mt-2 text-4xl font-bold">{{ stats.totalRequests.toLocaleString() }}</p>
                                    <p class="mt-1 text-blue-100 text-xs">This month</p>
                                </div>
                                <div class="bg-white/20 rounded-lg p-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Cost Card -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow duration-300">
                        <div class="p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-green-100 text-sm font-medium uppercase tracking-wide">Total Cost</p>
                                    <p class="mt-2 text-4xl font-bold">${{ stats.totalCost.toFixed(2) }}</p>
                                    <p class="mt-1 text-green-100 text-xs">This month</p>
                                </div>
                                <div class="bg-white/20 rounded-lg p-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active API Keys Card -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow duration-300">
                        <div class="p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-purple-100 text-sm font-medium uppercase tracking-wide">Active API Keys</p>
                                    <p class="mt-2 text-4xl font-bold">{{ stats.activeKeys }}</p>
                                    <Link :href="route('api-keys.page')" class="mt-1 text-purple-100 text-xs hover:text-white underline">
                                        Manage keys →
                                    </Link>
                                </div>
                                <div class="bg-white/20 rounded-lg p-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Usage Card -->
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow duration-300">
                        <div class="p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-orange-100 text-sm font-medium uppercase tracking-wide">Usage</p>
                                    <p class="mt-2 text-4xl font-bold">{{ stats.usagePercentage }}%</p>
                                    <p class="mt-1 text-orange-100 text-xs">Of plan limit</p>
                                </div>
                                <div class="bg-white/20 rounded-lg p-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                            </div>
                            <!-- Progress Bar -->
                            <div class="mt-4 bg-white/20 rounded-full h-2">
                                <div 
                                    class="bg-white rounded-full h-2 transition-all duration-500"
                                    :style="{ width: Math.min(stats.usagePercentage, 100) + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                            <p class="mt-1 text-sm text-gray-500">Common tasks and shortcuts</p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <Link
                                    :href="route('api-keys.page')"
                                    class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg hover:from-blue-100 hover:to-blue-200 transition-all duration-200 group"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-blue-500 rounded-lg p-2">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 group-hover:text-blue-600">Manage API Keys</p>
                                            <p class="text-sm text-gray-500">Create, view, and manage your API keys</p>
                                        </div>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Usage Overview -->
                    <div class="bg-white overflow-hidden shadow-lg rounded-xl">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Usage Overview</h3>
                            <p class="mt-1 text-sm text-gray-500">Your current plan usage</p>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">API Requests</span>
                                        <span class="text-sm font-semibold text-gray-900">{{ stats.usagePercentage }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div 
                                            class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500"
                                            :style="{ width: Math.min(stats.usagePercentage, 100) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                                <div class="pt-4 border-t border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500">Total Requests</span>
                                        <span class="text-lg font-bold text-gray-900">{{ stats.totalRequests.toLocaleString() }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">Total Cost</span>
                                    <span class="text-lg font-bold text-green-600">${{ stats.totalCost.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
