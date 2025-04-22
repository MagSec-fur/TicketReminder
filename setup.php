<?php
function plugin_init_ticketreminder() {
    global $PLUGIN_HOOKS;
    
    $PLUGIN_HOOKS['csrf_compliant']['ticketreminder'] = true;
    $PLUGIN_HOOKS['cron']['ticketreminder'] = ['PluginTicketreminderCron', 'cronTicketreminder'];
    $PLUGIN_HOOKS['menu_toadd']['ticketreminder'] = ['config'];
    $PLUGIN_HOOKS['config_page']['ticketreminder'] = 'front/config.form.php';

    Plugin::registerClass('PluginTicketreminderConfig', [
        'addtabon' => ['Config']
    ]);
    
    Plugin::registerClass('PluginTicketreminderCron');
}


function plugin_version_ticketreminder() {
    return [
        'name'           => 'Ticket Reminder',
        'version'        => '1.0.3',
        'author'         => 'Destiny_fur',
        'license'        => 'GPLv3+',
        'homepage'       => 'https://mgsec.nl',
        'requirements'   => [
            'glpi' => [
                'min' => '9.5',
                'max' => '10.1'
            ]
        ]
    ];
}

function plugin_ticketreminder_check_prerequisites() {
    if (version_compare(GLPI_VERSION, '9.5', 'lt') || version_compare(GLPI_VERSION, '10.1', 'ge')) {
        echo "This plugin requires GLPI version between 9.5 and 10.0";
        return false;
    }
    return true;
}

function plugin_ticketreminder_check_config($verbose = false) {
    return true;
}

function plugin_ticketreminder_install() {
    global $DB;
    
    // Create config table
    if (!$DB->tableExists('glpi_plugin_ticketreminder_config')) {
        $query = "CREATE TABLE `glpi_plugin_ticketreminder_config` (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `threshold` INT NOT NULL DEFAULT 3,
            `time_unit` ENUM('days', 'hours') NOT NULL DEFAULT 'days',
            `last_execution` DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $DB->queryOrDie($query, $DB->error());
        
        // Insert default config
        $DB->insertOrDie('glpi_plugin_ticketreminder_config', [
            'threshold' => 1,
            'time_unit' => 'days'
        ], $DB->error());
    }
    
    // Create cron task
    $cron = new CronTask();
    if (!$cron->getFromDBbyName('PluginTicketreminderCron', 'ticketreminder')) {
        $cron->add([
            'name'        => 'ticketreminder',
            'itemtype'    => 'PluginTicketreminderCron',
            'frequency'   => 3600, // Run hourly
            'param'       => 0,
            'state'       => CronTask::STATE_DISABLE,
            'mode'        => CronTask::MODE_EXTERNAL,
            'comment'     => 'Send reminders for stale tickets'
        ]);
    }
    
    return true;
}

function plugin_ticketreminder_uninstall() {
    global $DB;
    
    // Remove config table
    if ($DB->tableExists('glpi_plugin_ticketreminder_config')) {
        $query = "DROP TABLE `glpi_plugin_ticketreminder_config`";
        $DB->queryOrDie($query, $DB->error());
    }
    
    // Remove cron task
    $cron = new CronTask();
    if ($cron->getFromDBbyName('PluginTicketreminderCron', 'ticketreminder')) {
        $cron->delete(['id' => $cron->getID()]);
    }
    
    return true;
}