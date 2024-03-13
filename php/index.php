<!doctype html>
<html lang="en">
<?php
// Declare global constants, variables, etc.
$language = "en";
$wordSize = 9;
$word2Guess = "";
$word2GuessIndex = -1;
$word2GuessScrambled = "";
$wordYesterday = "";

$justForFun = false;
if(isset($_GET['9in90'])) $justForFun = true;


if($language == "en") {
	$wordsFile = "./languages/en/words-of-day.txt";
	$longWordsFile = "./languages/en/longWords.txt";
	$allPossibleWordsFile = './languages/en/allPossibleWords.txt';
	$possibleWordsFile = "./languages/en/possibleWordsToday.txt";
	}
else if($language == "fr") {
	$wordsFile = "./languages/fr/words-of-day.txt";
	$longWordsFile = "./languages/fr/longWords.txt";
	$allPossibleWordsFile = './languages/fr/allPossibleWords.txt';
	$possibleWordsFile = "./languages/fr/possibleWordsToday.txt";
	}
	
function included($wordLetters, $targetLetters) {
	global $wordSize;
	
    $targetIndex = 0;
    foreach($wordLetters as $letter) {
        while($letter != $targetLetters[$targetIndex]) {
            $targetIndex = $targetIndex + 1;
            if($targetIndex > ($wordSize - 1)) {
                return(false);
				}
			}
		}
    return(true);
	}

// Read word info from file

$wordsHistory = array();
$possibleWords = array();

if(!$justForFun) {

	$wFile = fopen($wordsFile,'r');
	$i = 0;
	while ($line = fgets($wFile)) {
		array_push($wordsHistory, explode(" ", $line));
		$i++;
		}
	fclose($wFile);

	$historyLength = $i;
	$word2Guess = $wordsHistory[$historyLength-1][1];
	$wordYesterday = $wordsHistory[$historyLength-2][1];
	$word2GuessIndex = (int) $wordsHistory[$historyLength-1][2];
	$word2GuessScrambled = $wordsHistory[$historyLength-1][3];
	$word2GuessValue = (int) $wordsHistory[$historyLength-1][4];
	
	$pFile = fopen($possibleWordsFile,'r');
	while ($line = fgets($pFile)) {
		array_push($possibleWords, str_replace(array("\n", "\r"), "", $line));
		}
	fclose($pFile);
	}
else {
	$longWords = array();

	$wFile = fopen($longWordsFile,'r');
	$i = 0;
	while ($line = fgets($wFile)) {
		array_push($longWords, $line);
		$i++;
		}
	fclose($wFile);
	$word2GuessIndex = rand(0, $i - 1);
	$word2Guess = str_replace(array("\n", "\r"), "", $longWords[$word2GuessIndex]);
	$word2GuessScrambled = str_shuffle($word2Guess);
	
	$wordLetters = str_split($word2Guess);
	sort($wordLetters);

	$pFile = fopen($allPossibleWordsFile,'r');
	$i = 0;
	while ($line = fgets($pFile)) {
		$dictWord = str_replace(array("\n", "\r"), "", $line);
		$dictLetters = str_split($dictWord);
		sort($dictLetters);
		if(included($dictLetters, $wordLetters)) {
			array_push($possibleWords, $dictWord);
			}
		}
	fclose($pFile);
	}


// echo $word2Guess, $word2GuessIndex, $word2GuessScrambled, $word2GuessValue;

?>
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
	<link rel="stylesheet" href="./css/style_imp.css" >

    <title>9in90</title>
  </head>
