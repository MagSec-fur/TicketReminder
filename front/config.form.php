<?php
include('../../../inc/includes.php');

Html::header(
   __('Ticket Reminder Config', 'ticketreminder'),
   $_SERVER['PHP_SELF'],
   'config'
);

$config = new PluginTicketreminderConfig();
$config->showForm(1);

Html::footer();
