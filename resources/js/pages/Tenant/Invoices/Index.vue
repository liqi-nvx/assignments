<template>
  <AuthenticatedLayout>
    <div class="bg-white p-6 rounded-lg shadow-md">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Billing & Invoices Registry</h2>
        
        <button 
          @click="clearFilters" 
          :disabled="!hasFilters"
          class="px-4 py-2 text-xs font-semibold rounded-lg border transition-all duration-200"
          :class="{
            'bg-rose-50 text-rose-600 border-rose-200 hover:bg-rose-100 shadow-sm cursor-pointer': hasFilters,
            'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed opacity-60': !hasFilters
          }"
        >
          Clear Filters
        </button>
      </div>
      
      <div v-if="$page.props.errors.error" class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
        {{ $page.props.errors.error }}
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <input v-model="query.invoice_no" placeholder="Invoice No..." class="border rounded p-2 text-xs focus:ring-1 focus:ring-indigo-500 outline-none" @input="debouncedFilter"/>
        <input v-model="query.customer_name" placeholder="Customer..." class="border rounded p-2 text-xs focus:ring-1 focus:ring-indigo-500 outline-none" @input="debouncedFilter"/>
        <input v-model="query.goods_name" placeholder="Product..." class="border rounded p-2 text-xs focus:ring-1 focus:ring-indigo-500 outline-none" @input="debouncedFilter"/>
        <select v-model="query.status" class="border rounded p-2 text-xs bg-white focus:ring-1 focus:ring-indigo-500 outline-none" @change="filterInvoices">
          <option value="">All Statuses</option>
          <option value="unpaid">Unpaid</option>
          <option value="partial">Partial</option>
          <option value="paid">Paid</option>
        </select>
        <input v-model="query.start_date" type="date" class="border rounded p-2 text-xs bg-white focus:ring-1 focus:ring-indigo-500 outline-none" @change="filterInvoices"/>
        <input v-model="query.end_date" type="date" class="border rounded p-2 text-xs bg-white focus:ring-1 focus:ring-indigo-500 outline-none" @change="filterInvoices"/>
      </div>

      <div class="overflow-x-auto border rounded-xl shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
          <thead class="bg-slate-50 text-slate-700 uppercase font-semibold">
            <tr>
              <th class="px-4 py-3">Inv No</th>
              <th class="px-4 py-3">Issue Date</th>
              <th class="px-4 py-3">Due Date</th>
              <th class="px-4 py-3">Customer</th>
              <th class="px-4 py-3">Product</th>
              <th class="px-4 py-3 text-center">Qty</th>
              <th class="px-4 py-3">Unit Price</th>
              <th class="px-4 py-3 font-medium">Total Cost</th>
              <th class="px-4 py-3 font-medium">Paid Amount</th>
              <th class="px-4 py-3 text-center">Status</th>
              <th class="px-4 py-3 text-right">Settlement</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-gray-600">
            <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-4 py-3 font-mono font-bold">
                <Link :href="`/invoices/${inv.id}`" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                  {{ inv.invoice_no }}
                </Link>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">{{ inv.issue_date }}</td>
              <td class="px-4 py-3 text-rose-600 whitespace-nowrap">{{ inv.due_date }}</td>
              <td class="px-4 py-3 font-medium text-slate-700">{{ inv.customer?.name }}</td>
              <td class="px-4 py-3 text-indigo-600">{{ inv.goods?.name }}</td>
              <td class="px-4 py-3 text-center font-mono">{{ inv.quantity }}</td>
              <td class="px-4 py-3 font-mono">${{ parseFloat(inv.unit_price).toFixed(2) }}</td>
              <td class="px-4 py-3 font-mono font-semibold text-gray-900">${{ parseFloat(inv.total_price).toFixed(2) }}</td>
              <td class="px-4 py-3 font-mono text-emerald-600">${{ parseFloat(inv.paid_amount).toFixed(2) }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="statusColors(inv.status)" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                  {{ inv.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <button v-if="inv.status !== 'paid'" @click="triggerPayment(inv)" class="bg-emerald-600 text-white px-3 py-1 rounded hover:bg-emerald-700 transition-colors font-medium text-[11px]">
                  Pay
                </button>
                <span v-else class="text-gray-400 italic text-[11px] select-none">Settled</span>
              </td>
            </tr>
            <tr v-if="invoices.data.length === 0">
              <td colspan="11" class="px-4 py-12 text-center text-gray-400 text-sm">No billing data found matching specific filters.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="invoices.links.length > 3" class="mt-4 flex justify-between items-center text-xs">
        <div class="text-gray-500">
          Showing entries {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }}
        </div>
        <div class="flex space-x-1">
          <Component
            :is="link.url ? 'Link' : 'span'"
            v-for="(link, key) in invoices.links"
            :key="key"
            :href="link.url"
            class="px-2.5 py-1 rounded transition-colors"
            :class="{
              'bg-indigo-600 text-white font-bold': link.active,
              'bg-white text-gray-700 hover:bg-gray-100 border': link.url && !link.active,
              'text-gray-300 pointer-events-none border bg-gray-50': !link.url
            }"
          >
            <span v-html="link.label"></span>
          </Component>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({ invoices: Object, filters: Object });

const query = ref({
  invoice_no: props.filters.invoice_no || '',
  customer_name: props.filters.customer_name || '',
  goods_name: props.filters.goods_name || '',
  status: props.filters.status || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

// 计算属性：监听当前是否有任何一个筛选框被填入了内容
const hasFilters = computed(() => {
  return Object.values(query.value).some(value => value !== '');
});

let debounceTimer = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    filterInvoices();
  }, 400);
};

const filterInvoices = () => {
  router.get('/invoices', query.value, { preserveState: true, replace: true });
};

// 执行一键重置清空
const clearFilters = () => {
  query.value = {
    invoice_no: '',
    customer_name: '',
    goods_name: '',
    status: '',
    start_date: '',
    end_date: '',
  };
  filterInvoices();
};

const statusColors = (status) => {
  if (status === 'paid') return 'bg-emerald-100 text-emerald-800';
  if (status === 'partial') return 'bg-amber-100 text-amber-800';
  return 'bg-rose-100 text-rose-800';
};

const triggerPayment = (invoice) => {
  const maxPayable = parseFloat((invoice.total_price - invoice.paid_amount).toFixed(2));
  
  const amt = prompt(`Enter payment settlement amount:\n(Remaining debt max limit acceptable: $${maxPayable.toFixed(2)})`);
  if (amt === null) return;

  const numericRegex = /^\d+(\.\d{1,2})?$/;
  if (!numericRegex.test(amt)) {
    alert("Invalid character or format input! Only positive numeric amounts with up to 2 decimal places are authorized.");
    return;
  }

  const parsed = parseFloat(amt);

  if (isNaN(parsed) || parsed <= 0) {
    alert("Payment value must be greater than zero.");
    return;
  }

  if (parsed > maxPayable) {
    alert(`Boundary Error: The input amount ($${parsed.toFixed(2)}) exceeds the maximum remaining balance of this invoice ($${maxPayable.toFixed(2)}).`);
    return;
  }

  router.post(`/invoices/${invoice.id}/pay`, { paid_amount: parsed });
};
</script>