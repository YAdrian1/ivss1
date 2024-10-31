<?php
require_once "../clases/Conexion.php";
$c = new conectar();
$conexion = $c->conexion();

if(isset($_GET['id'])){
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    $sql = "SELECT imagen_data, tipo_imagen FROM imagenes_subidas WHERE id = '$id'";
    $resultado = mysqli_query($conexion, $sql);
    
    if($fila = mysqli_fetch_assoc($resultado)){
        header("Content-Type: " . $fila['tipo_imagen']);
        echo $fila['imagen_data'];
    } else {
        echo "Imagen no encontrada";
    }
} else {
    echo "ID de imagen no proporcionado";
}
?>