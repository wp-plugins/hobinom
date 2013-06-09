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
?>
<div class="metabox-holder">
	<div class="postbox" style="float:left; width:45%; margin: 0 3px 0 0">
		<h3 class="hndle"><span>Hobinom Purchase (Simple)</span></h3>
		<div class="inside">
			
			<form name="hobinom_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
				<input type="hidden" name="hobinom_hidden" value="Y">  
				<?php _e( "Purchase domains right from this page." );  ?>
				<p><br /></p>
				<table><tr><td>
				<?php _e("Domain: " ); ?></td><td><input type="text" name="hobinom_domain" value="<?php echo $hn_domain; ?>" size="20"><?php _e(" ex: domain" ); ?></td></tr>
				<tr><td><?php _e("TLD: "); ?></td><td><input type="text" name="hobinom_tld" value="<?php echo $hn_tld; ?>" size="10"><?php _e("ex: com"); ?></td></tr>
				
				<tr><td></td><td>
				<p class="submit">  
					<input type="submit" name="purchase_simple" value="<?php _e('Purchase', 'hobinom_trdom' ) ?>" />  
				</p>  
				</td></tr>
				</table>
			</form>  
		</div>  
	<?php /*
	</div>
	
	<div class="postbox" style="float:left; width:45%">
		<h3 class="hndle"><span>Purchase (Advance)</span></h3>
		<div class="inside">
			<form name="hobinom_retrieve_status" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
				<input type="hidden" name="hobinom_hidden" value="Y">  
					<?php _e( "Purchase a domain with more detailed information." );  ?>
					<p><br /></p>
					<table><tr><td>
					<?php _e("Domain: " ); ?></td><td><input type="text" name="hobinom_domain" value="<?php echo $hn_domain; ?>" size="20"><?php _e(" ex: domain" ); ?></td></tr>
					<tr><td><?php _e("TLD: "); ?></td><td><input type="text" name="hobinom_tld" value="<?php echo $hn_tld; ?>" size="10"><?php _e("ex: com"); ?></td></tr>
					<tr><td></td><td>
					<p class="submit">  
						<input type="submit" name="purchase_advance" value="Purchase w/Advance Details" />  
					</p>  
					</td></tr>
					</table>
			</form>  
		</div>
	</div>
	*/ ?>
	<div class="clear">&nbsp;</div>

<?php
if($_POST['hobinom_hidden'] == 'Y') 
{  
	//Form data sent  
	$domain = $_POST['hobinom_domain'];	
	$tld = $_POST['hobinom_tld'];
	$premium = $_POST['premium'];
	$cira_legal = $_POST['cira_legal'];
	$cira_whois = $_POST['cira_whois'];
	$cira_language = $_POST['cira_language'];
	$cira_agreement_value = $_POST['cira_agreement_value'];
	$cira_agreement_version = $_POST['cira_agreement_version'];
	$usedns = $_POST['usedns'];
	$numyears = $_POST['numyears'];
	$registrant_first_name = $_POST['registrant_first_name'];
	$registrant_last_name = $_POST['registrant_last_name'];
	$registrant_organization = $_POST['registrant_organization'];
	$registrant_address1 = $_POST['registrant_address1'];
	$registrant_city = $_POST['registrant_city'];
	$registrant_country = $_POST['registrant_country'];
	$registrant_postal = $_POST['registrant_postal'];
	$registrant_state = $_POST['registrant_state'];
	$registrant_state_province_choice = $_POST['registrant_state_province_choice'];
	$registrant_email = $_POST['registrant_email'];
	$registrant_phone = $_POST['registrant_phone'];

	if(isset($_POST['purchase_simple']))
	{
  $url =  'https://resellertest.enom.com/interface.asp?command=Purchase&sld='.$domain.'&tld='.$tld.
					'&responsetype=xml&uid='.$username.'&pw='.$password;
	}
	else
	{
		$url = 'http://resellertest.enom.com/interface.asp?command=Purchase&UID='.$username.'&PW='.$password.'&sld='.$domain.'&tld='.$tld.'&ResponseType=XML&cira_legal_type='.$cira_legal.'&cira_whois_display='.$cira_whois.'&cira_language='.$cira_language.
		'&cira_agreement_value='.$cira_agreement_value.'&cira_agreement_version='.$cira_agreement_version.'&UseDNS='.$usedns.'&NumYears='.$numyears.'&RegistrantFirstName='.$registrant_first_name.'&RegistrantLastName='.$registrant_last_name.'&RegistrantOrganizationName='.$registrant_organization.'&RegistrantAddress1='.$registrant_address1.'&RegistrantCity='.$registrant_city.'&RegistrantCountry='.$registrant_country.'&RegistrantPostalCode='.$registrant_postal.'&RegistrantStateProvince='.$registrant_state.'&RegistrantStateProvinceChoice='.$registrate_state_province_choice.'&RegistrantEmailAddress='.$registrant_email.'&RegistrantPhone='.$registrant_phone;
	}
	
	// Load the API results into a SimpleXML object
  $xml = simplexml_load_file($url);
	//echo "<pre>".print_r($xml,true)."</pre>";
	
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
		else
		{
			print "<h3>Results</h3>";
			print "<table width='70%' style='text-align:center;border:1px solid #ccc'>".
						"<tr><td>Order ID:</td><td>$xml->OrderID</td></tr>".
						"<tr><td>Domain name:</td><td>$domain.$tld</td></tr>".
						"<tr><td>Total Charged:</td><td>$xml->TotalCharged</td></tr>".
						"<tr><td>Message:</td><td>$xml->RRPText</td></tr>";
			print "</table>";
		}
	}
} 
?>
</div>