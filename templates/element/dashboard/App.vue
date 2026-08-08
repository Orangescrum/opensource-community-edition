<template>
  <div class="vue-dashboard">
    <div class="db-topbar">
      <h2>{{ $t('Dashboard') }}</h2>
      <CustomizePanel />
    </div>

    <div v-if="noWidgetsVisible" class="db-empty-dashboard">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" stroke="#ccc" stroke-width="1.5" fill="none"/>
      </svg>
      <p>{{ $t('All widgets are hidden.') }}</p>
      <p>{{ $t('Click') }} <strong>{{ $t('Customize') }}</strong> {{ $t('to select which widgets to display.') }}</p>
    </div>

    <!-- KPI cards — outside the grid, compact -->
    <template v-if="kpiWidget">
      <component :is="kpiWidget.component" />
    </template>

    <draggable
      v-model="gridWidgets"
      item-key="id"
      handle=".db-drag-handle"
      animation="200"
      ghost-class="db-ghost"
      class="db-grid"
    >
      <template #item="{ element: widget }">
        <div :class="'db-grid__item db-grid__item--' + widget.type">
          <component :is="widget.component" />
        </div>
      </template>
    </draggable>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import draggable from 'vuedraggable'
import { usePermissions } from './composables/usePermissions'
import { useWidgetVisibility } from './composables/useWidgetVisibility'
import { useWidgetOrder } from './composables/useWidgetOrder'
import { buildWidgets } from './config/widgetRegistry'
import CustomizePanel from './components/shared/CustomizePanel.vue'

const can = usePermissions()
const { visibility: vis } = useWidgetVisibility()
const { order, updateOrder } = useWidgetOrder()

const allWidgets = buildWidgets(can)
const widgetMap = Object.fromEntries(allWidgets.map(w => [w.id, w]))

const visibleWidgets = computed(() => {
  return order.value
    .map(id => widgetMap[id])
    .filter(w => w && w.canShow() && vis[w.id] !== false)
})

// KPI stays outside the grid (compact, not draggable)
const kpiWidget = computed(() => visibleWidgets.value.find(w => w.type === 'kpi'))

// All other widgets go in the draggable grid
const gridWidgets = computed({
  get() { return visibleWidgets.value.filter(w => w.type !== 'kpi') },
  set(newVal) {
    // Rebuild full order: kpi first, then new grid order
    const kpiIds = order.value.filter(id => widgetMap[id]?.type === 'kpi')
    updateOrder([...kpiIds, ...newVal.map(w => w.id)])
  },
})

const noWidgetsVisible = computed(() => visibleWidgets.value.length === 0)
</script>

<style>
/*
 * Top padding clears the fixed .db-topbar (68px) and leaves one 20px spacing
 * step before the first widget row. The host already offsets the app by 20px,
 * so 48 + 20 lands the KPI row exactly one step below the header.
 */
.vue-dashboard {
  padding: 48px 15px 20px;
}
.vue-dashboard .db-topbar {
  position: fixed;
  top: 50px;
  left: 0;
  right: 0;
  height: 68px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px 0 255px;
  background: #fff;
  box-shadow: 0 3px 6px rgba(170,170,170,.23);
  z-index: 86;
}
.menu_squeeze .vue-dashboard .db-topbar {
  padding-left: 65px;
}
.vue-dashboard .db-topbar h2 {
  font-size: 20px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

/* ── 12-column CSS Grid ─────────────────────────────────────────── */
.vue-dashboard .db-grid {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  grid-auto-rows: minmax(380px, auto);
  gap: 20px;
}
.vue-dashboard .db-grid__item--full  { grid-column: span 12; }
.vue-dashboard .db-grid__item--table { grid-column: span 8; }
.vue-dashboard .db-grid__item--chart { grid-column: span 4; }
.vue-dashboard .db-grid__item--stat  { grid-column: span 6; }

/* Grid item — fills the grid cell */
.vue-dashboard .db-grid__item { display: flex; flex-direction: column; }

/* Card base — fills grid item, content scrolls if needed */
.vue-dashboard .db-card {
  background: #fff;
  border: 1px solid #F1F1F5;
  border-radius: 5px;
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24);
  transition: box-shadow .2s;
}
.vue-dashboard .db-card > :last-child {
  flex: 1;
  overflow-y: auto;
}
.vue-dashboard .db-card:hover {
  box-shadow: 0 0 20px rgba(113,113,124,.27);
}
.vue-dashboard .db-card__header {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  margin-bottom: 15px;
}
.vue-dashboard .db-card__title {
  font-size: 14px;
  font-weight: 600;
  color: #292940;
  line-height: 30px;
  flex: 1;
  min-width: 120px;
}
.vue-dashboard .db-card__filters {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
}
.vue-dashboard .db-card__filters select {
  height: 35px;
  border: 1px solid #dadce0;
  border-radius: 4px;
  font-size: 13px;
  color: #555;
  padding: 0 10px;
  min-width: 140px;
  background: #fff;
}

