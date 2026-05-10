<?php
namespace local_dbviewrecorder\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event for when a database is searched.
 */
class record_searched extends \core\event\base {

    /**
     * イベントデータの初期化
     */
    protected function init() {
        $this->data['crud'] = 'r'; // Read
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        // 検索イベントは特定のエントリではなく、データベース活動に紐づく。
        $this->data['objecttable'] = 'data';
    }

    /**
     * イベント名の取得
     */
    public static function get_name() {
        return get_string('eventrecordsearched', 'local_dbviewrecorder');
    }

    /**
     * ログ一覧での説明文
     */
    public function get_description() {
        $searchquery = $this->other['searchquery'] ?? '';
        $dataid = $this->other['dataid'] ?? $this->objectid;
        return "User with id {$this->userid} searched database {$dataid} for '{$searchquery}'.";
    }

    /**
     * ログクリック時の遷移先URL
     */
    public function get_url() {
        $params = [];
        if (!empty($this->other['cmid'])) {
            $params['id'] = $this->other['cmid'];
        } else if (!empty($this->other['dataid'])) {
            $params['d'] = $this->other['dataid'];
        }
        return new \moodle_url('/mod/data/view.php', $params);
    }

    /**
     * プラグインコンポーネント名
     */
    public static function get_component() {
        return 'local_dbviewrecorder';
    }
}
