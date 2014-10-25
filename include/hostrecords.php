<?php
	if (!current_user_can('manage_options')) {  
		wp_die('You do not have sufficient permissions to access this page.');  
	}  
	require_once('header.php'); 
?>
</div>
<div class="metabox-holder">
	<div class="postbox" style="float:left; width:45%; margin: 0 3px 0 0">
		<h3 class="hndle"><span>Get Domain HostRecords</span></h3>
		<div class="inside">
			
		<form name="hobinom_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
				<input type="hidden" name="hobinom_hidden" value="Y">  
				<?php _e( "Get a domain's host records that <strong>you own</strong>. Make sure to have <a href='admin.php?page=hobinom/settings.php'>your settings set</a> to use the API." );  ?>
				<p><br /></p>
				<table><tr><td>
				<?php _e("Domain: " ); ?></td><td><input type="text" name="hobinom_domain" value="<?php echo $hn_domain; ?>" size="20"><?php _e(" ex: domain" ); ?></td></tr>
				<tr><td><?php _e("TLD: "); ?></td><td><input type="text" name="hobinom_tld" value="<?php echo $hn_tld; ?>" size="10"><?php _e("ex: com"); ?></td></tr>
			
				<tr><td></td><td>
				<p class="submit">  
					<input type="submit" name="get_hostrecords" value="<?php _e('Search', 'hobinom_trdom' ) ?>" />  
				</p>  
				</td></tr>
				</table>
		</form>  
	</div>  
	
	</div>
	
	<div class="postbox" style="float:left; width:45%">
		<h3 class="hndle"><span>Host Records for: <?php echo $_POST['hobinom_domain'] . "." . $_POST['hobinom_tld']; ?></span></h3>
		<div class="inside">
		<?php 

		//Form data sent  
		$domain = $_POST['hobinom_domain'];	
		$tld = $_POST['hobinom_tld'];

		
		if(isset($_POST['get_hostrecords']))
		{
			$url = $api_url . '?command=gethosts&uid='.$username.'&pw='.$password.'&sld='.$domain.'&tld='.$tld.'&responsetype=xml';
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
		
			$name = $xml->host->name;
			$type = $xml->host->type;
			$address = $xml->host->address;
			$hostid = $xml->host->hostid;

			$emailforwarding = $xml->DomainServices->EmailForwarding;
			$hostrecords = $xml->DomainServices->HostRecords;
			
			$server = $xml->Server;
			$site = $xml->Site;
			$islockable = $xml->IsLockable;
			
			$table = 
			"<table>
				<tr><td id='info'>Name:</td><td>$name</td></tr>
				<tr><td id='info'>Type:</td><td>$type</td></tr>
				<tr><td id='info'>Address:</td><td>$address</td></tr>
				<tr><td id='info'>Host ID:</td><td>$hostid</td></tr>
				<tr><td id='info'>Email Forwarding:</td><td>$emailforwarding</td></tr>
				<tr><td id='info'>Host Records</td><td>$hostrecords</td></tr>
				<tr><td id='info'>Server:</td><td>$server</td></tr>
				<tr><td id='info'>Site: </td><td>$site</td></tr>
				<tr><td id='info'>Is Lockable?</td><td>$islockable</td></tr>
				</table>
				";
				
				echo $table;
		}
	
	?>
		</div>
	</div>