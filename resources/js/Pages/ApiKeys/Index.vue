<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const apiKeys = ref([]);
const loading = ref(true);
const showCreateModal = ref(false);
const newKeyName = ref('');
const errors = ref({});
const createdKey = ref(null);
const creating = ref(false);
const errorMessage = ref('');

const loadApiKeys = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/api/api-keys');
        apiKeys.value = response.data;
    } catch (error) {
        console.error('Failed to load API keys:', error);
    } finally {
        loading.value = false;
    }
};

const createApiKey = async () => {
    if (!newKeyName.value.trim()) {
        errors.value = { name: ['Key name is required'] };
        return;
    }

    try {
        creating.value = true;
        errors.value = {};
        errorMessage.value = '';
        
        const response = await axios.post('/api/api-keys', {
            name: newKeyName.value.trim(),
        });
        
        if (response.data && response.data.key) {
            createdKey.value = response.data.key;
            newKeyName.value = '';
            // Modal'ı kapatma - kullanıcı key'i kopyalayabilsin
            // showCreateModal.value = false;
            await loadApiKeys();
        } else {
            errorMessage.value = 'Failed to create API key. Please try again.';
        }
    } catch (error) {
        console.error('Create API key error:', error);
        
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else if (error.response?.data?.message) {
            errorMessage.value = error.response.data.message;
        } else if (error.response?.data?.error) {
            errorMessage.value = error.response.data.error;
        } else {
            errorMessage.value = 'An error occurred while creating the API key. Please try again.';
        }
    } finally {
        creating.value = false;
    }
};

const deleteApiKey = async (id) => {
    if (!confirm('Are you sure you want to delete this API key?')) {
        return;
    }

    try {
        await axios.delete(`/api/api-keys/${id}`);
        await loadApiKeys();
    } catch (error) {
        console.error('Failed to delete API key:', error);
        alert('Failed to delete API key');
    }
};

const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        alert('API key copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy:', err);
        // Fallback: select text
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            alert('API key copied to clipboard!');
        } catch (e) {
            alert('Failed to copy. Please copy manually.');
        }
        document.body.removeChild(textarea);
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    createdKey.value = null;
    newKeyName.value = '';
    errors.value = {};
    errorMessage.value = '';
};

onMounted(() => {
    loadApiKeys();
});
</script>

<template>
    <Head title="API Keys" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    API Keys
                </h2>
                <PrimaryButton @click="showCreateModal = true">
                    Create API Key
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="loading" class="text-center py-8">
                            Loading...
                        </div>

                        <div v-else-if="apiKeys.length === 0" class="text-center py-8">
                            <p class="text-gray-500 mb-4">No API keys found.</p>
                            <PrimaryButton @click="showCreateModal = true">
                                Create Your First API Key
                            </PrimaryButton>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="key in apiKeys"
                                :key="key.id"
                                class="border border-gray-200 rounded-lg p-4"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">{{ key.name }}</h3>
                                        <p class="text-sm text-gray-500 mt-1">
                                            ID: {{ key.litellm_key_id }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            Created: {{ new Date(key.created_at).toLocaleDateString() }}
                                            <span v-if="key.last_used_at">
                                                | Last used: {{ new Date(key.last_used_at).toLocaleDateString() }}
                                            </span>
                                        </p>
                                    </div>
                                    <DangerButton @click="deleteApiKey(key.id)">
                                        Delete
                                    </DangerButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create API Key Modal -->
        <Modal :show="showCreateModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Create API Key</h2>

                <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded">
                    <p class="text-sm text-red-800">{{ errorMessage }}</p>
                </div>

                <div class="mb-4">
                    <InputLabel for="name" value="Key Name" />
                    <TextInput
                        id="name"
                        v-model="newKeyName"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="My API Key"
                        :disabled="creating || !!createdKey"
                        @keyup.enter="createApiKey"
                    />
                    <InputError :message="errors.name?.[0]" class="mt-2" />
                </div>

                <div v-if="createdKey" class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-sm font-semibold text-yellow-800 mb-2">
                        ⚠️ Important: Save this API key now. You won't be able to see it again!
                    </p>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 p-2 bg-white border border-yellow-300 rounded text-sm break-all">
                            {{ createdKey }}
                        </code>
                        <PrimaryButton @click="copyToClipboard(createdKey)">
                            Copy
                        </PrimaryButton>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900"
                        :disabled="creating"
                    >
                        {{ createdKey ? 'Close' : 'Cancel' }}
                    </button>
                    <PrimaryButton 
                        @click="createApiKey"
                        :disabled="creating || !!createdKey"
                    >
                        <span v-if="creating">Creating...</span>
                        <span v-else>Create</span>
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

