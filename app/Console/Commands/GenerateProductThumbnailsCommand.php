<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductThumbnailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateProductThumbnailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:generate-thumbnails {--force : Recrée aussi les miniatures existantes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée les miniatures (320 px) des photos d\'articles utilisées dans le stock et la caisse';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $imagePaths = Product::whereNotNull('image')->distinct()->pluck('image');
        $created = 0;
        $failed = 0;

        $this->withProgressBar($imagePaths, function (string $imagePath) use (&$created, &$failed) {
            $alreadyExists = Storage::disk('public')->exists(ProductThumbnailService::thumbnailPathFor($imagePath));
            if ($alreadyExists && ! $this->option('force')) {
                return;
            }

            ProductThumbnailService::generate($imagePath) ? $created++ : $failed++;
        });

        $this->newLine();
        $this->info("{$created} miniature(s) créée(s) sur {$imagePaths->count()} photo(s).");
        if ($failed > 0) {
            $this->warn("{$failed} photo(s) illisible(s) ou introuvable(s) : la photo d'origine reste affichée.");
        }

        return self::SUCCESS;
    }
}
