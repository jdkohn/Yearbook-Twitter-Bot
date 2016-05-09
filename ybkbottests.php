<?php

include "createconnection.php";

require_once('TwitterAPIExchange.php');


$username = $argv[1];


$settings = array(
	'oauth_access_token' => "441378114-UH2UYwqhnQ9av7sWpJgZBHdIxxbNOEegYQqBlMTG",
	'oauth_access_token_secret' => "PdMjEa9xT0hrNVhD9ogGq1bKOcMtthmRABStPx4hXWvhL",
	'consumer_key' => "vWQ7bggg67cAKtuHF2m6YKLNB",
	'consumer_secret' => "MBXAFbar7AYIfjYMURMjM6FLgQczpzxpWrhdxK490RlMFCndfB"
	);



/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/
$url = 'https://api.twitter.com/1.1/followers/list.json';
$requestMethod = 'GET';
$getfield = '?screen_name=@' . $username . "&count=200";

// 		// Perform the request
$twitter = new TwitterAPIExchange($settings);
$json = $twitter->setGetfield($getfield)
->buildOauth($url, $requestMethod)
->performRequest();


$jsonObject = json_decode($json, true);

$json_string = json_encode($jsonObject, JSON_PRETTY_PRINT);

echo $json_string;

$nextCursor = $jsonObject["next_cursor_str"];

$counter = 0;

for($i=0; $i<count($jsonObject["users"]); $i++) {

	$name=$jsonObject["users"][$i]["name"];
	$screenname=$jsonObject["users"][$i]["screen_name"];


	$firstlast = explode(" ", $name);
	$last = $firstlast[count($firstlast) - 1];
	$first = $firstlast["0"];

	foreach($conn->query("SELECT * FROM students WHERE last='$last' AND username IS NULL") as $student) {
		$studentname = $student["name"];

		$fl = explode(" ", $studentname);
		$first = $firstlast["0"];		

		if(strpos(strtolower($screenname), strtolower($first)) == TRUE) {
			$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
		} else if(strpos(strtolower($name), strtolower($first)) == TRUE) {
			$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
		}
	}
	$counter++;
}


if($nextCursor !== "0") {
	while($nextCursor != "0") {

		echo "NEW CURSOR" . PHP_EOL;

		$url = 'https://api.twitter.com/1.1/followers/list.json';
		$requestMethod = 'GET';
		$getfield = '?screen_name=@' . $username . "&count=200&cursor=" . $nextCursor;

		// 		// Perform the request
		$twitter = new TwitterAPIExchange($settings);
		$json = $twitter->setGetfield($getfield)
		->buildOauth($url, $requestMethod)
		->performRequest();

		$jsonObject = json_decode($json, true);

		

		for($i=0; $i<count($jsonObject["users"]); $i++) {

			$counter++;

			// $name=$jsonObject["users"][$i]["name"];
			// $screenname=$jsonObject["users"][$i]["screen_name"];
			

			// if(mysqli_num_rows($conn->query("SELECT * FROM students WHERE name='$name' AND username IS NULL")) > 0) {
			// 	$conn->query("UPDATE students SET username='$screenname' WHERE name='$name'");
			// }
		}
		$nextCursor = $jsonObject["next_cursor_str"];
	} 
}

echo $counter;

?>