<?php

require 'core/init.php';

$user = new User();
$user->checkIsValidUser('teacher');
$teacherId = $user->data()->id;
$groups = new Groups();
$teacherGroups = $groups->getGroupsForTeacher($teacherId);
$students = $g->getAllStudentsFromTeacher($teacherId);


?>

<!doctype html>
<html class="no-js" lang="en">
<head>
  <title>LSAT | Estudiantes</title>
  <?php include 'includes/templates/headTags.php' ?>
</head>

<body>

  <?php include 'includes/templates/header.php' ?>

  <section class="scroll-container" role="main">

    <div class="row">
      <?php include 'includes/templates/teacherSidebar.php' ?>  
      <div class="large-9 medium-8 columns">
        <br/>
        <h3>Alumnos</h3>
        <h4 class="subheader">Administracion de mis alumnos</h4>
        <hr>  

        <table> 
         <thead> 
           <tr> 
             <th width="300">Matricula</th> 
             <th width="300">Nombre</th> 
             <th width="300">Recuperar Contraseña</th> 
           </tr> 
         </thead>

         <tbody> 
            <?php
            foreach($students as $student) {
              echo "
              <tr>
                <td>$student->idNumber</td>
                <td>$student->username</td>
                <td><a onclick='recoverPassword($student->id)'   class='tiny button alert'>Recuperar Contraseña</a></td>
              </tr>
              ";
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

</script>
</body>
</html>
