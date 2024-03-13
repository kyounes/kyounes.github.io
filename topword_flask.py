import time, re, random
#import tkinter as tk
#import tkinter.scrolledtext as tkst
#import tkinter.messagebox as tkmb
#import tkinter.simpledialog as tksd
#import tkinter.filedialog as tkfd
#import json
import sys, getopt, io, os
import unidecode
import datetime
from flask import Flask, render_template, jsonify, request

app = Flask(__name__)


letterValues = {
    'a' : 1, 'b' : 3, 'c' : 2, 'd' : 2, 'e' : 1, 'f' : 4, 'g' : 3, 'h' : 2,
    'i' : 1, 'j' : 8, 'k' : 5, 'l' : 1, 'm' : 2, 'n' : 1, 'o' : 1, 'p' : 3,
    'q' : 10, 'r' : 1, 's' : 1, 't' : 1, 'u' : 2, 'v' : 4, 'w' : 4, 'x' : 5,
    'y' : 4, 'z' : 6
    }
FrenchLetterValues = {
    'à' : 1, 'â' : 1, 'é' : 1, 'è' : 1, 'ê' : 1,
    'î' : 1, 'ï' : 1,
    'ô' : 1, 'û' : 1,
    'ç' : 3
    }

FrenchLanguage = False
UTFLanguage = FrenchLanguage
if FrenchLanguage:
    dictionaryFile = './liste.de.mots.francais.frgut.txt'
    letterValues.extend(FrenchLetterValues)
    wordFile = './mots-du-jour.txt'
else:
    dictionaryFile = './words_alpha.txt'
    wordFile = './words-of-day.txt'

avgValue = 0

targetWord = ''
yesterdayWord = ''
targetIndex = 0
targetValue = 0

lengthWeight = 40 / 100
valueWeight = 1.0 - lengthWeight

vowels = ['a', 'e', 'i', 'o', 'u', 'y']
vowelProb = 0.35
numLetters = 9
curLetters = [None] * numLetters

minSolutionLength = numLetters

longWordsList = []
orderedWordsList = []

appWindow = None

dateToday = datetime.date.today()
officialRelease = False
wordHistory = []

# Called to check if word is valid

def validWord(word, curLetters):
    global UTFLanguage

    tempLetters = curLetters.copy()
    if UTFLanguage: word = unidecode.unidecode(word)
    for letter in word:
        if letter in tempLetters:
            tempLetters.remove(letter)
        else:
            return False
    return True


# Called to check if word exists

def checkWord(word, curLetters):
    if word in wordsList:
        if validWord(word, curLetters):
            return True
        else:
 #           print('Invalid combination of allowed letters')
            return False
    else:
 #       print('Word not found in dictionary')
        return False

##def initializeLetters(letterValues):
##    sumValues = 0
##    for i in range(ord('a'), ord('z')):
##        letterValues[chr(i)] = random.randint(1,9)
##        sumValues = sumValues + letterValues[chr(i)]
##    avgValue = sumValues / len(range(ord('a'), ord('z')))
##    print(letterValues)

def getWordValue(word, letterValues):
    value = 0;
    for letter in word:
        value = value + letterValues[letter]
    return value

def pickLetters(numLetters, curLetters):
    global targetValue, targetWord, targetIndex, yesterdayWord

    targetIndex = random.choice(range(len(longWordsList)))
    targetWord = longWordsList[targetIndex]
    scrambled = ''.join(random.sample(targetWord,len(targetWord)))
   
    if(officialRelease):
        if(not os.path.isfile(wordFile)):
            wordF = open(wordFile, "w", encoding='utf-8')
            wordF.write(str(dateToday) + ' ' + targetWord + ' ' + str(targetIndex) + ' ' + scrambled)
            wordF.close()
        else:
            with open(wordFile, "r", encoding='utf-8') as wordF:            
                wordHistory = wordF.readlines()
            wordF.close()
            txt = wordHistory[len(wordHistory)-1].split();
            if(dateToday > datetime.datetime.strptime(txt[0], '%Y-%m-%d').date()):
                yesterdayWord = txt[1]
                wordF = open(wordFile, 'a', encoding='utf-8')
                wordF.write('\n'+ str(dateToday) + ' ' + targetWord + ' ' + str(targetIndex) + ' ' + scrambled)
                wordF.close()
            else:
                targetWord = txt[1]
                targetIndex = int(txt[2])
                scrambled = txt[3]
                txt = wordHistory[len(wordHistory)-2].split();
                yesterdayWord = txt[1]
                    
                
    targetValue = getWordValue(targetWord, letterValues)

