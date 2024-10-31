<?php 

session_start();

if (!isset($_SESSION['usuario'])) {

    header("location:../index.php");

    exit();

}

?>


<!DOCTYPE html>

<html>

<head>

    <title>Articulos</title>

    <?php require_once "menu.php"; ?>

    <?php require_once "../clases/Conexion.php"; 

    $c = new conectar();

    $conexion = $c->conexion();

    $sql = "SELECT id_categoria, nombreCategoria FROM categorias";

    $result = mysqli_query($conexion, $sql);

    ?>

    

    <!-- CSS -->

    <link rel="stylesheet" type="text/css" href="../librerias/bootstrap/css/bootstrap.css">

    <link rel="stylesheet" type="text/css" href="../librerias/alertifyjs/css/alertify.css">

    <link rel="stylesheet" type="text/css" href="../librerias/alertifyjs/css/themes/default.css">

    

    <!-- JavaScript -->

    <script src="../librerias/jquery-3.2.1.min.js"></script>

    <script src="../librerias/bootstrap/js/bootstrap.js"></script>

    <script src="../librerias/alertifyjs/alertify.js"></script>

</head>


<body>

    <div class="container">

        <h1>Articulos</h1>

        <div class="row">

            <div class="col-sm-4">

                <form id="frmArticulos" enctype="multipart/form-data">

                    <label>Categoria</label>

                    <select class="form-control input-sm" id="categoriaSelect" name="categoriaSelect">

                        <option value="A">Selecciona Categoria</option>

                        <?php while ($ver = mysqli_fetch_row($result)): ?>

                            <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>

                        <?php endwhile; ?>

                    </select>


                    <label>Nombre</label>

                    <input type="text" class="form-control input-sm" id="nombre" name="nombre">


                    <label>Imagen</label>

                    <input type="file" id="imagen" name="imagen" accept="image/*">


                    <p></p>

                    <span id="btnAgregaArticulo" class="btn btn-primary">Agregar</span>

                </form>

            </div>


            <div class="col-sm-8">

                <div id="tablaArticulosLoad"></div>

            </div>

        </div>

    </div>


    <!-- Modal para actualizar artículo -->

    <div class="modal fade" id="abremodalUpdateArticulo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">

        <div class="modal-dialog modal-sm" role="document">

            <div class="modal-content">

                <div class="modal-header">

                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

                    <h4 class="modal-title" id="myModalLabel">Actualiza Articulo</h4>

                </div>

                <div class="modal-body">

                    <form id="frmArticulosU" enctype="multipart/form-data">

                        <input type="text" id="idArticulo" hidden="" name="idArticulo">

                        <label>Categoria</label>

                        <select class="form-control input-sm" id="categoriaSelectU" name="categoriaSelectU">

                            <option value="A">Selecciona Categoria</option>

                            <?php 

                            $sql = "SELECT id_categoria, nombreCategoria FROM categorias";

                            $result = mysqli_query($conexion, $sql);

                            ?>

                            <?php while ($ver = mysqli_fetch_row($result)): ?>

                                <option value="<?php echo $ver[0] ?>"><?php echo $ver[1]; ?></option>

                            <?php endwhile; ?>

                        </select>


                        <label>Nombre</label>

                        <input type="text" class="form-control input-sm" id="nombreU" name="nombreU">

                    </form>

                </div>

                <div class="modal-footer">

                    <button id="btnActualizaarticulo" type="button" class="btn btn-warning" data-dismiss="modal">Actualizar</button>

                </div>

            </div>

        </div>

    </div>


    <script type="text/javascript">

        $(document).ready(function(){

            $('#tablaArticulosLoad').load("formatos/formatos.php");


            $('#btnAgregaArticulo').click(function(e){

                e.preventDefault();

                

                // Validaciones

                if($('#categoriaSelect').val() === 'A'){

                    alertify.error("Debes seleccionar una categoría");

                    return false;

                }


                if($('#nombre').val().trim() === ''){

                    alertify.error("El nombre es requerido");

                    return false;

                }


                if($('#imagen').val() === ''){

                    alertify.error("Debes seleccionar una imagen");

                    return false;

                }


                // Validar tipo de archivo

                var archivo = $('#imagen')[0].files[0];

                var tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];

                if(!tiposPermitidos.includes(archivo.type)) {

                    alertify.error("Tipo de archivo no permitido. Use JPG, PNG o GIF");

                    return false;

                }


                // Mostrar loader

                alertify.message("Procesando...");


                var formData = new FormData(document.getElementById("frmArticulos"));


                $.ajax({

                    url: "../procesos/articulos/insertaArticulos.php",

                    type: "POST",

                    dataType: "text",

                    data: formData,

                    cache: false,

                    contentType: false,

                    processData: false,

                    success: function(response){

                        console.log("Respuesta:", response);

                        

                        if(response.trim() === "1"){

                            // Limpiar formulario

                            $('#frmArticulos')[0].reset();

                            

                            // Recargar tabla

                            $('#tablaArticulosLoad').load("formatos/formatos.php");

                            

                            alertify.success("Artículo agregado correctamente");

                        } else {

                            alertify.error("Error al agregar el artículo");

                        }

                    },

                    error: function(xhr, status, error){

                        console.error("Error AJAX:", error);

                        alertify.error("Error de comunicación con el servidor");

                    }

                });

            });


            $('#btnActualizaarticulo').click(function(){

                var formData = new FormData(document.getElementById("frmArticulosU"));

                

                $.ajax({

                    url: "../procesos/articulos/actualizaArticulos.php",

                    type: "POST",

                    dataType: "text",

                    data: formData,

                    cache: false,

                    contentType: false,

                    processData: false,

                    success: function(response){

                        if(response.trim() === "1"){

                            $('#tablaArticulosLoad').load("formatos/formatos.php");

                            $('#abremodalUpdateArticulo').modal('hide');

                            alertify.success("Artículo actualizado correctamente");

                        } else {

                            alertify.error("Error al actualizar el artículo");

                        }

                    },

                    error: function(){

                        alertify.error("Error de comunicación con el servidor");

                    }

                });

            });

        });


        function agregaDatosArticulo(idArticulo){

            $.ajax({

                type: "POST",

                data: "idart=" + idArticulo,

                url: "../procesos/articulos/obtenDatosArticulo.php",

                success: function(response){

                    try {

                        var datos = JSON.parse(response);

                        $('#idArticulo').val(datos.id_producto);

                        $('#categoriaSelectU').val(datos.id_categoria);

                        $('#nombreU').val(datos.nombre);

                    } catch(error) {

                        console.error("Error al parsear datos:", error);

                        alertify.error("Error al cargar los datos del artículo");

                    }

                },

                error: function(){

                    alertify.error("Error al obtener los datos del artículo");

                }

            });

        }


        // Función para eliminar artículo

        function eliminaArticulo(idArticulo){

            alertify.confirm('¿Desea eliminar este artículo?', 

                function(){ 

                    $.ajax({

                        type: "POST",

                        data: "idarticulo=" + idArticulo,

                        url: "../procesos/articulos/eliminarArticulo.php",

                        success: function(response){

                            if(response.trim() === "1"){

                                $('#tablaArticulosLoad').load("formatos/formatos.php");

                                alertify.success("Artículo eliminado con éxito");

                            } else {

                                alertify.error("Error al eliminar el artículo");

                            }

                        },

                        error: function(){

                            alertify.error("Error de comunicación con el servidor");

                        }

                    });

                },

                function(){ 

                    alertify.warning('Operación cancelada');

                }

            ).set('labels', {ok:'Sí, eliminar', cancel:'Cancelar'});

        }


        // Función para validar formulario

        function validarFormVacio(formulario){

            var datos = $('#' + formulario).serialize();

            var vacios = 0;

            

            datos = datos.split('&');

            

            for(var i = 0; i < datos.length; i++){

                var valores = datos[i].split('=');

                if(valores[1] === "A" || valores[1] === ""){

                    vacios++;

                }

            }

            

            return vacios;

        }

    </script>

</body>

</html>