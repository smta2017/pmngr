<?php

namespace App\Observers;

use App\Contract;

class ContractObserver
{
    public function saving(Contract $contract)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $contract->company_id = company()->id;
        }
    }
}
