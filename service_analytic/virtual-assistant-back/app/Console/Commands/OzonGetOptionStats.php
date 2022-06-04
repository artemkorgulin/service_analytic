<?php

namespace App\Console\Commands;

use App\Mail\AdminOzonGetOptionStat;
use App\Models\OzOptionStat;
use App\Models\OzOptionStatSummary;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class OzonGetOptionStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:get-option-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get statistics on Ozon requests';

    /**
     * Каталог
     * @var string
     */
    protected $cat = 'features_from_parsing';

    /** @var int chunk size */
    protected int $chunkSize = 1000;

    /** @var int step iterator */
    protected int $i = 0;

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
        $this->alert('I get the latest version of files for parsing');
        $time_start = microtime(true);
        $changeStat = [];
        array_filter(Storage::disk('ftp')->files($this->cat), function ($item) use (&$changeStat) {
            if (!strpos($item, '.csv')) {
                return;
            }
            if (OzOptionStat::firstWhere('filename', $item)) {
                return;
            }
            $stat = OzOptionStat::create([
                'name' => 'Export from ' . $item,
                'filename' => $item,
                'comment' => 'Task add from console at ' . date('Y-m-d H:i:s', time()),
            ]);

            $fileStream = Storage::disk('ftp')->readStream($item);
            $csv = Reader::createFromStream($fileStream)->setDelimiter(';')->setHeaderOffset(0);
            $this->warn('Perform file: ' . $item);
            $r = '';
            foreach ($csv->getRecords() as $record) {
                if ($r !== $record['Ключевой запрос']) {
                    $this->info('Perform request: ' . $record['Ключевой запрос']);
                    $r = $record['Ключевой запрос'];
                }
                try {
                    $stat->items()->create([
                        'category' => $record['Категория'],
                        'key_request' => mb_substr($record['Ключевой запрос'], 0, 250),
                        'search_response' => mb_substr($record['Поисковая выдача'], 0, 250),
                        'popularity' => (int)$record['Популярность запроса'],
                        'add_to_cart' => (int)$record['Добавления в корзину'],
                        'conversion' => floatval(str_replace(',', '.', str_replace('.', '', $record['Конверсия в корзину']))),
                        'average_price' => floatval(str_replace(',', '.', str_replace('.', '', $record['Средняя стоимость']))),
                        'request_without_results' => $record['Запросы без результата'],
                        'share_of_request_without_results' => floatval(str_replace(',', '.', str_replace('.', '', $record['Доля запросов без результата']))),
                        'share_of_request_with_same_results' => floatval(str_replace(',', '.', str_replace('.', '', $record['Доля запросов с похожими результатами']))),
                        'search_date' => $record['Дата поиска'],
                        'parsing_datetime' => $record['Дата/время парсинга'],
                    ]);
                } catch (\Exception $exception) {
                    report($exception);
                    ExceptionHandlerHelper::logFail($exception);
                }
            }
            // We insert into the table of summary queries
            $stat->createSummary();
        });

        $this->alert("Starting to update options in the main table with product characteristics");
        OzOptionStatSummary::updateAllOptionPopularity();

        $time_end = microtime(true);
        $execution_time = round($time_end - $time_start, 0);

        // We send mail if only there is a user
        if (env('MAIL_FOR_CONTROL', '')) {
            Mail::to(env('MAIL_FOR_CONTROL'))
                ->queue(new AdminOzonGetOptionStat($changeStat));
        }

        $this->warn("\n\n" . 'Full script execution time: ' . $execution_time . ' seconds ');

        return 0;
    }
}
