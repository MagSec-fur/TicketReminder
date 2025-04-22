<?php
function plugin_ticketreminder_install_db() {
    global $DB;
    $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_ticketreminder_configs` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `entities_id` INT NOT NULL DEFAULT 0,
        `days` INT NOT NULL DEFAULT 3,
        `hours` INT NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `entities_id` (`entities_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $DB->query($query);
    return true;
}
?>
