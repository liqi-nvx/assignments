<template>
  <AuthenticatedLayout>
    <div class="bg-white p-6 rounded-lg shadow-md">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Stock & Product Management</h2>
        <div class="flex gap-4">
          <input v-model="search" @input="doSearch" type="text" placeholder="Search product name..." class="border rounded px-4 py-2 text-sm w-64"/>
          <select v-model="stockStatus" @change="doSearch" class="border rounded px-4 py-2 text-sm">
            <option value="">All Inventory Status</option>
            <option value="zero">Out of Stock (=0)</option>
            <option value="available">In Stock (>=1)</option>
          </select>
        </div>
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
            <tr v-for="prod in products.data" :key="prod.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-mono">{{ prod.id }}</td>
              <td class="px-6 py-4 font-medium text-indigo-600">
                <Link :href="`/products/${prod.id}`">{{ prod.name }}</Link>
              </td>
              <td class="px-6 py-4">
                <span :class="prod.stock === 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'" class="px-2.5 py-1 rounded-full text-xs font-semibold">
                  {{ prod.stock }} units
                </span>
              </td>
              <td class="px-6 py-4">${{ prod.price }}</td>
              <td class="px-6 py-4 text-right space-x-2">
                <button @click="openBuyModal(prod)" :disabled="prod.stock <= 0" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-xs disabled:opacity-50">Buy Now</button>
                <button @click="restock(prod)" class="bg-amber-500 text-white px-3 py-1.5 rounded text-xs">+ Restock</button>
                <button @click="deleteProd(prod.id)" class="bg-rose-600 text-white px-3 py-1.5 rounded text-xs">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showBuyModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg p-6 max-w-sm w-full">
        <h3 class="text-lg font-bold mb-4">Place Order: {{ selectedProd.name }}</h3>
        <p class="text-sm text-gray-500 mb-4">Stock Limit: {{ selectedProd.stock }} units available</p>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold mb-1">Target Customer</label>
            <select v-model="buyForm.customer_id" class="w-full border rounded p-2 text-sm">
              <option v-for="cust in customers" :key="cust.id" :value="cust.id">{{ cust.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold mb-1">Quantity</label>
            <input v-model.number="buyForm.quantity" type="number" min="1" :max="selectedProd.stock" class="w-full border rounded p-2 text-sm"/>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-6">
          <button @click="showBuyModal = false" class="bg-gray-200 px-4 py-2 rounded text-sm">Cancel</button>
          <button @click="submitBuy" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm">Confirm Purchase</button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({ products: Object, filters: Object, customers: Array });

const search = ref(props.filters.search || '');
const stockStatus = ref(props.filters.stock_status || '');
const showBuyModal = ref(false);
const selectedProd = ref(null);

const buyForm = ref({ customer_id: '', goods_id: '', quantity: 1 });

const doSearch = () => {
  router.get('/products', { search: search.value, stock_status: stockStatus.value }, { preserveState: true });
};

const openBuyModal = (product) => {
  selectedProd.value = product;
  buyForm.value.goods_id = product.id;
  buyForm.value.quantity = 1;
  showBuyModal.value = true;
};

const submitBuy = () => {
  if(buyForm.value.quantity > selectedProd.value.stock) {
    alert("Quantity violates store inventory capacity limits!");
    return;
  }
  router.post(`/products/${selectedProd.value.id}/buy`, buyForm.value, {
    onSuccess: () => showBuyModal.value = false
  });
};

const restock = (product) => {
  const qty = prompt("Enter additional inventory count to add:");
  if (qty) router.put(`/products/${product.id}`, { stock: parseInt(qty) });
};

const deleteProd = (id) => {
  if(confirm("Confirm deletion of this resource?")) router.delete(`/products/${id}`);
};
</script>