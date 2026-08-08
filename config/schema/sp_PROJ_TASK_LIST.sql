CREATE  PROCEDURE `sp_PROJ_TASK_LIST`(IN projectid INT(11), userid INT(11), IN offset SMALLINT(5), IN vcount SMALLINT(5))
BEGIN
    SELECT SQL_CALC_FOUND_ROWS 
        Easycase.id, 
        Easycase.uniq_id, 
        Easycase.case_no, 
        Easycase.case_count, 
        Easycase.project_id, 
        Easycase.user_id, 
        Easycase.updated_by, 
        Easycase.type_id,
        Easycase.priority,
        Easycase.title, 
        Easycase.estimated_hours, 
        Easycase.hours, 
        Easycase.completed_task, 
        Easycase.assign_to, 
        Easycase.gantt_start_date, 
        Easycase.initial_due_date, 
        Easycase.due_date, 
        Easycase.istype, 
        Easycase.client_status, 
        Easycase.format, 
        Easycase.status, 
        Easycase.legend, 
        Easycase.is_recurring, 
        Easycase.isactive, 
        Easycase.dt_created, 
        Easycase.actual_dt_created, 
        Easycase.reply_type, 
        Easycase.is_chrome_extension, 
        Easycase.from_email, 
        Easycase.depends, 
        Easycase.children, 
        Easycase.temp_est_hours, 
        Easycase.seq_id, 
        Easycase.parent_task_id, 
        Easycase.story_point, 
        Easycase.thread_count, 
        Easycase.custom_status_id, 
        User.short_name, 
        User.name, 
        IF((Easycase.assign_to = userid), 'Me', User.short_name) AS Assigned,
        Easycase.is_sub_sub_task, 
        IFNULL(Easycase.sub_sub_task, 0) sub_sub_task, 
        Easycase.tot_spent_hour 
    FROM (
        SELECT 
            Easycase.id, 
            Easycase.uniq_id, 
            Easycase.case_no, 
            Easycase.case_count, 
            Easycase.project_id, 
            Easycase.user_id, 
            Easycase.updated_by, 
            Easycase.type_id, 
            Easycase.priority, 
            Easycase.title, 
            Easycase.estimated_hours, 
            Easycase.hours, 
            Easycase.completed_task, 
            Easycase.assign_to, 
            Easycase.gantt_start_date, 
            Easycase.initial_due_date, 
            Easycase.due_date, 
            Easycase.istype, 
            Easycase.client_status, 
            Easycase.format, 
            Easycase.status, 
            Easycase.legend, 
            Easycase.is_recurring, 
            Easycase.isactive, 
            Easycase.dt_created, 
            Easycase.actual_dt_created, 
            Easycase.reply_type, 
            Easycase.is_chrome_extension, 
            Easycase.from_email, 
            Easycase.depends, 
            Easycase.children, 
            Easycase.temp_est_hours, 
            Easycase.seq_id, 
            Easycase.parent_task_id, 
            Easycase.story_point, 
            Easycase.thread_count, 
            Easycase.custom_status_id, 
            is_sub_sub_task, 
            sub_sub_task, 
            tot_spent_hour 
        FROM 
            easycases AS Easycase 
        LEFT JOIN (
            SELECT 
                id, 
                parent_task_id AS is_sub_sub_task, 
                COUNT(parent_task_id) sub_sub_task 
            FROM 
                easycases AS Easycase 
            WHERE 
                project_id = projectid 
                AND istype = '1' 
                AND 1 
                AND Easycase.isactive = 1 
            GROUP BY 
                id, parent_task_id
        ) IS_SUB ON IS_SUB.id = Easycase.parent_task_id 
        LEFT JOIN 
            easycase_milestones EasycaseMilestone ON Easycase.project_id = projectid 
            AND Easycase.id = EasycaseMilestone.easycase_id 
        LEFT JOIN (
            SELECT 
                SUM(t.total_hours) AS tot_spent_hour, 
                t.task_id 
            FROM 
                log_times t 
            WHERE 
                t.project_id = projectid 
            GROUP BY 
                t.task_id
        ) AS lt ON Easycase.id = lt.task_id 
        WHERE 
            Easycase.project_id = projectid 
            AND istype = '1' 
            AND 1 
            AND Easycase.isactive = 1
    ) AS Easycase 
    LEFT JOIN 
        users User ON Easycase.assign_to = User.id 
    LIMIT offset, vcount;
END