<?php
namespace SocialLogin;
class Google
{
	/*
		Client id provided by google's OAuth
	*/
	public $client_id;
	/*
		Client secret provided by google's OAuth
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
		$this->client_id = LoginFunctions::get_option('sociallogin-google-client-id'); // Client ID
		$this->client_secret = LoginFunctions::get_option('sociallogin-google-client-secret'); // Client secret
		$this->redirect_uri = home_url('/','https') . "?". 'google-auth=true'; // Redirect URI

		$this->token_uri = 'https://accounts.google.com/o/oauth2/token';
	}
	/*
		Returns an instance of class(i.e object)

		@RETURNS SocialLogin\Google object
	*/
	public static function get_instance()
	{
		return new Google();
	}
	/*
		Generates a url which user should follow to login/register using google

		@RETURNS string
	*/
	public function generate_auth_url()
	{
		$url = 'https://accounts.google.com/o/oauth2/auth';
		$params = array(
		    'redirect_uri'  => $this->redirect_uri,
		    'response_type' => 'code',
		    'client_id'     => $this->client_id,
		    'scope'         => apply_filters('social-login_google-scope','https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile'),
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
		A function that handles the goolge auth on "init" hook

		Allows user to be registered / logged in using hist google account

		@RETURNS VOID
	*/
	public static function handle_g_auth()
	{
		Actions::handle_social_network_auth("Google");
	}
	/*
		Gets the user's information provided by google 

		@param $access_token - the access token passed in params to google api

		@returns ARRAY | NULL
	*/
	public function get_user($access_token)
	{
		$params = array(
	        'client_id'     => $this->client_id,
	        'client_secret' => $this->client_secret,
	        'redirect_uri'  => $this->redirect_uri,
	        'grant_type'    => 'authorization_code',
	        'access_token'  => $access_token
	    );
	    $url = 'https://www.googleapis.com/oauth2/v1/userinfo';

	    $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url . "?" . http_build_query($params));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($curl);
		curl_close($curl);

		$user_info = json_decode($result,true);

	    if (isset($user_info['id'])) 
	    {
	        return $user_info;
	    }
	    return null;
	}
}