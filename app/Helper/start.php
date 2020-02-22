<?php
use Illuminate\Support\Str;

if (!function_exists('superAdmin')) {
    function superAdmin()
    {
        return auth()->user();
    }
}
if (!function_exists('company')) {
    function company()
    {
        if(auth()->user()) {
            $companyId = auth()->user()->company_id;
            $company = \App\Company::find($companyId);
            return $company;
        }

        return false;
    }
}

if (!function_exists('asset_url')) {

    // @codingStandardsIgnoreLine
    function asset_url($path)
    {
        $path = 'user-uploads/' . $path;
        $storageUrl = $path;

        if (!Str::startsWith($storageUrl, 'http')) {
            return url($storageUrl);
        }

        return $storageUrl;

    }

}

