<?php
// Moodleの基本設定読み込み
require_once("../../config.php");

// ログインチェック
require_login();

// セッションキーチェック (CSRF対策)
require_sesskey();

/**
 * JSONエラーを返して処理を終了する。
 *
 * @param int $status HTTPステータスコード
 * @param string $message エラーメッセージ
 */
function local_dbviewrecorder_json_error($status, $message) {
    http_response_code($status);
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => $message]);
    exit;
}

// JSON入力の取得
$rawinput = file_get_contents("php://input");
$data = json_decode($rawinput, true);

if (!is_array($data)) {
    local_dbviewrecorder_json_error(400, "Invalid JSON");
}

// データの取得とサニタイズ（安全化）
global $DB, $USER;

$userid = $USER->id;
$action = isset($data["action"]) ? clean_param($data["action"], PARAM_ALPHA) : ""; // アルファベットのみ許可
$recordid = isset($data["recordid"]) ? clean_param($data["recordid"], PARAM_INT) : 0;
$searchquery = isset($data["searchquery"]) ? clean_param($data["searchquery"], PARAM_TEXT) : "";
$courseid = isset($data["courseid"]) ? clean_param($data["courseid"], PARAM_INT) : 0;
$cmid = isset($data["cmid"]) ? clean_param($data["cmid"], PARAM_INT) : 0;
$dataid = isset($data["dataid"]) ? clean_param($data["dataid"], PARAM_INT) : 0;

if (!in_array($action, ["viewed", "searched"], true)) {
    local_dbviewrecorder_json_error(400, "Invalid action");
}

// 個別エントリ閲覧では rid から database instance id を補完できる。
if ($recordid > 0 && $dataid <= 0) {
    $record = $DB->get_record("data_records", ["id" => $recordid], "id,dataid", IGNORE_MISSING);
    if ($record) {
        $dataid = (int)$record->dataid;
    }
}

$course = null;
$cm = null;
$context = null;

// URLの id は course module id。これがある場合は最優先で活動コンテキストを使う。
if ($cmid > 0) {
    try {
        $cm = get_coursemodule_from_id("data", $cmid, 0, false, MUST_EXIST);
        $course = get_course($cm->course);
        $courseid = (int)$course->id;
        $dataid = (int)$cm->instance;
        require_login($course, false, $cm);
        $context = \context_module::instance($cm->id);
    } catch (\Throwable $e) {
        local_dbviewrecorder_json_error(400, "Invalid cmid");
    }
}

// URLの d は database instance id。検索時はこちらだけのことがある。
if (!$context && $dataid > 0) {
    try {
        $datarecord = $DB->get_record("data", ["id" => $dataid], "id,course", MUST_EXIST);
        $course = get_course($datarecord->course);
        $courseid = (int)$course->id;
        $cm = get_coursemodule_from_instance("data", $dataid, $courseid, false, MUST_EXIST);
        $cmid = (int)$cm->id;
        require_login($course, false, $cm);
        $context = \context_module::instance($cm->id);
    } catch (\Throwable $e) {
        local_dbviewrecorder_json_error(400, "Invalid dataid");
    }
}

// 後方互換: どうしても活動を特定できない場合のみコースコンテキストに落とす。
if (!$context && $courseid > 0) {
    try {
        $course = get_course($courseid);
        require_login($course);
        $context = \context_course::instance($courseid);
    } catch (\Throwable $e) {
        local_dbviewrecorder_json_error(400, "Invalid courseid");
    }
}

if (!$context) {
    $context = \context_system::instance();
}

// --- イベントログの発火 (Moodle標準ログへ保存) ---

$event = null;
$eventdata = [
    "context" => $context,
    "userid" => $userid,
    "objectid" => $recordid,
    "other" => [
        "cmid" => $cmid,
        "dataid" => $dataid,
        "courseid" => $courseid,
    ],
];

if ($action === "searched") {
    $eventdata["objectid"] = $dataid;
    $eventdata["other"]["searchquery"] = $searchquery;
    $event = \local_dbviewrecorder\event\record_searched::create($eventdata);
} else if ($recordid > 0) {
    $event = \local_dbviewrecorder\event\record_viewed::create($eventdata);
}

// イベントオブジェクトが正常に作成できていれば発火
if ($event) {
    $event->trigger();
}

// 成功レスポンス
header("Content-Type: application/json");
echo json_encode(["status" => "ok"]);
