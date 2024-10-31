<?php 
    session_start();
    if(isset($_SESSION['usuario'])){
        
 ?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="../css/personal.css">
    <title>inicio</title>
    <?php require_once "menu.php"; ?>

</head>
<body>





</body>
</html>
<?php 
    }else{
        header("location:../index.php");
    }
 ?>