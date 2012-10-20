/****************
 * System name: TransBox
 * Module: Login JS file
 * Functional overview: This file contains code for login js file 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/
$(function(){
	$("#logindialog").dialog({
		onBeforeClose: function(){
			return false;
		}
	});
	
	$("#login").click(function(){
		if ($("#logindialogfrm").form("validate")) {
			$.post("index.php?action=login",{
				email: $("#username").val(),
				password: $("#password").val()
			},function(data){
				if (processResponse(data)) {
					window.location.reload();
				}
			});
		}
	});
	
	$("#logindialogfrm").submit(function(){
		$("#login").click();
		return false;
	});
});