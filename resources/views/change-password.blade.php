<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambio password obbligatorio</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.12); padding: 2rem; width: 100%; max-width: 400px; }
        h1 { font-size: 1.25rem; font-weight: 600; margin: 0 0 .5rem; }
        p.desc { font-size: .875rem; color: #6b7280; margin: 0 0 1.5rem; }
        label { display: block; font-size: .875rem; font-weight: 500; margin-bottom: .25rem; }
        input { width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: .5rem .75rem; font-size: .875rem; margin-bottom: 1rem; }
        input:focus { outline: 2px solid #6366f1; outline-offset: 2px; border-color: transparent; }
        .error { color: #dc2626; font-size: .8rem; margin-top: -.75rem; margin-bottom: .75rem; }
        button { width: 100%; background: #111827; color: #fff; border: none; border-radius: 6px; padding: .625rem 1rem; font-size: .875rem; font-weight: 500; cursor: pointer; }
        button:hover { background: #1f2937; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Cambio password obbligatorio</h1>
        <p class="desc">
            @if($daysUntilExpiry === 0)
                La tua password è scaduta. Devi impostarne una nuova per continuare.
            @else
                La tua password scadrà tra {{ $daysUntilExpiry }} giorni. Aggiornala ora.
            @endif
        </p>

        @if(session('status'))
            <p style="color:#16a34a;font-size:.875rem;margin-bottom:1rem;">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf

            <label for="current_password">Password attuale</label>
            <input id="current_password" type="password" name="current_password" autocomplete="current-password" autofocus>
            @error('current_password') <p class="error">{{ $message }}</p> @enderror

            <label for="password">Nuova password</label>
            <input id="password" type="password" name="password" autocomplete="new-password">
            @error('password') <p class="error">{{ $message }}</p> @enderror

            <label for="password_confirmation">Conferma nuova password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
            @error('password_confirmation') <p class="error">{{ $message }}</p> @enderror

            <button type="submit">Aggiorna password</button>
        </form>
    </div>
</body>
</html>
