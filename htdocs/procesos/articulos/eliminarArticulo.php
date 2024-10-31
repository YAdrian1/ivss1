<?php 

require_once "../../clases/Conexion.php";

require_once "../../clases/Articulos.php";


$obj = new articulos();

$idart = $_POST['idarticulo'];


$resultado = $obj->eliminaArticulo($idart);


echo $resultado;

?>