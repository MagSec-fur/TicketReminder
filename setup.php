<?php
function plugin_init_ticketreminder() {
    global $PLUGIN_HOOKS;
    
    $PLUGIN_HOOKS['csrf_compliant']['ticketreminder'] = true;
    $PLUGIN_HOOKS['cron']['ticketreminder'] = ['PluginTicketreminderCron', 'cronTicketreminder'];
    
    Plugin::registerClass('PluginTicketreminderCron', ['addtabon' => ['CronTask']]);
}

function plugin_version_ticketreminder() {
    return [
        'name'           => 'Ticket Reminder',
        'version'        => '1.0.0',
        'author'         => 'Destiny_fur',
        'license'        => 'GPL32',
        'homepage'       => 'https://magsec.nl',
        'requirements'   => [
            'glpi' => [
                'min' => '9.5',
            ]
        ]
    ];
}

function plugin_ticketreminder_check_prerequisites() {
    if (version_compare(GLPI_VERSION, '9.5', 'lt')) {
        echo "This plugin requires GLPI 9.5 or higher";
        return false;
    }
    return true;
}


function plugin_ticketreminder_check_config($verbose = false) {
    if ($verbose) {
        echo 'Configuration is OK';
    }
    return true;
}

function plugin_ticketreminder_install() {
    global $DB;
    
    // Create cron task entry
    $cron = new CronTask();
    if (!$cron->getFromDBbyName('PluginTicketreminderCron', 'ticketreminder')) {
        $cron->add([
            'name'        => 'ticketreminder',
            'itemtype'    => 'PluginTicketreminderCron',
            'frequency'   => 86400, // Daily
            'param'       => 0,
            'state'       => CronTask::STATE_DISABLE,
            'mode'        => CronTask::MODE_EXTERNAL
        ]);
    }
    
    return true;
}

function plugin_ticketreminder_uninstall() {
    global $DB;
    
    // Remove cron task
    $cron = new CronTask();
    if ($cron->getFromDBbyName('PluginTicketreminderCron', 'ticketreminder')) {
        $cron->delete(['id' => $cron->getID()]);
    }
    
    return true;
}