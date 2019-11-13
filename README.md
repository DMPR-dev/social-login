# Social Login Plugin
The plugin that allows users to login to your site using your Google, Facebook or Vkontakte account. Wordpress Multisite is supported, currently newly registered user gets added to all sites on multisite system, maybe there will be a feature to configure it, but that's may be.

# Basic Methods
- *get_login_links()* -  Returns a list (array) of login/auth links (to login using google/facebook/vk), so you can use them in your custom form;
- *render_form($echo = true)* - Returns/renders a simple form that includes links to login/singup using Google,Facebook,Vkontakte(VK);

# Filters
- *social-login_facebook-scope* - a list(string with commas) of scopes(parameters of user) to retrieve from Facebook;
- *social-login_google-scope* - a list(string with commas) of scopes(parameters of user) to retrieve from Google;
- *social-login_vkontakte-scope* - a list(string with commas) of scopes(parameters of user) to retrieve from Vkontakte;

<code>
  apply_filters('social-login_google-scope','https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile');
</code>

# Screenshots
- Default login form
![enter image description here](https://i.imgur.com/vW2GRag.png)

- Settings Page
![enter image description here](https://i.imgur.com/tkjnEr3.png)

![enter image description here](https://i.imgur.com/EydywLY.png)
