<template>
  <AuthenticatedLayout>
    <div class="p-6 max-w-7xl mx-auto">
      
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">财务收款记录 (Payments)</h1>
        
        <button 
          @click="resetFilters" 
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

      <div class="bg-white p-4 rounded-lg shadow border mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Trans No</label>
          <input 
            v-model="searchFilters.trans_no" 
            type="text" 
            placeholder="Search Trans No..." 
            class="border rounded px-3 py-1.5 text-xs w-full focus:outline-none focus:ring-1 focus:ring-indigo-500"
            @input="debouncedFilter"
          />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Invoice No</label>
          <input 
            v-model="searchFilters.invoice_no" 
            type="text" 
            placeholder="Search Invoice No..." 
            class="border rounded px-3 py-1.5 text-xs w-full focus:outline-none focus:ring-1 focus:ring-indigo-500"
            @input="debouncedFilter"
          />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Start Date</label>
          <input 
            v-model="searchFilters.start_date" 
            type="date" 
            class="border rounded px-3 py-1.5 text-xs w-full focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
            @change="submitSearch"
          />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">End Date</label>
          <input 
            v-model="searchFilters.end_date" 
            type="date" 
            class="border rounded px-3 py-1.5 text-xs w-full focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white"
            @change="submitSearch"
          />
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
          <thead class="bg-slate-50 text-slate-700 uppercase font-semibold">
            <tr>
              <th class="px-6 py-3">Transaction No</th>
              <th class="px-6 py-3">Invoice No</th>
              <th class="px-6 py-3">Payment Date</th>
              <th class="px-6 py-3">Paid Amount</th>
              <th class="px-6 py-3 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-gray-600">
            <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 font-mono font-bold text-gray-900">{{ payment.trans_no }}</td>
              
              <td class="px-6 py-4 font-mono font-bold">
                <Link 
                  v-if="payment.invoice_id" 
                  :href="`/invoices/${payment.invoice_id}`" 
                  class="text-indigo-600 hover:text-indigo-900 hover:underline"
                >
                  {{ payment.invoice_no }}
                </Link>
                <span v-else class="text-gray-400 italic">N/A</span>
              </td>
              
              <td class="px-6 py-4 whitespace-nowrap">{{ payment.payment_date }}</td>
              
              <td class="px-6 py-4 font-mono font-bold text-emerald-600">
                ${{ parseFloat(payment.paid_amount).toFixed(2) }}
              </td>
              
              <td class="px-6 py-4 text-center">
                <span v-if="payment.status === 1" class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                  Success
                </span>
                <span v-else class="bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                  Failed
                </span>
              </td>
            </tr>
            
            <tr v-if="payments.data.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-gray-400">No payment records match the filter logic.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex justify-between items-center text-xs" v-if="payments.links && payments.links.length > 3">
        <div class="text-gray-500">Showing entries {{ payments.from }} to {{ payments.to }} of {{ payments.total }}</div>
        <div class="flex space-x-1">
          <Link 
            v-for="(link, index) in payments.links" 
            :key="index"
            :href="link.url || '#'"
            class="px-2.5 py-1 border rounded transition-colors"
            :class="{
              'bg-indigo-600 text-white font-bold border-indigo-600': link.active, 
              'bg-white text-gray-700 hover:bg-gray-100': !link.active, 
              'text-gray-300 border-gray-100 bg-gray-50 pointer-events-none': !link.url
            }"
          >
            <span v-html="link.label"></span>
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  payments: Object,
  filters: Object
});

// 使用响应式引用进行绑定
const searchFilters = ref({
  trans_no: props.filters.trans_no || '',
  invoice_no: props.filters.invoice_no || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || ''
});

// 动态追踪是否有任意筛选正在发生作用
const hasFilters = computed(() => {
  return Object.values(searchFilters.value).some(val => val !== '');
});

// 防抖节流引擎
let debounceTimer = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    submitSearch();
  }, 400);
};

// 提交过滤请求
const submitSearch = () => {
  router.get('/payments', searchFilters.value, { preserveState: true, replace: true });
};

// 一键清空并重装载
const resetFilters = () => {
  searchFilters.value = {
    trans_no: '',
    invoice_no: '',
    start_date: '',
    end_date: ''
  };
  submitSearch();
};
</script>