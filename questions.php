<?php

require 'core/init.php';

$user = new User();
$user->checkIsValidUser('teacher');
$question = new Question();
$topic = "";
$difficulty = "";
$info = $question->getAllQuestions($topic, $difficulty);

?>

    <!doctype html>
    <html class="no-js" lang="en">

    <head>
        <title>LSAT | Questions</title>
        <?php include 'includes/templates/headTags.php' ?>
    </head>

    <body>

        <?php include 'includes/templates/header.php' ?>

        <section class="scroll-container" role="main">

            <div class="row">
                <?php include 'includes/templates/teacherSidebar.php' ?>
                <div class="large-9 medium-8 columns">
                    <h3>Preguntas</h3>
                    <h4 class="subheader">Todas las diferentes preguntas</h4>
                    <hr>

                    <table>
                        <thead>
                            <tr>
                                <th width="300">Tema</th>
                                <th width="200">Creada por</th>
                                <th width="300">Pregunta</th>
                                <th width="300">Dificultad </th>
                                <th width="300">Opciones </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
           foreach ($info as $question) {

              echo "<tr id='$question->id'>
                    <td> $question->topic </td>
                    <td> $question->professor </td>
                    <td> $question->text </td>
                    <td> $question->difficulty </td>";
                    echo "<td> <a href=\"webDetail.php?web=$web->id\" class='tiny button primary'>Editar/pendiente</a>
                    <a href=\"webDetail.php?web=$web->id\" class='tiny button alert'>Eliminar/pendiente</a></td></tr>";
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
