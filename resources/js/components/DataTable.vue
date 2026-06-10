<template>
  <div class="flex flex-col w-full bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
    <div class="p-4 bg-gray-50 border-b border-gray-200 flex flex-wrap gap-4 items-center justify-between">
      <slot name="filters"></slot>
    </div>

    <div class="w-full overflow-x-auto scrollbar-thin">
      <table class="w-full text-sm text-left text-gray-500 min-w-[1000px]">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200">
          <tr>
            <th v-for="col in columns" :key="col.key" class="px-6 py-4 font-semibold whitespace-nowrap">
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in data" :key="index" class="bg-white border-b hover:bg-gray-50 transition-colors">
            <td v-for="col in columns" :key="col.key" class="px-6 py-4 whitespace-nowrap text-gray-900">
              <slot :name="`cell(${col.key})`" :row="item">
                {{ item[col.key] }}
              </slot>
            </td>
          </tr>
          <tr v-if="data.length === 0">
            <td :colspan="columns.length" class="text-center py-10 text-gray-400">
              No dynamic records found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="$slots.footer" class="p-4 bg-gray-50 border-t border-gray-200">
      <slot name="footer"></slot>
    </div>

    <div v-if="pagination && pagination.total > pagination.per_page" class="p-4 bg-white border-t border-gray-200 flex items-center justify-between">
      <span class="text-xs text-gray-600">Showing page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
      <div class="inline-flex space-x-2">
        <button 
          :disabled="pagination.current_page === 1"
          @click="$emit('page-change', pagination.current_page - 1)"
          class="px-3 py-1 bg-gray-200 rounded text-xs disabled:opacity-50 hover:bg-gray-300"
        >
          Previous
        </button>
        <button 
          :disabled="pagination.current_page === pagination.last_page"
          @click="$emit('page-change', pagination.current_page + 1)"
          class="px-3 py-1 bg-gray-200 rounded text-xs disabled:opacity-50 hover:bg-gray-300"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  columns: { type: Array, required: true },
  data: { type: Array, required: true },
  pagination: { type: Object, default: null }
});
defineEmits(['page-change']);
</script>