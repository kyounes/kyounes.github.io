import time, re, random
#import tkinter as tk
#import tkinter.scrolledtext as tkst
#import tkinter.messagebox as tkmb
#import tkinter.simpledialog as tksd
#import tkinter.filedialog as tkfd
#import json
import sys, getopt, io
import unidecode




letterValues = {
    'a' : 1, 'b' : 3, 'c' : 3, 'd' : 3, 'e' : 1, 'f' : 3, 'g' : 3, 'h' : 3,
    'i' : 1, 'j' : 3, 'k' : 3, 'l' : 3, 'm' : 3, 'n' : 3, 'o' : 1, 'p' : 3,
    'q' : 6, 'r' : 3, 's' : 3, 't' : 3, 'u' : 1, 'v' : 3, 'w' : 3, 'x' : 3,
    'y' : 1, 'z' : 3
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
else:
    dictionaryFile = './words_alpha.txt'

avgValue = 0

lengthWeight = 40 / 100
timeWeight = 30 / 100
valueWeight = 1.0 - timeWeight - lengthWeight

vowels = ['a', 'e', 'i', 'o', 'u', 'y']
vowelProb = 0.35
numLetters = 9
curLetters = [None] * numLetters

minSolutionLength = numLetters

longWordsList = []
orderedWordsList = []

appWindow = None

# Called to check if word is valid

def validWord(word, curLetters):
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
            print('Invalid combination of allowed letters')
            return False
    else:
        print('Word not found in dictionary')
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
    targetWord = random.choice(longWordsList)
#    print(targetWord)
    scrambled = ''.join(random.sample(targetWord,len(targetWord)))
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

# Find all 8 letter words, not just our "solution"

    sortedWord = ''.join(sorted(scrambled))
    res_list = list(filter(lambda x: orderedWordsList[x] == sortedWord, range(len(orderedWordsList))))
    for i in res_list:
        print(longWordsList[i])

def getWordGrade(length, value, time):
    lengthScore = (length / numLetters) * 100
    valueScore = (value / (length * 9)) * 100             # Re-think later
    timeScore = 50                                      # to be worked out later

    return ((lengthScore * lengthWeight) + (valueScore * valueWeight) + (timeScore * timeWeight))


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

while True:
    pickLetters(numLetters, curLetters)
    letters = ''
    for letter in curLetters: letters = letters + ("{0}  ".format(letter.upper()))
    print(letters)

# Guess loop - probably timed

    for i in range(0,3):

        word = input('Type a word: ')
        lword = str.lower(word)
        uword = str.upper(word)

        if(checkWord(lword, curLetters)):
            wordValue = getWordValue(lword, letterValues)
            wordTime = 50
            wordGrade = getWordGrade(len(word), wordValue, wordTime)

            print('Score: {:.1%} '.format(wordGrade/100))
    print('\n\n')


