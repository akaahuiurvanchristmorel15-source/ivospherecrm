<?php

namespace App\Support;

use Spatie\Browsershot\Browsershot;

/**
 * Rendu PDF d'une vue HTML avec Chromium (Browsershot), selon les chemins de config/services.php.
 */
class PdfRenderer
{
    /**
     * @param  array{0: float, 1: float, 2: float, 3: float}  $margins  Marges en mm (haut, droite, bas, gauche)
     */
    public function fromHtml(string $html, bool $landscape = false, array $margins = [0, 0, 0, 0]): string
    {
        return $this->configure(Browsershot::html($html))
            ->format('A4')
            ->landscape($landscape)
            ->margins(...$margins)
            ->showBackground()
            ->emulateMedia('print')
            ->pdf();
    }

    private function configure(Browsershot $browsershot): Browsershot
    {
        $config = config('services.browsershot');

        if (filled($config['node_binary'] ?? null)) {
            $browsershot->setNodeBinary($config['node_binary']);
        }

        if (filled($config['npm_binary'] ?? null)) {
            $browsershot->setNpmBinary($config['npm_binary']);
        }

        if (filled($config['chrome_path'] ?? null)) {
            $browsershot->setChromePath($config['chrome_path']);
        }

        if (filled($config['node_modules_path'] ?? null)) {
            $browsershot->setNodeModulePath($config['node_modules_path']);
        }

        return $browsershot;
    }
}
