<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\PatreonController;

class UpdatePatronCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:patroncache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the DB cache of patrons.';

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
		$access_token = env('PATREON_TOKEN');
		$api_client = new \Patreon\API($access_token);
		$campaign_response = $api_client->fetch_campaigns();
		$campaign_id = $campaign_response['data'][0]['id'];

		$membersUrl = 'https://patreon.com/api/oauth2/v2/campaigns/' . $campaign_id . '/members?page[size]=3000&include=user&fields[user]=first_name,full_name,vanity&fields[member]=patron_status';

		PatreonController::generatePatrons($membersUrl, $access_token);

        return 0;
    }
}
