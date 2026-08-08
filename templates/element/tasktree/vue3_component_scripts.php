<?php // TaskRow component ?>
const TaskRowComponent = {
    name: 'TaskRow',
    props: ['item', 'level', 'expandedItems', 'users'],
    emits: ['toggle'],
    template: '#task-row-template',
    data() {
        return {
            showActionMenu: false,
            // Localized strings
            locale: {
                priority: {
                    high: '<?php echo __('High'); ?>',
                    medium: '<?php echo __('Medium'); ?>',
                    low: '<?php echo __('Low'); ?>'
                },
                status: {
                    open: '<?php echo __('Open'); ?>',
                    new: '<?php echo __('New'); ?>',
                    inProgress: '<?php echo __('In Progress'); ?>',
                    closed: '<?php echo __('Closed'); ?>',
                    resolved: '<?php echo __('Resolved'); ?>'
                },
                user: {
                    unassigned: '<?php echo __('Unassigned'); ?>',
                    na: '<?php echo __('N/A'); ?>'
                },
                date: {
                    noDueDate: '<?php echo __('No due date'); ?>',
                    overdue: '<?php echo __('Overdue'); ?>',
                    today: '<?php echo __('Today'); ?>',
                    tomorrow: '<?php echo __('Tomorrow'); ?>',
                    days: '<?php echo __('days'); ?>',
                    justNow: '<?php echo __('just now'); ?>',
                    minsAgo: '<?php echo __('mins ago'); ?>',
                    hoursAgo: '<?php echo __('hours ago'); ?>',
                    daysAgo: '<?php echo __('days ago'); ?>'
                },
                actions: {
                    created: '<?php echo __('Created'); ?>',
                    updated: '<?php echo __('Updated'); ?>',
                    by: '<?php echo __('by'); ?>',
                    on: '<?php echo __('on'); ?>'
                },
                confirm: {
                    deleteTask: '<?php echo __('Are you sure you want to delete this task?'); ?>'
                }
            }
        };
    },
    computed: {
        hasChildren() {
            return this.item.children && this.item.children.length > 0;
        },
        isExpanded() {
            return this.expandedItems.includes(this.item.id);
        },
        isRTL() {
            // Detect RTL from the parent element's direction
            return document.getElementById('task-tree-app')?.getAttribute('dir') === 'rtl';
        },
        assignedUser() {
            if (this.item.assign_to) {
                return this.users[this.item.assign_to] || { name: this.locale.user.unassigned, short_name: this.locale.user.na };
            }
            return { name: this.locale.user.unassigned, short_name: this.locale.user.na };
        },
        priorityText() {
            const priorities = {
                0: this.locale.priority.high,
                1: this.locale.priority.medium,
                2: this.locale.priority.low
            };
            return priorities[this.item.priority] || this.locale.priority.low;
        },
        priorityColor() {
            const colors = {
                0: '#f9b9b9',      // High - light red/pink
                1: '#c8f7ddb6',    // Medium - light green
                2: '#f7efb0'       // Low - light yellow
            };
            return colors[this.item.priority] || '#f7efb0';
        },
        statusText() {
            // Check if custom status exists (matching case_project_v2.php logic)
            if (this.item.custom_status_id && this.item.CustomStatus) {
                return this.item.CustomStatus.name;
            }
            
            // Otherwise use legend-based status
            const statuses = {
                0: this.locale.status.open,
                1: this.locale.status.new,
                2: this.locale.status.inProgress,
                3: this.locale.status.closed,
                4: this.locale.status.inProgress,
                5: this.locale.status.resolved
            };
            return statuses[this.item.legend] || this.locale.status.open;
        },
        statusColor() {
            // Check if custom status exists (matching case_project_v2.php logic)
            console.log('Computing statusColor for item:', this.item.CustomStatus);
            if (this.item.custom_status_id && this.item.CustomStatus && this.item.CustomStatus.color) {
                const color = this.item.CustomStatus.color;
                // Add # prefix if not already present
                return color.startsWith('#') ? color : '#' + color;
            }
            
            // Otherwise use legend-based colors
            const colors = {
                0: '#f44336',      // red - Open
                1: '#ff9800',      // orange - New
                2: '#2196f3',      // blue - In Progress
                3: '#4caf50',      // green - Closed
                4: '#2196f3',      // blue - In Progress
                5: '#00bcd4'       // cyan - Resolved
            };
            return colors[this.item.legend] || '#f44336';
        },
        statusProgress() {
            // Return progress percentage for status
            if (this.item.custom_status_id && this.item.CustomStatus) {
                return this.item.CustomStatus.progress || 0;
            }
            return this.item.completed_task || 0;
        }
    },
    methods: {
        toggleExpanded() {
            this.$emit('toggle', this.item.id);
        },
        getRowClass(typeId) {
            const classes = {
                13: 'epic-row',
                15: 'feature-row',
                14: 'story-row'
            };
            return classes[typeId] || 'task-row-default';
        },
        getTypeIcon(typeId) {
            // Return Material Icons for standard task types matching custom.css
            const icons = {
                1: 'adb',                    // Type 1
                2: 'developer_mode',         // Type 2
                3: 'equalizer',              // Type 3
                4: 'youtube_searched_for',   // Type 4
                5: 'Q',                      // Type 5
                6: 'flip',                   // Type 6
                7: 'build',                  // Type 7
                8: 'label',                  // Type 8 (default/Development)
                9: 'new_releases',           // Type 9
                10: 'update',                // Type 10
                11: 'lightbulb_outline',     // Type 11
                12: 'description',           // Type 12
                13: 'widgets',               // Epic
                14: 'menu_book',             // Story
                15: 'extension'              // Feature
            };
            return icons[typeId] || 'label'; // Default to label icon
        },
        getTypeClass(typeId) {
            // Return CSS class for ttype_global sprite icons from custom.css
            const classes = {
                13: 'tt_epic',          // Epic - background-position: 0 -280px
                14: 'tt_story',         // Story - background-position: 0 -304px
                15: 'tt_feature',       // Feature (if defined in custom.css)
                // Add more mappings based on your types table
                1: 'tt_bug',
                2: 'tt_development',
                3: 'tt_enhancement',
                4: 'tt_idea',
                5: 'tt_maintenance',
                6: 'tt_quality-assurance',
                7: 'tt_release',
                8: 'tt_development',    // Default development
                9: 'tt_research-n-do',
                10: 'tt_update',
                11: 'tt_unit-testing',
                12: 'tt_change-request'
            };
            return classes[typeId] || 'tt_development'; // Default to development
        },
        getTypeColor(typeId) {
            // Use consistent color for all task type icons matching custom.css
            return '#555';  // All task type icons use this color from .task_type ul li:before
        },
        getTypeName(typeId) {
            // Use the typeName from the query if available
            if (this.item.typeName) {
                return this.item.typeName;
            }
            const types = {
                13: 'Epic',
                15: 'Feature',
                14: 'Story'
            };
            return types[typeId] || 'Development';
        },
        getHierarchyColor(hierarchyLabel) {
            // Color coding for hierarchy levels
            const colors = {
                'Epic': '#9C27B0',      // Purple
                'Feature': '#FF9800',   // Orange
                'Story': '#2196F3',     // Blue
                'Sub Task': '#4CAF50',  // Green
                'Task': '#607D8B'       // Blue Gray
            };
            return colors[hierarchyLabel] || '#555';
        },
        formatDate(dateString) {
            if (!dateString) return this.locale.date.noDueDate;
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = date - now;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays < 0) {
                return this.locale.date.overdue;
            } else if (diffDays === 0) {
                return this.locale.date.today;
            } else if (diffDays === 1) {
                return this.locale.date.tomorrow;
            } else if (diffDays < 7) {
                return `${diffDays} ${this.locale.date.days}`;
            } else {
                return date.toLocaleDateString();
            }
        },
        getUpdatedTime(updatedDate) {
            if (!updatedDate) return this.locale.date.justNow;
            const date = new Date(updatedDate);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return this.locale.date.justNow;
            if (diffMins < 60) return `${diffMins} ${this.locale.date.minsAgo}`;
            if (diffHours < 24) return `${diffHours} ${this.locale.date.hoursAgo}`;
            if (diffDays < 7) return `${diffDays} ${this.locale.date.daysAgo}`;
            return date.toLocaleDateString();
        },
        getUserInitials(name) {
            if (!name) return 'NA';
            const parts = name.split(' ');
            if (parts.length >= 2) {
                return (parts[0][0] + parts[1][0]).toUpperCase();
            }
            return name.substring(0, 2).toUpperCase();
        },
        getCreatedUpdatedText(item) {
            // Use pre-formatted date from server (similar to case_project_v2.php)
            const userName = item.usrShortName || '<?php echo __('Unknown'); ?>';
            const hasBeenUpdated = item.updated_by && item.dt_created;
            const action = hasBeenUpdated ? this.locale.actions.updated : this.locale.actions.created;
            
            // Use the formatted date from server (updtedCapDt or fbstyle)
            const dateText = item.updtedCapDt || item.fbstyle || '';
            
            if (!dateText) {
                return `${action} ${this.locale.actions.by} ${userName}`;
            }
            
            // Format similar to case_project_v2.php: "Updated by John on Nov 2, 2025"
            // The preposition "on" is only used when the date doesn't contain "Today" or "Y'day"
            const preposition = (dateText.includes(this.locale.date.today) || dateText.includes('Y\'day') || dateText.includes('ago')) ? '' : `${this.locale.actions.on} `;
            
            return `${action} ${this.locale.actions.by} ${userName} ${preposition}${dateText}`;
        },
        toggleActionMenu() {
            this.showActionMenu = !this.showActionMenu;
        },
        editTask(item) {
            this.showActionMenu = false;
            if (item.type_id == 13) {
                // Edit Epic
                window.editepic(item.uniq_id, item.pjUniqid, item.pjname);
            } else {
                // Edit Task
                window.editask(item.uniq_id, item.pjUniqid, item.pjname);
            }
        },
        closeTask(item) {
            this.showActionMenu = false;
            window.setCloseCase(item.id, item.case_no, item.uniq_id);
        },
        replyTask(item) {
            this.showActionMenu = false;
            // TODO: Implement reply functionality
            console.log('Reply to task:', item);
        },
        createSubtask(item) {
            this.showActionMenu = false;
            window.addSubtaskPopup(item.pjUniqid, item.id, item.project_id, item.uniq_id, item.title);
        },
        timeEntry(item) {
            this.showActionMenu = false;
            window.createlog(item.id, escape(item.title));
        },
        startTimer(item) {
            this.showActionMenu = false;
            window.startTimer(item.id, escape(item.title), item.uniq_id, item.pjUniqid, escape(item.pjname));
        },
        copyTask(item) {
            this.showActionMenu = false;
            window.copytask(item.uniq_id, item.id, item.case_no, item.project_id, item.pjname);
        },
        moveToProject(item) {
            this.showActionMenu = false;
            // TODO: Implement move to project
            console.log('Move to project:', item);
        },
        deleteTask(item) {
            this.showActionMenu = false;
            if (confirm(this.locale.confirm.deleteTask)) {
                // TODO: Implement delete task
                console.log('Delete task:', item);
            }
        }
    },
    mounted() {
        // Close action menu when clicking outside
        document.addEventListener('click', () => {
            if (this.showActionMenu) {
                this.showActionMenu = false;
            }
        });
    }
};

