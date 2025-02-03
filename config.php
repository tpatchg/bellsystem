<?php
// Configuration
define('CALENDAR_ID', 'INSERTIDHERE@group.calendar.google.com');
define('SERVICE_ACCOUNT_KEY', '/var/www/html/google_service_key.json');

define('WARNING_BELL', 'warningbell.mp3');
define('START_BELL', 'startbell.mp3');
define('END_BELL', 'endbell.mp3');

define('PLAY_SCRIPT', '/var/www/html/play.sh');
define('LOG_FILE', '/var/log/bells/sync.log');

date_default_timezone_set('America/Chicago');
