<template>
  <AuthenticatedLayout>
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Account Profile Settings</h2>
      <p class="text-sm text-gray-500 mb-6">Update your personal authentication profile data.</p>
      
      <form @submit.prevent="updateProfile" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">User Name</label>
          <input v-model="form.name" type="text" class="mt-1 block w-full border rounded-md shadow-sm p-2.5 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"/>
          <p v-if="form.errors.name" class="text-xs text-red-600 mt-1">{{ form.errors.name }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Email Address</label>
          <input v-model="form.email" type="email" class="mt-1 block w-full border rounded-md shadow-sm p-2.5 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"/>
          <p v-if="form.errors.email" class="text-xs text-red-600 mt-1">{{ form.errors.email }}</p>
        </div>

        <hr class="my-6 border-gray-200" />
        <p class="text-xs text-amber-600 font-semibold bg-amber-50 p-2 rounded">Leave password fields blank if you do not wish to update it.</p>

        <div>
          <label class="block text-sm font-medium text-gray-700">New Password</label>
          <input v-model="form.password" type="password" class="mt-1 block w-full border rounded-md shadow-sm p-2.5 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"/>
          <p v-if="form.errors.password" class="text-xs text-red-600 mt-1">{{ form.errors.password }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
          <input v-model="form.password_confirmation" type="password" class="mt-1 block w-full border rounded-md shadow-sm p-2.5 border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"/>
        </div>

        <div class="flex justify-end">
          <button :disabled="form.processing" type="submit" class="bg-indigo-600 text-white font-medium py-2 px-6 rounded-md shadow hover:bg-indigo-700 transition disabled:opacity-50 text-sm">
            Save Profile Changes
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ user: Object });

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: '',
});

const updateProfile = () => {
  form.put('/profile', {
    onSuccess: () => {
      form.reset('password', 'password_confirmation');
      alert("Profile updated effectively.");
    }
  });
};
</script>