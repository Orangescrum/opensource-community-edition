<template>
  <div class="customize-wrapper">
    <button class="customize-btn" @click="open = !open" :title="open ? $t('Close') : $t('Customize Widgets')">
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6.5 1L7.44 3.44L10 4.5L7.44 5.56L6.5 8L5.56 5.56L3 4.5L5.56 3.44L6.5 1Z" fill="currentColor"/>
        <path d="M11 7L11.63 8.87L13.5 9.5L11.63 10.13L11 12L10.37 10.13L8.5 9.5L10.37 8.87L11 7Z" fill="currentColor"/>
        <path d="M4.5 9L5.13 10.87L7 11.5L5.13 12.13L4.5 14L3.87 12.13L2 11.5L3.87 10.87L4.5 9Z" fill="currentColor"/>
      </svg>
      <span>{{ $t('Customize') }}</span>
    </button>

    <div v-if="open" class="customize-panel">
      <div class="panel-header">
        <strong>{{ $t('Show / Hide Widgets') }}</strong>
        <div class="panel-actions">
          <button class="action-btn" @click="onSelectAll">{{ $t('All') }}</button>
          <span class="action-sep">|</span>
          <button class="action-btn" @click="onSelectNone">{{ $t('None') }}</button>
          <span class="action-sep">|</span>
          <button class="action-btn action-btn--reset" @click="onReset">{{ $t('Reset') }}</button>
        </div>
      </div>
      <div class="panel-body">
        <template v-for="(label, key) in availableWidgets" :key="key">
          <label class="toggle-row">
            <input
              type="checkbox"
              :checked="visibility[key]"
              @change="toggle(key)"
            />
            <span>{{ label }}</span>
          </label>
        </template>
      </div>
    </div>

    <div v-if="open" class="panel-overlay" @click="open = false"></div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useWidgetVisibility, widgetLabels, widgetPermissionKey } from '../../composables/useWidgetVisibility'
import { useWidgetOrder } from '../../composables/useWidgetOrder'
import { usePermissions } from '../../composables/usePermissions'

const { visibility, toggle, resetAll, selectAll, selectNone } = useWidgetVisibility()
const { resetOrder } = useWidgetOrder()
const can = usePermissions()
const open = ref(false)

function onSelectAll() {
  selectAll(Object.keys(availableWidgets.value))
}

function onReset() {
  resetAll()
  resetOrder()
}

function onSelectNone() {
  selectNone(Object.keys(availableWidgets.value))
}

const availableWidgets = computed(() => {
  const widgets = {}
  for (const [key, label] of Object.entries(widgetLabels)) {
    const permKey = widgetPermissionKey[key]
    if (!permKey || can[permKey]) {
      widgets[key] = label
    }
  }
  return widgets
})
</script>

<style scoped>
.customize-wrapper {
  position: relative;
  display: inline-block;
}
.customize-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 13px;
  color: #555;
  cursor: pointer;
  transition: all .2s;
}
.customize-btn:hover {
  border-color: var(--primary, #6366f1);
  color: var(--primary, #6366f1);
}
.customize-panel {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 6px;
  width: 280px;
  background: #fff;
  border: 1px solid #e5e5e5;
  border-radius: 5px;
  box-shadow: 0 4px 20px rgba(0,0,0,.12);
  z-index: 1000;
  max-height: 420px;
  display: flex;
  flex-direction: column;
}
.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 15px;
  border-bottom: 1px solid #f0f0f0;
}
.panel-header strong {
  font-size: 13px;
  color: #292940;
}
.panel-actions {
  display: flex;
  align-items: center;
  gap: 4px;
}
.action-btn {
  font-size: 12px;
  color: var(--primary, #6366f1);
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}
.action-btn:hover {
  text-decoration: underline;
}
.action-btn--reset {
  color: #888;
}
.action-sep {
  font-size: 11px;
  color: #ccc;
}
.panel-body {
  padding: 8px 0;
  overflow-y: auto;
  flex: 1;
}
.toggle-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 7px 15px;
  cursor: pointer;
  font-size: 13px;
  color: #444;
  transition: background .15s;
}
.toggle-row:hover {
  background: #f8f8fb;
}
.toggle-row input[type="checkbox"] {
  width: 15px;
  height: 15px;
  accent-color: var(--primary, #6366f1);
  cursor: pointer;
}
.panel-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 999;
}
</style>
