<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Calendar;

// Configuration
define('SERVICE_ACCOUNT_KEY', '/var/www/html/google_service_key.json');
define('CALENDAR_ID', 'c_9somelongstring4@group.calendar.google.com');
define('WARNING_BELL', '/var/www/html/audio/warningbell.mp3');
define('START_BELL', '/var/www/html/audio/startbell.mp3');
define('END_BELL', '/var/www/html/audio/endbell.mp3');

// Initialize Google Client
function getGoogleClient() {
    $client = new Client();
    $client->setAuthConfig(SERVICE_ACCOUNT_KEY);
    $client->addScope(Calendar::CALENDAR_READONLY);
    return $client;
}

// Fetch events for the day
function getTodayEvents() {
    $client = getGoogleClient();
    $service = new Calendar($client);

    $startOfDay = (new DateTime('today'))->format(DateTime::RFC3339);
    $endOfDay = (new DateTime('tomorrow'))->format(DateTime::RFC3339);

    $events = $service->events->listEvents(CALENDAR_ID, [
        'timeMin' => $startOfDay,
        'timeMax' => $endOfDay,
        'singleEvents' => true,
        'orderBy' => 'startTime',
    ]);

    return $events->getItems();
}

// Schedule audio playback
function scheduleAudio($timestamp, $audioFile) {
    $delay = $timestamp - time();
    if ($delay > 0) {
        $playTime = date('h:i A', $timestamp);
        echo "Audio scheduled: $audioFile will play at $playTime";
        exec("sleep $delay && play $audioFile > /dev/null 2>&1 &");
    } else {
        echo "Skipped scheduling for $audioFile (time has already passed).\n";
    }
}

// Main logic
$events = getTodayEvents();

foreach ($events as $event) {
    $eventName = strtolower($event->getSummary());
    $startTime = strtotime($event->start->dateTime ?: $event->start->date);
    $endTime = strtotime($event->end->dateTime ?: $event->end->date);
    $warningTime = $startTime - 180;

    scheduleAudio($warningTime, WARNING_BELL);
    scheduleAudio($startTime, START_BELL);
    scheduleAudio($endTime, END_BELL);

}

echo "All events for today have been scheduled.\n";
?>
