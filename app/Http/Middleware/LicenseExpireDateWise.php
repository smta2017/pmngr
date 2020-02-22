<?php

namespace App\Http\Middleware;

use App\Company;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Redirect;

class LicenseExpireDateWise
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        $company = Company::where('id', $user->company_id)->first();
        $expireOn = $company->licence_expire_on;
        $currentDate = Carbon::now();

        if ((!is_null($expireOn) && $expireOn->lessThan($currentDate)) || $company->status == 'license_expired'){
            return Redirect::route('admin.billing');
        }
        return $next($request);
    }
}
