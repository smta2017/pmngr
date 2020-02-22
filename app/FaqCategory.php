<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FaqCategory extends Model
{
    protected $table = 'faq_categories';

    public function faqs()
    {
        return $this->hasMany(Faq::class, 'faq_category_id', 'id');
    }
}
