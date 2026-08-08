--
-- PostgreSQL database dump
--


-- Dumped from database version 16.14 (Debian 16.14-1.pgdg13+1)
-- Dumped by pg_dump version 16.14 (Debian 16.14-1.pgdg13+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: actions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.actions (
    id integer NOT NULL,
    uniq_id character varying(255) NOT NULL,
    module_id integer NOT NULL,
    action text NOT NULL,
    display_name character varying(225),
    display_group smallint DEFAULT '0'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: actions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.actions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.actions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_actions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_actions (
    id integer NOT NULL,
    easycase_id integer NOT NULL,
    user_id integer NOT NULL,
    action smallint DEFAULT '0'::smallint NOT NULL,
    dt_created timestamp(6) without time zone NOT NULL
);


--
-- Name: case_actions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_actions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_actions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_activities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_activities (
    id integer NOT NULL,
    easycase_id integer NOT NULL,
    comment_id integer,
    case_no integer,
    project_id integer NOT NULL,
    user_id integer NOT NULL,
    type smallint NOT NULL,
    isactive smallint DEFAULT '1'::smallint NOT NULL,
    dt_created timestamp(6) without time zone NOT NULL
);


--
-- Name: case_activities_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_activities ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_activities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_comments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_comments (
    id integer NOT NULL,
    easycase_id integer NOT NULL,
    comments text NOT NULL,
    user_id integer NOT NULL,
    dt_created timestamp(6) without time zone NOT NULL,
    isactive smallint DEFAULT '1'::smallint NOT NULL
);


--
-- Name: case_comments_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_comments ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_comments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_editor_files; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_editor_files (
    id integer NOT NULL,
    uniq_id character varying(64) NOT NULL,
    company_id integer NOT NULL,
    project_id integer DEFAULT 0 NOT NULL,
    easycase_id integer DEFAULT 0 NOT NULL,
    user_id integer NOT NULL,
    name character varying(200) NOT NULL,
    file_size integer DEFAULT 0 NOT NULL,
    is_deleted smallint DEFAULT '0'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: case_editor_files_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_editor_files ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_editor_files_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_file_drives; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_file_drives (
    id integer NOT NULL,
    project_id integer NOT NULL,
    easycase_id integer NOT NULL,
    file_info text,
    cloud_provider character varying(50)
);


--
-- Name: case_file_drives_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_file_drives ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_file_drives_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_files; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_files (
    id integer NOT NULL,
    user_id integer NOT NULL,
    project_id integer NOT NULL,
    company_id integer NOT NULL,
    easycase_id integer NOT NULL,
    comment_id integer NOT NULL,
    file character varying(222) NOT NULL,
    display_name character varying(255),
    upload_name character varying(255),
    thumb character varying(222) NOT NULL,
    file_size numeric(7,1) NOT NULL,
    count integer,
    downloadurl text,
    weburl text,
    onedrive_item_id text,
    isactive smallint DEFAULT '1'::smallint NOT NULL,
    defect_id integer,
    defect_reply_id integer,
    execute_id integer,
    test_case_id integer,
    type smallint DEFAULT '1'::smallint,
    created timestamp(6) without time zone,
    modified timestamp(6) without time zone,
    cloud_provider character varying(50),
    cloud_file_id text,
    cloud_file_path text,
    cloud_thumbnail_url text,
    cloud_icon_url text,
    cloud_metadata text,
    cloud_last_synced timestamp without time zone,
    mime_type character varying(100)
);


--
-- Name: case_files_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_files ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_files_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_filters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_filters (
    id integer NOT NULL,
    user_id integer NOT NULL,
    "order" text
);


--
-- Name: case_filters_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_filters ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_filters_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_recents; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_recents (
    id integer NOT NULL,
    easycase_id integer NOT NULL,
    company_id integer NOT NULL,
    user_id integer NOT NULL,
    project_id integer NOT NULL,
    dt_created timestamp(6) without time zone NOT NULL
);


--
-- Name: case_recents_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_recents ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_recents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_reminders; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_reminders (
    id integer NOT NULL,
    company_id integer NOT NULL,
    user_id integer NOT NULL,
    easycase_id integer NOT NULL,
    comment text NOT NULL,
    reminder_datetime timestamp(6) without time zone NOT NULL,
    status smallint DEFAULT '0'::smallint NOT NULL,
    is_emailed smallint DEFAULT '0'::smallint NOT NULL,
    user_ids text NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL,
    project_id integer NOT NULL
);


--
-- Name: case_reminders_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_reminders ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_reminders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_removed_files; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_removed_files (
    id integer NOT NULL,
    case_file_id integer NOT NULL,
    project_id integer NOT NULL,
    user_id integer NOT NULL,
    company_id integer NOT NULL,
    case_file_name character varying(255) NOT NULL
);


--
-- Name: case_removed_files_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_removed_files ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_removed_files_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_settings (
    id integer NOT NULL,
    project_id integer NOT NULL,
    project_uniqid character varying(250) NOT NULL,
    type_id integer NOT NULL,
    assign_to integer NOT NULL,
    priority smallint NOT NULL,
    due_date character varying(250) NOT NULL,
    email character varying(250) NOT NULL,
    user_id integer NOT NULL
);


--
-- Name: case_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_settings ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_templates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_templates (
    id integer NOT NULL,
    user_id integer NOT NULL,
    company_id integer NOT NULL,
    name character varying(100) NOT NULL,
    description text NOT NULL,
    is_active smallint DEFAULT '1'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: case_templates_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_templates ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_templates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_user_emails; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_user_emails (
    id integer NOT NULL,
    easycase_id integer NOT NULL,
    user_id integer NOT NULL,
    ismail smallint DEFAULT '1'::smallint NOT NULL
);


--
-- Name: case_user_emails_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_user_emails ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_user_emails_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: case_user_views; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.case_user_views (
    id integer NOT NULL,
    easycase_id integer NOT NULL,
    user_id integer NOT NULL,
    project_id integer NOT NULL,
    istype smallint DEFAULT '1'::smallint NOT NULL,
    isviewed smallint DEFAULT '0'::smallint NOT NULL,
    dt_created timestamp(6) without time zone NOT NULL
);


--
-- Name: case_user_views_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.case_user_views ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.case_user_views_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: check_lists; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.check_lists (
    id integer NOT NULL,
    uniq_id character varying(64) NOT NULL,
    company_id integer NOT NULL,
    project_id integer NOT NULL,
    easycase_id integer NOT NULL,
    user_id integer NOT NULL,
    title text NOT NULL,
    is_checked boolean DEFAULT false NOT NULL,
    sequence integer DEFAULT 0 NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: check_lists_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.check_lists ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.check_lists_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: companies; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.companies (
    id integer NOT NULL,
    uniq_id text,
    name character varying(250) NOT NULL,
    seo_url character varying(250) NOT NULL,
    logo character varying(100) NOT NULL,
    website character varying(100) NOT NULL,
    contact_phone character varying(100) NOT NULL,
    referrer text,
    industry_id integer DEFAULT 0 NOT NULL,
    work_hour numeric(10,2) DEFAULT 8 NOT NULL,
    week_ends character varying(100),
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL,
    user_last_login timestamp(6) without time zone NOT NULL,
    is_beta smallint DEFAULT '0'::smallint NOT NULL,
    is_active smallint DEFAULT '1'::smallint NOT NULL,
    is_deactivated smallint DEFAULT '0'::smallint NOT NULL,
    is_skipped smallint DEFAULT '0'::smallint NOT NULL,
    twitted smallint DEFAULT '0'::smallint NOT NULL,
    refering_plan_id integer DEFAULT 0 NOT NULL,
    country_name character varying(150) DEFAULT 'no'::character varying NOT NULL,
    new_layout_no smallint DEFAULT '0'::smallint NOT NULL,
    is_per_user smallint DEFAULT '0'::smallint NOT NULL,
    plan_user_count smallint DEFAULT '0'::smallint NOT NULL,
    is_delete_checked smallint DEFAULT '0'::smallint NOT NULL,
    add_defect_master smallint DEFAULT '0'::smallint,
    auth_token character varying(255),
    currency_id integer DEFAULT 144 NOT NULL,
    api_access_code character varying(8),
    parent_company_id integer DEFAULT 0,
    company_type_id integer,
    tenant_uuid character varying(36) NOT NULL
);


--
-- Name: companies_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.companies ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.companies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: company_apis; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.company_apis (
    id integer NOT NULL,
    company_id integer NOT NULL,
    api_key character varying(255) NOT NULL,
    is_active integer,
    created timestamp(6) without time zone NOT NULL,
    user_id integer DEFAULT 0 NOT NULL,
    project_id integer DEFAULT 0 NOT NULL
);


--
-- Name: company_apis_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.company_apis ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.company_apis_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: company_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.company_types (
    id integer NOT NULL,
    company_type_name character varying(255) NOT NULL,
    company_id integer
);


--
-- Name: company_types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.company_types ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.company_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: company_users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.company_users (
    id integer NOT NULL,
    company_id integer NOT NULL,
    company_uniq_id character varying(250) NOT NULL,
    user_id integer NOT NULL,
    user_type integer NOT NULL,
    is_active smallint DEFAULT '1'::smallint NOT NULL,
    is_access_change smallint DEFAULT '0'::smallint NOT NULL,
    change_timestamp bigint DEFAULT '0'::bigint NOT NULL,
    is_client smallint DEFAULT '0'::smallint NOT NULL,
    role_id integer DEFAULT 0 NOT NULL,
    est_billing_amt real DEFAULT '0'::real NOT NULL,
    act_date timestamp(6) without time zone,
    billing_start_date timestamp(6) without time zone,
    billing_end_date timestamp(6) without time zone,
    company_trial_expired smallint DEFAULT '0'::smallint NOT NULL,
    google_token text,
    is_dummy smallint DEFAULT '0'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL,
    business_unit_id integer,
    company_parent_id integer
);


--
-- Name: company_users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.company_users ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.company_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: countries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.countries (
    id integer NOT NULL,
    ccode character varying(2) DEFAULT ''::character varying NOT NULL,
    country character varying(200) DEFAULT ''::character varying NOT NULL
);


--
-- Name: countries_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.countries ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.countries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: currencies; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.currencies (
    id integer NOT NULL,
    name character varying(64),
    code character varying(3),
    cur_symbol character varying(7),
    status character varying(255) DEFAULT 'Active'::character varying NOT NULL
);


--
-- Name: currencies_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.currencies ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.currencies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: custom_filters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.custom_filters (
    id integer NOT NULL,
    project_uniq_id character varying(64) NOT NULL,
    company_id integer NOT NULL,
    user_id integer NOT NULL,
    filter_name character varying(100) NOT NULL,
    filter_date text,
    filter_duedate timestamp(6) without time zone,
    filter_type_id text,
    filter_status text,
    filter_member_id text,
    filter_priority text,
    filter_assignto text,
    filter_search text,
    dt_created timestamp(6) without time zone NOT NULL
);


--
-- Name: custom_filters_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.custom_filters ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.custom_filters_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: custom_statuses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.custom_statuses (
    id integer NOT NULL,
    company_id integer NOT NULL,
    name character varying(100) NOT NULL,
    progress integer NOT NULL,
    color character varying(25) NOT NULL,
    status_master_id integer NOT NULL,
    status_group_id integer NOT NULL,
    seq integer DEFAULT 0 NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: custom_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.custom_statuses ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.custom_statuses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: default_task_views; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.default_task_views (
    id integer NOT NULL,
    company_id integer NOT NULL,
    user_id integer NOT NULL,
    task_view_id smallint DEFAULT '1'::smallint NOT NULL,
    kanban_view_id smallint DEFAULT '7'::smallint NOT NULL,
    timelog_view_id smallint DEFAULT '5'::smallint NOT NULL,
    project_view_id smallint DEFAULT '8'::smallint NOT NULL,
    default_view_id smallint DEFAULT '0'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL,
    task_type_filter text DEFAULT '{"epic":0,"feature":0,"story":1}'::text NOT NULL,
    task_detail_view character varying(10) DEFAULT 'tab'::character varying NOT NULL
);


--
-- Name: default_task_views_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.default_task_views ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.default_task_views_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: default_tasks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.default_tasks (
    id integer NOT NULL,
    task character varying(200) NOT NULL,
    description text
);


--
-- Name: default_tasks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.default_tasks ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.default_tasks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: duedate_change_reasons; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.duedate_change_reasons (
    id integer NOT NULL,
    reason text NOT NULL,
    company_id integer NOT NULL,
    user_id integer NOT NULL,
    modified_by integer NOT NULL,
    is_default boolean DEFAULT false NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: duedate_change_reasons_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.duedate_change_reasons ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.duedate_change_reasons_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: easycase_favourites; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.easycase_favourites (
    id integer NOT NULL,
    company_id integer,
    project_id integer,
    user_id integer,
    easycase_id integer,
    created timestamp(6) without time zone,
    modified timestamp(6) without time zone
);


--
-- Name: easycase_favourites_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.easycase_favourites ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.easycase_favourites_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: easycase_labels; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.easycase_labels (
    id integer NOT NULL,
    easycase_id integer NOT NULL,
    label_id integer NOT NULL,
    company_id integer NOT NULL,
    project_id integer NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: easycase_labels_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.easycase_labels ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.easycase_labels_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: easycase_linkings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.easycase_linkings (
    id integer NOT NULL,
    easycase_id integer NOT NULL,
    link_id integer NOT NULL,
    company_id integer NOT NULL,
    project_id integer NOT NULL,
    easycase_relate_id integer NOT NULL
);


--
-- Name: easycase_linkings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.easycase_linkings ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.easycase_linkings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: easycase_links; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.easycase_links (
    id integer NOT NULL,
    project_id integer NOT NULL,
    source character varying(50) NOT NULL,
    target character varying(50) NOT NULL,
    type smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: easycase_links_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.easycase_links ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.easycase_links_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: easycase_mentions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.easycase_mentions (
    id integer NOT NULL,
    company_id integer NOT NULL,
    project_id integer NOT NULL,
    mention_type_id integer NOT NULL,
    mention_type integer NOT NULL,
    mention_by integer NOT NULL,
    easycase_id integer NOT NULL,
    comment_id integer DEFAULT 0,
    mention_message text,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: easycase_mentions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.easycase_mentions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.easycase_mentions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: easycase_milestones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.easycase_milestones (
    id integer NOT NULL,
    easycase_id integer NOT NULL,
    milestone_id integer NOT NULL,
    project_id integer NOT NULL,
    user_id integer NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    m_order integer NOT NULL,
    id_seq integer DEFAULT 0 NOT NULL
);


--
-- Name: easycase_milestones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.easycase_milestones ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.easycase_milestones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: easycase_recurring_tracks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.easycase_recurring_tracks (
    id integer NOT NULL,
    project_id integer NOT NULL,
    easycase_id integer NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: easycase_recurring_tracks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.easycase_recurring_tracks ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.easycase_recurring_tracks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: easycase_relates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.easycase_relates (
    id integer NOT NULL,
    title character varying(255) NOT NULL,
    status integer DEFAULT 1 NOT NULL,
    seq_id integer NOT NULL
);


--
-- Name: easycase_relates_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.easycase_relates ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.easycase_relates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: easycases; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.easycases (
    id integer NOT NULL,
    uniq_id character varying(64) NOT NULL,
    case_no integer NOT NULL,
    case_count integer NOT NULL,
    company_id integer,
    project_id integer NOT NULL,
    user_id integer NOT NULL,
    updated_by integer NOT NULL,
    type_id integer NOT NULL,
    priority character varying(4),
    title text,
    message text,
    estimated_hours integer DEFAULT 0 NOT NULL,
    hours numeric(6,1),
    completed_task integer DEFAULT 0 NOT NULL,
    assign_to integer NOT NULL,
    gantt_start_date timestamp(6) without time zone,
    due_date timestamp(6) without time zone,
    istype smallint DEFAULT '1'::smallint NOT NULL,
    is_splitted smallint DEFAULT '0'::smallint NOT NULL,
    client_status smallint DEFAULT '0'::smallint NOT NULL,
    format smallint DEFAULT '1'::smallint NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    legend smallint NOT NULL,
    isactive smallint DEFAULT '1'::smallint NOT NULL,
    is_recurring smallint DEFAULT '0'::smallint NOT NULL,
    dt_created timestamp(6) without time zone NOT NULL,
    dt_closed timestamp(6) without time zone,
    actual_dt_created timestamp(6) without time zone NOT NULL,
    reply_type integer DEFAULT 0 NOT NULL,
    is_chrome_extension boolean DEFAULT false NOT NULL,
    from_email boolean DEFAULT false NOT NULL,
    depends character varying(255),
    children character varying(255),
    temp_hours integer,
    temp_est_hours integer DEFAULT 0 NOT NULL,
    temp_est_hours_back real,
    seq_id integer,
    parent_task_id integer,
    custom_status_id integer DEFAULT 0 NOT NULL,
    thread_count integer DEFAULT 0 NOT NULL,
    git_sync smallint DEFAULT '0'::smallint NOT NULL,
    git_issue_id bigint DEFAULT '0'::bigint NOT NULL,
    real_git_issue_id bigint DEFAULT '0'::bigint NOT NULL,
    is_zapaction boolean DEFAULT false,
    initial_due_date timestamp(6) without time zone,
    epic_id integer,
    is_approved boolean,
    approver_id integer,
    approved_by integer,
    approval_status character varying(50),
    dt_approved timestamp(6) without time zone,
    feature_id integer,
    dependency_type text
);


--
-- Name: easycases_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.easycases ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.easycases_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: email_reminders; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.email_reminders (
    id integer NOT NULL,
    user_id integer NOT NULL,
    email_type integer NOT NULL,
    cron_date date NOT NULL
);


--
-- Name: email_reminders_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.email_reminders ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.email_reminders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: email_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.email_settings (
    id integer NOT NULL,
    company_id integer,
    user_id integer,
    host character varying(255),
    port character varying(255),
    is_smtp integer,
    email character varying(255),
    password character varying(255),
    from_email character varying(255),
    reply_email character varying(255),
    status smallint,
    is_default smallint,
    is_verified integer,
    created timestamp(6) without time zone,
    modified timestamp(6) without time zone
);


--
-- Name: email_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.email_settings ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.email_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: feedback; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.feedback (
    id integer NOT NULL,
    user_id integer NOT NULL,
    company_id integer NOT NULL,
    username character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    star boolean NOT NULL,
    message text NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: guest_role_actions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.guest_role_actions (
    id integer NOT NULL,
    company_id integer NOT NULL,
    role_id integer NOT NULL,
    action_details text
);


--
-- Name: guest_role_actions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.guest_role_actions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.guest_role_actions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: helps; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.helps (
    id integer NOT NULL,
    subject_id integer NOT NULL,
    title character varying(200) NOT NULL,
    description text NOT NULL,
    image text NOT NULL,
    keywords text NOT NULL,
    created timestamp(6) without time zone DEFAULT '2013-10-10 00:00:00'::timestamp without time zone NOT NULL,
    is_admin smallint DEFAULT '0'::smallint NOT NULL
);


--
-- Name: helps_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.helps ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.helps_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: industries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.industries (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    is_display smallint DEFAULT '1'::smallint NOT NULL
);


--
-- Name: industries_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.industries ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.industries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: invoice_customers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.invoice_customers (
    id integer NOT NULL,
    uniq_id character varying(64),
    company_id integer DEFAULT 0 NOT NULL,
    project_id integer NOT NULL,
    first_name character varying(100),
    last_name character varying(100),
    street text,
    city character varying(100),
    state character varying(100),
    country character varying(100),
    zipcode character varying(10),
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL,
    email character varying(100),
    phone character varying(50),
    title character varying(25),
    organization character varying(255),
    currency character varying(5),
    status character varying(255) DEFAULT 'Active'::character varying NOT NULL,
    user_id integer,
    customer_code character varying(100)
);


--
-- Name: invoice_customers_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.invoice_customers ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.invoice_customers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: labels; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.labels (
    id integer NOT NULL,
    lbl_title character varying(50) NOT NULL,
    company_id integer NOT NULL,
    project_id integer DEFAULT 0 NOT NULL,
    user_id integer NOT NULL,
    is_active smallint DEFAULT '1'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: labels_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.labels ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.labels_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: languages; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.languages (
    id integer NOT NULL,
    language character varying(255),
    short_code character varying(5)
);


--
-- Name: languages_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.languages ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.languages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: log_activities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.log_activities (
    id integer NOT NULL,
    company_id integer,
    user_id integer,
    log_type_id integer,
    json_value text,
    created timestamp(6) without time zone,
    ip character varying(100)
);


--
-- Name: log_activities_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.log_activities ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.log_activities_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: log_times; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.log_times (
    log_id integer NOT NULL,
    user_id integer NOT NULL,
    project_id integer NOT NULL,
    task_id integer NOT NULL,
    task_date date NOT NULL,
    start_time time without time zone,
    end_time time without time zone,
    total_hours integer NOT NULL,
    is_billable smallint NOT NULL,
    description text NOT NULL,
    task_status smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    timesheet_flag smallint DEFAULT '0'::smallint NOT NULL,
    ip character varying(20) NOT NULL,
    start_datetime timestamp(6) without time zone,
    end_datetime timestamp(6) without time zone,
    break_time integer DEFAULT 0 NOT NULL,
    approver_id integer,
    pending_status integer DEFAULT 0 NOT NULL,
    is_from_timer boolean DEFAULT false
);


--
-- Name: log_times_log_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.log_times ALTER COLUMN log_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.log_times_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: log_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.log_types (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: log_types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.log_types ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.log_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: login_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.login_types (
    id integer NOT NULL,
    login_type character varying(255) NOT NULL
);


--
-- Name: login_types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.login_types ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.login_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: menu_languages; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.menu_languages (
    id integer NOT NULL,
    string_name text,
    en text,
    spa text,
    por text,
    deu text,
    fra text
);


--
-- Name: menu_languages_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.menu_languages ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.menu_languages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: menus; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.menus (
    id integer NOT NULL,
    parent_id integer NOT NULL,
    name character varying(255) NOT NULL,
    is_active smallint DEFAULT '1'::smallint NOT NULL,
    menu_type smallint NOT NULL,
    menu_icon character varying(150) NOT NULL,
    menu_order integer NOT NULL,
    default_menu smallint NOT NULL,
    conditional_menu smallint NOT NULL,
    meta text,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: menus_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.menus ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.menus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    from_company_id integer,
    to_company_id integer,
    user_id integer,
    previous_projects text,
    current_projects text,
    type integer DEFAULT 1,
    comment text,
    status integer,
    report character varying(255),
    created timestamp(6) without time zone,
    modified timestamp(6) without time zone
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.migrations ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.migrations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: milestones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.milestones (
    id integer NOT NULL,
    uniq_id character varying(250) NOT NULL,
    project_id integer NOT NULL,
    company_id integer NOT NULL,
    title character varying(250) NOT NULL,
    description text NOT NULL,
    user_id integer NOT NULL,
    closed_by integer,
    estimated_hours numeric(10,0) NOT NULL,
    duration smallint DEFAULT '0'::smallint NOT NULL,
    start_date date,
    end_date date,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL,
    completed_date timestamp(6) without time zone,
    isactive smallint DEFAULT '1'::smallint NOT NULL,
    is_started smallint DEFAULT '0'::smallint NOT NULL,
    id_seq smallint DEFAULT '0'::smallint
);


--
-- Name: milestones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.milestones ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.milestones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: modules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.modules (
    id integer NOT NULL,
    uniq_id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    is_active smallint DEFAULT '0'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: modules_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.modules ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.modules_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notifications (
    id integer NOT NULL,
    user_id integer NOT NULL,
    notification_info text,
    total_seen timestamp(6) without time zone,
    dt_created timestamp(6) without time zone NOT NULL
);


--
-- Name: notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.notifications ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: os_session_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.os_session_logs (
    id integer NOT NULL,
    user_id integer NOT NULL,
    user_agent text NOT NULL
);


--
-- Name: os_session_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.os_session_logs ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.os_session_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: project_actions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_actions (
    id integer NOT NULL,
    project_id integer NOT NULL,
    role_id integer NOT NULL,
    action_id integer NOT NULL,
    is_allowed smallint DEFAULT '1'::smallint NOT NULL
);


--
-- Name: project_actions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.project_actions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.project_actions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: project_metas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_metas (
    id integer NOT NULL,
    company_id integer NOT NULL,
    project_id integer NOT NULL,
    project_manager character varying(100) DEFAULT '0'::character varying NOT NULL,
    client integer DEFAULT 0 NOT NULL,
    currency integer,
    budget integer DEFAULT 0 NOT NULL,
    default_rate numeric(10,2) DEFAULT 0.00 NOT NULL,
    cost_appr integer DEFAULT 0 NOT NULL,
    min_tol smallint DEFAULT '0'::smallint NOT NULL,
    max_tol smallint DEFAULT '0'::smallint NOT NULL,
    proj_type integer DEFAULT 0 NOT NULL,
    industry integer,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL,
    project_code character varying(100)
);


--
-- Name: project_metas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.project_metas ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.project_metas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: project_methodologies; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_methodologies (
    id integer NOT NULL,
    title character varying(150) NOT NULL,
    status_group_id integer NOT NULL,
    listing_description text NOT NULL,
    short_description text NOT NULL,
    description text NOT NULL,
    thumbnail character varying(255) NOT NULL,
    full_image character varying(255) NOT NULL,
    project_template_view_id integer DEFAULT 0 NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    seq_no integer NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    updated timestamp(6) without time zone NOT NULL
);


--
-- Name: project_methodologies_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.project_methodologies ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.project_methodologies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: project_notes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_notes (
    id integer NOT NULL,
    uniq_id character varying(80) NOT NULL,
    company_id integer NOT NULL,
    user_id integer NOT NULL,
    project_id integer NOT NULL,
    note text NOT NULL,
    is_updated smallint DEFAULT '0'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: project_notes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.project_notes ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.project_notes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: project_notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_notifications (
    id integer NOT NULL,
    user_id integer NOT NULL,
    company_id integer NOT NULL,
    sent_mail smallint NOT NULL,
    frequncy smallint NOT NULL,
    day smallint NOT NULL,
    notification_time character varying(100) NOT NULL,
    proj_name character varying(200) NOT NULL,
    admin_user character varying(200) NOT NULL,
    role_name character varying(50) NOT NULL,
    mail_date date,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: project_notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.project_notifications ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.project_notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: project_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_settings (
    id integer NOT NULL,
    project_id integer NOT NULL,
    company_id integer NOT NULL,
    velocity_reports smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: project_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.project_settings ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.project_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: project_statuses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_statuses (
    id integer NOT NULL,
    company_id integer NOT NULL,
    user_id integer NOT NULL,
    name character varying(100) NOT NULL,
    is_active smallint DEFAULT '1'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: project_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.project_statuses ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.project_statuses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: project_technologies; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_technologies (
    id integer NOT NULL,
    project_id integer NOT NULL,
    technology_id integer
);


--
-- Name: project_technologies_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.project_technologies ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.project_technologies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: project_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_types (
    id integer NOT NULL,
    company_id integer NOT NULL,
    user_id integer NOT NULL,
    title character varying(100) NOT NULL,
    is_active smallint DEFAULT '1'::smallint,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: project_types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.project_types ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.project_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: project_users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_users (
    id integer NOT NULL,
    project_id integer NOT NULL,
    company_id integer NOT NULL,
    user_id integer NOT NULL,
    istype smallint DEFAULT '2'::smallint NOT NULL,
    default_email smallint DEFAULT '1'::smallint NOT NULL,
    dt_visited timestamp(6) without time zone NOT NULL,
    role_id integer DEFAULT 0
);


--
-- Name: project_users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.project_users ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.project_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: projects; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.projects (
    id integer NOT NULL,
    uniq_id character varying(64) NOT NULL,
    user_id integer NOT NULL,
    company_id integer NOT NULL,
    task_type integer,
    name character varying(255) NOT NULL,
    short_name character varying(100) NOT NULL,
    description text NOT NULL,
    logo character varying(100) NOT NULL,
    project_type smallint DEFAULT '1'::smallint NOT NULL,
    priority smallint DEFAULT '2'::smallint NOT NULL,
    default_assign integer NOT NULL,
    isactive smallint DEFAULT '1'::smallint NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    start_date date,
    end_date date,
    estimated_hours numeric(10,0),
    dt_created timestamp(6) without time zone NOT NULL,
    dt_updated timestamp(6) without time zone,
    is_multiple_sprint smallint DEFAULT '0'::smallint NOT NULL,
    project_methodology_id integer DEFAULT 1 NOT NULL,
    status_group_id integer DEFAULT 0 NOT NULL,
    defect_status_group_id integer DEFAULT 0 NOT NULL,
    is_zapaction boolean DEFAULT false,
    purpose_type character varying(255) DEFAULT 'project'::character varying,
    parent_id integer,
    organization_id integer
);


--
-- Name: projects_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.projects ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.projects_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: quicklink_menus; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.quicklink_menus (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    menu_language_id integer,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: quicklink_menus_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.quicklink_menus ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.quicklink_menus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: quicklink_submenus; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.quicklink_submenus (
    id integer NOT NULL,
    quicklink_menu_id integer NOT NULL,
    name character varying(255) NOT NULL,
    menu_language_id integer,
    action_name character varying(255),
    status smallint DEFAULT '1'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: quicklink_submenus_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.quicklink_submenus ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.quicklink_submenus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: recurring_easycases; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.recurring_easycases (
    id integer NOT NULL,
    easycase_id integer NOT NULL,
    recurring_type character varying(255),
    start_date date,
    occurrence integer DEFAULT 0,
    end_date date,
    recurring_end_type character varying(255),
    created timestamp(6) without time zone,
    project_id integer NOT NULL,
    company_id integer NOT NULL,
    frequency character varying(255),
    rec_interval integer DEFAULT 0,
    bymonthday integer DEFAULT 0,
    byday character varying(255),
    byweekno integer DEFAULT 0,
    bymonth integer DEFAULT 0,
    occurrences integer DEFAULT 0
);


--
-- Name: recurring_easycases_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.recurring_easycases ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.recurring_easycases_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: role_actions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_actions (
    id integer NOT NULL,
    company_id integer NOT NULL,
    role_id integer NOT NULL,
    action_id integer NOT NULL,
    is_allowed smallint DEFAULT '1'::smallint NOT NULL
);


--
-- Name: role_actions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.role_actions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.role_actions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: role_groups; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_groups (
    id integer NOT NULL,
    uniq_id character varying(255) NOT NULL,
    company_id integer NOT NULL,
    name character varying(255) NOT NULL,
    short_name character varying(255) NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: role_groups_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.role_groups ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.role_groups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: role_modules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_modules (
    id integer NOT NULL,
    company_id integer NOT NULL,
    module_id integer NOT NULL,
    role_id integer NOT NULL,
    is_active smallint DEFAULT '1'::smallint NOT NULL
);


--
-- Name: role_modules_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.role_modules ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.role_modules_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: role_rates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_rates (
    id integer NOT NULL,
    company_id integer NOT NULL,
    project_id integer NOT NULL,
    user_id integer NOT NULL,
    role_id integer,
    rate numeric,
    actual_rate numeric,
    is_active smallint DEFAULT '1'::smallint,
    created timestamp(6) without time zone NOT NULL,
    updated timestamp(6) without time zone NOT NULL,
    created_by integer NOT NULL,
    updated_by integer NOT NULL
);


--
-- Name: role_rates_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.role_rates ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.role_rates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id integer NOT NULL,
    uniq_id character varying(255) NOT NULL,
    company_id integer NOT NULL,
    role_group_id integer DEFAULT 0,
    role character varying(255) NOT NULL,
    short_name character varying(10) NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.roles ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: search_filters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.search_filters (
    id integer NOT NULL,
    user_id integer NOT NULL,
    company_id integer NOT NULL,
    name character varying(255) NOT NULL,
    "json_array" text NOT NULL,
    first_records integer DEFAULT 0 NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: search_filters_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.search_filters ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.search_filters_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(40) NOT NULL,
    data bytea,
    expires integer,
    created timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    modified timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: sidebar_menus; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sidebar_menus (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    menu_language_id integer,
    status smallint DEFAULT '1'::smallint NOT NULL,
    href_exist smallint DEFAULT '1'::smallint NOT NULL
);


--
-- Name: sidebar_menus_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.sidebar_menus ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.sidebar_menus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: sidebar_submenus; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sidebar_submenus (
    id integer NOT NULL,
    sidebar_menu_id integer NOT NULL,
    menu_language_id integer,
    name character varying(255) NOT NULL,
    status smallint DEFAULT '1'::smallint NOT NULL,
    href_exist smallint DEFAULT '1'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: sidebar_submenus_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.sidebar_submenus ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.sidebar_submenus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: status_groups; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.status_groups (
    id integer NOT NULL,
    parent_id integer DEFAULT 0 NOT NULL,
    company_id integer NOT NULL,
    name character varying(100) NOT NULL,
    description text NOT NULL,
    created_by integer NOT NULL,
    is_default smallint DEFAULT '0'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: status_groups_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.status_groups ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.status_groups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: status_masters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.status_masters (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    legend smallint NOT NULL
);


--
-- Name: status_masters_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.status_masters ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.status_masters_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: subjects; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.subjects (
    id integer NOT NULL,
    subject_name character varying(200) NOT NULL,
    seq_odr smallint DEFAULT '0'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: subjects_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.subjects ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.subjects_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: task_cycles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.task_cycles (
    id integer NOT NULL,
    task_id integer NOT NULL,
    status_id integer NOT NULL,
    start_time timestamp(6) without time zone NOT NULL,
    difference timestamp(6) without time zone
);


--
-- Name: task_cycles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.task_cycles ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.task_cycles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: task_due_change_reasons; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.task_due_change_reasons (
    id integer NOT NULL,
    easycase_id integer NOT NULL,
    duedate_change_reason_id integer NOT NULL,
    user_id integer NOT NULL,
    due_date timestamp(6) without time zone NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: task_due_change_reasons_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.task_due_change_reasons ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.task_due_change_reasons_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: task_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.task_settings (
    id integer NOT NULL,
    company_id integer NOT NULL,
    edit_task smallint DEFAULT '1'::smallint NOT NULL
);


--
-- Name: task_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.task_settings ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.task_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: task_views; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.task_views (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    sub_name character varying(255) NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: task_views_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.task_views ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.task_views_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: team_users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.team_users (
    id integer NOT NULL,
    user_id integer NOT NULL,
    team_id integer NOT NULL,
    role character varying(255),
    created timestamp(6) without time zone,
    modified timestamp(6) without time zone,
    status character varying(10),
    effective_start_date timestamp(6) without time zone DEFAULT CURRENT_TIMESTAMP,
    effective_end_date timestamp(6) without time zone,
    company_id integer NOT NULL
);


--
-- Name: team_users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.team_users ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.team_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: teams; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.teams (
    id integer NOT NULL,
    company_id integer NOT NULL,
    name character varying(255) NOT NULL,
    parent_id integer,
    description character varying(255),
    created timestamp(6) without time zone,
    modified timestamp(6) without time zone,
    status character varying(10),
    effective_start_date timestamp(6) without time zone DEFAULT CURRENT_TIMESTAMP,
    effective_end_date timestamp(6) without time zone
);


--
-- Name: teams_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.teams ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.teams_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: technologies; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.technologies (
    id integer NOT NULL,
    name character varying(50) NOT NULL
);


--
-- Name: technologies_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.technologies ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.technologies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: temp_users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.temp_users (
    id integer NOT NULL,
    uniq_id character varying(255) NOT NULL,
    user_id integer NOT NULL,
    company_id integer NOT NULL,
    email character varying(250) NOT NULL,
    type smallint DEFAULT '1'::smallint NOT NULL,
    is_active smallint DEFAULT '1'::smallint NOT NULL,
    ga_count integer,
    ref_id integer DEFAULT 0 NOT NULL,
    is_winner smallint DEFAULT '0'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: time_zone; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.time_zone (
    time_zone_id bigint NOT NULL,
    use_leap_seconds character varying(255) DEFAULT 'N'::character varying NOT NULL
);


--
-- Name: time_zone_time_zone_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.time_zone ALTER COLUMN time_zone_id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.time_zone_time_zone_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: timezone_names; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.timezone_names (
    id integer NOT NULL,
    gmt character varying(15) NOT NULL,
    zone character varying(100) NOT NULL
);


--
-- Name: timezones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.timezones (
    id integer NOT NULL,
    gmt_offset real DEFAULT '0'::real,
    dst_offset real,
    code character varying(4)
);


--
-- Name: timezones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.timezones ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.timezones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: tool_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tool_settings (
    id integer NOT NULL,
    days integer NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    updated timestamp(6) without time zone NOT NULL
);


--
-- Name: type_companies; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.type_companies (
    id integer NOT NULL,
    company_id integer NOT NULL,
    project_id integer DEFAULT 0,
    type_id integer NOT NULL
);


--
-- Name: type_companies_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.type_companies ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.type_companies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.types (
    id integer NOT NULL,
    company_id integer DEFAULT 0 NOT NULL,
    project_id integer DEFAULT 0 NOT NULL,
    short_name character varying(100) NOT NULL,
    name character varying(150) NOT NULL,
    seq_order integer NOT NULL,
    is_global integer
);


--
-- Name: types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.types ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: user_device_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_device_tokens (
    id integer NOT NULL,
    user_id integer NOT NULL,
    ios_device_token text NOT NULL,
    android_device_token text NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: user_device_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.user_device_tokens ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.user_device_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: user_infos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_infos (
    id integer NOT NULL,
    user_id integer,
    access_token text,
    is_google_signup smallint DEFAULT '0'::smallint NOT NULL
);


--
-- Name: user_infos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.user_infos ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.user_infos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: user_invitations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_invitations (
    id integer NOT NULL,
    invitor_id integer NOT NULL,
    company_id integer NOT NULL,
    user_type smallint DEFAULT '3'::smallint NOT NULL,
    project_id text,
    user_id integer NOT NULL,
    is_active smallint DEFAULT '1'::smallint NOT NULL,
    qstr character varying(100) NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    invite_token character varying(64)
);


--
-- Name: user_invitations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.user_invitations ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.user_invitations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: user_logins; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_logins (
    id integer NOT NULL,
    user_id integer NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: user_logins_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.user_logins ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.user_logins_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: user_menus; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_menus (
    id integer NOT NULL,
    user_id integer NOT NULL,
    company_id integer NOT NULL,
    menu text NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: user_menus_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.user_menus ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.user_menus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: user_notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_notifications (
    id integer NOT NULL,
    user_id integer NOT NULL,
    type smallint DEFAULT '1'::smallint NOT NULL,
    value smallint NOT NULL,
    due_val smallint NOT NULL,
    due_frequency smallint,
    new_case smallint DEFAULT '1'::smallint NOT NULL,
    reply_case smallint DEFAULT '1'::smallint NOT NULL,
    case_status smallint DEFAULT '1'::smallint NOT NULL,
    weekly_usage_alert smallint DEFAULT '1'::smallint NOT NULL,
    mention_case smallint DEFAULT '1'::smallint NOT NULL
);


--
-- Name: user_notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.user_notifications ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.user_notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: user_quicklinks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_quicklinks (
    id integer NOT NULL,
    user_id integer NOT NULL,
    company_id integer NOT NULL,
    quicklink_menu_id integer NOT NULL,
    quicklink_submenu_id integer NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone
);


--
-- Name: user_quicklinks_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.user_quicklinks ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.user_quicklinks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: user_sidebar_menus; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_sidebar_menus (
    id integer NOT NULL,
    user_id integer NOT NULL,
    company_id integer NOT NULL,
    sidebar_menu_id integer NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: user_sidebar_menus_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.user_sidebar_menus ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.user_sidebar_menus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: user_sidebar_submenus; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_sidebar_submenus (
    id integer NOT NULL,
    user_id integer NOT NULL,
    company_id integer NOT NULL,
    user_sidebar_menu_id integer NOT NULL,
    sidebar_submenu_id integer NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: user_sidebar_submenus_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.user_sidebar_submenus ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.user_sidebar_submenus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: user_themes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_themes (
    id integer NOT NULL,
    user_id integer NOT NULL,
    sidebar_color character varying(100),
    navbar_color character varying(100),
    mini_leftmenu smallint DEFAULT '0'::smallint NOT NULL,
    dark_leftmenu smallint DEFAULT '0'::smallint NOT NULL,
    dark_navbar smallint DEFAULT '0'::smallint NOT NULL,
    fixed_navbar smallint DEFAULT '0'::smallint NOT NULL,
    footer_dark smallint DEFAULT '0'::smallint NOT NULL,
    footer_fixed smallint DEFAULT '0'::smallint NOT NULL,
    created timestamp(6) without time zone NOT NULL
);


--
-- Name: user_themes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.user_themes ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.user_themes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    uniq_id character varying(64) NOT NULL,
    btprofile_id character varying(100),
    credit_cardtoken character varying(100),
    card_number character varying(255),
    expiry_date character varying(255),
    email character varying(150) NOT NULL,
    username character varying(255),
    update_email character varying(150),
    update_random character varying(150),
    password character varying(64),
    name character varying(150) NOT NULL,
    is_beta smallint DEFAULT '0'::smallint NOT NULL,
    last_name character varying(100),
    short_name character varying(100),
    istype smallint DEFAULT '3'::smallint NOT NULL,
    photo character varying(50),
    photo_reset character varying(50),
    isactive smallint DEFAULT '1'::smallint NOT NULL,
    timezone_id integer,
    isemail smallint DEFAULT '1'::smallint NOT NULL,
    is_agree smallint DEFAULT '1'::smallint NOT NULL,
    usersub_type smallint DEFAULT '0'::smallint,
    est_billing_amount real DEFAULT '0'::real,
    dt_created timestamp(6) without time zone NOT NULL,
    dt_updated timestamp(6) without time zone,
    dt_last_login timestamp(6) without time zone,
    dt_last_logout timestamp(6) without time zone,
    query_string character varying(100),
    gaccess_token text,
    google_id character varying(200),
    ip character varying(15),
    sig character varying(100),
    desk_notify smallint DEFAULT '1'::smallint NOT NULL,
    active_dashboard_tab integer DEFAULT 7 NOT NULL,
    is_moderator smallint DEFAULT '0'::smallint NOT NULL,
    verify_string character varying(100),
    show_default_inner smallint DEFAULT '1'::smallint NOT NULL,
    updated_by integer DEFAULT 0 NOT NULL,
    is_online smallint DEFAULT '0'::smallint,
    is_dst smallint DEFAULT '0'::smallint NOT NULL,
    language_id integer DEFAULT 2 NOT NULL,
    is_agree_tosp smallint DEFAULT '1'::smallint NOT NULL,
    is_receive_update smallint DEFAULT '0'::smallint NOT NULL,
    outer_signup smallint DEFAULT '0'::smallint NOT NULL,
    language character varying(10) DEFAULT 'eng'::character varying NOT NULL,
    time_format smallint DEFAULT '12'::smallint NOT NULL,
    phone character varying(20) DEFAULT '0'::character varying NOT NULL,
    is_dummy smallint DEFAULT '0'::smallint NOT NULL,
    one_tap_token text,
    keep_hover_effect smallint DEFAULT '0'::smallint NOT NULL,
    linkedin_id character varying(100) DEFAULT '0'::character varying NOT NULL,
    is_zapaction boolean DEFAULT false
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.users ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: workflow_actions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workflow_actions (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    is_active boolean DEFAULT true NOT NULL
);


--
-- Name: workflow_actions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.workflow_actions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.workflow_actions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: workflow_conditions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workflow_conditions (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    is_active smallint NOT NULL
);


--
-- Name: workflow_conditions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.workflow_conditions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.workflow_conditions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: workflow_details; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workflow_details (
    id integer NOT NULL,
    workflow_id integer NOT NULL,
    workflow_condition_id integer NOT NULL,
    workflow_action_id integer NOT NULL,
    condition_details text,
    action_details text,
    created timestamp(6) without time zone NOT NULL,
    modified timestamp(6) without time zone NOT NULL
);


--
-- Name: workflow_details_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.workflow_details ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.workflow_details_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: workflows; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workflows (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    project_id integer DEFAULT 0,
    company_id integer DEFAULT 0 NOT NULL,
    project_uniq_id character varying(255),
    created_by integer NOT NULL,
    updated_by integer NOT NULL,
    created timestamp(6) without time zone NOT NULL,
    updated timestamp(6) without time zone NOT NULL
);


--
-- Name: workflows_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.workflows ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME public.workflows_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: actions actions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actions
    ADD CONSTRAINT actions_pkey PRIMARY KEY (id);


--
-- Name: case_actions case_actions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_actions
    ADD CONSTRAINT case_actions_pkey PRIMARY KEY (id);


--
-- Name: case_activities case_activities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_activities
    ADD CONSTRAINT case_activities_pkey PRIMARY KEY (id);


--
-- Name: case_comments case_comments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_comments
    ADD CONSTRAINT case_comments_pkey PRIMARY KEY (id);


--
-- Name: case_editor_files case_editor_files_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_editor_files
    ADD CONSTRAINT case_editor_files_pkey PRIMARY KEY (id);


--
-- Name: case_file_drives case_file_drives_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_file_drives
    ADD CONSTRAINT case_file_drives_pkey PRIMARY KEY (id);


--
-- Name: case_files case_files_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_files
    ADD CONSTRAINT case_files_pkey PRIMARY KEY (id);


--
-- Name: case_filters case_filters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_filters
    ADD CONSTRAINT case_filters_pkey PRIMARY KEY (id);


--
-- Name: case_recents case_recents_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_recents
    ADD CONSTRAINT case_recents_pkey PRIMARY KEY (id);


--
-- Name: case_reminders case_reminders_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_reminders
    ADD CONSTRAINT case_reminders_pkey PRIMARY KEY (id);


--
-- Name: case_removed_files case_removed_files_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_removed_files
    ADD CONSTRAINT case_removed_files_pkey PRIMARY KEY (id);


--
-- Name: case_settings case_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_settings
    ADD CONSTRAINT case_settings_pkey PRIMARY KEY (id);


--
-- Name: case_templates case_templates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_templates
    ADD CONSTRAINT case_templates_pkey PRIMARY KEY (id);


--
-- Name: case_user_emails case_user_emails_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_user_emails
    ADD CONSTRAINT case_user_emails_pkey PRIMARY KEY (id);


--
-- Name: case_user_views case_user_views_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.case_user_views
    ADD CONSTRAINT case_user_views_pkey PRIMARY KEY (id);


--
-- Name: check_lists check_lists_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.check_lists
    ADD CONSTRAINT check_lists_pkey PRIMARY KEY (id);


--
-- Name: companies companies_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_pkey PRIMARY KEY (id);


--
-- Name: company_apis company_apis_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.company_apis
    ADD CONSTRAINT company_apis_pkey PRIMARY KEY (id);


--
-- Name: company_types company_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.company_types
    ADD CONSTRAINT company_types_pkey PRIMARY KEY (id);


--
-- Name: company_users company_users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.company_users
    ADD CONSTRAINT company_users_pkey PRIMARY KEY (id);


--
-- Name: countries countries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_pkey PRIMARY KEY (id);


--
-- Name: currencies currencies_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.currencies
    ADD CONSTRAINT currencies_pkey PRIMARY KEY (id);


--
-- Name: custom_filters custom_filters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.custom_filters
    ADD CONSTRAINT custom_filters_pkey PRIMARY KEY (id);


--
-- Name: custom_statuses custom_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.custom_statuses
    ADD CONSTRAINT custom_statuses_pkey PRIMARY KEY (id);


--
-- Name: default_task_views default_task_views_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.default_task_views
    ADD CONSTRAINT default_task_views_pkey PRIMARY KEY (id);


--
-- Name: default_tasks default_tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.default_tasks
    ADD CONSTRAINT default_tasks_pkey PRIMARY KEY (id);


--
-- Name: duedate_change_reasons duedate_change_reasons_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.duedate_change_reasons
    ADD CONSTRAINT duedate_change_reasons_pkey PRIMARY KEY (id);


--
-- Name: easycase_favourites easycase_favourites_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.easycase_favourites
    ADD CONSTRAINT easycase_favourites_pkey PRIMARY KEY (id);


--
-- Name: easycase_labels easycase_labels_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.easycase_labels
    ADD CONSTRAINT easycase_labels_pkey PRIMARY KEY (id);


--
-- Name: easycase_linkings easycase_linkings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.easycase_linkings
    ADD CONSTRAINT easycase_linkings_pkey PRIMARY KEY (id);


--
-- Name: easycase_links easycase_links_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.easycase_links
    ADD CONSTRAINT easycase_links_pkey PRIMARY KEY (id);


--
-- Name: easycase_mentions easycase_mentions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.easycase_mentions
    ADD CONSTRAINT easycase_mentions_pkey PRIMARY KEY (id);


--
-- Name: easycase_milestones easycase_milestones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.easycase_milestones
    ADD CONSTRAINT easycase_milestones_pkey PRIMARY KEY (id);


--
-- Name: easycase_recurring_tracks easycase_recurring_tracks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.easycase_recurring_tracks
    ADD CONSTRAINT easycase_recurring_tracks_pkey PRIMARY KEY (id);


--
-- Name: easycase_relates easycase_relates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.easycase_relates
    ADD CONSTRAINT easycase_relates_pkey PRIMARY KEY (id);


--
-- Name: easycases easycases_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.easycases
    ADD CONSTRAINT easycases_pkey PRIMARY KEY (id);


--
-- Name: email_reminders email_reminders_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.email_reminders
    ADD CONSTRAINT email_reminders_pkey PRIMARY KEY (id);


--
-- Name: email_settings email_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.email_settings
    ADD CONSTRAINT email_settings_pkey PRIMARY KEY (id);


--
-- Name: guest_role_actions guest_role_actions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.guest_role_actions
    ADD CONSTRAINT guest_role_actions_pkey PRIMARY KEY (id);


--
-- Name: helps helps_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.helps
    ADD CONSTRAINT helps_pkey PRIMARY KEY (id);


--
-- Name: industries industries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.industries
    ADD CONSTRAINT industries_pkey PRIMARY KEY (id);


--
-- Name: invoice_customers invoice_customers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoice_customers
    ADD CONSTRAINT invoice_customers_pkey PRIMARY KEY (id);


--
-- Name: labels labels_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.labels
    ADD CONSTRAINT labels_pkey PRIMARY KEY (id);


--
-- Name: languages languages_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.languages
    ADD CONSTRAINT languages_pkey PRIMARY KEY (id);


--
-- Name: log_activities log_activities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.log_activities
    ADD CONSTRAINT log_activities_pkey PRIMARY KEY (id);


--
-- Name: log_times log_times_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.log_times
    ADD CONSTRAINT log_times_pkey PRIMARY KEY (log_id);


--
-- Name: log_types log_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.log_types
    ADD CONSTRAINT log_types_pkey PRIMARY KEY (id);


--
-- Name: login_types login_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.login_types
    ADD CONSTRAINT login_types_pkey PRIMARY KEY (id);


--
-- Name: menu_languages menu_languages_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.menu_languages
    ADD CONSTRAINT menu_languages_pkey PRIMARY KEY (id);


--
-- Name: menus menus_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.menus
    ADD CONSTRAINT menus_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: milestones milestones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.milestones
    ADD CONSTRAINT milestones_pkey PRIMARY KEY (id);


--
-- Name: modules modules_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.modules
    ADD CONSTRAINT modules_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: os_session_logs os_session_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.os_session_logs
    ADD CONSTRAINT os_session_logs_pkey PRIMARY KEY (id);


--
-- Name: project_actions project_actions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_actions
    ADD CONSTRAINT project_actions_pkey PRIMARY KEY (id);


--
-- Name: project_metas project_metas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_metas
    ADD CONSTRAINT project_metas_pkey PRIMARY KEY (id);


--
-- Name: project_methodologies project_methodologies_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_methodologies
    ADD CONSTRAINT project_methodologies_pkey PRIMARY KEY (id);


--
-- Name: project_notes project_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_notes
    ADD CONSTRAINT project_notes_pkey PRIMARY KEY (id);


--
-- Name: project_notifications project_notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_notifications
    ADD CONSTRAINT project_notifications_pkey PRIMARY KEY (id);


--
-- Name: project_settings project_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_settings
    ADD CONSTRAINT project_settings_pkey PRIMARY KEY (id);


--
-- Name: project_statuses project_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_statuses
    ADD CONSTRAINT project_statuses_pkey PRIMARY KEY (id);


--
-- Name: project_technologies project_technologies_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_technologies
    ADD CONSTRAINT project_technologies_pkey PRIMARY KEY (id);


--
-- Name: project_types project_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_types
    ADD CONSTRAINT project_types_pkey PRIMARY KEY (id);


--
-- Name: project_users project_users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_users
    ADD CONSTRAINT project_users_pkey PRIMARY KEY (id);


--
-- Name: projects projects_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projects
    ADD CONSTRAINT projects_pkey PRIMARY KEY (id);


--
-- Name: quicklink_menus quicklink_menus_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quicklink_menus
    ADD CONSTRAINT quicklink_menus_pkey PRIMARY KEY (id);


--
-- Name: quicklink_submenus quicklink_submenus_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quicklink_submenus
    ADD CONSTRAINT quicklink_submenus_pkey PRIMARY KEY (id);


--
-- Name: recurring_easycases recurring_easycases_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recurring_easycases
    ADD CONSTRAINT recurring_easycases_pkey PRIMARY KEY (id);


--
-- Name: role_actions role_actions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_actions
    ADD CONSTRAINT role_actions_pkey PRIMARY KEY (id);


--
-- Name: role_groups role_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_groups
    ADD CONSTRAINT role_groups_pkey PRIMARY KEY (id);


--
-- Name: role_modules role_modules_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_modules
    ADD CONSTRAINT role_modules_pkey PRIMARY KEY (id);


--
-- Name: role_rates role_rates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_rates
    ADD CONSTRAINT role_rates_pkey PRIMARY KEY (id);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: search_filters search_filters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.search_filters
    ADD CONSTRAINT search_filters_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: sidebar_menus sidebar_menus_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sidebar_menus
    ADD CONSTRAINT sidebar_menus_pkey PRIMARY KEY (id);


--
-- Name: sidebar_submenus sidebar_submenus_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sidebar_submenus
    ADD CONSTRAINT sidebar_submenus_pkey PRIMARY KEY (id);


--
-- Name: status_groups status_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_groups
    ADD CONSTRAINT status_groups_pkey PRIMARY KEY (id);


--
-- Name: status_masters status_masters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.status_masters
    ADD CONSTRAINT status_masters_pkey PRIMARY KEY (id);


--
-- Name: subjects subjects_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.subjects
    ADD CONSTRAINT subjects_pkey PRIMARY KEY (id);


--
-- Name: task_cycles task_cycles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.task_cycles
    ADD CONSTRAINT task_cycles_pkey PRIMARY KEY (id);


--
-- Name: task_due_change_reasons task_due_change_reasons_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.task_due_change_reasons
    ADD CONSTRAINT task_due_change_reasons_pkey PRIMARY KEY (id);


--
-- Name: task_settings task_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.task_settings
    ADD CONSTRAINT task_settings_pkey PRIMARY KEY (id);


--
-- Name: task_views task_views_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.task_views
    ADD CONSTRAINT task_views_pkey PRIMARY KEY (id);


--
-- Name: team_users team_users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.team_users
    ADD CONSTRAINT team_users_pkey PRIMARY KEY (id);


--
-- Name: teams teams_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.teams
    ADD CONSTRAINT teams_pkey PRIMARY KEY (id);


--
-- Name: technologies technologies_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.technologies
    ADD CONSTRAINT technologies_pkey PRIMARY KEY (id);


--
-- Name: time_zone time_zone_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.time_zone
    ADD CONSTRAINT time_zone_pkey PRIMARY KEY (time_zone_id);


--
-- Name: timezones timezones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timezones
    ADD CONSTRAINT timezones_pkey PRIMARY KEY (id);


--
-- Name: type_companies type_companies_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.type_companies
    ADD CONSTRAINT type_companies_pkey PRIMARY KEY (id);


--
-- Name: types types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.types
    ADD CONSTRAINT types_pkey PRIMARY KEY (id);


--
-- Name: user_device_tokens user_device_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_device_tokens
    ADD CONSTRAINT user_device_tokens_pkey PRIMARY KEY (id);


--
-- Name: user_infos user_infos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_infos
    ADD CONSTRAINT user_infos_pkey PRIMARY KEY (id);


--
-- Name: user_invitations user_invitations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_invitations
    ADD CONSTRAINT user_invitations_pkey PRIMARY KEY (id);


--
-- Name: user_logins user_logins_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_logins
    ADD CONSTRAINT user_logins_pkey PRIMARY KEY (id);


--
-- Name: user_menus user_menus_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_menus
    ADD CONSTRAINT user_menus_pkey PRIMARY KEY (id);


--
-- Name: user_notifications user_notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_notifications
    ADD CONSTRAINT user_notifications_pkey PRIMARY KEY (id);


--
-- Name: user_quicklinks user_quicklinks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_quicklinks
    ADD CONSTRAINT user_quicklinks_pkey PRIMARY KEY (id);


--
-- Name: user_sidebar_menus user_sidebar_menus_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_sidebar_menus
    ADD CONSTRAINT user_sidebar_menus_pkey PRIMARY KEY (id);


--
-- Name: user_sidebar_submenus user_sidebar_submenus_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_sidebar_submenus
    ADD CONSTRAINT user_sidebar_submenus_pkey PRIMARY KEY (id);


--
-- Name: user_themes user_themes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_themes
    ADD CONSTRAINT user_themes_pkey PRIMARY KEY (id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: workflow_actions workflow_actions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_actions
    ADD CONSTRAINT workflow_actions_pkey PRIMARY KEY (id);


--
-- Name: workflow_conditions workflow_conditions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_conditions
    ADD CONSTRAINT workflow_conditions_pkey PRIMARY KEY (id);


--
-- Name: workflow_details workflow_details_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflow_details
    ADD CONSTRAINT workflow_details_pkey PRIMARY KEY (id);


--
-- Name: workflows workflows_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workflows
    ADD CONSTRAINT workflows_pkey PRIMARY KEY (id);


--
-- Name: actions_uniq_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX actions_uniq_id ON public.actions USING btree (uniq_id);


--
-- Name: case_actions_action; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_actions_action ON public.case_actions USING btree (action);


--
-- Name: case_actions_easycase_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_actions_easycase_id ON public.case_actions USING btree (easycase_id);


--
-- Name: case_actions_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_actions_user_id ON public.case_actions USING btree (user_id);


--
-- Name: case_activities_case_no; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_activities_case_no ON public.case_activities USING btree (case_no);


--
-- Name: case_activities_comment_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_activities_comment_id ON public.case_activities USING btree (comment_id);


--
-- Name: case_activities_easycase_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_activities_easycase_id ON public.case_activities USING btree (easycase_id);


--
-- Name: case_activities_isactive; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_activities_isactive ON public.case_activities USING btree (isactive);


--
-- Name: case_activities_project_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_activities_project_id ON public.case_activities USING btree (project_id);


--
-- Name: case_activities_type; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_activities_type ON public.case_activities USING btree (type);


--
-- Name: case_activities_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_activities_user_id ON public.case_activities USING btree (user_id);


--
-- Name: case_comments_easycase_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_comments_easycase_id ON public.case_comments USING btree (easycase_id);


--
-- Name: case_comments_isactive; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_comments_isactive ON public.case_comments USING btree (isactive);


--
-- Name: case_comments_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_comments_user_id ON public.case_comments USING btree (user_id);


--
-- Name: case_editor_files_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_editor_files_company_id ON public.case_editor_files USING btree (company_id);


--
-- Name: case_editor_files_easycase_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_editor_files_easycase_id ON public.case_editor_files USING btree (easycase_id);


--
-- Name: case_editor_files_is_deleted; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_editor_files_is_deleted ON public.case_editor_files USING btree (is_deleted);


--
-- Name: case_editor_files_project_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_editor_files_project_id ON public.case_editor_files USING btree (project_id);


--
-- Name: case_editor_files_uniq_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_editor_files_uniq_id ON public.case_editor_files USING btree (uniq_id);


--
-- Name: case_editor_files_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_editor_files_user_id ON public.case_editor_files USING btree (user_id);


--
-- Name: case_file_drives_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_file_drives_id ON public.case_file_drives USING btree (id);


--
-- Name: case_files_comment_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_files_comment_id ON public.case_files USING btree (comment_id);


--
-- Name: case_files_easycase_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_files_easycase_id ON public.case_files USING btree (easycase_id);


--
-- Name: case_files_isactive; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_files_isactive ON public.case_files USING btree (isactive);


--
-- Name: case_recents_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_recents_company_id ON public.case_recents USING btree (company_id);


--
-- Name: case_recents_easycase_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_recents_easycase_id ON public.case_recents USING btree (easycase_id);


--
-- Name: case_recents_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_recents_id ON public.case_recents USING btree (id);


--
-- Name: case_recents_project_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_recents_project_id ON public.case_recents USING btree (project_id);


--
-- Name: case_recents_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_recents_user_id ON public.case_recents USING btree (user_id);


--
-- Name: case_reminders_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_reminders_company_id ON public.case_reminders USING btree (company_id);


--
-- Name: case_reminders_easycase_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_reminders_easycase_id ON public.case_reminders USING btree (easycase_id);


--
-- Name: case_reminders_is_emailed; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_reminders_is_emailed ON public.case_reminders USING btree (is_emailed);


--
-- Name: case_reminders_project_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_reminders_project_id ON public.case_reminders USING btree (project_id);


--
-- Name: case_reminders_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_reminders_user_id ON public.case_reminders USING btree (user_id);


--
-- Name: case_templates_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_templates_company_id ON public.case_templates USING btree (company_id);


--
-- Name: case_user_emails_easycase_id_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX case_user_emails_easycase_id_user_id ON public.case_user_emails USING btree (easycase_id, user_id);


--
-- Name: check_lists_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX check_lists_company_id ON public.check_lists USING btree (company_id);


--
-- Name: check_lists_easycase_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX check_lists_easycase_id ON public.check_lists USING btree (easycase_id);


--
-- Name: check_lists_project_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX check_lists_project_id ON public.check_lists USING btree (project_id);


--
-- Name: check_lists_uniq_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX check_lists_uniq_id ON public.check_lists USING btree (uniq_id);


--
-- Name: check_lists_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX check_lists_user_id ON public.check_lists USING btree (user_id);


--
-- Name: companies_is_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX companies_is_active ON public.companies USING btree (is_active);


--
-- Name: company_users_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX company_users_company_id ON public.company_users USING btree (company_id);


--
-- Name: company_users_is_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX company_users_is_active ON public.company_users USING btree (is_active);


--
-- Name: company_users_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX company_users_user_id ON public.company_users USING btree (user_id);


--
-- Name: company_users_user_type; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX company_users_user_type ON public.company_users USING btree (user_type);


--
-- Name: currencies_name; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX currencies_name ON public.currencies USING btree (name);


--
-- Name: easycase_labels_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycase_labels_id ON public.easycase_labels USING btree (id);


--
-- Name: easycase_linkings_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycase_linkings_id ON public.easycase_linkings USING btree (id);


--
-- Name: easycase_milestones_easycase_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycase_milestones_easycase_id ON public.easycase_milestones USING btree (easycase_id);


--
-- Name: easycase_milestones_milestone_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycase_milestones_milestone_id ON public.easycase_milestones USING btree (milestone_id);


--
-- Name: easycase_milestones_project_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycase_milestones_project_id ON public.easycase_milestones USING btree (project_id);


--
-- Name: easycase_milestones_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycase_milestones_user_id ON public.easycase_milestones USING btree (user_id);


--
-- Name: easycases_assign_to; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_assign_to ON public.easycases USING btree (assign_to);


--
-- Name: easycases_case_no; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_case_no ON public.easycases USING btree (case_no);


--
-- Name: easycases_children; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_children ON public.easycases USING btree (children);


--
-- Name: easycases_depends; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_depends ON public.easycases USING btree (depends);


--
-- Name: easycases_format; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_format ON public.easycases USING btree (format);


--
-- Name: easycases_isactive; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_isactive ON public.easycases USING btree (isactive);


--
-- Name: easycases_istype; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_istype ON public.easycases USING btree (istype);


--
-- Name: easycases_legend; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_legend ON public.easycases USING btree (legend);


--
-- Name: easycases_priority; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_priority ON public.easycases USING btree (priority);


--
-- Name: easycases_project_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_project_id ON public.easycases USING btree (project_id);


--
-- Name: easycases_project_id_istype_isactive; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_project_id_istype_isactive ON public.easycases USING btree (project_id, istype, isactive);


--
-- Name: easycases_project_id_istype_legend_depends_children; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_project_id_istype_legend_depends_children ON public.easycases USING btree (project_id, istype, legend, depends, children);


--
-- Name: easycases_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_status ON public.easycases USING btree (status);


--
-- Name: easycases_type_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_type_id ON public.easycases USING btree (type_id);


--
-- Name: easycases_uniq_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_uniq_id ON public.easycases USING btree (uniq_id);


--
-- Name: easycases_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX easycases_user_id ON public.easycases USING btree (user_id);


--
-- Name: idx_companies_tenant_uuid; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX idx_companies_tenant_uuid ON public.companies USING btree (tenant_uuid);


--
-- Name: idx_sessions_expires; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_sessions_expires ON public.sessions USING btree (expires);


--
-- Name: labels_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX labels_id ON public.labels USING btree (id);


--
-- Name: log_times_user_id_project_id_task_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX log_times_user_id_project_id_task_id ON public.log_times USING btree (user_id, project_id, task_id);


--
-- Name: milestones_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX milestones_company_id ON public.milestones USING btree (company_id);


--
-- Name: milestones_project_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX milestones_project_id ON public.milestones USING btree (project_id);


--
-- Name: milestones_uniq_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX milestones_uniq_id ON public.milestones USING btree (uniq_id);


--
-- Name: milestones_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX milestones_user_id ON public.milestones USING btree (user_id);


--
-- Name: modules_uniq_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX modules_uniq_id ON public.modules USING btree (uniq_id);


--
-- Name: os_session_logs_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX os_session_logs_user_id ON public.os_session_logs USING btree (user_id);


--
-- Name: project_notes_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_notes_company_id ON public.project_notes USING btree (company_id);


--
-- Name: project_notes_is_updated; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_notes_is_updated ON public.project_notes USING btree (is_updated);


--
-- Name: project_notes_project_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_notes_project_id ON public.project_notes USING btree (project_id);


--
-- Name: project_notes_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_notes_user_id ON public.project_notes USING btree (user_id);


--
-- Name: project_statuses_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_statuses_company_id ON public.project_statuses USING btree (company_id);


--
-- Name: project_statuses_is_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_statuses_is_active ON public.project_statuses USING btree (is_active);


--
-- Name: project_statuses_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_statuses_user_id ON public.project_statuses USING btree (user_id);


--
-- Name: project_technologies_project_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_technologies_project_id ON public.project_technologies USING btree (project_id);


--
-- Name: project_technologies_technology_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_technologies_technology_id ON public.project_technologies USING btree (technology_id);


--
-- Name: project_types_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_types_company_id ON public.project_types USING btree (company_id);


--
-- Name: project_types_is_active; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_types_is_active ON public.project_types USING btree (is_active);


--
-- Name: project_types_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_types_user_id ON public.project_types USING btree (user_id);


--
-- Name: project_users_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_users_company_id ON public.project_users USING btree (company_id);


--
-- Name: project_users_istype; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_users_istype ON public.project_users USING btree (istype);


--
-- Name: project_users_project_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_users_project_id ON public.project_users USING btree (project_id);


--
-- Name: project_users_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX project_users_user_id ON public.project_users USING btree (user_id);


--
-- Name: projects_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX projects_company_id ON public.projects USING btree (company_id);


--
-- Name: projects_isactive; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX projects_isactive ON public.projects USING btree (isactive);


--
-- Name: projects_project_type; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX projects_project_type ON public.projects USING btree (project_type);


--
-- Name: projects_uniq_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX projects_uniq_id ON public.projects USING btree (uniq_id);


--
-- Name: projects_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX projects_user_id ON public.projects USING btree (user_id);


--
-- Name: quicklink_submenus_ibfk_1; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX quicklink_submenus_ibfk_1 ON public.quicklink_submenus USING btree (quicklink_menu_id);


--
-- Name: quicklink_submenus_ibfk_3; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX quicklink_submenus_ibfk_3 ON public.quicklink_submenus USING btree (menu_language_id);


--
-- Name: role_groups_uniq_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX role_groups_uniq_id ON public.role_groups USING btree (uniq_id);


--
-- Name: roles_uniq_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX roles_uniq_id ON public.roles USING btree (uniq_id);


--
-- Name: sidebar_submenus_sidebar_menu_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sidebar_submenus_sidebar_menu_id ON public.sidebar_submenus USING btree (sidebar_menu_id);


--
-- Name: timezone_names_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX timezone_names_id ON public.timezone_names USING btree (id);


--
-- Name: unique_program_name_per_company; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX unique_program_name_per_company ON public.projects USING btree (company_id, name) WHERE ((purpose_type)::text = 'PROGRAM'::text);


--
-- Name: user_invitations_company_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_invitations_company_id ON public.user_invitations USING btree (company_id);


--
-- Name: user_invitations_invite_token; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_invitations_invite_token ON public.user_invitations USING btree (invite_token);


--
-- Name: user_invitations_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_invitations_user_id ON public.user_invitations USING btree (user_id);


--
-- Name: user_sidebar_menus_sidebar_menu_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_sidebar_menus_sidebar_menu_id ON public.user_sidebar_menus USING btree (sidebar_menu_id);


--
-- Name: user_sidebar_menus_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_sidebar_menus_user_id ON public.user_sidebar_menus USING btree (user_id);


--
-- Name: users_email; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX users_email ON public.users USING btree (email);


--
-- Name: users_isactive; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_isactive ON public.users USING btree (isactive);


--
-- Name: users_isemail; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_isemail ON public.users USING btree (isemail);


--
-- Name: users_istype; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_istype ON public.users USING btree (istype);


--
-- Name: users_timezone_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_timezone_id ON public.users USING btree (timezone_id);


--
-- Name: users_uniq_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX users_uniq_id ON public.users USING btree (uniq_id);


--
-- Name: quicklink_submenus quicklink_submenus_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quicklink_submenus
    ADD CONSTRAINT quicklink_submenus_ibfk_1 FOREIGN KEY (quicklink_menu_id) REFERENCES public.quicklink_menus(id);


--
-- Name: quicklink_submenus quicklink_submenus_ibfk_3; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quicklink_submenus
    ADD CONSTRAINT quicklink_submenus_ibfk_3 FOREIGN KEY (menu_language_id) REFERENCES public.menu_languages(id);


--
-- PostgreSQL database dump complete
--


