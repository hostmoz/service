<?php

namespace SpondonIt\Service\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class InitRepository {

    public function init() {
		config(['app.verifier' => 'http://auth.uxseven.com']);
    }


    public function checkDatabase(){

        try {
	        DB::connection()->getPdo();

            if(!Schema::hasTable(config('spondonit.settings_table')) || !Schema::hasTable('users')){
			    return false;
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
        }

        return true;
    }

    public function check() {

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

		$url = config('app.verifier') . '/api/cc?a=verify&u=' . $_SERVER['HTTP_HOST'] . '&ac=' . $ac . '&i=' . config('app.item') . '&e=' . $e . '&c=' . $c . '&v=' . $v;
		$response = curlIt($url);

		if($response){
            $status = gbv($response, 'status');

            if (!$status) {
                \Log::info('Initial License Verification failed. Message: '. gv($response, 'message'));
                
                Storage::delete(['.access_code', '.account_email']);
                Storage::put('.app_installed', '');
                \Auth::logout();
                return redirect()->route('service.install')->send();
            } else {
                Storage::put('.access_log', date('Y-m-d'));
            }
        }
    }

     public function product() {
        if (!isConnected()) {
            throw ValidationException::withMessages(['message' => 'No internect connection.']);
        }

        $ac = Storage::exists('.access_code') ? Storage::get('.access_code') : null;
        $e = Storage::exists('.account_email') ? Storage::get('.account_email') : null;
        $c = Storage::exists('.app_installed') ? Storage::get('.app_installed') : null;
        $v = Storage::exists('.version') ? Storage::get('.version') : null;

        $about = file_get_contents(config('app.verifier') . '/about');
        $update_tips = file_get_contents(config('app.verifier') . '/update-tips');
        $support_tips = file_get_contents(config('app.verifier') . '/support-tips');

        $url = config('app.verifier') . '/api/cc?a=product&u=' .  $_SERVER['HTTP_HOST'] . '&ac=' . $ac . '&i=' . config('app.item') . '&e=' . $e . '&c=' . $c . '&v=' . $v;

    
        $response = curlIt($url);
  
        $status = gbv($response, 'status');
   
        if (!$status) {
            
            abort(404);
        }

        $product = gv($response, 'product', []);

        $next_release_build = gv($product, 'next_release_build');

        $is_downloaded = 0;
        if ($next_release_build) {
            if (File::exists( $next_release_build)) {
                $is_downloaded = 1;
            }
        }

        if (isTestMode()) {
            $product['purchase_code'] = config('system.hidden_field');
            $product['email'] = config('system.hidden_field');
            $product['access_code'] = config('system.hidden_field');
            $product['checksum'] = config('system.hidden_field');

            $is_downloaded = 0;
        }

        return compact('about', 'product', 'update_tips', 'support_tips', 'is_downloaded');
    }

}
