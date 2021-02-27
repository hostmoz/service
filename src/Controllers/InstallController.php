<?php

namespace SpondonIt\Service\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SpondonIt\Service\Repositories\InstallRepository;
use SpondonIt\Service\Requests\DatabaseRequest;
use SpondonIt\Service\Requests\LicenseRequest;
use SpondonIt\Service\Requests\UserRequest;
use SpondonIt\Service\Requests\ModuleInstallRequest;

class InstallController extends Controller{
    protected $repo, $request;

    public function __construct(InstallRepository $repo, Request $request)
    {
        $this->repo = $repo;
        $this->request = $request;
    }


    public function preRequisite(){
  
        $ac = Storage::exists('.app_installed') ? Storage::get('.app_installed') : null;
        if($ac){
            abort(404);
        }
        $checks = $this->repo->getPreRequisite();
		$server_checks = $checks['server'];
		$folder_checks = $checks['folder'];
        $verifier = $checks['verifier'];
        $has_false = in_array(false, $checks);

		envu(['APP_ENV' => 'production']);
		$name = env('APP_NAME');

		return view('service::install.preRequisite', compact('server_checks', 'folder_checks', 'name', 'verifier', 'has_false'));
    }

    public function license(){
   
        $checks = $this->repo->getPreRequisite();
        if(in_array(false, $checks)){
            return redirect()->route('service.preRequisite')->with(['message' => __('service::install.requirement_failed'), 'status' => 'error']);
        }

        $ac = Storage::exists('.app_installed') ? Storage::get('.app_installed') : null;
        if($ac){
            abort(404);
        }

		return view('service::install.license');
    }

    public function post_license(LicenseRequest $request){
        $this->repo->validateLicense($request->all());
        session()->flash('license', 'verified');
		return response()->json(['message' => __('service::install.valid_license'), 'goto' => route('service.database')]);
    }

    public function database(){
        
        $ac = Storage::exists('.temp_app_installed') ? Storage::get('.temp_app_installed') : null;
        if(!$ac){
            abort(404);
        }
        if ($this->repo->checkDatabaseConnection()) {
            return redirect()->route('service.user')->with(['message' => __('service::install.connection_established'), 'status' => 'success']);
        }
		return view('service::install.database');
    }

    public function post_database(DatabaseRequest $request){
        $this->repo->validateDatabase($request->all());
        session()->flash('database', 'connected');
		return response()->json(['message' => __('service::install.connection_established'), 'goto' => route('service.user')]);
    }


    public function done(){

        $data['user'] = Storage::exists('.user_email') ? Storage::get('.user_email') : null;
        $data['pass'] = Storage::exists('.user_pass') ? Storage::get('.user_pass') : null;

        if($data['user'] and $data['pass']){
            \Log::info('done');
            Storage::delete(['.user_email', '.user_pass']);
            return view('service::install.done', $data);
        } else{
            abort(404);
        }

		
    }

     public function ManageAddOnsValidation(ModuleInstallRequest $request){
        $response = $this->repo->installModule($request->all());
        return response()->json(['message' => __('service::install.module_verify'), 'reload' => true]);
    }

    


}