<body>

	<div class="helpscreen" id = "helpscreenid">
		<div class="helpheader">
			<div class="helpheaderleft">&nbsp;</div>
			<div class="helptitle"><h1>How This Works</h1></div>
			<div class="helpheaderright" onclick="hideHelp()">X</div>
		</div>
		<div class="helptext">
			<p>Find the 9 letter word in 90 seconds or less. Try as many valid words as you like.</p>
			<p>Longer and more valuable words earn a higher score.</p>
			<p>Higher scores result in hints that show the first few letters of the solution. For example, 70 points yields three hints.</p>
			<p>Example:</p>
			<img class="helpimg" src="./img/help-img.png">
			<p>A new 9in90 word will be available every day.</p>
			<p class="helpwarning" id="helpwarningid"></p>
		</div>
	</div>
	
	<div class="settingscreen" id = "settingscreenid">
		<div class="settingheader">
			<div class="settingheaderleft">&nbsp;</div>
			<div class="settingtitle"><h1>Settings</h1></div>
			<div class="settingheaderright" onclick="hideSettings()">X</div>
		</div>
		<div class="settingtext">
			<table class="settingtable">
				<tr>
					<td class="settingname">More Hints</td>
					<td class="settingcontrol">
						<label class="switch"><input type="checkbox" id="hintslider" onclick="toggleHints()"><span class="slider round"></span></label>
					</td>
				</tr>
				<tr>
					<td class="settingname">Dark Theme</td>
					<td class="settingcontrol">
						<label class="switch"><input type="checkbox"  id="themeslider" checked="true" onclick="toggleTheme()"><span class="slider round"></span></label>
					</td>
				</tr>
			</table>
		</div>
	</div>	

	<div class="resultscreen" id = "resultscreenid">
		<div class="resultcenter">
			<div class="resultheader">
				<div class="resultheaderleft">&nbsp;</div>
				<div class="resulttitle"><h1>Stats</h1></div>
				<div class="resultheaderright" onclick="hideResults()">X</div>
			</div>
			<div class="resulttext">
				<div id="resultmsgid"></div>
			</div>

			<table class="statstable" id="statstableid">
				<tr>
					<td class="stat" id="numsessions"></td>
					<td class="stat" id="numlongest"></td>
					<td class="stat" id="currentstreak"></td>
					<td class="stat" id="beststreak"></td>
				</tr>
				<tr>
					<td class="stattext">Games</td>
					<td class="stattext">Wins</td>
					<td class="stattext">Current<br>Streak</td>
					<td class="stattext">Best<br>Streak</td>
				</tr>
			</table>

			<div class="resultcharts">
				<div id="successratechart"></div>
				<div id="histchart"></div>
			</div>
		</div>
	</div>	

	<div class="headingcontainer">
		<div class="headingleft" onclick="showHelp()">
		<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
			<path fill="var(--fgnd)" d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"></path>
		</svg>
		</div>
		<div class="headingtitle"><h1>9in90<span id="chancesleft"></span></h1></div>
		<div class="headingright">
			<span class="resultsicon" onclick="showResults()">
				<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
				<path fill="var(--fgnd)" d="M16,11V3H8v6H2v12h20V11H16z M10,5h4v14h-4V5z M4,11h4v8H4V11z M20,19h-4v-6h4V19z"></path>
				</svg>
			</span>
			<span class="settingsicon" id="settingsid" onclick="showSettings()">
			<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
				<path fill="var(--fgnd)" d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"></path>
			</svg>
			</span>
		</div>
	</div>

    <div class="game-container">

	<table class="gametable">
		<tr class="letterrow" id="word2guess">
		<?php
		for($i=0; $i<$wordSize; $i++) {
			echo "<td class = 'lettercell' id = 'lettercell", $i, "'></td>\n";
			}
		?>

		</tr>
		
		<tr class="guessrow" id="guessword">
		<?php
		for($i=0; $i<$wordSize; $i++) {
			echo "<td class = 'guesscell' id = 'guesscell", $i, "'></td>\n";
			}   
		?>

		</tr>
		<tr class="gameactionsrow" id="guessbuttons">
			<td colspan="2"><button class="gbutton" role="button" id = "clearButton" onclick="clearWord()">Clear</button></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td colspan="3"><button class="gbutton" role="button" id = "submitButton" style="float: right;" onclick="submitWord()">Score Word</button></td>
		</tr>
		<tr class="hintrow" id="hintword">
<?php
		for($i=0; $i<$wordSize; $i++) {
			echo "<td class = 'hintcell' id = 'hintcell", $i, "'></td>\n";
			}
?>

		</tr>

	</table>

	<div class="consolesection">
		<div class="col-md-12 text-center">
			<progress value="90" max="90" id="pageBeginCountdown"></progress> <span><span id="pageBeginCountdownText">90 </span> seconds</span>
		</div>
	</div>
	

	<div class="consolesection">
		<div class="col-md-12 text-center">
			<div id="mylog"></div>
		</div>
	</div>

</div>
<div class="playedsection" id="playedsectionid">
	<table class="playedtable" id='playedwords'/>
		<tr><td class="playedtitle" colspan="2">Attempted Words</td></tr>
	</table>
