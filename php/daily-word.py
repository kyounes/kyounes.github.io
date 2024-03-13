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
    dictionaryFile = '/var/www/html/languages/fr/liste.de.mots.francais.frgut.txt'
    letterValues.extend(FrenchLetterValues)
    wordFile = '/var/www/html/languages/fr/mots-du-jour.txt'
    possibleWordsFile = '/var/www/html/languages/fr/allPossibleWords.txt'
    longWordsFile = '/var/www/html/languages/fr/longWords.txt'
    orderedLongWordsFile = '/var/www/html/languages/fr/orderedLongWords.txt'
    possibleWordsTodayFile = '/var/www/html/languages/fr/possibleWordsToday.txt'
else:
    dictionaryFile = '/var/www/html/languages/en/OWL3_Dictionary.txt'
    wordFile = '/var/www/html/languages/en/words-of-day.txt'
    possibleWordsFile = '/var/www/html/languages/en/allPossibleWords.txt'
    longWordsFile = '/var/www/html/languages/en/longWords.txt'
    orderedLongWordsFile = '/var/www/html/languages/en/orderedLongWords.txt'
    possibleWordsTodayFile = '/var/www/html/languages/en/possibleWordsToday.txt'

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
possibleWordsTodayList = []
orderedLongWordsList = []

appWindow = None

dateToday = datetime.date.today()
officialRelease = False
wordHistory = []



#initializeLetters(letterValues)

def getWordValue(word, letterValues):
    value = 0;
    for letter in word:
        value = value + letterValues[letter]
    return value

def pickLetters():
    global targetValue, targetWord, targetIndex, yesterdayWord, numLetters, curLetters

    targetIndex = random.choice(range(len(longWordsList)))
    targetWord = longWordsList[targetIndex]
    scrambled = ''.join(random.sample(targetWord,len(targetWord)))
    targetValue = getWordValue(targetWord.lower(), letterValues)
   
 
    if(not os.path.isfile(wordFile)):
        wordF = open(wordFile, "w", encoding='utf-8')
        wordF.write(str(dateToday) + ' ' + targetWord + ' ' + str(targetIndex) + ' ' + scrambled + ' ' + str(targetValue))
        wordF.close()
    else:
        with open(wordFile, "r", encoding='utf-8') as wordF:            
            wordHistory = wordF.readlines()
        wordF.close()
        txt = wordHistory[len(wordHistory)-1].split();
        if(dateToday > datetime.datetime.strptime(txt[0], '%Y-%m-%d').date()):
            yesterdayWord = txt[1]
            wordF = open(wordFile, 'a', encoding='utf-8')
            wordF.write('\n'+ str(dateToday) + ' ' + targetWord + ' ' + str(targetIndex) + ' ' + scrambled + ' ' + str(targetValue))
            wordF.close()
        else:
            targetWord = txt[1]
            targetIndex = int(txt[2])
            scrambled = txt[3]
            targetValue = int(txt[4])
            txt = wordHistory[len(wordHistory)-2].split();
            yesterdayWord = txt[1]

    i = 0
    for letter in scrambled:
        curLetters[i] = letter
        if UTFLanguage: curLetters[i] = unidecode.unidecode(letter)
        i = i + 1

# Find all N letter words with these letters, not just our "solution"

    with io.open(orderedLongWordsFile, 'r',encoding='utf8') as mydict:
        orderedLongWordsList = mydict.readlines()

    sortedWord = ''.join(sorted(scrambled))
    res_list = list(filter(lambda x: orderedLongWordsList[x] == sortedWord, range(len(orderedLongWordsList))))
    for i in res_list:
        print(longWordsList[i])

def included(word, target):
    global numLetters
    
    targetIndex = 0
    for letter in word:
        while letter != target[targetIndex]:
            targetIndex = targetIndex + 1
            if targetIndex > (numLetters - 1):
                return False
    return True


with io.open(longWordsFile, 'r',encoding='utf8') as mydict:
    longWordsList = mydict.readlines()
mydict.close()

wordIndex = 0
while wordIndex < len(longWordsList):
    longWordsList[wordIndex] = longWordsList[wordIndex].strip('\n\r')
    wordIndex = wordIndex + 1

pickLetters()

sortedWord = ''.join(sorted(targetWord))

allPosFile = open(possibleWordsFile, "r", encoding='utf-8')
todayPosFile = open(possibleWordsTodayFile, "w", encoding='utf-8')

for line in allPosFile:
    dictWord = line.strip('\n\r')
    sortedDictWord = ''.join(sorted(dictWord))
    if included(sortedDictWord, sortedWord):
        todayPosFile.write(dictWord + "\n")

allPosFile.close()
todayPosFile.close()

    
