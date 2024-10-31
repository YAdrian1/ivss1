

<?php 

	require_once "../../clases/Conexion.php";

	$c = new conectar();

	$conexion = $c->conexion();

	$sql = "SELECT art.nombre,

                   img.ruta,

                   cat.nombreCategoria,

                   art.id_producto

            FROM articulos AS art 

            INNER JOIN imagenes AS img ON art.id_imagen = img.id_imagen

            INNER JOIN categorias AS cat ON art.id_categoria = cat.id_categoria";

	$result = mysqli_query($conexion, $sql);

?>


<table class="table table-hover table-condensed table-bordered" style="text-align: center;">

	<caption><label>Articulos</label></caption>

	<tr>

		<td class="0">Nombre</td>

		<td class="4">Imagen</td>

		<td class="5">Categoria</td>

		<td class="6">Editar</td>

		<td class="7">Eliminar</td>

	</tr>


	<?php while ($ver = mysqli_fetch_row($result)): ?>

	<tr>

		<td><?php echo $ver[0]; ?></td>

		<td>

			<?php 

			$imgVer = explode("/", $ver[1]); 

			$imgruta = $imgVer[1] . "/" . $imgVer[2] . "/" . $imgVer[3];

			?>

			<img width="80" height="80" src="<?php echo $imgruta ?>">

		</td>

		<td><?php echo $ver[2]; ?></td>

		<td>

			<span data-toggle="modal" data-target="#abremodalUpdateArticulo" class="btn btn-warning btn-xs" onclick="agregaDatosArticulo('<?php echo $ver[3] ?>')">

				<span class="glyphicon glyphicon-pencil"></span>

			</span>

		</td>

		<td>

			<span class="btn btn-danger btn-xs" onclick="eliminaArticulo('<?php echo $ver[3] ?>')">

				<span class="glyphicon glyphicon-remove"></span>

			</span>

		</td>

	</tr>

	<?php endwhile; ?>

</table>