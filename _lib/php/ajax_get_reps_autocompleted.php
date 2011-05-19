<?php
session_start();
require_once("db.class.php/db.class.php");
$DB = new DB();

$q = strtolower($_GET["q"]);
if (!$q) return;

$items = array();
$res = $DB->query("SELECT id, CONCAT(firstname, ' ', lastname) AS name FROM reps WHERE username != '' AND email != ''");
while($r = $DB->fetchNextObject($res)){
	$items[$r->name]=$r->id;
}

foreach ($items as $key=>$value) {
	if (strpos(strtolower($key), $q) !== false) {
		echo "$key|$value\n";
	}
}

?>