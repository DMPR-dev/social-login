jQuery(function(){
	if(window !== window.parent)
	{
		/*
			We are in iframe
		*/
		jQuery(".social-login-link").attr("target","_blank");
		/*
			Define that we need to wait for redirect URL
		*/
		localStorage.setItem('social-login-redirect-needed', true);
		/*
			Add local storage change event handler
		*/
		window.addEventListener('storage', local_storage_redirect_handler);
	}
});

function local_storage_redirect_handler(e)
{
	if(e.key)
	{
		if(e.key == "social-login-redirect-link" && e.newValue && e.newValue.Length > 0)
		{
			localStorage.setItem('social-login-redirect-needed', false);
			window.location.href = e.newValue;
		}
	}
}