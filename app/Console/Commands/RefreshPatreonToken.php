<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Token;
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
		$token = Token::first();

		if (!$token) {
			$token = new Token();
			$token->access = config('app.patreon_token');
			$token->refresh = config('app.patreon_refresh');
			$token->client = config('app.patreon_client');
			$token->expires_in = '';
			$token->expires = Carbon::now()->subSeconds(10);
			$token->save();
		}

		if(Carbon::now() > $token->expires) {
			$url = "https://patreon.com/api/oauth2/token?grant_type=refresh_token&refresh_token=" . $token->refresh . "&client_id=" . $token->client;
			$response = \Http::post($url);
	
			$token->access = $response['access_token'];
			$token->refresh = $response['refresh_token'];
			$token->expires_in = $response['expires_in'];
			$token->expires = Carbon::now()->addSeconds($response['expires_in']);
			$token->save();

			$this->info('Token has been refreshed!');
		} else {
			$this->info('Token is still valid, so not refreshed.');
		}

        return 0;
    }
}
