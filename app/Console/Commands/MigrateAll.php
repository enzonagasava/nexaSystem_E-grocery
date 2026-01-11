<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateAll extends Command
{
    protected $signature = 'migrate:all {--fresh} {--seed}';
    protected $description = 'Roda migrations em todos os bancos (mysql, credentials, content)';

    public function handle(): int
    {
        $fresh = $this->option('fresh') ? ':fresh' : '';
        $seed = $this->option('seed') ? ' --seed' : '';

        $this->info('Migrando banco principal (mysql)...');
        $this->call("migrate{$fresh}", ['--database' => 'mysql']);

        $this->info('Migrando banco credentials...');
        $this->call("migrate{$fresh}", [
            '--database' => 'credentials',
            '--path' => 'database/migrations/credentials',
        ]);

        $this->info('Migrando banco content...');
        $this->call("migrate{$fresh}", [
            '--database' => 'content',
            '--path' => 'database/migrations/content',
        ]);

        if ($this->option('seed')) {
            $this->info('Rodando seeders...');
            $this->call('db:seed', ['--database' => 'credentials']);
            $this->call('db:seed', ['--database' => 'content']);
        }

        $this->info('Todas as migrations foram executadas!');
        return Command::SUCCESS;
    }
}