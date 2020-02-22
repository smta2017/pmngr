<?php

namespace App;

use App\Observers\TaskFileObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TaskFile extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::observe(TaskFileObserver::class);

        $company = company();

        static::addGlobalScope('company', function (Builder $builder) use($company) {
            if ($company) {
                $builder->where('task_files.company_id', '=', $company->id);
            }
        });
    }
}
