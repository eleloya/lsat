<?php
require '../core/init.php';

if(Input::exists()) {

	$action = Input::get('action');

	switch ($action) {

		case "updateUser":
		try {

			$user = new User();
			if($user->data()->role != 'teacher' && $user->data()->role != 'admin'){
				return; /*Solo un maestro o administrador puede crear preguntas*/
			}

			$username = trim(stripslashes(Input::get('username')));
			$mail     = trim(stripslashes(Input::get('mail')));
			$password = trim(stripslashes(Input::get('password')));
			$userId   = trim(stripslashes(Input::get('uId')));
			$idNumber = trim(stripslashes(Input::get('idNumber')));


			if(!isValidIdNumber($idNumber) || $username == "" || $mail == "" || !is_numeric($userId) ){
				$response = array( "message" => "Datos incorrectos.");
				die(json_encode($response));
			}

			$salt = Hash::salt(32);
			$userId = intval($userId);

			if(strlen($password) != 0 ){
				$user->update(array(
					'username'  => $username,
					'mail'      => $mail,
					'password' 	=> Hash::make($password, $salt),
					'salt'		=> $salt,
					'idNumber' => $idNumber
					), $userId);
			}
			else{
				$user->update(array(
					'username'  => $username,
					'mail'      => $mail,
					'idNumber' => $idNumber
					), $userId);
			}

		} catch(Exception $e) {
			$response = array( "message" => "Error:031 ".$e->getMessage());
			die(json_encode($response));
		}

		$response = array( "message" => "success");
		echo json_encode($response);
		break;

		case "updateSettings":
		$username = Input::get('username');
		$mail     = Input::get('mail');
		$password = trim(Input::get('password'));
		$salt = Hash::salt(32);
		$user = new User();

		try {
			if(strlen($password) != 0 ){
				$user->update(array(
					'username'  => $username,
					'mail'      => $mail,
					'password' 	=> Hash::make($password, $salt),
					'salt'		=> $salt,
					), $user->data()->id);
			}
			else{
				$user->update(array(
					'username'  => $username,
					'mail'      => $mail
					), $user->data()->id);
			}

		} catch(Exception $e) {
			$response = array( "message" => "Error:003 ".$e->getMessage());
			die(json_encode($response));
		}

		$response = array( "message" => "success");
		echo json_encode($response);
		break;

		case "registerTopic":
		$name = Input::get('name');
		$name = trim($name);
		if($name == ""){
			$response = array( "message" => "Nombre del tema vacio ");
			die(json_encode($response));
		}

		$t = new Topics();
		$t->create($name);

		$response = array( "message" => "success");
		echo json_encode($response);

		break;


		case "registerLevel":
		$name = Input::get('name');
		$name = trim($name);
		if($name == ""){
			$response = array( "message" => "Nombre del nvel vacio ");
			die(json_encode($response));
		}

		$t = new Levels();
		$t->create($name);

		$response = array( "message" => "success");
		echo json_encode($response);

		break;

            
            
		case "registerTeacher":
		try {
			$user = new User();
			$salt = Hash::salt(32);

			$email    = trim(stripslashes(Input::get('email')));
			$password = trim(stripslashes(Input::get('password')));
			$username = trim(stripslashes(Input::get('username')));
			$idnumber = trim(stripslashes(Input::get('idnumber')));

			if (!isValidIdNumber($idnumber) || $username == "" || $email == "" ) {
				$response = array( "message" => "Datos incorrectos.");
				die(json_encode($response));
			}
            
			if($user->mailExists($email)){
				$response = array( "message" => "El correo ya esta siendo usado, favor de checar los datos.");
				die(json_encode($response));
			}
            
            $idnumber = strtoupper($idnumber);

            
            if($user->idNumberExists($idnumber)){
				$response = array( "message" => "La nómina/matrícula ya está siendo utilizada, favor de checar los datos.");
				die(json_encode($response));
			}

			$user->create(array(
				'mail' 	    => $email,
				'password' 	=> Hash::make($password, $salt),
				'salt'		=> $salt,
				'username'  => $username,
				'idnumber'  => $idnumber,
				'role'      =>'teacher'
				));


			$mailer = new Mailer();
			$mailer->sendActivationMail($email, $password);

		} catch(Exception $e) {
			$response = array( "message" => "Error:004 ".$e->getMessage());
			die(json_encode($response));
		}
		$response = array( "message" => "success");
		echo json_encode($response);
		break;

            
            
            
		case "createGroup":

		try {
			
			//Necesitamos la Matrícula del profesor, que es el usuario logueado
			$user = new User();
			$mailer = new Mailer();
			$teacherId = $user->data()->id;
			$groupname = trim(Input::get('groupname'));
			$students  = trim(Input::get('students'));
			
			if ( empty($groupname) || empty($students)) {
				$response = array( "message" => "No se puede crear un grupo vacío");
				die(json_encode($response));
			}
			
			$studentIds = explode(',', $students);
			foreach ($studentIds as $idnumber){
				if(!isValidIdNumber($idnumber)){
					$response = array( "message" => "Matriculas incorrectas");
					die(json_encode($response));
				}
			}

			$db = DB::getInstance();

			// Crear el nuevo grupo
			$group = new Groups();
			if($group->getGroupByName($groupname)){
				$response = array( "message" => "El grupo ya existe, cambia el nombre. ");
				echo json_encode($response);
				return;
			}

			$group->create(array(
				'professor' => intval($teacherId),
				'name'  => $groupname
				));

			// Obtener el id que se le asigno en la BD
			$groupId = $group->getGroupByName($groupname)->id;

			//Crear cada estudiante
			foreach ($studentIds as $idnumber) {
				/*Debemos de crear una nueva cuenta para cada alumno y asignarle el nuevo grupo
				pero si el alumno ya existe solo le asignamos el grupo*/
				$studentId = 0;
				$student = $user->getByIdNumber(trim($idnumber));
				if($student == false){
					$salt = Hash::salt(32);
					$email = trim($idnumber) . "@itesm.mx";
					$username = "Estudiante - " . trim($idnumber);
					$password = (new RandomPasswordGenerator())->generatePassword();
					$user->create(array(
						'mail'	 	=> $email,
						'password' 	=> Hash::make($password, $salt),
						'salt'		=> $salt,
						'username'  => $username,
						'idnumber'  => trim($idnumber),
						'role'      =>'student'
						));

					$mailer->sendActivationMail($email, $password);

					$studentId = $user->getByIdNumber(trim($idnumber))->id;
				} else {
					$studentId = $student->id;
				}

				//studentsingroup - groupId studentId
				$fields = array(
					'groupId' 	=> intval($groupId),
					'studentId' => intval($studentId),
					'active' => 1);
				if(!$db->insert('studentsingroup', $fields)) {
					throw new Exception('There was a problem assigning the student to the group.');
				}

			}

		} catch(Exception $e) {
			$response = array( "message" => "Error:005 ".$e->getMessage());
			die(json_encode($response));
		}
		$response = array( "message" => "success");
		echo json_encode($response);
		break;

		case "addStudentsToGroup":

		try {
			
			//Necesitamos la Matrícula del profesor, que es el usuario logueado
			$user = new User();
			$mailer = new Mailer();
			$teacherId = $user->data()->id;
			$groupname = trim(Input::get('groupname'));
			$students  = trim(Input::get('students'));
			
			if ( empty($groupname) || empty($students)) {
				$response = array( "message" => "No se puede agregar alumnos sin grupo");
				die(json_encode($response));
			}
			
			$studentIds = explode(',', $students);
			foreach ($studentIds as $idnumber){
				if(!isValidIdNumber($idnumber)){
					$response = array( "message" => "Matriculas incorrectas");
					die(json_encode($response));
				}
			}

			$db = DB::getInstance();

			// Crear el nuevo grupo
			$group = new Groups();
			if(!$group->getGroupByName($groupname)){
				$response = array( "message" => "El grupo seleccionado no existe");
				echo json_encode($response);
				return;
			}

			// Obtener el id que se le asigno en la BD
			$groupId = $group->getGroupByName($groupname)->id;

			//Crear cada estudiante
			foreach ($studentIds as $idnumber) {
				/*Debemos de crear una nueva cuenta para cada alumno y asignarle el nuevo grupo
				pero si el alumno ya existe solo le asignamos el grupo*/
				$studentId = 0;
				$student = $user->getByIdNumber(trim($idnumber));
				if($student == false){
					$salt = Hash::salt(32);
					$email = trim($idnumber) . "@itesm.mx";
					$username = "Estudiante - " . trim($idnumber);
					$password = (new RandomPasswordGenerator())->generatePassword();
					$user->create(array(
						'mail'	 	=> $email,
						'password' 	=> Hash::make($password, $salt),
						'salt'		=> $salt,
						'username'  => $username,
						'idnumber'  => trim($idnumber),
						'role'      =>'student'
						));

					$mailer->sendActivationMail($email, $password);

					$studentId = $user->getByIdNumber(trim($idnumber))->id;
				} else {
					$studentId = $student->id;
				}

				//studentsingroup - groupId studentId
				$fields = array(
					'groupId' 	=> intval($groupId),
					'studentId' => intval($studentId),
					'active' => 1);
				if(!$db->insert('studentsingroup', $fields)) {
					throw new Exception('There was a problem assigning the student to the group.');
				}

			}

		} catch(Exception $e) {
			$response = array( "message" => "Error:005 ".$e->getMessage());
			die(json_encode($response));
		}
		$response = array( "message" => "success");
		echo json_encode($response);
		break;

		case "createQuestion":

		$user = new User();
		if($user->data()->role != 'teacher'){
			return; /*Solo un maestro puede crear preguntas*/
		}

		//Necesitamos el id del profesor, que es el usuario logueado para ligar las preguntas con el
		$teacherId = $user->data()->id;

		try {
			//Esto lo usamos para convertir el objeto que le mandamos con toda la informacion
            //De esta forma nos queda como un hash map en el que podemos accesar a todos los valores facilmente
			$data = json_decode($_POST['data'],true);
			//Datos de la pregunta
			$text = $data['text'];

			//Validar que la pregunta tenga texto
			if (empty($text)) {
				$response = array( "message" => "No se puede crear una pregunta vacia");
				die(json_encode($response));
			}

			//var_dump($text);
			//$data = json_decode(stripslashes($_POST['data']),true);
			//var_dump($data);
			$url  =  $data['url'];
			$grade = $data['grade'];
			$topic = $data['topic'];

			$db = DB::getInstance();

			$options = array(4);

			//Crear las 4 respuestas
			$ans = new Answer();
			for ($i = 1; $i <= 4; $i++) {
				if (empty($data['ans'.$i]) && empty($data['urla'.$i]) ) {
					$response = array( "message" => "No se puede crear una pregunta sin respuesta");
					die(json_encode($response));
				}

				//Crear la respuesta
				$ans->create(array(
					'text' => $data['ans'.$i],
					'textFeedback' => $data['feed'.$i],
					'urlImage' => $data['urla'.$i],
					'imageFeedback' => $data['urlf'.$i],
    			'correct' => ($i==1)? true : false,  //La primera respuesta siempre sera la correcta
    			));

    			//Obtener el id de la respuesta
				$answerId = intval($db->lastInsertId());
				$options[$i-1] = $answerId;
			}

			// Crear la pregunta
			$question = new Question();
			$question->create(array(
				'professor' => intval($teacherId),
				'topic' => intval($topic),
				'difficulty' => intval($grade),
				'urlImage' => $url,
				'text' => $text,
				'optionA' => $options[0],
				'optionB' => $options[1],
				'optionC' => $options[2],
				'optionD' => $options[3]
				));

			// Obtener el id que se le asigno en la BD
			$questionId = intval($db->lastInsertId());


		} catch(Exception $e) {
			$response = array( "message" => "Error:005 ".$e->getMessage());
			die(json_encode($response));
		}
		$response = array( "message" => "success");
		echo json_encode($response);
		break;
            
            
            
            
            
            
            
            
            
            
            
        case "updateQuestion":
        $user = new User();
		if($user->data()->role != 'teacher'){
			return; /*Solo un maestro puede crear preguntas*/
		}

		//Necesitamos el id del profesor, que es el usuario logueado para ligar las preguntas con el
		$teacherId = $user->data()->id;

		try {
			//Esto lo usamos para convertir el objeto que le mandamos con toda la informacion
            //De esta forma nos queda como un hash map en el que podemos accesar a todos los valores facilmente
			$data = json_decode($_POST['data'],true);
			//Datos de la pregunta
			$text = $data['text'];

			//Validar que la pregunta tenga texto
			if (empty($text)) {
				$response = array( "message" => "No se puede actualizar una pregunta vacia");
				die(json_encode($response));
			}

			//var_dump($text);
			//$data = json_decode(stripslashes($_POST['data']),true);
			//var_dump($data);
            $id = $data['questionId'];
			$url  =  $data['url'];
			$grade = $data['grade'];
			$topic = $data['topic'];

			$db = DB::getInstance();

			$options = array(4);
			//Crear las 4 respuestas
			$ans = new Answer();
			for ($i = 1; $i <= 4; $i++) {
				if (empty($data['ans'.$i]) && empty($data['urla'.$i]) ) {
					$response = array( "message" => "No se puede actualizar una pregunta sin respuesta");
					die(json_encode($response));
				}

                
				//Crear la respuesta
				$ans->update($data['ansid'.$i], array(
					'text' => $data['ans'.$i],
					'textFeedback' => $data['feed'.$i],
					'urlImage' => $data['urla'.$i],
					'imageFeedback' => $data['urlf'.$i],
    			'correct' => ($i==1)? true : false,  //La primera respuesta siempre sera la correcta
    			));

    			//Obtener el id de la respuesta
				$options[$i-1] = $data['ansid'.$i];
			}

			// Crear la pregunta
			$question = new Question();
			$question->update($id, array(
				'professor' => intval($teacherId),
				'topic' => intval($topic),
				'difficulty' => intval($grade),
				'urlImage' => $url,
				'text' => $text,
				'optionA' => $options[0],
				'optionB' => $options[1],
				'optionC' => $options[2],
				'optionD' => $options[3]
				));

			// Obtener el id que se le asigno en la BD
//			$questionId = intval($db->lastInsertId());


		} catch(Exception $e) {
			$response = array( "message" => "Error:005 ".$e->getMessage());
			die(json_encode($response));
		}
		$response = array( "message" => "success");
		echo json_encode($response);
		break;
            
            
            
            
            
            
            
            
            

		case "filterQuestions":

		$difficulty = Input::get('difficulty');
		$topic      = Input::get('topic');

		$question = new Question();
		$filteredQuestions = $question->getFilteredQuestions($topic, $difficulty);

		echo json_encode($filteredQuestions);

		break;

		case "getQuestion":

		$id = Input::get('id');
		$response = array();
		$question = new Question();
		$answer = new Answer();

		$q = $question->getQuestion($id);
		$response['text'] = $q[0]->text;
		$response['urlImage'] = $q[0]->urlImage;
		$answersIds = array($q[0]->optionA, $q[0]->optionB, $q[0]->optionC, $q[0]->optionD);

		foreach ($answersIds as $id){
			$answersText = $answer->getAnswer($id);
			$response[] = $answersText[0];
		}

		echo json_encode($response);

		break;
		
		case "deleteGroup":
		try{
			$user = new User();
			if($user->data()->role != 'teacher'){
				$response = array( "message" => "Solo un maestro pueda eliminar grupos");
				die(json_encode($response));
			}
			$gid = Input::get('groupid');
			
			//Aqui deberia checar que el gid es numerico y no vacio
			
			$db = DB::getInstance();
			
			// Borrar asociacion en "competenceingroup" 
			$sql = "DELETE FROM competenceingroup WHERE groupId = $gid";
			if($db->query($sql,array())->error()) {
				throw new Exception('There was a problem deleting the group associations.');
			}
			
			// Borrar asociacion en "studentsingroup" 
			$sql = "DELETE FROM studentsingroup WHERE groupId = $gid";
			if($db->query($sql,array())->error()) {
				throw new Exception('There was a problem deleting the group associations.');
			}
			
			
			// Por el momento vamos a dejar el historial de la competencia en la db. El algoritmo para limpiarlo
			// ya esta especificado en los comentarios de esta funcion. Pero francamente es bastante trabajo para php.
			// Lo correcto deberia ser DELETE ON CASCADE CONSTRAINS en la db.
			// La aplicacion no tendria porque estarse preocupando de mantener consistencia en la db
			// Esto se debe a un mal diseño. Nosotros tomamos el codigo ya hecho
			
			// Borrar el grupo en si
			$sql = "DELETE FROM groups WHERE id = $gid";
			if($db->query($sql,array())->error()) {
				throw new Exception('There was a problem deleting the group.');
			}
			
			/* Algoritmo for properly deleting a group
					- Borrar asociacion en "competenceingroup" 
					- Buscar todos los "studentrecord" asociados a un groupId
						- Buscar todos los "studentprogress" asociados al studenrecord con con "studentRecord".studentProgressId -> "studentprogress".id
							- Sacar first y las question de "studentprogress"
							- Borrar todos los "questionsforstudent" between first y las question pasados.
						- Borrar todos los "studentprogress" pasados.
					- Borrar todos los "studentrecord" pasados.
				- Borrar grupo de "groups"
			*/
			// Buscar todos los "studentRecord asociados aun groupId"
			// SELECT * FROM studentrecord sr WHERE sr.groupId = 12
			// Buscar todos los "studentprogress" asociados al studenrecord con con "studentRecord".studentProgressId "studentprogress".id 
			// SELECT * FROM studentprogress sp, studentrecord sr WHERE sp.id = sr.studentProgressId AND sr.groupId = 12
			// Sacar first y las question de "studentprogress"
			// SELECT firstQuestion, lastQuestion FROM studentprogress sp, studentrecord sr WHERE sp.id = sr.studentProgressId AND sr.groupId = 12
			// Borrar todos los "questionsforstudent" between first y las question pasadoss
			
			
		} catch(Exception $e) {
			$response = array( "message" => "Error:005 ".$e->getMessage());
			die(json_encode($response));
		}
		// Si llegamos hasta aca no hubo excepcion alguna y se borro
		$response = array( "message" => "success");
		echo json_encode($response);
		break;

		case "createWeb":
		$user = new User();
		if($user->data()->role != 'teacher'){
			return; /*Solo un maestro puede crear preguntas*/
		}

		//Necesitamos el id del profesor, que es el usuario logueado para ligar las preguntas con el
		$teacherId = $user->data()->id;

		try{

			$webId = Input::get('webId');
			$name = Input::get('name');
			$questionsForLevel = Input::get('questionsForLevel');
			$isPublished= Input::get('isPublished');

			$w = new Web();
			$web = $w->getWeb($webId);
			$data = array(
				'professor' => intval($teacherId),
				'name' => $name,
				'isPublished' => intval($isPublished)
				);

			if ($web == null){
				//Se va a crear nueva red
				$w->create($data);
				$db = DB::getInstance();
				$webId = intval($db->lastInsertId());
			} else {
				//Se va a actualizar la red
				$w->update($webId, $data);
				$w->deleteAllQuestionsInWeb($webId);
			}

			//Este es el formato en el que nos llegan las preguntas por nivel, el primer indice corresponde al nivel y
			//	El segundo arreglo son las preguntas
			//	0 =>
			//			array
			//				0 => string '5' (length=1)
			//				1 => string '6' (length=1)
			//		1 =>
			//			array
			//				0 => string '7' (length=1)


			$currentLevel = 1;
			if(is_array($questionsForLevel) && count($questionsForLevel) > 0){
				foreach ($questionsForLevel as $key => $value) {
					//$key es el nivel - 1, debido a que los indices empiezan desde 0, *genius*
					//Ahora hay que meter todos estas relaciones de nivel con pregunta a la BD
					//var_dump($value);  //Value es el arreglo que contiene las preguntas de ese nivel
					foreach ($value as $key => $questionId) {
						$w->addQuestionInWeb($questionId, $webId, $currentLevel);
					}
					$currentLevel += 1;
				}
			}


		}catch(Exception $e) {
			$response = array( "message" => "Error:006 ".$e->getMessage());
			die(json_encode($response));
		}
		$response = array( "message" => $webId);
		echo json_encode($response);


		break;

		case "createCompetence":
		$user = new User();
		if($user->data()->role != 'teacher'){
			return; /*Solo un maestro puede crear competencia*/
		}

		$teacherId = $user->data()->id;

		try{

			$name = Input::get('name');
			$name = $name == "" ? "Nueva competencia" : $name;

			$webIds = Input::get('webIds');
			$webIds = array_filter(array_unique($webIds));
			$cleanWebIds = array();

			//Ver que cada webId existe en la BD y que puede ser usado para una competencia
			$w = new Web();
			foreach ($webIds as $key => $id) {
				if( $w->isWebReadyToUseInCompetence($id)){
					array_push($cleanWebIds, $id);
				} else {
					$response = array( "message" => "Asegurate que sean ids de redes existentes");
					die(json_encode($response));
				}
			}

			$competence = new Competence();
			$competenceId = $competence->createNewCompetence($name, $teacherId, $cleanWebIds);

		} catch(Exception $e) {
			$response = array( "message" => "Error:006 ".$e->getMessage());
			die(json_encode($response));
		}

		$response = array( "message" => "success", "response" => $competenceId);
		echo json_encode($response);

		break;

		case "updateCompetence":
		$user = new User();
		if ($user->data()->role != 'teacher') {
			return; /*Solo un maestro puede crear competencia*/
		}

		$teacherId = $user->data()->id;

		try {
			$competenceId = Input::get('competenceId');
			$name = Input::get('name');

			$webIds = Input::get('webIds');
			$webIds = array_filter(array_unique($webIds));
			$cleanWebIds = array();

			//Ver que cada webId existe en la BD y que puede ser usado para una competencia
			$w = new Web();
			foreach ($webIds as $key => $id) {
				if ($w->isWebReadyToUseInCompetence($id)){
					array_push($cleanWebIds, $id);
				} else {
					$response = array("message" => "Asegurate que sean ids de redes existentes");
					die(json_encode($response));
				}
			}

			$competence = new Competence();
			$success = $competence->updateCompetence($competenceId, $name, $cleanWebIds);

		} catch(Exception $e) {
			$response = array("message" => "Error:006 ".$e->getMessage());
			die(json_encode($response));
		}
		if (!$success) {
			$response = array("message" => "Hubo un error guardando los cambios.");
		} else {
			$response = array("message" => "success", "response" => $competenceId);
		}
		echo json_encode($response);

		break;

		case "getWebElementsForEdition":
		$user = new User();
		if($user->data()->role != 'teacher'){
			return; /*Solo un maestro puede accesar*/
		}

		try{
			$webId = Input::get('webId');

			$w = new Web();
			$questions = $w->getQuestionsInWeb($webId);

			echo json_encode($questions);


		}catch(Exception $e) {
			$response = array( "message" => "Error:006 ".$e->getMessage());
			die(json_encode($response));
		}

		break;


		case "gradeWeb":
		$user = new User();
		if($user->data()->role != 'teacher'){
			return; /*Solo un maestro puede asignar valores a la red*/
		}

		//Necesitamos el id del profesor, que es el usuario logueado
		$teacherId = $user->data()->id;

		try{

			$data = Input::get('data');
			$webId = Input::get('webId');
			$competenceId = Input::get('cId');

			$w = new Web();
			$websInCompetenceId = $w->getWebsInCompetenceId($webId, $competenceId);

			$db = DB::getInstance();

			//Guardar la ponderacion de cada pregunta para esa combinacion de red y competencia especifica
			foreach ($data as $key => $p) {
				$splitKey = explode('-', $key);

				$grade = intval($p); //$p es la ponderacion en un string
				$questionId = $splitKey[0];
				$answerId = $splitKey[1];

				$db->insert("answersinwebsincompetence",
					array("answerId"=>$answerId,
						"grade"=>$grade,
						"webInCompetence" => $websInCompetenceId->id));

			}

			//Una vez que se le asigno una ponderacion a cada pregunta
			//hay que decir que esa combinacion de red y competencia ya fue ponderada en su totalidad

			$db->update("websincompetence", $websInCompetenceId->id, array("isGraded" => true));


		} catch(Exception $e) {
			$response = array( "message" => "Error:006 ".$e->getMessage());
			die(json_encode($response));
		}

		$response = array( "message" => "success");
		echo json_encode($response);

		break;

		case "publishCompetence":
		$user = new User();
		if($user->data()->role != 'teacher'){
			return; /*Solo un maestro puede hacerlo*/
		}

		try{
			$competenceId = Input::get('cId');
			$c = new Competence();
			$c->update($competenceId, array("isPublished" => true));
		} catch(Exception $e) {
			$response = array( "message" => "Error:010 ".$e->getMessage());
			die(json_encode($response));
		}

		$response = array( "message" => "success");
		echo json_encode($response);

		break;

		case "webIsGraded":

		$user = new User();
		/*Solo un maestro puede hacerlo*/
		if($user->data()->role != 'teacher'){ return; }

		try{

			$competenceId = Input::get('cId');
			$db = DB::getInstance();
			$sql = "SELECT * FROM websincompetence WHERE competenceId = $competenceId";

			if(!$db->query($sql)->error()) {
				if($db->count()) {

					$everythingIsGraded = true;
					$returned = $db->results();
					foreach ($returned as $key => $webInCompetence) {
						if($webInCompetence->isGraded == false){
							$everythingIsGraded = false;
							break;
						}
					}

					$response = array( "message" => "success", "isGraded" => false);
					if($everythingIsGraded) $response["isGraded"] = true;
					echo json_encode($response);

				}
			}

		} catch(Exception $e) {
			$response = array( "message" => "Error:006 ".$e->getMessage());
			die(json_encode($response));
		}
		break;

		case "addCompetenceToGroup":

		$user = new User();
		/*Solo un maestro puede hacerlo*/
		if($user->data()->role != 'teacher'){ return; }

		try{

			$competenceId = Input::get('competenceId');
			$groupId = Input::get('groupId');
			//Verificar que la competencia no haya sido agregada al grupo anteriormente
			$c = new Competence();
			if($c->competenceExistsInGroup($groupId, $competenceId) ) {
				$response = array( "message" => "Error: La competencia ya fue asignada a ese grupo.");
				echo json_encode($response);
				return;
			}

			//Crear la relacion competencia-grupo
			$db = DB::getInstance();
			$fields = array(
				'competenceId' 	=> intval($competenceId),
				'groupId' => intval($groupId));

			if(!$db->insert('competenceingroup', $fields))
			{
				throw new Exception('There was a problem assigning the student to the group.');
			}

		} catch(Exception $e) {
			$response = array( "message" => "Error:011 ".$e->getMessage());
			die(json_encode($response));
		}
		$response = array( "message" => "success");
		echo json_encode($response);
		break;


		case "answerQuestion":

		$user = new User();

		/*Solo un alumno puede hacerlo*/
		if($user->data()->role != 'student'){ return; }

		try{

			$q = new Question();
			$db = DB::getInstance();
			$questionForStudentId = Input::get('qfs');
			$competenceId = Input::get('c');
			$webId = intval(Input::get('w'));
			$answerId = intval(Input::get('a'));
			$spid = intval(Input::get('sp'));

			//Marcar la pregunta dentro de questionsForStudent como contestada
			$fields = array("answered" => true);
			if(!$db->update('questionsforstudent', $questionForStudentId, $fields)) {
				throw new Exception('There was a problem updating QuestionsForStudent.');
			}

			//Con el id de la respuesta obtenemos su ponderacion
			$wic = null;
			$sql = "SELECT * FROM websincompetence WHERE competenceId = $competenceId and webId = $webId";
			if(!$db->query($sql)->error()) {
				if($db->count()) {
					$wic = $db->first();
				}
			}

			//Con el id de la respuesta obtenemos su feedback
			$feedback = null;
			$sql = "SELECT textFeedback, imageFeedback FROM answer WHERE id = $answerId";
			if(!$db->query($sql)->error()) {
				if($db->count()) {
					$feedback = $db->first();
				}
			}

			$aiwic = null;
			$sql = "SELECT * FROM answersinwebsincompetence WHERE webInCompetence = $wic->id and answerId = $answerId";
			//var_dump($sql);
			if(!$db->query($sql)->error()) {
				if($db->count()) {
					$aiwic = $db->first();
				}
			}
			//var_dump($aiwic);

			$grade = $aiwic->grade;

			//Con la informacion sobre ponderacion
			//Actualizar la ultima pregunta contestada dentro de studentProgress
			$fields = array("lastAnsweredQuestion" => $questionForStudentId, "lastAnswerGrade" => $grade);
			if(!$db->update('studentprogress', $spid, $fields)) {
				throw new Exception('There was a problem updating StudentProgress.');
			}
			//si la pregunta fue contestada correctamnte y ya no hay otro nivel en la red,
			// se le asigna una fecha de terminado

			//Nivel actual
			$level = -1;
			$sql = "SELECT * FROM questionsforstudent WHERE id = $questionForStudentId";
			//var_dump($sql);
			if(!$db->query($sql)->error()) {
				if($db->count()) {
					$r = $db->first();
					$level = $r->level;
				}
			}

			$lastLevel = -1;
			$lastQuestionId = -1;

			$sql = "SELECT * FROM studentprogress WHERE id = $spid";
			//var_dump($sql);
			if(!$db->query($sql)->error()) {
				if($db->count()) {
					$r = $db->first();
					$lastQuestionId = $r->lastQuestion;
				}
			}

			$sql = "SELECT * FROM questionsforstudent WHERE id = $lastQuestionId";
			//var_dump($sql);
			if(!$db->query($sql)->error()) {
				if($db->count()) {
					$r = $db->first();
					$lastLevel = $r->level;
				}
			}

			//var_dump($level);
			//var_dump($lastLevel);

			//Si contesto bien y es el ultimo nivel de la red
			if($level == $lastLevel && $grade > 0){
				$date = date('Y-m-d H:i:s');
				$fields = array( "finishedDate"=>"$date");
				if(!$db->update('studentprogress', $spid, $fields)) {
					throw new Exception('There was a problem updating StudentProgress.');
				}
			}

		} catch(Exception $e) {
			$response = array( "message" => "Error:012 ".$e->getMessage());
			die(json_encode($response));
		}
		$response = array( "message" => "success", "feedback" => $feedback);
		echo json_encode($response);
		break;

		case "unlockStudent":

		$user = new User();
		/*Solo un maestro puede hacerlo*/
		if($user->data()->role != 'teacher'){ return; }

		try{

			$studentId = Input::get('sId');
			$competenceId = Input::get('cId');
			$groupId = Input::get('gId');

			$c = new Competence();
			$c->unlockCompetence($studentId, $groupId, $competenceId);

		} catch(Exception $e) {
			$response = array( "message" => "Error:013 ".$e->getMessage());
			die(json_encode($response));
		}

		$response = array( "message" => "success");
		echo json_encode($response);

		break;

		case "updateGroup":

		$user = new User();
		/*Solo un maestro puede hacerlo*/
		if($user->data()->role != 'teacher'){ return; }

		try{

			$groupId = Input::get('g');
			$name = Input::get('name');

			$g = new Groups();
			$g->update($groupId, array("name" => $name));

		} catch(Exception $e) {
			$response = array( "message" => "Error:014 ".$e->getMessage());
			die(json_encode($response));
		}

		$response = array( "message" => "success");
		echo json_encode($response);

		break;


		default:
		echo "Error: 002";
		break;

	}

}else{
	echo "Error: 001";
}

function isValidIdNumber($idnumber = ""){
	$idnumber = trim($idnumber);
	$error = false;

	if($idnumber == ""){
		return false;
	}
	
	//El primer caracter debe de ser una A,a,L o l
	$firstPart = $idnumber[0];
	if($firstPart != 'a' && $firstPart != 'A' && $firstPart != 'l' && $firstPart != 'L'){
		$error = true;
	}

	//El resto debe de ser numerico
	$sndPart = substr($idnumber, 1);
	if(!is_numeric($sndPart)){
		$error = true;
	}

	if($error){
		return false;
	}

	return true;
}


function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
	/*
	* Return array converted to object
	* Using __FUNCTION__ (Magic constant)
	* for recursive call
	*/
	return array_map(__FUNCTION__, $d);
}
else {
		// Return array
	return $d;
}
}

?>
