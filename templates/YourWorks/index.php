<style>
    [v-cloak] {
        display: none;
    }

    .v-application--wrap {
        min-height: auto !important;
    }

    .v-list--dense .v-subheader {
        font-size: 0.75rem;
        height: 20px;
        padding: 0 8px;
    }

    .v-list--two-line .v-list-item,
    .v-list-item--two-line {
        min-height: 50px;
    }

    .v-list-item__icon {
        margin-top: 10px !important;
        margin-bottom: 10px !important;
    }

    .v-card__title.project-title {
        color: #71718E;
        font-size: 16px;
        font-weight: 500;
        font-family: 'Inter', sans-serif;
        line-height: 20px;
        cursor: pointer;
    }

    .v-card__subtitle {
        font-family: 'Inter', sans-serif;
        color: #8d8d8e;
        font-size: 12px;
        text-align: left;
    }

    .v-card__text {
        font-size: 12px;
        text-align: left;
        color: #8d8d8e;
        font-family: 'Inter', sans-serif;
    }

    .v-list-item__title.task-title {
        font-size: 14px;
        color: #2983FD;
        text-overflow: ellipsis;
    }

    a {
        text-decoration: none !important;
    }

    .task-count {
        display: block;
        float: right;
        background-color: #eee;
        border-radius: 50px;
        width: fit-content;
        min-width: 20px;
        height: 20px;
        text-align: center;
        user-select: none;
    }

    .v-tab {
        font-family: 'Inter', sans-serif !important;
        text-transform: none !important;
    }

    .v-tab.v-tab--active {
        color: #333 !important;
    }

    .v-tabs-items {
        min-height: 200px;
    }

    .v-subheader {
        font-size: 14px !important;
    }

    .v-list-item.no-tasks {
        font-size: 14px !important;
    }

    .task-details span {
        font-size: 14px !important;
    }

    .v-list-item__subtitle {
        font-size: 14px !important;
    }

    .task-title span {
        cursor: pointer;
    }

    .v-card__title {
        display: block !important;
    }

    .v-list-item {
        width: 50%;
    }

    .v-tooltip__content {
        padding: 2px !important;
        background: rgba(0, 0, 0, 0.9);
    }

    .v-pagination .v-pagination__item.v-pagination__item--active {
        box-shadow: 0px 5px 5px #727270 !important;
        background-color: #fff !important;
        background: var(--primary) !important;
        color: #ffffff !important;
        border-radius: 0 !important;
    }

    .v-pagination .v-pagination__item:hover {
        border-radius: 0 !important;
        box-shadow: 0px 5px 5px #727270 !important;
        background-color: #fff !important;
        background: #fff !important;
        color: #727270 !important;
    }

    .v-pagination .v-pagination__navigation:hover {
        border-radius: 0 !important;
        box-shadow: 0px 5px 5px #727270 !important;
        background-color: #fff !important;
        background: #fff !important;
        color: #727270 !important;
    }

    .v-pagination .v-pagination__item {
        border-radius: 0 !important;
        box-shadow: 0px 0px 0px !important;
        background-color: #fff !important;
        background: #fff !important;
        color: #727270 !important;
    }

    .v-pagination .v-pagination__navigation {
        border-radius: 0 !important;
        box-shadow: 0px 0px 0px !important;
        background-color: #fff !important;
        background: #fff !important;
        color: #727270 !important;
    }

    .v-tab--active:after {
        bottom: 2px;
        border-bottom: 2px solid #2e2e2e;
        left: 50%;
        border: solid transparent;
        content: " ";
        height: 0;
        width: 0;
        position: absolute;
        pointer-events: none;
        border-color: rgba(0, 145, 234, 0);
        border-bottom-color: #2e2e2e;
        border-width: 4px;
        margin-left: -4px;
    }
