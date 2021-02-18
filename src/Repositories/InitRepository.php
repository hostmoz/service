<?php

namespace SpondonIt\Service\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class InitRepository {

    public function init() {
		config(['app.item' => config('spondonit.item_id')]);
		config(['app.verifier' => 'http://auth.uxseven.com']);
    }

    public function config()
	{
		$config = collect();

		// write your code here which need to load berfore request

		return $config;
	}

    public function checkDatabase(){
        
       
        try {
	        DB::connection()->getPdo();

            if(!Schema::hasTable(config('spondonit.settings_table')) || !Schema::hasTable('users')){
               
                Storage::delete(['.access_code', '.account_email']);
			    Storage::put('.app_installed', '');
            }
            
        } catch(\Exception $e){
            $error = $e->getCode();
            if($error == 2002){
                return abort(403, 'No connection could be made because the target machine actively refused it');
            } else if($error == 1045){
                $c = Storage::exists('.app_installed') ? Storage::get('.app_installed') : false;{
                    if($c){
                        return abort(403, 'Access denied for user. Please check your database username and password.');
                    }
                }
            } 

            // return abort(403, $e->getMessage());
            
        }
    }

    public function check() {

        $this->checkDatabase();

		if (isTestMode()) {
			return;
		}

		if (Storage::exists('.access_log') && Storage::get('.access_log') == date('Y-m-d')) {
			return;
		}

		if (!isConnected()) {
			return;
		}

		$ac = Storage::exists('.access_code') ? Storage::get('.access_code') : null;
		$e = Storage::exists('.account_email') ? Storage::get('.account_email') : null;
		$c = Storage::exists('.app_installed') ? Storage::get('.app_installed') : null;
		$v = Storage::exists('.version') ? Storage::get('.version') : null;

		$url = config('app.verifier') . '/api/cc?a=verify&u=' . url()->current() . '&ac=' . $ac . '&i=' . config('app.item') . '&e=' . $e . '&c=' . $c . '&v=' . $v;
		$response = curlIt($url);

		$status = $response['status'];

		if (!$status) {
			Storage::delete(['.access_code', '.account_email']);
			Storage::put('.app_installed', '');
		} else {
			Storage::put('.access_log', date('Y-m-d'));
		}
    }

}
