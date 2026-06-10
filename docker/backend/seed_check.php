<?php
/**
 * Checks if marketplace_themes table has any rows.
 * Exit code 0 = empty (should seed), 1 = has data.
 */
try {
    $db = \Config\Database::connect();
    $count = $db->table('marketplace_themes')->countAllResults();
    exit($count > 0 ? 1 : 0);
} catch (\Exception $e) {
    exit(0); // On error, assume empty and seed
}
