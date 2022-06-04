<?php

namespace App\Http\Controllers;

use App\Http\Requests\Download\DownloadAbuseRequest;
use App\Services\Escrow\EscrowAbuseService;
use App\Services\Escrow\EscrowPdfService;

class DownloadController
{
    /**
     * Return PDF by abuse ID
     *
     * @param DownloadAbuseRequest $request
     * @param EscrowPdfService $escrowPdfService
     * @param EscrowAbuseService $escrowAbuseService
     * @return mixed
     */
    public function abuse(
        DownloadAbuseRequest $request,
        EscrowPdfService $escrowPdfService,
        EscrowAbuseService $escrowAbuseService
    ): mixed
    {
        $abuse = $escrowAbuseService->get($request->id);
        return $escrowPdfService->view($abuse);
    }
}
