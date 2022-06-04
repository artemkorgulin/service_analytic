<?php

namespace App\Console\Commands;

use App\Models\PlatfomSemantic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RemoveBrandsFromCharacteristics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:brand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удалить бренды из характеристик';

    /**
     * @var int $cnt
     */
    public static int $cnt = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Удалить бренды из характеристик');

        $handle = Storage::disk('ftp')->readStream('/ozon/brand.csv');

        $bar = $this->output->createProgressBar(1000);
        $this->info(date('Y-m-d H:i:s'));
        $bar->start();

        while (($data = fgetcsv($handle)) !== false) {
            self::clearKeyRequest($data[0]);
            $bar->advance();
        }

        $this->info("");
        $this->info(date('Y-m-d H:i:s'));
        $bar->finish();
        $this->info('Удаление  брендов из характеристик успешно завершено');

        return 0;
    }

    public function clearKeyRequest(string $brand): void
    {

        $platformSemantics = PlatfomSemantic::query()
            ->select('id', 'key_request')
            ->where('key_request', 'LIKE', '%' . $brand . '%')
            ->cursor();

        foreach ($platformSemantics as $key => $platformSemantic) {
            if (!Str::contains($platformSemantic->key_request, $brand)) {
                continue;
            }
            $platformSemantic->key_request = Str::of($platformSemantic->key_request)->replace($brand, '');
            $platformSemantic->save();
        }
    }}
