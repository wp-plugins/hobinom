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
				<input type="submit" name="Submit" value="<?php _e('Update Settings', 'hobinom_trdom' ) ?>" />  
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
		<div class="postbox">
			<?php 
			if(!empty($username) && !empty($password)):
				$url = $api_url . '?command=GetCusPreferences&UID='.$username.'&PW='.$password.'&responsetype=xml';		
			
				if($_POST['update_preferences'])
				{				
					//full api url below, need to add several update options (adv?)
					//$url = $api_url . 'command=UPDATECUSPREFERENCES&UID='.$username.'&PW='.$password.'DefPeriod=4&AutoRenew=on&AutoPakRenew=on&RegLock=on&URLForwardingRenew=on&EmailForwardRenew=on&useparentdefault=0&RecordType=A,A,A&address=85.92.87.177,85.92.87.179,85.92.87.180&hostname=@,*,www&ResponseType=XML';
					$url = $api_url . "?command=UPDATECUSPREFERENCES&UID=".$username."&PW=".$password."&DefPeriod=".$_POST['defperiod']."&AutoRenew=".$_POST['choice_autorenew']."&AutoPakRenew=". $_POST['choice_autopakrenew']."&RegLock=".$_POST['choice_reglock']."&IDProtect=".$_POST['choice_idprotect']."&DefIDProtectRenew=".$_POST['defidprotectrenew']."&DefWBLRenew=".$_POST['defwblrenew']."&ResponseType=XML";
				}   
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
					
					$choice_true = "<button type='button' class='btn btn-primary select' value='on' name='".$btn_name."'>True</button><button type='button' class='btn btn-primary unselect' value='off' name='".$btn_name."'>False</button>";
					$choice_false = "<button type='button' class='btn btn-primary unselect' value='on' name='".$btn_name."'>True</button><button type='button' class='btn btn-primary select' value='off' name='".$btn_name."'>False</button>";
				?>
					<h3 class='hndle'>Your Current Account Preferences</h3>
						<div class='inside'>
							<?php echo __("If you are set for demo mode, this will show your DEMO preferences, not your non-demo/main account. If you are using eNom's reseller test account (resellerid/resellertest), then you will see those settings."); ?><br /><br /><br />
							<form name="hobinom_preferences_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>"> 
								<table><th>Preference</th>
											<th>Currently Set To</th><th>Set Preference To</th></tr>
									<tr><td id='info'>Your Account Number</td><td><?php echo $customer->Account; ?></td></tr>
									<tr><td id='info'>Default Renewal Period (max 10 years)</td><td><?php echo $pref->DefPeriod; ?> year(s)</td>
											<td><input name='defperiod' value="" /></tr>
									<tr><td id='info'>If reseller, parent account?</td><td><?php echo $customer->ParentLogin . " (account #: " . $customer->ParentAccount . ")"; ?></td></tr>
									<tr><td id='info'>Service Status</td><td><?php echo $customer->NoService; ?></td></tr>
									<tr><td id='info'>Bulk Registration Limit</td><td><?php echo $customer->BulkRegLimit; ?></td></tr>
									<tr><td id='info'>Signed credit card agreement with eNom?</td><td><?php echo $customer->AcceptTerms; ?></td></tr>
									<tr><td id='info'>Allows non-eNom nameservers</td><td><?php echo $pref->AllowDNS; ?></td></tr>							
									<tr><td id='info'>Domain Lock?</td><td><?php echo $pref->RegLock; ?></td>
											<td>
												<input type="radio" value='on' name='choice_reglock' <?php if ($pref->RegLock == "True"): echo "checked"; endif; ?>>True</button>
												<input type="radio" value='off' name='choice_reglock'<?php if ($pref->RegLock == "False"): echo "checked"; endif; ?>>False</button>											
											</td>
									</tr>
									<tr><td id='info'>Auto Renew POP Paks?</td><td><?php echo $pref->AutoPakRenew; ?></td>
											<td>
												<input type="radio" value='on' name='choice_autopakrenew' <?php if ($pref->AutoPakRenew == "True"): echo "checked"; endif; ?>>True</button>
												<input type="radio" value='off' name='choice_autopakrenew' <?php if ($pref->AutoPakRenew == "False"): echo "checked"; endif; ?>>False</button>										
											</td>
									</tr>
									<tr><td id='info'>Uses eNom's DNS?</td><td><?php echo $pref->UseDNS; ?></td></tr>
									<tr><td id='info'>Uses eNom's DNS by Default?</td><td><?php echo $pref->UseOurDNS; ?></td></tr>
									<tr><td id='info'>Default to eNom's host records?</td><td><?php echo $pref->defaulthostrecordown; ?></td></tr>
									<tr><td id='info'>Auto Renew Domains?</td><td><?php echo $pref->AutoRenew; ?></td>
											<td>
												<input type="radio" value='on' name='choice_autorenew' <?php if ($pref->AutoRenew == "True"): echo "checked"; endif; ?>>True</button>
												<input type="radio" value='off' name='choice_autorenew'<?php if ($pref->AutoRenew == "False"): echo "checked"; endif; ?>>False</button>								
											</td>
									</tr>
									<tr><td id='info'>Renewal Settings</td><td><?php echo $pref->RenewalSetting; ?></td></tr>
									<tr><td id='info'>Renewal BCC</td><td><?php echo $pref->RenewalBCC; ?></td></tr>
									<tr><td id='info'>Renewal URLF orward</td><td><?php echo $pref->RenewalURLForward; ?></td></tr>
									<tr><td id='info'>Renewal EMail Forward</td><td><?php echo $pref->RenewalEmailForward; ?></td></tr>
									<tr><td id='info'>Mail Limit?</td><td><?php echo $pref->MailNumLimit; ?></td></tr>
									<tr><td id='info'>ID Protection?</td><td><?php echo $pref->IDProtect; ?></td>
											<td>
												<input type="radio" value='on' name='choice_idprotect' <?php if ($pref->IDProtect == "True"): echo "checked"; endif; ?>>True</button>
												<input type="radio" value='off' name='choice_idprotect'<?php if ($pref->IDProtect == "False"): echo "checked"; endif; ?>>False</button>	
												(Note: Purchases ID Protection for all eligible domains.)
												</div>
											</td> 
									</tr>
									<tr><td id='info'>Auto-renew ID Protection before expiration?</td><td></td>
											<td>
												<input type="radio" value='on' name='choice_defidprotectrenew' <?php if ($pref->DefIDProtectRenew == "True"): echo "checked"; endif; ?>>True</button>
												<input type="radio" value='off' name='choice_defidprotectrenew'<?php if ($pref->DefIDProtectRenew == "False"): echo "checked"; endif; ?>>False</button>											
											</td>
									</tr>
									<tr><td id='info'>Auto-renew Business Listing before expiration?</td><td></td>
											<td>
												<input type="radio" value='on' name='choice_defwblrenew' <?php if ($pref->DefWBLRenew == "True"): echo "checked"; endif; ?>>True</button>
												<input type="radio" value='off' name='choice_defwblrenew'<?php if ($pref->DefWBLRenew == "False"): echo "checked"; endif; ?>>False</button>											
											</td>
									</tr>
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
									</td>
								</tr>
								<tr><td></td><td><input type='submit' name='update_preferences' value='Update Account' /></td></tr>
						</table>
						</form>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php endif; ?>
	</div>
	<div class="clear:both;">&nbsp;</div>
</div>