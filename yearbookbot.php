<?php

include "createconnection.php";

require_once('TwitterAPIExchange.php');

//$username = $argv[1];

$settings = array(
	'oauth_access_token' => "441378114-UH2UYwqhnQ9av7sWpJgZBHdIxxbNOEegYQqBlMTG",
	'oauth_access_token_secret' => "PdMjEa9xT0hrNVhD9ogGq1bKOcMtthmRABStPx4hXWvhL",
	'consumer_key' => "vWQ7bggg67cAKtuHF2m6YKLNB",
	'consumer_secret' => "MBXAFbar7AYIfjYMURMjM6FLgQczpzxpWrhdxK490RlMFCndfB"
	);


foreach($conn->query("SELECT * FROM students WHERE username IS NOT NULL AND checked=0") as $student) {

	$username = $student["username"];

	/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/
	$url = 'https://api.twitter.com/1.1/followers/list.json';
	$requestMethod = 'GET';
	$getfield = '?screen_name=@' . $username . "&count=200";

		// Perform the request
	$twitter = new TwitterAPIExchange($settings);
	$json = $twitter->setGetfield($getfield)
	->buildOauth($url, $requestMethod)
	->performRequest();

	echo "REQUEST MADE: " . $username . PHP_EOL;

	$jsonObject = json_decode($json, true);

	if(isset($jsonObject["errors"])) {
		if($jsonObject["errors"][0]["message"] == "Rate limit exceeded") {
			echo "LIMIT EXCEEDED" . PHP_EOL;
			break;
		}
	}

	if(isset($jsonObject["next_cursor_str"])) {
		$nextCursor = $jsonObject["next_cursor_str"];
	} else {
		$nextCursor = 0;
	}

	//echo "NEXT CURSOR: " . $nextCursor . PHP_EOL;

	if(isset($jsonObject["users"])) {
		for($i=0; $i<count($jsonObject["users"]); $i++) {

			$name=$jsonObject["users"][$i]["name"];

			$screenname = $jsonObject["users"][$i]["screen_name"];

			$q = "SELECT * FROM students WHERE name='$name' AND username IS NULL";

			foreach($conn->query($q) as $stu) {
				$conn->query("UPDATE students SET username='$screenname' WHERE name='$name'");

				echo "ADDED USERNAME FOR: " . $name . PHP_EOL;
			}

			$firstlast = explode(" ", $name);
			$last = $firstlast[count($firstlast) - 1];
			$first = $firstlast["0"];

			foreach($conn->query("SELECT * FROM students WHERE last='$last' AND username IS NULL") as $student) {
				$studentname = $student["name"];
				if(strpos(strtolower($screenname), strtolower($first)) == TRUE) {
					$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
					echo "*ADDED USERNAME FOR: " . $name . PHP_EOL;
				} else if(strpos(strtolower($name), strtolower($first)) == TRUE) {
					$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
					echo "*ADDED USERNAME FOR: " . $name . PHP_EOL;
				}
			}
		}
	}

	while($nextCursor != "0") {
		$url = 'https://api.twitter.com/1.1/followers/list.json';
		$requestMethod = 'GET';
		$getfield = '?screen_name=@' . $username . "&count=200&cursor=" . $nextCursor;

		// 		// Perform the request
		$twitter = new TwitterAPIExchange($settings);
		$json = $twitter->setGetfield($getfield)
		->buildOauth($url, $requestMethod)
		->performRequest();

		$jsonObject = json_decode($json, true);

		if(isset($jsonObject["errors"])) {
			if($jsonObject["errors"][0]["message"] == "Rate limit exceeded") {
				echo "LIMIT EXCEEDED" . PHP_EOL;
				break;
			}
		}


		if(isset($jsonObject["next_cursor_str"])) {
			$nextCursor = $jsonObject["next_cursor_str"];
		} else {
			$nextCursor = 0;
		}

		//echo "NEXT CURSOR: " . $nextCursor . PHP_EOL;

		if(isset($jsonObject["users"])) {
			for($i=0; $i<count($jsonObject["users"]); $i++) {

				$name=$jsonObject["users"][$i]["name"];
				$screenname = $jsonObject["users"][$i]["screen_name"];

				$q = "SELECT * FROM students WHERE name='$name' AND username IS NULL";

				foreach($conn->query($q) as $stu) {
					$conn->query("UPDATE students SET username='$screenname' WHERE name='$name'");
					echo "ADDED USERNAME FOR: " . $name . PHP_EOL;
				}
			}

			$firstlast = explode(" ", $name);

			$last = $firstlast[count($firstlast) - 1];
			$first = $firstlast[0];

			foreach($conn->query("SELECT * FROM students WHERE last='$last' AND username IS NULL") as $student) {
				$studentname = $student["name"];
				if(strpos(strtolower($screenname), strtolower($first)) == TRUE) {
					$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
					echo "*ADDED USERNAME FOR: " . $name . PHP_EOL;
				} else if(strpos(strtolower($name), strtolower($first)) == TRUE) {
					$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
					echo "*ADDED USERNAME FOR: " . $name . PHP_EOL;
				}
			}
		}
	}
	$conn->query("UPDATE students SET checked=1 WHERE username='$username'");
}
?>