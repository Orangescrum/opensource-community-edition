CREATE  PROCEDURE `sp_Project_List`(
    IN compid INT(11),
    IN userid INT(11),
    IN offset SMALLINT(5),
    IN vcount SMALLINT(5)
)
BEGIN
    SELECT SQL_CALC_FOUND_ROWS
        Project.id,
        Project.uniq_id,
        Project.name,
        Project.user_id,
        project_type,
        Project.short_name,
        Project.description,
        Project.isactive,
        Project.status,
        Project.estimated_hours,
        Project.priority,
        Project.dt_created,
        Project.dt_updated,
        Project.start_date,
        Project.end_date,
        Project.project_methodology_id,
        Project.status_group_id,
        (SELECT COUNT(easycases.id) AS tot
         FROM easycases
         WHERE easycases.project_id = Project.id
           AND easycases.istype = '1'
           AND easycases.isactive = '1') AS totalcase,
        (SELECT SUM(LogTime.total_hours) AS hours
         FROM log_times AS LogTime
         LEFT JOIN easycases AS Easycase ON Easycase.id = LogTime.task_id
           AND LogTime.project_id = Easycase.project_id
         WHERE LogTime.project_id = Project.id
           AND Easycase.isactive = 1) AS totalhours,
        (SELECT COUNT(company_users.id) AS tot
         FROM company_users, project_users
         WHERE company_users.company_id = compid
           AND project_users.user_id = company_users.user_id
           AND project_users.company_id = company_users.company_id
           AND company_users.is_active = 1
           AND project_users.project_id = Project.id) AS totusers,
        (SELECT SUM(case_files.file_size) AS file_size
         FROM case_files
         WHERE case_files.project_id = Project.id) AS storage_used,
        (SELECT roles.role
         FROM project_users, roles
         WHERE project_users.company_id = compid
           AND project_users.user_id = userid
           AND project_users.role_id = roles.id
           AND project_users.project_id = Project.id
         GROUP BY project_users.id) AS role,
        (SELECT roles.role
         FROM roles, company_users
         WHERE company_users.role_id = roles.id
           AND company_users.user_id = userid
           AND company_users.company_id = compid
         GROUP BY company_users.id) AS crole
    FROM projects AS Project
    LEFT JOIN project_metas AS ProjectMeta ON ProjectMeta.company_id = compid
        AND ProjectMeta.project_id = Project.id
    LEFT JOIN invoice_customers AS Client ON Client.company_id = compid
        AND Client.id = ProjectMeta.client
    LEFT JOIN users AS User ON User.uniq_id = ProjectMeta.project_manager
    LEFT JOIN project_types AS Types ON Types.id = ProjectMeta.proj_type
        AND Types.company_id = compid
    LEFT JOIN (
        SELECT MAX(dt_visited) dt_visited, project_id
        FROM project_users
        WHERE project_users.company_id = compid
        GROUP BY project_id
    ) AS ProjectUser ON ProjectUser.project_id = Project.id
    WHERE Project.name != ''
      AND Project.company_id = compid
    LIMIT offset, vcount;
END