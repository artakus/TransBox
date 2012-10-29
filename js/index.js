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
	clearTimeout(reloadTimer);
	if ($("#autoReload:checked").length == 0)
		return;
	if (typeof $("#torrentTable").datagrid != "function")
		return;
	if ($("#torrentTable").datagrid("getRows").length == 0)
		return;
	var options = $("#torrentTable").datagrid("options");
	$.get("?action=getTorrentsStats",{
		page: options.pageNumber,
		row: options.pageSize
	},function(data) {
		var res = processResponse(data,true);
		if (!res) {
			$.messager.show({
				title: "Warning",
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

function control(obj){
	var $this = $(obj);
	var id = parseInt($this.attr("tid"),10);
	var hash = $this.attr("hash");
	var control = $this.attr("control");
	var param = {
		oper: control,
		hash: JSON.stringify([hash]),
		id: JSON.stringify([id])
	}
	switch(control) {
		case "download":
			$.post("?action=setTorrent",param,function(data){
				var res = processResponse(data);
				if (!res)
					return;
				if (typeof res.url != "undefined" && res.url.length > 0) {
					window.open(res.url,"_blank");
				}
			});
			break;
		case "delete":
			$.messager.confirm("Delete file","Are you sure want to delete this torrent? The torrent data will be also deleted",function(r){
				if (r) {
					$this.attr("src","images/snake.gif");
					$.post("?action=setTorrent",param,function(data){
						var res = processResponse(data);
						if (!res)
							return;
						setTimeout('$("#torrentTable").datagrid("reload")',500);
						if (res.msg.length > 0) {
							$.messager.show({
								title: "Warning",
								msg: res.msg,
								timeout: 2000
							});
						}
					});
				}
			});
			break;
		case "start":
			$this.attr("src","images/snake.gif");
			$.post("?action=setTorrent",param,function(data){
				var res = processResponse(data);
				if (!res)
					return;
				setTimeout('$("#torrentTable").datagrid("reload")',1000);
			});
			break;
		case "stop":
			$this.attr("src","images/snake.gif");
			$.post("?action=setTorrent",param,function(data){
				var res = processResponse(data);
				if (!res)
					return;
				setTimeout('$("#torrentTable").datagrid("reload")',5000);
			});
			break;
	}
}

function fileControl(obj){
	var $this = $(obj);
	var path = $this.attr("path");
	var control = $this.attr("control");
	var param = {
		path: path,
		oper: control
	}
	switch(control) {
		case "download":
			$.post("?action=setFile",param,function(data){
				var res = processResponse(data);
				if (!res)
					return;
				if (typeof res.url != "undefined" && res.url.length > 0) {
					window.open(res.url,"_blank");
				}
			});
			break;
		case "delete":
			$.messager.confirm("Delete file","Are you sure want to delete this file?",function(r){
				if (r) {
					$.post("?action=setFile",param,function(data){
						var res = processResponse(data);
						if (!res)
							return;
						setTimeout('$("#fileTable").datagrid("reload")',500);
						if (res.msg.length > 0) {
							$.messager.show({
								title: "Warning",
								msg: res.msg,
								timeout: 2000
							});
						}
					});
				}
			});
			break;
		case "zip":
			$.messager.confirm("Zip file","Are you sure want to zip this folder? It may take long time.",function(r){
				if (r) {
					$.messager.progress({
						title: "Zip folder",
						msg: "Zipping the folder",
						text: "Please wait..."
					});
					timeOut = 120000;
					$.post("?action=setFile",param,function(data){
						var res = processResponse(data);
						if (!res)
							return;
						setTimeout('$("#fileTable").datagrid("reload")',500);
						timeOut = 10000;
					});
				}
			});
			break;
	}
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
		pageList: [20,50,100],
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
				formatter: function(d,rd) {
					return "<div style='padding-top:2px;'><img class='control' control='download' tid='"+rd.id+"' hash='"+rd.hash+"' onclick='control(this)' src='css/famfam/disk.png'>&nbsp;"+
					"<img class='control' control='delete' tid='"+rd.id+"' hash='"+rd.hash+"' src='css/famfam/delete.png' onclick='control(this)'>&nbsp;"+
					"<img class='control' control='start' tid='"+rd.id+"' hash='"+rd.hash+"' src='css/famfam/play.png' onclick='control(this)'>&nbsp;"+
					"<img class='control' control='stop' tid='"+rd.id+"' hash='"+rd.hash+"' src='css/famfam/stop.png' onclick='control(this)'></div>";
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
					if (parseInt(rd.stopped,10) == 1)
						d = 0;
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
		]],
		onLoadSuccess: reloadTable
	});
	
	$("#fileTable").datagrid({
		url: "?action=getFiles",
		method: "get",
		striped: true,
		pagination: true,
		fit: true,
		toolbar: "#fileTableTb",
		checkOnSelect: false,
		singleSelect: true,
		pageList: [30,50,100],
		columns:[[
			{ 	field: "path",
				checkbox: true
			},
			{
				field: "control",
				title: lang.control,
				width: 100,
				align: "center",
				sortable: false,
				formatter: function(d,rd) {
					if (rd.icon == "folder") {
						return "<div style='padding-top:2px;'><img class='control' control='delete' path='"+rd.fullpath+"' src='css/famfam/delete.png' onclick='fileControl(this)'>&nbsp;"+
						"<img class='control' control='zip' path='"+rd.fullpath+"' src='css/famfam/page_white_compressed.png' onclick='fileControl(this)'></div>";
					} else {
						return "<div style='padding-top:2px;'><img class='control' control='delete' path='"+rd.fullpath+"' src='css/famfam/delete.png' onclick='fileControl(this)'>&nbsp;"+
						"<img class='control' control='download' path='"+rd.fullpath+"' onclick='fileControl(this)' src='css/famfam/disk.png'></div>";
					}
					
				}
			},
			{
				field: "name",
				title: lang.name,
				width: 500,
				align: "left",
				sortable: true,
				formatter: function(d,rd) {
					return "<img src='css/famfam/"+rd.icon+".png' class='fileicon'>&nbsp;"+d;
				}
			},
			{
				field: "type",
				title: lang.type,
				width: 100,
				align: "center",
				sortable: true
			},
			{
				field: "size",
				title: lang.size,
				width: 100,
				align: "right",
				sortable: true,
				formatter: function(d,rd){
					if (rd.icon=="folder")
						return "";
					return readableFileSize(d);
				}
			}
		]],
		onDblClickRow:function(ri,rd) {
			var ft = $('#folderList');
			var node = ft.tree('find', rd.fullpath);
			if (node) {
				ft.tree('expandTo', node.target);
				ft.tree('select', node.target);
			} else {
				var r = ft.tree("getSelected");
				if (r == null)
					r = ft.tree("getRoot");
				ft.tree("expand",r.target);
				node = ft.tree('find', rd.fullpath);
				if (node) {
					ft.tree('select', node.target);
				}
			}
			$("#fileTable").datagrid("load", {
				path: rd.fullpath
			});
		}
	});
	
	if (isAdmin) {
		$("#userTable").datagrid({
			url: "?action=getUsers",
			method: "get",
			striped: true,
			pagination: true,
			fit: true,
			toolbar: "#userTableTb",
			checkOnSelect: false,
			singleSelect: true,
			pageList: [30,50,100],
			columns:[[
				{
					field: "control",
					title: lang.control,
					width: 100,
					align: "center",
					formatter: function(d,rd) {
						return "<div style='padding-top:2px;'><img class='control' control='reset' uid='"+rd.id+"' onclick='userControl(this)' src='css/famfam/database_lightning.png'>&nbsp;"+
						"<img class='control' control='delete' uid='"+rd.id+"' onclick='userControl(this)' src='css/famfam/user_delete.png' onclick='control(this)'>&nbsp;"+
						"<img class='control' control='edit' uid='"+rd.id+"' onclick='userControl(this)' src='css/famfam/user_edit.png' onclick='control(this)'></div>";
					}
				},
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
						return readableFileSize(parseInt(rd.rx_current,10) + parseInt(rd.tx_current,10));
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
	}

	
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
	
	$("#addTorrentDialog").dialog({
		onClose: function(){
			torrentUploader.clearStoredFiles();
		}
	});
	
	torrentUploader = new qq.FileUploader({
		element: $("#torrentFileUploader").get(0),
		action: "?action=addTorrent",
		//multiple: false,
		allowedExtensions: ["torrent"],
		sizeLimit: (1024*1024),
		button: $("#selectTorrentFile").get(0),
		autoUpload: false,
		showMessage: function(m) {
			$.messager.alert(lang.addtorrent,m);
		},
		onComplete: function(id,fn,data) {
			if (torrentUploader.getInProgress() == 0) {
				$("#torrentTable").datagrid("reload");
				setTimeout(torrentUploader.clearStoredFiles(),2000);
			}
			//
		},
		onError: function(i,f,r) {
			$.messager.alert(lang.error,r);
		},
		params: {
			"_json": true
		}
	});
	
	
	$("#autoReload").change(function(){
		if (this.checked) {
			reloadTable();
		} else {
			clearTimeout(reloadTimer);
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
	
	$("#folderList").tree({
		loader: function(param,success,error) {
			$.get("?action=getFolder",param,function(data){
				var res = processResponse(data);
				if (!res) {
					error();
					return;
				}
				success(res.folders);					
			});
			return true;
		},
		onClick: function(node){
			$("#fileTable").datagrid("load", {
				path: node.id
			});
		}
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
});