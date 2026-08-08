CREATE  PROCEDURE `sp_RESOURCE_UTILIZATION`(
    IN companyid INT(11),
    IN frmTz VARCHAR(100),
    IN toTz VARCHAR(100),
    IN usr_cond VARCHAR(1000),
    IN log_condition VARCHAR(1000),
    IN qry VARCHAR(1000),
    IN grpby1 VARCHAR(1000),
    IN search_cond VARCHAR(1000)
)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS tmp_logtime;

    SET @query = CONCAT(
        "CREATE TEMPORARY TABLE tmp_logtime ENGINE=MEMORY ",
        "SELECT DISTINCT a.id task_id, a.estimated_hours ",
        "FROM easycases a, log_times b, projects c ",
        "WHERE a.id = b.task_id ",
        "AND b.project_id = c.id ",
        "AND c.company_id = ", companyid, " ",
        log_condition
    );

    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    SET @queryy = CONCAT(
        "SELECT Result.*, SUM(p.estimated_hours) AS est_hrs ",
        "FROM (",
            "SELECT ",
                "LogTime.user_id, ",
                "SUM(LogTime.total_hours) AS hours, ",
                "SUM(Easycase.estimated_hours) AS est_task, ",
                "GROUP_CONCAT(DISTINCT LogTime.task_id) AS esthrs, ",
                "IF(LogTime.is_billable = 1, 'Yes', 'No') AS billable, ",
                "User.name, ",
                "User.last_name, ",
                "Project.name AS pname, ",
                "Easycase.id, ",
                "Easycase.title, ",
                "Easycase.legend, ",
                "Easycase.custom_status_id, ",
                "Easycase.type_id, ",
                "Easycase.case_no, ",
                "CONVERT_TZ(LogTime.start_datetime, '", frmTz, "', '", toTz, "') AS start_datetime_n, ",
                "LogTime.project_id, ",
                "Milestone.title AS mlstn_name, ",
                "EasycaseLabel.label_id, ",
                "Label.lbl_title AS label ",
            "FROM log_times AS LogTime ",
            "LEFT JOIN easycases AS Easycase ON LogTime.task_id = Easycase.id AND LogTime.project_id = Easycase.project_id ",
            "LEFT JOIN easycase_milestones AS EasycaseMilestone ON LogTime.task_id = EasycaseMilestone.easycase_id ",
            "LEFT JOIN milestones AS Milestone ON EasycaseMilestone.milestone_id = Milestone.id ",
            "LEFT JOIN users AS User ON LogTime.user_id = User.id ",
            "LEFT JOIN projects AS Project ON LogTime.project_id = Project.id ",
            "LEFT JOIN easycase_labels AS EasycaseLabel ON Easycase.id = EasycaseLabel.easycase_id AND Easycase.project_id = EasycaseLabel.project_id ",
            "LEFT JOIN labels AS Label ON Label.id = EasycaseLabel.label_id AND Label.company_id = EasycaseLabel.company_id ",
            "WHERE LogTime.project_id IN (SELECT id FROM projects AS prj WHERE prj.company_id = ", companyid, ") ",
            "AND Easycase.isactive = 1 ",
            "AND ", usr_cond, " ",
            log_condition, " ",
            "AND Project.company_id = ", companyid, " ",
            qry, " ",
            search_cond, " ",
            "GROUP BY DATE(start_datetime_n), LogTime.user_id, LogTime.project_id, Easycase.id, Milestone.title, billable",
        ") AS Result ",
        "LEFT JOIN tmp_logtime AS p ON FIND_IN_SET(p.task_id, Result.esthrs) ",
        "WHERE p.task_id IS NOT NULL AND Result.id IS NOT NULL ",
        grpby1
    );

    PREPARE stmtt FROM @queryy;
    EXECUTE stmtt;
    DEALLOCATE PREPARE stmtt;
END