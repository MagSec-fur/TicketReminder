<?php
<?php
function plugin_init_ticketreminder() {
    global $PLUGIN_HOOKS;
    
    // Explicit CSRF compliance declaration
    $PLUGIN_HOOKS['csrf_compliant']['ticketreminder'] = true;
    
    // Cron hook registration
    $PLUGIN_HOOKS['cron']['ticketreminder'] = ['PluginTicketreminderCron', 'cronTicketreminder'];
    
    // Correct class registration - removed invalid 'checkright' parameter
    Plugin::registerClass('PluginTicketreminderCron', [
        'addtabon' => ['CronTask']
    ]);
    
    // Notification event registration
    $PLUGIN_HOOKS['item_get_events']['ticketreminder'] = [
        'NotificationTargetTicket' => ['plugin_ticketreminder_getEvents']
    ];
    
    // Add configuration tab to global configuration
    $PLUGIN_HOOKS['config_page']['ticketreminder'] = 'front/config.form.php';
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
    // Create cron task
    $cron = new CronTask();
    if (!$cron->getFromDBbyName('PluginTicketreminderCron', 'ticketreminder')) {
        $cron->add([
            'name'        => 'ticketreminder',
            'itemtype'    => 'PluginTicketreminderCron',
            'frequency'   => 86400,
            'param'       => 0,
            'state'       => CronTask::STATE_DISABLE,
            'mode'        => CronTask::MODE_EXTERNAL,
            'comment'     => 'Send reminders for tickets open more than 3 days'
        ]);
    }
    
    // Create right for this plugin
    $right = new ProfileRight();
    $right->add([
        'name' => 'plugin_ticketreminder',
        'rights' => READ | UPDATE | CREATE | PURGE
    ]);
    
    // Add right to all profiles that have config UPDATE right
    $profile = new Profile();
    foreach ($profile->find(['config' => UPDATE]) as $profile_data) {
        $profile->update([
            'id' => $profile_data['id'],
            'plugin_ticketreminder' => UPDATE
        ]);
    }
    
    return true;
}

function plugin_ticketreminder_uninstall() {
    // Remove cron task
    $cron = new CronTask();
    if ($cron->getFromDBbyName('PluginTicketreminderCron', 'ticketreminder')) {
        $cron->delete(['id' => $cron->getID()]);
    }
    
    // Remove right
    $right = new ProfileRight();
    $right->deleteByCriteria(['name' => 'plugin_ticketreminder']);
    
    return true;
}