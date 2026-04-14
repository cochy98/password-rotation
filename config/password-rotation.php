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
    | Mappatura colonne della tabella utenti
    |--------------------------------------------------------------------------
    | 'id'      — chiave primaria.
    | 'name'    — colonna o accessor Eloquent usato per visualizzare il nome
    |             utente nel frontend. Può essere un accessor (es. 'full_name')
    |             o una singola colonna (es. 'first_name'). Il controller
    |             usa getAttribute() quindi funziona con entrambi.
    | 'email'   — colonna email.
    | 'sort_by' — array di colonne DB usate per l'ORDER BY della query.
    |             Usare colonne reali (non accessor), es. ['first_name', 'last_name'].
    |             Se omesso, il fallback è la colonna 'name'.
    */
    'user_columns' => [
        'id'      => 'id',
        'name'    => 'full_name',
        'email'   => 'email',
        'sort_by' => ['first_name', 'last_name'],
    ],

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

    /*
    |--------------------------------------------------------------------------
    | Gate di gestione
    |--------------------------------------------------------------------------
    | Nome del Gate che protegge le rotte di amministrazione (lista utenti,
    | force reset). Viene definito nel ServiceProvider con un default basato
    | su is_admin; l'applicazione host può ridefinirlo in AuthServiceProvider.
    */
    'gate' => env('PASSWORD_ROTATION_GATE', 'manage-password-rotation'),

    /*
    |--------------------------------------------------------------------------
    | Rotte di amministrazione
    |--------------------------------------------------------------------------
    | Il package registra automaticamente le rotte per la lista utenti e il
    | force-reset. Imposta 'enabled' a false per gestirle nell'host app.
    |
    | 'inertia_component' — nome del componente Inertia da renderizzare.
    */
    'admin_routes' => [
        'enabled'          => env('PASSWORD_ROTATION_ADMIN_ROUTES', true),
        'inertia_component' => env('PASSWORD_ROTATION_INERTIA_COMPONENT', 'PasswordRotation/Users'),
    ],

];
