<template>
  <AuthenticatedLayout>
    <div class="bg-white p-6 rounded-lg shadow-md">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Stock & Product Management</h2>
        
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
          <input v-model="filters.search" @input="doSearch" type="text" placeholder="Search product name..." class="border rounded px-4 py-2 text-sm w-full sm:w-64"/>
          
          <select v-model="filters.stockStatus" @change="doSearch" class="border rounded px-4 py-2 text-sm bg-white">
            <option value="">All Inventory Status</option>
            <option value="zero">Out of Stock (=0)</option>
            <option value="available">In Stock (>=1)</option>
          </select>

          <button @click="showCreateModal = true" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
            + Create Product
          </button>
        </div>
      </div>

      <div v-if="$page.props.errors.error" class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
        {{ $page.props.errors.error }}
      </div>

      <div class="overflow-x-auto border rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
          <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold">
            <tr>
              <th class="px-6 py-3">ID</th>
              <th class="px-6 py-3">Product Name</th>
              <th class="px-6 py-3">Current Stock</th>
              <th class="px-6 py-3">Unit Price</th>
              <th class="px-6 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-gray-600">
            <tr v-for="prod in products.data" :key="prod.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 font-mono">{{ prod.id }}</td>
              <td class="px-6 py-4 font-medium text-indigo-600">
                <Link :href="`/products/${prod.id}`" class="hover:underline">{{ prod.name }}</Link>
              </td>
              <td class="px-6 py-4">
                <span :class="prod.stock === 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'" class="px-2.5 py-1 rounded-full text-xs font-semibold">
                  {{ prod.stock }} units
                </span>
              </td>
              <td class="px-6 py-4">${{ parseFloat(prod.price).toFixed(2) }}</td>
              <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                <button @click="openBuyModal(prod)" :disabled="prod.stock <= 0" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded text-xs disabled:opacity-40 disabled:cursor-not-allowed">Buy Now</button>
                <button @click="openRestockPrompt(prod)" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded text-xs">+ Restock</button>
                <button @click="deleteProd(prod.id)" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded text-xs">Delete</button>
              </td>
            </tr>
            <tr v-if="products.data.length === 0">
              <td colspan="5" class="px-6 py-10 text-center text-gray-400">No products found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50 animate-fade-in">
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Create New Product</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Product Name</label>
            <input v-model="createForm.name" type="text" placeholder="e.g. Premium Coffee Beans" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none"/>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Initial Stock</label>
              <input v-model.number="createForm.stock" type="number" min="0" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none"/>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Unit Price ($)</label>
              <input v-model.number="createForm.price" type="number" step="0.01" min="0" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none"/>
            </div>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <button @click="showCreateModal = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-medium">Cancel</button>
          <button @click="submitCreate" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-medium">Save Product</button>
        </div>
      </div>
    </div>

    <div v-if="showBuyModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Place Order: {{ selectedProd.name }}</h3>
        <p class="text-xs text-amber-600 font-medium mb-4">Limit Available: {{ selectedProd.stock }} units</p>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Target Customer</label>
            <select v-model="buyForm.customer_id" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
              <option value="" disabled>-- Select a Customer --</option>
              <option v-for="cust in customers" :key="cust.id" :value="cust.id">{{ cust.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Quantity to Purchase</label>
            <input v-model.number="buyForm.quantity" type="number" min="1" :max="selectedProd.stock" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-indigo-500"/>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <button @click="showBuyModal = false" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm">Cancel</button>
          <button @click="submitBuy" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium">Confirm & Generate Invoice</button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { ref, reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({ products: Object, filters: Object, customers: Array });

const filters = reactive({
  search: props.filters.search || '',
  stockStatus: props.filters.stock_status || ''
});

const showCreateModal = ref(false);
const showBuyModal = ref(false);
const selectedProd = ref(null);

const createForm = reactive({ name: '', stock: 0, price: 0.00 });
const buyForm = ref({ customer_id: '', goods_id: '', quantity: 1 });

const doSearch = () => {
  router.get('/products', { 
    search: filters.search, 
    stock_status: filters.stockStatus 
  }, { preserveState: true, replace: true });
};

const submitCreate = () => {
  if (!createForm.name || createForm.stock < 0 || createForm.price < 0) {
    alert("Please fill validation constraints properly (Stock/Price cannot be negative).");
    return;
  }
  router.post('/products', createForm, {
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.name = ''; createForm.stock = 0; createForm.price = 0.00;
    }
  });
};

const openBuyModal = (product) => {
  selectedProd.value = product;
  buyForm.value.goods_id = product.id;
  buyForm.value.customer_id = '';
  buyForm.value.quantity = 1;
  showBuyModal.value = true;
};

const submitBuy = () => {
  if(!buyForm.value.customer_id) {
    alert("Please choose target customer resource entity.");
    return;
  }
  if(buyForm.value.quantity > selectedProd.value.stock || buyForm.value.quantity < 1) {
    alert("Quantity violates store inventory boundaries!");
    return;
  }
  router.post(`/products/${selectedProd.value.id}/buy`, buyForm.value, {
    onSuccess: () => showBuyModal.value = false
  });
};

const openRestockPrompt = (product) => {
  const qty = prompt(`Enter additional stock count to add for [ ${product.name} ] :`);
  if (qty === null) return;
  
  const intQty = parseInt(qty);
  if (isNaN(intQty) || intQty < 0) {
    alert("Invalid incremental addition quantity.");
    return;
  }
  
  router.put(`/products/${product.id}`, { stock: intQty });
};

const deleteProd = (id) => {
  if(confirm("Are you absolutely sure you want to delete this product? This action intercepts if linked with past invoicing records.")) {
    router.delete(`/products/${id}`);
  }
};
</script>