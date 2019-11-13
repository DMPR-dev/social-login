<?php
namespace SocialLogin;
class Facebook
{
	/*
		Client id provided by facebook's OAuth
	*/
	public $client_id;
	/*
		Client secret provided by facebook's OAuth
	*/
	public $client_secret;
	/*
		A url of login/register handler where user will get redirected after selecting his account
	*/
	public $redirect_uri;
	/*
		A url of page where we get Access Token From
	*/
	public $token_uri;
	/*
		Constructor
	*/
	public function __construct()
	{
		$this->client_id = LoginFunctions::get_option('sociallogin-facebook-client-id'); // Client ID
		$this->client_secret = LoginFunctions::get_option('sociallogin-facebook-client-secret'); // Client secret
		$this->redirect_uri = home_url('/','https') . "?". 'facebook-auth=true'; // Redirect URI

		$this->token_uri = 'https://graph.facebook.com/oauth/access_token';
	}
	/*
		Returns an instance of class(i.e object)

		@RETURNS SocialLogin\Facebook object
	*/
	public static function get_instance()
	{
		return new Facebook();
	}
	/*
		Generates a url which user should follow to login/register using facebook

		@RETURNS string
	*/
	public function generate_auth_url()
	{
		$url = 'https://www.facebook.com/dialog/oauth';

		$params = array(
		    'client_id'     => $this->client_id,
		    'redirect_uri'  => $this->redirect_uri,
		    'response_type' => 'code',
		    'scope'         => apply_filters('social-login_facebook-scope','email'),
		    'redirect'		=> ''
		);
		if(isset($_GET["redirect"]))
		{
			$redirect_url = filter_var($_GET["redirect"],FILTER_VALIDATE_URL);
			if($redirect_url)
			{
				$_SESSION["social-login-redirect"] = $redirect_url;
			}
		}
		return $url . '?' . urldecode(http_build_query($params));
	}
	/*
		A function that handles the facebook auth on "init" hook

		Allows user to be registered / logged in using his facebook account

		@RETURNS VOID
	*/
	public static function handle_fb_auth()
	{
		Actions::handle_social_network_auth("Facebook");
	}
	/*
		Gets the user's information provided by facebook 

		@param $access_token - the access token passed in params to facebook api

		@returns ARRAY
	*/
	public function get_user($access_token)
	{
		$params = array(
	        'access_token'  => $access_token,
	        'fields' => 'email,first_name,last_name'
	    );
	    $url = 'https://graph.facebook.com/me';
	    $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($curl);
		curl_close($curl);
	    $user_info = json_decode($result, true);

        if (isset($user_info['id'])) 
	    {
	        return $user_info;
	    }
	    return array();
	}
}