<?php

require 'core/init.php';

$user = new User();
$user->checkIsValidUser('admin');
$students = $user->getUsersByRole('student');
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
  <title>LSAT | Alumnos</title>
  <?php include 'includes/templates/headTags.php' ?>
</head>

<body>

  <?php include 'includes/templates/header.php' ?>

  <section class="scroll-container" role="main">

    <div class="row">
      <?php include 'includes/templates/adminSidebar.php' ?>
      <div class="large-9 medium-8 columns">
        <br/>
        <h3>Lista de todos los alumnos registrados</h3>
        <hr>

        <table>
         <thead>
           <tr>
             <th width="300">Matricula</th>
             <th width="200">Mail</th>
             <th width="200">Nombre</th>
             <th width="200">Fecha de registro</th>
             <th width="300">Editar</th>
             <th width="300">Eliminar</th>
           </tr>
         </thead>

         <tbody>
           <?php
           foreach ($students as $student) {

             echo "<tr id='$student->id'>
             <td> $student->idNumber </td>
             <td> $student->username </td>
             <td> $student->mail </td>
             <td> $student->registeredDate </td>
             <td> <a href='editUser.php?uId=$student->id' class='tiny button secondary'>Editar</a> </td>
             <td> <a onclick='deleteStudent($student->id)'   class='tiny button alert'>Eliminar</a> </td>
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

  function deleteStudent(id){
     alert("Solo los maestros pueden remover a sus alumnos");
	 }

</script>
</body>
</html>
