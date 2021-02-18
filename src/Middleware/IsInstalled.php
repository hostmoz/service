<?php

namespace SpondonIt\Service\Middleware;

use Closure;
use Illuminate\Support\Facades\Storage;
use SpondonIt\Service\Repositories\InitRepository;

class IsInstalled
{
    protected $repo;

    public function __construct(
        InitRepository $repo
    ) {
        $this->repo = $repo;
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
        $this->repo->init();

        if($this->inExceptArray($request)){
            return $next($request);
        }

        // $this->repo->check();

        $c = Storage::exists('.app_installed') ? Storage::get('.app_installed') : false;
        if (!$c) {
            return redirect('/install');
        }

        $this->repo->config();

        return $next($request);


    }

      /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        'install', 'install/*'
    ];

    protected function inExceptArray($request)
    {

        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }
            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