</div>


<!-- <script type="text/javascript" src="./js/9in90.js"></script> -->
 <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
// Google chart stuff at bottom

// var wordsection = document.querySelector(".wordsection");
// var lettersection = document.querySelector(".lettersection");
// var guesssection = document.querySelector(".guesssection");
// var hintsection = document.querySelector(".hintsection");
// document.getElementById("game-over").addEventListener("click", ()=>{ window.location.reload()  })
// document.getElementById("won").addEventListener("click", ()=>{ window.location.reload()  })

var word2guess = document.getElementById("word2guess");
var guessword = document.getElementById("guessword");
var guessbuttons = document.getElementById("guessbuttons");
var hintrow = document.getElementById("hintrow");


//used later to declare win and lose
var tryIndex = 0;
const maxTries = 3;
let tryWord = [];
tryWord.push('');
const wordSize = 9;
var guessLetterCount = 0;
var first5 = <?php echo '"'.substr($word2Guess,0,5).'"'; ?>;
var justForFun = <?php if($justForFun) echo "true"; else echo "false"; ?>;
var wordYesterday = <?php echo '"'.$wordYesterday.'"'; ?>;


let guessStack = [];
let letterStack = [];

let letterValues = {
					'A' : 1, 'B' : 3, 'C' : 2, 'D' : 2, 'E' : 1, 'F' : 4, 'G' : 3, 'H' : 2,
					'I' : 1, 'J' : 8, 'K' : 5, 'L' : 1, 'M' : 2, 'N' : 1, 'O' : 1, 'P' : 3,
					'Q' : 10, 'R' : 1, 'S' : 1, 'T' : 1, 'U' : 2, 'V' : 4, 'W' : 4, 'X' : 5,
					'Y' : 4, 'Z' : 6
					 };

// var letterArray = "{{letters}}".replace(/ /g, '').split('');
// var targetIndex = {{targetIndex}};
var wordResult = "";


var letterArray = "<?php echo $word2GuessScrambled; ?>".replace(/ /g, '').split('');
var targetIndex = <?php echo $word2GuessIndex; ?>;
var possibleWords = <?php echo '["' . implode('", "', $possibleWords) . '"]'; ?>;

// section to log into element instead console
var actuallog = console.log;

console.log = (...args) => {
    args.map(arg => document.querySelector("#mylog").innerHTML += arg + '<br>')
	}


var numHints = 0;
var numHintsPrev = 0;
var timeHints = 0;
var gameTime = 90;
var winTime = 0;
var timeLeft = gameTime;
var timesUp = false;
var gameOver = false;
var gameStopped = false;
var playedToday = false;

// Cookies
var cookiesEnabled = navigator.cookieEnabled;
var cookiesUpdated = false;
var numSessions = 0;
var numLongest = 0;
var bestTimeEver = gameTime;
var topScoreToday = 0.0;
var avgWinTime = 0.0;
var topLengthToday = 0;
var lastSessionDate = "";
let timeHistory = Array(parseInt(gameTime/10));
let today = new Date();
var todayDate = today.getDate().toString() + '/' + (today.getMonth() + 1).toString() + '/' + today.getFullYear().toString();
var streak = 0;
var bestStreak = 0;

var histChartData = [];
var histChart;

var rootProps = document.querySelector(':root');
var moreHints = false;
var darkTheme = true;

var pauseTimer = false;

window.onbeforeunload = saveState;

for(var i=0; i<parseInt(gameTime/10); i++ ) timeHistory[i] = 0;

if (cookiesEnabled){
	var tempTheme;
	var tempHints;
	
	var temp = getCookie("darkTheme");
	if (temp != null) tempTheme = (temp == "true")? true: false;
	if (!tempTheme) {
		var node = document.getElementById("themeslider");
		node.checked = false;
		toggleTheme();
		}
	var temp = getCookie("moreHints");
	if (temp != null) tempHints = (temp == "true")? true: false;
	if(tempHints) {
		var node = document.getElementById("hintslider");
		node.checked = true;
		toggleHints();
		}
	var temp = getCookie("numSessions");
	numSessions = parseInt(temp);
	temp = getCookie("numLongest");
	if (temp != null) numLongest = parseInt(temp);
	temp = getCookie("bestTimeEver");
	if (temp != null) bestTimeEver = parseInt(temp);	
	temp = getCookie("avgWinTime");
	if (temp != null) avgWinTime = parseFloat(temp);
	temp = getCookie("timeHistory");
	if (temp != null) timeHistory = JSON.parse(temp);
	temp = getCookie("lastSessionDate");
	if (temp != null) lastSessionDate = temp;
	temp = getCookie("streak");
	if (temp != null) streak = parseInt(temp);
	temp = getCookie("bestStreak");
	if (temp != null) bestStreak = parseInt(temp);
	if(lastSessionDate === todayDate) playedToday = true;
//	if(!playedToday) showHelp(true);
//	printMsg("numSessions = " + numSessions + ", topScoreEver = " + niceFloat(topScoreEver));
	}
