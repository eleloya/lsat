<?php

require 'core/init.php';

$user = new User();
$user->checkIsValidUser('teacher');
$teacherId = $user->data()->id;
$groups = new Groups();
$teacherGroups = $groups->getGroupsForTeacher($teacherId);
//var_dump($teacherGroups);

?>

<!doctype html>
<html class="no-js" lang="en">
<head>
  <title>LSAT | Grupos</title>
  <?php include 'includes/templates/headTags.php' ?>
</head>

<body>

  <?php include 'includes/templates/header.php' ?>

  <section class="scroll-container" role="main">

    <div class="row">
      <?php include 'includes/templates/teacherSidebar.php' ?>  
      <div class="large-9 medium-8 columns">
        <br/>
        <h3>Grupos</h3>
        <h4 class="subheader">Administracion de mis grupos</h4>
        <hr>  

        <table> 
         <thead> 
           <tr> 
             <th width="300">Grupo</th> 
             <th width="300">Opciones</th> 
           </tr> 
         </thead>

         <tbody> 
           <?php
           foreach ($teacherGroups as $group) {

            echo "<tr id='$group->id'> 
            <td> <a href='group.php?id=$group->id'> $group->name </a> </td>
            <td> <a href='editGroup.php?g=$group->id' class='tiny button secondary'>Editar</a>  <a href='#' onclick='deleteGroup($group->id)' class='button round tiny alert'>Borrar</a>  </td> 
          </tr>";
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
	
	function deleteGroup(gid) {
		var r = confirm("¿Estas seguro que deseas borra este grupo y toda su historia?");
		if (r == true) {
			$.post("controls/doAction.php", { action:"deleteGroup", groupid: gid})
			.done(function(data) {
				data = JSON.parse(data);
				if (data.message = 'success'){
					window.location.replace('./groups.php?'+gid);
				}else{
					alert("Error:\n\n" + data.message);
				}
			});
		}
	}
</script>
</body>
</html>
