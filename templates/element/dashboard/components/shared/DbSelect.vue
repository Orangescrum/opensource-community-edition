<template>
  <div class="db-select" :class="{ 'db-select--open': open }" ref="wrapper">
    <button class="db-select__trigger" @click="open = !open" type="button">
      <span class="db-select__label">{{ selectedLabel }}</span>
      <span class="db-select__arrow"></span>
    </button>
    <div v-if="open" class="db-select__dropdown">
      <input
        v-if="searchable && Object.keys(options).length > 6"
        v-model="search"
        class="db-select__search"
        :placeholder="$t('Search...')"
        ref="searchInput"
        @click.stop
      />
      <div class="db-select__options">
        <div
          v-for="(label, value) in filteredOptions"
          :key="value"
          class="db-select__option"
          :class="{ 'db-select__option--active': String(modelValue) === String(value) }"
          @click="select(value)"
        >{{ label }}</div>
        <div v-if="!Object.keys(filteredOptions).length" class="db-select__option db-select__option--empty">{{ $t('No results') }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { t } from '../../composables/useI18n'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: { type: Object, default: () => ({}) },
  searchable: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue'])
const open = ref(false)
const search = ref('')
const wrapper = ref(null)
const searchInput = ref(null)

const selectedLabel = computed(() => {
  return props.options[props.modelValue] || Object.values(props.options)[0] || t('Select...')
})

const filteredOptions = computed(() => {
  if (!search.value) return props.options
  const s = search.value.toLowerCase()
  const result = {}
  for (const [k, v] of Object.entries(props.options)) {
    if (String(v).toLowerCase().includes(s)) result[k] = v
  }
  return result
})

function select(value) {
  emit('update:modelValue', value)
  open.value = false
  search.value = ''
}

function handleClickOutside(e) {
  if (wrapper.value && !wrapper.value.contains(e.target)) {
    open.value = false
    search.value = ''
  }
}

watch(open, (val) => {
  if (val && props.searchable) {
    nextTick(() => searchInput.value?.focus())
  }
})

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>

<style scoped>
.db-select {
  position: relative;
  min-width: 150px;
  font-size: 13px;
}
.db-select__trigger {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  height: 35px;
  padding: 0 30px 0 12px;
  border: 1px solid var(--gray-300, #dadce0);
  border-radius: 4px;
  background: #fff;
  cursor: pointer;
  font-size: 13px;
  color: var(--gray-600, #555);
  text-align: left;
  position: relative;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.db-select__trigger:hover {
  border-color: var(--gray-400, #bbb);
}
.db-select--open .db-select__trigger {
  border-color: var(--primary, #6366f1);
}
.db-select__arrow {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 0;
  height: 0;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 5px solid var(--gray-500, #666);
}
.db-select--open .db-select__arrow {
  border-top-color: var(--primary, #6366f1);
}
.db-select__dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: 2px;
  background: #fff;
  border: 1px solid var(--gray-300, #dadce0);
  border-radius: 4px;
  box-shadow: 0 4px 12px rgba(0,0,0,.1);
  z-index: 100;
  max-height: 250px;
  display: flex;
  flex-direction: column;
}
.db-select__search {
  padding: 8px 10px;
  border: none;
  border-bottom: 1px solid var(--gray-200, #eee);
  font-size: 13px;
  outline: none;
}
.db-select__options {
  overflow-y: auto;
  flex: 1;
}
.db-select__option {
  padding: 7px 12px;
  cursor: pointer;
  color: #444;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.db-select__option:hover {
  background: var(--gray-100, #f5f5f5);
}
.db-select__option--active {
  color: var(--primary, #6366f1);
  font-weight: 500;
  background: var(--gray-100, #fff5f0);
}
.db-select__option--empty {
  color: #999;
  cursor: default;
}
</style>
