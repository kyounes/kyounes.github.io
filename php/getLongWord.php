<?php
// Declare global constants, variables, etc.
$language = "en";
$wordSize = 9;
$word2Guess = "";
$word2GuessIndex = -1;
$word2GuessScrambled = "";

if($language == "en") $longWordsFile = "./languages/en/longWords.txt";
else if($language == "fr") $longWordsFile = "./languages/fr/longWords.txt";

// Read word info from file

$longWords = array();

$wFile = fopen($longWordsFile,'r');
$i = 0;
while ($line = fgets($wFile)) {
	array_push($longWords, $line);
	$i++;
	}
fclose($wFile);

$randIndex = rand(0, $i - 1);

echo json_encode(array(strtoupper(str_shuffle(longWords[$randIndex])), $randIndex));