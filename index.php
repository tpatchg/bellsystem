<!DOCTYPE html>
<html lang="en">
<?php
require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Calendar;

// Configuration
define('SERVICE_ACCOUNT_KEY', 'google_service_key.json');
define('CALENDAR_ID', 'c_4somethinglong31@group.calendar.google.com');

// Initialize Google Client
function getGoogleClient() {
    $client = new Client();
    $client->setAuthConfig(SERVICE_ACCOUNT_KEY);
    $client->addScope(Calendar::CALENDAR_READONLY);
    return $client;
}

// Fetch today's events
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

// Get events
$events = getTodayEvents();
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clock and Calendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script language="javascript" type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
	<script language="javascript" type="text/javascript" src="clock/jquery.thooClock.js"></script>
    <style>
        @font-face {
        font-family: "SFBold";
        src: url('clock/sfns_bold.ttf') format('truetype');
        }
</style>
</head>
<body class="bg-gray-100 flex items-center min-h-screen px-4">
    <div class="basis-3/5 flex gap-8 flex-col place-items-center">
        <div id="analogClock"></div>
        <div id="digitalClock" class="text-9xl font-bold text-gray-800" style="font-family: SFBold;">Loading...</div>
    </div>
    <div class="w-full flex justify-center basis-2/5">
    <div class="w-full max-w-3xl">
        <?php if (!empty($events)) : ?>
            <?php foreach ($events as $event) :
                $eventStart = strtotime($event->start->dateTime ?: $event->start->date);
                $eventEnd = strtotime($event->end->dateTime ?: $event->end->date);
                $eventSummary = htmlspecialchars($event->getSummary());
            ?>
                <div class="bg-white shadow-md rounded-lg p-4 mb-4 border-l-4 border-blue-500">
                    <h2 class="text-lg font-bold text-gray-700"><?= $eventSummary ?></h2>
                    <p class="text-gray-600">
                        <?= date('h:i A', $eventStart) ?> - <?= date('h:i A', $eventEnd) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="text-gray-500 text-center bg-white shadow-md rounded-lg p-4">
                No events scheduled for today.
            </div>
        <?php endif; ?>
    </div>
    </div>

    <script>
		let intVal, analogClock;

		$(document).ready(function () {

			$('#analogClock').thooClock({
				size: $(document).height() / 2,
				sweepingMinutes: true,
				sweepingSeconds: true,
				showNumerals: true,
				brandText: 'A. LANGE & SÖHNE',
				brandText2: 'NSA',
				onEverySecond: function () {
				}
			});

            updateClock();
            setInterval(updateClock, 1000); // Update clock every second
		});

        function updateClock() {
            const clockDiv = document.getElementById("digitalClock");
            const now = new Date();
            clockDiv.textContent = now.toLocaleTimeString("en-US");
        }
    </script>

</body>
</html>
