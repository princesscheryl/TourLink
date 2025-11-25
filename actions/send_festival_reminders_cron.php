<?php
/**
 * Festival Reminder Cron Job
 * Run this script daily via cron to send festival reminders
 *
 * Setup cron (on server):
 * crontab -e
 * Add line: 0 9 * * * /usr/bin/php /path/to/tourlink/actions/send_festival_reminders_cron.php
 * (Runs daily at 9 AM)
 */

require_once __DIR__ . '/../classes/notification_class.php';

// Prevent direct browser access (optional security)
if (php_sapi_name() !== 'cli' && !isset($_GET['run_cron'])) {
    die("This script should be run via cron or with ?run_cron=1 parameter");
}

$notification = new Notification();

try {
    $results = $notification->send_upcoming_festival_reminders();

    // Log results
    $log_entry = date('Y-m-d H:i:s') . " - Festival Reminders Sent:\n";

    if (empty($results)) {
        $log_entry .= "No upcoming festivals requiring reminders.\n";
    } else {
        foreach ($results as $result) {
            $log_entry .= "  - {$result['festival']}: {$result['notifications_sent']} notifications sent\n";
        }
    }

    $log_entry .= "---\n";

    // Write to log file
    $log_file = __DIR__ . '/../logs/festival_reminders.log';
    $log_dir = dirname($log_file);

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    file_put_contents($log_file, $log_entry, FILE_APPEND);

    // Output for cron log
    echo $log_entry;

    exit(0);
} catch (Exception $e) {
    $error_log = date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n";
    file_put_contents(__DIR__ . '/../logs/festival_reminders.log', $error_log, FILE_APPEND);
    echo $error_log;
    exit(1);
}
?>
