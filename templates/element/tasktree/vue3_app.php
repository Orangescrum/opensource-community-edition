<script>
    $(document).ready(function () {
        <?php // Load Vue component scripts ?>
        <?php echo $this->element('tasktree/vue3_component_scripts'); ?>

        <?php // Vue 3 App starts here ?>
        const { createApp } = Vue;
        const { createVuetify } = Vuetify;

        const vuetify = createVuetify({
            theme: { defaultTheme: 'light' }
        });

        // Get current project ID from URL if exists
        const urlParams = new URLSearchParams(window.location.search);
        const currentProjectId = urlParams.get('project_id') || null;

        const app = createApp({
            data() {
                return {
                    treeItems: [],
                    expandedItems: [],
                    viewMode: 'hierarchy',
                    users: {},
                    projects: {},
                    selectedProject: currentProjectId,
                    loading: false,
                    initialLoad: true,
                    groupBy: '',
                    sortBy: 'case_no',
                    sortOrder: 'ASC',
                    isRTL: document.getElementById('task-tree-app')?.getAttribute('dir') === 'rtl'
                };
            },
            computed: {
                totalTasks() {
                    const countTasks = (items) => {
                        let count = 0;
                        items.forEach(item => {
                            count++;
                            if (item.children && item.children.length > 0) {
                                count += countTasks(item.children);
                            }
                        });
                        return count;
                    };
                    return countTasks(this.treeItems);
                },
                overdueCount() {
                    const countOverdue = (items) => {
                        let count = 0;
                        const now = new Date();
                        items.forEach(item => {
                            if (item.due_date) {
                                const dueDate = new Date(item.due_date);
                                if (dueDate < now && item.legend !== 3) {
                                    count++;
                                }
                            }
                            if (item.children && item.children.length > 0) {
                                count += countOverdue(item.children);
                        }
                        });
                        return count;
                    };
                    return countOverdue(this.treeItems);
                }
            },
            methods: {
                async loadTaskData(projectId = null) {
                    this.loading = true;
                    try {
                        const url = '<?= $this->Url->build(['controller' => 'TaskTree', 'action' => 'ajaxTaskTree']) ?>';
                        
                        // Prepare POST data similar to caseProject
                        const formData = new FormData();
                        if (projectId) {
                            formData.append('project_id', projectId);
                        }
                        formData.append('casegroupby', this.groupBy);
                        formData.append('sortby', this.sortBy);
                        formData.append('sortorder', this.sortOrder);
                        
                        // Use axios which already has CSRF token configured in header_inner.php
                        const response = await axios.post(url, formData);
                        
                        const data = response.data;
                        
                        if (data.success) {
                            this.treeItems = data.taskTrees || [];
                            this.users = data.usrDtlsArr || {};
                            this.projects = data.projectsList || {};
                            this.selectedProject = data.projectId || null;
                            this.groupBy = data.groupBy || '';
                            this.sortBy = data.sortBy || 'case_no';
                            this.sortOrder = data.sortOrder || 'ASC';
                            
                            // Restore expanded state from localStorage
                            this.loadExpandedState();
                            
                            // Clear selections when reloading
                            this.selectedTasks = [];
                            this.selectAll = false;
                            
                            console.log('Task data loaded:', {
                                tasks: this.treeItems.length,
                                users: Object.keys(this.users).length,
                                projects: Object.keys(this.projects).length,
                                groupBy: this.groupBy,
                                sortBy: this.sortBy
                            });
                        } else {
                            console.error('Failed to load task data');
                        }
                    } catch (error) {
                        console.error('Error loading task data:', error);
                        alert('<?php echo __('Failed to load tasks. Please try again.'); ?>');
                    } finally {
                        this.loading = false;
                        this.initialLoad = false;
                    }
                },
                toggleItem(itemId) {
                    const index = this.expandedItems.indexOf(itemId);
                    if (index === -1) {
                        this.expandedItems.push(itemId);
                    } else {
                        this.expandedItems.splice(index, 1);
                    }
                    // Save expanded state to localStorage
                    this.saveExpandedState();
                },
                expandAll() {
                    const getAllIds = (items) => {
                        let ids = [];
                        items.forEach(item => {
                            ids.push(item.id);
                            if (item.children && item.children.length > 0) {
                                ids = ids.concat(getAllIds(item.children));
                            }
                        });
                        return ids;
                    };
                    this.expandedItems = getAllIds(this.treeItems);
                    // Save expanded state to localStorage
                    this.saveExpandedState();
                },
                collapseAll() {
                    this.expandedItems = [];
                    // Save expanded state to localStorage
                    this.saveExpandedState();
                },
                saveExpandedState() {
                    // Store expanded item IDs in localStorage
                    const stateKey = 'TASKTREE_EXPANDED_ITEMS';
                    localStorage.setItem(stateKey, JSON.stringify(this.expandedItems));
                },
                loadExpandedState() {
                    // Load expanded item IDs from localStorage
                    const stateKey = 'TASKTREE_EXPANDED_ITEMS';
                    const savedState = localStorage.getItem(stateKey);
                    if (savedState) {
                        try {
                            const expandedIds = JSON.parse(savedState);
                            // Only restore IDs that exist in current tree
                            const allTaskIds = this.getAllTaskIds(this.treeItems);
                            this.expandedItems = expandedIds.filter(id => allTaskIds.includes(id));
                        } catch (e) {
                            console.error('Error loading expanded state:', e);
                            this.expandedItems = [];
                        }
                    }
                },
                reloadTasks() {
                    this.loadTaskData(this.selectedProject);
                },
                filterByProject(projectId) {
                    this.loading = true;
                    this.loadTaskData(projectId);
                    
                    // Update URL without page reload
                    const url = new URL(window.location);
                    if (projectId) {
                        url.searchParams.set('project_id', projectId);
                    } else {
                        url.searchParams.delete('project_id');
                    }
                    window.history.pushState({}, '', url);
                },
                changeGroupBy(groupBy) {
                    this.groupBy = groupBy;
                    // Store in localStorage similar to caseProject
                    localStorage.setItem('AJAX_TASK_GROUPBY', groupBy);
                    this.loadTaskData(this.selectedProject);
                },
                changeSorting(sortBy, sortOrder = null) {
                    this.sortBy = sortBy;
                    if (sortOrder) {
                        this.sortOrder = sortOrder;
                    } else {
                        // Toggle sort order if same field
                        this.sortOrder = this.sortOrder === 'ASC' ? 'DESC' : 'ASC';
                    }
                    // Store in cookies similar to caseProject
                    document.cookie = `TASKTREE_SORTBY=${sortBy}; path=/; domain=${window.location.hostname}`;
                    document.cookie = `TASKTREE_SORTORDER=${this.sortOrder}; path=/; domain=${window.location.hostname}`;
                    this.loadTaskData(this.selectedProject);
                }
            },
            mounted() {
                // Load groupBy from localStorage similar to caseProject
                const savedGroupBy = localStorage.getItem('AJAX_TASK_GROUPBY');
                if (savedGroupBy) {
                    this.groupBy = savedGroupBy;
                }
                
                // Load initial data
                this.loadTaskData(this.selectedProject);
            }
        });

        app.component('task-row', TaskRowComponent);
        app.use(vuetify).mount('#task-tree-app');
    });
</script>
