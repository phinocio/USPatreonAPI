<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PatreonController extends Controller
{

	public static function getPatrons($url, $access_token) 
	{
		// TODO: Cache the list of patrons, and if it's x hours old, refresh it.
		$patronCache = \App\Models\PatronCache::first();

		if(!$patronCache) {
			$patronCache = PatreonController::generatePatrons($url, $access_token);
		}
		
		return $patronCache->patrons;
	}

	public static function getPosts($url, $access_token) 
	{
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

	private static function getPatronIDs($url, $access_token)
	{
		$allPatronIDs = [];
		$resp = Http::withToken($access_token)->get($url);

		foreach ($resp['data'] as $patron) {
			if ($patron['attributes']['patron_status'] == 'active_patron') {
				array_push($allPatronIDs, $patron);
			}
		}

		$nextLink = $resp['links']['next'];

		while ($nextLink != false) {
			$resp = Http::withToken($access_token)->get($nextLink);

			foreach ($resp['data'] as $patron) {
				if ($patron['attributes']['patron_status'] == 'active_patron') {
					array_push($allPatronIDs, $patron);
				}
			}

			if (isset($resp['links']['next'])) {
				$nextLink = $resp['links']['next'];
			} else {
				$nextLink = false;
			}
		}

		return $allPatronIDs;
	}

	public static function generatePatrons($url, $access_token)
	{
		$IDs = PatreonController::getPatronIDs($url, $access_token);
		$patrons = [];

		foreach ($IDs as $id) {
			$resp = Http::withToken($access_token)->get('https://patreon.com/api/oauth2/v2/members/' . $id['id'] . '?include=user&fields[user]=first_name,full_name,vanity');

			array_push($patrons, $resp['included'][0]['attributes']);
		}

		// Convert to CSV then save to DB.
		$patronCSV = '';
		foreach($patrons as $patron) {
			if($patron['vanity']) {
				$patronCSV .= trim($patron['vanity']) . ', ';
			} else {
				$patronCSV .= trim($patron['full_name']) . ', ';
			}
		}

		$cache = \App\Models\PatronCache::first();

		if(!$cache) {
			$cache = new \App\Models\PatronCache();		
		}

		$cache->patrons = $patronCSV;
		$cache->save();

		return $cache;
	}
}
