<template>
  <AuthenticatedLayout>
    <div class="bg-white p-6 rounded-lg shadow-md">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Billing & Invoices Registry</h2>
        <div class="flex items-center gap-3">
          <button @click="openCreateModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-xs font-semibold shadow-sm transition-all duration-200">
            + Create Invoice
          </button>
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
      </div>
      
      <div v-if="$page.props.errors.error && !showPaymentModal" class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
        {{ $page.props.errors.error }}
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <input v-model="query.invoice_no" placeholder="Invoice No..." class="border rounded p-2 text-xs focus:ring-1 focus:ring-indigo-500 outline-none" @input="debouncedFilter"/>
        <input v-model="query.customer_name" placeholder="Customer..." class="border rounded p-2 text-xs focus:ring-1 focus:ring-indigo-500 outline-none" @input="debouncedFilter"/>
        <input v-model="query.goods_name" placeholder="Product contains..." class="border rounded p-2 text-xs focus:ring-1 focus:ring-indigo-500 outline-none" @input="debouncedFilter"/>
        <select v-model="query.status" class="border rounded p-2 text-xs bg-white focus:ring-1 focus:ring-indigo-500 outline-none" @change="filterInvoices">
          <option value="">All Statuses</option>
          <option value="unpaid">Unpaid</option>
          <option value="partial">Partial</option>
          <option value="paid">Paid</option>
          <option value="overdue">Overdue</option>
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
              <th class="px-4 py-3">Products Listed</th>
              <th class="px-4 py-3 font-medium">Total Price</th>
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
              <td class="px-4 py-3 text-indigo-600 max-w-xs truncate">
                <span v-for="(item, idx) in inv.items" :key="item.id">
                  {{ item.goods?.name }} (x{{ item.quantity }}){{ idx < inv.items.length - 1 ? ', ' : '' }}
                </span>
              </td>
              <td class="px-4 py-3 font-mono font-semibold text-gray-900">${{ parseFloat(inv.total_price).toFixed(2) }}</td>
              <td class="px-4 py-3 font-mono text-emerald-600">${{ parseFloat(inv.paid_amount).toFixed(2) }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="statusColors(inv.status)" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                  {{ inv.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <button v-if="inv.status !== 'paid'" @click="openPaymentModal(inv)" class="bg-emerald-600 text-white px-3 py-1 rounded hover:bg-emerald-700 transition-colors font-medium text-[11px]">
                  Pay
                </button>
                <span v-else class="text-gray-400 italic text-[11px] select-none">Settled</span>
              </td>
            </tr>
            <tr v-if="invoices.data.length === 0">
              <td colspan="9" class="px-4 py-12 text-center text-gray-400 text-sm">No billing data found matching specific filters.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="invoices.links.length > 3" class="mt-4 flex justify-between items-center text-xs">
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

    <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Create Consolidated Invoice</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Target Customer</label>
            <select v-model="invoiceForm.customer_id" class="w-full border rounded p-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
              <option value="" disabled>-- Select a Client --</option>
              <option v-for="cust in customers" :key="cust.id" :value="cust.id">{{ cust.name }}</option>
            </select>
          </div>
          <div>
            <div class="flex justify-between items-center mb-2">
              <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">Purchase Items Collection</label>
              <button @click="addItemRow" type="button" class="text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-3 py-1 rounded font-bold transition-colors">
                + Add Item Row
              </button>
            </div>
            <div class="space-y-2 max-h-[40vh] overflow-y-auto border p-3 rounded-lg bg-slate-50/50">
              <div v-for="(item, index) in invoiceForm.items" :key="index" class="flex gap-2 items-center bg-white border p-2 rounded shadow-sm">
                <div class="flex-1">
                  <select v-model="item.goods_id" @change="onGoodsChange(index)" class="w-full border rounded p-1.5 text-xs bg-white focus:ring-1 focus:ring-indigo-500">
                    <option value="" disabled>-- Select Product --</option>
                    <option v-for="g in availableGoodsForId(index)" :key="g.id" :value="g.id">
                      {{ g.name }} (${{ parseFloat(g.price).toFixed(2) }}) | Stock: {{ g.stock }}
                    </option>
                  </select>
                </div>
                <div class="w-20 text-center text-xs font-mono text-gray-500">
                  ${{ item.price ? parseFloat(item.price).toFixed(2) : '0.00' }}
                </div>
                <div class="w-24">
                  <input v-model.number="item.quantity" type="number" min="1" :max="item.max_stock" placeholder="Qty" class="w-full border rounded p-1.5 text-xs font-mono text-center focus:ring-1 focus:ring-indigo-500" />
                </div>
                <div class="w-24 text-right font-mono font-bold text-gray-700 text-xs px-2">
                  ${{ (item.price * (item.quantity || 0)).toFixed(2) }}
                </div>
                <button @click="removeItemRow(index)" type="button" class="text-rose-600 hover:bg-rose-50 p-1.5 rounded transition-colors text-xs font-bold">
                  Remove
                </button>
              </div>
              <p v-if="invoiceForm.items.length === 0" class="text-center text-gray-400 text-xs py-6 italic">No products allocated. Click "+ Add Item Row" above.</p>
            </div>
          </div>
          <div class="bg-slate-900 text-white p-4 rounded-xl flex justify-between items-center">
            <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Sub Total Aggregate Value</span>
            <span class="text-2xl font-black font-mono text-emerald-400">${{ computedTotal.toFixed(2) }}</span>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <button @click="showCreateModal = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">Cancel</button>
          <button @click="submitCreateInvoice" :disabled="invoiceForm.items.length === 0" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium disabled:opacity-40 shadow-md">
            Confirm & Save Invoice
          </button>
        </div>
      </div>
    </div>

    <div v-if="showPaymentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full animate-fade-in">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold text-gray-800">Record Payment</h3>
          <span class="text-xs font-mono font-bold bg-slate-100 text-slate-700 px-2 py-1 rounded">
            {{ selectedInvoice?.invoice_no }}
          </span>
        </div>

        <div v-if="$page.props.errors.error" class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-xs">
          {{ $page.props.errors.error }}
        </div>
        
        <div class="grid grid-cols-2 gap-3 mb-4 bg-slate-50 p-3 rounded-lg text-xs">
          <div>
            <span class="text-gray-400 block">Total Price</span>
            <span class="font-mono font-bold text-gray-900">${{ parseFloat(selectedInvoice?.total_price).toFixed(2) }}</span>
          </div>
          <div>
            <span class="text-gray-400 block">Remaining Due</span>
            <span class="font-mono font-bold text-rose-600">${{ maxPayableAmount.toFixed(2) }}</span>
          </div>
        </div>

        <div class="mb-5">
          <label class="block text-xs font-semibold text-gray-600 mb-1">Payment Amount ($)</label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-gray-400 font-medium text-sm">$</span>
            
            <input 
              v-model="paymentForm.paid_amount" 
              type="text" 
              placeholder="0.00" 
              class="w-full border rounded pl-7 pr-16 py-2 text-sm font-mono focus:ring-2 focus:ring-emerald-500 outline-none"
              @input="validatePaymentInput"
              @keyup.enter="submitPayment"
            />
            
            <button 
              @click="setFullPayment" 
              type="button" 
              class="absolute right-2 top-2 text-[10px] bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold px-2 py-1 rounded"
            >
              Pay Full
            </button>
          </div>
          <p v-if="paymentValidationError" class="text-rose-600 text-[11px] mt-1 font-medium">{{ paymentValidationError }}</p>
        </div>

        <div class="flex justify-end gap-2">
          <button @click="closePaymentModal" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-xs font-medium">
            Cancel
          </button>
          <button 
            @click="submitPayment" 
            :disabled="!isPaymentValid" 
            class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-xs font-medium shadow-md disabled:opacity-40"
          >
            Submit Payment
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({ invoices: Object, filters: Object, customers: Array, goods: Array });

// 搜索栏筛选状态
const query = ref({
  invoice_no: props.filters.invoice_no || '',
  customer_name: props.filters.customer_name || '',
  goods_name: props.filters.goods_name || '',
  status: props.filters.status || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
});

const hasFilters = computed(() => Object.values(query.value).some(val => val !== ''));

let debounceTimer = null;
const debouncedFilter = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => { filterInvoices(); }, 400);
};

const filterInvoices = () => {
  router.get('/invoices', query.value, { preserveState: true, replace: true });
};

const clearFilters = () => {
  query.value = { invoice_no: '', customer_name: '', goods_name: '', status: '', start_date: '', end_date: '' };
  filterInvoices();
};

const statusColors = (status) => {
  if (status === 'paid') return 'bg-emerald-100 text-emerald-800';
  if (status === 'partial') return 'bg-amber-100 text-amber-800';
  return 'bg-rose-100 text-rose-800';
};

// ---------------------------------
// 新建发票的多商品控制
// ---------------------------------
const showCreateModal = ref(false);
const invoiceForm = ref({ customer_id: '', items: [] });

const openCreateModal = () => {
  invoiceForm.value = { customer_id: '', items: [] };
  addItemRow();
  showCreateModal.value = true;
};

const addItemRow = () => {
  invoiceForm.value.items.push({ goods_id: '', quantity: 1, price: 0.00, max_stock: 0 });
};

const removeItemRow = (index) => {
  invoiceForm.value.items.splice(index, 1);
};

const availableGoodsForId = (currentIndex) => {
  const otherSelectedIds = invoiceForm.value.items
    .filter((item, idx) => idx !== currentIndex && item.goods_id !== '')
    .map(item => item.goods_id);
  return props.goods.filter(g => !otherSelectedIds.includes(g.id));
};

const onGoodsChange = (index) => {
  const selectedId = invoiceForm.value.items[index].goods_id;
  const match = props.goods.find(g => g.id === selectedId);
  if (match) {
    invoiceForm.value.items[index].price = parseFloat(match.price);
    invoiceForm.value.items[index].max_stock = parseInt(match.stock);
    if (invoiceForm.value.items[index].quantity > match.stock) {
      invoiceForm.value.items[index].quantity = match.stock;
    }
  }
};

const computedTotal = computed(() => {
  return invoiceForm.value.items.reduce((sum, item) => {
    return sum + (item.price * (item.quantity || 0));
  }, 0);
});

const submitCreateInvoice = () => {
  if (!invoiceForm.value.customer_id) {
    alert("Please assign a target customer.");
    return;
  }
  for (let i = 0; i < invoiceForm.value.items.length; i++) {
    const item = invoiceForm.value.items[i];
    if (!item.goods_id) { alert(`Line ${i + 1} has an unselected product row.`); return; }
    if (item.quantity <= 0) { alert(`Line ${i + 1} quantity must be higher than 0.`); return; }
    if (item.quantity > item.max_stock) { alert(`Line ${i + 1} quantity exceeds warehouse stock bounds (Max: ${item.max_stock}).`); return; }
  }
  router.post('/invoices', invoiceForm.value, {
    onSuccess: () => { showCreateModal.value = false; }
  });
};

// ---------------------------------
// 💎 强控收账 Modal 控制中心
// ---------------------------------
const showPaymentModal = ref(false);
const selectedInvoice = ref(null);
const paymentForm = ref({ paid_amount: '' });
const paymentValidationError = ref('');

// 计算当前发票剩余应付的最大金额
const maxPayableAmount = computed(() => {
  if (!selectedInvoice.value) return 0;
  return parseFloat((selectedInvoice.value.total_price - selectedInvoice.value.paid_amount).toFixed(2));
});

// 前端实时交互按钮可用性校验器
const isPaymentValid = computed(() => {
  const amt = parseFloat(paymentForm.value.paid_amount);
  return !isNaN(amt) && amt > 0 && amt <= maxPayableAmount.value;
});

const openPaymentModal = (invoice) => {
  selectedInvoice.value = invoice;
  paymentForm.value.paid_amount = ''; 
  paymentValidationError.value = '';
  showPaymentModal.value = true;
};

const closePaymentModal = () => {
  showPaymentModal.value = false;
  selectedInvoice.value = null;
};

// 一键拉满全额
const setFullPayment = () => {
  paymentForm.value.paid_amount = maxPayableAmount.value.toString();
  paymentValidationError.value = '';
};

/**
 * 🔒 输入层实时强力拦截核心逻辑
 * 拒绝负数、拒绝多于2位的小数、拦截非数字字符
 */
const validatePaymentInput = (event) => {
  paymentValidationError.value = '';
  let value = event.target.value;

  // 1. 清理掉所有不是“数字”或“小数点”的字符（完美拦截负号 - 、加号 +、科学计数 e）
  value = value.replace(/[^\d.]/g, '');

  // 2. 如果错输了多个小数点，只保留第一个
  value = value.replace(/\.{2,}/g, '.');
  value = value.replace('.', '$#$').replace(/\./g, '').replace('$#$', '.');

  // 3. 🛡️ 核心强控：如果包含小数点，截断限制最多保留2位有效小数
  if (value.indexOf('.') > -1) {
    const parts = value.split('.');
    if (parts[1].length > 2) {
      parts[1] = parts[1].substring(0, 2);
      value = parts.join('.');
    }
  }

  // 4. 将格式化清洗后的纯净字符串安全回填给模型层与 DOM 节点
  paymentForm.value.paid_amount = value;
  event.target.value = value;

  // 5. 动态溢出边界检测提示
  const amt = parseFloat(value);
  if (!isNaN(amt) && amt > maxPayableAmount.value) {
    paymentValidationError.value = `Amount cannot exceed the remaining balance ($${maxPayableAmount.value.toFixed(2)})`;
  }
};

// 提交收账表单到后端
const submitPayment = () => {
  paymentValidationError.value = '';
  const amt = parseFloat(paymentForm.value.paid_amount);

  if (isNaN(amt) || amt <= 0) {
    paymentValidationError.value = "Please enter a valid amount.";
    return;
  }

  if (amt > maxPayableAmount.value) {
    paymentValidationError.value = `Amount cannot exceed the remaining balance ($${maxPayableAmount.value.toFixed(2)})`;
    return;
  }

  router.post(`/invoices/${selectedInvoice.value.id}/pay`, { 
    paid_amount: amt 
  }, {
    preserveState: true,
    onSuccess: () => {
      closePaymentModal();
    }
  });
};
</script>