//else showHelp(false);


// substitution test
//document.getElementById('embed').innerHTML = "{{embed}}"


//alphabet array to select letters from
// let alphabetArray = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
//create alphabets to select before fetching the word

// console.log(letterArray)

google.charts.load("current", {packages:["corechart"]});

if((justForFun) || (!playedToday)) {
	if((!cookiesEnabled) || (numSessions == 0)) showHelp();
	let i = 0;
	letterArray.map((letter)=>{
		let tdnode = document.getElementById("lettercell" + i)
		
		let node = document.createElement("span")
		node.setAttribute("class", "alphabet")
		node.setAttribute("id", letter + i)
		let letterNode = document.createTextNode(letter)
		node.appendChild(letterNode)
		
//		lletter = letter.toLowerCase();
		let node1 = document.createElement("span")
		node1.setAttribute("class", "valuedigit")
		let valueNode = document.createTextNode(letterValues[letter])
		node1.appendChild(valueNode)
		
		node.appendChild(node1)
		node.addEventListener('click', selectLetter)

		tdnode.appendChild(node)

		i = i + 1
	})

//	setTimeout(function(){ showHelp(cookiesEnabled); }, 1000);

	// ProgressCountdown(60, 'pageBeginCountdown', 'pageBeginCountdownText').then(value => alert(`Page has started: ${value}.`));
	ProgressCountdown('pageBeginCountdown', 'pageBeginCountdownText').then(value => stopGame(true, true));
	}
else {
	stopGame(false, true);
	}

function ProgressCountdown( bar, text) {
  return new Promise((resolve, reject) => {
	var countdownTimer = setInterval(() => {
	  if(!pauseTimer) timeLeft--;

	  document.getElementById(bar).value = timeLeft;
	  document.getElementById(text).textContent = timeLeft;
	  
	  if(moreHints && ((timeLeft == (gameTime - 30)) || (timeLeft == (gameTime - 60)))) {
		newHints = parseInt((90 - timeleft) / 15);
		if (newHints > numHints) {
				numHintsPrev = numHints;
				numHints = newHints;
				displayHints(first5);
				}
		}
	  if (timeLeft <= 0) timesUp = true;
	  if ((timeLeft <= 0) || (gameOver)) {
		clearInterval(countdownTimer);
		resolve(true);
	  }
	}, 1000);
  });
}


// document.querySelectorAll(".alphabet").forEach(each=> {each.addEventListener('click', selectLetter)});

function printMsg(message) {
	console.log(message);
	}

function niceFloat(number) {
	return number.toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1});
	}

function saveCookies(){
	if (cookiesEnabled) {
		if(!cookiesUpdated) {
			cookiesUpdated = true;
			setCookie("darkTheme", darkTheme, 365);
			setCookie("moreHints", moreHints, 365);


			setCookie("numSessions", numSessions, 365);
			setCookie("numLongest", numLongest, 365);
			setCookie("bestTimeEver", bestTimeEver, 365);
			setCookie("avgWinTime", avgWinTime, 365);
			setCookie("timeHistory", JSON.stringify(timeHistory), 365);
			setCookie("lastSessionDate", todayDate, 365);
			setCookie("streak", streak, 365);
			setCookie("bestStreak", bestStreak, 365);
			}
		}
	}


function setCookie(cname, cvalue, exdays) {
	const d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	let expires = "expires="+d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}

function getCookie(name) {
    var match = document.cookie.match(RegExp('(?:^|;\\s*)' + name + '=([^;]*)')); 
    return match ? match[1] : null;
	}

function deleteCookie(name) {
    document.cookie = name +"=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;";
	}

