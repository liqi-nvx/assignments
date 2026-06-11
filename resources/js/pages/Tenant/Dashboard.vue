<template>
  <AuthenticatedLayout>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Overview</h1>

      <!-- 统计卡片网格 -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- 卡片 1: Current Month Revenue -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Current Month Revenue</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">${{ formatNumber(stats.current_month_revenue) }}</h3>
          </div>
          <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600">
            <span class="text-2xl">📈</span>
          </div>
        </div>

        <!-- 卡片 2: Outstanding Invoices -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Outstanding Invoices</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">${{ formatNumber(stats.outstanding_invoices_total) }}</h3>
          </div>
          <div class="p-3 rounded-lg bg-amber-50 text-amber-600">
            <span class="text-2xl">⏳</span>
          </div>
        </div>

        <!-- 卡片 3: Paid Invoices -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Paid Invoices Total</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">${{ formatNumber(stats.paid_invoices_total) }}</h3>
          </div>
          <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
            <span class="text-2xl">✅</span>
          </div>
        </div>

        <!-- 卡片 4: Overdue Invoices -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Overdue Invoices</p>
            <h3 class="text-2xl font-bold text-rose-600 mt-1">${{ formatNumber(stats.overdue_invoices_total) }}</h3>
          </div>
          <div class="p-3 rounded-lg bg-rose-50 text-rose-600">
            <span class="text-2xl">🚨</span>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';

defineProps({
  stats: Object
});

// 格式化金额，保留两位小数
const formatNumber = (num) => {
  return Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>