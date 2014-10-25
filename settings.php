<?php 
	if (!current_user_can('manage_options')) {  
		wp_die('You do not have sufficient permissions to access this page.');  
	}  
	require_once('include/functions.php'); 
	$hobinom = new hobinom_db(); 
	
	$current_user_id = get_current_user_id();
	$details = $hobinom->get_enom_details($current_user_id);
	
	$username = $details['username'];  
	$password = $details['password']; 
	$demo_mode = $details['demo_mode'];
	
	$checked = (empty($demo_mode)) ? NULL : "checked";
?>
<div class="metabox-holder">
	
	<!-- Getting Started box -->
	<div class="postbox" style="width:48%;float:left">
		<h3 class="hndle"><span>Hobinom Settings</span></h3>
		<div class="inside">
			<form name="hobinom_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
			<input type="hidden" name="hobinom_hidden" value="Y">  
			<?php    echo __( "These are your eNom settings - your username and password is required to use the API. You MUST have an eNom account." ); ?>  
			<p><?php _e("Username: " ); ?><input type="text" name="hobinom_username" value="<?php echo $username; ?>" size="20">
				<?php _e(" ex: localhost" ); ?></p>  
			<p><?php _e("Password: " ); ?><input type="password" name="hobinom_password" value="<?php echo $password; ?>" size="20">
				<?php _e(" ex: secretpassword" ); ?></p>  
			<p><?php _e("Demo mode? "); ?><input type="checkbox" name="hobinom_demo" value="1" <?php echo $checked; ?>>
				<br />If checked, you will be accessing the reseller test API - no charges/changes will be made to your live eNom account.
			</p>
			<p class="submit">  
				<input type="submit" name="Submit" value="<?php _e('Update Options', 'hobinom_trdom' ) ?>" />  
			</p>  
		</form>  

		<?php
		if(($_POST['hobinom_hidden'] == 'Y') && is_user_logged_in())
		{ 	
			$username = $_POST['hobinom_username'];  	
			$password = $_POST['hobinom_password'];  
			$demo_mode = $_POST['hobinom_demo'];
			
			// encode and serialize for extra protection
			$settings_orig = base64_encode(serialize(array("user_id" => $current_user_id, "username" => "$username", "password" => "$password", "demo_mode" => "$demo_mode")));
			
			// check to make sure username doesn't exist; if does, update or else insert	
			(!$hobinom->get_id_settings($current_user_id)) ? 
				$hobinom->set_insert('settings', $settings_orig, $current_user_id) :
				$hobinom->set_update('settings', $settings_orig, $current_user_id);

			// redirect
			$redirect_to = $_SERVER['REQUEST_URI'];
			wp_safe_redirect( $redirect_to );
		} 
		?>
		</div>
	</div>

	<!-- Getting Started box -->
	<div class="postbox" style="width:48%; float:right">
		<h3 class="hndle">Setting Up Your eNom Live Interface</h3>
		<div class="inside">
		<div class="wrap">

		<p>To activate your API and add/change your IP in the live environment, please complete the following steps:</p>

    <ol><li>Visit eNom Help Center.</li>
    <li>Click "Launch the Support Center" button and submit a new ticket.</li>
    <li>Enter the following information:<br />
			Subject: "Add IP" or "Change IP"<br />
			Question: Type the IP address(es) you wish to add or change<br />
			Category: API
		</li>
		</ol>
	Note: this plugin CANNOT do this automatically. You MUST contact eNom with your account and follow the steps above to use the plugin.
	</div>
</div></div>
</div>