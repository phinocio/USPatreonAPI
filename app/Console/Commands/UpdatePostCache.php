<?php

namespace App\Console\Commands;

use App\Http\Controllers\PatreonController;
use App\Models\Token;
use Illuminate\Console\Command;

class UpdatePostCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patreon:postcache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the DB cache of Posts';

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

        $postsUrl = 'https://patreon.com/api/oauth2/v2/campaigns/' . $campaign_id . '/posts?fields[post]=title,content,is_public,published_at,url';

        PatreonController::generatePosts($postsUrl, $access_token);

        $this->info('Post cache updated');

        return 0;
    }
}
