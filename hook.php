<?php
define('PLUGIN_TICKETREMINDER_VERSION', '1.1.0');

if (!defined("GLPI_ROOT")) {
    die("Sorry. You can't access directly to this file");
}

class PluginTicketreminderConfig extends CommonDBTM {
    static $rightname = 'config';
    
    static function getTypeName($nb = 0) {
        return __('Ticket Reminder Config', 'ticketreminder');
    }
    
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if ($item->getType() == 'Config') {
            return self::getTypeName();
        }
        return '';
    }
    
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'Config') {
            $config = new self();
            $config->showForm();
        }
        return true;
    }
    
    function showForm() {
        global $CFG_GLPI;
        
        $this->getFromDB(1);
        
        echo "<form action='" . $this->getFormURL() . "' method='post'>";
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='2'>" . __('Ticket Reminder Settings', 'ticketreminder') . "</th></tr>";
        
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Threshold', 'ticketreminder') . "</td>";
        echo "<td>";
        echo Html::input('threshold', [
            'value' => $this->fields['threshold'] ?? 3,
            'type' => 'number',
            'min' => 1
        ]);
        echo "</td></tr>";
        
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Time Unit', 'ticketreminder') . "</td>";
        echo "<td>";
        Dropdown::showFromArray('time_unit', [
            'days' => __('Days'),
            'hours' => __('Hours')
        ], [
            'value' => $this->fields['time_unit'] ?? 'days'
        ]);
        echo "</td></tr>";
        
        echo "<tr class='tab_bg_2'>";
        echo "<td colspan='2' class='center'>";
        echo Html::hidden('id', ['value' => 1]);
        echo Html::submit(_x('button', 'Save'), ['name' => 'update']);
        echo "</td></tr>";
        
        echo "</table>";
        echo "</div>";
        Html::closeForm();
    }
}

class PluginTicketreminderCron extends CommonDBTM {
    static $rightname = 'config';
    
    static function cronTicketreminder($task) {
        global $DB, $CFG_GLPI;
        
        // Load configuration
        $config = new PluginTicketreminderConfig();
        $config->getFromDB(1);
        $threshold = $config->fields['threshold'];
        $time_unit = $config->fields['time_unit'];
        
        // Build time comparison based on unit
        $time_compare = $time_unit == 'hours' 
            ? "TIMESTAMPDIFF(HOUR, t.date, NOW()) > $threshold"
            : "DATEDIFF(NOW(), t.date) > $threshold";
        
        $query = "SELECT t.id, t.name, t.date, t.status, t.users_id_assign, 
                         t.date_mod, u.email, u.firstname, u.realname
                  FROM glpi_tickets t
                  LEFT JOIN glpi_tickets_users tu ON t.id = tu.tickets_id AND tu.type = 2
                  LEFT JOIN glpi_users u ON tu.users_id = u.id
                  WHERE t.is_deleted = 0
                  AND t.status NOT IN (" . Ticket::SOLVED . "," . Ticket::CLOSED . ")
                  AND $time_compare";
        
        $iterator = $DB->request($query);
        $count = 0;
        
        foreach ($iterator as $data) {
            if (!empty($data['users_id_assign'])) {
                $ticket = new Ticket();
                $ticket->getFromDB($data['id']);
                
                $options = [
                    'ticket' => $ticket,
                    'threshold' => $threshold,
                    'time_unit' => $time_unit,
                    'user_name' => formatUserName(
                        $data['id'],
                        $data['firstname'],
                        $data['realname'],
                        ''
                    )
                ];
                
                NotificationEvent::raiseEvent('ticketreminder', $ticket, $options);
                $count++;
            }
        }
        
        // Update last execution time
        $config->update([
            'id' => 1,
            'last_execution' => date('Y-m-d H:i:s')
        ]);
        
        $task->log("Processed $count tickets");
        return ($count > 0);
    }
    
    static function getTypeName($nb = 0) {
        return __('Ticket Reminder', 'ticketreminder');
    }
    
    static function canCreate() {
        return Session::haveRight('config', UPDATE);
    }
}