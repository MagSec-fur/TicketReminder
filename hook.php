<?php
define('PLUGIN_TICKETREMINDER_VERSION', '1.0.1');

if (!defined("GLPI_ROOT")) {
    die("Sorry. You can't access directly to this file");
}

class PluginTicketreminderCron extends CommonDBTM {
    // Define proper rights for this class
    static $rightname = 'plugin_ticketreminder';
    
    static function cronTicketreminder($task) {
        // Check if current user has right to execute this cron
        if (!self::canCreate()) {
            $task->log("User doesn't have rights to execute this cron task");
            return false;
        }
    
    static function cronTicketreminder($task) {
        global $DB, $CFG_GLPI;
        
        $message = [];
        $message[] = "Starting Ticket Reminder Plugin";
        
        // Get threshold from config or use default (3 days)
        $threshold = 3;
        
        // Get all open tickets older than threshold days
        $query = "SELECT t.id, t.name, t.date, t.status, t.users_id_recipient, 
                         t.users_id_lastupdater, t.users_id_assign, t.date_mod, 
                         tu.users_id, u.email
                  FROM glpi_tickets t
                  LEFT JOIN glpi_tickets_users tu ON t.id = tu.tickets_id AND tu.type = 2
                  LEFT JOIN glpi_users u ON tu.users_id = u.id
                  WHERE t.is_deleted = 0
                  AND t.status NOT IN (" . Ticket::SOLVED . "," . Ticket::CLOSED . ")
                  AND DATEDIFF(NOW(), t.date) > $threshold";
        
        $result = $DB->query($query);
        
        $count = 0;
        $notification = new Notification();
        
        while ($data = $DB->fetchAssoc($result)) {
            $ticket = new Ticket();
            $ticket->getFromDB($data['id']);
            
            $user = new User();
            if ($data['users_id_assign'] && $user->getFromDB($data['users_id_assign'])) {
                $email = $user->getDefaultEmail();
                if (!empty($email)) {
                    $count++;
                    
                    // Use GLPI's notification system for better compliance
                    $options = [
                        'ticket' => $ticket,
                        'user'   => $user,
                        'threshold' => $threshold
                    ];
                    
                    $notification->add([
                        'itemtype' => 'Ticket',
                        'items_id' => $data['id'],
                        'event'    => 'ticketreminder',
                        'targets'  => [
                            [
                                'type' => 'user',
                                'items_id' => $data['users_id_assign']
                            ]
                        ],
                        'options'  => $options
                    ]);
                    
                    $message[] = "Queued reminder for ticket #" . $data['id'] . " to " . $email;
                }
            }
        }
        
        $message[] = "Processed " . $count . " tickets";
        $task->log(implode("\n", $message));
        
        return ($count > 0);
    }
    
    static function getTypeName($nb = 0) {
        return __('Ticket Reminder', 'ticketreminder');
    }
}


    // Add this method to define who can view/execute this plugin
    static function canCreate() {
        return Session::haveRight('config', UPDATE);
    }
}