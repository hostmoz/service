<?php

namespace SpondonIt\Service\Repositories;
ini_set('max_execution_time', -1);


use Illuminate\Support\Facades\Storage;
use Throwable;

class LicenseRepository {
	/**
	 * Instantiate a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {

	}

	public function revoke() {

        $ac = Storage::exists('.access_code') ? Storage::get('.access_code') : null;
        $e = Storage::exists('.account_email') ? Storage::get('.account_email') : null;
        $c = Storage::exists('.app_installed') ? Storage::get('.app_installed') : null;
        $v = Storage::exists('.version') ? Storage::get('.version') : null;

        $url = config('app.verifier') . '/api/cc?a=remove&u=' . $_SERVER['HTTP_HOST'] . '&ac=' . $ac . '&i=' . config('app.item') . '&e=' . $e . '&c=' . $c . '&v=' . $v;
        $response = curlIt($url);

        \Auth::logout();

		\Artisan::call('db:wipe', ['--force' => true]);

		envu([
            'DB_PORT' => '3306',
            'DB_HOST' => 'localhost',
            'DB_DATABASE' => "",
            'DB_USERNAME' => "",
            'DB_PASSWORD' => "",
        ]);

        Storage::delete(['.access_code', '.account_email']);
        Storage::put('.app_installed', '');
        
    }
}
