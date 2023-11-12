<?php

/*  Poweradmin, a friendly web-based admin tool for PowerDNS.
 *  See <https://www.poweradmin.org> for more details.
 *
 *  Copyright 2007-2010 Rejo Zenger <rejo@zenger.nl>
 *  Copyright 2010-2023 Poweradmin Development Team
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use Poweradmin\Application\Presenter\ErrorPresenter;
use Poweradmin\Domain\Error\ErrorMessage;
use Poweradmin\PDOLayer;

function dbConnect(array $databaseCredentials, $isQuiet = true, $installerMode = false)
{
    $db_type = $databaseCredentials['db_type'];
    $db_user = $databaseCredentials['db_user'];
    $db_pass = $databaseCredentials['db_pass'];
    $db_host = $databaseCredentials['db_host'];
    $db_port = $databaseCredentials['db_port'];
    $db_name = $databaseCredentials['db_name'];
    $db_charset = $databaseCredentials['db_charset'];
    $db_file = $databaseCredentials['db_file'] ?? null;
    $db_debug = $databaseCredentials['db_debug'] ?? false;

    global $sql_regexp;

    if (!(isset($db_type) && $db_type == 'mysql' || $db_type == 'mysqli' || $db_type == 'pgsql' || $db_type == 'sqlite' || $db_type == 'sqlite3')) {
        if (!$installerMode) {
            include_once("header.inc.php");
        }
        if (!file_exists('install')) {
            $error = new ErrorMessage(_('No or unknown database type has been set in config.inc.php.'));
            $errorPresenter = new ErrorPresenter();
            $errorPresenter->present($error);
        }

        if (!$installerMode) {
            include_once("footer.inc.php");
        }

        exit;
    }

    if ($db_type != 'sqlite' && $db_type != 'sqlite3' && !(isset($db_user) && $db_user != "")) {
        $error = new ErrorMessage(_('No database username has been set in config.inc.php.'));
        $errorPresenter = new ErrorPresenter();
        $errorPresenter->present($error);

        if (!$installerMode) {
            include_once("footer.inc.php");
        }

        exit;
    }

    if ($db_type != 'sqlite' && $db_type != 'sqlite3' && !(isset($db_pass) && $db_pass != '')) {
        if (!$installerMode) {
            include_once("header.inc.php");
        }

        $error = new ErrorMessage(_('No database password has been set in config.inc.php.'));
        $errorPresenter = new ErrorPresenter();
        $errorPresenter->present($error);

        if (!$installerMode) {
            include_once("footer.inc.php");
        }

        exit;
    }

    if ($db_type != 'sqlite' && $db_type != 'sqlite3' && !(isset($db_host) && $db_host != '')) {
        if (!$installerMode) {
            include_once("header.inc.php");
        }

        $error = new ErrorMessage(_('No database host has been set in config.inc.php.'));
        $errorPresenter = new ErrorPresenter();
        $errorPresenter->present($error);

        if (!$installerMode) {
            include_once("footer.inc.php");
        }
        exit;
    }

    if ($db_type != 'sqlite' && $db_type != 'sqlite3' && !(isset($db_name) && $db_name != '')) {
        if (!$installerMode) {
            include_once("header.inc.php");
        }

        $error = new ErrorMessage(_('No database name has been set in config.inc.php.'));
        $errorPresenter = new ErrorPresenter();
        $errorPresenter->present($error);

        if (!$installerMode) {
            include_once("footer.inc.php");
        }
        exit;
    }

    if ($db_type != 'sqlite' && $db_type != 'sqlite3' && !(isset($db_port)) || $db_port == '') {
        if ($db_type == "mysql" || $db_type == "mysqli") {
            $db_port = 3306;
        } else {
            $db_port = 5432;
        }
    }

    if (($db_type == 'sqlite' || $db_type == 'sqlite3') && (!(isset($db_file) && $db_file != ''))) {
        if (!$installerMode) {
            include_once("header.inc.php");
        }

        $error = new ErrorMessage(_('No database file has been set in config.inc.php.'));
        $errorPresenter = new ErrorPresenter();
        $errorPresenter->present($error);

        if (!$installerMode) {
            include_once("footer.inc.php");
        }

        exit;
    }

    if ($db_type == 'sqlite' || $db_type == 'sqlite3') {
        $dsn = "$db_type:$db_file";
    } else {
        $dsn = "$db_type:host=$db_host;port=$db_port;dbname=$db_name";
    }

    if ($db_type === 'mysql' && $db_charset === 'utf8') {
        $dsn .= ';charset=utf8';
    }

    $db = new PDOLayer($dsn, $db_user, $db_pass, [], $isQuiet);

    if (isset($db_debug) && $db_debug) {
        $db->setOption('debug', 1);
    }

    $dsn = '';

    if ($db_type == 'mysql' || $db_type == 'mysqli') {
        $sql_regexp = "REGEXP";
    } else if ($db_type == 'sqlite' || $db_type == 'sqlite3') {
        $sql_regexp = 'GLOB';
    } elseif ($db_type == "pgsql") {
        $sql_regexp = "~";
    } else {
        if (!$installerMode) {
            include_once("header.inc.php");
        }

        $error = new ErrorMessage(_('No or unknown database type has been set in config.inc.php.'));
        $errorPresenter = new ErrorPresenter();
        $errorPresenter->present($error);

        if (!$installerMode) {
            include_once("footer.inc.php");
        }

        exit;
    }
    return $db;
}
