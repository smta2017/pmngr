<?php

namespace App;

use App\Observers\TicketFileObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TicketFile extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::observe(TicketFileObserver::class);

        $company = company();

        static::addGlobalScope('company', function (Builder $builder) use($company) {
            if ($company) {
                $builder->where('ticket_files.company_id', '=', $company->id);
            }
        });
    }
}
