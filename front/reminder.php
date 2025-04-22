<?php
include ('../../../inc/includes.php');

Html::header(__('Ticket Reminder Configuration', 'ticketreminder'), $_SERVER['PHP_SELF'], 'config', 'plugin');

if (!Session::haveRight('config', UPDATE)) {
    Html::displayRightError();
    exit;
}

Search::show('PluginTicketreminderCron');

Html::footer();