<?php 

session_start();


if(!isset($_SESSION['iduser'])) {

    echo "0";

    exit;

}


require_once "../../clases/Conexion.php";

require_once "../../clases/Articulos.php";


// Verificar que se recibieron todos los datos necesarios

if(!isset($_POST['categoriaSelect']) || !isset($_POST['nombre']) || !isset($_FILES['imagen'])) {

    echo "0";

    exit;

}


try {

    $obj = new articulos();

    $c = new conectar();

    $conexion = $c->conexion();

    

    // Procesar la imagen

    $nombreImg = uniqid() . "_" . $_FILES['imagen']['name']; // Nombre único

    $rutaTemp = $_FILES['imagen']['tmp_name'];

    $carpeta = '../../archivos/';

    $rutaFinal = $carpeta . $nombreImg;


    // Verificar que el directorio existe y tiene permisos

    if(!file_exists($carpeta)) {

        mkdir($carpeta, 0777, true);

    }


    // Verificar el tipo de archivo

    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];

    if(!in_array($_FILES['imagen']['type'], $tiposPermitidos)) {

        echo "0"; // Tipo de archivo no permitido

        exit;

    }


    // Mover el archivo

    if(move_uploaded_file($rutaTemp, $rutaFinal)) {

        // Comenzar transacción

        mysqli_begin_transaction($conexion);


        // Datos de la imagen

        $datosImg = array(

            $_POST['categoriaSelect'],

            $nombreImg,

            $rutaFinal

        );


        // Insertar imagen

        $idimagen = $obj->agregaImagen($datosImg);


        if($idimagen > 0) {

            // Datos del artículo

            $datos = array(

                $_POST['categoriaSelect'],

                $idimagen,

                $_SESSION['iduser'],

                $_POST['nombre']

            );


            // Insertar artículo

            if($obj->insertaArticulo($datos)) {

                mysqli_commit($conexion);

                echo "1"; // Éxito

            } else {

                throw new Exception("Error al insertar artículo");

            }

        } else {

            throw new Exception("Error al agregar imagen");

        }

    } else {

        throw new Exception("Error al mover el archivo");

    }


} catch(Exception $e) {

    // Si hay error, hacer rollback

    if(isset($conexion)) {

        mysqli_rollback($conexion);

    }

    

    // Si se subió la imagen, intentar eliminarla

    if(isset($rutaFinal) && file_exists($rutaFinal)) {

        unlink($rutaFinal);

    }

    

    error_log("Error en insertaArticulos.php: " . $e->getMessage());

    echo "0";

}

?>