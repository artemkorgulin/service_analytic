<?php

namespace App\Services\Escrow;

use App\Models\EscrowAbuse;
use Barryvdh\DomPDF\Facade as PDF;

class EscrowPdfService
{
    /**
     * Return PDF-file
     *
     * @param  EscrowAbuse  $abuse
     *
     * @return mixed
     */
    public function view(EscrowAbuse $abuse): mixed
    {
        if (empty($abuse)) {
            return null;
        }
        return PDF::loadView('pdf.abuse', $abuse->data)->stream("abuse_{$abuse->product_id}.pdf");
    }
}
