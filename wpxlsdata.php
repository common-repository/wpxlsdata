<?php 
/**
* Plugin Name:Wp Convert Excel Data To Tabel And DB 
* Plugin URI:
* Description: An Plugin to convert Excel files to WordPress database And manage data in your WordPress menu 

* Version: 1.2.0
* Author: behzadrohizadeh@yahoo.com
*Text Domain: wpxlsdata
*Domain Path: /lang 
*
* @package wpxlsdata 
* @category Wordpress
* @author Behzad Rohizadeh
*
*/


/*
*
*check for correct directory
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'wpxlsdata' ) ) :

class wpxlsdata 
{

	function __construct()
	{
	  register_activation_hook( __FILE__,array(&$this, 'wpxlsdata_activate_pliugin' ));   
      add_action('plugins_loaded',array(&$this, 'wpxlsdata_localization_init_textdomain'));
      include_once('inc/menue.php');
      include_once('inc/addmenue.php');
      include_once('inc/shortcode.php');

	}


	function wpxlsdata_localization_init_textdomain()
	  {
	    $path = dirname(plugin_basename( __FILE__ )) . '/lang/';
	    $loaded = load_plugin_textdomain( 'wpxlsdata', false, $path);
	  }

	  function wpxlsdata_activate_pliugin()

	  {

	  	 global $wpdb;
         $table=$wpdb->prefix."wpxlsdata";
		  if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) 
		  {
			$databaser=$wpdb->query("CREATE TABLE `$table` (
				 `id_wpxlsdata` int(255) NOT NULL AUTO_INCREMENT,
				  `title` varchar(255) CHARACTER SET utf8 NOT NULL,
				  `xlsname` varchar(255) CHARACTER SET utf8 NOT NULL,
				  `description` text,
				  `db` varchar(255)  CHARACTER SET utf8 NOT NULL,
				   PRIMARY KEY (`id_wpxlsdata`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8");

		    }

		    $table=$wpdb->prefix."wpxlsdata_meta";
		  if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) 
		  {
			$databaser=$wpdb->query("CREATE TABLE `$table` (
				  `id_meta` int(255) NOT NULL AUTO_INCREMENT,
				  `meta_key` varchar(255) CHARACTER SET utf8 NOT NULL,
				  `meta_value` varchar(255) CHARACTER SET utf8 NOT NULL,
				  `id_wpxlsdata` int(255) NOT NULL,
				  `type_input` varchar(255) CHARACTER SET utf8 DEFAULT 'text',
				  `isunique` tinyint(1) NOT NULL DEFAULT '0',
				  `dbcolumn` varchar(255)  CHARACTER SET utf8 NOT NULL ,
				   PRIMARY KEY (`id_meta`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

		    }



		  $table=$wpdb->prefix."wpxlsdata_shortcode";
		  if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) 
		  {
			$databaser=$wpdb->query("CREATE TABLE `$table` (
				  `id_shortcode` int(255) NOT NULL AUTO_INCREMENT,
				  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				  `id_wpxlsdata` int(255) NOT NULL,
				  `shortcode` text CHARACTER SET utf8,
				   PRIMARY KEY (`id_shortcode`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

		    }



	  }
}

new wpxlsdata();
endif;?>