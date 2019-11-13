<?php
namespace SocialLogin;

class Filters
{
	/*
		Sets custom outgoing E-Mail set from plugin settings

		@returns STRING
	*/
	public static function send_from_email($email_address)
	{
		$custom_email_from = sanitize_email(LoginFunctions::get_option("social-login-registered-outgoingemail"));
		if(strlen($custom_email_from) > 0)
		{
			$email_address = $custom_email_from;
		}
		return $email_address;
	}
	/*
		Sets custom outgoing E-Mail Name (FROM) set from plugin settings

		@returns STRING
	*/
	public static function send_from_name($name)
	{
		$custom_from_name = LoginFunctions::get_option("social-login-registered-outgoingname");
		if(strlen($custom_from_name) > 0)
		{
			$name = $custom_from_name;
		}
		return $name;
	}
}