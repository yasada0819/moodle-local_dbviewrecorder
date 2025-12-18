<?php
namespace local_dbviewrecorder\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Event for when a database record is viewed.
 */
class record_viewed extends \core\event\base {

    /**
     * イベントデータの初期化
     */
    protected function init() {
        $this->data['crud'] = 'r'; // Read (読み取り)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'data_records'; 
    }

    /**
     * イベント名の取得
     */
    public static function get_name() {
        return get_string('eventrecordviewed', 'local_dbviewrecorder');
    }

    /**
     * ログ一覧での説明文
     */
    public function get_description() {
        $recordid = $this->objectid;
        return "User with id {$this->userid} viewed record {$recordid}.";
    }

    /**
     * ログクリック時の遷移先URL
     */
    public function get_url() {
        return new \moodle_url('/mod/data/view.php', ['rid' => $this->objectid]);
    }

    /**
     * プラグインコンポーネント名
     */
    public static function get_component() {
        return 'local_dbviewrecorder';
    }
}