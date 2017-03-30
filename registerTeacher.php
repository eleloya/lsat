<?php

require 'core/init.php';

$user = new User();
$user->checkIsValidUser('admin');

?>

<!doctype html>
<html class="no-js" lang="en">
<head>
  <title>LSAT | Registrar maestro</title>
  <?php include 'includes/templates/headTags.php' ?>
</head>

<body>

  <?php include 'includes/templates/header.php' ?>

  <section class="scroll-container" role="main">

    <div class="row">
      <?php include 'includes/templates/adminSidebar.php' ?>
      <div class="large-9 medium-8 columns">
        <br/>
        <h3>Registrar nuevo maestro</h3>
        <hr>
        <div id="">
	        <h5>Nombre:</h5>
			<input id="username" type="text">
			<h5>Mail:</h5>
			<input id="email" type="text">
			<h5>Nómina / Matrícula:</h5>
			<input id="idnumber" type="text">
			<a href="#" onclick="registerTeacher()" class="button tiny right">Registrar</a>
       </div>

     </div>
   </div>
 </section>


 <?php include 'includes/templates/footer.php' ?>


 <script src="js/vendor/jquery.js"></script>
 <script src="js/foundation.min.js"></script>
 <script>
  $(document).foundation();

  function registerTeacher(){
    var username  = $("#username").val().trim();
    var email     = $("#email").val().trim();
    var idnumber  = $("#idnumber").val().trim();
    var password  = <?php echo json_encode((new RandomPasswordGenerator())->generatePassword()); ?>;

    if (username == "" || email == "" || idnumber == "") {
      alert("No puedes dejar campos vacíos.");
      return;
    }

    $.post("controls/doAction.php", { action:"registerTeacher", email: email, password: password, username: username, idnumber: idnumber})
    .done(function( data ) {
      data = JSON.parse(data);
      if (data.message == 'success') {
        window.location.replace('./manageTeachers.php');
      } else {
        alert("Error: " + data.message);
      }

    });
  }


</script>
</body>
</html>
