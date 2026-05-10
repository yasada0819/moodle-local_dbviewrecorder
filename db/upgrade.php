<?php
defined('MOODLE_INTERNAL') || die();

/**
 * プラグインのアップグレード処理
 *
 * @param int $oldversion 古いバージョン番号
 * @return bool 成功したらtrue
 */
function xmldb_local_dbviewrecorder_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // 2026051001: use Moodle standard logstore only and remove the legacy custom log table.
    if ($oldversion < 2026051001) {
        $table = new xmldb_table('local_dbviewrecorder_log');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        upgrade_plugin_savepoint(true, 2026051001, 'local', 'dbviewrecorder');
    }

    return true;
}