function stopGame(doCookies, doResult) {
	var resultMsg="";
	
	if(!gameStopped) {					//Test if we already got here somehow...
		gameStopped = true;
		// Disable letter event listeners
		document.querySelectorAll(".alphabet").forEach(each=> {each.removeEventListener('click', selectLetter)});
		document.getElementById("submitButton").disabled = true;
		document.getElementById("clearButton").disabled = true;

		document.querySelectorAll(".resultheaderright").forEach(each=> {each.removeEventListener('click', hideResults()); each.innerHTML="&nbsp";});
		
		numSessions += 1;
		
		resultMsg += "<p>";

		if(gameOver) {									// Win
			winTime = gameTime - timeLeft;
			timeHistory[parseInt(winTime / 10)] += 1;
			
			resultMsg += "Nice! You've solved today's 9in90 puzzle in " + winTime + " seconds. ";
		
			if(numLongest > 0) avgWinTime = ((avgWinTime * numLongest) + winTime) / (numLongest + 1);
			else avgWinTime = winTime;
			numLongest += 1;
			streak += 1;
			if(streak > bestStreak) bestStreak = streak;
			if(winTime < bestTimeEver) {
				bestTimeEver = winTime;
				resultMsg += "This is your best time ever.</p>";
				}
			else resultMsg += "Your best result is still " + bestTimeEver + " seconds.</p>";
			}
		else if(timesUp) {								// Timeout without winning
			resultMsg += "Time's up! </p>";
			streak = 0;
			}
		
		if(doCookies) saveCookies();
		if(doResult) showResults(resultMsg);
		}
	}

//function on selecting letter event
function selectLetter(event){
	if (!gameStopped) {
		let node = event.target;
		if (node.id == "") node = node.parentNode;			// If click on value, make parent target
		let selectedLetter = node.innerText[0];
	// Check if letter has been selected already, otherwise mark it and move on
		if(!node.classList.contains('bg')) {
			guessLetterCount = guessLetterCount + 1;
			letterStack.push(node.id);
			tryWord[tryIndex] += selectedLetter;
			document.getElementById(node.id).classList.add("bg");
			

			let node1 = document.createElement("span");
			node1.setAttribute("class", "guessletter");
			guessLetterId = selectedLetter + selectedLetter + guessLetterCount;
			node1.setAttribute("id", guessLetterId);
			guessStack.push(guessLetterId);

	// Add it to the right cell
			
			let guesscell = document.getElementById("guesscell" + (guessLetterCount - 1));
			let guessNode = document.createTextNode(selectedLetter);
			node1.appendChild(guessNode);
			if(guessLetterCount > 1) {
				document.getElementById(guessStack[guessLetterCount-2]).removeEventListener('click', removeLetter);
				document.getElementById(guessStack[guessLetterCount-2]).setAttribute("class", "guessletter");
				}
			node1.addEventListener('click', removeLetter);
			node1.classList.add("lastguess");
			guesscell.appendChild(node1);

			}
	//	printMsg(tryWord[tryIndex]);
		}
	}

function removeLetter(event) {

	if(!gameStopped) {
		guessStack.pop();
		tryWord[tryIndex] = tryWord[tryIndex].slice(0,tryWord[tryIndex].length-1);
		guessLetterCount = guessLetterCount - 1;
		var node = document.getElementById(event.target.id);
		node.parentNode.removeChild(node);
		if(guessLetterCount > 0) {
			node = document.getElementById(guessStack[guessLetterCount - 1]);
			node.addEventListener('click', removeLetter);
			node.classList.add("lastguess");
			}
	// Now take care of the original letters
		availLetterId = letterStack.pop();
		node = document.getElementById(availLetterId);
		node.setAttribute('class', 'alphabet');
		}
	}

function displayHints(hintString){
	for(i = numHintsPrev; i < numHints; ++i) {
		let node = document.createElement("span");
		node.setAttribute("class", "hintletter");
//		guessLetterId = selectedLetter + selectedLetter + guessLetterCount;
//		node.setAttribute("id", guessLetterId);
//		guessStack.push(guessLetterId);
		let hintNode = document.createTextNode(hintString[i]);
		node.appendChild(hintNode);
		let hintcell = document.getElementById("hintcell" + i);
		hintcell.appendChild(node);		
		}
	}

