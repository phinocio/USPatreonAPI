<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PatreonController extends Controller
{

    public static function getPatrons() {
		$access_token = env('PATREON_TOKEN', false);
		
		$api_client = new \Patreon\API($access_token);
		$campaign_response = $api_client->fetch_campaigns();
		dd($campaign_response);
		// $campaign = $campaign_response->get('data')->get('0');
		// echo "campaign is\n";
		// print_r($campaign->asArray(true));
		// $user = $campaign->relationship('creator')->resolve($campaign_response);
		// echo "user is\n";
		// print_r($user->asArray(true));
	}
}