#    print(targetWord)

#    print(scrambled)
    i = 0
    for letter in scrambled:
        curLetters[i] = letter
        if UTFLanguage: curLetters[i] = unidecode.unidecode(letter)
        i = i + 1
##    i = scrambledLength
##    while i < numLetters:
##        if random.random() < vowelProb:
##            curLetters[i] = random.choice(vowels)
##        else:
##            curLetters[i] = chr(random.randint(ord('a'), ord('z')))
##        i = i + 1

# Find all N letter words with these letters, not just our "solution"

    sortedWord = ''.join(sorted(scrambled))
    res_list = list(filter(lambda x: orderedWordsList[x] == sortedWord, range(len(orderedWordsList))))
    for i in res_list:
        print(longWordsList[i])

def getWordGrade(word):
    global targetValue, numLetters, letterValues

    wordValue = getWordValue(word, letterValues)
    wordValueGrade = 50 * (wordValue / targetValue)
    wordLengthGrade = 50 * (len(word) / numLetters)

    return wordValueGrade + wordLengthGrade, wordValueGrade, wordLengthGrade, wordValue


#initializeLetters(letterValues)

with io.open(dictionaryFile, 'r',encoding='utf8') as mydict:
    wordsList = mydict.readlines()


wordIndex = 0
dictLength = len(wordsList)

# Strip the newline characters and lowercase each word.
# Could also remove all words longer than numLetter (game length) here if optimization is necessary (doubtful)

while wordIndex < dictLength:
    wordsList[wordIndex] = wordsList[wordIndex].strip('\n\r').lower()
    wordLength = len(wordsList[wordIndex])
    if wordLength == numLetters:
        longWordsList.append(wordsList[wordIndex])
        sortedWord = ''.join(sorted(wordsList[wordIndex]))
        orderedWordsList.append(sortedWord)
    wordIndex = wordIndex + 1

#print(len(wordsList), len(longWordsList))

#x = slice(50)
#print(wordsList[x])

# Daily loop

@app.route("/")
def topword(): 
    global targetValue, targetWord, numLetters, curLetters, targetIndex

    pickLetters(numLetters, curLetters)

#    print(targetWord, targetValue)
    letters = ''
    for letter in curLetters: letters = letters + ("{0}  ".format(letter.upper()))

    return render_template('index.html', letters=letters, targetIndex=targetIndex, first5=str.upper(targetWord[:5]))

@app.route('/testWord/<word>/<index>', methods=['GET', 'POST'])
def testWord(word, index):
    global targetWord, longWordsList

    # POST request
    if request.method == 'POST':
        print('Incoming..')
        word = request.get_text()
        print(word)
        return 'OK', 200

    # GET request
    else:
#        print('Oh well..')
#        message = {'greeting':'Hello from Flask!'}
#        return jsonify(message)  # serialize and use JSON headers
    
        lword = str.lower(word)
        targetWord = longWordsList[int(index)]


        if(checkWord(lword, curLetters)):
            wordValid = True
            wordGrade, wordValueGrade, wordLengthGrade, wordValue = getWordGrade(lword)
            returnData = {
                "word": word,
                "wordValid": wordValid,
                "wordGrade" : wordGrade,
                "wordValueGrade": wordValueGrade,
                "wordLengthGrade": wordLengthGrade,
                "wordValue" : wordValue,
                "first5": str.upper(targetWord[:5])
                }
        else:
            wordValid = False
            returnData = {
                "word": word,
                "wordValid": wordValid,
                "wordGrade" : 0.0,
                "wordValueGrade": 0.0,
                "wordLengthGrade": 0.0,
                "wordValue" : 0.0
                }

        return jsonify(returnData)






