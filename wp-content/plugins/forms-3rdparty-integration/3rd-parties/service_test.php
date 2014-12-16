<?php
header('Content-Type: text/plain');

$as_success = false;

// manually determine success/failure
if( isset($_REQUEST['fail']) ) {
	if( 'yes' != $_REQUEST['fail'] ) $as_success = true;
}
// randomly determine it
if( rand() < 0.5 ) $as_success = true;

if($as_success){
	?>
	<?xml version="1.0" encoding="UTF-8"?>
<response><status>Matched</status><lead_id>874</lead_id></response>
<?php
}else {
	?>
	<response><status>UnMatched</status><lead_id>875</lead_id></response>
<?php
}

?>

