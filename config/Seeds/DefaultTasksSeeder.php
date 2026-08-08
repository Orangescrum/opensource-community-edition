<?php

use Migrations\AbstractSeed;

class DefaultTasksSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            0 =>
                [
                    'id' => 1,
                    'task' => 'How to Add a Task?',
                    'description' => '<ul>
	<li>Click on "+ New Task", there you can add a new task.</li>
	<li>Add a quick Task within a second. Press [N], enter a task Title and hit [Enter].</li>
	<li>Select memebrs to send email notification.</li>
	<li>Click on "More" to Assign Taskm set Due Date, select Task Type, Description, File attachment.</li>
	<li>If you have multiple projects, you can switch to another project and create a task for that project.</li>
</ul>',
                ],
            1 =>
                [
                    'id' => 2,
                    'task' => 'How to reply on a Task?',
                    'description' => '<ul>
	<li>Click on a Task Title on dashboard Task listing.</li>
	<li>You can see the Task details with all the replies on that tasks.</li>
	<li>There is a reply icon on all the reply at the right top section of the reply box.</li>
	<li>Or, you can go directly to the bottom of the thread and there you can see a text editor to reply on that Task.</li>
	<li>Same as Add new Task, here can click on "More Options" to send Email Notification, set status, Assign To, % completion, Hours spent and file attachment.</li>
</ul>',
                ],
            2 =>
                [
                    'id' => 3,
                    'task' => 'How to attach sync Google Drive and Dropbox files?',
                    'description' => '<h3>Filter</h3>
<ul>
<li>There is a status widget below the project name, where all statuses are listed with #of tasks. Click on any status to filter tasks.</li>
<li>Also, you can set multiple filters by clicking on the "Showing tasks of Status, Type, Priority, Members". This section is just below the status widget aligned to right side of the page</li>
</ul>
<h3>Search</h3>
<ul>
<li>Type your search keyword in the search box "jump to tasks" and it will filter and display the relevant tasks.</li>
<li>Click on any tasks to view details of that task or, click on the blue arrow image to list all search tasks.</li>
</ul>',
                ],
            3 =>
                [
                    'id' => 4,
                    'task' => 'How to Filter & Search Tasks?',
                    'description' => '<h3>Filter</h3>
<ul>
	<li>Menu Filters
		<ul>
			<li>Recent - Tasks of Last 24 hours</li>
			<li>Assigned To Me - All the Task assigned to you (including the Tasks you assigned to youself)</li>
			<li>Delegated to Others - All the Task you assigned to others</li>
			<li>Bug - All the Task set as Bug Task Type while creating a Task</li>
			<li>Closed - All the closed Tasks</li>
		</ul>
	</li>
	<li>Project Filters
		<ul>
			<li>When you have multiple projects, you can switch difefrent projects on Dashboard.</li>
			<li>You can also see Tasks of All projects by selecting All on the switch project section on Dashboard</li>
			<li>You can also search a Project on the switch project section</li>
		</ul>
	</li>
	<li>Widget Filters
		<ul>
			<li>You can see the the widgets on dashboard showing the #of New, WIP, Resolved and Closed tasks.</li>
			<li>Each widgets are filters.</li>
			<li>You can click on the widgets to see the filetered tasks.</li>
		</ul>
	</li>
	<li>Task Filters
		<ul>
			<li>Below the Task widget, you can see a list of filters. Saying Showing tasks of Date, Status, Types, Priority, Memebrs & Assign To</li>
			<li>Select multiple values to filter the tasks</li>
		</ul>
	</li>
</ul>

<h3>Search</h3>
<ul>
	<li>Search a tasks by entering a search phrase on the top of the page.</li>
	<li>You will see a list of matching results (max. 6) from them you can select one Task to see the details.</li>
	<li>Or, You hit enter on the search box to see all the matching results in a list.</li>
</ul>',
                ],
            4 =>
                [
                    'id' => 5,
                    'task' => 'How to manage Team?',
                    'description' => '<ul>
	<li>How do I add new Member to my Company?</li>
		<ul>
		    <li>Go to the Team menu and you see there is "Invite" on the sub-menu.</li>
		    <li>Click on the <b>Invite</b> link to invite new Memebr</li>
			<li>The invited memebr will get an email and a link in the email to activate her account.</li>
			<li>You can also Invite the members those have already an account in OrangeScrum (may be as a Memebr or Admin of another company).</li>
		</ul>
	<li>How do I manage user?</li>
		<ul>
		    <li>Go to <b>Manage</b> link of Team menu, there you can see the listed users.</li>
		</ul>
	<li>What is Active/Inactive User?</li>
		<ul>
		    <li><b>Inactive User:</b></li>
		    <ul>
			<li>If a user is invited and not yet signed up.</li>
			<li>If an Active user is disabled/inactivated.</li>
		    </ul>
		</ul>
	<li>How do I delete a User?</li>
		<ul>
		    <li>An existing user cannot be deleted; you can only inactivate the user.</li>
		    <li>The inactive user <b>cannot login</b>. But all his related data (tasks, milestone etc.) will remain there in the application.</li>
		</ul>
	<li>How do I assign multiple users  to multiple projects?</li>
    		<ul>
    		    <li>Go to "Manage" option Under "Projects" menu. Clicking on project name, the user names will be shown below it.</li>
    		    <li>To remove this project from any users it needs to un- check the check box opposite to the user name.</li>
    		    <li>In order to add a new user, click the icon "Add User" in the right-most column.</li>
    		    <li>A pop-up will appear showing the list of user names, where multiple users can be selected and assigned to the particular project.</li>
    		</ul>
</ul>',
                ],
            5 =>
                [
                    'id' => 6,
                    'task' => 'How to manage Projects?',
                    'description' => '<ul>
	<li>How do I add new project?</li>
		<ul>
		    <li>Similar to User, click on the <b>"+ New"</b> link of Project menu to add new project.</li>				
		</ul>
	<li>How do I assign/de-assign users to &amp; from a project?</li>
		<ul>
		    <li>Go to <b>Assign User</b> section of Project menu, where you can select a project and drag-n-drop members/customer to that project.</li>
		    <li>Similarly, to de-assign, reverse the assign process.</li>
		</ul>
	<li>How do I manage and delete/edit project?</li>
		<ul>
		    <li>Go to <b>Manage</b> link of Project menu, where the projects are listed under two different view "Grid View" &amp; "Classic View"</li>
		</ul>
	<li>How do I assign multiple projects to multiple users?</li>
		<ul>
		    <li>Go to "Manage" option Under "Users" menu. Clicking on user name, the project names will be shown below it.</li>
		    <li>To remove the user from any project, by un- checking the check box opposite to the project name.</li>
		    <li>In order to add a new project, click the icon "Add Project" in the right-most column.</li>
		</ul>
</ul>',
                ],
        ];
        $this->table('default_tasks')->insert($data)->save();
    }
}
