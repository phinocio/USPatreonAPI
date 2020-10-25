<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\PatreonController;
use Exception;

class ResponseController extends Controller
{
	public function index() {
		$access_token = env('PATREON_TOKEN');
		$api_client = new \Patreon\API($access_token);
		$campaign_response = $api_client->fetch_campaigns();
		$campaign_id = $campaign_response['data'][0]['id'];
		$postsUrl = 'https://patreon.com/api/oauth2/v2/campaigns/' . $campaign_id . '/posts?fields[post]=title,content,is_public,published_at,url';
		try {
			$posts = PatreonController::getPosts($postsUrl, $access_token);
			//$patrons = PatreonController::getPatrons();

			dd($posts);
		} catch (Exception $e) {
			var_dump($e);
		}
	}
    
}
