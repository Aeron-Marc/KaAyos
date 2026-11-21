<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Profanity Word Lists
    |--------------------------------------------------------------------------
    |
    | Words to filter in English and Filipino/Tagalog. Extend as needed.
    | All matching is case-insensitive.
    */
    'words' => [
        'en' => [
            'fuck', 'fucking', 'fucker', 'fuckwit', 'fucktard',
            'shit', 'shitty', 'shithead', 'shitbag',
            'bitch', 'bastard', 'damn', 'damned', 'crap', 'crappy',
            'asshole', 'dick', 'dickhead', 'prick', 'cock', 'pussy',
            'slut', 'whore', 'nigger', 'nigga', 'faggot', 'cunt',
            'twat', 'wanker', 'bollocks', 'bugger',
        ],
        'tl' => [
            'puta', 'putang', 'putang ina', 'putangina', 'puñeta',
            'gago', 'gaga', 'gagwa', 'gagawin',
            'tanga', 'tangina', 'tarantado', 'taranta',
            'ulol', 'buwisit', 'buwisit na',
            'leche', 'leche ka',
            'putik', 'kantot', 'kantota', 'kantotin',
            'burat', 'puki', 'titit', 'bubuhi',
            'lubang', 'pekpek', 'fufufu',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Obfuscation Patterns
    |--------------------------------------------------------------------------
    |
    | Common obfuscations (l33t speak, symbols for letters, repeated letters)
    | are normalized before matching.
    */
    'obfuscation_patterns' => [
        '/[0@]/i'   => 'a',   // 0 or @ → a
        '/[1!|]/i'  => 'i',   // 1 or ! or | → i
        '/[3#]/i'   => 'e',   // 3 or # → e
        '/[4]/i'    => 'a',   // 4 → a
        '/[5$]/i'   => 's',   // 5 or $ → s
        '/[7]/i'    => 't',   // 7 → t
        '/[8]/i'    => 'b',   // 8 → b
        '/[9]/i'    => 'g',   // 9 → g
        '/\+/i'     => 't',   // + → t
        '/\//i'     => 't',   // / → t
        '/@/i'      => 'a',   // @ → a
    ],

    /*
    |--------------------------------------------------------------------------
    | Filtering Action
    |--------------------------------------------------------------------------
    |
    | 'mask'  → Replace profanity with mask_char (e.g., "f***").
    | 'block' → Reject the submission entirely with a validation error.
    */
    'action'   => 'mask',
    'mask_char' => '*',

    /*
    |--------------------------------------------------------------------------
    | Minimum word length to check
    |--------------------------------------------------------------------------
    |
    | Words shorter than this are skipped to avoid false positives
    | (e.g., "ass" inside "class").
    */
    'min_length' => 3,

];
