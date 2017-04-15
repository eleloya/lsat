<?php
require 'core/init.php';

if (Input::exists('get')) {
	$user = new User();
	$user->checkIsValidUser('teacher');

	$teacherId = $user->data()->id;
	$competenceId = trim(Input::get("id"));

	if ($competenceId != '') {
		$c = new Competence();
		$competence = $c->getValidCompetence($competenceId, $teacherId);

		if ($competence == false) {
			Redirect::to('allCompetences.php');
		}
	}

	if ($competenceId == "" || !is_numeric($competenceId)) {
		Redirect::to('allCompetences.php');
	}

	$competence = new Competence();
	$competence->delete($competenceId);
}

Redirect::to('allCompetences.php');

?>