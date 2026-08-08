<script type="text/x-template" id="task-row-template">
    <div>
        <div :class="[getRowClass(item.type_id), 'task-row']">
            <!-- Expand/Collapse Button -->
            <div class="col-expand">
                <v-btn 
                    v-if="hasChildren"
                    @click="toggleExpanded"
                    icon
                    size="x-small"
                    variant="text"
                    class="expand-button"
                >
                    <v-icon size="10px" :style="{ transform: isExpanded ? 'rotate(90deg)' : 'rotate(0deg)', transition: 'transform 0.2s' }">
                        mdi-chevron-right
                    </v-icon>
                </v-btn>
                <div v-else style="width: 24px; height: 24px; display: inline-block;"></div>
            </div>

            <!-- Actions -->
            <div class="col-actions">
                <div class="dropdown" v-if="item.type_id !== 13 && item.type_id !== 15">
                    <a class="dropdown-toggle action-menu-toggle" href="javascript:void(0);" @click.stop="toggleActionMenu">
                        <v-icon size="small">mdi-dots-vertical</v-icon>
                    </a>
                    <ul class="dropdown-menu action-menu" :class="{ show: showActionMenu }" @click.stop>
                        <?php if ($this->Format->isAllowed('Edit Task', $roleAccess) || $this->Format->isAllowed('Edit All Task', $roleAccess)) { ?>
                        <li @click="editTask(item)">
                            <a href="javascript:void(0);">
                                <i class="material-icons">edit</i>
                                <?php echo __('Edit'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        
                        <?php if ($this->Format->isAllowed('Change Status of Task', $roleAccess)) { ?>
                        <li v-if="item.legend !== 3" @click="closeTask(item)">
                            <a href="javascript:void(0);">
                                <i class="material-icons">done</i>
                                <?php echo __('Close'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        
                        <?php if ($this->Format->isAllowed('Reply on Task', $roleAccess)) { ?>
                        <li @click="replyTask(item)">
                            <a href="javascript:void(0);">
                                <i class="material-icons">{{ item.legend === 3 ? 'refresh' : 'reply' }}</i>
                                {{ item.legend === 3 ? '<?php echo __('Re-open'); ?>' : '<?php echo __('Reply'); ?>' }}
                            </a>
                        </li>
                        <?php } ?>
                        
                        <?php if ($this->Format->isAllowed('Create Task', $roleAccess)) { ?>
                        <li v-if="item.legend !== 3 && !item.is_sub_sub_task" @click="createSubtask(item)">
                            <a href="javascript:void(0);">
                                <i class="material-icons">subdirectory_arrow_right</i>
                                <?php echo __('Create Subtask'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        
                        <?php if ($this->Format->isAllowed('Manual Time Entry', $roleAccess)) { ?>
                        <li @click="timeEntry(item)">
                            <a href="javascript:void(0);">
                                <i class="material-icons">schedule</i>
                                <?php echo __('Time Entry'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        
                        <?php // OSS edition: task timer removed ?>
                        <?php if ($this->Format->isAllowed('Change Other Details of Task', $roleAccess)) { ?>
                        <li @click="copyTask(item)">
                            <a href="javascript:void(0);">
                                <i class="material-icons">content_copy</i>
                                <?php echo __('Copy'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        
                        <?php if ($this->Format->isAllowed('Move to Project', $roleAccess)) { ?>
                        <li v-if="item.isactive === 1" @click="moveToProject(item)">
                            <a href="javascript:void(0);">
                                <i class="material-icons">drive_file_move</i>
                                <?php echo __('Move to Project'); ?>
                            </a>
                        </li>
                        <?php } ?>
                        
                        <?php if (SES_TYPE == 1 || SES_TYPE == 2 || $this->Format->isAllowed('Delete All Task', $roleAccess)) { ?>
                        <li class="divider"></li>
                        <li @click="deleteTask(item)">
                            <a href="javascript:void(0);" style="color: #dc3545;">
                                <i class="material-icons">delete</i>
                                <?php echo __('Delete'); ?>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <!-- Number -->
            <div class="col-number">
                <span class="task-number">{{ item.case_no }}</span>
            </div>

            <!-- Title -->
            <div class="col-title">
                <div class="task-title-wrapper">
                    <span 
                        class="tasktree-type-icon"
                        :class="getTypeClass(item.type_id)"
                    ></span>
                    <div style="flex: 1;">
                        <div class="task-title">{{ item.title }}</div>
                        <div class="task-meta">
                            <v-chip 
                                color="#555"
                                size="x-small"
                                variant="flat"
                                class="type-chip ml-1"
                            >
                                {{ item.typeName || getTypeName(item.type_id) }}
                            </v-chip>
                        </div>
                        <div class="created-txt">
                            {{ getCreatedUpdatedText(item) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned to -->
            <div class="col-assigned">
                <div class="user-info">
                    <v-avatar size="20" :color="assignedUser.photo ? '' : 'primary'">
                        <template v-if="assignedUser.photo">
                            <img :src="assignedUser.photo" :alt="assignedUser.name">
                        </template>
                        <template v-else>
                            <span class="text-caption">{{ getUserInitials(assignedUser.name) }}</span>
                        </template>
                    </v-avatar>
                    <span class="text-body-2 ml-1">{{ item.Assigned || item.asgnShortName || assignedUser.short_name || assignedUser.name }}</span>
                </div>
            </div>

            <!-- Priority -->
            <div class="col-priority">
                <v-chip
                    :style="{ backgroundColor: priorityColor, color: '#000' }"
                    size="small"
                    class="priority-chip"
                >
                    {{ priorityText }}
                </v-chip>
            </div>

            <!-- Updated -->
            <div class="col-updated">
                <span class="text-body-2">{{ getUpdatedTime(item.dt_created) }}</span>
            </div>

            <!-- Status -->
            <div class="col-status">
                <v-chip 
                    :style="{ backgroundColor: statusColor, color: '#fff' }" 
                    size="small" 
                    class="status-chip"
                    :title="statusText + (statusProgress ? ' (' + statusProgress + '%)' : '')"
                >
                    {{ statusText }}
                </v-chip>
            </div>

            <!-- Due Date -->
            <div class="col-due">
                <span class="text-body-2 text-medium-emphasis">
                    {{ item.due_date_formatted || formatDate(item.due_date) }}
                </span>
            </div>
        </div>
        
        <!-- Children -->
        <div v-if="isExpanded && hasChildren">
            <task-row
                v-for="child in item.children"
                :key="child.id"
                :item="child"
                :level="level + 1"
                :expanded-items="expandedItems"
                :users="users"
                @toggle="$emit('toggle', $event)"
            ></task-row>
        </div>
    </div>
</script>
