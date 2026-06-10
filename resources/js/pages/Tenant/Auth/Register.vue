<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <h2 class="text-3xl font-bold tracking-tight text-gray-900">
        Join Workspace Instance
      </h2>
      <p class="mt-2 text-sm text-gray-600">
        Already have an profile?
        <Link href="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
          Sign In here
        </Link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form @submit.prevent="handleTenantRegister" class="space-y-6">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
            <input
              v-model="form.name"
              type="text"
              id="name"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              :class="{'border-red-500': form.errors.name}"
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Corporate Email</label>
            <input
              v-model="form.email"
              type="email"
              id="email"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              :class="{'border-red-500': form.errors.email}"
            />
            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Access Password</label>
            <input
              v-model="form.password"
              type="password"
              id="password"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              :class="{'border-red-500': form.errors.password}"
            />
            <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
          </div>

          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              id="password_confirmation"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
            >
              {{ form.processing ? 'Registering...' : 'Complete Register' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const handleTenantRegister = () => {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>