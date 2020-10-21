<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\PatreonController;
use Exception;

class ResponseController extends Controller
{
	public function index() {
		try {
			$response = PatreonController::getPatrons();
		} catch (Exception $e) {
			var_dump($e);
		}
		return response()->json([
			'name' => 'Abigail',
			'state' => 'CA',
		]);
	}
    
}
