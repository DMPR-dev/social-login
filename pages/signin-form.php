<?php
if(!is_user_logged_in())
{
	$links = SocialLogin\SocialLogin::get_login_links();
	?>
	<div class="social-login-form">
		<a class="social-login-link" href="<?php echo $links['google'];?>"><i class="fab fa-google"></i> <?php _e("Login");?></a>

		<a class="social-login-link" href="<?php echo $links['facebook'];?>"><i class="fab fa-facebook"></i> <?php _e("Login");?></a>

		<a class="social-login-link" href="<?php echo $links['vkontakte'];?>"><i class="fab fa-vk"></i> <?php _e("Login");?></a>
	</div>
	<?php
}
?>