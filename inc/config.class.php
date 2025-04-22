<?php
class PluginTicketreminderConfig extends CommonDBTM {
    static function getConfig($entities_id = 0) {
        global $DB;
        $res = $DB->request([
            'FROM' => 'glpi_plugin_ticketreminder_configs',
            'WHERE' => ['entities_id' => $entities_id]
        ]);
        if ($row = $res->next()) {
            return $row;
        }
        return ['days' => 3, 'hours' => 0];
    }

    static function setConfig($entities_id, $days, $hours) {
        global $DB;
        $res = $DB->request([
            'FROM' => 'glpi_plugin_ticketreminder_configs',
            'WHERE' => ['entities_id' => $entities_id]
        ]);
        if ($row = $res->next()) {
            $DB->update(
                'glpi_plugin_ticketreminder_configs',
                ['days' => $days, 'hours' => $hours],
                ['entities_id' => $entities_id]
            );
        } else {
            $DB->insert(
                'glpi_plugin_ticketreminder_configs',
                ['entities_id' => $entities_id, 'days' => $days, 'hours' => $hours]
            );
        }
    }
}
?>
