<?php
/**
 * Plugin Name: Smart WP Login
 * Version: 1.2
 * Author: Nishant Kumar
 * Author URI: http://thebinary.in/
 * Text Domain: smart-wp-login
 * Description: Use email to login, register and retrieve password in WordPress.
 */

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

//No direct access allowed.
if(!function_exists('add_action')){
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    echo 'Get Well Soon. :)';
    exit();
}

//global constants
define('SWPL_VERSION', '1.0.2');
define('SWPL_URL', plugin_dir_url(__FILE__));

//load required files
require 'swpl_settings.php';
require 'swpl_engine.php';
require 'swpl_overrides_wp.php';
require_once 'vendor/Hashids/HashGenerator.php';
require_once 'vendor/Hashids/Hashids.php';

//Go Go Go
new SWPL_Settings();
new SWPL_Engine();