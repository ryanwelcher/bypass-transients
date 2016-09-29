<?php
/**
 * Plugin Name: Suspend Transients
 * Plugin URI:
 * Description: Bypass transients for development.
 * Version: 1.0.0
 * Author: Ryan Welcher
 * Author URI: http://www.ryanwelcher.com
 * Test Domain: brmi
 * Domain Path : /lang
 * License: GPL2
 *
 * Copyright 2014  Ryan Welcher  (email : me@ryanwelcher.com)
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace suspendTransients;

require_once 'includes/class-suspend-transients.php';
require_once 'includes/class-suspend-transients-database.php';



// Get the instance we need.

$suspend_transients = ( wp_using_ext_object_cache() ) ? new Suspend_Transients() : new Suspend_Transients_Database();
$suspend_transients->init();

register_activation_hook( __FILE__, array( $suspend_transients, 'on_activate' ) );

function add_to_debug_bar( $panels ) {
	require_once 'includes/class-suspend-transients-debug-bar.php';
	$panels[] = new \Suspend_Transients_Debug_Bar();
	return $panels;
}
add_filter( 'debug_bar_panels', __NAMESPACE__ .'\\add_to_debug_bar' );

