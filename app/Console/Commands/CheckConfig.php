<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckConfig extends Command
{
    protected $signature = 'retide:check-config';
    protected $description = 'Cek config wajib sudah terisi (tanpa menampilkan nilainya)';

    public function handle(): int
    {
        $default = config('database.default');
        $keys = ['app.key', 'midtrans.server_key', 'cloudinary.cloud_url'];

        if ($default !== 'sqlite') {
            $keys[] = "database.connections.$default.host";
        }

        $missing = [];
        foreach ($keys as $key) {
            if (blank(config($key))) {
                $missing[] = $key;
                $this->error("MISSING: $key");
            }
        }

        if ($missing) {
            return self::FAILURE;
        }

        $this->info('Semua config wajib sudah terisi.');
        return self::SUCCESS;
    }
}
