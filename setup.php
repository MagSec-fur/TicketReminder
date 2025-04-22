<?php
function plugin_init_ticketreminder() {
    global $PLUGIN_HOOKS;
    
    $PLUGIN_HOOKS['csrf_compliant']['ticketreminder'] = true;
    $PLUGIN_HOOKS['cron']['ticketreminder'] = ['PluginTicketreminderCron', 'cronTicketreminder'];
    
    Plugin::registerClass('PluginTicketreminderCron', [
        'addtabon' => ['CronTask'],
        'checkright' => true
    ]);
}

function plugin_version_ticketreminder() {
    return [
        'name'           => 'Ticket Reminder',
        'version'        => '1.0.1',
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
    $cron = new CronTask();
    if (!$cron->getFromDBbyName('PluginTicketreminderCron', 'ticketreminder')) {
        $cron->add([
            'name'        => 'ticketreminder',
            'itemtype'    => 'PluginTicketreminderCron',
            'frequency'   => 86400, // Daily
            'param'       => 0,
            'state'       => CronTask::STATE_DISABLE,
            'mode'        => CronTask::MODE_EXTERNAL,
            'comment'     => 'Send reminders for tickets open more than 3 days'
        ]);
    }
    return true;
}

function plugin_ticketreminder_uninstall() {
    $cron = new CronTask();
    if ($cron->getFromDBbyName('PluginTicketreminderCron', 'ticketreminder')) {
        $cron->delete(['id' => $cron->getID()]);
    }
    return true;
}