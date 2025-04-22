<?php
include ('../../../inc/includes.php');
Session::checkRight("config", READ);

$config = PluginTicketreminderConfig::getConfig(0);

if (isset($_POST['update'])) {
    $days = max(0, (int)$_POST['days']);
    $hours = max(0, min(23, (int)$_POST['hours']));
    PluginTicketreminderConfig::setConfig(0, $days, $hours);
    Html::displayMessageAfterRedirect(__('Configuration saved!', 'ticketreminder'), true, $_SERVER['PHP_SELF']);
}

Html::header(__('Ticket Reminder Configuration', 'ticketreminder'), $_SERVER['PHP_SELF'], 'config', 'plugin');
echo "<form method='post'>";
echo "<table class='tab_cadre_fixe'>";
echo "<tr><th colspan='2'>".__('Reminder Threshold Configuration', 'ticketreminder')."</th></tr>";
echo "<tr class='tab_bg_1'><td>".__('Days', 'ticketreminder')."</td><td><input type='number' name='days' min='0' max='365' value='".(int)$config['days']."'></td></tr>";
echo "<tr class='tab_bg_1'><td>".__('Hours', 'ticketreminder')."</td><td><input type='number' name='hours' min='0' max='23' value='".(int)$config['hours']."'></td></tr>";
echo "<tr class='tab_bg_2'><td colspan='2' class='center'><input type='submit' name='update' value='"._sx('button', 'Save')."' class='submit'></td></tr>";
echo "</table>";
echo "</form>";
Html::footer();
?>
