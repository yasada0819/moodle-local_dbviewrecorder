<?php
// Moodleの基本設定読み込み
require_once('../../config.php');

// ログインチェック
require_login();

// セッションキーチェック (CSRF対策)
require_sesskey(); 

// JSON入力の取得
$rawinput = file_get_contents('php://input');
$data = json_decode($rawinput, true);

// データの取得とサニタイズ（安全化）
$userid = $USER->id;
$action = isset($data['action']) ? clean_param($data['action'], PARAM_ALPHA) : ''; // アルファベットのみ許可
$recordid = isset($data['recordid']) ? clean_param($data['recordid'], PARAM_INT) : 0;
$searchquery = isset($data['searchquery']) ? clean_param($data['searchquery'], PARAM_TEXT) : '';
$courseid = isset($data['courseid']) ? clean_param($data['courseid'], PARAM_INT) : 0;
$cmid        = isset($data['cmid']) ? clean_param($data['cmid'], PARAM_INT) : 0;

if ($courseid > 0) {
    $course = get_course($courseid);
    require_course_login($courseid);
}

// コンテキストの取得（エラー回避）
if ($courseid > 0) {
    try {
        $course = get_course($courseid);
    } catch (\Throwable $e) {
        json_error(400, 'Invalid courseid');
    }

    // require_course_loginは $course を渡す
    require_course_login($course);

    // context_course::instance 例外確認
    try {
        $context = \context_course::instance($courseid);
    } catch (\Throwable $e) {
        json_error(400, 'Invalid course context');
    }
} else {
    // コースIDがない場合はシステムコンテキスト
    $context = \context_system::instance();
}


$time = time();

// --- 1. イベントログの発火 (Moodle標準ログへ保存) ---

if ($action === 'searched') {
    // 検索イベント
    $event = \local_dbviewrecorder\event\record_searched::create([
        'context' => $context,
        'userid' => $userid,
        'objectid' => $recordid, // 検索では0の場合もあるが、int必須なら0を入れる
        'other' => [
            'searchquery' => $searchquery
        ]
    ]);
} else {
    // 閲覧イベント (デフォルト)
    // objectid (recordid) が必須なので、0の場合は発火しないほうが安全かもしれない
    if ($recordid > 0) {
        $event = \local_dbviewrecorder\event\record_viewed::create([
            'context' => $context,
            'userid' => $userid,
            'objectid' => $recordid,
            'other' => []
        ]);
    } else {
        $event = null; // レコードIDがない閲覧ログはおかしいのでスキップ
    }
}

// イベントオブジェクトが正常に作成できていれば発火
if ($event) {
    $event->trigger();
}

// --- 2. カスタムテーブルへの保存 (自作テーブルへ保存) ---

// DB操作用のグローバル変数
global $DB;

$log_entry = new stdClass();
$log_entry->userid = $userid;
$log_entry->action = $action;
$log_entry->recordid = $recordid;
$log_entry->courseid = $courseid; 
$log_entry->searchquery = $searchquery;
$log_entry->timecreated = $time;


$DB->insert_record('local_dbviewrecorder_log', $log_entry);

// 成功レスポンス
header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);