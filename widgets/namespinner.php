<?php
//require_once('../include/functions.php'); 

if( !defined( 'ABSPATH' ) ) { exit;}
	
class HNNamespinnerWidget extends WP_Widget 
{
	function HNNamespinnerWidget() 
	{
		$widget_ops = array(
			'classname' => 'HNNamespinnerWidget',
			'description' => 'Spin some domains in a widget.'
		);
		$this->WP_Widget('HNNamespinnerWidget', 'HobiNom Namespinner', $widget_ops);
	}
	
	// admin form, in the widget page
	function form($instance)
	{
		global $wpdb;
		(isset($wpdb->base_prefix) ? $_prefix = $wpdb->base_prefix : $_prefix = $wpdb->prefix);	
		
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = $instance['title']; // allow updating widget name
		$max_results = $instance['max_results'];
?>
  <p>
		<label for="<?php echo $this->get_field_id('title'); ?>">Title:<br />
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
		</label>
		<label>Number of Domains to Show:
			<input class="" id="<?php echo $this->get_field_id('max_results'); ?>" name="<?php echo $this->get_field_name('max_results'); ?>" type="text" value="<?php echo attribute_escape($max_results); ?>" />
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
		$max_results = apply_filters('widget_input', $instance['max_results']);
		
		$submit_change = apply_filters('widget_submit', $instance['submit']);
		$submit_text = $instance['text'];
		echo $before_widget;
		
		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';

		// Check if title is set
		if ( $title ) { echo $before_title . $title . $after_title;}
		
		// the actual search get_permalink( $post->ID ); 
		$p = get_queried_object();
		$pid = (isset($p->ID)) ? get_permalink($p->ID) : home_url();
		
		echo '<form method="post" id="domainspin" action="" >';
		echo '<input type="text" name="domainspin" value="Domain.com" onfocus="this.value = \'\';" />';
		echo '<input type="submit" value="' . __( 'Spin domain' ) . '" name="spin_domain" /><br />';
		echo '</div></form>';

		$this->spin_domain($_POST['domainspin'], $max_results);
		
		echo $after_widget;
	}
	
	// update widget options
	function update($new_instance, $old_instance) 
	{
		$instance = $old_instance;
		// Fields
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['max_results'] = strip_tags($new_instance['max_results']);
		
		return $instance;
	}
	
	function deactivate()
	{
    delete_option('HNNamespinnerWidget');
  }

	
	// spin domain via API
	function spin_domain($spin_domain, $max_results = 10)
	{		
		if(isset($spin_domain))
		{
			// get details to access hobi + enom API
			$hobinom = new hobinom_db(); 

			$current_user_id = get_current_user_id();
			$details = $hobinom->get_enom_details($current_user_id);
	
			$username = $details['username'];  
			$password = $details['password']; 

			// separate the data from domain and tld
			// explode makes each subsequent . a new array, so domain.co.uk is [0],[1],[2] while domain.co is [0][1]
			$root_domain = explode(".", $spin_domain);
			$domain = $root_domain[0]; 
			$tld = substr($spin_domain, strrpos($spin_domain, ".")+1);
			
			// access enom api
			$url = 'http://resellertest.enom.com/interface.asp?command=NameSpinner&UID='.$username.'&PW='.$password.'&SLD='.$domain.'&TLD='.$tld.'&MaxResults='.$max_results.'&Net=N&Tv=N&CC=N&ResponseType=XML';


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
				
				for($i=0; $i<count($xml->namespin->domains->domain); $i++)
				{
					$xm = $xml->namespin->domains->domain->$i->attributes();
					if($xm->com = "n")
					{
						echo '<div id="domain-notavail">'. $xm->name . '</div>';
					}
					else
					{
						echo '<div id="domain-avail">'. $xm->name . '</div>';
					}
				}
				
			}		
		}
	}
}
add_action('widgets_init', create_function('','return register_widget("HNNamespinnerWidget");'));
?>