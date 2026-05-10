<?php
namespace local_dbviewrecorder\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider for local_dbviewrecorder.
 */
class provider implements \core_privacy\local\metadata\null_provider {

    /**
     * Returns the reason why this plugin stores no personal data.
     *
     * @return string
     */
    public static function get_reason() {
        return 'privacy:metadata';
    }
}
