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

    // 2025121601: courseid カラムを追加する処理
    if ($oldversion < 2025121601) {

        // テーブルの定義
        $table = new xmldb_table('local_dbviewrecorder_log');

        // 追加するフィールドの定義
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'recordid');

        // フィールドがまだなければ追加（ここは field_exists があるのでOK）
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // キーの追加
        // ★修正箇所: key_exists というメソッドはないので、if文を削除しました
        $key = new xmldb_key('courseid', XMLDB_KEY_FOREIGN, ['courseid'], 'course', ['id']);
        
        // そのまま追加します（バージョンチェックで守られているので重複の心配はありません）
        $dbman->add_key($table, $key);

        // アップグレードのセーブポイント
        upgrade_plugin_savepoint(true, 2025121601, 'local', 'dbviewrecorder');
    }

    return true;
}