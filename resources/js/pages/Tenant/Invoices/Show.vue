<template>
  <AuthenticatedLayout>
    <div class="max-w-5xl mx-auto space-y-6 p-6">
      
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-3">
          <Link href="/invoices" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg border font-medium transition-colors">
            ← Back to Registry
          </Link>
          <h2 class="text-2xl font-bold text-gray-800">Invoice: {{ invoice.invoice_no }}</h2>
        </div>
        <span :class="statusColors(invoice.status)" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
          {{ invoice.status }}
        </span>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border grid grid-cols-1 md:grid-cols-3 gap-6 text-xs text-gray-600">
        <div>
          <p class="text-gray-400 uppercase font-semibold mb-1">Customer Details</p>
          <p class="font-bold text-gray-800 text-sm">{{ invoice.customer?.name }}</p>
          <p class="text-gray-400 mt-1">ID: #{{ invoice.customer_id }}</p>
        </div>
        <div>
          <p class="text-gray-400 uppercase font-semibold mb-1">Timeline Dates</p>
          <p>Issued: <span class="font-medium text-gray-800">{{ invoice.issue_date }}</span></p>
          <p class="mt-1">Due Date: <span class="font-medium text-rose-600">{{ invoice.due_date }}</span></p>
        </div>
        <div class="bg-slate-50 p-4 rounded-lg border flex flex-col justify-between">
          <div>
            <div class="flex justify-between mb-1">
              <span>Total Bill Cost:</span>
              <span class="font-mono font-bold text-gray-900">${{ parseFloat(invoice.total_price).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-emerald-600">
              <span>Total Paid:</span>
              <span class="font-mono font-bold">${{ parseFloat(invoice.paid_amount).toFixed(2) }}</span>
            </div>
          </div>
          <div class="border-t pt-2 mt-2 flex justify-between font-bold text-gray-900">
            <span>Remaining Debt:</span>
            <span class="font-mono text-rose-600">${{ (invoice.total_price - invoice.paid_amount).toFixed(2) }}</span>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider flex items-center">
          <span class="w-2 h-4 bg-indigo-600 rounded mr-2 inline-block"></span>
          Purchased Products Manifest
        </h3>
        <div class="overflow-x-auto border rounded-xl">
          <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
            <thead class="bg-slate-50 text-slate-700 uppercase font-semibold">
              <tr>
                <th class="px-5 py-2.5">Product Name</th>
                <th class="px-5 py-2.5 text-center">Quantity</th>
                <th class="px-5 py-2.5">Unit Transaction Price</th>
                <th class="px-5 py-2.5 text-right">Row Total Cost</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-600">
              <tr v-for="item in invoice.items" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                <td class="px-5 py-3 font-medium text-indigo-600">{{ item.goods?.name || 'Deleted Product Spec' }}</td>
                <td class="px-5 py-3 text-center font-mono">{{ item.quantity }} units</td>
                <td class="px-5 py-3 font-mono">${{ parseFloat(item.unit_price).toFixed(2) }}</td>
                <td class="px-5 py-3 text-right font-mono font-bold text-gray-900">${{ parseFloat(item.total_price).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider flex items-center">
          <span class="w-2 h-4 bg-emerald-600 rounded mr-2 inline-block"></span>
          Transaction Payment History Log
        </h3>
        <div class="overflow-x-auto border rounded-xl">
          <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
            <thead class="bg-slate-50 text-slate-700 uppercase font-semibold">
              <tr>
                <th class="px-5 py-3">Transaction No</th>
                <th class="px-5 py-3">Payment Date</th>
                <th class="px-5 py-3 font-medium">Paid Amount</th>
                <th class="px-5 py-3 text-center">Gateway Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-600">
              <tr v-for="pay in payments" :key="pay.id" class="hover:bg-slate-50/50 transition-colors">
                <td class="px-5 py-3 font-mono font-bold text-gray-900">{{ pay.trans_no }}</td>
                <td class="px-5 py-3 whitespace-nowrap">{{ pay.payment_date }}</td>
                <td class="px-5 py-3 font-mono font-semibold text-emerald-600">${{ parseFloat(pay.paid_amount).toFixed(2) }}</td>
                <td class="px-5 py-3 text-center">
                  <span v-if="pay.status === 1" class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full font-bold text-[10px]">SUCCESS</span>
                  <span v-else class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-bold text-[10px]">FAILED</span>
                </td>
              </tr>
              <tr v-if="payments.length === 0">
                <td colspan="4" class="px-5 py-8 text-center text-gray-400 italic">No payment history log records found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({ invoice: Object, payments: Array });

const statusColors = (status) => {
  if (status === 'paid') return 'bg-emerald-100 text-emerald-800';
  if (status === 'partial') return 'bg-amber-100 text-amber-800';
  return 'bg-rose-100 text-rose-800';
};
</script>