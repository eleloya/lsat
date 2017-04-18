<?php

require 'core/init.php';

$user = new User();
$user->checkIsValidUser('teacher');
$teacherId = $user->data()->id;
$groups = new Groups();
$teacherGroups = $groups->getGroupsForTeacher($teacherId);
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
  <title>LSAT | Agregar Alumno a Grupo</title>
  <?php include 'includes/templates/headTags.php' ?>
</head>

<body>

  <?php include 'includes/templates/header.php' ?>

  <section class="scroll-container" role="main">

    <div class="row">
      <?php include 'includes/templates/teacherSidebar.php' ?>  
      <div class="large-9 medium-8 columns">
        <h3>Grupo</h3>
        <h4 class="subheader">Agregar nuevo alumno a grupo existente</h4>
        <hr>  

        <form> 
          <div class="row"> 
            <div class="large-4 columns"> 
            <label>Nombre del grupo 
						<select id="groupname">
		           <?php
		           foreach ($teacherGroups as $group) {
								 echo "<option value='$group->name'>$group->name</option>";
		        	 }
		        ?>
					</select>
						</label> 
            </div>
          </div>
          <div class="row"> 
            <div class="large-12 columns"> 
              <label>Alumnos<input id="students" type="text" placeholder="Matrículas de alumnos separadas por comas... A012345, A02389"/> </label>
            </div> 
          </div>  
          <a href="#" onclick="createGroup()" class="button round small right">Crear</a>
        </form>

      </div>
    </div>
  </section>


  <?php include 'includes/templates/footer.php' ?>

  <script src="js/vendor/jquery.js"></script>
  <script src="js/foundation.min.js"></script>

	<script>
	$(document).foundation();
	function createGroup() {
		var groupname = $("#groupname").val();
		var students = $("#students").val();

		$.post("controls/doAction.php", { action:"addStudentsToGroup", groupname: groupname, students: students})
		.done(function(data) {
			data = JSON.parse(data);
			if (data.message == 'success') {
				window.location.replace('./students.php');
			} else {
				alert("Error:\n\n" + data.message);
			}
		});
	}
	</script>
</body>
</html>
