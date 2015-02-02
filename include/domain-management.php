<?php
	if (!current_user_can('manage_options')) {  
		wp_die('You do not have sufficient permissions to access this page.');  
	}  
	require_once('header.php'); 
?>

<a onclick="clickTabSwitch(1);">List Domains</a> <?php /* | <a onclick="clickTabSwitch(2);">Set Preferences</a> */ ?>


<!-- LIST DOMAINS -->
<div id="clickTab1">

	<div class="metabox-holder">
		
		<!-- Getting Started box -->
		<div class="postbox">
			<h3 class="hndle"><span>Hobinom List Domains</span></h3>
			<div class="inside">
				
			<form name="hobinom_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
				<input type="hidden" name="hobinom_hidden" value="Y">  
				Displaying a list of domains in a customer's account has several uses:

				<ul id="hobinom"><li>It displays an inventory of the names in their account.</li>
				<li>It allows you to display data such as expiration dates and settings so that your customers can easily maintain their domain name portfolios.</li>
				<li>It gives you an overview of an individual customer's portfolio so you can guide them toward the products and services that best fit their needs.</li></ul>
					<p><br /></p>
					<table>
					<tr>
					<td><input type="submit" name="nooptionsdomains" value="List All Domains (No Options)" /></td>
					<td><input type="submit" name="optionsdomains" value="List All Domains (With Options)" /><input type="submit" name="expireddomains" value="List Expired Domains" /></td>  				
					</tr>
					<tr><td><strong>Optional</strong></td></tr>
					<tr><td>Tab</td><td>
						<select name="hobinom_tab">
							<option value="IOwn">IOwn</option>
							<option value="Sub_IOwn">Sub_IOwn</option>
							<option value="WatchList">Watchlist</option>
							<option value="IHost">IHost</option>
							<option value="ExpiringNames">Expiring Names</option>
							<option value="ExpiredDomains">Expired Domains</option>
							<option value="RGP">RGP</option>
							<option value="Promotion">Promotion</option>
						</select>The types of names to return.</td></tr>
					<tr><td>Days to Expired</td><td>
						<input type="text" name="hobinom_daystoexpired" maxlength="4" size="4" value="<?php echo $hn_daystoexpired; ?>"> Return names that expire within this number of days, whether they are set to auto-renew or not. Optional with Tab=ExpiringNames.</td></tr>
					<tr><td>Registration Status</td><td>
						<select name="hobinom_regstatus">
							<option value="Registered">Registered</option>
							<option value="Expired">Expired</option>
						</select> The type of domains to return for a subaccount. Optional with Tab=Sub_IOwn.</td></tr>
						<tr><td>Display</td><td><input type="text" name="hobinom_display" maxlength="3" size="4" value="<?php echo $hn_display; ?>">Number of domains to return in one response. Permitted values are 0 to 100.</td></tr>
						<tr><td>Start</td><td><input type="text" name="hobinom_start" maxlength="3" size="4" value="<?php echo $hn_start; ?>">Return names that start with this number in the sorted list.<br />For example, Display=25 & Start=26 returns the 26th through 50th names from a numero- alphabetically sorted list.</td></tr>
						<tr><td>Order By</td><td>
							<select name="hobinom_orderby">
								<option value="SLD">SLD</option>
								<option value="TLD">TLD</option>
								<option value="DNS">DNS</option>
								<option value="ExpirationDate">ExpirationDate</option>
							</select>The order to return the results.</td></tr>
						<tr><td>Start Letter</td><td><input type="text" name="hobinom_startletter" maxlength="1" size="4" value="<?php echo $hn_startletter; ?>">Return names that start with this letter</td></tr>
						<tr><td>Multilanguage Support</td>
						<td><select name="hobinom_multilang">
							<option value="On">On</option>
							<option value="Off">Off</option>
							</select> If set to "On", the domain name will be display in native character set in UI.</td></tr>
						<tr><td>Domain</td><td><input type="text" name="hobinom_domainname" maxlength="60" value="<?php echo $hn_domainname; ?>">Return names that match this name. Use format: DOMAINNAME.TLD</td></tr>
					</table>
			</form>  
		</div>
		</div>
	</div>
 
