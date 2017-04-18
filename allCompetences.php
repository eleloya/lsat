<?php

require 'core/init.php';

$user = new User();
$user->checkIsValidUser('teacher');
$teacherId = $user->data()->id;
$competence = new Competence();
$allCompetences = $competence->getAllCompetences();
//var_dump($allCompetences);

?>

<!doctype html>
<html class="no-js" lang="en">
<head>
	<title>LSAT | Competencias</title>
	<?php include 'includes/templates/headTags.php' ?>
</head>

<body>
	<?php include 'includes/templates/header.php' ?>

	<section class="scroll-container" role="main">
		<div class="row">
			<?php include 'includes/templates/teacherSidebar.php' ?>
			<div class="large-9 medium-8 columns">
				<h3>Competencias</h3>
				<h4 class="subheader">Administracion de competencias</h4>
				<hr>

				<table>
					<thead>
						<tr>
							<th width="40%">Nombre</th>
							<th width="40%">Creador</th>
							<th width="20%">Editar</th>
						</tr>
					</thead>

					<tbody>
					<?php
					$db = DB::getInstance();
					if ($allCompetences != null) {
						foreach ($allCompetences as $competence) {
							echo "<tr id='$competence->id'>";
							echo "<td>$competence->name</td>";


							$sql = "SELECT U.username FROM competence JOIN user U WHERE U.id = $competence->professor LIMIT 1";
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
							if ($competence->isPublished) {
								echo "Competencia publicada<br><br>";
							}
							if ($teacherId == $competence->professor) {
								echo "<a href=\"competenceDetail.php?competence=$competence->id\" class='tiny button secondary'>Editar</a>";
								echo "<br><a onclick='deleteCompetence($competence->id)' class='tiny button alert'>Borrar</a>";
							}
							echo "</td>";

							echo "</tr>";
						}
					} else {
						echo "<tr> <td> No hay competencias </td> </tr>";
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

		function deleteCompetence(id) {
			var r = confirm("Estas seguro que deseas eliminar esta competencia?");
			if (r == true) {
				window.location.replace('./deleteCompetence.php?id='+id);
			}
		}
	</script>
</body>
</html>