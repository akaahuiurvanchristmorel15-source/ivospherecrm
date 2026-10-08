<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Assure que chaque produit en base possède un code-barres EAN-13 valide
        Product::all()->each(function (Product $product) {
            $barcode = trim((string) $product->barcode);

            if (empty($barcode)) {
                $product->barcode = Product::generateEan13($product->id);
                $product->saveQuietly();
            } elseif (strlen($barcode) === 12 && ctype_digit($barcode)) {
                $sum = 0;
                for ($i = 0; $i < 12; $i++) {
                    $sum += (int) $barcode[$i] * ($i % 2 === 0 ? 1 : 3);
                }
                $checksum = (10 - ($sum % 10)) % 10;
                $product->barcode = $barcode.$checksum;
                $product->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        // Pas de rollback destructif nécessaire
    }
};
