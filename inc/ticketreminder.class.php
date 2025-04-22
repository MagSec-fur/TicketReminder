<?php
class PluginTicketreminderTicketreminder {
    static function cronReminder() {
        global $DB;

        $config = PluginTicketreminderConfig::getConfig(0);
        $days = (int)$config['days'];
        $hours = (int)$config['hours'];
        $interval = ($days * 24) + $hours;

        $sql = "SELECT t.id, t.name, t.users_id_assign, t.date, u.email AS assign_email
                FROM glpi_tickets t
                LEFT JOIN glpi_users u ON t.users_id_assign = u.id
                WHERE t.status = 1
                  AND t.is_deleted = 0
                  AND t.users_id_assign IS NOT NULL
                  AND t.date <= DATE_SUB(NOW(), INTERVAL $interval HOUR)";

        foreach ($DB->request($sql) as $ticket) {
            if (filter_var($ticket['assign_email'], FILTER_VALIDATE_EMAIL)) {
                $subject = "Ticket #{$ticket['id']} is still open";
                $body = "Hello,\n\nThe ticket '{$ticket['name']}' (ID: {$ticket['id']}) is still open after $days days and $hours hours.\nPlease review it in GLPI.";
                Toolbox::sendMail($ticket['assign_email'], $subject, ['content_text' => $body]);
            }
        }
        return true;
    }
}
?>
