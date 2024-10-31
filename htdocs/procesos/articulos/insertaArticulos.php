<?php 
	session_start();
	$iduser=$_SESSION['iduser'];
	require_once "../../clases/Conexion.php";
	require_once "../../clases/Articulos.php";

	$obj= new articulos();

	$datos=array();
	
	$nombreImg=$_FILES['imagen']['name'];
	$rutaAlmacenamiento=$_FILES['imagen']['tmp_name'];
	$carpeta='../../archivos/';
	$rutaFinal=$carpeta.$nombreImg;

	$datosImg=array(
		$_POST['categoriaSelect'],
		$nombreImg,
		$rutaFinal
					);

		if(move_uploaded_file($rutaAlmacenamiento, $rutaFinal)){
				$idimagen=$obj->agregaImagen($datosImg);

				if($idimagen > 0){

					$datos[0]=$_POST['categoriaSelect'];
					$datos[1]=$idimagen;
					$datos[2]=$iduser;
					$datos[3]=$_POST['nombre'];
					echo $obj->insertaArticulo($datos);
				}else{
					echo 0;
				}
		}

		// Código para insertar el artículo

if ($resultado) {

    echo 1; // Éxito

} else {

    echo 2; // Error

}


$c = new conectar();

$conexion = $c->conexion();


$nombre = $_POST['nombre'];

$categoriaSelect = $_POST['categoriaSelect'];

// Manejo de la imagen

$imagen = $_FILES['imagen']['name'];

$ruta = "../images/" . $imagen;

move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta);


$sql = "INSERT INTO articulos (nombre, id_categoria, imagen) VALUES ('$nombre', '$categoriaSelect', '$imagen')";

$result = mysqli_query($conexion, $sql);


if ($result) {

    echo 1; // Inserción exitosa

} else {

    echo 0; // Inserción fallida

}

 ?>