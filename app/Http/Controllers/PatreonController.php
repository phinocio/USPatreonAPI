<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PatreonController extends Controller
{

	public static function getPatrons($url, $access_token)
	{
		$patronCache = \App\Models\PatronCache::first();

		if (!$patronCache) {
			$patronCache = PatreonController::generatePatrons($url, $access_token);
		}

		return json_decode($patronCache->patrons);
	}

	public static function getPosts($url, $access_token)
	{
		$postCache = \App\Models\PostCache::first();

		if (!$postCache) {
			$postCache = PatreonController::generatePosts($url, $access_token);
		}

		return json_decode($postCache->posts);
	}

	public static function generatePosts($url, $access_token)
	{
		$allPosts = [];
		$resp = Http::withToken($access_token)->get($url);

		foreach ($resp['data'] as $post) {
			if ($post['attributes']['is_public'] != false) {
				$post = [
					'title' => $post['attributes']['title'],
					'content' => $post['attributes']['content'],
					'published' => $post['attributes']['published_at'],
					'url' => $post['attributes']['url']
				];
				array_push($allPosts, $post);
			}
		}
		$nextLink = $resp['links']['next'];

		while ($nextLink != false) {
			$resp = Http::withToken($access_token)->get($nextLink);

			foreach ($resp['data'] as $post) {
				if ($post['attributes']['is_public'] != false) {
					$post = [
						'title' => $post['attributes']['title'],
						'content' => $post['attributes']['content'],
						'published' => $post['attributes']['published_at'],
						'url' => $post['attributes']['url']
					];
					array_push($allPosts, $post);
				}
			}

			if (isset($resp['links']['next'])) {
				$nextLink = $resp['links']['next'];
			} else {
				$nextLink = false;
			}
		}
		$cache = \App\Models\PostCache::first();

		if (!$cache) {
			$cache = new \App\Models\PostCache();
			echo 'no cache';
		}
		$cache->posts = json_encode(array_reverse($allPosts));
		$cache->save();

		return $cache;
	}

	public static function generatePatrons($url, $access_token)
	{
		$activePatronIDs = [];
		$activePatronNames = [];
		$resp = Http::withToken($access_token)->get($url);
		$tiers = [];

		// Get a list of all IDs for active patrons.
		foreach ($resp['data'] as $patron) {
			if ($patron['attributes']['patron_status'] == 'active_patron') {
				$userID = $patron['relationships']['user']['data']['id'];
				$tierID = $patron['relationships']['currently_entitled_tiers']['data'] ? $patron['relationships']['currently_entitled_tiers']['data'][0]['id'] : '';

				array_push($activePatronIDs, ['userID' => $userID, 'tierID' => $tierID]);
			}
		}

		// Get a list of tiers
		foreach ($resp['included'] as $tier) {
			if ($tier['type'] == 'tier') {
				// Convert Adoring Fan to Patron
				if ($tier['attributes']['title'] == 'Adoring Fan') {
					$tier['attributes']['title'] = 'Patron';
				}

				// Convert Champion to Super Patron
				if ($tier['attributes']['title'] == 'Champion') {
					$tier['attributes']['title'] = 'Super Patron';
				}

				array_push($tiers, $tier);
			}
		}

		// Loop through the list of active patron IDs compared to the included user info to build the names array
		foreach ($resp['included'] as $user) {
			foreach ($activePatronIDs as $id) {
				if ($user['id'] == $id['userID']) {
					$name = $user['attributes']['vanity'] ? trim($user['attributes']['vanity']) : trim($user['attributes']['full_name']);

					$tierName = '';
	
					foreach ($tiers as $tier) {
						if ($tier['id'] == $id['tierID']) {
							$tierName = $tier['attributes']['title'];
						}
					}
	
					array_push($activePatronNames, ['name' => $name, 'tier' => $tierName]);
				}

			}
		}

		$cache = \App\Models\PatronCache::first();

		if (!$cache) {
			$cache = new \App\Models\PatronCache();
		}

		$cache->patrons = json_encode($activePatronNames);
		$cache->save();

		return $cache;
	}
}
