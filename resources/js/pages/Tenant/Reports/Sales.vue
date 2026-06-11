<template>
  <AuthenticatedLayout>
    <div class="bg-white p-6 rounded-lg shadow-md max-w-7xl mx-auto">
      
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Operational Sales Report</h2>
        
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

      <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6 bg-slate-50 p-4 rounded-xl border">
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Invoice No</label>
          <input 
            v-model="searchFilters.invoice_no" 
            placeholder="Search Invoice No..." 
            class="border rounded p-2 text-xs w-full bg-white focus:ring-1 focus:ring-indigo-500 outline-none" 
            @input="debouncedFilter"
          />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Customer Name</label>
          <input 
            v-model="searchFilters.customer_name" 
            placeholder="Search Customer..." 
            class="border rounded p-2 text-xs w-full bg-white focus:ring-1 focus:ring-indigo-500 outline-none" 
            @input="debouncedFilter"
          />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Start Date (Invoice Date)</label>
          <input 
            v-model="searchFilters.start_date" 
            type="date" 
            class="border rounded p-2 text-xs w-full bg-white focus:ring-1 focus:ring-indigo-500 outline-none" 
            @change="submitSearch"
          />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">End Date (Invoice Date)</label>
          <input 
            v-model="searchFilters.end_date" 
            type="date" 
            class="border rounded p-2 text-xs w-full bg-white focus:ring-1 focus:ring-indigo-500 outline-none" 
            @change="submitSearch"
          />
        </div>
      </div>

      <div class="overflow-x-auto border rounded-xl shadow-sm">
        <table class="min-w-full text-xs text-left divide-y divide-gray-200">
          <thead class="bg-slate-900 text-white uppercase font-medium">
            <tr>
              <th class="px-6 py-4">Invoice No</th>
              <th class="px-6 py-4">Invoice Date</th>
              <th class="px-6 py-4">Customer Name</th>
              <th class="px-6 py-4">Total Price</th>
              <th class="px-6 py-4">Paid Amount</th>
              <th class="px-6 py-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-gray-600 bg-white">
            <tr v-for="item in report.paginated_items.data" :key="item.id" class="hover:bg-slate-50/80 transition-colors">
              <td class="px-6 py-4 font-mono font-bold">
                <Link :href="`/invoices/${item.id}`" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                  {{ item.invoice_no }}
                </Link>
              </td>
              
              <td class="px-6 py-4 whitespace-nowrap">{{ item.issue_date }}</td>
              
              <td class="px-6 py-4 font-medium text-slate-700">{{ item.customer?.name || 'N/A' }}</td>
              
              <td class="px-6 py-4 font-mono font-semibold text-gray-900">
                ${{ parseFloat(item.total_price).toFixed(2) }}
              </td>
              
              <td class="px-6 py-4 font-mono font-semibold text-emerald-600">
                ${{ parseFloat(item.paid_amount).toFixed(2) }}
              </td>
              
              <td class="px-6 py-4">
                <span :class="statusColors(item.status)" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                  {{ item.status }}
                </span>
              </td>
            </tr>
            
            <tr v-if="report.paginated_items.data.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-400">No records found.</td>
            </tr>
          </tbody>
          
          <tfoot class="bg-slate-50 font-bold text-slate-900 border-t-2 border-slate-200">
            <tr>
              <td colspan="3" class="px-6 py-4 text-right text-sm tracking-wide text-slate-500 uppercase">Financial Metrics Summary:</td>
              <td class="px-6 py-4 text-indigo-600 font-mono text-sm">${{ report.summary.total_price_sum.toFixed(2) }}</td>
              <td class="px-6 py-4 text-emerald-600 font-mono text-sm">${{ report.summary.paid_amount_sum.toFixed(2) }}</td>
              <td class="px-6 py-4 text-rose-600 font-mono text-sm">
                <span class="text-[10px] text-gray-400 block font-normal normal-case tracking-normal">Outstanding Due</span>
                ${{ report.summary.outstanding_amount_sum.toFixed(2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="mt-4 flex justify-between items-center text-xs" v-if="report.paginated_items.links && report.paginated_items.links.length > 3">
        <div class="text-gray-500">Showing entries {{ report.paginated_items.from }} to {{ report.paginated_items.to }} of {{ report.paginated_items.total }}</div>
        <div class="flex space-x-1">
          <Link 
            v-for="(link, index) in report.paginated_items.links" 
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
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({ report: Object, filters: Object });

// 结构化绑定筛选数据
const searchFilters = ref({
  invoice_no: props.filters.invoice_no || '',
  customer_name: props.filters.customer_name || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

// 精准研判过滤器当前是否有值
const hasFilters = computed(() => {
  return Object.values(searchFilters.value).some(value => value !== '');
});

// 400ms 动态防抖过滤器
let debounceTimer = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    submitSearch();
  }, 400);
};

// 触发 Inertia 带参数拉取
const submitSearch = () => {
  router.get('/sales-report', searchFilters.value, { preserveState: true, replace: true });
};

// 一键重置清空过滤器
const resetFilters = () => {
  searchFilters.value = {
    invoice_no: '',
    customer_name: '',
    start_date: '',
    end_date: '',
  };
  submitSearch();
};

// 状态色块分发小工具
const statusColors = (status) => {
  if (status === 'paid') return 'bg-emerald-100 text-emerald-800';
  if (status === 'partial') return 'bg-amber-100 text-amber-800';
  return 'bg-rose-100 text-rose-800';
};
</script>