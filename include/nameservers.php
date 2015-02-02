<?php
	if (!current_user_can('manage_options')) {  
		wp_die('You do not have sufficient permissions to access this page.');  
	}  
	require_once('header.php'); 

	?>


<a onclick="clickTabSwitch(1);">Get Nameservers</a>


<!-- LIST DOMAINS -->
<div id="clickTab1">

	<div class="metabox-holder">
		<div class="postbox" style="float:left; width:48%; margin: 0 3px 0 0">
			<h3 class="hndle"><span>Get NameServers<c/span></h3>
			<div class="inside">
				
				<form name="hobinom_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
					<input type="hidden" name="hobinom_hidden" value="Y">  
					<?php _e( "Retrieve the name server settings for a domain name." );  ?>
					<p><br /></p>
					<table><tr><td>
					<?php _e("Domain: " ); ?></td><td><input type="text" name="hobinom_domain" value="<?php echo $hn_domain; ?>" size="20"><?php _e(" ex: domain" ); ?></td></tr>
					<tr><td><?php _e("TLD: "); ?></td><td><input type="text" name="hobinom_tld" value="<?php echo $hn_tld; ?>" size="10"><?php _e("ex: com"); ?></td></tr>
				
					<tr><td></td><td>
					<p class="submit">  
						<input type="submit" name="getdns" value="<?php _e('Search', 'hobinom_trdom' ) ?>" />  
					</p>  
					</td></tr>
					</table>
				</form>  
			</div>
		</div>

		<?php if(isset($_POST['getdns'])) {

			//Form data sent  
			$domain = $_POST['hobinom_domain'];	
			$tld = $_POST['hobinom_tld'];

			//$url = $api_url . '?command=GetDNS&&uid='.$username.'&pw='.$password.'&sld='.$domain.'&tld='.$tld.'&ResponseType=XML';
			$url = $api_url . '?command=GetDNS&uid=resellid&pw=resellpw&sld=resellerdocs&tld=com&ResponseType=XML';
		
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
		?>
			<div class="postbox" style="float:right; width:48%; margin: 0 3px 0 0">
			<h3 class="hndle"><span>Nameservers for: <strong><?php echo $domain . "." . $tld; ?></strong></span></h3>
			<div class="inside">
				<strong>Nameservers:</strong> <?php foreach($xml->dns as $dns) { echo $dns . "<br />"; } ?>
				<strong>Use DNS?</strong> <?php echo $xml->UseDNS; ?><br />
				<strong>NS Status?</strong> <?php echo $xml->NSStatus; ?><br />
				<strong># Of Hosts Limit?</strong> <?php echo $xml->HostsNumLimit; ?><br />
				<strong>Registry Synced?</strong> <?php echo $xml->NameserverRegistrySynced; ?><br />
				<strong>Is Lockable?</strong><?php echo $xml->IsLockable; ?>
			</div>
		</div>
		<?php
			}
		} 
	?>