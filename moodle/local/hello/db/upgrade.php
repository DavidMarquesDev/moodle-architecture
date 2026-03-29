<?php
declare(strict_types=1);

defined('MOODLE_INTERNAL') || die();

/**
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_hello_upgrade(int $oldversion): bool
{
    global $DB;

    $dbmanager = $DB->get_manager();

    if ($oldversion < 2026032901) {
        $table = new xmldb_table('local_hello_messages');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('message', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('userid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid']);

        if (!$dbmanager->table_exists($table)) {
            $dbmanager->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026032901, 'local', 'hello');
    }

    return true;
}
