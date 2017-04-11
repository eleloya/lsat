<?php
require 'core/init.php';

if (Input::exists('get')) {
	$user = new User();
	$user->checkIsValidUser('teacher');

	$teacherId = $user->data()->id;
	$webId = trim(Input::get("id"));

	if ($webId != '') {
		$w = new Web();
		$web = $w->getValidWeb($webId, $teacherId);

		if ($web == false) {
			Redirect::to('allWebs.php');
		}
	}

	if ($webId == "" || !is_numeric($webId)) {
		Redirect::to('allWebs.php');
	}

	$web = new Web();
	$web->delete($webId);
}

Redirect::to('allWebs.php');

?>