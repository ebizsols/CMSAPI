<?php

namespace App\Observers;

use App\Ticket;
use App\UniversalSearch;

class TicketObserver
{

    public function saving(Ticket $ticket)
    {
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if (company()) {
            $ticket->company_id = company()->id;
        }
    }

    public function deleting(Ticket $ticket){
        $universalSearches = UniversalSearch::where('searchable_id', $ticket->id)->where('module_type', 'ticket')->get();
        if ($universalSearches){
            foreach ($universalSearches as $universalSearch){
                UniversalSearch::destroy($universalSearch->id);
            }
        }
    }

}
