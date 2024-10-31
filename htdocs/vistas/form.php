<?php 
session_start();

if (isset($_SESSION['usuario'])) {
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
                        <input type="file" id="imagen" name="imagen" required>

                        <p></p>
                        <span id="btnAgregaArticulo" class="btn btn-primary">Agregar</span>
                    </form>
                </div>

                <div class="col-sm-8">
                    <div id="tablaArticulosLoad"></div>
                </div>
            </div>
        </div>

        <!-- Modal -->
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
                            <button type="submit" id="btnEnviarArticulo" class="btn btn-primary">Enviar</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button id="btnActualizaarticulo" type="button" class="btn btn-warning" data-dismiss="modal">Actualizar</button>
                    </div>
                </div>
            </div>
        </div>

    </body>
    </html>

    <script type="text/javascript">
        function agregaDatosArticulo(idarticulo) {
            $.ajax({
                type: "POST",
                data: "idart=" + idarticulo,
                url: "../procesos/articulos/obtenDatosArticulo.php",
                success: function(r) {
                    dato = jQuery.parseJSON(r);
                    $('#idArticulo').val(dato['id_producto']);
                    $('#categoriaSelectU').val(dato['id_categoria']);
                    $('#nombreU').val(dato['nombre']);
                }
            });
        }

        function eliminaArticulo(idArticulo) {
            alertify.confirm('¿Desea eliminar este articulo?', function() { 
                $.ajax({
                    type: "POST",
                    data: "idarticulo=" + idArticulo,

                    url: "../procesos/articulos/eliminarArticulo.php",

                    success: function(r) {

                        if (r == 1) {

                            $('#tablaArticulosLoad').load("formatos/formatos.php");

                            alertify.success("Eliminado con éxito!!");

                        } else {

                            alertify.error("No se pudo eliminar :(");

                        }

                    }

                });

            }, function() { 

                alertify.error('Cancelado!');

            });

        }


        $(document).ready(function() {

            $('#tablaArticulosLoad').load("formatos/formatos.php");


            $('#btnAgregaArticulo').click(function() {

                // Validar que la categoría no sea la opción por defecto

                if ($('#categoriaSelect').val() === 'A') {

                    alertify.alert("Debes seleccionar una categoría!!");

                    return false;

                }


                // Validar que el nombre no esté vacío

                var nombre = $('#nombre').val();

                if (nombre.trim() === "") {

                    alertify.alert("El nombre no puede estar vacío!!");

                    return false;

                }


                // Validar que se haya seleccionado una imagen

                var imagen = $('#imagen').val();

                if (imagen === "") {

                    alertify.alert("Debes seleccionar una imagen!!");

                    return false;

                }


                var formData = new FormData(document.getElementById("frmArticulos"));


                $.ajax({

                    url: "../procesos/articulos/insertaArticulos.php",

                    type: "post",

                    dataType: "html",

                    data: formData,

                    cache: false,

                    contentType: false,

                    processData: false,

                    success: function(r) {

                        if (r == 1) {

                            $('#frmArticulos')[0].reset(); // Reset the form

                            $('#tablaArticulosLoad').load("formatos/formatos.php"); // Reload the articles

                            alertify.success("Agregado con éxito :D"); // Success alert

                        } else {

                            alertify.error("Error al agregar el artículo.");

                        }

                    }

                });

            });

        });


        function validarFormVacio(formId) {

            var vacios = 0;

            $('#' + formId + ' :input').each(function() {

                if ($(this).val() === "" && $(this).attr('type') !== 'file') {

                    vacios++;

                }

            });

            return vacios;

        }

    </script>


    <?php 

} else {

    header("location:../index.php");

}

?>