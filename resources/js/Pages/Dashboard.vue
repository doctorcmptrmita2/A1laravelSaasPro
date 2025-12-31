<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const stats = ref({
    totalRequests: 0,
    totalCost: 0,
    activeKeys: 0,
    usagePercentage: 0,
});

const loading = ref(true);

onMounted(async () => {
    try {
        // TODO: Fetch stats from API
        // const response = await axios.get('/api/dashboard/stats');
        // stats.value = response.data;
        
        // Placeholder data
        stats.value = {
            totalRequests: 0,
            totalCost: 0,
            activeKeys: 0,
            usagePercentage: 0,
        };
    } catch (error) {
        console.error('Failed to load stats:', error);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Total API Calls</div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">
                                {{ stats.totalRequests.toLocaleString() }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">This month</div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Total Cost</div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">
                                ${{ stats.totalCost.toFixed(2) }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">This month</div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Active API Keys</div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">
                                {{ stats.activeKeys }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                <Link :href="route('api-keys.index')" class="text-blue-600 hover:text-blue-800">
                                    Manage keys
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Usage</div>
                            <div class="mt-2 text-3xl font-bold text-gray-900">
                                {{ stats.usagePercentage }}%
                            </div>
                            <div class="mt-1 text-xs text-gray-500">Of plan limit</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="flex gap-4">
                            <Link
                                :href="route('api-keys.index')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Manage API Keys
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
