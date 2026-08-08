CREATE  PROCEDURE `sp_ALL_PROJ_TASK_LIST`(
    IN `companyid` INT(11),
    IN `projId` INT(11),
    IN `userid` INT(11),
    IN `prm_clt_sql` VARCHAR(6000),
    IN `prm_cond_easycase_actuve` VARCHAR(6000),
    IN `prm_searchcase` VARCHAR(6000),
    IN `prm_qry` VARCHAR(6000),
    IN `prm_orderby` VARCHAR(3000),
    IN `OFFSET` SMALLINT(5),
    IN `vcount` SMALLINT(5)
)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS temp_easycases;
    SET @query = CONCAT(
        "CREATE TEMPORARY TABLE temp_easycases
        SELECT
            Easycase.id, Easycase.uniq_id, Easycase.case_no, Easycase.case_count, Easycase.project_id, Easycase.user_id, Easycase.updated_by, Easycase.type_id, Easycase.priority, Easycase.title, Easycase.estimated_hours, Easycase.hours, Easycase.completed_task, Easycase.assign_to, Easycase.gantt_start_date, Easycase.initial_due_date, Easycase.due_date, Easycase.istype, Easycase.client_status, Easycase.format, Easycase.status, Easycase.legend, Easycase.is_recurring, Easycase.isactive, Easycase.dt_created, Easycase.actual_dt_created, Easycase.reply_type, Easycase.is_chrome_extension, Easycase.from_email, Easycase.depends, Easycase.children, Easycase.temp_est_hours, Easycase.seq_id, Easycase.parent_task_id, Easycase.story_point, Easycase.thread_count, Easycase.custom_status_id, Easycase.is_splitted, IF((Easycase.assign_to = ", userid, "),'Me',User.short_name) AS Assigned, EasycaseMilestone.milestone_id, EasycaseMilestone.m_order, User.short_name, User.name
        FROM
            easycases as Easycase
            INNER JOIN projects ON Easycase.project_id = projects.id AND projects.isactive = '1'
            INNER JOIN project_users ON project_users.company_id = ", companyid, " AND project_users.user_id = ", userid, " AND project_users.project_id = projects.id
            LEFT JOIN users User ON Easycase.assign_to = User.id
            LEFT JOIN easycase_milestones EasycaseMilestone ON Easycase.id = EasycaseMilestone.easycase_id AND EasycaseMilestone.user_id = project_users.user_id
        WHERE
            Easycase.project_id = IF(", projId, " = 0, Easycase.project_id, ", projId, ")
            AND Easycase.istype = '1'
            AND ", prm_clt_sql, " ", prm_cond_easycase_actuve, " ", prm_searchcase, prm_qry, "
        ORDER BY ", REPLACE(prm_orderby, "EasycaseMilestone", "Easycase"), "
        LIMIT ", OFFSET, ",", vcount
    );
    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET @query = "";
    SET @query = CONCAT(
        "SELECT
            SQL_CALC_FOUND_ROWS Easycase.id, Easycase.uniq_id, Easycase.case_no, Easycase.case_count, Easycase.project_id, Easycase.user_id, Easycase.updated_by, Easycase.type_id, Easycase.priority, Easycase.title, Easycase.estimated_hours, Easycase.hours, Easycase.completed_task, Easycase.assign_to, Easycase.gantt_start_date, Easycase.initial_due_date, Easycase.due_date, Easycase.istype, Easycase.client_status, Easycase.format, Easycase.status, Easycase.legend, Easycase.is_recurring, Easycase.isactive, Easycase.dt_created, Easycase.actual_dt_created, Easycase.reply_type, Easycase.is_chrome_extension, Easycase.from_email, Easycase.depends, Easycase.children, Easycase.temp_est_hours, Easycase.seq_id, Easycase.parent_task_id, Easycase.story_point, Easycase.thread_count, Easycase.custom_status_id, Easycase.short_name, Easycase.name, Assigned, Easycase.is_sub_sub_task, IFNULL(Easycase.sub_sub_task, 0) sub_sub_task, Easycase.tot_spent_hour, Easycase.is_splitted, Easycase.milestone_id, Easycase.m_order
        FROM
        (
            SELECT
                Easycase.id, Easycase.uniq_id, Easycase.case_no, Easycase.case_count, Easycase.project_id, Easycase.user_id, Easycase.updated_by, Easycase.type_id, Easycase.priority, Easycase.title, Easycase.estimated_hours, Easycase.hours, Easycase.completed_task, Easycase.assign_to, Easycase.gantt_start_date, Easycase.initial_due_date, Easycase.due_date, Easycase.istype, Easycase.client_status, Easycase.format, Easycase.status, Easycase.legend, Easycase.is_recurring, Easycase.isactive, Easycase.dt_created, Easycase.actual_dt_created, Easycase.reply_type, Easycase.is_chrome_extension, Easycase.from_email, Easycase.depends, Easycase.children, Easycase.temp_est_hours, Easycase.seq_id, Easycase.parent_task_id, Easycase.story_point, Easycase.thread_count, Easycase.custom_status_id, IS_SUB.is_sub_sub_task, IS_SUB.sub_sub_task, tot_spent_hour, Easycase.is_splitted, Easycase.milestone_id, Easycase.m_order, Easycase.Assigned, Easycase.short_name, Easycase.name
            FROM
                temp_easycases as Easycase
                LEFT JOIN
                (
                    SELECT
                        Easycase.id, Easycase.parent_task_id is_sub_sub_task, COUNT(Easycase.parent_task_id) sub_sub_task
                    FROM
                        easycases as Easycase
                        INNER JOIN projects ON Easycase.project_id = projects.id AND projects.isactive = '1'
                        INNER JOIN project_users ON project_users.company_id = ", companyid, " AND project_users.user_id = ", userid, " AND project_users.project_id = projects.id
                    WHERE
                        Easycase.istype = '1' AND 1 AND Easycase.isactive = 1 AND Easycase.project_id = IF(", projId, " = 0, Easycase.project_id, ", projId, ")
                    GROUP BY
                        Easycase.id, Easycase.parent_task_id
                ) IS_SUB ON IS_SUB.id = Easycase.parent_task_id
                LEFT JOIN
                (
                    SELECT
                        SUM(t.total_hours) tot_spent_hour, t.task_id
                    FROM
                        log_times t
                        INNER JOIN projects ON t.project_id = projects.id AND projects.isactive = '1'
                        INNER JOIN project_users ON project_users.company_id = ", companyid, " AND project_users.project_id = projects.id AND t.user_id = project_users.user_id
                    WHERE
                        t.project_id = IF(", projId, " = 0, t.project_id, ", projId, ")
                    GROUP BY
                        t.task_id
                ) as lt ON Easycase.id = lt.task_id
        ) AS Easycase
        "
    );
    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END