-- Add indexes used by the expenses dashboard. Safe for databases that already
-- have them (including databases initialized from sql/schema.sql).
SET @schema_name = DATABASE();

SET @migration_sql = IF(
    (SELECT COUNT(*) FROM information_schema.statistics
     WHERE table_schema = @schema_name
       AND table_name = 'expenses'
       AND index_name = 'idx_expenses_calendar_year') = 0,
    'ALTER TABLE expenses ADD INDEX idx_expenses_calendar_year (calendar_year)',
    'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;

SET @migration_sql = IF(
    (SELECT COUNT(*) FROM information_schema.statistics
     WHERE table_schema = @schema_name
       AND table_name = 'expenses'
       AND index_name = 'idx_expenses_date') = 0,
    'ALTER TABLE expenses ADD INDEX idx_expenses_date (date)',
    'SELECT 1'
);
PREPARE migration_statement FROM @migration_sql;
EXECUTE migration_statement;
DEALLOCATE PREPARE migration_statement;
