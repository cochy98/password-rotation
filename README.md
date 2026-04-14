# cosmoferrigno/password-rotation

Laravel package per la rotazione obbligatoria delle password. Supporta:

- Periodo di scadenza configurabile via ENV
- Invalidazione immediata della password di un utente specifico
- Compatibile con applicazioni Blade e Inertia.js

---

## Requisiti

- PHP >= 8.1
- Laravel >= 10.x
- Tabella `users` con colonna `id`

---

## Installazione

### 1. Aggiungi il repository in `composer.json`

**Da path locale (monorepo):**
```json
"repositories": [
    {
        "type": "path",
        "url": "packages/cosmoferrigno/password-rotation",
        "options": { "symlink": true }
    }
],
"require": {
    "cosmoferrigno/password-rotation": "@dev"
}
```

**Da repository VCS (uso standalone):**
```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/cochy98/password-rotation"
    }
],
"require": {
    "cosmoferrigno/password-rotation": "^1.0"
}
```

### 2. Installa il package

```bash
composer update cosmoferrigno/password-rotation
```

Il service provider viene scoperto automaticamente tramite Laravel Package Discovery.

### 3. Pubblica le migration

Il package usa stub e un comando Artisan dedicato che genera i file migration con il timestamp corrente nella directory `database/migrations/` dell'applicazione:

```bash
php artisan password-rotation:install
```

```bash
php artisan password-rotation:install --force   # Sovrascrive migration esistenti
```

La migration aggiunge la colonna `password_expires_at` (nullable timestamp) alla tabella `users`.

### 4. Esegui le migration

```bash
php artisan migrate
```

---

## Pubblicazione delle risorse

| Tag | Comando | Cosa pubblica |
|---|---|---|
| `password-rotation-config` | `php artisan vendor:publish --tag=password-rotation-config` | `config/password-rotation.php` nell'applicazione |
| `password-rotation-stubs` | `php artisan vendor:publish --tag=password-rotation-stubs` | Gli stub delle migration in `stubs/vendor/password-rotation/` |

Per pubblicare tutto in una volta:

```bash
php artisan vendor:publish --provider="Cosmoferrigno\PasswordRotation\PasswordRotationServiceProvider"
```

---

## Configurazione

Variabili ENV disponibili:

| Variabile | Default | Descrizione |
|---|---|---|
| `PASSWORD_ROTATION_ENABLED` | `true` | Abilita/disabilita il redirect forzato |
| `PASSWORD_ROTATION_DAYS` | `90` | Giorni di validità della password |
| `PASSWORD_ROTATION_CHANGE_ROUTE` | `password.change` | Nome della route a cui reindirizzare |

Pubblica il file di configurazione per personalizzare anche `users_table`, `change_route` ed `excluded_routes`:

```bash
php artisan vendor:publish --tag=password-rotation-config
```

---

## Integrazione nell'applicazione

### 1. Aggiungi il trait al modello User

```php
use Cosmoferrigno\PasswordRotation\Traits\HasPasswordRotation;

class User extends Authenticatable
{
    use HasPasswordRotation;
    // ...
}
```

Il trait aggiunge automaticamente `password_expires_at` ai cast (`datetime`) e al `$fillable`.

### 2. Applica il middleware alle route protette

Il package registra automaticamente l'alias `password.rotation`. La route di cambio password deve stare in un gruppo **senza** `password.rotation`, altrimenti si genera un redirect infinito:

```php
// routes/web.php

// Route di cambio password — solo auth, ESCLUSA dalla rotazione
Route::middleware('auth')->group(function () {
    Route::get('/password/change', [PasswordChangeController::class, 'show'])
        ->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'update'])
        ->name('password.change.update');
});

// Tutte le altre route protette
Route::middleware(['auth', 'password.rotation'])->group(function () {
    // ...
});
```

### 3. Crea il controller di cambio password

```php
use Cosmoferrigno\PasswordRotation\Facades\PasswordRotation;

class PasswordChangeController extends Controller
{
    // Blade
    public function show()
    {
        return view('auth.change-password', [
            'expiresAt' => PasswordRotation::resolveExpiresAt(auth()->user()),
        ]);
    }

    // Inertia
    public function show()
    {
        return Inertia::render('Auth/ChangePassword', [
            'expiresAt' => PasswordRotation::resolveExpiresAt(auth()->user()),
        ]);
    }

    public function update(UpdatePasswordRequest $request)
    {
        $user = auth()->user();
        $user->update(['password' => $request->validated('password')]);

        // Calcola e salva la nuova password_expires_at
        PasswordRotation::markPasswordChanged($user);

        return redirect()->route('dashboard')->with('success', 'Password aggiornata.');
    }
}
```

### 4. (Opzionale) Alias del middleware in bootstrap/app.php

Se usi Laravel 11+:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'password.rotation' => \Cosmoferrigno\PasswordRotation\Middleware\CheckPasswordRotation::class,
    ]);
})
```

> Il service provider lo registra già tramite il router — questo step è opzionale ma consigliato per esplicitezza.

---

## Comportamento del middleware

Il middleware controlla `password_expires_at` ad ogni richiesta autenticata. Se la data è nel passato:

- **Blade**: redirect 302 alla route configurata (`password.change`)
- **Inertia XHR**: risposta 409 con header `X-Inertia-Location` che forza una navigazione full-page

---

## Come funziona `password_expires_at`

La data di scadenza viene memorizzata direttamente nella colonna `password_expires_at`:

| Situazione | Valore |
|---|---|
| Mai impostata (null) | Il service calcola `created_at + rotation_days` come fallback |
| Dopo `markPasswordChanged()` | `now() + rotation_days` |
| Invalidazione admin | `now()` — il service la vede come scaduta |

---

## API del Service / Facade

```php
use Cosmoferrigno\PasswordRotation\Facades\PasswordRotation;

// Verifica se l'utente deve cambiare password
PasswordRotation::needsRotation($user): bool

// Giorni rimanenti (0 = scaduta, PHP_INT_MAX = rotazione disabilitata)
PasswordRotation::daysUntilExpiry($user): int

// Data di scadenza effettiva (usa password_expires_at o fallback su created_at)
PasswordRotation::resolveExpiresAt($user): Carbon

// Giorni di rotazione dalla configurazione
PasswordRotation::getRotationDays(): int

// Calcola e salva la nuova password_expires_at
PasswordRotation::markPasswordChanged($user): void
```

---

## Gestione amministratore

Per invalidare immediatamente la password di un utente, imposta `password_expires_at = now()`:

```php
// Nel controller admin
$user->forceFill(['password_expires_at' => now()])->save();
```

Al prossimo accesso l'utente sarà reindirizzato alla pagina di cambio password. Dopo il cambio, `markPasswordChanged()` ricalcola e salva la nuova data di scadenza.

---

## Route escluse dal controllo

Personalizzabili in `config/password-rotation.php`:

```php
'excluded_routes' => [
    'password.change',
    'password.change.update',
    'logout',
],
```
