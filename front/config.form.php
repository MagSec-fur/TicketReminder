<?php
include ('../../../inc/includes.php');

Html::header(__('Ticket Reminder Configuration', 'ticketreminder'), $_SERVER['PHP_SELF'], 'config', 'plugins');

$config = new PluginTicketreminderConfig();
$config->showForm();

Html::footer();