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

	// Perform the request
	$twitter = new TwitterAPIExchange($settings);
	$json = $twitter->setGetfield($getfield)
	->buildOauth($url, $requestMethod)
	->performRequest();


	$jsonObject = json_decode($json, true);

	$json_string = json_encode($jsonObject, JSON_PRETTY_PRINT);

	//echo $json_string . PHP_EOL;

	echo "COUNT: " . count($jsonObject["users"]) . PHP_EOL;

	for($i=0; $i<count($jsonObject["users"]); $i++) {

		//check to see if is a student -> name matches, last name matches, something along 
		//							   ->those lines, check with Mr. Snyder to see how
		//							   ->how accurate we need to be

		//check to see if in mysql database

		//

		//add into mysql database
		//$conn->query("UPDATE halestudents SET username='$screenname' WHERE name='$name'")

		//add into array

		//go to next "counter" if necessary

		//move to next user
	}
?>