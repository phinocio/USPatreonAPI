<?php

namespace App\Console\Commands;

use App\Http\Controllers\PatreonController;
use App\Models\Token;
use Illuminate\Console\Command;

class UpdatePatronCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patreon:patroncache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the DB cache of Patrons';

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
        $access_token = Token::first()->access;
        $api_client = new \Patreon\API($access_token);
        $campaign_response = $api_client->fetch_campaigns();
        $campaign_id = $campaign_response['data'][0]['id'];

        $membersUrl = 'https://patreon.com/api/oauth2/v2/campaigns/' . $campaign_id . '/members?page[size]=3000&include=user,currently_entitled_tiers&fields[tier]=title&fields[user]=full_name,vanity&fields[member]=full_name,patron_status';

        PatreonController::generatePatrons($membersUrl, $access_token);

        $this->info('Patron cache updated');

        return 0;
    }
}