</style>
<div id="your-works" v-cloak>
    <v-app>
        <v-container fluid>
            <div class="recent-projects">
                <h4><?php echo __('Recent Projects'); ?></h4>
                <v-row no-gutters v-if="projectsLoading">
                    <v-col v-for="i in projectsPerPage" :key="i" sm="2">
                        <v-skeleton-loader type="card" class="ma-1 rounded-lg" v-if="projectsLoading" height="140"></v-skeleton-loader>
                    </v-col>
                </v-row>
                <v-row no-gutters v-else>
                    <v-col v-for="project in recentProjects" :key="project.id" sm="2">
                        <v-hover v-slot="{ hover }">
                            <v-card class="ma-1 rounded-lg" :elevation="hover ? 3 : 1" outlined height="140">
                                <v-card-title class="project-title text-truncate" @click="goToProject(project)">
                                    <v-tooltip bottom>
                                        <template v-slot:activator="{ on }">
                                            <span v-on="on">{{ project.name }}</span>
                                        </template>
                                        {{ project.name }}
                                    </v-tooltip>
                                </v-card-title>
                                <v-card-subtitle class="text-truncate">({{ project.short_name.toUpperCase() }}) {{ project.type }} <?php echo __('Project'); ?></v-card-subtitle>
                                <v-card-text>
                                    <div>
                                        <span><?php echo __('Open Tasks'); ?></span>
                                        <span class="task-count">{{ +project.opened_task }}</span>
                                    </div>
                                    <div>
                                        <span><?php echo __('Closed Tasks'); ?></span>
                                        <span class="task-count">{{ +project.closed_tasks }}</span>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-hover>
                    </v-col>
                </v-row>
            </div>
            <div class="mt-5">
                <template>
                    <v-tabs @change="tabChanged">
                        <v-tab v-for="(tab, tabindex) in tabs" :key="tabindex">
                            {{ tab.title }}
                        </v-tab>
                        <v-tab-item v-for="(tab, tabindex) in tabs" :key="tabindex" :transition="false">
                            <v-container fluid>
                                <v-row no-gutters>
                                    <v-col>
                                        <v-list two-line subheader dense flat>
                                            <v-subheader v-if="tab.title=='Todo'">Today</v-subheader>
                                            <template v-if="tasksLoading">
                                                <v-skeleton-loader type="list-item-two-line" v-for="ii in 3" :key="'ske'+ii"></v-skeleton-loader>
                                            </template>
                                            <template v-else>
                                                <template v-if="todayTasks && todayTasks.length">
                                                    <v-list-item v-for="(todayTask, index) in todayTasks" :key="todayTask.id">
                                                        <v-list-item-content>
                                                            <v-list-item-title class="task-title">
                                                                <v-tooltip bottom>
                                                                    <template v-slot:activator="{ on }">
                                                                        <span v-on="on" @click="goToTask(todayTask)">#{{ todayTask.case_no }}: {{ todayTask.title }}</span>
                                                                    </template>
                                                                    #{{ todayTask.case_no }}: {{ todayTask.title }}
                                                                </v-tooltip>
                                                            </v-list-item-title>
                                                            <v-list-item-subtitle>{{ todayTask.Projects.name }}</v-list-item-subtitle>
                                                            <v-list-item-subtitle>
                                                                <span class="task-details">
                                                                    <span><?php echo __('Task Type'); ?>: {{todayTask.Types.name}} </span>
                                                                    <span>Estimated Hours: {{ formatHour(todayTask.estimated_hours) }}</span>
                                                                    <span v-if="tab.title=='Completed'">Spent Time: {{ formatHour(todayTask.LogTimes.total_hours) }}</span>
                                                                </span>
                                                            </v-list-item-subtitle>
                                                        </v-list-item-content>
                                                    </v-list-item>
                                                    <template v-if="taskCount > 10">
                                                        <div class="text-center">
                                                            <v-pagination v-model="taskPage" :length="taskPageLength" :total-visible="7" @input="paginateTasks"></v-pagination>
                                                        </div>
                                                    </template>
                                                </template>
                                                <template v-else>
                                                    <v-list-item class="no-tasks">
                                                        <?php echo __('No Tasks'); ?>
                                                    </v-list-item>
                                                </template>
                                            </template>
                                        </v-list>
                                        <v-list two-line subheader dense flat v-if="tab.title=='Todo' && taskCount < 10">
                                            <v-subheader>Upcoming</v-subheader>
                                            <template v-if="tasksLoading">
                                                <v-skeleton-loader type="list-item-two-line" v-for="ii in 3" :key="'ske'+ii"></v-skeleton-loader>
                                            </template>
                                            <template v-else>
                                                <template v-if="nextUpcomingTasks && nextUpcomingTasks.length">
                                                    <v-list-item v-for="(nextUpcomingTask, index) in nextUpcomingTasks" :key="nextUpcomingTask.id">
                                                        <v-list-item-content>
                                                            <v-list-item-title class="task-title">
                                                                <v-tooltip bottom>
                                                                    <template v-slot:activator="{ on }">
                                                                        <span v-on="on" @click="goToTask(nextUpcomingTask)">#{{ nextUpcomingTask.case_no }}: {{ nextUpcomingTask.title }}</span>
                                                                    </template>
                                                                    #{{ nextUpcomingTask.case_no }}: {{ nextUpcomingTask.title }}
                                                                </v-tooltip>
                                                            </v-list-item-title>
                                                            <v-list-item-subtitle>{{ nextUpcomingTask?.Projects?.name  }} </v-list-item-subtitle>
                                                            <v-list-item-subtitle>
                                                                <span class="task-details">
                                                                    <span><?php echo __('Task Type'); ?>: {{nextUpcomingTask?.Types?.name}} </span>
                                                                    <span>Estimated Hours: {{ formatHour(nextUpcomingTask.estimated_hours) }}</span>
                                                                </span>
                                                            </v-list-item-subtitle>
                                                        </v-list-item-content>
                                                    </v-list-item>
                                                </template>
                                                <template v-else>
                                                    <v-list-item class="no-tasks">
                                                        <?php echo __('No Tasks'); ?>
                                                    </v-list-item>
                                                </template>
                                            </template>
                                        </v-list>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-tab-item>
                    </v-tabs>
                </template>
            </div>
        </v-container>
    </v-app>
