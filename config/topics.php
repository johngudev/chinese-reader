<?php 

$easy_forms = [
    'a short narrative story',
    'a two-person dialogue (just the spoken lines, no "he said")',
    'a first-person diary entry',
    'a short personal anecdote',
    'a short message to a friend',
    'a miniature folk tale',
    'an overheard conversation',
    'a short opinion paragraph',
    'a tale involving an animal',
];  

$forms = [
    'a short narrative story',
    'a two-person dialogue (just the spoken lines, no "he said")',
    'a first-person diary entry',
    'a short personal anecdote',
    'a short message to a friend',
    'a miniature folk tale',
    'a humorous everyday vignette',
    'an overheard conversation',
    'a short product or restaurant review',
    'an encyclopedia-style explanatory paragraph',
    'a news-style report',
    'a how-to / instructional snippet',
    'a short opinion paragraph',
];

$tones = ['warm','humorous','matter-of-fact',
              'optimistic','ironic','curious','hopeful'];

$topics = [
    // feelings & human bonds
    'love','friendship','jealousy','loneliness','hope','nostalgia','courage','patience','curiosity','forgiveness',
    'ambition','regret','gratitude','trust','homesickness','pride','kindness','boredom','surprise','grief',

    // people & relationships
    'family','grandparents','neighbors','strangers','teachers','old friends','siblings','a first love','rivals','mentors',

    // food & drink
    'fruit','tea','coffee','noodles','dumplings','street food','bread','spicy food','chocolate','wine',

    // animals & nature
    'cats','dogs','birds','the ocean','mountains','rivers','forests','rain','snow','the moon',
    'stars','flowers','autumn leaves','the wind','deserts','gardens','insects','fish','thunderstorms','sunrise',

    // places
    'France','Japan','small towns','big cities','train stations','airports','libraries','markets','the countryside','islands',
    'Paris','an old neighborhood','a mountain village','the seaside','a night market',

    // objects & possessions
    'cars','bicycles','books','letters','photographs','umbrellas','clocks','keys','mirrors','maps',
    'shoes','phones','musical instruments','old furniture','a diary',

    // ideas & abstractions
    'AI','time','money','dreams','memory','language','freedom','luck','change','tradition',
    'the future','secrets','silence','distance','beginnings',

    // arts & culture
    'novels','poetry','music','painting','film','dance','theater','photography','calligraphy','festivals',

    // activities & work
    'cooking','travel','gardening','running','swimming','studying','farming','fishing','writing','teaching',
    'shopping','moving house','a job interview','learning to drive','starting a business',

    // time & seasons
    'spring','summer','winter','holidays','the new year','weekends','midnight','childhood','growing up','old age',

    // everyday life
    'sleep','dreams at night','exercise','health','weather','traffic','the internet','social media','restaurants','school',
];

$easy_topics = [
    // family & people
    'my family','my mom and dad','my older brother or sister','my friend','my teacher',
    'my Chinese teacher','a classmate','my son or daughter','meeting a friend','my friend’s name',

    // food & drink
    'eating breakfast','drinking tea','drinking water','eating rice','eating with family',
    'a favorite food','apples','eating at a restaurant','making food at home','being hungry',

    // daily routine
    'getting up in the morning','going to school','coming home','a day at home','reading a book',
    'watching TV','watching a movie','doing homework','learning Chinese','writing characters',

    // time & days
    'what day it is today','today and tomorrow','what time it is','my week','this month',
    'how old I am','a birthday','the morning','the afternoon','a busy day',

    // school & study
    'my school','my Chinese class','learning to read','learning new words','a Chinese book',
    'liking to study','my classmates','a small test','speaking Chinese','a good student',

    // places & going out
    'going to the store','buying things','buying fruit','spending money','going to the hospital',
    'going to a restaurant','the train station','taking a taxi','going to China','going to Beijing',

    // weather & nature
    'today’s weather','a hot day','a cold day','a rainy day','liking the rain',
    'a nice day','it is very hot','it is very cold','a cat','a dog',

    // simple feelings & likes
    'being happy','what I like','what I want to do','liking my friend','a good friend',
    'being too busy','liking cats','liking to eat','a happy day','liking to read',

    // simple things & home
    'my home','my phone','my book','my clothes','my car',
    'a big home','a small store','my name and age','my favorite thing','asking a question',
];

return [
    'forms' => $forms,
    'tones' => $tones,
    'topics' => $topics,
    'easy_topics' => $easy_topics,
    'easy_forms' => $easy_forms,
];