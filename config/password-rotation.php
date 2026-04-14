<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rotazione password abilitata
    |--------------------------------------------------------------------------
    | Se false, il middleware CheckPasswordRotation non esegue alcun controllo.
    */
    'enabled' => env('PASSWORD_ROTATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Giorni di rotazione predefiniti
    |--------------------------------------------------------------------------
    | Numero di giorni dopo i quali la password scade. Può essere sovrascritto
    | a runtime tramite la configurazione globale o per-utente nel DB.
    | 0 = rotazione disabilitata per tutti.
    */
    'days' => env('PASSWORD_ROTATION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Tabelle
    |--------------------------------------------------------------------------
    | Nomi delle tabelle usate dal package. Modificare solo se la propria
    | applicazione usa nomi non standard.
    */
    'users_table' => 'users',

    /*
    |--------------------------------------------------------------------------
    | Route di cambio password
    |--------------------------------------------------------------------------
    | Nome della route a cui l'utente viene reindirizzato quando la password
    | è scaduta. La route deve essere definita nell'applicazione host.
    */
    'change_route' => env('PASSWORD_ROTATION_CHANGE_ROUTE', 'password.change'),

    /*
    |--------------------------------------------------------------------------
    | Route escluse dal controllo
    |--------------------------------------------------------------------------
    | Lista di nomi di route che il middleware deve ignorare (es. logout,
    | la stessa pagina di cambio password, API di health-check, ecc.).
    */
    'excluded_routes' => [
        // 'password.change',
        // 'logout',
    ],

];
