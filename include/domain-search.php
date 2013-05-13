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
	
	<!-- Getting Started box -->
	<div class="postbox">
		<h3 class="hndle"><span>Hobinom Domain Search</span></h3>
		<div class="inside">
			
		<form name="hobinom_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
				<input type="hidden" name="hobinom_hidden" value="Y">  
				<?php _e( "Search for available domains in eNom. Make sure to have <a href='admin.php?page=hobinom/hobinom-settings.php'>your settings set</a> to use the API." );  ?>
				<p><br /></p>
				<table><tr><td>
				<?php _e("Domain: " ); ?></td><td><input type="text" name="hobinom_domain" value="<?php echo $hn_domain; ?>" size="20"><?php _e(" ex: domain" ); ?></td></tr>
				<tr><td><?php _e("TLD: "); ?></td><td><input type="text" name="hobinom_tld" value="<?php echo $hn_tld; ?>" size="10"><?php _e("ex: com"); ?></td></tr>
			
				<tr><td></td><td>
				<p class="submit">  
					<input type="submit" name="Submit" value="<?php _e('Search', 'hobinom_trdom' ) ?>" />  
				</p>  
				</td></tr>
				</table>
		</form>  
</div>  

<?php
if($_POST['hobinom_hidden'] == 'Y') 
{  
	//Form data sent  
	$domain = $_POST['hobinom_domain'];	
	$tld = $_POST['hobinom_tld'];

	 // URL for API request
  $url =  'https://resellertest.enom.com/interface.asp?command=check&sld='.$domain.'&tld='.$tld.
					'&responsetype=xml&uid='.$username.'&pw='.$password;
	
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
?>
</div>
</div>
