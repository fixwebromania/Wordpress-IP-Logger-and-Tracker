<?php
/*
Plugin Name: IP Logger and Tracker
Plugin URI: https://fixweb.ro/
Description: A wordpress plugin that log and track every visitator on site
*/

define("TITLE","IP Logger and Tracker");
define("TEMPLATE_DIR","template");

function request($url)
{
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	curl_setopt($ch, CURLOPT_URL,$url);
	
	$response=curl_exec($ch);
	
	curl_close($ch);
	
	if(!$response)
	{
		throw new \Exception(curl_error($ch));	
	}

	return $response;
}
function activate() 
{
	global $wpdb;

	$sql ='CREATE TABLE IF NOT EXISTS wp_log_visit ( ';
	$sql.='`id` INT NOT NULL AUTO_INCREMENT, ';
	$sql.='`date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ';
	$sql.='`ip` VARCHAR(64) NOT NULL, ';
	$sql.='`path` TEXT NOT NULL, ';
	$sql.='`location_data`JSON NULL, ';
	$sql.='PRIMARY KEY (`id`)';
	$sql.=') ENGINE = InnoDB;';
  
	$result=$wpdb->query($sql);
	
	if(!$result)
	{
		throw new \Exception($wpdb->last_error);
	}
}

register_activation_hook( __FILE__, 'activate' );

function logVisit()
{
	global $wpdb;
	
	if(!preg_match("/(\/wp-admin\/|wp-cron)/i",$_SERVER['REQUEST_URI']))
	{
		$result=$wpdb->insert( 'wp_log_visit', array( 'ip' =>$_SERVER['REMOTE_ADDR'], 'path' => $_SERVER['REQUEST_URI'] ) );
		
		if(!$result)
		{
			throw new \Exception($wpdb->last_error);
		}
	}
}

add_action( 'init', 'logVisit');

function addMenu()
 {
    add_menu_page(TITLE,TITLE,"edit_posts","ip-logger-tracker", "index", null,99);	
}

add_action("admin_menu", "addMenu");

function getiplist()
{
	ini_set('max_execution_time', '0');

	global $wpdb;

	$filters=isset($_GET['filters']) ? urldecode($_GET['filters']) : '';
	
	$filters=json_decode(stripslashes_deep($filters),true) ?? [];

	trackIp();

	$sql="SELECT * FROM wp_log_visit WHERE 1=1 ";

	foreach($filters as $filter_name => $filter_value)
	{
		if(!empty($filter_value) && preg_match("/^filter/",$filter_name))
		{
			$sql.=" AND LOWER(".str_ireplace("filter_","",esc_sql($filter_name)).") LIKE '%".strtolower(esc_sql($filter_value))."%' ";
		}
	}
	
	if($filters['exclude_my_ip'])
	{
		$sql.=" AND ip!='".$_SERVER['REMOTE_ADDR']."' ";
	}

	if(!empty($filters['exclude_ip']))
	{
		$sql.=" AND ip!='".esc_sql($filters['exclude_ip'])."' ";
	}

	$sql.="ORDER BY date DESC";

	$rows=$wpdb->get_results($sql);

	print wp_send_json(["rows"=>$rows]);

	die();
}

add_action('wp_ajax_getiplist', 'getiplist');

function trackIp()
{
	global $wpdb;

	$rows=$wpdb->get_results("SELECT DISTINCT ip FROM wp_log_visit WHERE location_data IS NULL;");

	foreach($rows as $row)
	{
		$data=request("http://ip-api.com/json/".$row->ip);
		
		$wpdb->update("wp_log_visit", ["location_data"=>$data], ["ip" =>$row->ip]);
	}
}

function index()
{
	$nonce = wp_create_nonce("my_user_like_nonce");

	$api_link = admin_url('admin-ajax.php?action=getiplist&nonce='.$nonce);

	include(TEMPLATE_DIR."/index.php");
}
?>