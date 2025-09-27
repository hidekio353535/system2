<?php
session_start();
if(!empty($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    $myPath = __FILE__;                              //  /home/php/basic/test.php
    $dirname = pathinfo($myPath, PATHINFO_DIRNAME);  //  $dirname => '/home/php/basic'
    $upload_dir = $dirname . "/uploads/".$id."/";
    $upload_url = dirname($_SERVER["SCRIPT_NAME"]) . "/uploads/".$id. "/";
    
    $_SESSION['upload_dir'] = $upload_dir;
    $_SESSION['upload_url'] = $upload_url;    
}
else {
    echo "illigal parm";
    exit;
}
  
?>

<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>jQuery File Upload Example</title>
</head>
<body>
    
<?php    
    //echo "upload_dir:".$upload_dir;
    //echo "<br>";
    //echo "upload_url:".$upload_url;
?>
<input id="fileupload" type="file" name="files[]" data-url="uploads/" multiple>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="../js/vendor/jquery.ui.widget.js"></script>
<script src="../js/jquery.iframe-transport.js"></script>
<script src="../js/jquery.fileupload.js"></script>
<script>
$(function () {
    $('#fileupload').fileupload({
        dataType: 'json',
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                $('<p></p>').text(file.name).appendTo(document.body);
            });
        }
    });
});
</script>
</body> 
</html>