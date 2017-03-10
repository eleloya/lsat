<?php
require '../core/init.php';

$user = new User();
$salt = Hash::salt(32);
echo "aqui <br/>";	
try {

	$user->create(array(
		'mail' 	=> "lsat.development@gmail.com",
		'password' 	=> Hash::make("itesm2017", $salt),
		'salt'		=> $salt,
		'username' 	=> "Admin",
		'role'      => 'admin'
		));

	echo "success";	

} catch(Exception $e) {
	die($e->getMessage());
}


?>