function value(testWord) {
	var sum = 0;
	for(var i=0; i<testWord.length; ++i) {
		sum = sum + letterValues[testWord[i]];
	}
	return(sum);
}

function grade(testWord) {
	var wordValue = value(testWord);
	
	var wordValueGrade = 50 * (value(testWord) / value("<?php echo $word2GuessScrambled; ?>"));
    var wordLengthGrade = 50 * (testWord.length / wordSize);
	
	return(wordValueGrade + wordLengthGrade);
}

function submitWord() {

	if(guessLetterCount > 0) {
		if((tryIndex == 0) || ((tryIndex > 0) && (tryWord.lastIndexOf(tryWord[tryIndex], tryIndex - 1) < 0))) {
			testWord = tryWord[tryIndex];
			tryIndex = tryIndex + 1;
			tryWord.push('');	
//			clearWord();
			if(possibleWords.includes(testWord)) {
				wordGrade = grade(testWord);
				if(wordGrade > topScoreToday) {
					var newHints = 0;
					if((testWord.length != wordSize) && (wordGrade > 50.0)) newHints = Math.min(((wordGrade - 45.0)/10).toFixed(), 4);
					if (newHints > numHints) {
							numHintsPrev = numHints;
							numHints = newHints;
							displayHints(first5);
							}
						topScoreToday = wordGrade;
						}
					if(testWord.length > topLengthToday) topLengthToday = testWord.length;
					if((testWord.length) == wordSize) {
						gameOver = true;
						stopGame(true, true);
						}
					//printMsg("Score for " + wordResult.word + ": " + niceFloat(wordResult.wordGrade))
					displayPlayed("<tr><td class='scoreword'>" + testWord + "</td><td class='scorescore'>" + niceFloat(wordGrade) + '</td></tr>');
					}
				else {
					displayPlayed("<tr><td class='scoreword'>" + testWord + "</td><td class='scorescore'>" + "Inv" + '</td></tr>');
					}
			if(!gameOver) clearWord();
//					saveCookies();
			}
		else if((tryIndex > 0) && (tryWord.lastIndexOf(tryWord[tryIndex], tryIndex - 1) >= 0)) {
			displayPlayed("<tr><td class='scoreword'>" + tryWord[tryIndex] + "</td><td class='scorescore'>" + "Rep" + '</td></tr>');
			tryIndex = tryIndex + 1;
			tryWord.push('');	
			clearWord();			
			}
//		tryIndex = tryIndex + 1;
//		tryWord.push('');	
//		clearWord();
		}
	}

function clearWord() {
	// Delete displayed letters
	
	// Clear selected letters and current try word
	document.querySelectorAll(".alphabet").forEach(each=> {each.setAttribute('class', 'alphabet')});
	tryWord[tryIndex] = '';
	guessLetterCount = 0;
	for(i = 0; i < wordSize; ++i) {
		var parent = document.getElementById("guesscell"+ i);
		while (parent.firstChild) {
			parent.removeChild(parent.firstChild);
			}
		}
	guessStack.length = 0;
	letterStack.length = 0;
    }

function saveState() {
	if((timeLeft < 45) &&(!cookiesUpdated)) {
		numSessions += 1;
		saveCookies();
		}
	}

function displayPlayed(message) {

	if(document.getElementById("playedsectionid").style.display != "flex") document.getElementById("playedsectionid").style.display = "flex";
	document.querySelector("#playedwords").innerHTML += message;
	}
	
function showResults(resultMsg) {	
	pauseTimer = true;

	if(!resultMsg) var resultMsg = "";
	
		
	if(numSessions > 0) {
		var node = document.getElementById("numsessions");
		node.innerHTML = numSessions;
		node = document.getElementById("numlongest");
		node.innerHTML = numLongest;
		node = document.getElementById("currentstreak");
		node.innerHTML = streak;
		node = document.getElementById("beststreak");
		node.innerHTML = bestStreak;
		}
	else {
		resultMsg += "<p>There are no stats to display yet.</p>";
		var node = document.getElementById("statstableid");
		node.display = "none";
		}

	drawResultCharts();
 
	var textNode = document.getElementById("resultmsgid");
	textNode.innerHTML = resultMsg;

	var node = document.getElementById("resultscreenid");
	node.style.height = "100%";
	}
	
function hideResults() {
	pauseTimer = false;
	var node = document.getElementById("resultscreenid");
	node.style.height = "0px";
	}

