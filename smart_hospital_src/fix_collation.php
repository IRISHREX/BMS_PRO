<?php
/**
 * fix_collation.php — One-time database collation migration script
 *
 * Converts ALL tables and text columns in the database to utf8mb4 / utf8mb4_unicode_ci.
 * This permanently fixes MySQL Error 1271 ("Illegal mix of collations for operation UNION").
 *
 * Usage:
 *   Browser: https://your-domain.com/fix_collation.php?key=YOUR_SECRET_KEY
 *   CLI:     php fix_collation.php
 *
 * IMPORTANT: Delete this file from the server after running it successfully.
 */

// ── Configuration ──────────────────────────────────────────────────────────────
$SECRET_KEY       = 'BMS_FIX_COLLATION_2026';   // Change this before deploying
$TARGET_CHARSET   = 'utf8mb4';
$TARGET_COLLATION = 'utf8mb4_unicode_ci';
$DRY_RUN          = false;  // Set to true to preview changes without executing

// ── Security Check ─────────────────────────────────────────────────────────────
$is_cli = (php_sapi_name() === 'cli');

if (!$is_cli) {
    $provided_key = isset($_GET['key']) ? $_GET['key'] : '';
    if ($provided_key !== $SECRET_KEY) {
        http_response_code(403);
        die('<h1>403 Forbidden</h1><p>Invalid or missing security key. Usage: ?key=YOUR_SECRET_KEY</p>');
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Collation Fix</title>'
       . '<style>body{font-family:monospace;background:#1a1a2e;color:#e0e0e0;padding:20px;}'
       . '.ok{color:#4ecca3}.warn{color:#ffc107}.err{color:#ff6b6b}.info{color:#7ec8e3}'
       . 'h1{color:#4ecca3}h2{color:#7ec8e3;border-bottom:1px solid #333;padding-bottom:5px}'
       . '.summary{background:#16213e;padding:15px;border-radius:8px;margin:10px 0}'
       . '</style></head><body>';
    echo '<h1>🔧 Database Collation Migration</h1>';
    if ($DRY_RUN) echo '<p class="warn">⚠ DRY RUN MODE — no changes will be made</p>';
    ob_flush(); flush();
}

// ── Load CodeIgniter database config ────────────────────────────────────────────
define('BASEPATH', __DIR__ . '/system/');
define('ENVIRONMENT', 'production');
require_once __DIR__ . '/application/config/database.php';

// ── Helper Functions ────────────────────────────────────────────────────────────

function out($msg, $class = 'info') {
    global $is_cli;
    if ($is_cli) {
        echo strip_tags($msg) . "\n";
    } else {
        echo "<p class=\"$class\">$msg</p>";
        ob_flush(); flush();
    }
}

function fix_database($db_config, $db_label, $target_charset, $target_collation, $dry_run) {
    $host     = $db_config['hostname'];
    $user     = $db_config['username'];
    $pass     = $db_config['password'];
    $dbname   = $db_config['database'];
    $port     = isset($db_config['port']) ? (int)$db_config['port'] : 3306;

    out("<h2>Database: <strong>$db_label</strong> ($dbname @ $host:$port)</h2>", 'info');

    $mysqli = new mysqli($host, $user, $pass, $dbname, $port);
    if ($mysqli->connect_errno) {
        out("❌ Connection failed: " . $mysqli->connect_error, 'err');
        return ['tables_fixed' => 0, 'columns_fixed' => 0, 'errors' => 1];
    }

    $stats = ['tables_fixed' => 0, 'columns_fixed' => 0, 'tables_skipped' => 0, 'columns_skipped' => 0, 'errors' => 0];

    // Relax SQL mode to handle legacy data (0000-00-00 dates, etc.)
    $mysqli->query("SET SESSION sql_mode = ''");
    // Disable foreign key checks to avoid constraint errors during ALTER
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

    // Step 1: Convert database default
    $sql = "ALTER DATABASE `$dbname` CHARACTER SET $target_charset COLLATE $target_collation";
    out("📦 Converting database default: <code>$sql</code>", 'info');
    if (!$dry_run) {
        if (!$mysqli->query($sql)) {
            out("❌ Error: " . $mysqli->error, 'err');
            $stats['errors']++;
        } else {
            out("✅ Database default converted", 'ok');
        }
    }

    // Step 2: Get all tables
    $escaped_dbname = $mysqli->real_escape_string($dbname);
    $tables_result = $mysqli->query("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$escaped_dbname' AND TABLE_TYPE = 'BASE TABLE'");
    if (!$tables_result) {
        out("❌ Cannot read tables: " . $mysqli->error, 'err');
        $mysqli->close();
        return $stats;
    }

    $tables = [];
    while ($row = $tables_result->fetch_assoc()) {
        $tables[] = $row;
    }
    $tables_result->free();

    out("📋 Found <strong>" . count($tables) . "</strong> tables to check", 'info');

    foreach ($tables as $table) {
        $tbl_name      = $table['TABLE_NAME'];
        $tbl_collation = $table['TABLE_COLLATION'];

        // Step 3: Convert table default charset/collation
        if ($tbl_collation !== $target_collation) {
            $sql = "ALTER TABLE `$tbl_name` DEFAULT CHARACTER SET $target_charset COLLATE $target_collation";
            if (!$dry_run) {
                if (!$mysqli->query($sql)) {
                    out("  ❌ Table <code>$tbl_name</code> default: " . $mysqli->error, 'err');
                    $stats['errors']++;
                } else {
                    out("  ✅ Table <code>$tbl_name</code> default: $tbl_collation → $target_collation", 'ok');
                    $stats['tables_fixed']++;
                }
            } else {
                out("  🔍 [DRY] Table <code>$tbl_name</code>: $tbl_collation → $target_collation", 'warn');
                $stats['tables_fixed']++;
            }
        } else {
            $stats['tables_skipped']++;
        }

        // Step 4: Get all text columns in this table that need conversion
        $escaped_tbl = $mysqli->real_escape_string($tbl_name);
        $cols_sql = "SELECT COLUMN_NAME, COLUMN_TYPE, CHARACTER_SET_NAME, COLLATION_NAME, IS_NULLABLE, COLUMN_DEFAULT
                     FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = '$escaped_dbname'
                       AND TABLE_NAME = '$escaped_tbl'
                       AND COLLATION_NAME IS NOT NULL
                       AND COLLATION_NAME != '$target_collation'";
        $cols_result = $mysqli->query($cols_sql);

        if ($cols_result && $cols_result->num_rows > 0) {
            while ($col = $cols_result->fetch_assoc()) {
                $col_name      = $col['COLUMN_NAME'];
                $col_type      = $col['COLUMN_TYPE'];
                $col_collation = $col['COLLATION_NAME'];
                $nullable      = ($col['IS_NULLABLE'] === 'YES') ? 'NULL' : 'NOT NULL';
                $default_part  = '';
                if ($col['COLUMN_DEFAULT'] !== null) {
                    $escaped_default = $mysqli->real_escape_string($col['COLUMN_DEFAULT']);
                    $default_part = " DEFAULT '$escaped_default'";
                } elseif ($col['IS_NULLABLE'] === 'YES') {
                    $default_part = ' DEFAULT NULL';
                }

                $sql = "ALTER TABLE `$tbl_name` MODIFY `$col_name` $col_type CHARACTER SET $target_charset COLLATE $target_collation $nullable$default_part";

                if (!$dry_run) {
                    if (!$mysqli->query($sql)) {
                        out("    ❌ Column <code>$tbl_name.$col_name</code>: " . $mysqli->error, 'err');
                        $stats['errors']++;
                    } else {
                        out("    ✅ Column <code>$tbl_name.$col_name</code> ($col_type): $col_collation → $target_collation", 'ok');
                        $stats['columns_fixed']++;
                    }
                } else {
                    out("    🔍 [DRY] Column <code>$tbl_name.$col_name</code> ($col_type): $col_collation → $target_collation", 'warn');
                    $stats['columns_fixed']++;
                }
            }
        }
        if ($cols_result) $cols_result->free();
    }

    // Step 5: Verification — check for remaining mismatches
    out("<h2>Verification for $db_label</h2>", 'info');

    $verify_tables = $mysqli->query("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$escaped_dbname' AND TABLE_TYPE = 'BASE TABLE' AND TABLE_COLLATION != '$target_collation'");
    $remaining_tables = $verify_tables ? $verify_tables->num_rows : -1;

    $verify_cols = $mysqli->query("SELECT TABLE_NAME, COLUMN_NAME, COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$escaped_dbname' AND COLLATION_NAME IS NOT NULL AND COLLATION_NAME != '$target_collation'");
    $remaining_cols = $verify_cols ? $verify_cols->num_rows : -1;

    if ($remaining_tables == 0 && $remaining_cols == 0) {
        out("🎉 All tables and columns are now <code>$target_collation</code>!", 'ok');
    } else {
        if ($remaining_tables > 0) {
            out("⚠ $remaining_tables tables still have wrong collation:", 'warn');
            while ($row = $verify_tables->fetch_assoc()) {
                out("   - <code>{$row['TABLE_NAME']}</code>: {$row['TABLE_COLLATION']}", 'warn');
            }
        }
        if ($remaining_cols > 0) {
            out("⚠ $remaining_cols columns still have wrong collation:", 'warn');
            while ($row = $verify_cols->fetch_assoc()) {
                out("   - <code>{$row['TABLE_NAME']}.{$row['COLUMN_NAME']}</code>: {$row['COLLATION_NAME']}", 'warn');
            }
        }
    }
    if ($verify_tables) $verify_tables->free();
    if ($verify_cols)   $verify_cols->free();

    // Re-enable foreign key checks
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
    $mysqli->close();

    return $stats;
}

// ── Main Execution ─────────────────────────────────────────────────────────────

$all_stats = [];

// Fix the default (main) database
$all_stats['default'] = fix_database($db['default'], 'Main Database (default)', $TARGET_CHARSET, $TARGET_COLLATION, $DRY_RUN);

// Fix multi-branch databases if any
foreach ($db as $key => $config) {
    if ($key === 'default') continue;
    if (strpos($key, 'branch_') === 0) {
        $all_stats[$key] = fix_database($config, "Branch: $key", $TARGET_CHARSET, $TARGET_COLLATION, $DRY_RUN);
    }
}

// ── Summary ─────────────────────────────────────────────────────────────────────
if (!$is_cli) {
    echo '<div class="summary">';
    echo '<h2>📊 Summary</h2>';
}

$total_tables = 0; $total_cols = 0; $total_errors = 0;
foreach ($all_stats as $label => $s) {
    out("  $label: {$s['tables_fixed']} tables fixed, {$s['columns_fixed']} columns fixed, {$s['errors']} errors", $s['errors'] > 0 ? 'err' : 'ok');
    $total_tables += $s['tables_fixed'];
    $total_cols   += $s['columns_fixed'];
    $total_errors += $s['errors'];
}

out("", 'info');
if ($total_errors === 0) {
    out("✅ Migration completed successfully! Total: $total_tables tables, $total_cols columns converted.", 'ok');
    out("", 'info');
    out("⚠ <strong>IMPORTANT: Delete this file from the server now!</strong>", 'warn');
} else {
    out("⚠ Migration completed with $total_errors error(s). Review the log above.", 'warn');
}

if (!$is_cli) {
    echo '</div></body></html>';
}
