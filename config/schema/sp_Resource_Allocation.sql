CREATE  PROCEDURE `sp_Resource_Allocation`(
    IN compid INT(11),
    IN projid INT(11),
    IN userid INT(11),
    IN fr_dt DATETIME,
    IN to_dt DATETIME
)
BEGIN
    SELECT 
        Easycase.case_no,
        Easycase.project_id,
        Easycase.assign_to,
        Easycase.legend,
        Easycase.title,
        Easycase.uniq_id,
        Easycase.due_date,
        Easycase.gantt_start_date,
        Easycase.estimated_hours,
        Easycase.actual_dt_created,
        Easycase.dt_created,
        Easycase.id,
        a.*
    FROM easycases Easycase
    INNER JOIN (
        SELECT DISTINCT ProjectUser.project_id
        FROM project_users AS ProjectUser, projects AS Project
        WHERE ProjectUser.company_id = compid
            AND ProjectUser.project_id = Project.id
            AND Project.isactive = '1'
            AND ProjectUser.project_id = IF(projid = 0, ProjectUser.project_id, projid)
            AND ProjectUser.user_id = IF(userid = 0, ProjectUser.user_id, userid)
    ) c ON Easycase.project_id = c.project_id
    LEFT JOIN (
        SELECT 
            SUM(aa.booked_hours) booked_hours,
            SUM(aa.overload) overload,
            aa.date,
            aa.easycase_id
        FROM (
            SELECT 
                ProjectBookedResource.booked_hours,
                0 overload,
                ProjectBookedResource.date,
                ProjectBookedResource.easycase_id
            FROM project_booked_resources AS ProjectBookedResource
            WHERE ProjectBookedResource.company_id = compid
                AND ProjectBookedResource.project_id = IF(projid = 0, ProjectBookedResource.project_id, projid)
                AND ProjectBookedResource.user_id = IF(userid = 0, ProjectBookedResource.user_id, userid)
                AND DATE(ProjectBookedResource.date) BETWEEN DATE(fr_dt) AND DATE(to_dt)
            UNION
            SELECT 
                0 booked_hours,
                Overload.overload,
                Overload.date,
                Overload.easycase_id
            FROM overloads AS Overload
            WHERE Overload.company_id = compid
                AND Overload.project_id = IF(projid = 0, Overload.project_id, projid)
                AND Overload.user_id = IF(userid = 0, Overload.user_id, userid)
                AND DATE(Overload.date) BETWEEN DATE(fr_dt) AND DATE(to_dt)
        ) aa
        GROUP BY aa.date, aa.easycase_id
    ) a ON a.easycase_id = Easycase.id
    WHERE Easycase.istype = 1
        AND Easycase.isactive = 1
        AND Easycase.project_id = IF(projid = 0, Easycase.project_id, projid)
        AND 1 = 1
        AND (Easycase.assign_to = IF(userid = 0, Easycase.assign_to, userid))
        AND Easycase.project_id != 0
        AND (
            DATE(Easycase.gantt_start_date) BETWEEN DATE(fr_dt) AND DATE(to_dt)
            OR DATE(Easycase.due_date) BETWEEN DATE(fr_dt) AND DATE(to_dt)
        );
END