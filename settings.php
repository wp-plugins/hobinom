<?php 
	if (!current_user_can('manage_options')) {  
		wp_die('You do not have sufficient permissions to access this page.');  
	}  
	require_once('include/functions.php'); 
	require_once('include/header.php');
	
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
<div class="clear">&nbsp;</div>
</div>

<div class="clear">&nbsp;</div>
<div class="metabox-holder">
	
	<!-- Getting Started box -->
	<div class="postbox" style="">
		<h3 class="hndle"><span>Account Preferences</span></h3>
		<div class="inside">
			<?php echo __("This can only be used once you've set your settings / set up your eNom Interface! NOTE: if you are set for demo mode, this will show your DEMO preferences, not your non-demo/main account."); ?>
			<br />
			<form name="hobinom_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
				<input type="submit" name="get_preferences" value="<?php _e('Get Preferences', 'hobinomnom_trdom' ) ?>" />  
				<input type="submit" name="update_preferences" value="<?php _e('Update Account', 'hobinomnom_trdom' ) ?>" />  
			</form>  
		</div>  
	</div>

	
	<?php if(isset($_POST['get_preferences'])): ?>
				<!-- Getting Started box -->
		<div class="postbox">
			
		<?php
			if($_POST['get_preferences'])
				$url = $api_url . '?command=GetCusPreferences&UID='.$username.'&PW='.$password.'&responsetype=xml';
		
			// Load the API results into a SimpleXML object
			$xml = simplexml_load_file($url);
			
			if(isset($xml->errors)) 
			{
				// echo all errors
				function recursive_print($item, $key)
				{
					echo '<div class="updated"><p><strong>'.$item.'</strong></p></div>';
				}
				array_walk_recursive($xml->errors, 'recursive_print');
			}
			else
			{
				$pref = $xml->CustomerPrefs;
				$host = $xml->CustomerPrefs->defaulthostrecords->hostrecord;
				$customer = $xml->CustomerInformation;
				$nameservers = $xml->CustomerPrefs->NameServers;
			?>
				<h3 class='hndle'>Your Current Account Preferences</h3>
					<div class='inside'>
						<table><th>Preference</th>
									<th>Currently Set To</th></tr>
							<tr><td id='info'>Your Account Number</td><td><?php echo $customer->Account; ?></td></tr>
							<tr><td id='info'>Default Renewal Period</td><td><?php echo $pref->DefPeriod; ?> year(s)</td></tr>
							<tr><td id='info'>If reseller, parent account?</td><td><?php echo $customer->ParentLogin . " (account #: " . $customer->ParentAccount . ")"; ?></td></tr>
							<tr><td id='info'>Service Status</td><td><?php echo $customer->NoService; ?></td></tr>
							<tr><td id='info'>Bulk Registration Limit</td><td><?php echo $customer->BulkRegLimit; ?></td></tr>
							<tr><td id='info'>Signed credit card agreement with eNom?</td><td><?php echo $customer->AcceptTerms; ?></td></tr>
							<tr><td id='info'>Allows non-eNom nameservers</td><td><?php echo $pref->AllowDNS; ?></td></tr>
							<tr><td id='info'>Show Popup Menus</td><td><?php echo $pref->ShowPopups; ?></td></tr>
							<tr><td id='info'>Domain Lock?</td><td><?php echo $pref->RegLock; ?></td></tr>
							<tr><td id='info'>Auto Renew POP Paks?</td><td><?php echo $pref->AutoPakRenew; ?></td></tr>
							<tr><td id='info'>Uses eNom's DNS?</td><td><?php echo $pref->UseDNS; ?></td></tr>
							<tr><td id='info'>Uses eNom's DNS by Default?</td><td><?php echo $pref->UseOurDNS; ?></td></tr>
							<tr><td id='info'>Default to eNom's hostrecords?</td><td><?php echo $pref->defaulthostrecordown; ?></td></tr>
							<tr><td id='info'>Auto Renew Domains?</td><td><?php echo $pref->AutoRenew; ?></td></tr>
							<tr><td id='info'>Renewal Settings</td><td><?php echo $pref->RenewalSetting; ?></td></tr>
							<tr><td id='info'>Renewal BCC</td><td><?php echo $pref->RenewalBCC; ?></td></tr>
							<tr><td id='info'>Renewal URLF orward</td><td><?php echo $pref->RenewalURLForward; ?></td></tr>
							<tr><td id='info'>Renewal EMail Forward</td><td><?php echo $pref->RenewalEmailForward; ?></td></tr>
							<tr><td id='info'>Mail Limit?</td><td><?php echo $pref->MailNumLimit; ?></td></tr>
							<tr><td id='info'>ID Protection?</td><td><?php echo $pref->IDProtect; ?></td></tr>
							<tr><td id='info'>NameJet Sales?</td><td><?php echo $pref->NameJetSales; ?></td></tr>
							<tr><td id='info'>HostName</td>
									<td>
									<table>
										<?php for($i=0;$i<count($host); $i++) {
											echo "<tr><td id='info'>Hostname: </td><td> ".$host[$i]->attributes()->hostname ."</td></tr>";
											echo "<tr><td id='info'>Address: </td><td> ".$host[$i]->attributes()->address."</td></tr>";
											echo "<tr><td id='info'>Record Type: </td><td> " .$host[$i]->attributes()->recordtype."</td></tr>";
										}?>
										</table>
									</td>
							</tr>
						<tr><td id='info'>Nameservers</td>
							<td><?php echo $pref->NameServers->DNS1 . "<br />" .
														$pref->NameServers->DNS2 . "<br />" .
														$pref->NameServers->DNS3 . "<br />" .
														$pref->NameServers->DNS4 . "<br />" .
														$pref->NameServers->DNS5 . "<br />"; ?>
						</tr>
											
				<?php
				echo "</table></div>";
				
			
			}
		
		?>
				</div>
			</div>
		<?php endif; ?>
		</div>

	</div>
	<div class="clear:both;">&nbsp;</div>
</div>