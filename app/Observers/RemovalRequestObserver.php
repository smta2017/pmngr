<?php

namespace App\Observers;


use App\RemovalRequest;

class RemovalRequestObserver
{

    public function saving(RemovalRequest $removalRequest)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $removalRequest->company_id = company()->id;
        }
    }

}
