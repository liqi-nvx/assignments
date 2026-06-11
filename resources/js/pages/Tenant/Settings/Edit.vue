<template>
  <AuthenticatedLayout>
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="p-6 bg-slate-50 border-b border-gray-100">
        <h2 class="text-xl font-bold text-gray-800">Outgoing Mail Configuration (Gmail SMTP)</h2>
        <p class="text-xs text-gray-500 mt-1">Configure your organization's custom Gmail to send customer invoices automatically.</p>
      </div>

      <form @submit.prevent="submitForm" class="p-6 space-y-5">
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Sender Name</label>
          <input 
            v-model="form.sender_name" 
            type="text" 
            placeholder="e.g., ABC Trading Co."
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
            :class="{ 'border-rose-500': form.errors.sender_name }"
          />
          <div v-if="form.errors.sender_name" class="text-rose-500 text-xs mt-1">{{ form.errors.sender_name }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Gmail Address</label>
          <input 
            v-model="form.mail_username" 
            type="email" 
            placeholder="your-business@gmail.com"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
            :class="{ 'border-rose-500': form.errors.mail_username }"
          />
          <div v-if="form.errors.mail_username" class="text-rose-500 text-xs mt-1">{{ form.errors.mail_username }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Google App Password 
            <span v-if="props.settings.has_password" class="text-emerald-600 font-normal text-xs ml-2">(🔒 Saved / Saved)</span>
          </label>
          <input 
            v-model="form.mail_password" 
            type="password" 
            :placeholder="props.settings.has_password ? '•••••••••••••••• (Leave blank to keep current)' : 'Enter 16-character app password'"
            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
            :class="{ 'border-rose-500': form.errors.mail_password }"
          />
          <div v-if="form.errors.mail_password" class="text-rose-500 text-xs mt-1">{{ form.errors.mail_password }}</div>
        </div>

        <div class="p-4 bg-amber-50 rounded-lg border border-amber-200 flex items-start space-x-3">
          <span class="text-lg mt-0.5">⚠️</span>
          <div class="text-xs text-amber-800 leading-relaxed">
            <strong class="block mb-1">Do NOT use your regular Gmail login password!</strong>
            Gmail SMTP requires a 16-character <span class="font-semibold underline">App Password</span>. 
            To generate one: Enable 2-Step Verification in your Google Account &gt; Search 'App Passwords' &gt; Generate for 'Mail'.
          </div>
        </div>

        <div class="flex justify-end pt-2 border-t border-gray-100">
          <button 
            type="submit" 
            :disabled="form.processing"
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-5 rounded-md text-sm transition shadow-sm disabled:opacity-50"
          >
            {{ form.processing ? 'Saving...' : 'Save Mail Settings' }}
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  settings: Object
});

const form = useForm({
  sender_name: props.settings.sender_name,
  mail_username: props.settings.mail_username,
  mail_password: '',
});

const submitForm = () => {
  form.put('/settings', {
    preserveScroll: true,
    onSuccess: () => {
      form.mail_password = '';
      alert('Settings saved successfully!');
    }
  });
};
</script>