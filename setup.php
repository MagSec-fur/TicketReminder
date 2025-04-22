<?php
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}
define('TICKETREMINDER_ROOT', dirname(__FILE__));

function plugin_init_ticketreminder() {
    global $PLUGIN_HOOKS;
    $PLUGIN_HOOKS['csrf_compliant']['ticketreminder'] = true;
    $PLUGIN_HOOKS['menu_toadd']['ticketreminder'] = ['config'];
    $PLUGIN_HOOKS['config_page']['ticketreminder'] = 'front/config.form.php';
    $PLUGIN_HOOKS['cron']['ticketreminder'] = ['PluginTicketreminderTicketreminder', 'cronReminder'];
}

function plugin_version_ticketreminder() {
    return [
        'name'           => 'Ticket Reminder',
        'version'        => '1.0.4',
        'author'         => 'Destiny_fur',
        'license'        => 'GPLv3+',
        'homepage'       => '',
        'minGlpiVersion' => '10.0.0'
    ];
}

function plugin_ticketreminder_install() {
    include_once(TICKETREMINDER_ROOT.'/install.php');
    return plugin_ticketreminder_install_db();
}

function plugin_ticketreminder_uninstall() {
    global $DB;
    $DB->query("DROP TABLE IF EXISTS `glpi_plugin_ticketreminder_configs`");
    return true;
}
?>
