<?php
include ('../../../inc/includes.php');
Session::checkRight("config", UPDATE);
$csrf_token = Session::getNewCSRFToken();
if (isset($_POST['update'])) {
    // CSRF check
    Session::checkCSRF([
        '_glpi_csrf_token' => $_POST['_glpi_csrf_token']
    ]);
    // Save config here...
    Html::redirect($_SERVER['PHP_SELF']);
}

Html::header(__('Ticket Reminder Configuration', 'ticketreminder'), $_SERVER['PHP_SELF'], 'config', 'plugin');

echo "<form method='post'>";
echo "<table class='tab_cadre_fixe'>";
echo "<tr><th colspan='2'>Settings</th></tr>";
echo "<tr class='tab_bg_1'><td>Days</td><td><input type='number' name='days' min='0' max='365' value='3'></td></tr>";
echo "<tr class='tab_bg_1'><td>Hours</td><td><input type='number' name='hours' min='0' max='23' value='0'></td></tr>";
echo Html::hidden('_glpi_csrf_token', ['value' => $csrf_token]);
echo "<tr class='tab_bg_2'><td colspan='2' class='center'><input type='submit' name='update' value='Save' class='submit'></td></tr>";
echo "</table>";
echo "</form>";
Html::closeForm();
Html::footer();
?>
