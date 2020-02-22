<?php

namespace App\Observers;

use App\RemovalRequestLead;

class RemovalRequestLeadObserver
{

    public function saving(RemovalRequestLead $removalRequestLead)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $removalRequestLead->company_id = company()->id;
        }
    }

}
