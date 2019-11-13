<?php
/*

*/
namespace SocialLogin;

class Settings
{
	/*
		To store the tabs with settings
	*/
	protected $tabs;
	/*
		To store the arrays of settings
	*/
	protected $settings;

	function __construct()
	{
		$this->init();

		add_action("admin_menu",array($this,"add_plugin_settings_page"));
	}
	/*
		Register page under Plugins submenu
	*/
	function add_plugin_settings_page()
	{
		if(!current_user_can("administrator"))
		{
			return;
		}
		add_plugins_page( __("Social Login"),  __("Social Login"), "manage_options", "sociallogin-settings", array($this,"render"));
	}
	private function init()
	{
		$this->tabs = array(
			array(
				"title" => __("Google Settings"),
				"id" => "google-settings"
			),
			array(
				"title" => __("Facebook Settings"),
				"id" => "facebook-settings"
			),
			array(
				"title" => __("Vkontakte Settings"),
				"id" => "vkontakte-settings"
			),
			array(
				"title" => __("E-Mail"),
				"id" => "email-settings"
			),
			array(
				"title" => __("Misc"),
				"id" => "misc-settings"
			)
		);
		$this->settings = array(
			array(
				"name" => "sociallogin-google-client-id",
				"container" => "google-settings",
				"type" => "text",
				"label" => __("Google OAuth Client ID"),
			),
			array(
				"name" => "sociallogin-google-client-secret",
				"container" => "google-settings",
				"type" => "text",
				"label" => __("Google OAuth Client Secret"),
			),

			array(
				"name" => "sociallogin-facebook-client-id",
				"container" => "facebook-settings",
				"type" => "text",
				"label" => __("Facebook OAuth Client ID"),
			),
			array(
				"name" => "sociallogin-facebook-client-secret",
				"container" => "facebook-settings",
				"type" => "text",
				"label" => __("Facebook OAuth Client Secret"),
			),

			array(
				"name" => "sociallogin-vkontakte-client-id",
				"container" => "vkontakte-settings",
				"type" => "text",
				"label" => __("Vkontakte OAuth Client ID [APP ID]"),
			),
			array(
				"name" => "sociallogin-vkontakte-client-secret",
				"container" => "vkontakte-settings",
				"type" => "text",
				"label" => __("Vkontakte OAuth Client Secret [APP SECRET]"),
			),
			array(
				"name" => "social-login-registered-email-title",
				"container" => "email-settings",
				"type" => "text",
				"label" => __("Outgoing E-Mail Title"),
			),
			array(
				"name" => "social-login-registered-message",
				"container" => "email-settings",
				"type" => "wp_editor",
				"additional_text" => __("Variables: ") . "%FIRSTNAME%, %LASTNAME%, %PASSWORD%, %USERNAME%",
				"label" => __("A text sent to newly registered user"),
			),
			array(
				"name" => "social-login-registered-outgoingemail",
				"container" => "email-settings",
				"type" => "text",
				"label" => __("Outgoing E-Mail Address"),
			),
			array(
				"name" => "social-login-registered-outgoingname",
				"container" => "email-settings",
				"type" => "text",
				"label" => __("Outgoing E-Mail Name (FROM)"),
			),
			array(
				"name" => "social-login-enable-default-form",
				"container" => "misc-settings",
				"type" => "radio",
				"label" => __("Enable form on default WP Login"),
				"value" => "1",
				"title" => __("Default Form Settings")
			),
			array(
				"name" => "social-login-enable-default-form",
				"container" => "misc-settings",
				"type" => "radio",
				"label" => __("Disable form on default WP Login"),
				"value" => "0"
			),
		);
	}
	public function save()
	{
		if(!current_user_can("administrator"))
		{
			return;
		}
		if(isset($_REQUEST))
		{
			if(isset($_REQUEST["save_nonce"]))
			{
				if(wp_verify_nonce($_REQUEST["save_nonce"],__FILE__))
				{
					foreach($this->settings as $field)
					{
						$name = $field["name"];
						if(isset($_REQUEST[$name]))
						{
							if(is_array($_REQUEST[$name]))
							{
								$_REQUEST[$name] = json_encode($_REQUEST[$name]);
							}
							$setting = sanitize_text_field($_REQUEST[$name]);
							LoginFunctions::update_option($name,$setting);
						}
					}
					?>
					<br>
					<div class="notice notice-success is-dismissible">
				        <p> <strong><?php echo __("Success").'!'?></strong>
		                <?php
		                	echo __("Settings saved").'.';
		                ?>
		            	</p>
				    </div>
					<?php
				}
			}
		}
	}
	public function render()
	{
		if(!current_user_can("administrator"))
		{
			return;
		}
		/*
			Save Settings
		*/
		$this->save();
		/*
			Prepare names for JS
		*/
		$names = array();
		foreach($this->settings as $field)
		{
			if($field["type"] == "wp_editor")
			{
				array_push($names, $field["name"]);
			}
		}
		/*
			Enqueue scripts / styles
		*/
		$plugin_url = plugins_url() . '/' . basename( plugin_dir_path( (dirname( __FILE__ ) ) ));
		
		wp_enqueue_style("social-login/admin-bootstrap",$plugin_url.'/css/bootstrap.min.css');

		wp_enqueue_style("social-login/settings-css",$plugin_url.'/css/settings-page.css');

		wp_enqueue_script("social-login/settings-js",$plugin_url.'/js/settings-page.js');

		wp_localize_script( "social-login/settings-js", 'php_vars', 
	        array(
	          'names' => $names 
	        )
	    );
		/*
			Render HTML
		*/
		?>
		<div class="wrap">
			<?php
				$google = new Google;
				$facebook = new Facebook;
				$vk = new Vkontakte;
			?>
			<h5>
				<?php 
					_e("Pages that you should allow to be redirected to:")
				?>
			</h5>
			<input type="text" disabled value="<?php echo $google->redirect_uri?>" style="width: 100%"/>
				<input type="text" disabled value="<?php echo $facebook->redirect_uri?>" style="width: 100%"/>
				<input type="text" disabled value="<?php echo $vk->redirect_uri?>" style="width: 100%"/>
				<br><br>
			<form method="post">
			<input type="hidden" name="save_nonce" value="<?php echo wp_create_nonce(__FILE__);?>"/>
			<ul class="nav nav-tabs">
				<?php
				foreach($this->tabs as $container)
				{
					?>
					<li><a class="bootstrap-tab-link" href="javascript:void(0)" onclick="open_tab(this)" id="<?php echo $container["id"];?>"><?php echo $container["title"];?></a></li>
					<?php
					
				}
				?>
			</ul>
			<?php
				foreach($this->settings as $field)
				{
					?>
					<div class="container-tab-contents" opens-by="<?php echo $field['container'];?>">
						<?php
						$current_val = LoginFunctions::get_option($field["name"]);
						$settings = array( 'textarea_name' => $field["name"].'-editor', 'editor_height' => '350');
						if(isset($field["title"]))
						{
							?>
								<h3><?php echo $field["title"];?></h3>
							<?php
						}
			    		if($field["type"] == "text")
			    		{
			    			?>
			    			<label for="<?php echo $field["name"];?>">
			    					<span><?php echo $field["label"];?></span>
			    				</label>
			    				<input type="text" style="width: 100%" id="<?php echo $field["name"];?>" name="<?php echo $field["name"];?>" value="<?php echo $current_val; ?>" />
			    			<?php
			    		}
			    		if($field["type"] == "radio")
			    		{
			    			?>
			    				<input type="radio" id="<?php echo $field["name"].'-'.$field['value'];?>" name="<?php echo $field["name"];?>" value="<?php echo $field['value']; ?>" <?php if($current_val == $field['value']) echo "checked";?> />
			    				<label class="radio-label" for="<?php echo $field["name"].'-'.$field['value'];?>">
			    					<span><?php echo $field["label"];?></span>
			    				</label>
			    			<?php
			    		}
			    		if($field["type"] == "checkboxarray")
			    		{
			    			$test = json_decode($current_val);
			    			if(!is_null($test) && is_array($test))
			    			{
			    				if(in_array($field["value"], $test))
			    				{
			    					$current_val = $field["value"];
			    				}
			    			}
			    			?>
			    				<input type="checkbox" id="<?php echo $field["name"].'-'.$field['value'];?>" name="<?php echo $field["name"];?>[]" value="<?php echo $field['value']; ?>" <?php if($current_val == $field['value']) echo "checked";?> />
			    				<label class="radio-label" for="<?php echo $field["name"].'-'.$field['value'];?>">
			    					<span><?php echo $field["label"];?></span>
			    				</label>
			    			<?php
			    		}
			    		if($field["type"] == "select")
			    		{
			    			?>
			    			<label for="<?php echo $field["name"];?>">
			    					<span><?php echo $field["label"];?></span>
			    				</label>
							<?php
								$current_value = LoginFunctions::get_option($field["name"]);
							?>
							<select style="width: 100%" name="<?php echo $field["name"];?>" id="<?php echo $field["name"];?>">
								<?php
									foreach($field["values"] as $value)
									{
										?>
											<option value="<?php echo $value['shortname'];?>" <?php echo ($current_value == $value['shortname']) ? 'selected' : '';?>><?php echo $value['name'] .' ['.$value['shortname'].']';?></option>
										<?php
									}
								?>
							</select>
			    			<?php
			    		}
			    		if($field["type"] == "wp_editor")
						{
							?>
							<label for="<?php echo $field["name"];?>">
			    				<span><?php echo $field["label"];?></span>
			    			</label>
							<?php
							if(isset($field["additional_text"]))
							{
								?>
									<p><?php echo $field["additional_text"];?></p>
								<?php
							}
			    			wp_editor( base64_decode($current_val), $field["name"].'-editor', $settings );
			    			?>
			    			<input type="hidden" name="<?php echo $field["name"];?>" id="<?php echo $field["name"];?>" value="<?php echo $current_val; ?>">
			    			<?php
			    		}

			    		?>
		    		</div>
		    		<?php
				}
				?>
				<br>
				<button class="button" type="submit"><?php _e("Save");?></button>
			</form>
		</div>
		<?php
	}
}