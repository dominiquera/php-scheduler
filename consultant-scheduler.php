<?php
/*
Plugin Name: Consultant Scheduler
Version: 1.0.1
*/

if ( !function_exists( 'add_action' ) ) {
	exit;
}
include(plugin_dir_path( __FILE__ ) . 'consultant-scheduler.class.php');
include(plugin_dir_path( __FILE__ ) . 'consultant-scheduler.admin.php');
include(plugin_dir_path( __FILE__ ) . 'consultant-scheduler.api.php');

function consultant_scheduler_activate() {

	global $wpdb;
	$res = $wpdb->query(  
		"CREATE TABLE `".$wpdb->prefix."booking` (
			`id` INT NOT NULL AUTO_INCREMENT,
			`consultant_id` INT NULL,
			`booking` DATETIME NULL,
			`booking_user` INT NULL,
			`slot_length` INT NULL,			
			PRIMARY KEY (`id`),
			INDEX `consultant_id` (`consultant_id` ASC));" );	
			
}
register_activation_hook( __FILE__, 'consultant_scheduler_activate' );
