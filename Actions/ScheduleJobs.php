<?php
namespace Actions\ScheduleJobs;

require __DIR__ . '/vendor/autoload.php';



use App\Models\Application;
use Google\Client;
use Google\Service\Calendar;

// Configuration
define('CALENDAR_ID', '#CALENDARID#@group.calendar.google.com');
define('SERVICE_ACCOUNT_KEY', '/var/www/html/google_service_key.json');
define('PLAY_SCRIPT', '/var/www/html/play.sh');
define('WARNING_BELL', '/var/www/html/audio/warningbell.mp3');
define('START_BELL', '/var/www/html/audio/startbell.mp3');
define('END_BELL', '/var/www/html/audio/endbell.mp3');
define('LOG_FILE', '/var/log/bell_scheduler.log');

date_default_timezone_set('America/Chicago');


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

// Log messages
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(LOG_FILE, "[$timestamp] $message\n", FILE_APPEND);
}

// Schedule audio playback using "at"
function scheduleAt($time, $audioFile) {
    $atCommand = sprintf('echo "%s %s" | at %s', PLAY_SCRIPT, escapeshellarg($audioFile), escapeshellarg($time));
    exec($atCommand, $output, $returnCode);

    $returnMessage = implode("\n", $output);
    if ($returnCode === 0) {
        logMessage("Scheduled $audioFile at $time.");
    } else {
        logMessage("Failed: $returnMessage");
    }
}

// Remove all existing at jobs
function clearAtJobs() {
    // Check if there are any existing 'at' jobs
    exec('atq', $output, $returnCode);
    if (!empty($output)) {
        exec('atrm $(atq | awk \'{print $1}\')', $removeOutput, $removeReturnCode);
        if ($removeReturnCode === 0) {
            logMessage("All existing 'at' jobs cleared successfully.");
        } else {
            logMessage("Failed to clear 'at' jobs.");
        }
    } else {
        logMessage("No existing 'at' jobs to clear.");
    }
}

// Main logic
clearAtJobs();
$events = getTodayEvents();

foreach ($events as $event) {
    $eventName = strtolower($event->getSummary());
    $startTime = strtotime($event->start->dateTime ?: $event->start->date);
    $endTime = strtotime($event->end->dateTime ?: $event->end->date);
    $warningTime = $startTime - 180; // 3 minutes before start

    // Convert times to "HH:MM YYYY-MM-DD" format for "at" command
    $formattedWarningTime = date('H:i Y-m-d', $warningTime);
    $formattedStartTime = date('H:i Y-m-d', $startTime);
    $formattedEndTime = date('H:i Y-m-d', $endTime);

    // Schedule "at" jobs
    scheduleAt($formattedWarningTime, WARNING_BELL);
    scheduleAt($formattedStartTime, START_BELL);
    scheduleAt($formattedEndTime, END_BELL);
}
logMessage("All events for today have been scheduled.\n");
?>
