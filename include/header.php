<?php
	require_once(dirname(__FILE__).'/functions.php'); 
	$hobinom = new hobinom_db(); 
	
	$current_user_id = get_current_user_id();
	$details = $hobinom->get_enom_details($current_user_id);
	
	$username = $details['username'];  
	$password = $details['password']; 
	$demo_mode = $details['demo_mode'];
	
	// get correct url, depending on demo/live mode
	$api_url = $hobinom->is_demo($demo_mode);
?>