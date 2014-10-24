<?php
	if (!current_user_can('manage_options')) {  
		wp_die('You do not have sufficient permissions to access this page.');  
	}  
	require_once('functions.php'); 
	$hobinom = new hobinom_db(); 
	
	$current_user_id = get_current_user_id();
	$details = $hobinom->get_enom_details($current_user_id);
	
	$username = $details['username'];  
	$password = $details['password']; 

	if($_POST['hobinom_hidden'] == 'Y') 
	{  
		//Form data sent  
		$domain = $_POST['hobinom_domain'];	
		$tld = $_POST['hobinom_tld'];

		
		if(isset($_POST['search_domain']))
		{
			$url =  'https://resellertest.enom.com/interface.asp?command=check&sld='.$domain.'&tld='.$tld.'&responsetype=xml&uid='.$username.'&pw='.$password;
		}
		else
		{
			$url = 'https://resellertest.enom.com/interface.asp?command=GetDomainInfo&uid='.$username.'&pw='.$password.'&sld='.$domain.'&tld='.$tld.'&ResponseType=XML';
		}
		
		// Load the API results into a SimpleXML object
		$xml = simplexml_load_file($url);
		
		if(isset($xml->errors)) 
		{
			// print all errors
			function recursive_print($item, $key)
			{
				echo '<div class="updated"><p><strong>'.$item.'</strong></p></div>';
			}
			array_walk_recursive($xml->errors, 'recursive_print');
		}
		else
		{
			if(isset($_POST['search_domain']))
			{
				// Read the results
				$rrpCode = $xml->RRPCode;
				$rrpText = $xml->RRPText;
				
				// Perform actions based on results
				switch ($rrpCode) 
				{
					case 210:
						echo '<div class="updated"><p><strong>Domain ('. $xml->DomainName.') available</strong></p></div>';
						break;
					case 211:
						echo '<div class="updated"><p><strong>Domain ('.$xml->DomainName.') not available (code: '.$rrpCode.')</strong></p></div>';
						break;
					default:
						echo '<div class="updated"><p><strong>Code: '. $rrpCode . ' ' . $rrpText . '</strong></p></div>';
						break;
				}
			} 
		}
	} 
	?>
<div class="metabox-holder">
	<div class="postbox" style="float:left; width:45%; margin: 0 3px 0 0">
		<h3 class="hndle"><span>Hobinom Domain Search</span></h3>
		<div class="inside">
			
		<form name="hobinom_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
				<input type="hidden" name="hobinom_hidden" value="Y">  
				<?php _e( "Search for available domains in eNom. Make sure to have <a href='admin.php?page=hobinom/settings.php'>your settings set</a> to use the API." );  ?>
				<p><br /></p>
				<table><tr><td>
				<?php _e("Domain: " ); ?></td><td><input type="text" name="hobinom_domain" value="<?php echo $hn_domain; ?>" size="20"><?php _e(" ex: domain" ); ?></td></tr>
				<tr><td><?php _e("TLD: "); ?></td><td><input type="text" name="hobinom_tld" value="<?php echo $hn_tld; ?>" size="10"><?php _e("ex: com"); ?></td></tr>
			
				<tr><td></td><td>
				<p class="submit">  
					<input type="submit" name="search_domain" value="<?php _e('Search', 'hobinom_trdom' ) ?>" />  
				</p>  
				</td></tr>
				</table>
		</form>  
	</div>  
	
	</div>
	
	<div class="postbox" style="float:left; width:45%">
		<h3 class="hndle"><span>Retrieve Status Information</span></h3>
		<div class="inside">
		<form name="hobinom_retrieve_status" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
			<input type="hidden" name="hobinom_hidden" value="Y">  
				<?php _e( "Retrieve more detailed information on a single domain name that you or your resellers own (current status and settings, etc)." );  ?>
				<p><br /></p>
				<table><tr><td>
				<?php _e("Domain: " ); ?></td><td><input type="text" name="hobinom_domain" value="<?php echo $hn_domain; ?>" size="20"><?php _e(" ex: domain" ); ?></td></tr>
				<tr><td><?php _e("TLD: "); ?></td><td><input type="text" name="hobinom_tld" value="<?php echo $hn_tld; ?>" size="10"><?php _e("ex: com"); ?></td></tr>
			
				<tr><td></td><td>
				<p class="submit">  
					<input type="submit" name="retrieve_status" value="Retrieve Status" />  
				</p>  
				</td></tr>
				</table>
		</form>  
		</div>
	</div>
</div>

<?php if(isset($_POST['retrieve_status'])): ?>
<div class="metabox-holder" style="clear:both;width:50%;margin:0 auto;text-align:center">
	<div class="postbox">
		<h3 class="hndle"><span>Status Results for <?php echo $_POST['hobinom_domain'].".".$_POST['hobinom_tld']; ?></span></h3>
		<div class="inside">
		<?php
		
			//echo "<pre>".print_r($xml->GetDomainInfo,true)."</pre>";
			$info = $xml->GetDomainInfo;
			$status = $xml->GetDomainInfo->status;
			$wbl = $xml->GetDomainInfo->services->entry[5]->wbl;

			echo "<table style='text-align:center;width:100%'>"
			."<tr><td id='info'>Domain name:</td><td>$info->domainname</td> 
						<td id='info'>Expiration:</td><td>$status->expiration</td></tr>"
			."<tr><td id='info'>Restorable?</td><td>$status->restorable</td>
						<td id='info'>Registrar</td><td>$status->registrar</td></tr>"
			."<tr><td id='info'>Belongs To:</td><td>$status->belongs-to</td>
						<td id='info'>Parking Enabled?</td><td>$info->ParkingEnabled</td></tr>"
			."<tr><td></td></tr>"
			."<tr><td id='info'>Company Name</td><td>$wbl->companyname</td>
						<td id='info'>Street</td><td>$wbl->street</td></tr>"
			."<tr><td id='info'>City</td><td>$wbl->city</td>
						<td id='info'>State</td><td>$wbl->stateprovince</td></tr>"
			."<tr><td id='info'>Postal</td><td>$wbl->postalcode</td>
						<td id='info'>Country</td><td>$wbl->country</td></tr>"
			."<tr><td></td></tr>"
			."<tr><td id='info'>Parking Enabled?</td><td>$info->ParkingEnabled</td>
						<td id='info'>DNS</td><td>";
			foreach($info->services->entry->configuration->dns as $dns)
			{
				echo $dns."<br />";
			}
			echo "</td></tr>";
			
			echo "</table>";
			
			?>
		</div>
	</div>
</div>
<?php endif; ?>