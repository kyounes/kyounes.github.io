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
let lengthHistory = Array(wordSize + 1);
var first5 = <?php echo '"'.substr($word2Guess,0,5).'"'; ?>;
var justForFun = <?php if($justForFun) echo "true"; else echo "false"; ?>;


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


// Cookies
var cookiesEnabled = navigator.cookieEnabled;
var cookiesUpdated = false;
var numSessions = 0;
var numLongest = 0;
var topScoreEver = 0.0;
var topScoreToday = 0.0;
var topLengthToday = 0;
var avgScore = 0.0;
var lastSessionDate = "";
let today = new Date();
var todayDate = today.getDate().toString() + '/' + (today.getMonth() + 1).toString() + '/' + today.getFullYear().toString();
var streak = 0;
var bestStreak = 0;

var numHints = 0;
var numHintsPrev = 0;
var timeHints = 0;
var gameTime = 90;
var timesUp = false;
var gameOver = false;
var gameStopped = false;
var playedToday = false;

var rootProps = document.querySelector(':root');
var moreHints = false;
var darkTheme = true;

var pauseTimer = false;

window.onbeforeunload = saveState;


if ((!justForFun) && (cookiesEnabled)){
	var temp = getCookie("numSessions");
	if (temp != null) {
		numSessions = parseInt(temp);
		temp = getCookie("numLongest");
		if (temp != null) numLongest = parseInt(temp);
		temp = getCookie("topScoreEver");
		if (temp != null) topScoreEver = parseFloat(temp);	
		temp = getCookie("avgScore");
		if (temp != null) avgScore = parseFloat(temp);	
		temp = getCookie("lengthHistory");
		if (temp != null) lengthHistory = JSON.parse(temp);
		temp = getCookie("lastSessionDate");
		if (temp != null) lastSessionDate = temp;
		temp = getCookie("streak");
		if (temp != null) streak = parseInt(temp);
		temp = getCookie("bestStreak");
		if (temp != null) bestStreak = parseInt(temp);
//		if(lastSessionDate === todayDate) playedToday = true;
		}
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

if((justForFun) || (!playedToday)) {
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
	ProgressCountdown(gameTime, 'pageBeginCountdown', 'pageBeginCountdownText').then(value => stopGame(true, true));
	}
else {
	stopGame(false, true);;
	}

function ProgressCountdown(timeleft, bar, text) {
  return new Promise((resolve, reject) => {
    var countdownTimer = setInterval(() => {
      if(!pauseTimer) timeleft--;

      document.getElementById(bar).value = timeleft;
      document.getElementById(text).textContent = timeleft;
	  
	  if(moreHints && ((timeleft == (gameTime - 30)) || (timeleft == (gameTime - 60)))) {
		newHints = parseInt((90 - timeleft) / 15);
		if (newHints > numHints) {
				numHintsPrev = numHints;
				numHints = newHints;
				displayHints(first5);
				}
		}


      if ((timeleft <= 0) || (gameOver)) {
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
	if ((!justForFun) && (cookiesEnabled)) {
		if(!cookiesUpdated) {
			cookiesUpdated = true;
			avgScore = ((avgScore * numSessions) + topScoreToday) / (numSessions + 1);
			if(topScoreToday > topScoreEver) topScoreEver = topScoreToday;
			if(topScoreToday == wordSize) streak = streak + 1; else streak = 0;
			if(streak > bestStreak) bestStreak = streak;
			lengthHistory[topLengthToday] = lengthHistory[topLengthToday] + 1;
			numSessions = numSessions + 1;
			if(gameOver) numLongest = numLongest + 1;
			setCookie("numSessions", numSessions, 365);
			setCookie("numLongest", numLongest, 365);
			setCookie("topScoreEver", topScoreEver, 365);
			setCookie("avgScore", avgScore, 365);
			setCookie("lengthHistory", JSON.stringify(lengthHistory), 365);
			setCookie("lastSessionDate", todayDate, 365);
			setCookie("streak", todayDate, 365);
			setCookie("bestStreak", todayDate, 365);
			}
		}
	}

function showResults() {
//	printMsg("Showing results");
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
	if(!gameStopped) {					//Test if we already got here somehow...
		gameStopped = true;
		// Disable letter event listeners
		document.querySelectorAll(".alphabet").forEach(each=> {each.removeEventListener('click', selectLetter)});
		document.getElementById("submitButton").disabled = true;
		document.getElementById("clearButton").disabled = true;
		
		if(doCookies) saveCookies();

		if(gameOver) {									// Win
			// printMsg("Well done! You win today!");
			numLongest = numLongest + 1;
			}
		else if(timesUp) {								// Timeout without winning
			if(topScoreToday > topScoreEver) {			// High score, but not 100
				// printMsg("Great! You have a new high score: " + niceFloat(topScoreEver));
				}
			}
		if(doResult) showResults();
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
						topLengthToday = testWord.length;
						}
//						if(topScoreToday > topScoreEver) topScoreEver = topScoreToday;
					if((testWord.length) == wordSize) {
						gameOver = true;
						stopGame(true, true);
						}
					//printMsg("Score for " + wordResult.word + ": " + niceFloat(wordResult.wordGrade))
					displayPlayed("<tr><td class='scoreword'>" + testWord + "</td><td class='scorescore'>" + niceFloat(wordGrade) + '</td></tr>');
					}
				else {
					displayPlayed("<tr><td class='scoreword'>" + testWord + "</td><td class='scorescore'>" + "Bad" + '</td></tr>');
					}
			if(!gameOver) clearWord();
//					saveCookies();
			}
		else if((tryIndex > 0) && (tryWord.lastIndexOf(tryWord[tryIndex], tryIndex - 1) >= 0)) {
			displayPlayed("<tr><td class='scoreword'>" + tryWord[tryIndex] + "</td><td class='scorescore'>" + "REP" + '</td></tr>');
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
	saveCookies();
	}

function displayPlayed(message) {

	if(document.getElementById("playedsectionid").style.display != "flex") document.getElementById("playedsectionid").style.display = "flex";
	document.querySelector("#playedwords").innerHTML += message;
	}
	
function showResults() {

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

