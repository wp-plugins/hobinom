<?php
 /*
 Plugin Name: HobiNom
 Plugin URI: http://hobihut.com/hobinom
 Description: Manage your eNom reseller account from within Wordpress, includes widgets. Requires an eNom reseller account.
 Version: .5
 Author: Immortal Design
 Author URI: http://immortaldc.com
 */

 
/*  © Copyright 2013+  Immortal Design ( dev@immortaldc.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2 or later, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once('include/functions.php');
require_once('widget.php');

// Globals
define( 'HOBINOM_PATH', plugin_dir_path(__FILE__) );
define( 'HOBINOM_DIR', 'hobinom');
global $hobinom_db_version;
$hobinom_db_version = ".1";
$plugin = plugin_basename( __FILE__ );

$HNfunctions = new hobinomFuncCollection();

// update check
add_action( 'plugins_loaded', array($HNfunctions,'hobinom_update_db_check') );

// Create pages
add_action( 'admin_menu', array($HNfunctions, 'hobinom_navigation_menu') );  
add_action( 'admin_init', array($HNfunctions,'hobinom_style') );   //css style

add_filter( 'plugin_action_links', array($HNfunctions, 'hobinom_add_settings_link'), 10, 2 );
add_filter( 'rul_before_user', 'login_widget_redirect', 10, 4 );
add_filter( 'page_template', array($HNfunctions, 'hobinom_page_template' ));

register_activation_hook( __FILE__, array($HNfunctions, 'hobinom_install') );
// register_activation_hook( __FILE__, 'hobinom_install_data' );

// runs on plugin deactivation
register_deactivation_hook( __FILE__, array($HNfunctions, 'hobinom_remove') );



class hobinomFuncCollection 
{

	function __construct() 
	{
		// Register style sheet.
		add_action( 'wp_enqueue_scripts', array( $this, 'hobinom_style' ) );
	}
	
	//install database schema for saving data
	function hobinom_install() 
	{
		global $wpdb;
		global $hobinom_db_version;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$table_name = $wpdb->prefix . "hobinom";
			
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id mediumint(10) NOT NULL AUTO_INCREMENT,
		optionname tinytext NOT NULL,
		optionvalue tinytext NOT NULL,
		user_id int(10) NOT NULL,
		UNIQUE KEY id (id)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		
		$wpdb->query($sql);

		add_option( "hobinom_db_version", $hobinom_db_version );
	}
	
	/* register_activation_hook is not called when a plugin is updated, so to run the above code on automatic upgrade you need to check the plugin db version on another hook. like this: */
	function hobinom_update_db_check() 
	{
		global $hobinom_db_version;
		if (get_site_option( 'hobinom_db_version' ) != $hobinom_db_version) 
		{
			$this->hobinom_install();
		}
	}
		
	// Add settings link on plugin page
	function hobinom_add_settings_link($links, $file) 
	{
		static $this_plugin;
	
    if (!$this_plugin) { $this_plugin = plugin_basename(__FILE__); }
 
    // check to make sure we are on the correct plugin
    if ($file == $this_plugin) 
		{
			$settings_link = '<a href="' . get_bloginfo('wpurl') . 
			'/wp-admin/admin.php?page=hobinom/settings.php">Settings</a>';
			
			// add the link to the list
			array_unshift($links, $settings_link);
    }
 
    return $links;
}
 
	function hobinom_navigation_menu() 
	{
		if (current_user_can('manage_options'))  
		{
			//  add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position )
			// add_menu_page(__('Test Toplevel','menu-test'), __('Test ','menu-test'), 'manage_options', 'mt-top-level-handle', 'mt_toplevel_page' );
			
			//  add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position )
			add_menu_page(__('HobiNom','hobinom'), __('HobiNom','hobinom'), 'manage_options', HOBINOM_DIR.'/settings.php', '', plugins_url('hobinom/images/starfish.png') );
			
			// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function)
			add_submenu_page(HOBINOM_DIR.'/settings.php', __('Settings','settings'), __('Settings','settings'), 'manage_options', HOBINOM_DIR.'/settings.php');

			add_submenu_page(HOBINOM_DIR.'/settings.php', __('Search Domains','domain-search'), __('Search Domains','domain-search'), 'manage_options', HOBINOM_DIR.'/include/domain-search.php');
			add_submenu_page(HOBINOM_DIR.'/settings.php', __('List Domains','name-spinner'), __('List Domains','list-domains'), 'manage_options', HOBINOM_DIR.'/include/domain-list.php');
			add_submenu_page(HOBINOM_DIR.'/settings.php', __('Name Spinner','name-spinner'), __('Name Spinner','name-spinner'), 'manage_options', HOBINOM_DIR.'/include/domain-namespinner.php');
			add_submenu_page(HOBINOM_DIR.'/settings.php', __('Purchase','domain-purchase'), __('Purchase','domain-purchase'), 'manage_options', HOBINOM_DIR.'/include/domain-purchase.php');
			//add_submenu_page('hobinom', __('Domain Management','domain-management'), __('Domain Management','domain-management'), 'manage_options', HOBINOM_DIR.'/include/domain-management.php');
		
		}          
	}
	
	function hobinom_style()  
	{  
		// Register the style like this for a plugin:  
		wp_register_style( 'custom-style', plugins_url( '/css/style.css', __FILE__ ), array(), '20130414', 'all' );  
    wp_enqueue_style( 'custom-style' );
	}  
	
	function widget_redirect( $redirect_to, $requested_redirect_to )
	{
		// If the referring page contains "/forums/"...
		if (stripos($_SERVER['HTTP_REFERER'],'/forums/') !== false)
		{
			$referringpage = $_SERVER[HTTP_REFERER];
			return $referringpage;
		}
		//Otherwise, check the referring page contains "/blog/"...
		else if (stripos($_SERVER['HTTP_REFERER'],'/blog/') !== false)
		{
			$referringpage = $_SERVER[HTTP_REFERER];
			return $referringpage;
		}
		// Otherwise, default to the plugin's redirect settings...
		else
		{
			return false;
		}
	}

	function hobinom_remove()
	{
		/* Deletes the database field */
		global $wpdb;
		
		$table_name = $wpdb->prefix . "hobinom";
		$sql = "DROP TABLE IF EXISTS `$table_name`";
		
		$wpdb->query($sql);
	}
	
	function hobinom_page_template( $page_template )
	{
    if ( is_page( 'hobinom' ) ) {
			$page_template = dirname( __FILE__ ) . '/settings.php';
    }
    return $page_template;
	}
}

print_r($page_template);
print_r($wp);


?>