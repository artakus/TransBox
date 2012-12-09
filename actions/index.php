<?php
/****************
 * System name: TransBox
 * Module: Index action
 * Functional overview: This file code for action index. 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}
$obj = array();
$obj['name'] = $_SESSION['login']['email'];
$sql = "SELECT * FROM `config`";
$sth = $db->query($sql) or onError();
$_SESSION['cfg'] = $sth->fetchAll(PDO::FETCH_KEY_PAIR);
$userdialog = $usertab = "";

if ($_SESSION['login']['level'] == 1) {
	$usertab = <<<USERTAB
				<div title="<!users!>">
    				<table id="userTable"></table>
    				<div id="userTableTb">
    					<a href="#" id="userTableAdd" class="easyui-linkbutton" plain="true" iconCls="ff-user-add" title="<!adduser!>"></a>
    					<!--<a href="#" id="userTableEdit" class="easyui-linkbutton" plain="true" iconCls="ff-user-edit"></a>-->
						<!--<a href="#" id="userTableDel" class="easyui-linkbutton" plain="true"  iconCls="ff-user-delete"></a>-->
    				</div>
				</div>
USERTAB;
	$userdialog = <<<USERDIALOG
	<div id="addUserDialog" class="easyui-dialog" title="<!adduser!>" modal="true" closed="true" style="width: 300px;padding:5px;" buttons="#addUserDialogBtn">
    	<form id="addUserDialogFrm">
    		<fieldset>
    			<legend><!email!></legend>
    			<span class="combo"><input type="email" class="easyui-validatebox combo-text" style="width: 250px;" name="email" required="true" /></span>
    		</fieldset>
    		<fieldset>
    			<legend><!password!></legend>
    			<span class="combo"><input type="text" class="easyui-validatebox combo-text" style="width: 250px;" name="password" /></span>
    		</fieldset>
			<fieldset>
    			<legend><!dslimit!></legend>
    			<span class="combo"><input type="number" class="easyui-numberbox combo-text" style="width: 200px; text-align: right;" min="0" name="ds_limit" id="userFrmDsLimit"/> MB</span>
    		</fieldset>
    		<fieldset>
    			<legend><!xferlimit!></legend>
    			<span class="combo"><input type="number" class="easyui-numberbox combo-text" style="width: 180px; text-align: right;" min="0" name="xfer_limit"  id="userFrmXferLimit" /> MB/month</span>
    		</fieldset>
    		<fieldset>
    		<legend><!rxlimit!></legend>
    			<span class="combo"><input type="number" class="easyui-numberbox combo-text" style="width: 180px; text-align: right;" min="0" name="rx_limit"  id="userFrmRxLimit" /> MB/month</span>
    		</fieldset>
    		<fieldset>
    			<legend><!txlimit!></legend>
    			<span class="combo"><input type="number" class="easyui-numberbox combo-text" style="width: 180px; text-align: right;" min="0" name="tx_limit"  id="userFrmTxLimit" /> MB/month</span>
    		</fieldset>
    		<input type="hidden" name="id" value="0" />
    		<input type="hidden" name="oper" value="edit" />
    	</form>
    </div>
    <div id="addUserDialogBtn" >
    	<a href="#" id="addUserDialogBtnAdd" class="easyui-linkbutton" icon="ff-tick"><!ok!></a>
    	<a href="#" id="addUserDialogBtnCnl" class="easyui-linkbutton" icon="ff-cancel"><!cancel!></a>
    </div>
USERDIALOG;
}
$obj['usertab'] = $usertab;
$obj['userdialog'] = $userdialog;

viewHTML($obj);