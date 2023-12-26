<?php
/**
 * @package  Email Posts Information
Plugin Name: Email Posts Information
Plugin URI: http://www.finaldatasolutions.com/
Description: This plugin simply email the posts information to an email may be admin user or someone else.
Version: 1.0.1
Author: Ibrar Ayoub
Author URI: http://www.finaldatasolutions.com/
License: GPLv2 or later
*/
require 'plugin-update-checker-master/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/manager-wiseTech/email-post-info/',
	__FILE__,
	'email-post-information'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('main');

//Optional: If you're using a private repository, specify the access token like this:
$myUpdateChecker->setAuthentication('your-token-here');




add_action("admin_menu","email_post_info_menu");
function email_post_info_menu(){
  add_menu_page( "Mail Post Information", "EPI Settings", 'manage_options', 'email-post-information', 'email_post_info_cb_fn', 'dashicons-email-alt');
  add_submenu_page('email-post-information', "Mail Post Information", "EPI Settings", 'manage_options', 'email-post-information', 'email_post_info_cb_fn');
  add_submenu_page('email-post-information', "Mail Post Information", "Instant Report", 'manage_options', 'send-instant-email', 'send_instant_email_cb_fn');
}
function email_post_info_cb_fn(){
	?>
	<div class="wrap">
    <h2>Email Posts Information Settings</h2>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
   <?php
    settings_fields( 'epi-plugin-settings' );
    do_settings_sections( 'epi-plugin-settings' );
    submit_button();
    ?>
</div>
    <?php
}

function send_instant_email_cb_fn(){
	?>
		<div>
			<form method="post" action="">
				<h3>Send Instant Email Report to Set Email Address</h3>
				<button name="send_instant_report" class="button button-primary">Send Instant Report</button>
			</form>
		</div>
	<?php
		if (isset($_POST['send_instant_report'])) {
			mail_site_posts_information();
		}
}

function reminder_plugin_settings_init(){
  include_once plugin_dir_path(__FILE__).'settings/email-post-info-settings.php'; 
}
add_action('admin_init', 'reminder_plugin_settings_init');


register_activation_hook(__FILE__,'activate_my_cron_job');
function activate_my_cron_job(){
	  if (! wp_next_scheduled ( 'mail_post_info_hook' )) {
    	wp_schedule_event(time(), 'everyminute', 'mail_post_info_hook');
    }
}

add_action('mail_post_info_hook','mail_site_posts_information');
function mail_site_posts_information(){
	//$count_posts = wp_count_posts($type = 'post');
 	//$count_posts = get_total_post();
 	$headers = "";
	$data = "";
	$data .= get_total_post();

	$data .= "Post created yesterday: ".get_posts_edited_created_yesterday('post_date')."<br>";
	$data .= "Posts Edited Yesterday ".get_posts_edited_created_yesterday('post_modified')."<br>";
	$data .= "Last 7 days posts: ".get_posts_created_this_week()."<br>";
	$data .= "Last 40 posts: ".get_last_40_posts()."<br>";
	$subject = get_site_url()." ".date('d/m/Y');
	$mai_add = get_option('epi_plugin_email_txtbox');
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	if (empty($mai_add)) {
		$mai_add = get_option('admin_email');
		
	}
	
	$mail_sent = wp_mail($mai_add, $subject, $data,$headers);
	//echo $data;
	if ($mail_sent) {
            echo '<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
				<p><strong>Email Sent Successfully.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
    
    } else {
       echo '<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
				<p><strong>Error Sending Email.</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
    }


}
function get_total_post(){
	$all_post_type = get_option('epi_plugin_posttype_checkboxes');
	$total_posts = "";
	$publish_posts = $future_posts = $trash_posts = 0;
	if (is_array($all_post_type)) {
		foreach ($all_post_type as $post_type) {
		  $publish_posts += (int) wp_count_posts($post_type)->publish;
		  $future_posts += (int) wp_count_posts($post_type)->future;
		  $trash_posts += (int) wp_count_posts($post_type)->trash;
		}
	}
	$total_posts .= "All Published Posts: ".$publish_posts."<br>";
	$total_posts .= "All Scheduled Posts: ".$future_posts."<br>";
	$total_posts .= "All Trashed Posts: ".$trash_posts."<br>";
	return $total_posts;
}

function get_posts_edited_created_yesterday($param){
	$all_post_type = get_option('epi_plugin_posttype_checkboxes');
	$post_type_query = "";
	if (is_array($all_post_type)) {
		$count = count($all_post_type);
		foreach ($all_post_type as $key) {
			
			if ($count>1) {
				$post_type_query .= "post_type = '".$key."' OR ";	
			}
			else{
				$post_type_query .= "post_type = '".$key."'";
			}

			$count--;
			
		}
	}
	$yesterday = date('Y-m-d',strtotime("-1 days"));
	$yesterday = "'".$yesterday."'";
	global $wpdb;
	$table_name = $wpdb->prefix."posts";
	$post_id = $wpdb->get_results("SELECT ID FROM $table_name WHERE DATE($param) =  $yesterday AND ($post_type_query) AND post_status = 'publish'");
	$count = 0;
	if ($post_id) {
			foreach ($post_id as $post) {
		$count++;
		}
	}

	return $count;

}

function get_posts_created_this_week(){
	
	$all_post_type = get_option('epi_plugin_posttype_checkboxes');
	if (!is_array($all_post_type)) {
		$all_post_type = "post";
	}
$args = array(
	'post_type' => $all_post_type,
    'date_query' => array(
        array(
            'year' => date( 'Y' ),
            'week' => date( 'W' ),
        ),
    ),
);
	$the_query = new WP_Query( $args );
 $count = 0;
// The Loop
if ( $the_query->have_posts() ) {
  
    while ( $the_query->have_posts() ) {
        $the_query->the_post();
        $count++;
    }
  
} 

/* Restore original Post Data */
wp_reset_postdata();
return $count;
}
function get_last_40_posts(){
	$all_post_type = get_option('epi_plugin_posttype_checkboxes');
	if (!is_array($all_post_type)) {
		$all_post_type = "post";
	}
	$posts = get_posts(
						 array(
						  'numberposts' => 40,
						  'order_by' => 'DESC',
						  'post_type' => $all_post_type,
						 )
					);

	$count = 0;
	$data = "<table border='1'>";
	$data .= "<tr><th>Post ID</th><th>Post Title</th><th>Publish Date</th></tr>";
	if ($posts) {
		foreach ($posts as $post) {
		$data .= "<tr><td>".$post->ID."</td><td>".$post->post_title."</td><td>".date("d-m-Y", strtotime($post->post_date))."</td></tr>";
		}
	}
	$data .= "</table>";

	return $data;
}
?>
