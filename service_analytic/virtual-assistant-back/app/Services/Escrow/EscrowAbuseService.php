<?php

namespace App\Services\Escrow;

use App\Models\EscrowAbuse;
use App\Services\Download\Routes\DownloadRoutes;

class EscrowAbuseService
{
    /**
     * Get abuse by ID
     *
     * @param int $id
     * @return EscrowAbuse
     */
    public function get(int $id): EscrowAbuse
    {
        return EscrowAbuse::find($id);
    }

    /**
     * Store new abuse
     *
     * @param array $data
     * @return EscrowAbuse
     */
    public function store(array $data): EscrowAbuse
    {
        $abuse = EscrowAbuse::firstOrNew([
            'product_id' => $data['product_id'],
            'site' => $data['site'],
            'self_product_link' => $data['self_product_link'],
            'another_product_link' => $data['another_product_link'],
            'certificate_link' => $data['certificate_link'],
            'data' => $data
        ]);
        $abuse->save();
        return $abuse;
    }

    /**
     * Return link to PDF
     *
     * @param int $id
     * @return string
     */
    public function link(int $id): string
    {
        return str_replace(
            request()->getSchemeAndHttpHost(),
            '/vp',
            route(DownloadRoutes::ROUTE_ABUSE_PDF, $id)
        );
    }
}
