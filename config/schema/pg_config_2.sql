DO $$
DECLARE
    CONTR_REC RECORD;
    V_CNTR INTEGER;
    qry_str TEXT;
BEGIN
    FOR CONTR_REC IN 
        SELECT 
            t.oid::regclass table_name, a.attname column_name, s.relname sequence_name
        FROM 
            pg_class t
            JOIN pg_attribute a ON a.attrelid = t.oid
            JOIN pg_depend d ON d.refobjid = t.oid AND d.refobjsubid = a.attnum
            JOIN pg_class s ON s.oid = d.objid
        WHERE 
            d.classid = 'pg_catalog.pg_class'::regclass AND d.refclassid = 'pg_catalog.pg_class'::regclass AND 
            d.deptype IN ('i', 'a') AND t.relkind IN ('r', 'P') AND s.relkind = 'S' 
            AND t.relnamespace = (SELECT oid FROM pg_namespace WHERE nspname = 'public')
    LOOP
        qry_str := 'SELECT COALESCE(MAX(' || CONTR_REC.column_name || '), 0) + 1 FROM ' || CONTR_REC.table_name;
        EXECUTE qry_str INTO V_CNTR;

        qry_str := 'SELECT setval(' || quote_literal(CONTR_REC.sequence_name) || ', ' || V_CNTR || ', false)';
        EXECUTE qry_str;

    END LOOP;
END
$$;