<?php

namespace App\Services;

use App\Models\Product;
use App\Services\QrCode\QRCodeEncoder;
use Illuminate\Http\Response;

class QrCodeService
{
    /**
     * Génère l'image binaire PNG brute du QR code seul.
     */
    public function generatePng(string $data, int $scale = 14, int $margin = 4): string
    {
        $encoder = new QRCodeEncoder($data, ['s' => 'qrm']);
        $matrixData = $encoder->getMatrix();
        $matrix = $matrixData['b'];
        $size = $matrixData['s'][0];

        $w = ($size + $margin * 2) * $scale;
        $h = $w;

        $img = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($img, 255, 255, 255);
        $dark = imagecolorallocate($img, 11, 15, 20); // #0B0F14

        imagefilledrectangle($img, 0, 0, $w, $h, $white);

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if (! empty($matrix[$y][$x])) {
                    $x1 = ($x + $margin) * $scale;
                    $y1 = ($y + $margin) * $scale;
                    $x2 = $x1 + $scale - 1;
                    $y2 = $y1 + $scale - 1;
                    imagefilledrectangle($img, $x1, $y1, $x2, $y2, $dark);
                }
            }
        }

        ob_start();
        imagepng($img, null, 9);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
    }

    /**
     * Génère le flux SVG du QR code.
     */
    public function generateSvg(string $data): string
    {
        $encoder = new QRCodeEncoder($data, ['s' => 'qrm']);

        return $encoder->createSVG();
    }

    /**
     * Génère une étiquette article commerciale & stock complète en PNG haute résolution (600x750 px).
     */
    public function generateProductLabelPng(Product $product): string
    {
        $ean = $product->ean;
        $formattedEan = $product->formatted_ean;
        $sku = $product->sku;
        $name = $product->name;
        $price = number_format((float) $product->selling_price, 0, ',', ' ').' FCFA';
        $domain = strtoupper($product->domain?->name ?? 'GÉNÉRAL');

        $w = 600;
        $h = 750;

        $img = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($img, 255, 255, 255);
        $bgLight = imagecolorallocate($img, 248, 250, 252); // #F8FAFC
        $primary = imagecolorallocate($img, 0, 102, 255); // #0066FF
        $dark = imagecolorallocate($img, 11, 15, 20); // #0B0F14
        $slate = imagecolorallocate($img, 100, 116, 139); // #64748B
        $borderCol = imagecolorallocate($img, 226, 232, 240); // #E2E8F0
        $green = imagecolorallocate($img, 16, 149, 106); // Emerald

        // Fond blanc principal
        imagefilledrectangle($img, 0, 0, $w, $h, $white);

        // Cadre extérieur
        imagerectangle($img, 10, 10, $w - 11, $h - 11, $borderCol);
        imagerectangle($img, 11, 11, $w - 12, $h - 12, $borderCol);

        // Bandeau d'en-tête IVOSPHERE
        imagefilledrectangle($img, 12, 12, $w - 13, 80, $bgLight);
        imagefilledrectangle($img, 12, 78, $w - 13, 81, $primary);

        // En-tête : Logo texte
        imagestring($img, 5, 30, 26, 'IVOSPHERE ERP', $primary);
        imagestring($img, 3, 30, 48, 'POLE COMMERCIAL & LOGISTIQUE - '.$domain, $slate);
        imagestring($img, 2, $w - 150, 36, 'REF: '.$sku, $dark);

        // Titre produit
        $shortName = mb_substr($name, 0, 38);
        imagestring($img, 5, 30, 105, strtoupper($shortName), $dark);
        imagestring($img, 3, 30, 130, 'SKU : '.$sku.'  |  UNITE : '.strtoupper($product->unit ?? 'PIECE'), $slate);

        // Générer le QR Code central (taille ~300x300)
        $qrRaw = $this->generatePng($ean, 10, 2);
        $qrResource = imagecreatefromstring($qrRaw);
        if ($qrResource) {
            $qrW = imagesx($qrResource);
            $qrH = imagesy($qrResource);
            $qrTargetW = 320;
            $qrTargetH = 320;
            $qrX = (int) (($w - $qrTargetW) / 2);
            $qrY = 175;

            // Fond blanc avec bordure pour le QR
            imagefilledrectangle($img, $qrX - 8, $qrY - 8, $qrX + $qrTargetW + 7, $qrY + $qrTargetH + 7, $bgLight);
            imagerectangle($img, $qrX - 8, $qrY - 8, $qrX + $qrTargetW + 7, $qrY + $qrTargetH + 7, $borderCol);

            imagecopyresampled($img, $qrResource, $qrX, $qrY, 0, 0, $qrTargetW, $qrTargetH, $qrW, $qrH);
            imagedestroy($qrResource);
        }

        // Section Code-barres EAN
        $eanText = 'EAN-13 : '.$formattedEan;
        $eanX = (int) (($w - (strlen($eanText) * 9)) / 2);
        imagefilledrectangle($img, 40, 520, $w - 41, 555, $bgLight);
        imagestring($img, 4, $eanX, 530, $eanText, $dark);

        // Section Prix de vente
        $priceLabel = 'PRIX DE VENTE : '.$price;
        $priceX = (int) (($w - (strlen($priceLabel) * 9)) / 2);
        imagestring($img, 5, $priceX, 580, $priceLabel, $primary);

        // Séparateur discret
        imageline($img, 30, 630, $w - 30, 630, $borderCol);

        // Pied de page
        imagestring($img, 2, 30, 650, 'Scannable via douchette & terminal mobile IVOSPHERE', $slate);
        imagestring($img, 2, 30, 672, 'Code produit unique integre aux ventes POS, devis et stocks.', $slate);
        imagestring($img, 2, $w - 180, 660, date('d/m/Y H:i'), $slate);

        ob_start();
        imagepng($img, null, 9);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
    }

    /**
     * Retourne une réponse de téléchargement HTTP directe pour le fichier PNG.
     */
    public function downloadResponse(Product $product, bool $asLabel = false): Response
    {
        $ean = $product->ean;
        $sku = $product->sku;

        if ($asLabel) {
            $pngContent = $this->generateProductLabelPng($product);
            $filename = "etiquette-produit-{$sku}-ean-{$ean}.png";
        } else {
            $pngContent = $this->generatePng($ean, 16, 4);
            $filename = "qr-code-{$sku}-ean-{$ean}.png";
        }

        return response($pngContent)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Cache-Control', 'private, max-age=3600')
            ->header('Content-Length', (string) strlen($pngContent));
    }
}