</div>
<script type="text/javascript" src="/js/moment.js"></script>
<script>
    $(document).ready(function() {
       var vue_obj = new Vue({
            el: '#your-works',
            vuetify: new Vuetify({
                icons: {
                    iconfont: 'md'
                }
            }),
            data() {
                return {
                    tabs: [{
                            title: 'Todo',
                            statusFilter: 'todo',
                        },
                        {
                            title: 'In Progress',
                            statusFilter: 'in_progress',
                        },
                        {
                            title: 'Completed',
                            statusFilter: 'completed',
                        },
                        {
                            title: 'Favourites',
                            statusFilter: 'favourites',
                        },
                    ],
                    recentProjects: [],
                    statusFilter: '',
                    todayTasks: {},
                    nextUpcomingTasks: {},
                    tasks: [],
                    projectsLoading: false,
                    tasksLoading: false,
                    projectsPerPage: 5,
                    taskPage: 1,
                    taskCount: 0,
                    upcommingTaskCount: 0,
                    taskPageLength: 1,
                    upcommingTaskPage: 1,
                    upcommingTaskPageLength: 1,
                    pageLimt: 10
                };
            },
            watch: {
                taskPage() {
                    this.getYourWorks();
                },
                upcommingTaskPage() {
                    this.getYourWorks();
                },
            },
            computed: {},
            methods: {
                paginateTasks(page) {},
                paginateUpcommingTask(page) {},
                formatHour(seconds) {
                    return new Date(seconds * 1000).toISOString().substring(11, 16);
                },
                goToProject(project) {
                    let uniq_id = project.uniq_id;
                    remember_filters('ALL_PROJECT', '');
                    resetAllFilters('all', 1);
                    $('#projFil').val(uniq_id);
                    axios.post(HTTP_ROOT + 'projects/updateDateVisited', {
                            uniq_id
                        })
                        .then((response) => {
                            let res = response.data;
                            if (res.status == 'success') {
                                window.location.href = HTTP_ROOT + "dashboard/#tasks";
                            } else {
                                showTopErrSucc('error', _('Oops! You are not a member of the project. Please add yourself as a member of this project.'));
                            }
                        })
                        .catch((error) => {});
                },
                goToTask(task) {
                    let uniq_id = task.uniq_id;
                    easycase.ajaxCaseDetails(uniq_id, 'case', 0);
                },
                tabChanged(tabindex) {
                    let current_tab = null;
                    if (tabindex >= 0 && tabindex < this.tabs.length) {
                        current_tab = this.tabs[tabindex];
                    }
                    if (current_tab) {
                        this.statusFilter = current_tab.statusFilter;
                        this.taskPage = 1;
                        this.getYourWorks();
                    }
                },
                getYourWorks(params) {
                    this.tasksLoading = true;
                    this.todayTasks = [];
                    this.nextUpcomingTasks = [];
                    let statusfilter = this.statusFilter;
                    let page = this.taskPage;
                    let page_upcomming = this.upcommingTaskPage;
                    let page_limt = this.pageLimt;
                    axios.post(HTTP_ROOT + '/your-works', {
                            statusfilter,
                            page,
                            page_upcomming,
                            page_limt
                        })
                        .then((response) => {
                            let data = response.data;
                            this.todayTasks = data.today_task;
                            this.nextUpcomingTasks = data.next_upcoming_tasks;
                            this.taskCount = data.today_task_cnt;
                            this.taskPageLength = Math.ceil(data.today_task_cnt / this.pageLimt);
                            this.upcommingTaskCount = data.next_upcoming_task_cnt;
                            this.upcommingTaskPageLength = Math.ceil(data.next_upcoming_task_cnt / this.pageLimt);
                        })
                        .catch((error) => {
                            this.todayTasks = [];
                            this.nextUpcomingTasks = [];
                        })
                        .finally(() => {
                            this.tasksLoading = false;
                        });
                },
                getRecentProjects() {
                    this.projectsLoading = true;
                    axios.post(HTTP_ROOT + '/recent-projects')
                        .then((response) => {
                            let data = response.data;
                            this.recentProjects = data.Projects;
                        })
                        .catch((error) => {})
                        .finally(() => {
                            this.projectsLoading = false;
                        });
                }
            },
            mounted() {
                this.getRecentProjects();
                routerHideShow();
            },
            created() {},
        });
        $(document).on('your-works:reload', function() {
            vue_obj.getYourWorks();
        });
    });

    function routerHideShow() {
        if (typeof easycase !== undefined) {
            easycase.routerHideShow('your-works');
            $('.top_cmn_breadcrumb').html('<a href="javascript:void(0)" onClick="showHideTopNav();"><i class="material-icons">&#xF075;</i> ' + _('Your Works') + '</a>');
            $('.pfl-icon-dv').hide();
            $('.kanban_breadcrumb_v2').removeClass('active-list');
            $('.kanban_breadcrumb').removeClass('active-list');
            $('.Your-work-breadcrumb').addClass('active-list');
        }
    }
    $(document).on('click', '.close', function() {
        $(document).trigger("your-works:reload");
    }); 
</script>
