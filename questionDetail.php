<?php

require 'core/init.php';

$user = new User();
$user->checkIsValidUser('teacher');
$difficulty = new Difficulty();
$difficulties = $difficulty->getDifficulties();
$topic = new Topic();
$topics = $topic->getTopics();

$q = new Question();
$questionId = $_GET["question"];

if ($questionId != ''){
  $question = $q->getQuestion($questionId);

  if ($question == null) {
    Redirect::to('questions.php');
  }
}else{
  Redirect::to('questions.php');
}


$answer = new Answer();
$answerA = $answer->getAnswer($question[0]->optionA);
$answerB = $answer->getAnswer($question[0]->optionB);
$answerC = $answer->getAnswer($question[0]->optionC);
$answerD = $answer->getAnswer($question[0]->optionD);




?>

    <!doctype html>
    <html class="no-js" lang="en">

    <head>
        <title>LSAT | Editar pregunta</title>
        <?php include 'includes/templates/headTags.php' ?>
        <link rel="stylesheet" href="css/jquery.wysiwyg.css" type="text/css" />
    </head>

    <body>

        <?php include 'includes/templates/header.php' ?>

        <section class="scroll-container" role="main">

            <div class="row">
                <?php include 'includes/templates/teacherSidebar.php' ?>
                <div class="large-9 medium-8 columns">
                    <br/>
                    <h3>Editar pregunta</h3>
                    <hr>
                    <form id="updateQuestion">

                        <input name="questionId" type="hidden" id="qId" value="<?php echo $question[0]->id ?>" />


                        <div class="row">
                            <div class="large-12 columns">
                                <label>Texto de la pregunta
                <textarea id="qtext" name="text" style="width:100%; height: 200px;"><?php echo $question[0]->text ?></textarea>
              </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="large-12 columns">
                                <label>Url media
                <input type="text" id="qurl" name="url" value="<?php echo $question[0]->urlImage ?>" /> 
              </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="large-6 columns">
                                <label>Dificultad
                <select id="qgrade" name="grade">
                  <?php
                  foreach ($difficulties as $item) {
                    echo "<option value='$item->id'>$item->name</option>";
                  }
                  ?>
                </select>
              </label>
                            </div>

                            <div class="large-6 columns">
                                <label>Tema
                <select id="qtopic" name="topic">
                  <?php
                  foreach ($topics as $item) {
                    echo "<option value='$item->id'>$item->name</option>";
                  }
                  ?>
                </select>
              </label>
                            </div>
                        </div>

                        <hr>

                        <h4>Respuestas</h4>

                        <div class="row correctAns">
                            <input type="hidden" name="ansid1" value="<?php echo $answerA[0]->id ?>">
                            <div class="large-6 columns">
                                <label>Respuesta 1 - CORRECTA <textarea  name="ans1"><?php echo $answerA[0]->text ?></textarea> </label>
                            </div>

                            <div class="large-6 columns">
                                <label>Feedback <textarea  name="feed1"><?php echo $answerA[0]->textFeedback ?></textarea> </label>
                            </div>

                            <div class="large-6 columns">
                                <label>URL <input type="text" name="urla1" value="<?php echo $answerA[0]->urlImage ?>" /> </label>
                            </div>

                            <div class="large-6 columns">
                                <label>URL feedback <input type="text" name="urlf1" value="<?php echo $answerA[0]->imageFeedback ?>" /> </label>
                            </div>

                        </div>

                        <div class="row grey1">
                            <input type="hidden" name="ansid2" value="<?php echo $answerB[0]->id ?>">
                            <div class="large-6 columns">
                                <label>Respuesta 2 <textarea  name="ans2"><?php echo $answerB[0]->text ?></textarea> </label>
                            </div>

                            <div class="large-6 columns">
                                <label>Feedback <textarea  name="feed2"><?php echo $answerB[0]->textFeedback ?></textarea> </label>
                            </div>

                            <div class="large-6 columns">
                                <label>URL <input type="text" name="urla2" value="<?php echo $answerB[0]->urlImage ?>" />  </label>
                            </div>

                            <div class="large-6 columns">
                                <label>URL feedback <input type="text" name="urlf2" value="<?php echo $answerB[0]->imageFeedback ?>"/>  </label>
                            </div>

                        </div>


                        <div class="row grey2">
                            <input type="hidden" name="ansid3" value="<?php echo $answerC[0]->id ?>">
                            <div class="large-6 columns">
                                <label>Respuesta 3 <textarea  name="ans3"><?php echo $answerC[0]->text ?></textarea> </label>
                            </div>

                            <div class="large-6 columns">
                                <label>Feedback <textarea  name="feed3"><?php echo $answerC[0]->textFeedback ?></textarea> </label>
                            </div>

                            <div class="large-6 columns">
                                <label>URL <input type="text" name="urla3" value="<?php echo $answerC[0]->urlImage ?>" />  </label>
                            </div>

                            <div class="large-6 columns">
                                <label>URL feedback <input type="text" name="urlf3" value="<?php echo $answerC[0]->imageFeedback ?>"/>  </label>
                            </div>

                        </div>

                        <div class="row grey1">
                            <input type="hidden" name="ansid4" value="<?php echo $answerD[0]->id ?>">
                            <div class="large-6 columns">
                                <label>Respuesta 4 <textarea  name="ans4"><?php echo $answerD[0]->text ?></textarea> </label>
                            </div>

                            <div class="large-6 columns">
                                <label>Feedback <textarea  name="feed4"><?php echo $answerD[0]->textFeedback ?></textarea> </label>
                            </div>

                            <div class="large-6 columns">
                                <label>URL <input type="text" name="urla4" value="<?php echo $answerD[0]->urlImage ?>"/>  </label>
                            </div>

                            <div class="large-6 columns">
                                <label>URL feedback <input type="text" name="urlf4" value="<?php echo $answerD[0]->imageFeedback ?>"/>  </label>
                            </div>

                        </div>

                        <br/>

                        <a href="#" onclick="editQuestion()" class="button round small right">Actualizar</a>

                    </form>

                </div>
            </div>
        </section>


        <?php include 'includes/templates/footer.php' ?>


        <script src="js/vendor/jquery.js"></script>
        <script src="js/foundation.min.js"></script>

        <script type="text/javascript" src="js/jquery.wysiwyg.js"></script>
        <script type="text/javascript" src="js/controls/wysiwyg.image.js"></script>
        <script type="text/javascript" src="js/controls/wysiwyg.link.js"></script>
        <script type="text/javascript" src="js/controls/wysiwyg.table.js"></script>


        <script>
            $(document).foundation();

            function editQuestion() {

                var fields = $("#updateQuestion").serializeArray();
                //      console.log(fields);

                var id = $("#qId").val();
                var topic = $("#qtopic").val();
                var grade = $("#qgrade").val();
                var url = $("#qurl").val();
                var text = $("#qtext").val();
                console.log(text);

                var len = fields.length,
                    dataObj = {};

                for (i = 0; i < len; i++) {
                    dataObj[fields[i].name] = fields[i].value;
                }

                var data = JSON.stringify(dataObj);
                console.log(data);


                $.post("controls/doAction.php", {
                        action: "updateQuestion",
                        data: data
                    })
                    .done(function(data) {

                        data = JSON.parse(data);
                        if (data.message == 'success') {
                            alert("La pregunta fue actualizada");
                            window.location.reload();
                        } else {
                            alert("Error: \n\n" + data.message);
                        }

                    });

            }

        </script>
    </body>

    </html>
