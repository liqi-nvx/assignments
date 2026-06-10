<template>
  <AuthenticatedLayout>
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Billing & Invoices Registry</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <input v-model="query.invoice_no" placeholder="Invoice No..." class="border rounded p-2 text-xs" @input="filterInvoices"/>
        <input v-model="query.customer_name" placeholder="Customer..." class="border rounded p-2 text-xs" @input="filterInvoices"/>
        <input v-model="query.goods_name" placeholder="Product..." class="border rounded p-2 text-xs" @input="filterInvoices"/>
        <select v-model="query.status" class="border rounded p-2 text-xs" @change="filterInvoices">
          <option value="">All Statuses</option>
          <option value="unpaid">Unpaid</option>
          <option value="partial">Partial</option>
          <option value="paid">Paid</option>
        </select>
        <input v-model="query.start_date" type="date" class="border rounded p-2 text-xs" @change="filterInvoices"/>
        <input v-model="query.end_date" type="date" class="border rounded p-2 text-xs" @change="filterInvoices"/>
      </div>

      <div class="overflow-x-auto border rounded-xl">
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
            <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono font-bold text-gray-900">{{ inv.invoice_no }}</td>
              <td class="px-4 py-3">{{ inv.issue_date }}</td>
              <td class="px-4 py-3 text-rose-600">{{ inv.due_date }}</td>
              <td class="px-4 py-3 font-medium">{{ inv.customer?.name }}</td>
              <td class="px-4 py-3">{{ inv.goods?.name }}</td>
              <td class="px-4 py-3 text-center">{{ inv.quantity }}</td>
              <td class="px-4 py-3">${{ inv.unit_price }}</td>
              <td class="px-4 py-3 font-semibold text-gray-900">${{ inv.total_price }}</td>
              <td class="px-4 py-3 text-emerald-600">${{ inv.paid_amount }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="statusColors(inv.status)" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">
                  {{ inv.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <button v-if="inv.status !== 'paid'" @click="triggerPayment(inv)" class="bg-emerald-600 text-white px-2.5 py-1 rounded hover:bg-emerald-700 font-medium">Pay</button>
                <span v-else class="text-gray-400 italic">Settled</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({ invoices: Object, filters: Object });

const query = ref({
  invoice_no: props.filters.invoice_no || '',
  customer_name: props.filters.customer_name || '',
  goods_name: props.filters.goods_name || '',
  status: props.filters.status || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

const filterInvoices = () => {
  router.get('/invoices', query.value, { preserveState: true, replace: true });
};

const statusColors = (status) => {
  if (status === 'paid') return 'bg-emerald-100 text-emerald-800';
  if (status === 'partial') return 'bg-amber-100 text-amber-800';
  return 'bg-rose-100 text-rose-800';
};

const triggerPayment = (invoice) => {
  const maxPayable = invoice.total_price - invoice.paid_amount;
  const amt = prompt(`Enter payment amount (Max acceptable: $${maxPayable.toFixed(2)}):`);
  if (amt) {
    const parsed = parseFloat(amt);
    if(parsed <= 0 || parsed > maxPayable || isNaN(parsed)) {
      alert("Invalid payment boundary constraint violated.");
      return;
    }
    router.post(`/invoices/${invoice.id}/pay`, { paid_amount: parsed });
  }
};
</script>