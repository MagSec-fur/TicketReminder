<?php
class PluginTicketreminderConfig extends CommonDBTM {
    static $rightname = 'config';
    static protected $notable = false; // Important for CommonDBTM
    
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
            $config->showFormDisplay();
        }
        return true;
    }
    
function showFormDisplay() {
    // Load existing config or initialize defaults
    if (!$this->getFromDB(1)) {
        $this->fields = [
            'id' => 1,
            'threshold' => 1,
            'time_unit' => 'days'
        ];
        $this->addToDB(); // Create record if doesn't exist
    } else {
        // Ensure all required fields are set, even if they exist in DB but not in fields array
        $this->fields['threshold'] = $this->fields['threshold'] ?? 1;
        $this->fields['time_unit'] = $this->fields['time_unit'] ?? 'days';
    }

    if (isset($_POST['update'])) {
        $threshold = isset($_POST['threshold']) ? (int)$_POST['threshold'] : 1;
        $time_unit = isset($_POST['time_unit']) ? $_POST['time_unit'] : 'days';

        // Optional: validate time_unit
        $valid_units = ['days', 'hours'];
        if (!in_array($time_unit, $valid_units)) {
            $time_unit = 'days';
        }

        $config = new PluginTicketreminderConfig();
        $config->getFromDB(1);
        $config->updateInDB([
            'threshold' => $threshold,
            'time_unit' => $time_unit
        ]);
        
        // Reload the values after update
        $this->getFromDB(1);
    }

    $this->showForm(1);
}
    
    function showForm($ID, array $options = []) {
    // Ensure fields are set
    $threshold = $this->fields['threshold'] ?? 1;
    $time_unit = $this->fields['time_unit'] ?? 'days';
    
    // Start form
    echo "<form action='".Toolbox::getItemTypeFormURL(__CLASS__)."' method='post'>";
    echo "<div class='center'>";
    echo "<table class='tab_cadre_fixe'>";
    echo "<tr><th colspan='2'>".__('Ticket Reminder Settings', 'ticketreminder')."</th></tr>";

    // Threshold field
    echo "<tr class='tab_bg_1'>";
    echo "<td>".__('Threshold', 'ticketreminder')."</td>";
    echo "<td>";
    echo Html::input('threshold', [
        'value' => $threshold,
        'type' => 'number',
        'min' => 1
    ]);
    echo "</td></tr>";

    // Time unit field
    echo "<tr class='tab_bg_1'>";
    echo "<td>".__('Time Unit', 'ticketreminder')."</td>";
    echo "<td>";
    Dropdown::showFromArray('time_unit', [
        'days' => __('Days'),
        'hours' => __('Hours')
    ], [
        'value' => $time_unit
    ]);
    echo "</td></tr>";

    // Form buttons
    echo "<tr class='tab_bg_2'>";
    echo "<td colspan='2' class='center'>";
    echo Html::hidden('id', ['value' => 1]);
    echo Html::submit(_x('button', 'Save'), ['name' => 'update']);
    echo "</td></tr>";

    echo "</table>";
    echo "</div>";
    Html::closeForm();
}
    static function install(Migration $migration) {
        global $DB;
        
        $table = 'glpi_plugin_ticketreminder_configs';
        
        if (!$DB->tableExists($table)) {
            $query = "CREATE TABLE `$table` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `threshold` int(11) NOT NULL DEFAULT 1,
                `time_unit` varchar(10) NOT NULL DEFAULT 'days',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $DB->query($query) or die($DB->error());
            
            // Insert default configuration
            $query = "INSERT INTO `$table` (`id`, `threshold`, `time_unit`) 
                     VALUES (1, 1, 'days')";
            $DB->query($query) or die($DB->error());
        }
    }
    
    static function uninstall(Migration $migration) {
        global $DB;
        
        $table = 'glpi_plugin_ticketreminder_configs';
        if ($DB->tableExists($table)) {
            $DB->query("DROP TABLE `$table`") or die($DB->error());
        }
    }
}