<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProposalItem extends Model
{
    protected $guarded = ['id'];

    public static function taxbyid($id) {
        return Tax::where('id', $id);
    }
}
