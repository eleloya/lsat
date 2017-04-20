<?php

require 'core/init.php';

$user = new User();
$user->checkIsValidUser('teacher');
$teacherId = $user->data()->id;

$allWebs = (new Web())->getAllPublishedWebs();

$c = new Competence();
$competenceId = Input::get("competence");
$websInCompetence = null;
if ($competenceId != '') {
	$competence = $c->getValidCompetence($competenceId, $teacherId);
	
	if ($competence == false) {
		$competence = null;
	} else {
		$websInCompetence = $c->getWebsInCompetence($competenceId);
	}
}
// var_dump($websInCompetence);
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
	<title>LSAT | Nueva competencia</title>
	<?php include 'includes/templates/headTags.php' ?>
</head>

<body>
	<?php include 'includes/templates/header.php' ?>

	<section class="scroll-container" role="main">

	<div class="row">
		<?php include 'includes/templates/teacherSidebar.php' ?>
		<div class="large-9 medium-8 columns">
			<br/>
			<h3>Nueva competencia</h3>
			<h4 class="subheader">Crear una nueva competencia reuniendo varias redes de aprendizaje</h4>
			<hr>

			<form id="newCompetence">
			<div class="row">
				<?php 
					$competenceName = "";
					if ($competence) {
						$competenceName = $competence->name;
						echo "<input type='hidden' id='competenceId' value='".$competence->id."'/>";
					}
					echo "<label>Nombre de la competencia<input type='text' name='name' id='name' value='".$competenceName."'/></label>";
				?>

				<h5>A continuacion, escribe los ids de las redes que formaran la competencia.</h5>
				<ol>
				
				<?php 
					$i = 0;
					$optns = array();
					if ($websInCompetence) {
						foreach ($websInCompetence as $web) {
							$optns[$i] = $web->webId;
							$i += 1;
						}
						while ($i < 5) {
							$optns[$i] = "-1";
							$i += 1;
						}
					}

					for ($i=1; $i <= 5; $i++) {
						echo "<li>";
						echo "<select id='web".$i."'>";
						echo "<option value=''>-</option>";
						foreach ($allWebs as $web) {
							$selected = $web->id==$optns[$i-1] ? "selected" : "";
							echo "<option value='".$web->id."' ".$selected.">".$web->name."</option>";
						}
						echo "</select>";
						echo "</li>";
					}
				?>
				</ol>
			</div>
			</form>
			<?php 
				if ($competence) {
					echo "<a onclick='updateCompetence()'' class='button round small right'>Guardar</a>";
				} else {
					echo "<a onclick='createCompetence()'' class='button round small right'>Crear</a>";
				}
			?>
			
	 	</div>
	</div>
	</section>



	<?php include 'includes/templates/footer.php' ?>

	<script src="js/vendor/jquery.js"></script>
	<script src="js/foundation.min.js"></script>

	<script>
	$(document).foundation();

	function createCompetence() {
		var name = $("input#name").val();
		var ids = [];
		ids[0] = $("select#web1").val();
		ids[1] = $("select#web2").val();
		ids[2] = $("select#web3").val();
		ids[3] = $("select#web4").val();
		ids[4] = $("select#web5").val();

		$.post("controls/doAction.php", { action: "createCompetence", name: name, webIds:ids }).done(function(data) {
			data = JSON.parse(data);
			console.log(data);
			if (data.message == 'success') {
				//Llevar al explorador de la red para mostrar detalle de la red creada
				window.location.replace('./competenceDetail.php?competence='+data.response);
			} else {
				alert("Error: \n\n" + data.message);
			}
		});
	}

	function updateCompetence() {
		var competenceId = $("input#competenceId").val();
		var name = $("input#name").val();
		var ids = [];
		ids[0] = $("select#web1").val();
		ids[1] = $("select#web2").val();
		ids[2] = $("select#web3").val();
		ids[3] = $("select#web4").val();
		ids[4] = $("select#web5").val();

		$.post("controls/doAction.php", { action: "updateCompetence", competenceId: competenceId, name: name, webIds:ids }).done(function(data) {
			data = JSON.parse(data);
			console.log(data);
			if (data.message == 'success') {
				//Llevar al explorador de la red para mostrar detalle de la red creada
				window.location.replace('./competenceDetail.php?competence='+competenceId);
			} else {
				alert("Error: \n\n" + data.message);
			}
		});
	}
	</script>
</body>
</html>
