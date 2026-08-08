<?php 
// Detect RTL languages
$locale = \Cake\I18n\I18n::getLocale();
$rtlLanguages = ['ar', 'he', 'fa', 'ur', 'yi']; // Arabic, Hebrew, Persian, Urdu, Yiddish
$isRTL = false;
foreach ($rtlLanguages as $rtlLang) {
    if (strpos($locale, $rtlLang) === 0) {
        $isRTL = true;
        break;
    }
}
$dirAttribute = $isRTL ? 'rtl' : 'ltr';
?>
<?php echo $this->element('tasktree/styles'); ?>

<div id="task-tree-app" dir="<?= $dirAttribute ?>">
    <v-app>
        <v-container fluid class="hierarchy-container">
            <!-- Toolbar -->
            <div class="main-toolbar">
                <div class="toolbar-left">
                    <span class="text-body-2">
                        <?php echo __('Displaying all'); ?> {{ totalTasks }} <?php echo __('task'); ?>{{ totalTasks !== 1 ? '<?php echo __('s'); ?>' : '' }}
                    </span>
                </div>
                <div class="toolbar-right">
                    <v-btn size="small" variant="text" @click="expandAll" :title="'<?php echo __('Expand All'); ?>'">
                        <v-icon start>mdi-arrow-expand-vertical</v-icon>
                    </v-btn>
                    <v-btn size="small" variant="text" @click="collapseAll" :title="'<?php echo __('Collapse All'); ?>'">
                        <v-icon start>mdi-arrow-collapse-vertical</v-icon>
                    </v-btn>
                    <v-btn size="small" variant="text" @click="reloadTasks" :title="'<?php echo __('Reload'); ?>'">
                        <v-icon start>mdi-refresh</v-icon>
                    </v-btn>
                </div>
            </div>

            <!-- Task List Container -->
            <div class="task-list">
                <!-- Loading Indicator -->
                <div v-if="loading || initialLoad" class="loading-container">
                    <img src="<?= $this->Url->build('/images/rolling.gif') ?>" alt="<?php echo __('Loading...'); ?>">
                </div>

                <!-- No Tasks Message -->
                <div v-else-if="!loading && treeItems.length === 0" class="no-tasks-container">
                    <v-icon size="48" color="grey">mdi-clipboard-text-outline</v-icon>
                    <div class="no-tasks-text"><?php echo __('No tasks found'); ?></div>
                </div>

                <!-- Task List -->
                <div v-else>
                    <!-- Header Row -->
                    <div class="header-row">
                        <div class="col-expand"></div>
                        <div class="col-actions"></div>
                        <div class="col-number sortable-header" @click="changeSorting('case_no')">
                            # 
                            <v-icon v-if="sortBy === 'case_no'" size="small" class="sort-icon">
                                {{ sortOrder === 'ASC' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                            </v-icon>
                        </div>
                        <div class="col-title sortable-header" @click="changeSorting('title')">
                            <?php echo __('Title'); ?>
                            <v-icon v-if="sortBy === 'title'" size="small" class="sort-icon">
                                {{ sortOrder === 'ASC' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                            </v-icon>
                        </div>
                        <div class="col-assigned sortable-header" @click="changeSorting('assign_to')">
                            <?php echo __('Assigned to'); ?>
                            <v-icon v-if="sortBy === 'assign_to'" size="small" class="sort-icon">
                                {{ sortOrder === 'ASC' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                            </v-icon>
                        </div>
                        <div class="col-priority sortable-header" @click="changeSorting('priority')">
                            <?php echo __('Priority'); ?>
                            <v-icon v-if="sortBy === 'priority'" size="small" class="sort-icon">
                                {{ sortOrder === 'ASC' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                            </v-icon>
                        </div>
                        <div class="col-updated sortable-header" @click="changeSorting('dt_created')">
                            <?php echo __('Updated'); ?>
                            <v-icon v-if="sortBy === 'dt_created'" size="small" class="sort-icon">
                                {{ sortOrder === 'ASC' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                            </v-icon>
                        </div>
                        <div class="col-status sortable-header" @click="changeSorting('status')">
                            <?php echo __('Status'); ?>
                            <v-icon v-if="sortBy === 'status'" size="small" class="sort-icon">
                                {{ sortOrder === 'ASC' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                            </v-icon>
                        </div>
                        <div class="col-due sortable-header" @click="changeSorting('due_date')">
                            <?php echo __('Due Date'); ?>
                            <v-icon v-if="sortBy === 'due_date'" size="small" class="sort-icon">
                                {{ sortOrder === 'ASC' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                            </v-icon>
                        </div>
                    </div>
                    <!-- Task Rows - Scrollable Content -->
                    <div class="task-list-content">
                        <task-row v-for="item in treeItems" :key="item.id" :item="item" :level="0"
                            :expanded-items="expandedItems" :users="users"
                            @toggle="toggleItem"></task-row>
                    </div>
                </div>
            </div>
        </v-container>
    </v-app>
</div>

<?php echo $this->element('tasktree/vue3_component_templates'); ?>

<?php echo $this->element('tasktree/vue3_app'); ?>