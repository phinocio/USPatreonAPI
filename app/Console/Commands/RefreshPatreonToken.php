<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshPatreonToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patreon:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes the Patreon token';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
		$url = "https://patreon.com/api/oauth2/token?grant_type=refresh_token&refresh_token=" . config('app.patreon_refresh') . "&client_id=" . config('app.patreon_client');
		$response = \Http::post($url);
		dd($response->json());
        return 0;
    }
}
