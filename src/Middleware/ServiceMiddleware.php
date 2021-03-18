<?php

namespace SpondonIt\Service\Middleware;

use Closure;
use SpondonIt\Service\Repositories\InitRepository as ServiceRepository;
use Illuminate\Support\Facades\Storage;

class ServiceMiddleware
{
    protected $repo, $service_repo;

    public function __construct(
        ServiceRepository $service_repo
    ) {
        $this->service_repo = $service_repo;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $logout = \Storage::exists('.logout') ? \Storage::get('.logout') : false;
     
          if ($logout) {
            \Auth::logout();
            Storage::delete(['.access_code', '.account_email']);
            Storage::put('.app_installed', '');
            \Storage::delete('.logout');
            return redirect('/install');
          }

          if (\Auth::check() and \Auth::user()->role_id == 1) {
            $this->service_repo->check();
          }
        if (\Auth::check() and \Auth::user()->role_id == 1) {
          $this->service_repo->check();
        }

        return $next($request);
    }
}
