function open_tab(initiator)
{
	var id = jQuery(initiator).attr("id");
	jQuery(".container-tab-contents").hide();
	jQuery(initiator).parent().parent().find("li").removeClass("active");

	jQuery(initiator).parent().addClass("active");
	jQuery(".container-tab-contents[opens-by="+id+"]").show();
}

var names = php_vars.names;
jQuery(document).ready(function(){
	var intervals = new Array(names.length);
	var editors = new Array(names.length);
	for(var i = 0; i < names.length; i++)
	{
		jQuery("#"+names[i] + '-editor').change(function(){
			parse_value();
		});
		jQuery("#"+names[i] + '-editor').keyup(function(){
			parse_value();
		});
		intervals[i] = setInterval(function(i_var){
			var my_name = names[i_var];
			editors[i_var] = tinyMCE.get(my_name + '-editor');

			if(editors[i_var] != undefined && editors[i_var] != null)
			{
				clearInterval(intervals[i_var]);
				editors[i_var].on("change",function(){
					parse_value();
 				});
 				editors[i_var].on("keyup",function(){
 					parse_value();
 				});
			}
		},500,i);
	}
	open_tab(jQuery("ul.nav.nav-tabs").find("li").first().find("a"));
});
function parse_value()
{
	for(var i = 0; i < names.length; i++)
	{
		var my_name = names[i];
		var mce = tinyMCE.get(my_name+'-editor');
		if(mce != null && mce != undefined && !mce.isHidden())
		{
			var temp = tinyMCE.get(my_name+'-editor').getContent();
			jQuery("#"+my_name + '-editor').val(temp);
		}
		var text = jQuery("#"+my_name + '-editor').val();;
        var content = btoa(unescape(encodeURIComponent(text)));
        jQuery("#"+my_name).val(content);
     }
}