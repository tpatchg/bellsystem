<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Calendar;

// Configuration
define('CALENDAR_ID', 'ID@group.calendar.google.com');
define('SERVICE_ACCOUNT_KEY', '/var/www/html/google_service_key.json');

define('WARNING_BELL', 'warningbell.mp3');
define('START_BELL', 'startbell.mp3');
define('END_BELL', 'endbell.mp3');

define('PLAY_SCRIPT', '/var/www/html/play.sh');
define('LOG_FILE', '/var/log/bells/sync.log');

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
    file_put_contents(LOG_FILE, "[$timestamp] $message", FILE_APPEND);
}

// Schedule audio playback using "at"
function scheduleAt($time, $audioFile) {
    $command = "echo '".PLAY_SCRIPT." $audioFile' | at $time 2>&1 &";
    $output = shell_exec($command);
    // Process output
    if (strpos($output, 'warning: commands will be executed using /bin/sh') === 0) {
        $output = "Scheduled ".substr($output, 49, null);
    } elseif (strpos($output, 'at: refusing to create job destined in the past') === 0) {
        $output = str_replace('at: refusing to create job destined in the past', 'Skipping time set in past', $output);
    }
    logMessage("At $time playing $audioFile --- $output");
}

// Remove all existing at jobs
function clearAtJobs() {
    // Check if there are any existing 'at' jobs
    exec('atq', $output, $returnCode);
    if (!empty($output)) {
        exec('atrm $(atq | awk \'{print $1}\')', $removeOutput, $removeReturnCode);
        if ($removeReturnCode === 0) {
            logMessage("Previous schedule cleared successfully.\n");
        } else {
            logMessage("Failed to clear 'at' jobs.\n");
        }
    } else {
        logMessage("No pre-existing schedule to clear.\n");
    }
}

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $logMessage = "[ERROR] [$errno] $errstr in $errfile on line $errline";
    file_put_contents(LOG_FILE, "[" . date('Y-m-d H:i:s') . "] $logMessage\n", FILE_APPEND);
}

// Capture fatal errors
function fatalErrorHandler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $logMessage = "[FATAL ERROR] {$error['message']} in {$error['file']} on line {$error['line']}";
        file_put_contents(LOG_FILE, "[" . date('Y-m-d H:i:s') . "] $logMessage\n", FILE_APPEND);
        die();
    }
}

// Set handlers
set_error_handler("customErrorHandler");
register_shutdown_function("fatalErrorHandler");

// Main logic
logMessage("###################### \n");
logMessage("Beginning Sync Process \n");
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
