<?php

namespace SpondonIt\Service\Repositories;
ini_set('max_execution_time', -1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
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

        $url = config('app.verifier') . '/api/cc?a=remove&u=' . app_url() . '&ac=' . $ac . '&i=' . config('app.item') . '&e=' . $e . '&c=' . $c . '&v=' . $v;

        $response = curlIt($url);

        Auth::logout();

		Artisan::call('db:wipe', ['--force' => true]);

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


public function revokeModule($params){

    $name = gv($params, 'name');

    $module_class_name = config('spondonit.module_manager_model');
    $moduel_class = new $module_class_name;
    $s = $moduel_class->where('name', $name)->first();

    if ($s) {
        $row = gbv($params, 'row');
        $file = gbv($params, 'file');

        $dataPath = base_path('Modules/' . $name . '/' . $name . '.json');

        $strJsonFileContents = file_get_contents($dataPath);
        $array = json_decode($strJsonFileContents, true);

        $item_id = $array[$name]['item_id'];
        $version = $array[$name]['versions'][0];

         $url = config('app.verifier') . '/api/cc?a=remove&u=' . app_url() . '&ac=' . $s->purchase_code . '&i=' . $item_id . '&t=Module'.'&c='.$s->checksum.'&v='.$version;

        $response = curlIt($url);

        $this->disableModule($name, $row, $file);
    }

}

protected function disableModule($module_name, $row = false, $file = false)
    {
        
        $settings_model_name = config('spondonit.settings_model');
        $settings_model = new $settings_model_name;
        if ($row) {
            $config = $settings_model->firstOrNew(['key' => $module_name]);
            $config->value = 0;
            $config->save();
        } else if($file){
            app('general_settings')->put([
               $module_name => 0
            ]);
        } else {
            $config = $settings_model->find(1);
            $config->$module_name = 0;
            $config->save();
        }
        $module_model_name = config('spondonit.module_model');
        $module_model = new $module_model_name;
        $ModuleManage = $module_model::find($module_name)->disable();
    }

    }