/* Drag */
.vue-dashboard .db-drag-handle {
  cursor: grab;
  color: #ccc;
  padding: 2px;
  transition: color .2s;
  flex-shrink: 0;
}
.vue-dashboard .db-drag-handle:hover { color: #888; }
.vue-dashboard .db-drag-handle:active { cursor: grabbing; }
.vue-dashboard .db-ghost { opacity: 0.4; }
.vue-dashboard .db-ghost .db-card { box-shadow: 0 0 0 2px var(--primary, #6366f1); }

/* Table */
.vue-dashboard .db-table {
  width: 100%;
  border-collapse: collapse;
}
.vue-dashboard .db-table th {
  text-align: left;
  font-size: 13px;
  font-weight: 500;
  color: #727272;
  padding: 8px;
  border-bottom: 1px solid #ddd;
  white-space: nowrap;
}
.vue-dashboard .db-table td {
  font-size: 14px;
  color: #2e2e2e;
  padding: 8px;
  border-top: 1px solid #f1f1f1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.vue-dashboard .db-table tbody tr:hover {
  background: #f8f8f8;
}
.vue-dashboard .db-table-scroll {
  max-height: 280px;
  overflow-y: auto;
}
.vue-dashboard .db-table-scroll--compact {
  max-height: none;
}

/* Status dots */
.vue-dashboard .db-status { display: inline-block; position: relative; padding-left: 13px; }
.vue-dashboard .db-status::before { content:''; width:8px; height:8px; border-radius:50%; display:inline-block; position:absolute; left:0; top:50%; transform:translateY(-50%); }
.vue-dashboard .db-status--complete::before { background:#2AD36C; }
.vue-dashboard .db-status--ontrack::before { background:#6570FD; }
.vue-dashboard .db-status--delay::before { background:#F99003; }
.vue-dashboard .db-status--risk::before { background:#E84C85; }

/* Progress bar */
.vue-dashboard .db-progress { display:flex; align-items:center; gap:8px; }
.vue-dashboard .db-progress__bar { flex:1; height:6px; background:#f0f0f5; border-radius:3px; overflow:hidden; }
.vue-dashboard .db-progress__fill { height:100%; border-radius:3px; transition: width .3s; }
.vue-dashboard .db-progress__text { font-size:12px; color:#888; min-width:35px; text-align:right; }

/* Stats row for donut cards */
.vue-dashboard .db-stats { display:flex; justify-content:center; gap:0; margin-top:15px; }
.vue-dashboard .db-stats__item { text-align:center; padding:0 12px; font-size:11px; color:#71718E; line-height:18px; }
.vue-dashboard .db-stats__item strong { display:block; font-size:18px; margin-bottom:2px; }
.vue-dashboard .db-stats__item--total { font-size:14px; font-weight:600; color:#292940; }
.vue-dashboard .db-stats__item--total strong { font-size:20px; color:#292940; }
.vue-dashboard .db-stats__item--complete strong { color:#2AD36C; }
.vue-dashboard .db-stats__item--ontrack strong { color:#6570FD; }
.vue-dashboard .db-stats__item--delay strong { color:#F99003; }
.vue-dashboard .db-stats__item--risk strong { color:#E84C85; }

/* Empty state */
.vue-dashboard .db-empty { text-align:center; padding:40px 20px; color:#999; font-size:13px; }
.vue-dashboard .db-empty-dashboard {
  text-align: center;
  padding: 80px 20px;
  color: #999;
}
.vue-dashboard .db-empty-dashboard svg { margin-bottom: 12px; }
.vue-dashboard .db-empty-dashboard p { font-size: 14px; margin: 4px 0; color: #888; }
.vue-dashboard .db-empty-dashboard p strong { color: var(--primary, #6366f1); }

/* Loading skeleton */
.vue-dashboard .db-skeleton { height:14px; background:#f0f0f5; border-radius:4px; margin-bottom:10px; animation: db-pulse 1.5s ease-in-out infinite; }
.vue-dashboard .db-skeleton--short { width:60%; }
@keyframes db-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

/* Tabs */
.vue-dashboard .db-tabs { display:flex; border-bottom:2px solid #eee; margin-bottom:10px; }
.vue-dashboard .db-tabs__btn { padding:8px 12px; border:none; background:none; cursor:pointer; font-size:13px; font-weight:400; color:#888; border-bottom:2px solid transparent; margin-bottom:-2px; }
.vue-dashboard .db-tabs__btn--active { font-weight:600; color:var(--primary, #6366f1); border-bottom-color:var(--primary, #6366f1); }

/* Scrollable list */
.vue-dashboard .db-scroll { max-height:300px; overflow-y:auto; }

/* Links */
.vue-dashboard .db-link { color:#292940; text-decoration:none; }
.vue-dashboard .db-link:hover { color:var(--primary, #6366f1); }
.vue-dashboard .db-link--muted { color:#71718E; text-decoration:none; }
.vue-dashboard .db-link--muted:hover { color:var(--primary, #6366f1); }

/* List items */
.vue-dashboard .db-list-item { padding:10px; border-top:1px solid #eee; }
.vue-dashboard .db-list-item:first-child { border-top-color:transparent; }
.vue-dashboard .db-list-item__title { font-size:14px; color:#292940; font-weight:400; margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.vue-dashboard .db-list-item__meta { font-size:12px; color:#71718E; margin-top:3px; }
.vue-dashboard .db-list-item__meta a { color:#6570FD; text-decoration:none; }
.vue-dashboard .db-list-item .db-due-tag { font-size:12px; color:#E84C85; background:#f7e3ea; padding:2px 6px; border-radius:4px; }
</style>
