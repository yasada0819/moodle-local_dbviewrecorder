<?php
defined('MOODLE_INTERNAL') || die();

/**
 * HTMLヘッダー出力前の処理
 * mod_data のページでのみロガーJSを読み込む
 */
function local_dbviewrecorder_before_http_headers() {
    global $PAGE;

    // 現在のページが mod_data (データベースモジュール) かどうか判定
    if (strpos($PAGE->pagetype, 'mod-data') === 0) {
        $PAGE->requires->js(new moodle_url('/local/dbviewrecorder/js/logger.js'));
    }
}