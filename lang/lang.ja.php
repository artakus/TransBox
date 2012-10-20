<?php
/****************
 * System name: TransBox
 * Module: Japanese language file
 * Functional overview: This file contains variable that editable to adapt the language. 
 * Last update: 2012/10/27
 * Author： Artakus
 * © 2012 Artakus. All Rights Reserved. GPL
 * *************/

if (!defined("TRANSBOX")) {
	header('HTTP/1.1 403 Forbidden');
	die("Forbidden");
}
	
// language setting

$lang['verno'] = $version;
$lang['pageTitle'] = "Cerdas 7";
$lang['logout'] = "ログアウト";
$lang['error'] = "エラー";
$lang['record'] = "録画";
$lang['login'] = "ログイン";
$lang['username'] = "ユーザ名";
$lang['password'] = "パスワード";
$lang['badpassword'] = "パスワードが間違いました";
$lang['stayLoggedIn'] = "ログインデータを保存";
$lang['ok'] = "ＯＫ";
$lang['cancel'] = "取り消し";
$lang['date'] = "日付";
$lang['setting'] = "設定";
$lang['startTime'] = "開始時刻";
$lang['endTime'] = "終了時刻";
$lang['playback'] = "再生";
$lang['schedule'] = "スケジュール";
$lang['template'] = "テンプレート";
$lang['templateSelector'] = "テンプレート選択";
$lang['scheduleEditor'] = "スケジュールエディター";
$lang['control'] = "操作";
$lang['startRecording'] = "記録開始";
$lang['stopRecording'] = "記録停止";
$lang['timecode'] = "タイムコード";
$lang['chapter'] = "チャプター";
$lang['autoChapter'] = "自動チャプター";
$lang['comment'] = "コメント";
$lang['startChapter'] = "チャｐたー開始";
$lang['stopChapter'] = "チャプター終了";
$lang['fileSuffix'] = "ファイルサフィクス ";
$lang['filePath'] = "ファイルパス";
$lang['uploader'] = "アップローダ";
$lang['defaultTemplate'] = "デフォルトのテンプレート";
$lang['defaultSource'] = "デフォルトのカメラ";
$lang['mail'] = "メール";
$lang['commentName'] = "コメントのイベント名";
$lang['commentValues'] = "コメントの値";
$lang['add'] = "追加";
$lang['remove'] = "削除";
$lang['selectPath'] = "パスを選択";
$lang['duration'] = "デュレイション";
$lang['source'] = "カメラ";
$lang['useAutoRecord'] = "自動録画を使用";
$lang['privilegeEditor'] = "動画権限エディター";
$lang['groups'] = "グループ";
$lang['abandon'] = "放棄";
$lang['plsSelTmpl'] = "テンプレートを選択してください";
$lang['plsSelTmpWGrp'] = "グループ権限を含まれているテンプレートを選択してください";
$lang['plsSelGrp'] = "グループを選択してください";
$lang['disAutoRec'] = "自動録画を無効化";
$lang['disAutoRecPrompt'] = "自動録画を無効化するとすべて入力されているコメントのデータが消されてしまう。無効化しますか。";
$lang['appTmpl'] = "テンプレートを適用";
$lang['appTmplPrompt'] = "テンプレートを適用すると映像情報が初期化されてしまう。適用しますか。";
$lang['remComment'] = "コメントを削除";
$lang['remCommentPrompt'] = "選択されているコメントを削除しますか";
$lang['failedAddSche'] = "当たらしスケジュールを追加失敗しました。";

$lang['video_status'] = array(
	"録画していない",
	"録画を開始中",
	"録画中...",
	"録画を開始失敗",
	"録画を停止中",
	"録画を停止した",
	"録画を停止失敗"
);


$lang['sessTimeOut'] = "セッションが切れました。 再ログインしてください";

$lang['weekDays'] = array("日","月","火","水","木","金","土");
$lang['monthName'] = array("1月","2月","3月","4月","5月","6月","7月","8月","9月","10月","11月","12月");