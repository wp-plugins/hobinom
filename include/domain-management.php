<a onclick="clickTabSwitch(1);">Get Preferences</a>
<div id="clickTab1">
[your content here]
</div>

<a onclick="clickTabSwitch(2);">Set Preferences</a>


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