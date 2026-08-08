<?php

use Migrations\AbstractSeed;

class HelpsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'subject_id' => 1,
                    'title' => 'How to create a new Task?',
                    'description' => '<li>Click on "Create Task" button or click Task from "+Add" menu. This will open up create task page. Enter Title of the task.</li><li>By default, current project selected in project list box, you can always choose different project. Then choose Task Type, Priority and Assigned to from the respective list box.</li><li>Attach file or link your files from Google Drive or DropBox.</li><li>To create a detailed task: Set the due date and choose which task group it will be belong to, Enter estimated hours, choose start time and end time from a list box, enter break time  and spent hours will be calculated automatically.</li><li>Select the users from drop down, you want to notify the new task details via email notification<a href="http://www.easyagile.us/img/help/task/creat_task.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            1 =>
                [
                    'id' => 2,
                    'subject_id' => 1,
                    'title' => 'Can I see tasks of all projects?',
                    'description' => '<li>Please select an "All" from the project drop down and you can see all the tasks in a single page.<a href="http://www.easyagile.us/img/help/task/creat_seeall.jpg"><img src="http://www.easyagile.us/img/help/task/creat_seeall.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            2 =>
                [
                    'id' => 3,
                    'subject_id' => 1,
                    'title' => 'How to filter Tasks?',
                    'description' => '<li>Click on the "Filters" <span class="db-filter-icon_help"></span> present at the top right corner of "Task Page".</li><li>Click on the filter type to get the filter options.</li><li>You can select multiple filters there.</li><li>You can close the filters one by one or reset them all.<a href="http://www.easyagile.us/img/help/task/creat_task_filter.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_filter.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            3 =>
                [
                    'id' => 5,
                    'subject_id' => 1,
                    'title' => 'How to view Task details and reply on a task?',
                    'description' => '<li>Please click on a task to view the details.</li><li>Put the task details, where you can specify Status (as In Progress or Resolve or Close), Assign to, Set the priority, start time, end time, break time and select the option "Is Billable?" while replying on a Task.</li><li>Share your documents (if required) using your Google Drive or DropBox account</li><li>Select concerned members to send email notification and hit "Post" to reply.<a href="http://www.easyagile.us/img/help/task/creat_task_detail.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_detail.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            4 =>
                [
                    'id' => 6,
                    'subject_id' => 1,
                    'title' => 'How to edit a Task?',
                    'description' => '<li>There are two ways you can edit a task;
<ol><li>On the task listing page, click the drop down icon <span class="sett_help"></span> and select edit <span class="act_edit_task_help"></span> to edit the task.</li></ol><a href="http://www.easyagile.us/img/help/task/creat_task_edit1.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_edit1.jpg"/></a></li><li>In the task detail page, on the top right hand corner you will be able to see the edit icon <span class="act_edit_task_help"></span>. Click on it to edit the task.<a href="http://www.easyagile.us/img/help/task/creat_task_edit2.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_edit2.jpg"/></a></li><li>Note: A task can only be modified if the status is "NEW" <color code: red> and the user who would have created the task.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            5 =>
                [
                    'id' => 7,
                    'subject_id' => 1,
                    'title' => 'Can I resolve or close a Task without any reply?',
                    'description' => '<li>Sure, there is an option icon <span class="sett_help"></span> on left side of each Task, choose from option to resolve or close the task.</li><li>Also, you can check multiple tasks and do the same by selecting Close or Resolve on the top of the list.<a href="http://www.easyagile.us/img/help/task/creat_task_resolve.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_resolve.jpg"/></a></li><li>You can resolve or close task from the task detail page.<a href="http://www.easyagile.us/img/help/task/creat_task_resolve_detail.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_resolve_detail.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            6 =>
                [
                    'id' => 8,
                    'subject_id' => 1,
                    'title' => 'How to change the "task type", "assign to", & "due date" on the task listing page?',
                    'description' => '<li>You can change task type from the left side option of each task.</li><li>You can change assign to and due date on the right side of each task listing.<a href="http://www.easyagile.us/img/help/task/creat_task_type.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_type.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            7 =>
                [
                    'id' => 9,
                    'subject_id' => 1,
                    'title' => 'How can I set different tabs above task listing?',
                    'description' => '<li>Click "+" on the tab section, Select/Deselect the checkboxes and click save.<a href="http://www.easyagile.us/img/help/task/creat_task_tab1.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_tab1.jpg"/></a></li><li>You can view the tabs on the tab section.<a href="http://www.easyagile.us/img/help/task/creat_task_tab2.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_tab2.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            8 =>
                [
                    'id' => 10,
                    'subject_id' => 1,
                    'title' => 'How to archive or delete a task?',
                    'description' => '<li>Click the option icon <span class="sett_help"></span> on the task listing; select "Archive" to archive the task.<li>You can later restore or remove the archive permanently from the "Archive" icon <span class="act_arcv_task_help"></span> in the left.<a href="http://www.easyagile.us/img/help/task/creat_task_delete.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_delete.jpg"/></a><a href="http://www.easyagile.us/img/help/task/creat_task_archive.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_archive.jpg"/></a></li><li>Note: Only new tasks created by you can be archived.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            9 =>
                [
                    'id' => 11,
                    'subject_id' => 2,
                    'title' => 'Can I upload Files without creating a task?',
                    'description' => '<li>Now we have only option to upload files while creating or replying on a task.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            10 =>
                [
                    'id' => 12,
                    'subject_id' => 2,
                    'title' => 'How can I see all Files of my projects?',
                    'description' => '<li>Click "<strong>Files</strong>" present on the left panel, Here, all uploaded and shared files of the current project are listed.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            11 =>
                [
                    'id' => 13,
                    'subject_id' => 2,
                    'title' => 'Can I archive or delete a file of a task?',
                    'description' => '<li>Yes, Click on the archive icon on left side of each file to archive a file of task.</li>
<li>Yes, You can delete a file of task from the archive section.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            12 =>
                [
                    'id' => 14,
                    'subject_id' => 2,
                    'title' => 'Can I download all Files in a zip?',
                    'description' => '<li>You can download them one by one.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            13 =>
                [
                    'id' => 15,
                    'subject_id' => 3,
                    'title' => 'How to create a new Project?',
                    'description' => '<li>Click "<strong>Create Project</strong>", Enter a Project Name, Short Name, Email IDs of users and hit "<strong>Create</strong>".</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            14 =>
                [
                    'id' => 16,
                    'subject_id' => 3,
                    'title' => 'How to add users to Projects?',
                    'description' => '<li>Click on the "Projects" menu on the left panel for going to the projects listing.</li>
<li>Click "<strong>Add User</strong>", Select the users and hit Add.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            15 =>
                [
                    'id' => 17,
                    'subject_id' => 3,
                    'title' => 'How to remove users from Projects?',
                    'description' => '<li>Click "Remove User" on a project, Select users and hit Remove.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            16 =>
                [
                    'id' => 18,
                    'subject_id' => 3,
                    'title' => 'Can I hide or disable a Project?',
                    'description' => '<li>Click "<strong>Disable</strong>" on a project and hit "OK" in the pop-up for disabling the project.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 1,
                ],
            17 =>
                [
                    'id' => 19,
                    'subject_id' => 3,
                    'title' => 'Can I delete a Project?',
                    'description' => '<li>In project listing page, Click the "<strong>Inactive</strong>" tab. Click "<strong>Delete</strong>" on a project and hit "OK" in the pop-up.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 1,
                ],
            18 =>
                [
                    'id' => 20,
                    'subject_id' => 3,
                    'title' => 'Can I move a task from one project to another?',
                    'description' => '<li>Yes</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 1,
                ],
            19 =>
                [
                    'id' => 21,
                    'subject_id' => 4,
                    'title' => 'How to invite a new User to Orangescrum?',
                    'description' => '<li>Click "<strong>Invite User</strong>", enter user\'s Email ID and hit "<strong>Add</strong>" to send email invitation to the users.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 1,
                ],
            20 =>
                [
                    'id' => 22,
                    'subject_id' => 4,
                    'title' => 'Can a User be in multiple account in orangescrum?',
                    'description' => '<li><strong>Yes</strong></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 1,
                ],
            21 =>
                [
                    'id' => 23,
                    'subject_id' => 4,
                    'title' => 'How to restrict an User to access?',
                    'description' => '<li>In the users listing page, Click "<strong>Disable</strong>" on a user and hit "OK" in the pop-up.',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 1,
                ],
            22 =>
                [
                    'id' => 24,
                    'subject_id' => 4,
                    'title' => 'How to delete an User from my account?',
                    'description' => '<li>In users listing page, Click the "<strong>Invited</strong>" tab. Click "<strong>Delete</strong>" on a user and hit "OK" in the pop-up.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 1,
                ],
            23 =>
                [
                    'id' => 25,
                    'subject_id' => 4,
                    'title' => 'How to assign projects to Users?',
                    'description' => '<li>In users listing page, Click the "<strong>Active</strong>" tab. Click "<strong>Assign Project</strong>" on a user, select projects and hit "<strong>Assign</strong>".</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 1,
                ],
            24 =>
                [
                    'id' => 26,
                    'subject_id' => 4,
                    'title' => 'How to remove projects from Users?',
                    'description' => '<li>In users listing page, Click the "<strong>Active/Disabled</strong>" tab. Click "<strong>Remove Project</strong>" on a user, select projects and hit "<strong>Remove</strong>".</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 1,
                ],
            25 =>
                [
                    'id' => 31,
                    'subject_id' => 6,
                    'title' => 'Can I see the Bug reports?',
                    'description' => '<li>Click on <strong>Dashboard</strong>, go to semi circle pie chart, select <strong>bug</strong> from list box.</li> <li>User can view status and statistics of bugs.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            26 =>
                [
                    'id' => 32,
                    'subject_id' => 6,
                    'title' => 'Can I get a usage report?',
                    'description' => '<li>Yes, Click "<strong>Analytics</strong>" and click "<strong>Usage Report</strong>".</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            27 =>
                [
                    'id' => 33,
                    'subject_id' => 6,
                    'title' => 'What are different types of analytics on orangescrum?',
                    'description' => '<li>"<strong>Weekly Usage</strong>", "<strong>Task Reports</strong>", "<strong>Hour Reports</strong>" are different types of analytics.</li>
<li>Users can also filter the report based on Task type (Bug, Enhancement, development).</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            28 =>
                [
                    'id' => 34,
                    'subject_id' => 7,
                    'title' => 'Can I restore archived tasks or files?',
                    'description' => '<li>Yes, You can check the task and select the Restore option on the top of the list.</li>
<li>Also, you can check multiple tasks and do the same by selecting Restore on the top of the list.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            29 =>
                [
                    'id' => 35,
                    'subject_id' => 7,
                    'title' => 'Can I see the archived tasks or files of my team members?',
                    'description' => '<li>Yes, you can see in the archive listing.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            30 =>
                [
                    'id' => 36,
                    'subject_id' => 7,
                    'title' => 'Can I get the tasks or files once I delete them from Archive section?',
                    'description' => '<li>No, After deleting from the archive section the tasks or files will be permanently deleted.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            31 =>
                [
                    'id' => 39,
                    'subject_id' => 9,
                    'title' => 'How to change my name, timezone and profile photo?',
                    'description' => '<li>Click your profile button at the right top corner of the page; click "<strong>My Profile</strong>" to change your name, timezone and profile photo.</li>
<li><strong>Note:</strong> To update your account with new time zone, you need to log out and log in again.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            32 =>
                [
                    'id' => 40,
                    'subject_id' => 9,
                    'title' => 'How can I change my password?',
                    'description' => '<li>Go to the "<strong>My Profile</strong>" section.</li>
<li>Click "<strong>Change Password</strong>" to change your account password.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            33 =>
                [
                    'id' => 43,
                    'subject_id' => 11,
                    'title' => 'Can I post a reply by replying to the task create email from my Inbox?',
                    'description' => '<li>Yes, You can reply to the email for posting reply to the task.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            34 =>
                [
                    'id' => 44,
                    'subject_id' => 11,
                    'title' => 'Can I disable the Task create or reply emails I am getting?',
                    'description' => '<li>Click the setting icon at the top right corner of the page, click "<strong>Email Notification</strong>" to disable the emails.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            35 =>
                [
                    'id' => 45,
                    'subject_id' => 11,
                    'title' => 'What are the other email notifications and settings?',
                    'description' => '<li>"Desktop Notification", "Weekly Usage", "Task Status", "Task Due", "Daily Update Report" are different types of email notifications and settings.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            36 =>
                [
                    'id' => 46,
                    'subject_id' => 12,
                    'title' => 'How can I upgrade from a free plan to paid plan?',
                    'description' => '<li>Click the setting icon at the top right corner of the page, click "<strong>Subscription</strong>".</li>
<li>In the "Subscription" page, click "Change Plan" to upgrade from free to paid plan.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            37 =>
                [
                    'id' => 47,
                    'subject_id' => 12,
                    'title' => 'What happens when I upgrade?',
                    'description' => '<li>After up gradation, you will get more storage limit as compared to the free account.</li>
<li>You can use more functionality other than free users.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            38 =>
                [
                    'id' => 48,
                    'subject_id' => 12,
                    'title' => 'Can I downgrade from a paid plan to free plan?',
                    'description' => '<li>You can downgrade to a lower paid plan at any time.</li>
<li>Also you can cancel at any time.</li>
<li>If you are canceling in between the billing period, your card will be charged for that entire month, however your account will be canceled with immediate effect.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            39 =>
                [
                    'id' => 50,
                    'subject_id' => 12,
                    'title' => 'Apart from online payment, is there any other way of making payment?',
                    'description' => '<li>Yes. The other modes of payment is Wire Transfer.</li>
<li>This mode is accepted only for the yearly subscription.</li>
<li>Contact us for yearly subscription.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            40 =>
                [
                    'id' => 51,
                    'subject_id' => 12,
                    'title' => 'How much is the free space available for use? Is it expandable?',
                    'description' => '<li>Contact us to get custom plan for your account.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            41 =>
                [
                    'id' => 52,
                    'subject_id' => 13,
                    'title' => 'How can I Import my data into Orangescrum?',
                    'description' => '<li>Click on the top settings icon near profile image.</li>
<li>You can see the "<strong>Import & Export</strong>" link on the Company Settings section.</li>
<li>Read the instructions on the "<strong>Import & Export</strong>" page.</li>
<li>We accept only data in CSV format.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            42 =>
                [
                    'id' => 53,
                    'subject_id' => 13,
                    'title' => 'How can I take backup of my data from Orangescrum?',
                    'description' => '<li>Click on the top settings icon near profile image.</li>
<li>You can see the "<strong>Import & Export</strong>" link on the Company Settings section.</li>
<li>There you can find a "<strong>Export to CSV</strong>" button.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-12 00:00:00',
                    'is_admin' => 0,
                ],
            43 =>
                [
                    'id' => 54,
                    'subject_id' => 14,
                    'title' => '',
                    'description' => '<li>We\'d hate to see you go, but it\'s very easy to cancel your account.</li>
<li>Select the "Settings" from top and go to "Subscription".</li>
<li>Click "Cancel Account" and you\'ll be able to cancel your account.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-12-23 00:00:00',
                    'is_admin' => 0,
                ],
            44 =>
                [
                    'id' => 68,
                    'subject_id' => 5,
                    'title' => 'How do I log time?',
                    'description' => '<li>Go to "<b>Time Log</b>" Section , by clicking <a onclick="open_timelog();" href="javascript:void(0);"><span class="timelog_nb smenu"></span></a> this icon on left hand side bar.</li>
	<li>Click on "<b>Log More Time</b>" Button on top right hand side of the grid. This will pop up a window.
	<li>Select Task from task list .</li>
	<li>Select Resource Name from list and pick Date from calendar.</li>
	<li>Select Start time and End time from list.</li>
	<li>Enter Break time. Spent Hours will be calculated automatically.</li>
	<li>By Default billable field is checked , you can always unchek in case non-billable hours.</li>
	<li>Click on "<b>Add Item</b>", if you want to log more hours for a different resource or same resource different date or different time.</li>
	<li>Enter Summary</li>
	<li>Click on "<b>Log This Time</b>" Button to save data.</li>',
                    'image' => 'logtime.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            45 =>
                [
                    'id' => 81,
                    'subject_id' => 15,
                    'title' => 'How to see customers?',
                    'description' => '<li>Click on "<b>Manage Customers</b>" tab to see all customers of company.</li>',
                    'image' => 'Customers.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            46 =>
                [
                    'id' => 82,
                    'subject_id' => 15,
                    'title' => 'How to create Invoice without unbilled time?',
                    'description' => '<li>Go to Invoice section by clicking on "<b>Invoice</b>" icon on left hand side bar. It will show all unbilled time.</li>
	<li>Click on right side down arrow of "<b>Create Invoice</b>" button on "<b>Create invoice without unbilled time</b>". It will redirect to create invoice page with an empty form.</li>',
                    'image' => 'create_Invoice_without_unbilled_time.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            47 =>
                [
                    'id' => 83,
                    'subject_id' => 15,
                    'title' => 'How to modify an invoice?',
                    'description' => '<li>Click on "<b>Invoice<b>" tab and click on any invoice to modify it.</li>',
                    'image' => 'modify_an_invoice.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            48 =>
                [
                    'id' => 69,
                    'subject_id' => 5,
                    'title' => 'How do I log time form quick add?',
                    'description' => '<li>You can also log time by clicking Log time item from "+Add" from header.  This will pop up a window.</li>
	<li>Select Task from task list .</li>
	<li>Select Resource Name from list and pick Date from calendar.</li>
	<li>Select Start time and End time from list.</li>
	<li>Enter Break time. Spent Hours will be calculated automatically.</li>
	<li>By Default billable field is checked , you can always unchek in case non-billable hours.</li>
	<li>Click on "Add Item", if you want to log more hours for a different resource or same resource different date or different time.</li>
	<li>Enter Summary</li>
	<li>Click on "Log This Time" Button to save data.</li><li>Please refer to circle numbered 3 in the image given below.</li>',
                    'image' => 'time_log.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            49 =>
                [
                    'id' => 70,
                    'subject_id' => 5,
                    'title' => 'How do I log time from task listing page?',
                    'description' => '<li>From Task List , select Time Log from down arrow  drop down.</li> 
	<li>Click on " Log Time" Button on top right hand side of the grid. This will pop up a window.</li>
	<li>Here task title will be selected.</li>
	<li>Select Resource Name from list and pick Date from calendar.</li>
	<li>Select Start time and End time from list.</li>
	<li>Enter Break time. Spent Hours will be calculated automatically.</li>
	<li>By Default billable field is checked , you can always unchek in case non-billable hours.</li>
	<li>Click on "Add Item", if you want to log more hours for a different resource or same resource different date or different time.</li>
	<li>Enter Summary.</li>
	<li>Click on "Log This Time" Button to save data.</li><li>Please refer to circle numbered 2 in the image given below.</li>',
                    'image' => 'time_log.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            50 =>
                [
                    'id' => 71,
                    'subject_id' => 5,
                    'title' => 'How do I log time while creating task?',
                    'description' => '<li>Click on "<b>Create Task</b>" button on the top left hand side of the page.</li>
	<li>Enter Task Title.</li>
	<li>Select Start time and End time from list.</li>
	<li>Enter Break time. Spent Hours will be calculated automatically.</li>
	<li>By Default billable field is checked , you can always unchek in case non-billable hours.</li>
	<li>Click on "<b>Save & Exit</b>" or "<b>Save & Create</b>" button to save data.</li>',
                    'image' => 'create_task_timelog.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            51 =>
                [
                    'id' => 72,
                    'subject_id' => 5,
                    'title' => 'How do I log time while replying a Task?',
                    'description' => '<li>Go to task reply section in task detail page.</li>
	<li>Select Start time and End time from list.</li>
	<li>Enter Break time. Spent Hours will be calculated automatically.</li>
	<li>By Default billable field is checked , you can always unchek in case non-billable hours.</li>
	<li>Click on "<b>Post</b>" button to save data</li>',
                    'image' => 'reply_timelog.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            52 =>
                [
                    'id' => 73,
                    'subject_id' => 5,
                    'title' => 'How do I log time from task detail page?',
                    'description' => '<li>Go to Task detail page by clicking on a task in task listing page.</li>
	<li>Click on "<b>Log More Time</b>" Button. This will pop up a window.</li>
	<li>By default the task is selected, you can change task by selecting another  task from task list .</li>
	<li>Select Resource Name from list and pick Date from calendar.</li>
	<li>Select Start time and End time from list.</li>
	<li>Enter Break time. Spent Hours will be calculated automatically.</li>
	<li>By Default billable field is checked , you can always unchek in case non-billable hours.</li>
	<li>Click on "<b>Log This Time</b>" Button to save data.</li>',
                    'image' => 'fromtaskdetail.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            53 =>
                [
                    'id' => 74,
                    'subject_id' => 5,
                    'title' => 'How do I see all time log records?',
                    'description' => '<li>Go to "<b>Time Log</b>" Section , by clicking <a onclick="open_timelog();" href="javascript:void(0);"><span class="timelog_nb smenu"></span></a> this icon on left hand side bar.</li>
	<li>A new page with all records will open.</li>',
                    'image' => 'timelog_grid.png',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            54 =>
                [
                    'id' => 75,
                    'subject_id' => 5,
                    'title' => 'How do I modify logged time?',
                    'description' => '<li>Go to "<b>Time Log</b>" Section , by clicking <a onclick="open_timelog();" href="javascript:void(0);"><span class="timelog_nb smenu"></span></a> this icon on left hand side bar.</li>
	<li>Click on edit icon at the end of each time log record to edit it. </li>
	<li>Click on delete icon at the end ofeach time log record to delete it. </li>
	<li>Note: Owner, admin and the user for whom time is logged, they can modify it.</li>',
                    'image' => 'modify_timelog.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            55 =>
                [
                    'id' => 76,
                    'subject_id' => 5,
                    'title' => 'How do I filter time log records?',
                    'description' => '<li>Go to "<b>Time Log</b>" Section , by clicking <a onclick="open_timelog();" href="javascript:void(0);"><span class="timelog_nb smenu"></span></a> this icon on left hand side bar.</li>
	<li>Pick Start Date and End date from calendar on top right hand side of the page.</li>
	<li>Select Resource Name from list.</li>
	<li>Click on "<b>Search</b>" Button to view filtered data.</li>',
                    'image' => 'filter_timelog.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            56 =>
                [
                    'id' => 77,
                    'subject_id' => 15,
                    'title' => 'How to create Invoice?',
                    'description' => '<li>Go to Invoice section by clicking <a onclick="open_invoice();" href="javascript:void(0);"><span class="invoice_nb smenu"></span></a> this icon on left hand side bar. It will display all unbilled time.</li>
	<li>Check one or more "<b>Time log entries</b>" and click on "<b>Create Invoice</b>" button.
	<a href="http://www.easyagile.us/img/Invoice/creat_invoice.jpg"><img src="http://www.easyagile.us/img/Invoice/creat_invoice.jpg"/></a></li> 
	<li>It will pop up a window to select either to create a new invoice or add it to existing invoice. Then click on "<b>Update</b>" button.
	<a href="http://www.easyagile.us/img/Invoice/creat_invoice-update.jpg"><img src="http://www.easyagile.us/img/Invoice/creat_invoice-update.jpg"/></a></li>
	<li>It will redirect to create invoice page where you can enter invoice number. It should be alpha numeric. This field is mandatory. And also this number can not be duplicate in one company.</li>
	<li>Then select one term from terms drop down list. Terms is the number of days to pay the bill. According to the term selected, it changes the due date by which customer has to pay the bill. User can also manually change the due date.</li>
	<li>Then pick an invoice date. It is the date for which invoice has been created.</li>  
	<li>Then provide the billing from address. This is the address of the company who is creating the invoice.</li>
	<li>Then select one customer form the customers drop down list. You can also add customer by selecting "+ Add new customer option".</li>
	<li>Then provide the billing to address which is the address of the customer.</li>
	<li>Then in line item pick a date for which date invoice is created.</li>
	<li>Provide the description.</li>
	<li>Then enter the quantity. Here quantity refers to the hour(s) spent on that particular item.</li>
	<li>Then enter rate, which is the unit price per hour.</li>
	<li>If you want to add more line item, you can do so by clicking on "<b>+ Add line-item</b>" button.</li>
	<li>Then selsct the discount mode whether percent or flat and enter discount amount.</li>
	<li>Then enter tax amount in percentage.</li>
	<li>You can also upload your company logo at the top left hand side of the page. The logo must be smaller than 2MB in size. For the first time while creating invoice, if company logo is present it will be shown otherwise it will display no image. While editing image if there is no image for that company it will be stored as company logo. Otherwise it will be stored as that invoice logo.</li>
	<li>Click on "<b>Save & Send</b>" button to save invoice and send email to customer.</li>
	<li>Click on "<b>Save & Download</b>" button to save invoice and download it as pdf.</li>
	<li>Click on "<b>Save and Close</b>" button to save invoice and go to invoice list page.</li>
	<li>Click on "<b>Save and New</b>" button to save invoice and create another invoice.</li>',
                    'image' => 'invoice_page.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            57 =>
                [
                    'id' => 78,
                    'subject_id' => 15,
                    'title' => 'How to create Invoice from quick add?',
                    'description' => '<li>You can also create invoice by clicking invoice item from "+Add" from header.</li>
	<li>Check one or more "<b>Time log entries</b>" and click on "<b>Create Invoice</b>" button.</li>
	<li>It will pop up a window to select either to create a new invoice or add it to existing invoice. Then click on "Update" button.</li>
	<li>It will redirect to create invoice page where you can enter invoice number. It should be alpha numeric. This field is mandatory. And also this number can not be duplicate in one company.</li>
	<li>Then select one term from terms drop down list. Terms is the number of days to pay the bill. According to the term selected, it changes the due date by which customer has to pay the bill. User can also manually change the due date.</li>
	<li>Then pick an invoice date. Invoice date is the date for which invoice has been created. </li>  
	<li>Then provide the billing from address. This is the address of the company who is creating the invoice.</li>
	<li>Then select one customer form the customers drop down list. You can also add customer by selecting "+ Add new customer option".</li>
	<li>Then provide the billing to address which is the address of the customer.</li>
	<li>Then in line item pick a date for which date invoice is created.</li>
	<li>Provide the description.</li>
	<li>Then enter the quantity. Here quantity refers to the hour(s) spent on that particular item.</li>
	<li>Then enter rate, which is the unit price per hour.</li>
	<li>If you want to add more line item, you can do so by clicking on "<b>+ Add line-item</b>" button.</li>
	<li>Then select the discount mode whether percent or flat and enter discount amount.</li>
	<li>Then enter tax amount in percentage.</li>
	<li>You can also upload your company logo at the top left hand side of the page. The logo must be smaller than 2MB in size.</li> 
	<li>Click on "<b>Save & Send</b>" button to save invoice and send email to customer.</li>
	<li>Click on "<b>Save & Download</b>" button to save invoice and download it as pdf.</li>
	<li>Click on "<b>Save and Close</b>" button to save invoice and go to invoice list page.</li>
	<li>Click on "<b>Save and New</b>" button to save invoice and create another invoice.</li>',
                    'image' => 'Quick_add.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            58 =>
                [
                    'id' => 79,
                    'subject_id' => 15,
                    'title' => 'How to see unbilled time?',
                    'description' => '<li>Click on "<b>Unbilled Time"</b> tab to view all unbilled time.</li>',
                    'image' => 'unbilled_time.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            59 =>
                [
                    'id' => 80,
                    'subject_id' => 15,
                    'title' => 'How to see invoice list?',
                    'description' => '<li>Click on "<b>Invoice</b>" tab to see invoice list.</li>',
                    'image' => 'invoice_list.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            60 =>
                [
                    'id' => 84,
                    'subject_id' => 15,
                    'title' => 'How to add customer?',
                    'description' => '<li>Click on "Manage Customer" tab and then click on "<b>Add Customer</b>" button. It will open the pop-up to add customer details.
	<a href="http://www.easyagile.us/img/Invoice/add_customer.jpg"><img src="http://www.easyagile.us/img/Invoice/add_customer.jpg"/></a></li> 
	<li>Enter Customer name, email and select currency and click on "<b>Create</b>" button to add customer.
	<a href="http://www.easyagile.us/img/Invoice/add_customer-create.jpg"><img src="http://www.easyagile.us/img/Invoice/add_customer-create.jpg"/></a></li> 
	<li>If you want to add more detail for customer, click on "<b>+ Details</b>" and enter more detail of customer.
	<a href="http://www.easyagile.us/img/Invoice/add-customer-details.jpg"><img src="http://www.easyagile.us/img/Invoice/add-customer-details.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            61 =>
                [
                    'id' => 85,
                    'subject_id' => 15,
                    'title' => 'How to add customer while creating Invoice?',
                    'description' => '<li>While creating invoice, click on "<b>Customer</b>" drop down and select "<b>+ Add new Customer</b>". It will open a pop-up to enter customer details.</li>
	<li>Enter Customer name, email and select currency and click on "<b>Create</b>" button to add customer.</li>
	<li>If you want to add more detail for customer, click on "<b>+ Details</b>" and enter more detail of customer.</li>',
                    'image' => 'add_new_Customer.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            62 =>
                [
                    'id' => 86,
                    'subject_id' => 15,
                    'title' => 'How to manage customers?',
                    'description' => 'li>Click on "<b>Manage Customer</b>" tab. It will show all existing customers of the company.</li> 
	<li>Click on right side edit icon to modify details of an existing customer.</li>',
                    'image' => 'manage_customers.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            63 =>
                [
                    'id' => 87,
                    'subject_id' => 15,
                    'title' => 'How to change status of customer?',
                    'description' => '<li>While adding or edit of a customer, check "<b>Make Inactive</b>" check box to make a customer inactive and uncheck it to make customer active again.</li>',
                    'image' => 'Make_Inactive.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            64 =>
                [
                    'id' => 88,
                    'subject_id' => 15,
                    'title' => 'How do I send invoice to customer?',
                    'description' => '<li>While creating invoice click on "<b>Save & Send</b>" button. It will pop-up a window.</li>
	<li>In that pop up window, all the fields except To such as From, Subject and Message are pre-filled. You can also change those.</li>
	<li>Then enter customer\'s email address to whom Invoice will be sent in To text box.</li>
	<li>Then click on "Send" button to send Invoice.</li>',
                    'image' => 'Send.jpg',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            65 =>
                [
                    'id' => 89,
                    'subject_id' => 15,
                    'title' => 'How Do I download Invoice?',
                    'description' => '<li>While creating invoice click on "<b>Save & Download</b>" button to download the invoice as pdf.</li>
	<li>Also while sending invoice to customer by clicking on the attachment the invoice will be downloaded as pdf.
	<a href="http://www.easyagile.us/img/Invoice/download.jpg"><img src="http://www.easyagile.us/img/Invoice/download.jpg"/></a></li>',
                    'image' => 'invoice_pdf.png',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            66 =>
                [
                    'id' => 103,
                    'subject_id' => 1,
                    'title' => 'How to Create or Import Task?',
                    'description' => '<li>In the dashboard, go to "+Add" Menu.</li><li>Click on "Import & Export" sub-menu present under Company Settings. The screenshot for "Import & Export" has been shown below.<a href="http://www.easyagile.us/img/help/task/creat_task_import1.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_import1.jpg"/></a></li><li>Click on "Download .csv file" option to downlaod a sample file. Insert you task in the prescribed format of downloaded file.</li><li>Click on "Choose File" to upload the .csv file.</li><li>Click on "Continue" to add multiple tasks from your .csv file.</li><li>You may export your created/uploaded task by clicking this button.<a href="http://www.easyagile.us/img/help/task/creat_task_import2.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_import2.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            67 =>
                [
                    'id' => 104,
                    'subject_id' => 1,
                    'title' => 'How to create Task Template?',
                    'description' => '<li>Click on Task Template icon <span class="template_n_help"></span> from left side navigation. Click on + Create task Template box.<a href="http://www.easyagile.us/img/help/task/creat_task_template1.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_template1.jpg"/></a></li><li>It will pop out a box , You need to enter template name and its content.<a href="http://www.easyagile.us/img/help/task/creat_task_template2.jpg"><img src="http://www.easyagile.us/img/help/task/creat_task_template2.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            68 =>
                [
                    'id' => 122,
                    'subject_id' => 10,
                    'title' => 'What is Daily Catch-ups?',
                    'description' => '<li>To get the update of the assigned project through email.</li>
<li>Click the setting icon at the top right corner of the page, click "<strong> Daily Catch-up</strong>" to change the  Daily Catch-up settings.<a href="http://www.easyagile.us/img/help/Daily_Catchup_click.jpg"><img alt="Loading..." src="http://www.easyagile.us/img/help/Daily_Catchup_click.jpg"/></a>
<a href="http://www.easyagile.us/img/help/Daily_Catchup_tab.jpg"><img alt="Loading..." src="http://www.easyagile.us/img/help/Daily_Catchup_tab.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            69 =>
                [
                    'id' => 123,
                    'subject_id' => 16,
                    'title' => 'How to customize a Task ?',
                    'description' => '<li>Click on "How To Customize Task" button on Dashboard page. This will open up Task Type Customization Page.<a href="http://www.easyagile.us/img/help/customize_task.jpg"><img alt="Loading..." src="http://www.easyagile.us/img/help/customize_task.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            70 =>
                [
                    'id' => 124,
                    'subject_id' => 16,
                    'title' => 'How to add new task types ?',
                    'description' => '<li>You can add custom task types or choose from default available task types listed on task type customizationpage.</li><li>To add new task type ,click on "New Task Type" button.This will open up a form to add custom task type.<a href="http://www.easyagile.us/img/help/task_type.jpg"><img alt="Loading..." src="http://www.easyagile.us/img/help/task_type.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            71 =>
                [
                    'id' => 125,
                    'subject_id' => 16,
                    'title' => 'How to view the task type of a task ?',
                    'description' => '<li>Go to task listings page see the text next to <span class="act_task_type_tag"></span> icon.This text represents the task type.<a href="http://www.easyagile.us/img/help/view_tast_type.jpg"><img alt="Loading..." src="http://www.easyagile.us/img/help/view_tast_type.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            72 =>
                [
                    'id' => 126,
                    'subject_id' => 16,
                    'title' => 'How to change the task type of a task ?',
                    'description' => '<li>On the task listing page, click the drop down icon <span class="act_task_type_dropdwn"></span>next to the task type text or click on the tag icon <span class="act_task_type_tag"></span> to view all the available task types in a dropdown.</li>
<li>Click on any available task type from the dropdown to change the task type.<a href="http://www.easyagile.us/img/help/dropdown.jpg"><img alt="Loading..." src="http://www.easyagile.us/img/help/dropdown.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            73 =>
                [
                    'id' => 127,
                    'subject_id' => 10,
                    'title' => 'How to get Daily Catch-Up alerts for a project ?',
                    'description' => '<li>In the Daily Catch-up Alerts settings page, select the Project from the project drop down to get all settings for daily Catch-up alerts.</li>
<li>Select the user(s) by checking the check boxe(s) to send them Daily catch-up alerts.</li>
<li>Select the Alert time from the drop down options to set a time for the Daily catch-up alerts.</li>
<li>Select your Time zone from drop down List.</li>
<li>Select the frequency to send the Daily catch-up alerts for particular days in a week.<a href="http://www.easyagile.us/img/help/section.jpg"><img alt="Loading..." src="http://www.easyagile.us/img/help/section.jpg"/></a></li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-10-10 00:00:00',
                    'is_admin' => 0,
                ],
            74 =>
                [
                    'id' => 128,
                    'subject_id' => 10,
                    'title' => 'Can I cancel a Daily Catch-up ?',
                    'description' => '<li>Yes, in the "<strong> Daily Catch-up</strong>" section ,select the project in which Daily Catch-up alerts has been set and click on the "Cancel Daily Catch-up" link to cancel daily catch-up alerts.</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2013-09-03 00:00:00',
                    'is_admin' => 0,
                ],
            75 =>
                [
                    'id' => 129,
                    'subject_id' => 18,
                    'title' => 'How to manage company?',
                    'description' => '<li>Click on the "User Account Tab" to generate the menu as shown in the figure below.<a href="http://www.easyagile.us/img/help/company-settings-1.PNG">
<img style="max-width:45%" src="http://www.easyagile.us/img/help/company-settings-1.PNG">
</a></li>
<li>From the Menu select "My Company" under "Company Settings".</li>
<li>When you click on "My Company" new window will open which will look like the figure below:<a href="http://www.easyagile.us/img/help/company-settings-2.PNG">
<img style="max-width:45%" src="http://www.easyagile.us/img/help/company-settings-2.PNG">
</a></li>
<li>In the form you will see "5" fill up columns. They are namely:</li>
	<ul><li>Name of your company</li>
	<li>OrangeScrum URL – This does not change. It is fixed</li>
	<li>Company website Contact details</li>
	<li>Company Logo</li></ul>
<li>Having filled up those columns click on "Update" to continue or click on Cancel to go back. When you click on update button, a pop up message will appear which will say "Company Updated Successfully".</li>',
                    'image' => '',
                    'keywords' => '',
                    'created' => '2015-11-06 00:00:00',
                    'is_admin' => 1,
                ],
        ];
        $this->table('helps')->insert($data)->save();
    }
}
