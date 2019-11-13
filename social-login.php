<?php
/*
Plugin Name: Social Login / Sign Up
Description: Login using Google, Facebook, VK.
Version:     1.0.0
Author:      Dmytro Proskurin
 *
 */
namespace SocialLogin;

require_once plugin_dir_path(__FILE__) ."functions.php";
require_once plugin_dir_path(__FILE__) ."actions.php";
require_once plugin_dir_path(__FILE__) ."filters.php";
require_once plugin_dir_path(__FILE__) ."lists.php";
require_once plugin_dir_path(__FILE__) ."classes/settings.php";
require_once plugin_dir_path(__FILE__) ."classes/google.php";
require_once plugin_dir_path(__FILE__) ."classes/facebook.php";
require_once plugin_dir_path(__FILE__) ."classes/vkontakte.php";

class SocialLogin
{
	public function __construct()
	{
		/*
			Init other classes
		*/
		new Settings();
		/*
			Add actions (attach functions to hooks)
		*/
		/*
			Google Auth
		*/
		add_action("init","SocialLogin\Google::handle_g_auth");
		/*
			Facebook Auth
		*/
		add_action("init","SocialLogin\Facebook::handle_fb_auth");
		/*
			Vkontakte Auth
		*/
		add_action("init","SocialLogin\Vkontakte::handle_vk_auth");

		/*
			Render form on default login form
		*/
		add_action("login_form","SocialLogin\Actions::render_form_on_default_login_form");
		/*
			Add default form style
		*/
		add_action("login_enqueue_scripts","SocialLogin\Actions::enqueue_form_style");
		/*
			Add default form script
		*/
		add_action("login_enqueue_scripts","SocialLogin\Actions::enqueue_form_script");

		/*
			Enable PHP sessions
		*/
		add_action('init', function(){
			if(!session_id())
			{
				session_start();
			}
		},1);
		add_action('end_session_action',function(){
			session_destroy();
		});
	}
	/*
		Returns an instance of class(i.e object)

		@RETURNS SocialLogin\Google object
	*/
	public static function get_instance()
	{
		return new SocialLogin();
	}
	/*
		Provides a list (array) of login/auth links (to login using google/facebook/vk)

		@returns ARRAY of strings
	*/
	public static function get_login_links()
	{
		$google = new Google;
		$facebook = new Facebook;
		$vk = new Vkontakte;

		return array (
			"google" => $google->generate_auth_url(),
			"facebook" => $facebook->generate_auth_url(),
			"vkontakte" => $vk->generate_auth_url()
		);
	}
	/*
		Renders / returns a login/register form provided by plugin

		@param $echo - optional - default:false - a boolean variable that allows us to decide to render HTML of form or to return it

		@returns HTML | STRING
	*/
	public static function render_form($echo = true)
	{
		if(!$echo)
		{
			ob_start();
		}
		if(file_exists(plugin_dir_path(__FILE__)."pages/signin-form.php"))
		{
			require_once plugin_dir_path(__FILE__)."pages/signin-form.php";
		}
		if(!$echo)
		{
			return ob_get_clean();
		}
	}
}
new SocialLogin();
