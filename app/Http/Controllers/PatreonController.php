<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PatreonController extends Controller
{

    public static function getPatrons() {
		$access_token = env('PATREON_TOKEN');
		$api_client = new \Patreon\API($access_token);
		$campaign_response = $api_client->fetch_campaigns();
		$campaign_id = $campaign_response['data'][0]['id'];
		//$details = $api_client->fetch_campaign_details($campaign_id);

		$resp = Http::withToken($access_token)->get('https://patreon.com/api/oauth2/v2/campaigns/' . $campaign_id . '/posts?fields[post]=title,content,is_public,published_at,url&page[cursor]=oO1VaaHnEyhUIq13xaX688_BgsM');

		// $resp = Http::withToken($access_token)->get('https://patreon.com/api/oauth2/v2/members/006defe4-1750-445b-843c-2f993db27272?fields[member]=full_name');

		// $resp = Http::withToken($access_token)->get('
		https://patreon.com/api/oauth2/v2/campaigns/352583/posts?fields[post]=title,Ccontent,is_public,published_at,url&page[cursor]=oO1VaaHnEyhUIq13xaX688_BgsM');

		$posts = $resp->json();

		// foreach ($posts['data'] as $post)
		// {
		// 	echo $post['attributes']['title'] . "<br>";
		// }

		// echo $posts['links']['next'];

	}

	public static function getPosts($url, $access_token) {
		$allPosts = [];
		$resp = Http::withToken($access_token)->get($url);
		

		foreach ($resp['data'] as $post) {
			if ($post['attributes']['is_public'] != false) {
				array_push($allPosts, $post);
			}
		}
		$nextLink = $resp['links']['next'];

		while($nextLink != false)
		{
			$resp = Http::withToken($access_token)->get($nextLink);
			
			foreach ($resp['data'] as $post) {
				if ($post['attributes']['is_public'] != false) {
					array_push($allPosts, $post);
				}
			}

			if(isset($resp['links']['next'])) {
				$nextLink = $resp['links']['next'];
			} else {
				$nextLink = false;
			}
			dump($resp->json());
		}

		dd($allPosts);

		return $allPosts;
	}
}
