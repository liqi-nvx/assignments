<template>
  <AuthenticatedLayout>
    <div class="space-y-6">
      <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
        <div class="mb-4">
          <Link href="/products" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">← Back to Product List</Link>
        </div>
        <div class="flex justify-between items-start">
          <div>
            <span class="text-xs font-mono uppercase bg-gray-100 text-gray-600 px-2 py-0.5 rounded">ID: {{ product.id }}</span>
            <h1 class="text-3xl font-bold text-gray-800 mt-1">{{ product.name }}</h1>
          </div>
          <div class="text-right">
            <p class="text-xs text-gray-400 uppercase font-semibold">Standard Unit Price</p>
            <p class="text-2xl font-extrabold text-slate-700">${{ parseFloat(product.price).toFixed(2) }}</p>
            <span :class="product.stock === 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'" class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-bold">
              {{ product.stock }} Units Remaining
            </span>
          </div>
        </div>
      </div>

      <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
        <h3 class="text-xs font-bold uppercase text-slate-500 mb-3">Linked Invoice Filters Explorer</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
          <input v-model="searchFields.invoice_no" @input="debouncedSearch" type="text" placeholder="Invoice No (e.g. INV...)" class="border rounded p-2 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"/>
          <input v-model="searchFields.customer_name" @input="debouncedSearch" type="text" placeholder="Filter customer name..." class="border rounded p-2 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"/>
          <select v-model="searchFields.status" @change="debouncedSearch" class="border rounded p-2 text-xs bg-white">
            <option value="">All Payment Statuses</option>
            <option value="unpaid">Unpaid</option>
            <option value="partial">Partial</option>
            <option value="paid">Paid</option>
          </select>
          <input v-model="searchFields.start_date" @change="debouncedSearch" type="date" class="border rounded p-2 text-xs bg-white"/>
          <input v-model="searchFields.end_date" @change="debouncedSearch" type="date" class="border rounded p-2 text-xs bg-white"/>
        </div>
        <div class="flex justify-end mt-2" v-if="hasActiveFilters">
          <button @click="resetFilters" class="text-xs text-rose-600 hover:underline">Clear Search Filter Settings</button>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-md overflow-hidden border">
        <div class="px-6 py-4 bg-gray-50 border-b">
          <h2 class="text-lg font-bold text-gray-700">Invoiced Transaction Logs Portfolio (Product Tracking View)</h2>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
            <thead class="bg-slate-100 text-slate-700 text-xs font-bold uppercase">
              <tr>
                <th class="px-6 py-3">Invoice No</th>
                <th class="px-6 py-3">Customer Client</th>
                <th class="px-6 py-3">Qty Purchased Here</th>
                <th class="px-6 py-3">Deal Unit Price</th>
                <th class="px-6 py-3">Item Combined Subtotal</th>
                <th class="px-6 py-3">Issued Date</th>
                <th class="px-6 py-3">Lifecycle Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-600">
              <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                  <Link :href="`/invoices/${inv.id}`" class="text-indigo-600 hover:underline">{{ inv.invoice_no }}</Link>
                </td>
                <td class="px-6 py-4 text-indigo-700 font-medium">{{ inv.customer_name }}</td>
                <td class="px-6 py-4 font-mono">{{ inv.quantity }} pcs</td>
                <td class="px-6 py-4 font-mono">${{ parseFloat(inv.unit_price).toFixed(2) }}</td>
                <td class="px-6 py-4 font-mono font-semibold text-slate-800">${{ parseFloat(inv.total_price).toFixed(2) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ inv.issue_date }}</td>
                <td class="px-6 py-4">
                  <span :class="{
                    'bg-red-100 text-red-800': inv.status === 'unpaid',
                    'bg-amber-100 text-amber-800': inv.status === 'partial',
                    'bg-green-100 text-green-800': inv.status === 'paid'
                  }" class="px-2.5 py-1 rounded text-xs font-bold uppercase tracking-wider">
                    {{ inv.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="invoices.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-400">No linked transactional invoices matching parameters.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="invoices.links.length > 3" class="px-6 py-4 bg-gray-50 border-t flex justify-between items-center">
          <div class="text-gray-500">
            Showing entries {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }}
          </div>
          
          <div class="flex space-x-1">
            <template v-for="(link, key) in invoices.links" :key="key">
              
              <Link 
                v-if="link.url" 
                :href="link.url" 
                class="px-2.5 py-1 rounded transition-colors border"
                :class="{
                  'bg-indigo-600 text-white font-bold border-indigo-600': link.active, 
                  'bg-white text-gray-700 hover:bg-gray-100': !link.active
                }"
              >
                <span v-html="link.label"></span>
              </Link>

              <span 
                v-else 
                class="px-2.5 py-1 rounded border text-gray-300 pointer-events-none bg-gray-50"
              >
                <span v-html="link.label"></span>
              </span>

            </template>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { reactive, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({ product: Object, invoices: Object, filters: Object });

const searchFields = reactive({
  invoice_no: props.filters.invoice_no || '',
  customer_name: props.filters.customer_name || '',
  status: props.filters.status || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || ''
});

const hasActiveFilters = computed(() => Object.values(searchFields).some(value => value !== ''));

let timeoutTimer = null;
const debouncedSearch = () => {
  clearTimeout(timeoutTimer);
  timeoutTimer = setTimeout(() => { executeSearch(); }, 400);
};

const executeSearch = () => {
  router.get(`/products/${props.product.id}`, {
    invoice_no: searchFields.invoice_no,
    customer_name: searchFields.customer_name,
    status: searchFields.status,
    start_date: searchFields.start_date,
    end_date: searchFields.end_date
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  searchFields.invoice_no = ''; searchFields.customer_name = ''; searchFields.status = ''; searchFields.start_date = ''; searchFields.end_date = '';
  executeSearch();
};
</script>