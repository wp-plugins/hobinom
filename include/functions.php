<?php

class hobinom_db
{
	// url to enom api  
	protected $enom_url = 'https://resellertest.enom.com/interface.asp';  
      
	// enom username and password
	protected $enom_user;
	protected $enom_password;
	
	protected $domain_name;
	protected $domain_tld;

	//public function __construct($enom_url, $enom_user, $enom_password, $domain_name, $domain_tld)  
	public function __construct()  
	{  
		/*
		$this->enom_url = $enom_url;
		$this->user 		= $enom_user;  
		$this->password = $enom_password;
		$this->domain 	= $domain_name;
		$this->tld 			= $domain_tld;
		*/
	}  
		
	public function get_all() 
	{
	 global $wpdb;
	 //$links = $wpdb->get_col( "SELECT ID FROM " . $wpdb->prefix . "reglevel" );
	 //return $links;
	}

	// get single value from database
	public function get_single_name_value($optname, $optvalue, $user_id) 
	{
	 global $wpdb;
	 $query = "SELECT id FROM " . $wpdb->prefix . "hobinom WHERE optionname='%s' AND optionvalue='%s' AND user_id='%s'";
	 $id = $wpdb->query( $wpdb->prepare($query, $optname, $optvalue, $user_id));
	 return $id;
	}
	
	// get the settings of a user using their id (if logged in) else just settings of user (if not logged in)
	public function get_id_settings($user_id) 
	{
		global $wpdb;
		
		// if the user is logged in
		if($user_id !=0)
			$query = 'SELECT id,optionname,optionvalue FROM ' . $wpdb->prefix . 'hobinom WHERE user_id="%s" AND optionname="settings"';
		
		// if the user is not logged in (ie, front end widget)
		if($user_id == 0)
			$query = 'SELECT id,optionname,optionvalue FROM ' . $wpdb->prefix . 'hobinom WHERE optionname="settings" LIMIT 1';
		
		$result = $wpdb->get_results( $wpdb->prepare($query, $user_id));
		
		
		return $result;
	}
	
	public function set_insert($optname, $optvalue, $user_id) 
	{
		global $wpdb;
		$query = 'INSERT INTO ' . $wpdb->prefix . 'hobinom( optionname, optionvalue, user_id ) VALUES (%s, %s, %s)';
		$wpdb->query( $wpdb->prepare( $query, $optname, $optvalue, $user_id));
	}

	public function set_update($optname, $optvalue, $user_id) 
	{
		global $wpdb;
		$query = 'UPDATE ' . $wpdb->prefix . 'hobinom SET optionvalue="%s" WHERE optionname="%s" AND user_id="%s"';
		$wpdb->query( $wpdb->prepare( $query, $optvalue, $optname, $user_id));
	}

	public function set_delete($hobiid, $user_id) 
	{
		global $wpdb;
		$query = 'DELETE FROM ' . $wpdb->prefix . 'hobinom WHERE id=%s AND user_id=%s';
		$wpdb->query( $wpdb->prepare( $query, $hobiid, $user_id) );
	}
	
	// get your enom details from the database
	public function get_enom_details($current_user_id)
	{
		global $wpdb;
		$settings_db = $this->get_id_settings($current_user_id);
		
		$settings_decode = unserialize( base64_decode($settings_db[0]->optionvalue));
		
		$username = $settings_decode['username'];  
		$password = $settings_decode['password'];  
		
		$enom = array('username' => $username, 'password' => $password);
		
		return $enom;
	}
	
	// TODO: single place for url manipulation
	public function set_domain_url_data($type)
	{
		switch ($type)
		{
			case "search-domain":
				echo "Search domain";
				break;
			case "domain-namespinner":
				echo "Namespinner";
				break;
			default:
				echo "";
		}
	}
}

?>