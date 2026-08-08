-- MySQL dump 10.13  Distrib 8.0.39, for Linux (x86_64)
--
-- Host: 192.168.2.226    Database: osc4_2
-- ------------------------------------------------------
-- Server version	8.0.39-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping routines for database 'osc4_2'
--
/*!50003 DROP PROCEDURE IF EXISTS `sp_ALL_PROJ_TASK_LIST` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES' */ ;
DELIMITER ;;
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_Project_List` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES' */ ;
DELIMITER ;;
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_PROJ_TASK_LIST` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES' */ ;
DELIMITER ;;
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_Resource_Allocation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES' */ ;
DELIMITER ;;
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_RESOURCE_UTILIZATION` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES' */ ;
DELIMITER ;;
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-09-12 18:05:33
