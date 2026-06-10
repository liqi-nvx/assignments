<template>
  <div class="min-h-screen bg-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <h2 class="text-3xl font-extrabold text-white tracking-tight">
        Create Your Cloud Platform Instance
      </h2>
      <p class="mt-2 text-sm text-slate-400">
        Enter your desired tenant identifier to provision a private database.
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form @submit.prevent="submitCentralRegister" class="space-y-6">
          <div>
            <label for="tenant_id" class="block text-sm font-medium text-gray-700">
              Tenant ID (Workspace Subdomain)
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <input
                v-model="form.id"
                type="text"
                id="tenant_id"
                required
                placeholder="e.g., tenant1"
                class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-32 sm:text-sm border-gray-300 rounded-md p-2.5 border"
                :class="{'border-red-500': errors.id}"
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none bg-gray-100 border-l px-3 rounded-r-md">
                <span class="text-gray-500 sm:text-sm">.localhost</span>
              </div>
            </div>
            <p v-if="errors.id" class="mt-2 text-xs text-red-600">{{ errors.id }}</p>
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition"
            >
              {{ loading ? 'Provisioning Environment...' : 'Register Tenant Workspace' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const form = ref({ id: '' });
const errors = ref({});
const loading = ref(false);

const submitCentralRegister = async () => {
  loading.value = true;
  errors.value = {};
  
  try {
    // 中央租户生成可能需要跑底层 Migration 耗时稍长，因此采用无阻塞的 Axios 请求
    const response = await axios.post('/register', form.value);
    alert(response.data.message || 'Tenant workspace provisioned successfully!');
    
    // 自动重定向去往该租户独立的独立子域登录台
    if (response.data.redirect_url) {
      window.location.href = response.data.redirect_url;
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      alert(error.response?.data?.message || 'System provisioning failed.');
    }
  } finally {
    loading.value = false;
  }
};
</script>