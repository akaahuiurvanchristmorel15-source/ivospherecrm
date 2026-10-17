<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Rendu PDF d'une vue HTML en PHP pur (dompdf) : fonctionne sans Chromium ni Node, y compris sur un hébergement mutualisé.
 */
class PdfRenderer
{
    public function fromHtml(string $html, bool $landscape = false): string
    {
        return Pdf::loadHTML($html)
            ->setPaper('a4', $landscape ? 'landscape' : 'portrait')
            ->setOption([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'dpi' => 96,
            ])
            ->output();
    }
}
