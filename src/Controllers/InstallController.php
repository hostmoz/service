<?php

namespace SpondonIt\Service\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Session\Session;
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

    public function index(){
        return view('service::install.welcome');
    }

    public function preRequisite(){
        $checks = $this->repo->getPreRequisite();
		$server_checks = $checks['server'];
		$folder_checks = $checks['folder'];
        $verifier = $checks['verifier'];
        $has_false = in_array(false, $checks);

		envu(['APP_ENV' => 'local']);
		$name = env('APP_NAME');

		return view('service::install.preRequisite', compact('server_checks', 'folder_checks', 'name', 'verifier', 'has_false'));
    }

    public function license(){
        $checks = $this->repo->getPreRequisite();
        if(in_array(false, $checks)){
            return redirect()->route('service.preRequisite')->with(['message' => __('service::install.requirement_failed'), 'status' => 'error']);
        }

		return view('service::install.license');
    }

    public function post_license(LicenseRequest $request){
        $this->repo->validateLicense($request->all());
		return response()->json(['message' => __('service::install.valid_license'), 'goto' => route('service.database')]);
    }

    public function database(){
        $checks = $this->repo->checkLicense();
        if(!$checks){
            return redirect()->route('service.license')->with(['message' => __('service::install.invalid_license'), 'status' => 'error']);
        }
		return view('service::install.database');
    }

    public function post_database(DatabaseRequest $request){
        $this->repo->validateDatabase($request->all());
        session()->flash('database', 'connected');
		return response()->json(['message' => __('service::install.connection_established'), 'goto' => route('service.user')]);
    }

    public function user(){
        if(session('database') != 'connected'){
            return redirect()->route('service.database')->with(['message' => 'Please set your database connection.', 'status' => 'error']);
        }
		return view('service::install.user');
    }

    public function post_user(UserRequest $request){
        $this->repo->install($request->all());
		return response()->json(['message' => __('service::install.done_msg'), 'goto' => route('service.done')]);
    }

    public function done(){
		return view('service::install.done');
    }

     public function ManageAddOnsValidation(ModuleInstallRequest $request)
    {
        $response = $this->repo->installModule($request->all());
        return response()->json(['message' => __('service::install.module_verify'), 'reload' => '']);
    }


}
