<?php

namespace SocialLogin;

class Actions
{
	/*
		Handles the login attemp using availble social networks

		@param $network_name - a name of network to listen to

		@returns VOID
	*/
	public static function handle_social_network_auth($network_name)
	{
		if(!isset($_GET["code"]) || !isset($_GET[strtolower($network_name)."-auth"]) || $_GET[strtolower($network_name)."-auth"] != true)
		{
			return;
		}
		/*
			Declare a class of current network
		*/
		$class_name = "SocialLogin\\".$network_name;
		$network_class = new $class_name;
		/*
			Make network name lowercase
		*/
		$network_name = strtolower($network_name);
		/*
			Get access token from social network
		*/
		$access_token = LoginFunctions::get_token($network_class,$_GET["code"]);
		$email = "";
		if(is_array($access_token) && isset($access_token["email"]) && isset($access_token["access_token"]))
		{
			$email = $access_token["email"];
			$access_token = $access_token["access_token"];
		}
	    if(!is_null($access_token))
	    {
	    	/*
				Get user info from social network
	    	*/
	    	$user_info = $network_class->get_user($access_token);
	    	if(strlen($email) == 0 && is_array($user_info) && isset($user_info["email"]))
	    	{
	    		$email = $user_info["email"];
	    	}
	    	/*
				Check if there is a user with email received from social network
	    	*/
	    	if(strlen($email) > 0 && LoginFunctions::user_with_email_exists($email)
	    	|| isset($user_info["id"]) && LoginFunctions::user_with_social_network_id_exists($user_info["id"],$network_name))
	    	{
	    		/*
					Login
	    		*/
	    		$args = array(
	    			"email" => isset($email) ? $email : '',
	    			"id" => isset($user_info["id"]) ? $user_info["id"] : 0,
	    			"is_social_network_login" => true,
	    			"network" => $network_name
	    		);
	    		LoginFunctions::login_user($args,$user_info);
	    	}
	    	else
	    	{
	    		/*
					Register
	    		*/
				if(!isset($user_info["email"]) && strlen($email) > 0)
				{
					$user_info["email"] = $email;
				}
	    		$user_info["network"] = $network_name;
 
	    		$new_user = LoginFunctions::register_user($user_info);
	    		/*
					Send an activation E-Mail 
	    		*/
				wp_new_user_notification($new_user);
	    		/*
					Login after registeration
	    		*/
	    		$args = array(
	    			"email" => isset($email) ? $email : '',
	    			"id" => isset($user_info["id"]) ? $user_info["id"] : 0,
	    			"is_social_network_login" => true,
	    			"network" => $network_name
	    		);
	    		LoginFunctions::login_user($args);
	    	}
	    }
	}
	/*
		Renders default social login form on default wordpress form

		@returns VOID
	*/
	public static function render_form_on_default_login_form()
	{
		$is_render_allowed = filter_var(LoginFunctions::get_option("social-login-enable-default-form"),FILTER_VALIDATE_BOOLEAN);
		if($is_render_allowed)
		{
			SocialLogin::render_form();
		}
	}
	/*
		Adds styles for default social login form

		@return VOID
	*/
	public static function enqueue_form_style()
	{
		$is_render_allowed = filter_var(LoginFunctions::get_option("social-login-enable-default-form"),FILTER_VALIDATE_BOOLEAN);
		if($is_render_allowed)
		{
			wp_enqueue_style("social-login-form-style", plugin_dir_url(__FILE__) . 'css/style.css');
			wp_enqueue_style("social-login-font-awesome",'https://use.fontawesome.com/releases/v5.7.2/css/all.css');
		}
	}
	/*
		Adds scripts for default social login form

		@return VOID
	*/
	public static function enqueue_form_script()
	{
		$is_render_allowed = filter_var(LoginFunctions::get_option("social-login-enable-default-form"),FILTER_VALIDATE_BOOLEAN);
		if($is_render_allowed)
		{
			wp_enqueue_script("social-login-form-script", plugin_dir_url(__FILE__) . 'js/script.js',array('jquery'));
		}
	}
}