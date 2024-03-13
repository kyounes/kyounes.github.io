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
    dictionaryFile = './languages/fr/liste.de.mots.francais.frgut.txt'
    letterValues.extend(FrenchLetterValues)
    wordFile = './languages/fr/mots-du-jour.txt'
    possibleWordsFile = './languages/en/allPossibleWords.txt'
    longWordsFile = './languages/en/longWords.txt'
    orderedLongWordsFile = './languages/en/orderedLongWords.txt'
else:
    dictionaryFile = './languages/en/OWL3_Dictionary.txt'
    wordFile = './languages/en/words-of-day.txt'
    possibleWordsFile = './languages/en/allPossibleWords.txt'
    longWordsFile = './languages/en/longWords.txt'
    orderedLongWordsFile = './languages/en/orderedLongWords.txt'

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
possibleWordsList = []
orderedLongWordsList = []

appWindow = None

dateToday = datetime.date.today()
officialRelease = False
wordHistory = []



#initializeLetters(letterValues)

with io.open(dictionaryFile, 'r',encoding='utf8') as mydict:
    wordsList = mydict.readlines()


wordIndex = 0
dictLength = len(wordsList)

# Strip the newline characters and lowercase each word.
# Could also remove all words longer than numLetter (game length) here if optimization is necessary (doubtful)

while wordIndex < dictLength:
    curWord = wordsList[wordIndex].strip('\n\r').upper()
    wordLength = len(curWord)
    if wordLength <= numLetters:
        possibleWordsList.append(curWord)
        if wordLength == numLetters:
            longWordsList.append(curWord)
            sortedWord = ''.join(sorted(curWord))
            orderedLongWordsList.append(sortedWord)
    wordIndex = wordIndex + 1

myFile = open(possibleWordsFile, "w", encoding='utf-8')
myFile.writelines("%s\n" % l for l in possibleWordsList)
myFile.close();

myFile = open(orderedLongWordsFile, "w", encoding='utf-8')
myFile.writelines("%s\n" % l for l in orderedLongWordsList)
myFile.close();

myFile = open(longWordsFile, "w", encoding='utf-8')
myFile.writelines("%s\n" % l for l in longWordsList)
myFile.close();
