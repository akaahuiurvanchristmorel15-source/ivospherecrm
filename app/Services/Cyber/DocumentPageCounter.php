<?php

namespace App\Services\Cyber;

use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

/**
 * Compte les pages d'un document déposé au cyber (PDF, Word, Excel), sans dépendance externe.
 *
 * - PDF : lecture de l'arbre des pages (y compris dans les flux compressés) ;
 * - Word / Excel : conversion en PDF par LibreOffice quand il est disponible (résultat exact),
 *   sinon nombre de pages enregistré par Word ou estimation à partir des lignes Excel.
 */
class DocumentPageCounter
{
    /**
     * Lignes Excel imprimées par page A4 portrait (estimation sans LibreOffice).
     */
    private const EXCEL_ROWS_PER_PAGE = 50;

    /**
     * @return array{pages: ?int, estimated: bool, method: string}
     */
    public function count(string $path, string $extension): array
    {
        $extension = strtolower($extension);

        try {
            if ($extension === 'pdf') {
                return $this->result($this->countPdfPages((string) file_get_contents($path)), false, 'pdf');
            }

            $converted = $this->countWithLibreOffice($path);
            if ($converted !== null) {
                return $this->result($converted, false, 'libreoffice');
            }

            return match ($extension) {
                'docx' => $this->result($this->countDocxPages($path), false, 'word'),
                'xlsx' => $this->result($this->estimateXlsxPages($path), true, 'excel'),
                default => $this->result(null, false, 'inconnu'),
            };
        } catch (Throwable $exception) {
            report($exception);

            return $this->result(null, false, 'inconnu');
        }
    }

    /**
     * Nombre de pages d'un PDF : la plus grande valeur /Count des nœuds /Pages (la racine les contient toutes).
     */
    public function countPdfPages(string $content): ?int
    {
        $count = $this->maxPagesCount($content);

        if ($count === null) {
            // PDF 1.5+ : l'arbre des pages peut être rangé dans des flux d'objets compressés
            preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $content, $streams);
            foreach ($streams[1] as $stream) {
                $decoded = @gzuncompress($stream);
                if ($decoded === false) {
                    $decoded = @gzinflate(substr($stream, 2));
                }
                if (is_string($decoded) && ($found = $this->maxPagesCount($decoded)) !== null) {
                    $count = max($count ?? 0, $found);
                }
            }
        }

        if ($count === null && preg_match_all('/\/Type\s*\/Page(?![a-zA-Z])/', $content, $pages) > 0) {
            $count = count($pages[0]);
        }

        return $count !== null && $count > 0 ? $count : null;
    }

    /**
     * Nombre de pages enregistré par Word dans docProps/app.xml.
     */
    public function countDocxPages(string $path): ?int
    {
        $xml = $this->zipEntry($path, 'docProps/app.xml');

        return $xml !== null && preg_match('/<Pages>(\d+)<\/Pages>/', $xml, $match) ? max(1, (int) $match[1]) : null;
    }

    /**
     * Estimation Excel : lignes utilisées de chaque feuille, à raison de EXCEL_ROWS_PER_PAGE lignes par page.
     */
    public function estimateXlsxPages(string $path): ?int
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            return null;
        }

        $pages = 0;
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = (string) $zip->getNameIndex($index);
            if (! preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                continue;
            }

            $sheet = (string) $zip->getFromIndex($index);
            $rows = preg_match('/<dimension ref="[A-Z]+\d+(?::[A-Z]+(\d+))?"/', $sheet, $match) ? (int) ($match[1] ?? 1) : substr_count($sheet, '<row ');
            if ($rows > 0 && str_contains($sheet, '<c ')) {
                $pages += (int) ceil($rows / self::EXCEL_ROWS_PER_PAGE);
            }
        }
        $zip->close();

        return $pages > 0 ? $pages : null;
    }

    /**
     * Conversion en PDF par LibreOffice (si installé et autorisé sur le serveur), puis comptage exact.
     */
    private function countWithLibreOffice(string $path): ?int
    {
        $binary = $this->libreOfficeBinary();
        if ($binary === null) {
            return null;
        }

        $outputDirectory = sys_get_temp_dir().'/cyber-'.bin2hex(random_bytes(6));
        @mkdir($outputDirectory, 0700, true);

        try {
            $process = new Process([
                $binary, '--headless', '--norestore', '-env:UserInstallation=file://'.$outputDirectory.'/profile',
                '--convert-to', 'pdf', '--outdir', $outputDirectory, $path,
            ]);
            $process->setTimeout(90)->run();

            $pdf = glob($outputDirectory.'/*.pdf')[0] ?? null;

            return $pdf ? $this->countPdfPages((string) file_get_contents($pdf)) : null;
        } finally {
            $this->deleteDirectory($outputDirectory);
        }
    }

    private function libreOfficeBinary(): ?string
    {
        $configured = config('services.libreoffice.binary');
        if ($configured === false || ! function_exists('proc_open')) {
            return null;
        }

        foreach (array_filter([$configured, '/usr/bin/soffice', '/usr/bin/libreoffice', '/usr/local/bin/soffice']) as $candidate) {
            if (is_string($candidate) && @is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function maxPagesCount(string $content): ?int
    {
        preg_match_all('/\/Type\s*\/Pages\b[^>]*?\/Count\s+(\d+)|\/Count\s+(\d+)[^>]*?\/Type\s*\/Pages\b/s', $content, $matches);
        $counts = array_map('intval', array_filter([...$matches[1], ...$matches[2]], fn ($value) => $value !== ''));

        return $counts === [] ? null : max($counts);
    }

    private function zipEntry(string $path, string $entry): ?string
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            return null;
        }
        $content = $zip->getFromName($entry);
        $zip->close();

        return $content === false ? null : $content;
    }

    /**
     * @return array{pages: ?int, estimated: bool, method: string}
     */
    private function result(?int $pages, bool $estimated, string $method): array
    {
        return ['pages' => $pages, 'estimated' => $pages !== null && $estimated, 'method' => $method];
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($directory);
    }
}
