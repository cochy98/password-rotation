<?php

namespace Cosmoferrigno\PasswordRotation\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'password-rotation:install
                            {--force : Sovrascrive le migration esistenti}';

    protected $description = 'Pubblica le migration del package password-rotation con il timestamp corrente';

    /**
     * Stub da pubblicare, in ordine di esecuzione.
     * La chiave è il nome base; viene anteposto il timestamp al momento della pubblicazione.
     */
    private array $stubs = [
        'add_password_expires_at_to_users_table',
    ];

    public function handle(): int
    {
        $this->info('Pubblicazione migration password-rotation...');
        $this->newLine();

        foreach ($this->stubs as $offset => $name) {
            $this->publishMigration($name, $offset);
        }

        $this->newLine();
        $this->info('Fatto! Esegui ora:');
        $this->comment('  php artisan migrate');

        return self::SUCCESS;
    }

    private function publishMigration(string $name, int $secondOffset): void
    {
        $existing = glob(database_path("migrations/*_{$name}.php"));

        if (!empty($existing) && !$this->option('force')) {
            $this->warn("  Già presente: {$name} — usa --force per sovrascrivere.");
            return;
        }

        // Rimuove le versioni precedenti se --force
        if (!empty($existing) && $this->option('force')) {
            foreach ($existing as $file) {
                unlink($file);
                $this->warn("  Rimossa versione precedente: ".basename($file));
            }
        }

        $timestamp = now()->addSeconds($secondOffset)->format('Y_m_d_His');
        $target    = database_path("migrations/{$timestamp}_{$name}.php");
        $stub      = __DIR__."/../../../stubs/{$name}.php.stub";

        copy($stub, $target);

        $this->line("  <info>Creata:</info> {$timestamp}_{$name}.php");
    }
}