function saveSettings() {
	if (cookiesEnabled) {
		setCookie("darkTheme", darkTheme, 365);
		setCookie("moreHints", moreHints, 365);
		}
	}

function showSettings() {
	pauseTimer = true;
	var node = document.getElementById("settingscreenid");
//	node.style.display = "block";
//	node.style.opacity = "1";
	node.style.height = "100%";
	// show some game explanation in a pop up, wait for user to click ok
	}

function hideSettings(){
	pauseTimer = false;
	var node = document.getElementById("settingscreenid");
	node.style.height = "0px";
//	node.style.overlay = "hidden";
//	node.style.transition = "all 0.4s ease";
//	node.style.display = "none";
//	node.style.opacity = "0";
//	node.style.overflow = "hidden";
	saveSettings();
}

function showHelp() {
	pauseTimer = true;
	var node = document.getElementById("helpwarningid");
	if (!cookiesEnabled) node.innerHTML = "Warning: It looks like cookies are disabled. Your stats cannot be saved.";
	node = document.getElementById("helpscreenid");
//	node.style.display = "block";
//	node.style.opacity = "1";
	node.style.height = "100%";
	// show some game explanation in a pop up, wait for user to click ok
	}

function hideHelp(){
	pauseTimer = false;
	var node = document.getElementById("helpscreenid");
	node.style.height = "0px";
//	node.style.overlay = "hidden";
//	node.style.transition = "all 0.4s ease";
//	node.style.display = "none";
//	node.style.opacity = "0";
//	node.style.overflow = "hidden";
	} 

function toggleTheme() {
	darkTheme = !darkTheme;

	if(darkTheme) {
		rootProps.style.setProperty('--fgnd', '#ffffff');
		rootProps.style.setProperty('--bgnd', '#141414');
		}
	else {
		rootProps.style.setProperty('--bgnd', '#ffffff');
		rootProps.style.setProperty('--fgnd', '#141414');
		}
	}

function toggleHints() {
	moreHints = !moreHints;
	// Change colors...
	}


function drawResultCharts() {
	histChartData.length = 0;
	histChartData.push(['Time Interval', '# of Wins', { role: 'annotation' }]);
//	for(var i=0; i<timeHistory.length; i++) {
//		histChartData[i+1] = ['[' + (i*10).toString() + '-' + ((i+1)*10).toString() + ']', timeHistory[i]];
//		}
	histChartData.push(['[0 - 20]', timeHistory[0] + timeHistory[1], String(timeHistory[0] + timeHistory[1])] );
	histChartData.push(['[20 - 40]', timeHistory[2] + timeHistory[3], String(timeHistory[2] + timeHistory[3])]);
	histChartData.push(['[40 - 60]', timeHistory[4] + timeHistory[5], String(timeHistory[4] + timeHistory[5])]);
	histChartData.push(['[60 - 90]', timeHistory[6] + timeHistory[7] + timeHistory[8], (timeHistory[6] + timeHistory[7] + timeHistory[8]).toString()]);

	google.charts.setOnLoadCallback(drawHistChart);
	
//	successRateData[0] = ['Genre', 'Success', 'Failure'];
//	if(numSessions > 0) successRateData[1] = ['Rate', numLongest/numSessions, 1-(numLongest/numSessions)];
//	else successRateData[1] = ['Rate', 0, 0];
	
	}

function drawHistChart() {
	var data = google.visualization.arrayToDataTable(histChartData);

	var charColor = getComputedStyle(rootProps).getPropertyValue('--fgnd');

	var options = {
		title: 'Success Time (s)',
		backgroundColor: { fill:'transparent' },
		textStyle: {color: '#335eb0'},
		width: '440',
		height: '150',
		legend: { position: 'none' },
		vAxis: {
			textStyle: {color: '#335eb0'}
          },
		hAxis: {
			gridlines: {count: '0'},
			ticks: [],
			textStyle: {color: '#335eb0'}
          }
		};

	if (histChart === undefined || histChart === null) {
		histChart = new google.visualization.BarChart(document.getElementById('histchart'));
		} 
	else {
		histChart.clearChart();
		}
//	if(histChart) histChart.clearChart();
//	histChart = new google.visualization.BarChart(document.getElementById('histchart'));
	histChart.draw(data, options);
	}
	

</script>



<body>