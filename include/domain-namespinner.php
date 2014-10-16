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
		<h3 class="hndle"><span>Hobinom Namespinner</span></h3>
		<div class="inside">
		
		<form name="hobinom_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
				<input type="hidden" name="hobinom_hidden" value="Y">  
				<?php _e( "Not sure what domain you want, or want to know if better names exist? Try the domain spinner. Generate variations of a domain name that you specify, for .com, .net, .tv, and .cc. Make sure to have <a href='admin.php?page=hobinom/hobinom-settings.php'>your settings set</a> to use the API." );  ?>
				<p><br /></p>
				<table><tr><td>
				<?php _e("Domain: " ); ?></td><td><input type="text" name="hobinom_domain" value="<?php echo $hn_domain; ?>" size="20"><?php _e(" ex: domain" ); ?></td></tr>
				<tr><td><?php _e("TLD: "); ?></td><td>
				<select name="hobinom_tld">
					<option value="com">.com</option>
					<option value="net">.net</option>
					<option value="tv">.tv</option>
					<option value="cc">.cc</option>
				</select> 
				</td></tr><tr></tr>
				<tr><td><strong>Optional</strong></td></tr>
				<tr><td><?php _e("Sensitive Content"); ?></td><td>
					<select name="hobinom_sensitive">
						<option value="True">True</option>
						<option value="False">False</option>
					</select><?php _e("True filters out offensive content"); ?></td></tr>
				<tr><td> <?php _e("MaxLength"); ?></td><td>
					<input type="text" name="hobinom_maxlength" size="4" value="<?php echo $hn_maxlength; ?>"> <?php _e("Permitted 2 to 63. Maximum domain length to return."); ?></td></tr>
					
				<tr><td><?php _e("Max Results: "); ?></td><td><input type="text" name="hobinom_max_results" value="<?php echo $hn_max_results; ?>" size="4"> <?php _e("Max 99" ); ?></td></tr>
				<tr><td><?php _e("Use Hyphens"); ?></td><td>
					<select name="hobinom_hyphens">
						<option value="True">True</option>
						<option value="False">False</option>
					</select><?php _e("Return domain names that include hyphens."); ?></td></tr>
				<tr><td><?php _e("Use Numbers"); ?></td><td>
					<select name="hobinom_usenumbers">
						<option value="True">True</option>
						<option value="False">False</option>
					</select><?php _e("Return domain names that include numbers."); ?></td></tr>
				<tr><td><?php _e("Basic: "); ?></td><td>
					<select name="hobinom_basic">
						<option value="Off">Off</option>
						<option value="Low">Low</option>
						<option value="Medium">Medium</option>
						<option value="High">High</option>
					</select> 
				<?php _e("Higher values return suggestions built by adding prefixes, suffixes, and words to original input."); ?></td></tr>
				<tr><td><?php _e("Related: "); ?></td><td>
					<select name="hobinom_related">
						<option value="Off">Off</option>
						<option value="Low">Low</option>
						<option value="Medium">Medium</option>
						<option value="High">High</option>
					</select> 
				<?php _e("Higher values return domain names by interpreting the input semantically and construct suggestions with a similar meaning."); ?></td></tr>
				<tr><td><?php _e("Topical: "); ?></td><td>
					<select name="hobinom_topical">
						<option value="Off">Off</option>
						<option value="Low">Low</option>
						<option value="Medium">Medium</option>
						<option value="High">High</option>
					</select> <?php _e("Higher values return suggestions that reflect current topics and popular words."); ?>
				</td></tr>
				<tr><td></td><td>
				<p class="submit">  
					<input type="submit" name="Submit" value="<?php _e('Search', 'hobinom_trdom' ) ?>" />  
				</p>  
				</td></tr>
				</table>
		</form>  
		</div>
	</div>

	<!-- Getting Started box -->
<div class="postbox">
	
<?php
if($_POST['hobinom_hidden'] == 'Y') 
{  
	//Form data sent  
	$domain = $_POST['hobinom_domain'];	
	$tld = $_POST['hobinom_tld'];
	$sensitive = $_POST['hobinom_sensitive'];
	$max_length = $_POST['hobinom_maxlength'];
	$max_results = $_POST['hobinom_max_results'];
	$hyphens = $_POST['hobinom_hyphens'];
	$use_numbers = $_POST['hobinom_usenumbers'];
	$basic = $_POST['hobinom_basic'];
	$related = $_POST['hobinom_related'];
	$similarity = $_POST['hobinom_similar'];
	$topical = $_POST['hobinom_topical'];

	if(empty($max_results)) { $max_results = 20;}
	if(empty($max_length)) { $max_length = 65;}
	
	 // URL for API request
	$url = 'http://resellertest.enom.com/interface.asp?command=NameSpinner&UID='.$username.'&PW='.$password.'&SLD='.$domain.'&TLD='.$tld.'&SensitiveContent='.$sensitive.'&MaxLength='.$max_length.'&MaxResults='.$max_results.'&UseHyphens='.$hyphens.'&UseNumbers='.$use_numbers.'&Basic='.$basic.'&Similar='.$similarity.'&Topical='.$topical.'&ResponseType=XML';

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
		echo "<h3 class='hndle'>Namespinner Results</h3>";
		echo "<div class='inside'>";
		echo "<table width='60%'><tr><td></td><td><th>Availability</th></td>";
		echo "<tr><th>Spinned Name</th><td><i>.com</i></td><td><i>.net</i></td><td><i>.tv</i></td><td><i>.cc</i></td></strong></td></tr>";
		for($i=0; $i<count($xml->namespin->domains->domain); $i++)
		{	
			$domain = $xml->namespin->domains->domain->$i->attributes();
			$style_com = ($domain->com == 'y') ? "background:green; color:#fff" : "background: red; color: #fff";
			$style_net = ($domain->net == 'y') ? "background:green; color: #fff" : "background: red; color: #fff";
			$style_tv = ($domain->tv == 'y') ? "background:green; color: #fff" : "background: red; color: #fff";
			$style_cc = ($domain->cc == 'y') ? "background:green; color: #fff" : "background: red; color: #fff";
			
			echo "<tr>";
			echo "<td>".$domain->name."</td>";
			echo "<td style='".$style_com."'>".$domain->com."</td>";
			echo "<td style='".$style_net."'>".$domain->net."</td>";
			echo "<td style='".$style_tv."'>".$domain->tv."</td>";
			echo "<td style='".$style_cc."'>".$domain->cc."</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
}
?>
		</div>
	</div>
</div>