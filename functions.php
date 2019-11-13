<?php
namespace SocialLogin;
class LoginFunctions
{
	/*
		Allows to use plugin with multisite and save user meta across all sites(common meta data)

		@param $user_id - an identifier of user to set meta to

		@param $param - the name of parameter to set

		@param $value the value to set

		@returns VOID
	*/
	public static function set_user_meta($user_id,$param,$value)
	{
		if(!is_multisite())
		{
			if(!update_user_meta($user_id,$param,$value))
			{
				add_user_meta($user_id,$param,$value,true);
			}
		}
		else
		{
			update_user_option( $user_id, $param, $value);
		}
	}
	/*
		Allows to us plugin with multisite and get user meta across all sites(common meta data)

		@param $user_id - the identifier of user to get param of

		@param $param - the name of parameter to retrieve

		@returns STRING
	*/
	public static function get_user_meta($user_id,$param)
	{
		if(!is_multisite())
		{
			return get_user_meta($user_id,$param,true);
		}
		else
		{
			return get_user_option($param,$user_id);
		}
	}
	/*
		Allows to use plugin with multisite and save settings across all sites

		@param $param - a string name of setting/parameter to update

		@param $value - a string value to set as new

		@returns STRING
	*/
	public static function update_option($param,$value)
	{
		$update_option = "update_option";
		if(is_multisite())
		{
			$update_option = "update_site_option";
		}
		return $update_option($param,$value);
	}
	/*
		Allows to use plugin with multisite and get settings across all sites

		@param $param - a string name of setting/parameter to get

		@returns STRING
	*/
	public static function get_option($param)
	{
		$get_option = "get_option";
		if(is_multisite())
		{
			$get_option = "get_site_option";
		}
		return $get_option($param);
	}
	/*
		Checks if there is a user exists with provided email

		@param $email - E-Mail of user to look for by

		@returns BOOLEAN
	*/
	public static function user_with_email_exists($email)
	{
		$email = sanitize_email($email);
		$user = get_user_by("email",$email);

		return !($user === false);
	}
	/*
		Checks if there is a user with provide ID of provided network

		@param $ID - user identifier provided by network

		@param $network - a name of network (i.e facebook,goolge,vkontakte)

		@returns BOOLEAN
	*/
	public static function user_with_social_network_id_exists($id,$network)
	{
		return !(self::get_user_by_network_id($id,$network) === null);
	}
	/*	
		Provides the user object by given ID of provided network

		@param $ID - user identifier provided by network

		@param $network - a name of network (i.e facebook,goolge,vkontakte)

		@returns WP_USER object | NULL
	*/
	public static function get_user_by_network_id($id,$network)
	{
		$args = array(
			 'meta_key' => $network."-id",
			 'meta_value' => $id,
			 'number' => 1,
			 'count_total' => false
		);
		if(is_multisite())
		{
			$args["blog_id"] = 0;
		}
		$users = get_users($args);

		if(is_array($users) && sizeof($users) > 0 && isset($users[0]))
		{
			return $users[0];
		}
		return null;
	}
	/*
		Logins user by provided credentials (E-Mail or social network ID provided from social network)

		@param $args - an array of args provided to function

		returns VOID
	*/
	public static function login_user(array $args)
	{
		$error = self::validate_login_args($args);	
		if(is_wp_error($error))
		{
			throw new \Exception($error->get_error_message());
		}
		/*
			Gather information
		*/
		$email = sanitize_email(isset($args["email"]) ? $args["email"] : "");

		$is_social_login = filter_var(isset($args["is_social_network_login"]) ? $args["is_social_network_login"] : false,FILTER_VALIDATE_BOOLEAN);

		$user = null;

		if($is_social_login && strlen($email) > 0)
		{
			$user = get_user_by('email', $email);
		}
		if(($user == false || is_null($user)) && $is_social_login && isset($args["id"]) && isset($args["network"]))
		{
			$social_id = $args["id"];
			if(strlen($social_id) > 0)
			{
				$user = self::get_user_by_network_id($social_id,sanitize_text_field($args["network"]));
			}
		}

		/*
			Login by object
		*/
		self::login_user_by_object($user);
	}
	/*
		Logins user by provided user object if it's not null

		@param $user - an object of WP_USER class that will be logged in

		@returns void
	*/
	public static function login_user_by_object($user)
	{
		if(!is_null($user) && property_exists($user, 'ID'))
		{
			wp_clear_auth_cookie();
		    wp_set_current_user ( $user->ID );
		    wp_set_auth_cookie  ( $user->ID );
		    /*
				Perform a redirect if it's set
		    */
		    if(isset($_SESSION["social-login-redirect"]))
		    {
		    	$url = filter_var($_SESSION["social-login-redirect"],FILTER_VALIDATE_URL);
		    	if($url)
		    	{
		    		unset($_SESSION["social-login-redirect"]);
		    		?>
		    		<script>
		    			var redirect_needed = (localStorage.getItem('social-login-redirect-needed') === "true");
		    			if(redirect_needed)
		    			{
		    				localStorage.setItem("social-login-redirect-link","<?php echo $url;?>");
		    				// close the window
		    				window.close();
		    			}
		    			else
		    			{
		    				window.location.href = "<?php echo $url;?>";
		    			}
		    		</script>
		    		<?php
		    		exit;
		    	}
		    }
		    /*
				User has been loggedin successfully, execute the hooked functions
			*/
			do_action("wp_login",$user->user_login,$user);
		}
	}
	/*
		Validates the array of argumnets passed to login function

		@param - $args - an array of argumnets passed to login function

		@returns array | WP_Error
	*/
	public static function validate_login_args(array $args)
	{
		// TO DO
		return $args;
	}
	/*
		Registers a user by provided array of arguments

		@param $args - an array of main params to register user(i.e email, first/last name, password, network)

		@param $user_info - an array of additional params to register user - an original array of params received from social network

		@returns INT
	*/
	public static function register_user(array $args,$user_info = array())
	{
		$error = self::validate_register_args($args);	
		if(is_wp_error($error))
		{
			throw new \Exception($error->get_error_message);
		}
		/*
			Gather information
		*/
		$email = sanitize_email(isset($args["email"]) ? $args["email"] : '');
		$id = intval(isset($args["id"]) ? $args["id"] : "");
		$user_firstname = (isset($args["first_name"]) ? $args["first_name"] : "");
		$user_lastname = (isset($args["last_name"]) ? $args["last_name"] : "");
		$password = (isset($args["password"]) ? $args["password"] : "");
		$network = sanitize_text_field($args["network"]);

		$userlogin = self::generate_username();

		/*
			Re-check & register
		*/
		if ( !username_exists( $userlogin )  && !email_exists( $email ) ) 
		{
			/*
				Generate password
			*/
			if(strlen($password) < 8)
			{
				$password = wp_generate_password(12);
			}
			$user_id = 0;
			if(!is_multisite())
			{
				$user_data = array(
			    	'user_login'  =>  $userlogin,
			    	'user_pass'   =>  $password,
			    	'user_email'  =>  $email,
			    	'first_name'  =>  $user_firstname, 
			    	'last_name'   =>  $user_lastname, 
			    );
			    $user_id = wp_insert_user($user_data);
			}
			else
			{
				/*
					Add user to all sites on multisite network
				*/
				$user_id = wpmu_create_user( $userlogin, $password, $email );
				if(function_exists("get_sites") && function_exists("add_user_to_blog"))
				{
					$sites = get_sites();
					foreach($sites as $site)
					{
						add_user_to_blog($site->blog_id, $user_id, 'subscriber');
					}
				}
			}
		    if(!is_wp_error($user_id) && intval($user_id) > 0)
		    {
		    	/*
					Add default user meta
		    	*/
				self::set_user_meta($user_id,$network."-id",$args["id"]);
			    /*
					User has been registered successfully, execute the hooked functions
		    	*/
				if(self::send_registered_email(get_user_by("ID",$user_id),$password))
				{
					do_action("social-login_new-user",$user_id,$user_info);
				}
			}
		    return wp_insert_user($user_data);
		}
		return 0;
	}
	/*
		Validates the array of argumnets passed to Register function

		@param - $args - an array of argumnets passed to login function

		@returns array | WP_Error
	*/
	public static function validate_register_args(array $args)
	{
		if(!isset($args["network"]))
		{
			return new \WP_Error("no_network_set",__("No network has been set, unable to register user!"));
		}
		if(!isset($args["id"]))
		{
			return new \WP_Error("no_id_set",__("No network user ID has been set, unable to register user!"));
		}
		return $args;
	}
	/*
		Gets an access token from GOOGLE/FACEBOOK/VK so we will be able to query user's information

		@param $object - an object sample tpo get client id, client secret and redirect uri from

		@param $code - a code included in get request after making URL generation (returned from social network)

		@returns STRING | NULL
	*/
	public static function get_token($object,$code)
	{
		/*
			Do a curl request
		*/
		$url = $object->token_uri;
		$params = array(
	        'client_id'     => $object->client_id,
	        'client_secret' => $object->client_secret,
	        'redirect_uri'  => $object->redirect_uri,
	        'grant_type'    => 'authorization_code',
	        'code'          => $code
	    );
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($curl);
		curl_close($curl);

		/*
			Handle curl response from social network
		*/
		$token_info = json_decode($result, true);
		if(!is_null($token_info) 
		&& (is_array($token_info) && isset($token_info["access_token"])))
		{
			if(isset($token_info["email"]))
			{
				return $token_info;
			}
			return $token_info["access_token"];
		}

		return null;
	}
	/*
		Generates random username

		@ORIGINAL https://github.com/MaPhil/username-generator

		@returns STRING
	*/
	public static function generate_username()
	{
		$adjectives = Lists::Adjectives();
		$names = Lists::Names();
		$separator = '_';

		$generate = function() use ($adjectives, $names,$separator)
		{
			$first_part = $names[rand(0,sizeof($names)-1)];
			$second_part = $adjectives[rand(0,sizeof($adjectives)-1)];

			return $first_part . $separator . $second_part;
		};

		$username = $generate();

		while(username_exists($username))
		{
			$username = $generate();
		}
		return $username;
	}
	/*
		Sends an E-mail to newly registered user with current plugin(i.e registered using social networks)

		@param $user - an object of WP_User class - a user that has just registered

		@returns BOOLEAN
	*/
	public static function send_registered_email($user,$password)
	{
		/*
			Check user's email address
		*/
		if(!is_object($user))
		{
			return false;
		}
		$email = sanitize_email($user->user_email);
		if(strlen($email) > 0)
		{
			/*
				Prepare the message and send
			*/
			$user_firstname = self::get_user_meta( $user->ID, 'first_name', true );
			$user_lastname = self::get_user_meta( $user->ID, 'last_name', true );

			$message = base64_decode(get_option("social-login-registered-message"));
			/*
				Replace variables
			*/
			$search_arr = array("%NAFIRSTNAMEME%","%LASTNAME%","%PASSWORD%","%USERNAME%");
			$replace_arr = array($user_firstname,$user_lastname,$password,$user->user_login);

			$title = get_option('social-login-registered-email-title');
			$message = str_replace($search_arr,$replace_arr,$message);

			/*
				Add Filters
			*/
			add_filter("wp_mail_from","SocialLogin\Filters::send_from_email");
			add_filter("wp_mail_from_name","SocialLogin\Filters::send_from_name");
			/*
				Send
			*/
			$result = wp_mail(
			    $email,
			   	$title,
			    $message
			);
			/*
				Remove Filters
			*/	
			remove_filter("wp_mail_from","SocialLogin\Filters::send_from_email");
			remove_filter("wp_mail_from_name","SocialLogin\Filters::send_from_name");

			return $result;
		}
	}
}