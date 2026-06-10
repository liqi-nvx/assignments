<template>
  <AuthenticatedLayout>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">财务收款记录 (Payments)</h1>
        </div>

        <div class="bg-white p-4 rounded-lg shadow mb-6 grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">交易单号</label>
            <input 
            v-model="searchFilters.trans_no" 
            type="text" 
            placeholder="搜索交易单号..." 
            class="border rounded px-3 py-1.5 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">金额 (等于/大于)</label>
            <input 
            v-model="searchFilters.paid_amount" 
            type="number" 
            step="0.01"
            placeholder="输入金额..." 
            class="border rounded px-3 py-1.5 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">开始日期</label>
            <input 
            v-model="searchFilters.start_date" 
            type="date" 
            class="border rounded px-3 py-1.5 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">结束日期</label>
            <input 
            v-model="searchFilters.end_date" 
            type="date" 
            class="border rounded px-3 py-1.5 text-sm w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>
        <div class="flex space-x-2">
            <button @click="submitSearch" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 rounded shadow">
            筛选
            </button>
            <button @click="resetFilters" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium py-2 px-3 rounded">
            重置
            </button>
        </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">流水ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">交易单号 (Trans No)</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">关联发票ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">实付金额</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">付款时间</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ payment.id }}</td>
                <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900 font-semibold">{{ payment.trans_no }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 hover:underline">
                <Link :href="`/invoices/${payment.invoice_id}`">查看发票 #{{ payment.invoice_id }}</Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                ${{ parseFloat(payment.paid_amount).toFixed(2) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatDate(payment.created_at) }}</td>
            </tr>
            <tr v-if="payments.data.length === 0">
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">在当前筛选条件下没有查到任何收款流水</td>
            </tr>
            </tbody>
        </table>
        </div>

        <div class="mt-4 flex justify-between items-center" v-if="payments.links && payments.links.length > 3">
        <div class="text-sm text-gray-600">当前页共 {{ payments.data.length }} 条，总计 {{ payments.total }} 条</div>
        <div class="flex space-x-1">
            <Link 
            v-for="(link, index) in payments.links" 
            :key="index"
            :href="link.url || '#'"
            class="px-3 py-1 border rounded text-sm"
            :class="{
                'bg-blue-600 text-white': link.active, 
                'text-gray-600 hover:bg-gray-50': !link.active, 
                'opacity-50 pointer-events-none': !link.url
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
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  payments: Object,
  filters: Object
});

// 初始化过滤状态
const searchFilters = reactive({
  trans_no: props.filters.trans_no || '',
  paid_amount: props.filters.paid_amount || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || ''
});

// 点击筛选执行请求
const submitSearch = () => {
  router.get('/payments', searchFilters, { preserveState: true, replace: true });
};

// 重置过滤器
const resetFilters = () => {
  searchFilters.trans_no = '';
  searchFilters.paid_amount = '';
  searchFilters.start_date = '';
  searchFilters.end_date = '';
  router.get('/payments', {}, { replace: true });
};

// 格式化日期小工具
const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleString();
};
</script>