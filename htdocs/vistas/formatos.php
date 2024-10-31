<?php 
session_start();
if(isset($_SESSION['usuario'])){
    require_once "../clases/Conexion.php";
    $c = new conectar();
    $conexion = $c->conexion();

    // Procesar eliminación
    if(isset($_POST['eliminar'])){
        $id_imagen = $_POST['imagen_eliminar'];
        $sql = "DELETE FROM imagenes_subidas WHERE id = '$id_imagen'";
        if(mysqli_query($conexion, $sql)){
            header("Location: ".$_SERVER['PHP_SELF']."?status=deleted");
            exit();
        } else {
            header("Location: ".$_SERVER['PHP_SELF']."?status=error");
            exit();
        }
    }

    // Procesar subida de imagen
    if(isset($_POST['submit'])){
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $imagen = $_FILES['imagen'];
        
        if($imagen['error'] == 0){
            // Procesar y redimensionar la imagen antes de guardarla
            $imagen_temporal = imagecreatefromstring(file_get_contents($imagen['tmp_name']));
            $ancho_nuevo = 60;
            $alto_nuevo = 60;
            
            $imagen_redimensionada = imagecreatetruecolor($ancho_nuevo, $alto_nuevo);
            
            // Obtener dimensiones originales
            $ancho_original = imagesx($imagen_temporal);
            $alto_original = imagesy($imagen_temporal);
            
            // Redimensionar
            imagecopyresampled(
                $imagen_redimensionada, 
                $imagen_temporal, 
                0, 0, 0, 0, 
                $ancho_nuevo, 
                $alto_nuevo, 
                $ancho_original, 
                $alto_original
            );
            
            // Guardar la imagen redimensionada
            ob_start();
            imagejpeg($imagen_redimensionada);
            $imagen_data = ob_get_contents();
            ob_end_clean();
            
            // Liberar memoria
            imagedestroy($imagen_temporal);
            imagedestroy($imagen_redimensionada);
            
            $imagen_data = mysqli_real_escape_string($conexion, $imagen_data);
            $tipo_imagen = 'image/jpeg'; // Forzamos el tipo a JPEG
            $id_usuario = $_SESSION['iduser'];

            $sql = "INSERT INTO imagenes_subidas (id_usuario, nombre, imagen_data, tipo_imagen) VALUES ('$id_usuario', '$nombre', '$imagen_data', '$tipo_imagen')";
            
            if(mysqli_query($conexion, $sql)){
                header("Location: ".$_SERVER['PHP_SELF']."?status=success");
                exit();
            } else {
                header("Location: ".$_SERVER['PHP_SELF']."?status=error");
                exit();
            }
        } else {
            header("Location: ".$_SERVER['PHP_SELF']."?status=error");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Formatos</title>
    <?php require_once "menu.php"; ?>
    <link rel="stylesheet" type="text/css" href="../css/personal.css">
    <style>
        .container {
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn-group {
            display: flex;
            gap: 5px;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        .imagen-miniatura {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .upload-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="upload-section">
            <h2>Subir Formato</h2>
            <form id="uploadForm" action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nombre">Nombre del Formato:</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="imagen">Seleccionar Imagen:</label>
                    <input type="file" class="form-control-file" id="imagen" name="imagen" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-primary" name="submit">Subir Formato</button>
            </form>
        </div>

        <div class="table-section">
            <h2>Formatos Disponibles</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Formato</th>
                        <th>Vista Previa</th>
                        <th>Fecha de Subida</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, nombre, fecha_subida FROM imagenes_subidas ORDER BY fecha_subida DESC";
                    $resultado = mysqli_query($conexion, $sql);
                    while($fila = mysqli_fetch_assoc($resultado)){
                        echo "<tr>";
                        echo "<td>".$fila['id']."</td>";
                        echo "<td>".$fila['nombre']."</td>";
                        echo "<td><img src='mostrar_imagen.php?id=".$fila['id']."' alt='".$fila['nombre']."' class='imagen-miniatura'></td>";
                        echo "<td>".$fila['fecha_subida']."</td>";
                        echo "<td>
                                <div class='btn-group'>
                                    <form action='' method='POST' style='display: inline-block;'>
                                        <input type='hidden' name='imagen_eliminar' value='".$fila['id']."'>
                                        <button type='submit' class='btn btn-danger btn-sm' name='eliminar' onclick='return confirm(\"¿Está seguro de eliminar este formato?\")'>
                                            <span class='glyphicon glyphicon-trash'></span> Eliminar
                                        </button>
                                    </form>
                                    <button type='button' class='btn btn-primary btn-sm' onclick='imprimirImagen(".$fila['id'].", \"".htmlspecialchars($fila['nombre'])."\")'>
                                        <span class='glyphicon glyphicon-print'></span> Imprimir
                                    </button>
                                </div>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function imprimirImagen(id, nombre) {
        let ventanaImpresion = window.open('', '_blank');
        
        ventanaImpresion.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Imprimir ${nombre}</title>
                <style>
                    body {
                        margin: 0;
                        padding: 20px;
                        text-align: center;
                    }
                    img {
                        max-width: 100%;
                        height: auto;
                    }
                    .header {
                        margin-bottom: 20px;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h2>${nombre}</h2>
                    <p>Fecha de impresión: ${new Date().toLocaleDateString()}</p>
                </div>
                <img src="mostrar_imagen.php?id=${id}" alt="${nombre}">
            </body>
            </html>
        `);
        
        ventanaImpresion.document.close();
        ventanaImpresion.onload = function() {
            ventanaImpresion.print();
        }
    }

    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        
        if(status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Formato agregado correctamente'
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        } else if(status === 'error') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un error al procesar su solicitud'
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        } else if(status === 'deleted') {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Formato eliminado correctamente'
            }).then(() => {
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    });
    </script>
</body>
</html>

<?php 
} else {
    header("location:../index.php");
}
?>