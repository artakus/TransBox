/****************
 * System name: TransBox
 * Module: Main JS file
 * Functional overview: This file contains code for main js file 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

var reloadTimer;

function reloadTable(){
	if ($("#autoReload:checked").length == 0)
		return;
	var options = $("#torrentTable").datagrid("options");
	$.get("?action=getTorrentsStats",{
		page: options.pageNumber,
		row: options.pageSize
	},function(data) {
		var res = processResponse(data,true);
		if (!res) {
			$.messager.show({
				msg: "Failed to get torrent stats",
				timeout: 2000
			});
			return;
		}
		if (typeof res.torrents != "undefined" && $.isArray(res.torrents)) {
			//compact("status","ratio","percentage","up_speed","down_speed");
			$.each(res.torrents,function(i,v){
				var t = $("span[tid='"+v.id+"']");
				t.filter(".tStatus").text(lang.tstatus[v.status]);
				t.filter(".tPercent").text((v.percentage * 100).toFixed(1));
				t.filter(".sUSpeed").text((v.up_speed < 1 ? "0": (v.up_speed/1024).toFixed(1)));
				t.filter(".tDSpeed").text((v.down_speed < 1 ? "0": (v.down_speed/1024).toFixed(1)));
				t.filter(".tRatio").text(v.ratio.toFixed(2));
			});
		}
	});
	reloadTimer = setTimeout("reloadTable()",5000);
}



$(function(){
	var torrentUploader;
	
	$("#torrentTable").datagrid({
		url: "?action=getTorrents",
		method: "get",
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
				align: "center",
				formatter: function(d,ri,rd) {
					return "<div style='padding-top:2px;'><img class='contolBtn' control='download' tid='"+rd.id+"' trid='"+rd.tid+"' src='css/famfam/disk.png'>&nbsp;"+
					"<img class='contolBtn' control='delete' tid='"+rd.id+"' trid='"+rd.tid+"' src='css/famfam/delete.png'>&nbsp;"+
					"<img class='contolBtn' control='start' tid='"+rd.id+"' trid='"+rd.tid+"' src='css/famfam/play.png'>&nbsp;"+
					"<img class='contolBtn' control='stop' tid='"+rd.id+"' trid='"+rd.tid+"' src='css/famfam/stop.png'></div>";
				}
			},
			{
				field: "email",
				title: lang.user,
				width: 200,
				align: "left",
				sortable: true,
				hidden: !isAdmin
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
				formatter: function(d,rd){
					return "<span tid='"+rd.id+"' class='tStatus'>"+lang.tstatus[d]+"</span>";
				}
			},
			{
				field: "percentage",
				title: lang.percentage,
				width: 100,
				align: "right", 
				formatter: function(d,rd){
					var p =parseFloat(d);
					return "<span tid='"+rd.id+"' class='tPercent'>"+(p * 100).toFixed(1) + "</span>%";
				}
			},
			{
				field: "up_speed",
				title: lang.up_speed,
				width: 100,
				align: "right",
				formatter: function(d,rd){
					var p = parseInt(d,10);
					if (p > 0) {
						return "<span tid='"+rd.id+"' class='tUSpeed'>"+(p/1024).toFixed(1) + "</span> kBps";	
					}
					return "<span tid='"+rd.id+"' class='tUSpeed'>0</span> kBps";
				}
			},
			{
				field: "down_speed",
				title: lang.down_speed,
				width: 100,
				align: "right",
				formatter: function(d,rd){
					var p = parseInt(d,10);
					if (p > 0) {
						return "<span tid='"+rd.id+"' class='tDSpeed'>"+(p/1024).toFixed(1) + "</span> kBps";
					}
					return "<span tid='"+rd.id+"' class='tDSpeed'>0</span> kBps";
				}
			},
			{
				field: "ratio",
				title: lang.ratio,
				width: 100,
				align: "right",
				formatter: function(d,rd){
					var p = parseFloat(d);
					if (p > 0) {
						return "<span tid='"+rd.id+"' class='tRatio'>"+p.toFixed(2)+"</span>";
					}
					return "<span tid='"+rd.id+"' class='tRatio'>0</span>";
				}
			}
		]]
	});
	
	
	$("#userTable").datagrid({
		url: "?action=getUsers",
		method: "get",
		striped: true,
		pagination: true,
		fit: true,
		toolbar: "#userTableTb",
		checkOnSelect: false,
		singleSelect: true,
		columns:[[
			{
				field: "email",
				title: lang.email,
				width: 300,
				align: "left",
				sortable: true
			},
			{
				field: "ds_limit",
				title: lang.dslimit,
				width: 100,
				align: "right",
				sortable: true,
				formatter: readableFileSize
			},{
				field: "ds_current",
				title: lang.dscurrent,
				width: 100,
				align: "right",
				sortable: true,
				formatter: readableFileSize
			},{
				field: "xfer_limit",
				title: lang.xferlimit,
				width: 100,
				align: "right",
				sortable: true,
				formatter: readableFileSize
			},{
				field: "xfer_current",
				title: lang.xfercurrent,
				width: 100,
				align: "right",
				sortable: true,
				formatter: function(d,rd) {
					return readableFileSize(rd.rx_current + rd.tx_current);
				}
			},{
				field: "rx_limit",
				title: lang.rxlimit,
				width: 100,
				align: "right",
				sortable: true,
				formatter: readableFileSize
			},{
				field: "rx_current",
				title: lang.rxcurrent,
				width: 100,
				align: "right",
				sortable: true,
				formatter: readableFileSize
			},{
				field: "tx_limit",
				title: lang.txlimit,
				width: 100,
				align: "right",
				sortable: true,
				formatter: readableFileSize
			},{
				field: "tx_current",
				title: lang.txcurrent,
				width: 100,
				align: "right",
				sortable: true,
				formatter: readableFileSize
			}
		]]
	});
	
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
				$("#torrentTable").datagrid("reload");
			});
		} else {
			torrentUploader.uploadStoredFiles();
		}
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
			$("#torrentTable").datagrid("reload");
			torrentUploader.clearStoredFiles();
		},
		onError: function(i,f,r) {
			$.messager.alert(lang.error,r);
		}
	});
	
	
	$("#autoReload").change(function(){
		if (this.checked) {
			reloadTable();
		} else {
			clearTimeout(reloadTimer);
		}
	});
	
	$("img.controlBtn").live("click",function(){
		var $this = $(this);
		var id = $this.attr("tid");
		var tid = $this.attr("trid");
		var control = $this.attr("control");
		
		switch(control) {
			case "download":
				break;
			case "delete":
				break;
			case "start":
				break;
			case "stop":
				break;
		}
	});
	
	$("#userTableAdd").click(function(){
		$("#addUserDialogFrm").get(0).reset();
		$("#addUserDialog").dialog("open");
	});
	
	$("#userTableEdit").click(function(){
		$("#addUserDialogFrm").get(0).reset();
		$("#addUserDialog").dialog("open");
	});
	
	$("#userTableDel").click(function(){

	});
	
	$("#addUserDialogBtnAdd").click(function(){
		var frm = $("#addUserDialogFrm");
		if (frm.form("validate")) {
			var obj = frm.serializeObject();
			$.post("?action=addUser", obj ,function (data){
				var res = processResponse(data);
				if (!res)
					return;
				frm.get(0).reset();
				$("#userTable").datagrid("reload");
				$("#addUserDialog").dialog("close");
			});
		}
	});
	
	$("#addUserDialogBtnCnl").click(function(){
		$("#addUserDialog").dialog("close");
	});
	
	
	$("#mainTabPanel").tabs({
		onSelect: function(t,i) {
			switch (i) {
				case 0:
					reloadTable();
				break;
				default:
					clearTimeout(reloadTimer);
				break;
			}
		}
	});
	if (!isAdmin) {
		$("#mainTabPanel").tabs("close",2);
	}
});