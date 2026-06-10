<template>
  <AuthenticatedLayout>
    <div class="bg-white p-6 rounded-lg shadow-md">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Operational Sales Report</h2>
        <div class="flex items-center gap-2">
          <input v-model="dates.start_date" type="date" class="border rounded p-2 text-sm" @change="reloadReport"/>
          <span class="text-gray-400">to</span>
          <input v-model="dates.end_date" type="date" class="border rounded p-2 text-sm" @change="reloadReport"/>
        </div>
      </div>

      <div class="overflow-x-auto border rounded-xl shadow-inner">
        <table class="min-w-full text-sm text-left">
          <thead class="bg-slate-900 text-white uppercase text-xs">
            <tr>
              <th class="px-6 py-4">Invoice ID</th>
              <th class="px-6 py-4">Settlement Date</th>
              <th class="px-6 py-4">Client Destination</th>
              <th class="px-6 py-4">Gross Total</th>
              <th class="px-6 py-4">Liquidated Paid</th>
              <th class="px-6 py-4">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 text-gray-700">
            <tr v-for="item in report.items" :key="item.id" class="hover:bg-slate-50">
              <td class="px-6 py-4 font-mono font-semibold">{{ item.invoice_no }}</td>
              <td class="px-6 py-4">{{ item.issue_date }}</td>
              <td class="px-6 py-4 font-medium">{{ item.customer?.name }}</td>
              <td class="px-6 py-4 font-semibold text-gray-900">${{ item.total_price }}</td>
              <td class="px-6 py-4 text-emerald-600 font-semibold">${{ item.paid_amount }}</td>
              <td class="px-6 py-4 uppercase text-xs font-bold">{{ item.status }}</td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-100 font-bold text-slate-900 border-t-2 border-slate-300">
            <tr>
              <td colspan="3" class="px-6 py-4 text-right text-base tracking-wide">Financial Metrics Summary:</td>
              <td class="px-6 py-4 text-indigo-600 text-base">${{ report.summary.total_price_sum.toFixed(2) }}</td>
              <td class="px-6 py-4 text-emerald-600 text-base">${{ report.summary.paid_amount_sum.toFixed(2) }}</td>
              <td class="px-6 py-4 text-rose-600 text-base">
                <span class="text-xs text-gray-400 block font-normal font-sans">Outstanding Due</span>
                ${{ report.summary.outstanding_amount_sum.toFixed(2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({ report: Object, filters: Object });

const dates = ref({
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

const reloadReport = () => {
  router.get('/sales-report', dates.value, { preserveState: true });
};
</script>