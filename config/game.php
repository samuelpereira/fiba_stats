<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Game defaults
    |--------------------------------------------------------------------------
    |
    | Applied when a game is created. Colors are red, orange, yellow, green,
    | cyan, purple, pink, white, maroon, brown, olive, dark-green, blue,
    | dark-purple, black, or gray. Period lengths are minutes from 1 to 99.
    |
    */

    'quarter_minutes' => env('GAME_QUARTER_MINUTES', 10),

    'overtime_minutes' => env('GAME_OVERTIME_MINUTES', 5),

    'home_color' => env('GAME_HOME_COLOR', 'purple'),

    'away_color' => env('GAME_AWAY_COLOR', 'blue'),

    'mark_shot_location' => env('GAME_MARK_SHOT_LOCATION', false),

];
