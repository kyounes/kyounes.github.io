
var wordsection = document.querySelector(".wordsection");
var lettersection = document.querySelector(".lettersection");
var guesssection = document.querySelector(".guesssection");
var hintsection = document.querySelector(".hintsection");
// document.getElementById("game-over").addEventListener("click", ()=>{ window.location.reload()  })
// document.getElementById("won").addEventListener("click", ()=>{ window.location.reload()  })

//used later to declare win and lose
var tryIndex = 0;
const maxTries = 3;
let tryWord = [];
tryWord.push('');
const wordSize = 9;
var guessLetterCount = 0;
let lengthHistory = Array(wordSize + 1);


let guessStack = [];
let letterStack = [];

let letterValues = {
					'a' : 1, 'b' : 3, 'c' : 2, 'd' : 2, 'e' : 1, 'f' : 4, 'g' : 3, 'h' : 2,
					'i' : 1, 'j' : 8, 'k' : 5, 'l' : 1, 'm' : 2, 'n' : 1, 'o' : 1, 'p' : 3,
					'q' : 10, 'r' : 1, 's' : 1, 't' : 1, 'u' : 2, 'v' : 4, 'w' : 4, 'x' : 5,
					'y' : 4, 'z' : 6
					 };

var letterArray = "{{letters}}".replace(/ /g, '').split('');

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

var numHints = 0;
var numHintsPrev = 0;
var timesUp = false;
var gameOver = false;
var gameStopped = false;

window.onbeforeunload = saveState;


if (cookiesEnabled){
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
		}
	else showHelp(true);
	printMsg("numSessions = " + numSessions + ", topScoreEver = " + niceFloat(topScoreEver));
	}
else showHelp(false);



function ProgressCountdown(timeleft, bar, text) {
  return new Promise((resolve, reject) => {
    var countdownTimer = setInterval(() => {
      timeleft--;

      document.getElementById(bar).value = timeleft;
      document.getElementById(text).textContent = timeleft;

      if ((timeleft <= 0) || (gameOver)) {
        clearInterval(countdownTimer);
        resolve(true);
      }
    }, 1000);
  });
}

// substitution test
//document.getElementById('embed').innerHTML = "{{embed}}"


//alphabet array to select letters from
// let alphabetArray = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z'];
//create alphabets to select before fetching the word

// console.log(letterArray)

let i = 0;
letterArray.map((letter)=>{
    let node = document.createElement("span")
    node.setAttribute("class", "alphabet")
    node.setAttribute("id", letter + i)
    let letterNode = document.createTextNode(letter)
    node.appendChild(letterNode)
	
	lletter = letter.toLowerCase();
	let node1 = document.createElement("span")
    node1.setAttribute("class", "valuedigit")
	let valueNode = document.createTextNode(letterValues[lletter])
	node1.appendChild(valueNode)
	node.appendChild(node1)
	

	lettersection.appendChild(node)
	i = i + 1
})

document.querySelectorAll(".alphabet").forEach(each=> {each.addEventListener('click', selectLetter)});

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
			avgScore = ((avgScore * numSessions) + topScoreToday) / (numSessions + 1);
			if(topScoreToday > topScoreEver) topScoreEver = topScoreToday;
			lengthHistory[topLengthToday] = lengthHistory[topLengthToday] + 1;
			numSessions = numSessions + 1;
			if(gameOver) numLongest = numLongest + 1;
			setCookie("numSessions", numSessions, 365);
			setCookie("numLongest", numLongest, 365);
			setCookie("topScoreEver", topScoreEver, 365);
			setCookie("avgScore", avgScore, 365);
			setCookie("lengthHistory", JSON.stringify(lengthHistory), 365);
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

function stopGame() {
	if(!gameStopped) {					//Test if we already got here somehow...
		gameStopped = true;
		// Disable letter event listeners
		document.querySelectorAll(".alphabet").forEach(each=> {each.removeEventListener('click', selectLetter)});
		
		saveCookies();

		if(gameOver) {									// Win
			printMsg("Well done! You win today!");
			numLongest = numLongest + 1;
			}
		else if(timesUp) {								// Timeout without winning
			if(topScoreToday > topScoreEver) {			// High score, but not 100
				printMsg("Great! You have a new high score: " + niceFloat(topScoreEver));
				}
			}
		printMsg("Thanks for playing today!");
		}
	}

