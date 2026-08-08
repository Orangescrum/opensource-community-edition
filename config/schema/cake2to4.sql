ALTER TABLE companies ADD parent_company_id INT NULL;
ALTER TABLE companies ADD company_type_id INT NULL;
ALTER TABLE projects ADD purpose_type varchar(100) NULL DEFAULT 'project';
ALTER TABLE projects ADD parent_id varchar(100);
UPDATE projects SET purpose_type='project';
ALTER TABLE project_metas ADD project_code varchar(100);
ALTER TABLE invoice_customers ADD customer_code INT NULL;
UPDATE menus SET meta='{"url":"mydashboard","li_id":"","a_id":"","li_class":"","a_class":"","a_click":"resetAllProjectFromDbd();","li_click":"","cnt_span":"","a_tooltip":""}' WHERE id=1;

INSERT INTO menus (id, parent_id, name, is_active, menu_type, menu_icon, menu_order, default_menu, conditional_menu, meta, created, modified) VALUES(61, 0, 'Programs', 1, 0, '<i class="left-menu-icon material-icons">cases</i>', 2, 1, 0, '{"url":"programs","li_id":"","a_id":"","li_class":"","a_class":"","a_click":"","li_click":"","cnt_span":"","a_tooltip":""}', '2020-01-10 00:00:00', '2020-01-10 00:00:00');

ALTER TABLE user_skills ADD years_of_experience INT NULL;
