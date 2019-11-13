<?php
namespace SocialLogin;
class Vkontakte
{
	/*
		Client ID (App id) provided by VK's OAuth
	*/
	public $client_id;
	/*
		Client secret(Secure Key) provided by VK's OAuth
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
		$this->client_id = LoginFunctions::get_option('sociallogin-vkontakte-client-id'); // Client ID
		$this->client_secret = LoginFunctions::get_option('sociallogin-vkontakte-client-secret'); // Client secret
		$this->redirect_uri = home_url('/','https') . "?". 'vkontakte-auth=true'; // Redirect URI

		$this->token_uri = 'https://oauth.vk.com/access_token';
	}
	/*
		Returns an instance of class(i.e object)

		@RETURNS SocialLogin\Vkontakte object
	*/
	public static function get_instance()
	{
		return new Vkontakte();
	}
	/*
		Generates a url which user should follow to login/register using VK

		@RETURNS string
	*/
	public function generate_auth_url()
	{
		$url = 'https://oauth.vk.com/authorize';
		$params = array(
		    'redirect_uri'  => $this->redirect_uri,
		    'response_type' => 'code',
		    'client_id'     => $this->client_id,
		    'scope'         => apply_filters('social-login_vkontakte-scope','email'),
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
		A function that handles the goolge auth on "init" hook

		Allows user to be registered / logged in using his Vkontakte account

		@RETURNS VOID
	*/
	public static function handle_vk_auth()
	{
		Actions::handle_social_network_auth("Vkontakte");
	}
	/*
		Gets the user's information provided by Vkontakte 

		@param $access_token - the access token passed in params to VK api

		@returns ARRAY | NULL
	*/
	public function get_user($access_token)
	{
		$params = array(
	        'access_token'  => $access_token,
	        'uid' => 0,
	        'v' => '5.101'
	    );
	    $url = 'https://api.vk.com/method/getProfiles';

	    $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url . "?" . http_build_query($params));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($curl);
		curl_close($curl);

		$user_info = json_decode($result,true);

		if (isset($user_info['response']))
		{ 
		    if (isset($user_info['response'][0]['id'])) 
		    {
		        return $user_info['response'][0];
		    }
		}
	    return null;
	}
}