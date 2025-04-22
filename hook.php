<?php
define('PLUGIN_TICKETREMINDER_VERSION', '1.0.0');

if (!defined("GLPI_ROOT")) {
    die("Sorry. You can't access directly to this file");
}

class PluginTicketreminderCron extends CommonDBTM {
    static $rightname = 'config';
    
    static function cronTicketreminder($task) {
        global $DB, $CFG_GLPI;
        
        $message = [];
        $message[] = "Starting Ticket Reminder Plugin";
        
        // Get all open tickets older than 3 days
        $query = "SELECT t.id, t.name, t.date, t.status, t.users_id_recipient, t.users_id_lastupdater, 
                         t.users_id_assign, t.date_mod, tu.users_id
                  FROM glpi_tickets t
                  LEFT JOIN glpi_tickets_users tu ON t.id = tu.tickets_id AND tu.type = 2
                  WHERE t.status NOT IN (" . Ticket::SOLVED . "," . Ticket::CLOSED . ")
                  AND DATEDIFF(NOW(), t.date) > 3";
        
        $result = $DB->query($query);
        
        $count = 0;
        
        while ($data = $DB->fetchAssoc($result)) {
            $ticket = new Ticket();
            $ticket->getFromDB($data['id']);
            
            $assigned_user = new User();
            if ($data['users_id_assign'] && $assigned_user->getFromDB($data['users_id_assign'])) {
                $email = $assigned_user->getDefaultEmail();
                if (!empty($email)) {
                    $count++;
                    
                    // Prepare email content
                    $subject = "[Reminder] Ticket #" . $data['id'] . " is still open";
                    $body = "Dear " . $assigned_user->getName() . ",\n\n";
                    $body .= "This is a reminder that the following ticket is still open:\n\n";
                    $body .= "Ticket ID: #" . $data['id'] . "\n";
                    $body .= "Title: " . $data['name'] . "\n";
                    $body .= "Created: " . $data['date'] . "\n";
                    $body .= "Last updated: " . $data['date_mod'] . "\n\n";
                    $body .= "You can view the ticket here: " . $CFG_GLPI['url_base'] . "/front/ticket.form.php?id=" . $data['id'] . "\n\n";
                    $body .= "Please take appropriate action.\n\n";
                    $body .= "This is an automated message.";
                    
                    // Send email
                    $mail = new NotificationMail();
                    $mail->addTo($email);
                    $mail->setSubject($subject);
                    $mail->setBody($body);
                    
                    if (!$mail->send()) {
                        $message[] = "Failed to send reminder for ticket #" . $data['id'];
                    } else {
                        $message[] = "Sent reminder for ticket #" . $data['id'] . " to " . $email;
                    }
                }
            }
        }
        
        $message[] = "Processed " . $count . " tickets";
        $task->log(implode("\n", $message));
        
        return true;
    }
    
    static function getTypeName($nb = 0) {
        return __('Ticket Reminder', 'ticketreminder');
    }
}