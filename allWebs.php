<?php

require 'core/init.php';

$user = new User();
$user->checkIsValidUser('teacher');
$teacherId = $user->data()->id;
$web = new Web();
$allWebs = $web->getAllWebs();

?>

<!doctype html>
<html class="no-js" lang="en">
<head>
  <title>LSAT | All Webs</title>
  <?php include 'includes/templates/headTags.php' ?>
</head>

<body>
<?php include 'includes/templates/header.php' ?>
<section class="scroll-container" role="main">
	<div class="row">
		<?php include 'includes/templates/teacherSidebar.php' ?>
		<div class="large-9 medium-8 columns">
			<h3>Redes</h3>
			<h4 class="subheader">Redes de aprendizaje</h4>
			<hr>

			<table>
				<thead>
					<tr>
						<th width="30%">Nombre de la red</th>
						<th width="20%">Fecha de creación</th>
						<th width="15%">Creador de la red</th>
						<th width="15%">Editar</th>
						<th width="20%">Detalle<br><small>Ver las preguntas que componen la red</small></th>
					</tr>
				</thead>

				<tbody>
					<?php
					$db = DB::getInstance();
					foreach ($allWebs as $web) {
						echo "<tr id='$web->id'>";
						echo "<td>$web->name</td>";
						echo "<td>$web->createdDate</td>";
						
						$sql = "SELECT U.username FROM web JOIN user U WHERE U.id = $web->professor LIMIT 1";
						if (!$db->query($sql)->error()) {
							if ($db->count()) {
								echo "<td>".$db->results()[0]->username."</td>";
							} else {
								echo "<td></td>";
							}
						} else {
							echo "<td></td>";
						}

						echo "<td><br>";
						if ($web->isPublished) {
							echo "Red publicada<br><br>";
						} elseif ($teacherId == $web->professor) {
							echo "Red no publicada<br><br>";
						}
						if ($teacherId == $web->professor) {
							echo "<a href=\"newWeb.php?web=$web->id\" class='tiny button secondary'>Editar</a>";
							echo "<a onclick='deleteWeb($web->id)' class='tiny button alert'>Borrar</a>";
						}
						echo "</td>";
							
						echo "<td> <a href=\"webDetail.php?web=$web->id\" class='tiny button secondary'>Ver detalle</a> </td></tr>";
					}

					?>
				</tbody>
			</table>
		</div>
 	</div>
 </section>


<?php include 'includes/templates/footer.php' ?>


<script src="js/vendor/jquery.js"></script>
<script src="js/foundation.min.js"></script>
<script>
	$(document).foundation();

	function deleteWeb(id) {
		var r = confirm("Estas seguro que deseas eliminar esta red?");
		if (r == true) {
			window.location.replace('./deleteWeb.php?id='+id);
		}
	}
</script>
</body>
</html>