<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfflinePlanChange extends Model
{
    protected $appends = ['file'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function offline_method()
    {
        return $this->belongsTo(OfflinePaymentMethod::class, 'offline_method_id');
    }

    public function getFileAttribute()
    {
        return ($this->file_name) ? asset_url('offline-payment-files/' . $this->file_name) : asset('default-profile-3.png');
    }
}
