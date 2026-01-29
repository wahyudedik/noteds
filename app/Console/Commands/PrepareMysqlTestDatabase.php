<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use PDO;

class PrepareMysqlTestDatabase extends Command
{
    protected $signature = 'db:test:prepare';
    protected $description = 'Create MySQL testing database if missing and run fresh migrations with seed';

    public function handle(): int
    {
        $this->info('Loading .env.testing');
        $envPath = base_path('.env.testing');
        if (file_exists($envPath)) {
            $dotenv = \Dotenv\Dotenv::createImmutable(base_path(), '.env.testing');
            $dotenv->load();
        }
        $host = env('DB_HOST', '127.0.0.1');
        $port = (int) env('DB_PORT', 3306);
        $db = env('DB_DATABASE', 'noteds_test');
        $user = env('DB_USERNAME', 'root');
        $pass = env('DB_PASSWORD', '');
        try {
            $dsn = "mysql:host={$host};port={$port}";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("Database {$db} ensured.");
        } catch (\Throwable $e) {
            $this->error('Failed to create database: ' . $e->getMessage());
            return 1;
        }
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.host', $host);
        Config::set('database.connections.mysql.port', $port);
        Config::set('database.connections.mysql.database', $db);
        Config::set('database.connections.mysql.username', $user);
        Config::set('database.connections.mysql.password', $pass);
        $this->info('Running migrate:fresh --seed on testing database');
        Artisan::call('migrate:fresh', ['--seed' => true]);
        $this->line(Artisan::output());
        return 0;
    }
}
