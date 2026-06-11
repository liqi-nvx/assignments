<template>
  <AuthenticatedLayout>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Customer Management (Customers)</h1>
        <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
            + Create Customer
        </button>
        </div>

        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-end bg-gray-50 p-4 rounded-lg border border-gray-100">
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Name</label>
            <input 
                v-model="searchFilters.name" 
                @input="handleSearch"
                type="text" 
                placeholder="Name..." 
                class="border border-gray-300 rounded px-3 py-1.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
            <input 
                v-model="searchFilters.email" 
                @input="handleSearch"
                type="text" 
                placeholder="Email..." 
                class="border border-gray-300 rounded px-3 py-1.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Phone Number</label>
            <input 
                v-model="searchFilters.phone" 
                @input="handleSearch"
                type="text" 
                placeholder="Phone number..." 
                class="border border-gray-300 rounded px-3 py-1.5 w-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <button @click="clearFilters" class="text-sm text-gray-500 hover:text-blue-600 underline">
              Clear Filters
            </button>
          </div>
        </div>

        <div v-if="$page.props.errors.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {{ $page.props.errors.error }}
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ customer.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ customer.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ customer.phone }}</td>
                <td class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ customer.address || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="openEditModal(customer)" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                <button @click="deleteCustomer(customer.id)" class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            </tr>
            <tr v-if="customers.data.length === 0">
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">No customers found</td>
            </tr>
            </tbody>
        </table>
        </div>

        <div class="mt-4 flex justify-between items-center" v-if="customers.links && customers.links.length > 3">
        <div class="text-sm text-gray-600">Showing {{ customers.total }} results</div>
        <div class="flex space-x-1">
            <Link 
            v-for="(link, index) in customers.links" 
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

        <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold mb-4 text-gray-900">{{ isEdit ? 'Edit Customer Details' : 'Create New Customer' }}</h3>
            <form @submit.prevent="submitForm">
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input v-model="form.name" type="text" class="border w-full rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required />
                <span v-if="form.errors.name" class="text-red-500 text-xs">{{ form.errors.name }}</span>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input v-model="form.email" type="email" class="border w-full rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required />
                <span v-if="form.errors.email" class="text-red-500 text-xs">{{ form.errors.email }}</span>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                <input v-model="form.phone" type="text" class="border w-full rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required />
                <span v-if="form.errors.phone" class="text-red-500 text-xs">{{ form.errors.phone }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <textarea v-model="form.address" rows="3" class="border w-full rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                <span v-if="form.errors.address" class="text-red-500 text-xs">{{ form.errors.address }}</span>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" @click="showModal = false" class="px-4 py-2 border rounded text-sm text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-medium shadow">
                {{ form.processing ? 'Saving...' : 'Save' }}
                </button>
            </div>
            </form>
        </div>
        </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Components/AuthenticatedLayout.vue';
import { ref, reactive } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce'; // 如果没有装 lodash，可以直接用普通函数替代

const props = defineProps({
  customers: Object,
  filters: Object
});

const searchFilters = reactive({
  name: props.filters.name || '',
  email: props.filters.email || '',
  phone: props.filters.phone || ''
});

const showModal = ref(false);
const isEdit = ref(false);
const currentCustomerId = ref(null);

const form = useForm({
  name: '',
  email: '',
  phone: '',
  address: ''
});

// 防抖搜索处理
const handleSearch = debounce(() => {
  router.get('/customers', searchFilters, { preserveState: true, replace: true });
}, 400);

const clearFilters = () => {
  searchFilters.name = '';
  searchFilters.email = '';
  searchFilters.phone = '';
  handleSearch();
};

const openCreateModal = () => {
  isEdit.value = false;
  currentCustomerId.value = null;
  form.reset();
  form.clearErrors();
  showModal.value = true;
};

const openEditModal = (customer) => {
  isEdit.value = true;
  currentCustomerId.value = customer.id;
  form.clearErrors();
  form.name = customer.name;
  form.email = customer.email;
  form.phone = customer.phone;
  form.address = customer.address;
  showModal.value = true;
};

const submitForm = () => {
  if (isEdit.value) {
    // 对应后端的 public function update(Request $request, Customer $customer)
    form.put(`/customers/${currentCustomerId.value}`, {
      onSuccess: () => showModal.value = false
    });
  } else {
    // 对应后端的 public function store(CustomerRequest $request)
    form.post('/customers', {
      onSuccess: () => showModal.value = false
    });
  }
};

const deleteCustomer = (id) => {
  if (confirm('Are you sure you want to delete this customer? Associated data may be affected.')) {
    // 对应后端的 public function destroy(Customer $customer)
    router.delete(`/customers/${id}`);
  }
};
</script>