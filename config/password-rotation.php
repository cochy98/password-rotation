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
    | è scaduta. Il package registra questa route automaticamente a meno che
    | 'routes.enabled' non sia false.
    */
    'change_route' => env('PASSWORD_ROTATION_CHANGE_ROUTE', 'password.change'),

    /*
    |--------------------------------------------------------------------------
    | Route interne del package
    |--------------------------------------------------------------------------
    | Il package registra automaticamente GET/POST /password/change.
    | Imposta 'enabled' a false se vuoi definire le route nell'applicazione
    | host (es. per personalizzare il controller o l'URL).
    |
    | 'middleware' — applicato al gruppo di route del package.
    | 'prefix'     — prefisso URL aggiuntivo (default: nessuno).
    */
    'routes' => [
        'enabled'    => env('PASSWORD_ROTATION_ROUTES', true),
        'middleware' => ['web', 'auth'],
        'prefix'     => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirect dopo il cambio password
    |--------------------------------------------------------------------------
    | Nome della route a cui l'utente viene reindirizzato dopo aver aggiornato
    | la password con successo.
    */
    'redirect_after_change' => env('PASSWORD_ROTATION_REDIRECT', '/'),

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
