jQuery(document).ready(function($){
	$(".buzzroll_comments_load").bind("click", function(e) {
		var page = $(".buzzroll_comments_load_page").val();
		if(page) {
			var link = $("#buzzroll_current_link").val();
			var blog_url = $("#buzzroll_blog_url").val();
			$.post(blog_url+"/wp-content/plugins/buzz-roll/buzzroll_ajax.php", {buzzroll_comments_page:page, buzzroll_link:link}, function(data) {
				$(".buzzroll_comments_load_page_box").replaceWith(data);
				var page_upd = $("#buzzroll_comments_load_page").val();
				if (page_upd === undefined) {
					$(".buzzroll_comments_load_button").hide();
				}
		    });
		    return false;
		} 
	});
	
	$(".buzzroll_icon_link").each(function(){
		$(this).bind("click", function(e) {
			var w = 600, h = 250;
	        var left = (screen.width/2)-(w/2);
	        var top = (screen.height/2)-(h/2);
			link = $(this).attr('href');
			newwindow=window.open(link,'buzzroll','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=716, height=480, top='+top+', left='+left);
			if (window.focus) {
				newwindow.focus();
			}
			return false;	
		});
	});
	
	$("#buzzroll_buzzroll_comments_loc").bind("change", function(e) {
		if($(this).val() == 'comment_form') {
			$("#buzzroll_buzzroll_comments_loc_box").append('<div id="buzzroll_buzzroll_comments_loc_box_msg" class="msg_error_regular">This option might not work if you have <a target="_blank" href="http://intensedebate.com/">IntenseDebate</a> or <a target="_blank" href="http://disqus.com/">Disqus</a> plugin installed</div>');
		} else {
			$("#buzzroll_buzzroll_comments_loc_box_msg").remove();
		}
	});	
})