<?php 
	if ( $_GET['a'] == 'unset' ) {
		// or this would remove all the variables in the session, but not the session itself 
		session_unset(); 
		echo 'unset';
	} else if ( $_GET['a'] == 'destroy' ) {
		// this would destroy the session variables 
		session_destroy(); 
		echo 'destroy';
	}
?> 