//function on selecting letter event
function selectLetter(event){
	let selectedLetter = event.target.innerText[0];
// Check if letter has been selected already, otherwise mark it and move on
	if(!document.getElementById(event.target.id).classList.contains('bg')) {
		guessLetterCount = guessLetterCount + 1;
		letterStack.push(event.target.id);
		tryWord[tryIndex] += selectedLetter;
		document.getElementById(event.target.id).classList.add("bg");
		

		let node = document.createElement("span");
		node.setAttribute("class", "guessletter");
		guessLetterId = selectedLetter + selectedLetter + guessLetterCount;
		node.setAttribute("id", guessLetterId);
		guessStack.push(guessLetterId);
		let guessNode = document.createTextNode(selectedLetter);
		node.appendChild(guessNode);
		if(guessLetterCount > 1) {
			document.getElementById(guessStack[guessLetterCount-2]).removeEventListener('click', removeLetter);
			}
		node.addEventListener('click', removeLetter)
		guesssection.appendChild(node);

		}
//	printMsg(tryWord[tryIndex])
	}

function removeLetter(event) {

	guessStack.pop();
	tryWord[tryIndex] = tryWord[tryIndex].slice(0,tryWord[tryIndex].length-1);
	guessLetterCount = guessLetterCount - 1;
	var node = document.getElementById(event.target.id);
	node.parentNode.removeChild(node);
	if(guessLetterCount > 0) {
		node = document.getElementById(guessStack[guessLetterCount - 1]);
		node.addEventListener('click', removeLetter)
		}
// Now take care of the original letters
	availLetterId = letterStack.pop();
	node = document.getElementById(availLetterId);
	node.setAttribute('class', 'alphabet');
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
		hintsection.appendChild(node);		
		}
	}

function submitWord() {

	if(guessLetterCount > 0) {
		if((tryIndex == 0) || ((tryIndex > 0) && (tryWord.lastIndexOf(tryWord[tryIndex], tryIndex - 1) < 0))) {
			testWord = tryWord[tryIndex][0] + tryWord[tryIndex].slice(1).toLowerCase();
			tryIndex = tryIndex + 1;
			tryWord.push('');	
			clearWord();
			fetch('/testWord/' + tryWord[tryIndex-1], {method: 'GET'})
				.then(function (response) {
					return response.json();
				}).then(function (json) {
					//printMsg(testWord + ' ' + text); // Print the return value
					wordResult = JSON.parse(JSON.stringify(json));
					if(wordResult.wordValid) {
						if(wordResult.wordGrade > topScoreToday) {
							var newHints = 0;
							if((wordResult.word.length != wordSize) && (wordResult.wordGrade > 50.0)) newHints = Math.min(((wordResult.wordGrade - 45.0)/10).toFixed(), 4);
							if (newHints > numHints) {
								numHintsPrev = numHints;
								numHints = newHints;
								displayHints(wordResult.first5);
								}
							topScoreToday = wordResult.wordGrade;
							topLengthToday = wordResult.word.length;
							}
//						if(topScoreToday > topScoreEver) topScoreEver = topScoreToday;
						if((wordResult.word.length) == wordSize) {
							gameOver = true;
							stopGame();
							}
						printMsg("Score for " + wordResult.word + ": " + niceFloat(wordResult.wordGrade))
						}
					else {
						printMsg("The word " + wordResult.word + " does not seem to exist in our dictionary");
						}
//					saveCookies();
				});
//			let response =  fetch('/testWord/' + tryWord[tryIndex], {method: 'GET'}); // resolves with response headers
//			let result =  response.json(); 		// read body as json
//			wordResult = JSON.parse(result);

//			var goodWord = true;   // hard set for now until we get JSON results going
//			var wordScore = guessLetterCount * 10;

			}
		else if((tryIndex > 0) && (tryWord.lastIndexOf(tryWord[tryIndex], tryIndex - 1) >= 0)) {
			printMsg("You have already tried this word today.");
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
	var parent = guesssection;
	while (parent.firstChild) {
		parent.removeChild(parent.firstChild);
		}
	guessStack.length = 0;
	letterStack.length = 0;
    }

function saveState() {

	saveCookies();
	}

setTimeout(function(){ showHelp(cookiesEnabled); }, 1000);


function showHelp(cookiesOk) {
	var helpMsg = "";
	if (!cookiesOk) helpMsg += ("It looks like cookies are disabled. Your stats cannot be saved.");
	// show some game explanation in a pop up, wait for user to click ok
	helpMsg += "\n\nThis game works this way";
	alert(helpMsg);
	}

// ProgressCountdown(60, 'pageBeginCountdown', 'pageBeginCountdownText').then(value => alert(`Page has started: ${value}.`));
ProgressCountdown(60, 'pageBeginCountdown', 'pageBeginCountdownText').then(value => stopGame());


