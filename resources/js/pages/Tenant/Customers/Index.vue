<template>
  <AuthenticatedLayout>
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">客户管理 (Customers)</h1>
        <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
            + 添加客户
        </button>
        </div>

        <div class="mb-4 flex items-center">
        <input 
            v-model="searchQuery" 
            @input="handleSearch"
            type="text" 
            placeholder="输入客户名称或邮箱搜索..." 
            class="border border-gray-300 rounded px-4 py-2 w-80 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        </div>

        <div v-if="$page.props.errors.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {{ $page.props.errors.error }}
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">名称</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">邮箱</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">电话</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">地址</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">操作</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ customer.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ customer.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ customer.phone }}</td>
                <td class="px-6 py-4 text-gray-600 max-w-xs truncate">{{ customer.address || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="openEditModal(customer)" class="text-blue-600 hover:text-blue-900 mr-3">编辑</button>
                <button @click="deleteCustomer(customer.id)" class="text-red-600 hover:text-red-900">删除</button>
                </td>
            </tr>
            <tr v-if="customers.data.length === 0">
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">暂无客户数据</td>
            </tr>
            </tbody>
        </table>
        </div>

        <div class="mt-4 flex justify-between items-center" v-if="customers.links && customers.links.length > 3">
        <div class="text-sm text-gray-600">共 {{ customers.total }} 条记录</div>
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
            <h3 class="text-lg font-bold mb-4 text-gray-900">{{ isEdit ? '修改客户信息' : '添加新客户' }}</h3>
            <form @submit.prevent="submitForm">
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">姓名 <span class="text-red-500">*</span></label>
                <input v-model="form.name" type="text" class="border w-full rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required />
                <span v-if="form.errors.name" class="text-red-500 text-xs">{{ form.errors.name }}</span>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">邮箱 <span class="text-red-500">*</span></label>
                <input v-model="form.email" type="email" class="border w-full rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required />
                <span v-if="form.errors.email" class="text-red-500 text-xs">{{ form.errors.email }}</span>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">电话 <span class="text-red-500">*</span></label>
                <input v-model="form.phone" type="text" class="border w-full rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" required />
                <span v-if="form.errors.phone" class="text-red-500 text-xs">{{ form.errors.phone }}</span>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">地址</label>
                <textarea v-model="form.address" rows="3" class="border w-full rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                <span v-if="form.errors.address" class="text-red-500 text-xs">{{ form.errors.address }}</span>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" @click="showModal = false" class="px-4 py-2 border rounded text-sm text-gray-600 hover:bg-gray-50">取消</button>
                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-medium shadow">
                {{ form.processing ? '保存中...' : '确定保存' }}
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
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce'; // 如果没有装 lodash，可以直接用普通函数替代

const props = defineProps({
  customers: Object,
  filters: Object
});

const searchQuery = ref(props.filters.search || '');
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
  router.get('/customers', { search: searchQuery.value }, { preserveState: true, replace: true });
}, 400);

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
  if (confirm('确定要删除该客户吗？关联数据可能会受到影响。')) {
    // 对应后端的 public function destroy(Customer $customer)
    router.delete(`/customers/${id}`);
  }
};
</script>