<?php
if(($_POST['hobinom_hidden'] == 'Y')  && ($_POST['nooptionsdomains'] || $_POST['optionsdomains'] || $_POST['expireddomains']))
{  
	//Form data sent  
	$nooptions = $_POST['nooptionsdomains'];	
	$options = $_POST['optionsdomains'];	
	$expired = $_POST['expiredomains'];
	
	$tab = $_POST['hobinom_tab'];
	$daystoexpired = $_POST['hobinom_daystoexpired'];
	$regstatus = $_POST['hobinom_regstatus'];
	$display = $_POST['hobinom_display'];
	$startletter = $_POST['hobinom_startletter'];
	$orderby = $_POST['hobinom_orderby'];
	$multilang = $_POST['hobinom_multilang'];
	
	// set defaults
	if(!isset($tab)) { $tab = "IOwn";}
	if(!isset($daystoexpired)) { $daystoexpired = ""; }
	if(!isset($regstatus)) { $regstatus = "Sub_IOwn";}
	if(!isset($display)) { $display = 25;}
	if(!isset($start)) { $start = 1;}
	if(!isset($orderby)) { $orderby = "";}
	if(!isset($multilang)) { $multilang = ""; }
	
	
	if(isset($nooptions) || isset($options))
	{
		// URL for API request, get list of all domains with options
		$url =  $api_url . '?command=GetDomains&responsetype=xml&'.
					'uid='.$username.'&pw='.$password.'&Tab='.$tab.'&DaysToExpired='.$daystoexpired.'&RegStatus='.$regstatus.'&Display='.$display.'&StartLetter='.$startletter.'&OrderBy='.$orderby.'&MultiLang='.$multilang;
	}
	else
	{
	 // URL for API request, get list of expired domains
		$url =  $api_url . '?command=GetExpiredDomains&responsetype=xml&uid='.$username.'&pw='.$password;
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
	
		echo "<div class='metabox-holder'>";
		echo "<div class='postbox'>";
		
		$domain_list = $xml->GetDomains->{"domain-list"}->domain;

		echo '<h3 class="hndle"><span>Domain Results</span></h3>';
		echo '<div class="inside">';
		if(isset($nooptions) || ($options))
		{
			print "<table width='70%' style='text-align:center'>
			<tr><th>Domain ID</th><th>Domain Name</th><th>Extension</th><th>Using eNom Nameservers?</th><th>Expiration Date</td>
			<th>Auto Renew?</th><th>WhoIs Privacy Protection</th></tr>";
			
			for($i=0; $i<count($domain_list); $i++)
			{	
				print "<tr>";
				print "<td>".$domain_list->$i->DomainNameID."</td>";
				print "<td id='info'>".$domain_list->$i->sld."</td>";
				print "<td id='info'>".$domain_list->$i->tld."</td>";
				print "<td>".$domain_list->$i->{'ns-status'}."</td>";
				print "<td>".$domain_list->$i->{'expiration-date'}."</td>";
				print "<td>".$domain_list->$i->{'auto-renew'}."</td>";
				print "<td>".$domain_list->$i->wppsstatus."</td>";
				print "</tr>";
			}
		}
		else
		{
		
		print "<table width='70%' style='text-align:center'>
			<tr><th>Domain ID</th><th>Domain Name</th><th>Status</th><th>Expiration Date</th><th>Lock Status</th></tr>";

			for($i=0; $i<count($domain_list); $i++)
			{	
				print "<tr>";
				print "<td>".$domain_list->$i->DomainNameID."</td>";
				print "<td>".$domain_list->$i->DomainName."</td>";
				print "<td>".$domain_list->$i->Status."</td>";
				print "<td>".$domain_list->$i->{'expiration-date'}."</td>";
				print "<td>".$domain_list->$i->{'LockStatus'}."</td>";
				print "<td>".$domain_list->$i->wppsstatus."</td>";
				print "</tr>";
			}
		}
		print "</table>";
	}
}
?>
	</div>
	</div>
</div>
<!-- LIST DOMAINS -->



<div id="clickTab2" style="display:none;">
	<div class="wrap">  
	<?php echo "<h2>" . __( 'Domain Management', 'hobinomnom_trdom' ) . "</h2>";?>
			
		<form name="hobinomnom_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
				<input type="hidden" name="hobinomnom_hidden" value="Y">  
				<?php    echo __( "Manage your eNom registered domains here." ); ?>  
				
				<p><?php _e("Domain: " ); ?><input type="text" name="hobinomnom_domain" value="<?php echo $hn_domain; ?>" size="20"><?php _e(" ex: immortaldc" ); ?></p>  
				<p><?php _e("TLD: "); ?><input type="text" name="hobinomnom_tld" value="<?php echo $hn_tld; ?>" size="10"><?php _e("ex: com"); ?></p>
			
				<p class="submit">  
				<input type="submit" name="Submit" value="<?php _e('Update Options', 'hobinomnom_trdom' ) ?>" />  
				</p>  
		</form>  
	</div>  
</div>


<!-- CODE -->

