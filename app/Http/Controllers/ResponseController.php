<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\PatreonController;
use Exception;
use App\Models\Token;
use Carbon\Carbon;

class ResponseController extends Controller
{
	public function index()
	{
		$access_token = Token::first()->access;
		$api_client = new \Patreon\API($access_token);
		$campaign_response = $api_client->fetch_campaigns();
		$campaign_id = $campaign_response['data'][0]['id'];

		
		$postsUrl = 'https://patreon.com/api/oauth2/v2/campaigns/' . $campaign_id . '/posts?fields[post]=title,content,is_public,published_at,url';

		$membersUrl = 'https://patreon.com/api/oauth2/v2/campaigns/' . $campaign_id . '/members?page[size]=1000&include=user,currently_entitled_tiers&fields[tier]=title&fields[user]=full_name,vanity&fields[member]=full_name,patron_status';

		try {
			$posts = PatreonController::getPosts($postsUrl, $access_token);
			$patrons = PatreonController::getPatrons($membersUrl, $access_token);
			$data = [
				'patrons' => $patrons,
				'posts' => $posts
			];

			return $data;
		} catch (Exception $e) {
			var_dump($e);
		}
	}

	public function update()
	{
		try {
			\Artisan::call('patreon:patroncache');
			\Artisan::call('patreon:postcache');
			return response()->json([
				'message' => 'Patron and Post caches updated successfully',
				'status' => 200
			]);
		} catch (\Throwable $th) {
			//throw $th;
			return response()->json([
				'message' => 'Error: ' . $th->getMessage(),
				'status' => 500
			], 500);
		}
		
	}
}
