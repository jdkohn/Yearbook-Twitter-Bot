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
$done = FALSE;

foreach($conn->query("SELECT * FROM students WHERE username IS NOT NULL AND checked=0") as $student) {

	$username = $student["username"];

	echo $username . PHP_EOL;

	$nextCursor = "-1";

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
				$nextCursor = "0";
				$done = TRUE;
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
				$description = $jsonObject["users"][$i]["description"];


				foreach($conn->query("SELECT * FROM students WHERE name='$name' AND username IS NULL") as $stu) {
					$conn->query("UPDATE students SET username='$screenname' WHERE name='$name'");
					echo "ADDED USERNAME FOR: " . $name . PHP_EOL;
				}

				$firstlast = explode(" ", $name);
				$twtlast = $firstlast[count($firstlast) - 1];
				$twtfirst = $firstlast[0];

				foreach($conn->query("SELECT * FROM students WHERE username IS NULL") as $stu) {
					$first = $stu["first"];
					$last = $stu["last"];
					$studentname = $stu["name"];

					if((strpos(strtolower($name), strtolower($first)) == TRUE) && (strpos(strtolower($name), strtolower($last)) == TRUE)) {
						$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
						echo "ADDED USERNAME FOR: " . $studentname . PHP_EOL;
						break;
					} else if((strpos(strtolower($name), strtolower($twtfirst)) == TRUE) && (strpos(strtolower($name), strtolower($twtlast)) == TRUE)) {
						$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
						echo "ADDED USERNAME FOR: " . $studentname . PHP_EOL;
						break;
					} else if((strpos(strtolower($screenname), strtolower($first)) == TRUE) && (strpos(strtolower($screenname), strtolower($last)) == TRUE)) {
						$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
						echo "ADDED USERNAME FOR: " . $studentname . PHP_EOL;
						break;
					} else if((strpos(strtolower($screenname), strtolower($first)) == TRUE) && (strpos(strtolower($name), strtolower($last)) == TRUE)) {
						$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
						echo "ADDED USERNAME FOR: " . $studentname . PHP_EOL;
						break;
					} else if((strpos(strtolower($name), strtolower($first)) == TRUE) && (strpos(strtolower($screenname), strtolower($last)) == TRUE)) {
						$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
						echo "ADDED USERNAME FOR: " . $studentname . PHP_EOL;
						break;
					}
				}

				$firstlast = explode(" ", $name);

				$last = $firstlast[count($firstlast) - 1];

				foreach($conn->query("SELECT * FROM students WHERE last='$last' AND username IS NULL") as $student) {
					$studentname = $student["name"];

					$fl = explode(" ", $studentname);
					$first = $fl[0];

					if(strpos(strtolower($screenname), strtolower($first)) == TRUE) {
						$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
						echo "*ADDED USERNAME FOR: " . $name . PHP_EOL;
					} else if(strpos(strtolower($name), strtolower($first)) == TRUE) {
						$conn->query("UPDATE students SET username='$screenname' WHERE name='$studentname'");
						echo "*ADDED USERNAME FOR: " . $name . PHP_EOL;
					}
				}

				if(strpos(strtolower($description), "hale") || strpos(strtolower($description), "nh")) {
					if(mysqli_num_rows($conn->query("SELECT * FROM students WHERE username='$screenname'")) == 0) {
						$conn->query("INSERT INTO students (name, username) VALUES ('$name', '$screenname')");
						echo "$ADDED USERNAME FOR: " . $name . PHP_EOL;
					}
				}

			}
		}
	}
	$conn->query("UPDATE students SET checked=1 WHERE username='$username'");

	if($done) {
		break;
	}
}
?>