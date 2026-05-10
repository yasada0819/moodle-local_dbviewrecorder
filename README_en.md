# Database View Recorder (local_dbviewrecorder)

[日本語README](README.md)

## Overview

In Moodle's Database activity, viewing an individual entry and running a search can be difficult to distinguish in standard logs. This plugin records those actions as separate Moodle events: one for viewing a record and one for searching a database activity.

## Features

- Records individual entry views as `Record viewed` events
- Records searches as `Record searched` events
- Uses the course module context of the relevant Database activity
- Stores `cmid`, `dataid`, and `courseid` in event `other` data
- Stores search text in `other.searchquery` for search events
- Uses Moodle's standard log stores without maintaining a custom log table

## Installation

1. Place this repository in Moodle's `local/dbviewrecorder` directory.
2. Run the plugin upgrade from Moodle site administration.
3. Purge caches if needed.

## How It Works

The plugin loads JavaScript on `mod_data` pages and reads URL parameters such as `rid`, `id`, `d`, `search`, and `f_*`. When it detects a supported action, it posts the details to `local/dbviewrecorder/logrecord.php`. The server resolves the relevant Database activity context and triggers a Moodle event.

## Privacy

This plugin does not store personal data in its own database tables. It emits Moodle events, which may be stored by the site's enabled Moodle log stores. Log retention and deletion are controlled by the relevant log store settings.

## Requirements

- Moodle 4.4 or later

## Notes

This plugin depends on Moodle's standard logging system. A suitable log store must be enabled on the site for events to be retained.
