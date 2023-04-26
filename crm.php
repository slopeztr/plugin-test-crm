<?php 
/**
 * Plugin Name: Demo CRM
 * Description: CRM Plugin
 * Author: slopeztr
 * Version: 1.0.1
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined( 'ABSPATH' ) || exit;

// general constants
define( 'CRM__PLUGIN_VERSION', '1.0.1' );
define( 'CRM__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CRM__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CRM__PLUGIN_TEXTDOMAIN', 'crm' );

// required functions
require_once( CRM__PLUGIN_DIR . '/helpers.php' );

// Include the main CRM class
if ( ! class_exists( 'CRM', false ) ) {
	include_once CRM__PLUGIN_DIR . 'includes/classes/class-crm.php';
}

function CRM() {
  return CRM::instance();
}

$GLOBALS[ 'CRM' ] = CRM();
