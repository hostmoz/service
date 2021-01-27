<?php
namespace SpondonIt\Service\Repositories;
ini_set('max_execution_time', -1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use Throwable;

class InstallRepository {
	/**
	 * Instantiate a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * Used to compare version of PHP
	 */
	public function my_version_compare($ver1, $ver2, $operator = null) {
		$p = '#(\.0+)+($|-)#';
		$ver1 = preg_replace($p, '', $ver1);
		$ver2 = preg_replace($p, '', $ver2);
		return isset($operator) ?
		version_compare($ver1, $ver2, $operator) :
		version_compare($ver1, $ver2);
	}

	/**
	 * Used to check whether pre requisites are fulfilled or not and returns array of success/error type with message
	 */
	public function check($boolean, $message, $help = '', $fatal = false) {
		if ($boolean) {
			return array('type' => 'success', 'message' => $message);
		} else {
			return array('type' => 'error', 'message' => $help);
		}
	}


	/**
	 * Used to check whether pre requisites are fulfilled or not and returns array of success/error type with message
	 */
	public function checkPreviousInstallation() {

		try {
	        DB::connection()->getPdo();

	        if(Schema::hasTable(config('spondonit.settings_table')) && Schema::hasTable('users')){
		        $settings_model_name = config('spondonit.settings_model');
		         $settings_model = new $settings_model_name;
		        $config = $settings_model->find(1);
		        $url = config('app.verifier') . '/api/cc?a=install&u=' . $_SERVER['HTTP_HOST'] . '&ac=' . $config->system_purchase_code . '&i=' . config('app.item') . '&e=' . $config->email;

		        $response = curlIt($url);
		        $status = (isset($response['status']) && $response['status']) ? 1 : 0;

		        if ($status) {
		            $checksum = isset($response['checksum']) ? $response['checksum'] : null;
		            $response = true;
		        } else {
		           return false;
		        }

		        Storage::put('.app_installed', isset($checksum) ? $checksum : '');
		        Storage::put('.access_code', $config->system_purchase_code );
		        Storage::put('.account_email', $config->email);

		        return true;
		    }
	   
	    } catch (\Exception $e) {
	        return false;
	    }
	}

	/**
	 * Check all pre-requisite for script
	 */
	public function getPreRequisite() {
		$server[] = $this->check((dirname($_SERVER['REQUEST_URI']) != '/' && str_replace('\\', '/', dirname($_SERVER['REQUEST_URI'])) != '/'), 'Installation directory is valid.', 'Please use root directory or point your sub directory to domain/subdomain to install.', true);
		$server[] = $this->check($this->my_version_compare(phpversion(), '7.2.0', '>='), sprintf('Min PHP version 7.2.0 (%s)', 'Current Version ' . phpversion()), 'Current Version ' . phpversion(), true);
		$server[] = $this->check(extension_loaded('fileinfo'), 'Fileinfo PHP extension enabled.', 'Install and enable Fileinfo extension.', true);
		$server[] = $this->check(extension_loaded('ctype'), 'Ctype PHP extension enabled.', 'Install and enable Ctype extension.', true);
		$server[] = $this->check(extension_loaded('json'), 'JSON PHP extension enabled.', 'Install and enable JSON extension.', true);
		$server[] = $this->check(extension_loaded('openssl'), 'OpenSSL PHP extension enabled.', 'Install and enable OpenSSL extension.', true);
		$server[] = $this->check(extension_loaded('tokenizer'), 'Tokenizer PHP extension enabled.', 'Install and enable Tokenizer extension.', true);
		$server[] = $this->check(extension_loaded('mbstring'), 'Mbstring PHP extension enabled.', 'Install and enable Mbstring extension.', true);
		$server[] = $this->check(extension_loaded('zip'), 'Zip archive PHP extension enabled.', 'Install and enable Zip archive extension.', true);
		$server[] = $this->check(class_exists('PDO'), 'PDO is installed.', 'Install PDO (mandatory for Eloquent).', true);
		$server[] = $this->check(extension_loaded('curl'), 'CURL is installed.', 'Install and enable CURL.', true);
		$server[] = $this->check(ini_get('allow_url_fopen'), 'allow_url_fopen is on.', 'Turn on allow_url_fopen.', true);

		$folder[] = $this->check(is_writable(base_path('/.env')), 'File .env is writable', 'File .env is not writable', true);
		$folder[] = $this->check(is_writable(base_path("/storage/framework")), 'Folder /storage/framework is writable', 'Folder /storage/framework is not writable', true);
		$folder[] = $this->check(is_writable(base_path("/storage/logs")), 'Folder /storage/logs is writable', 'Folder /storage/logs is not writable', true);
		$folder[] = $this->check(is_writable(base_path("/bootstrap/cache")), 'Folder /bootstrap/cache is writable', 'Folder /bootstrap/cache is not writable', true);

		$verifier = config('app.verifier');

		return ['server' => $server, 'folder' => $folder, 'verifier' => $verifier];
	}

	/**
	 * Validate database connection, table count
	 */
	public function validateDatabase($params) {
		$db_host = gv($params, 'db_host');
		$db_username = gv($params, 'db_username');
		$db_password = gv($params, 'db_password');
		$db_database = gv($params, 'db_database');

        $link = @mysqli_connect($db_host, $db_username, $db_password);

		if (!$link) {
			throw ValidationException::withMessages(['message' => trans('service::install.connection_not_established')]);
		}

        $select_db = mysqli_select_db($link, $db_database);
        if (!$select_db) {
			throw ValidationException::withMessages(['message' => trans('service::install.db_not_found')]);
        }

        $count_table_query = mysqli_query($link, "show tables");
		$count_table = mysqli_num_rows($count_table_query);

		if ($count_table) {
			throw ValidationException::withMessages(['message' => trans('service::install.existing_table_in_database')]);
        }

        $this->setDBEnv($params);

		return true;
    }

    public function validateLicense($params)
    {
        if (isTestMode()) {
			return;
		}

		if (!isConnected()) {
			return;
        }
      
       $url = config('app.verifier') . '/api/cc?a=install&u=' . $_SERVER['HTTP_HOST'] . '&ac=' . request('access_code') . '&i=' . config('app.item') . '&e=' . request('envato_email');
   
        
        $response = curlIt($url);

		$status = (isset($response['status']) && $response['status']) ? 1 : 0;

		if ($status) {
			$checksum = isset($response['checksum']) ? $response['checksum'] : null;
		} else {
			$message = gv($response, 'message') ? $response['message'] : trans('service::install.contact_script_author');
			throw ValidationException::withMessages(['access_code' => $message]);
        }

        Storage::put('.app_installed', isset($checksum) ? $checksum : '');
		Storage::put('.access_code', request('access_code'));
        Storage::put('.account_email', request('envato_email'));

        return true;

    }

    public function checkLicense() {
		if (isTestMode()) {
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

		$status = $response['status'];

		if (!$status) {
			Storage::delete(['.access_code', '.account_email']);
			Storage::put('.app_installed', '');
			return false;
		} else {
			Storage::put('.access_log', date('Y-m-d'));
			return true;
		}
    }



	/**
	 * Install the script
	 */
	public function install($params) {

		$this->migrateDB();

        $this->makeAdmin($params);

		$this->seed(gbv($params, 'seed'));

		$this->postInstallScript();

		File::cleanDirectory('storage/app/public');
		Artisan::call('storage:link');
        envu([
            'APP_ENV' => 'production',
            'APP_DEBUG'     =>  'false',
            ]);

        Artisan::call('key:generate');
	}

	public function postInstallScript(){
		//write your post install script here
	}

	/**
	 * Write to env file
	 */
	public function setDBEnv($params) {
		envu([
			'APP_URL' => app_url(),
			'DB_PORT' => gv($params, 'db_port'),
			'DB_HOST' => gv($params, 'db_host'),
			'DB_DATABASE' => gv($params, 'db_database'),
			'DB_USERNAME' => gv($params, 'db_username'),
			'DB_PASSWORD' => gv($params, 'db_password'),
		]);

		DB::disconnect('mysql');

		config([
			'database.connections.mysql.host' => gv($params, 'db_host'),
			'database.connections.mysql.port' => gv($params, 'db_port'),
			'database.connections.mysql.database' => gv($params, 'db_database'),
			'database.connections.mysql.username' => gv($params, 'db_username'),
			'database.connections.mysql.password' => gv($params, 'db_password'),
		]);

		DB::setDefaultConnection('mysql');
	}

	/**
	 * Mirage tables to database
	 */
	public function migrateDB() {
        try {
            Artisan::call('migrate:refresh', array('--force' => true));
        } catch (Throwable $e) {
            $db = DB::select('SELECT @@global.max_allowed_packet as max_allowed_packet');
        	$old = $db[0]->max_allowed_packet;
            $sql = base_path('database/'.config('spondonit.database_file'));
            if(File::exists($sql)){
            	DB::statement('SET @@global.max_allowed_packet = ' . (strlen( $sql ) + 1024));
            	DB::unprepared(file_get_contents($sql));
            	DB::statement('SET @@global.max_allowed_packet = ' . $old);
            }

        }
	}

	/**
	 * Seed tables to database
	 */
	public function seed($seed = 0) {
		if (!$seed) {
			return;
		}

		$db = Artisan::call('db:seed');
	}


	/**
	 * Insert default admin details
	 */
	public function makeAdmin($params) {
        $user_model_name = config('spondonit.user_model');
    	$user = new $user_model_name;
        $user->full_name = gv($params, 'name');
		$user->email = gv($params, 'email');
		$user->username = gv($params, 'username', gv($params, 'email'));
        $user->role_id = 1;
		$user->uuid = Str::uuid();
		$user->password = bcrypt(gv($params, 'password', 'abcd1234'));
        $user->save();

        return true;
	}

	public function installModule($params){

        $code = gv($params, 'purchase_code');
        $name = gv($params, 'name');


        $dataPath = base_path('Modules/' . $name . '/' . $name . '.json');

        $strJsonFileContents = file_get_contents($dataPath);
        $array = json_decode($strJsonFileContents, true);

        $item_id = $array[$name]['item_id'];
        
        $e = Storage::exists('.account_email') ? Storage::get('.account_email') : null;

        $url = config('app.verifier') . '/api/cc?a=install&u=' . $_SERVER['HTTP_HOST'] . '&ac=' . $code  . '&i=' .$item_id . '&e=' . $e.'&t=Module';
           
        $response = curlIt($url);

  		
        $status = gbv($response, 'status', 0);
            
        if ($status) {

            // added a new column in sm general settings
            if (!Schema::hasColumn(config('spondonit.settings_table'), $name)) {
                Schema::table(config('spondonit.settings_table'), function ($table) use ($name) {
                    $table->integer($name)->default(1)->nullable();
                });
            }



            try {
                                 
                $version = $array[$name]['versions'][0];
                $url = $array[$name]['url'][0];
                $notes = $array[$name]['notes'][0];

                DB::beginTransaction();
                $module_class_name = config('spondonit.module_manager_model');
                $moduel_class = new $module_class_name;
                $s =$moduel_class->where('name', $name)->first();
                if (empty($s)) {
                    $s = $moduel_class;
                }
                $s->name = $name;
                $s->email = $e;
                $s->notes = $notes;
                $s->version = $version;
                $s->update_url = $url;
                $s->installed_domain = url('/');
                $s->activated_date = date('Y-m-d');
                $s->purchase_code = $code;
                $s->checksum = gv($response, 'checksum');
                $r = $s->save();

                $settings_model_name = config('spondonit.settings_model');
                $settings_model = new $settings_model_name;
                $config = $settings_model->find(1);
                $config->$name = 1;
                $r = $config->save();

                DB::commit();


                return true;
                
            } catch (\Exception $e) {
                DB::rollback();
                $this->disableModule($name);
               	throw ValidationException::withMessages(['message' => $e->getMessage()]);
            }
        } else {
            $this->disableModule($name);
           	throw ValidationException::withMessages(['message' => gv($response, 'message', 'Something is not right')]);
        }
    }

    protected function disableModule($module_name){
    	$settings_model_name = config('spondonit.settings_model');
    	$settings_model = new $settings_model_name;
        $config = $settings_model->find(1);
        $config->$module_name = 0;
        $config->save();
        $module_model_name = config('spondonit.module_model');
        $module_model = new $module_model_name;
        $ModuleManage = $module_model::find($module_name)->disable();
    }
}
