/****************
 * System name: TransBox
 * Module: Main JS file
 * Functional overview: This file contains code for main js file 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

$(function(){
	var torrentUploader;
	
	$("#torrentTable").datagrid({
		url: "?action=getTorrents",
		striped: true,
		pagination: true,
		fit: true,
		toolbar: "#torrentTableTb",
		checkOnSelect: false,
		singleSelect: true,
		columns:[[
			{
				field: "id",
				checkbox: true
			},
			{
				field: "control",
				title: lang.control,
				width: 100,
				align: "center"
			},
			{
				field: "name",
				title: lang.name,
				width: 300,
				align: "left",
				sortable: true
			},
			{
				field: "size",
				title: lang.size,
				width: 100,
				align: "right",
				sortable: true,
				formatter: readableFileSize
			},
			{
				field: "added_date",
				title: lang.addeddate,
				width: 100,
				align: "center",
				sortable: true
			},
			{
				field: "status",
				title: lang.status,
				width: 100,
				align: "center",
				formatter: function(d){
					switch(d){
						case 1:
						return "check pending";
						case 2:
						return "checking";
						case 4:
						return "downloading";
						case 6: 
						return "seeding";
						case 8: 
						return "stopped";
					}
				}
			},
			{
				field: "percentage",
				title: lang.percentage,
				width: 100,
				align: "right", 
				formatter: function(d){
					var p =parseFloat(d);
					return (p * 100).toFixed(1) + "%";
				}
			},
			{
				field: "up_speed",
				title: lang.up_speed,
				width: 100,
				align: "right",
				formatter: function(d){
					var p = parseInt(d,10);
					if (p > 0) {
						return (p/1024).toFixed(1) + " kBps";	
					}
					return "0 kBps";
				}
			},
			{
				field: "down_speed",
				title: lang.down_speed,
				width: 100,
				align: "right",
				formatter: function(d){
					var p = parseInt(d,10);
					if (p > 0) {
						return (p/1024).toFixed(1) + " kBps";	
					}
					return "0 kBps";
				}
			},
			{
				field: "ratio",
				title: lang.ratio,
				width: 100,
				align: "right",
				formatter: function(d){
					var p = parseFloat(d);
					if (p > 0) {
						return p.toFixed(2);	
					}
					return "0";
				}
			}
		]]
	})
	
	$("#torrentTableAdd").click(function(){
		$("#addTorrentDialogFrm").get(0).reset();
		$("#addTorrentDialog").dialog("open");
	});
	
	$("#addTorrentDialogBtnAdd").click(function(){
		log(torrentUploader);
		var frm = $("#addTorrentDialogFrm");
		var obj = frm.serializeObject();
		if (typeof obj.url != "undefined" && $.trim(obj.url) != "") {
			$.post("?action=addTorrent", {
				url: obj.url
			},function (data){
				var res = processResponse(data);
				if (!res)
					return;
				frm.get(0).reset();
			});
		} else {
			torrentUploader.uploadStoredFiles();
		}
		
		$("#addTorrentDialog").dialog("open");
	});
	
	$("#addTorrentDialogBtnCnl").click(function(){
		$("#addTorrentDialog").dialog("close");
	});
	
	torrentUploader = new qq.FileUploader({
		element: $("#torrentFileUploader").get(0),
		action: "?action=addTorrent",
		multiple: false,
		allowedExtensions: ["torrent"],
		sizeLimit: (1024*1024),
		button: $("#selectTorrentFile").get(0),
		autoUpload: false,
		showMessage: function(m) {
			$.messager.alert(lang.addtorrent,m);
		},
		onComplete: function(id,fn,data) {
			var res = processResponse(data);
			if (!res)
				return;
			frm.get(0).reset();
		},
		onError: function(i,f,r) {
			$.messager.alert(lang.error,r);
		}
	});
	
});