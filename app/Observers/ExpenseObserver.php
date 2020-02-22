<?php

namespace App\Observers;

use App\Expense;
use Illuminate\Support\Facades\File;

class ExpenseObserver
{

    public function saving(Expense $expense)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $expense->company_id = company()->id;
        }
    }

    public function deleting(Expense $expense)
    {
        File::delete('user-uploads/expense-invoice/' . $expense->bill);
    }

    public function updating(Expense $expense)
    {
        $original = $expense->getOriginal();
        if ($expense->isDirty('bill')) {
            File::delete('user-uploads/expense-invoice/' . $original['bill']);
        }
    }
}
