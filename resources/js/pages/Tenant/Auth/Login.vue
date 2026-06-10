<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <h2 class="text-3xl font-bold tracking-tight text-gray-900">
        Workspace Portal Sign In
      </h2>
      <p class="mt-2 text-sm text-gray-600">
        Or
        <Link href="/register" class="font-medium text-indigo-600 hover:text-indigo-500">
          create a new internal account
        </Link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div v-if="$page.props.errors.email" class="mb-4 bg-red-50 p-3 rounded-md text-sm text-red-700">
          {{ $page.props.errors.email }}
        </div>

        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input
              v-model="form.email"
              type="email"
              id="email"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input
              v-model="form.password"
              type="password"
              id="password"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2.5 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input id="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"/>
              <label for="remember" class="ml-2 block text-sm text-gray-900">Remember session</label>
            </div>
          </div>

          <div>
            <button
              type="submit"
              class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Authenticate & Enter
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
  email: '',
  password: '',
});

const handleLogin = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>