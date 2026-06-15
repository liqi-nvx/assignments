<template>
  <div class="flex h-screen bg-gray-100 font-sans">
    <aside class="w-64 bg-slate-900 text-white flex flex-col justify-between">
      <div class="p-5">
        <h2 class="text-xl font-bold tracking-wider mb-8 text-indigo-400">TENANT PANEL</h2>
        <nav class="space-y-2">
          <Link href="/" class="block py-2.5 px-1 rounded transition duration-200 hover:bg-slate-800">📊 Dashboard</Link>
          <Link href="/products" class="block py-2.5 px-1 rounded transition duration-200 hover:bg-slate-800">📦 Stock Management</Link>
          <Link href="/customers" class="block py-2.5 px-1 rounded transition duration-200 hover:bg-slate-800">👥 Customer Management</Link>
          <Link href="/invoices" class="block py-2.5 px-1 rounded transition duration-200 hover:bg-slate-800">🧾 Invoice Management</Link>
          <Link href="/payments" class="block py-2.5 px-1 rounded transition duration-200 hover:bg-slate-800">💰 Payment Records</Link>
          <Link href="/sales-report" class="block py-2.5 px-1 rounded transition duration-200 hover:bg-slate-800">📊 Sales Reports</Link>
          <Link href="/profile" class="block py-2.5 px-1 rounded transition duration-200 hover:bg-slate-800">⚙️ Edit Profile</Link>
          <Link href="/settings" class="block py-2.5 px-1 rounded transition duration-200 hover:bg-slate-800">⚙️ Mail Settings</Link>
        </nav>
      </div>
      <div class="p-4 border-t border-slate-800">
        <button @click="logout" class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2 px-4 rounded-md text-sm font-medium transition">
          Log Out
        </button>
      </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-y-auto">
      <header class="bg-white shadow-sm py-4 px-8 flex justify-between items-center">
        <h1 class="text-lg font-semibold text-gray-700">
          Tenant: <span class="text-indigo-600 uppercase">{{ tenantId }}</span>
        </h1>
        <div class="flex items-center space-x-3 text-sm text-gray-600">
          <span>Hi, <strong>{{ userName }}</strong></span>
        </div>
      </header>
      <div class="p-8">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const tenantId = computed(() => usePage().props.tenant || 'Unknown');
const userName = computed(() => usePage().props.auth?.user?.name || 'Guest');

const logout = () => {
  router.post('/logout');
};
</script>