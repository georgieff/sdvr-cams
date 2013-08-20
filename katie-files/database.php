<?php

if (!defined('BASEURL')) {
    //exit('No direct calls allowed!');
}

// Start of database.php (KatiePHP) /config
/* * ************************************************************************ */

/* Automatically connection to the database when the class is called.
 * EXAMPLE: *
 *          $config['auto_connect'] = true; <<<
 */
$config['auto_connect'] = true;

/* Change database connection options
 * EXAMPLE: *
 *          $config['db_server'] = 'localhost'; <<<
 *          $config['db_name'] = 'database_name'; <<<
 *          $config['db_user'] = 'root'; <<<
 *          $config['db_password'] = 'your_password'; <<<
 *          $config['db_charset'] = 'UTF8'; <<<
 *          $config['db_prefix'] = 'UD_'; <<<
 */
$config['db_server'] = 'localhost';
$config['db_name'] = 'test';
$config['db_user'] = 'root';
$config['db_password'] = '';
$config['db_charset'] = 'UTF8';
$config['db_prefix'] = 'sdvr_';


// End of the file database.php