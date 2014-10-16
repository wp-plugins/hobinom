<?php
//require_once('../include/functions.php');

if( !defined( 'ABSPATH' ) ) { exit;}
	
class HNDomainSearchWidget extends WP_Widget 
{
	function HNDomainSearchWidget() 
	{
		$widget_ops = array(
			'classname' => 'HNDomainSearchWidget',
			'description' => 'Search for domains on your site.'
		);
		$this->WP_Widget('HNDomainSearchWidget', 'HobiNom Domain Search', $widget_ops);
	}
	
	// admin form, in the widget page
	function form($instance)
	{
		global $wpdb;
		(isset($wpdb->base_prefix) ? $_prefix = $wpdb->base_prefix : $_prefix = $wpdb->prefix);	
		
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = $instance['title'];
		// allow for updating the widget name
?>
  <p>
		<label for="<?php echo $this->get_field_id('title'); ?>">Title: 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
		</label>
	</p>
<?php
  }
	
	function widget($args, $instance) 
	{ 
		global $wpdb;
		global $post;

		$referrer = $_SERVER['HTTP_REFERER'];
		
		// widget sidebar output
		extract( $args );
		// these are the widget options
		$title = apply_filters('widget_title', $instance['title']);
		$text = $instance['text'];
		echo $before_widget;
		
		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';

		// Check if title is set
		if ( $title ) { echo $before_title . $title . $after_title;}
		
		// the actual search get_permalink( $post->ID ); 
		$p = get_queried_object();
		$pid = (isset($p->ID)) ? get_permalink($p->ID) : home_url();
		
		echo '<form method="post" id="domainsearch" action="" >';
		echo '<input type="text" name="domain" value="Domain.com" onfocus="this.value = \'\';" />';
		echo '<input type="submit" value="' . __( 'Search' ) . '" name="search_domain" /><br />';
		echo '</div></form>';

		$this->search_for_domain($_POST['domain']);
		
		echo $after_widget;
	}
	
	// update widget options
	function update($new_instance, $old_instance) 
	{
		$instance = $old_instance;
		// Fields
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}
	
	function deactivate()
	{
    delete_option('HNDomainSearchWidget');
  }

	
	// process the widget input and search for domain using eNom API
	function search_for_domain($search_domain)
	{		
		if(isset($search_domain))
		{
			// get details to access hobi + enom API
			$hobinom = new hobinom_db(); 

			$current_user_id = get_current_user_id();
			$details = $hobinom->get_enom_details($current_user_id);
	
			$username = $details['username'];  
			$password = $details['password']; 

			// separate the data from domain and tld
			// explode makes each subsequent . a new array, so domain.co.uk is [0],[1],[2] while domain.co is [0][1]
			$root_domain = explode(".", $search_domain);
			$domain = $root_domain[0]; 
			$tld = substr($search_domain, strrpos($search_domain, ".")+1);
			
			// access enom api
			$url =  'https://resellertest.enom.com/interface.asp?command=check&sld='.$domain.'&tld='.$tld.
				'&responsetype=xml&uid='.$username.'&pw='.$password;
	
			// Load the API results into a SimpleXML object
			$xml = simplexml_load_file($url);
			
			if(isset($xml->errors)) 
			{
				// print all errors
				function recursive_print($item, $key)
				{
					echo '<div id="domain-notavail">'.$item.'</div><br />';
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
						echo '<div id="domain-avail">'. $xml->DomainName.' is available</div>';
						break;
					case 211:
						echo '<div id="domain-notavail">'.$xml->DomainName.' is not available</div>';
						break;
					default:
						echo '<div id="domain-default">Code: '. $rrpCode . ' ' . $rrpText . '</div>';
						break;
				}
			}		
		}
	}
}
add_action('widgets_init', create_function('','return register_widget("HNDomainSearchWidget");'));
?>