<?php

namespace App\Console\Commands;

use App\Models\WbCategory;
use App\Models\WbDirectory;
use App\Models\WbDirectoryItem;
use App\Models\WbFeature;
use App\Services\Json\JsonService;
use App\Services\Wildberries\Client;
use Carbon\Carbon;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonMachine\Items;
use PHPUnit\Exception;
use function Swoole\Coroutine\run;

class GetWildberriesDirectories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:get-directories';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получение и обновление всех значений словарей для Wildberries';

    protected $wildberriesClientId;
    protected $wildberriesApiToken;

    private $wbClient;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->wildberriesApiToken = config('env.wildberries_api_token');
        $this->wildberriesClientId = config('env.wildberries_client_id');
    }

    protected $connections = [];
    protected $chunks = 25;

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->connections = $this->openAdditionalConnection();
        $this->wbClient = new Client($this->wildberriesClientId, $this->wildberriesApiToken);
        $this->getDirectories();
        $this->updateElementsForDirectories();
        $this->downloadTnved();
        $extDirectoryId = $this->createOrUpdateWbFeatures();
        $this->downloadExtFeatures();
        $this->createDirectoriesFromFeatures($extDirectoryId);
        $this->fillExtOptionsToDb($extDirectoryId);
    }

    /**
     * Get dictionary for "ТНВЭД"
     *
     * @return void
     */
    private function downloadTnved()
    {
        foreach (WbDirectory::select()->whereIn('slug', ['/tnved'])->get() as $directory) {
            $this->info("\n\n");
            $this->info("Получаем ТНВЭД справочники");
            $bar = $this->output->createProgressBar(WbCategory::count());
            $bar->start();
            WbCategory::chunk(100, function ($categories) use ($directory, $bar) {
                run(function () use ($categories, $bar, $directory) {
                    foreach ($categories as $category) {
                        go(function () use ($directory, $bar, $category) {
                            try {
                                $response = $this->wbClient->getDirectory($directory->requestUrl, ['subject' => $category->name, 'top' => (string)pow(10, 10)], true);
                            } catch (\Exception $e) {
                                Log::channel('console')->error($e->getMessage());
                                return;
                            }

                            Storage::disk('json')->put("wildberries_tnved_$category->id.json", $response);
                            $path = Storage::disk('json')->path("wildberries_tnved_$category->id.json");
                            try {
                                $items = Items::fromFile($path, ['pointer' => '/data']);
                            } catch (\Exception $exception) {
                                return;
                            }
                            $attachedItems = [];

                            foreach ($items as $item) {
                                try {
                                    ModelHelper::transaction(function () use (&$attachedItems, $directory, $item) {
                                        if (!isset($item->tnvedCode) || !isset($item->description)) {
                                            return;
                                        }
                                        $newOrUpdatedItem = WbDirectoryItem::updateOrCreate(
                                            [
                                                'wb_directory_id' => $directory->id,
                                                'title' => $item->tnvedCode
                                            ],
                                            [
                                                'translation' => $item->tnvedCode,
                                                'data' => ['description' => $item->description]
                                            ]
                                        );
                                        $attachedItems[] = $newOrUpdatedItem->id;
                                    });
                                } catch (\Exception $exception) {
                                    continue;
                                }
                            }
                            $bar->setMessage("Получаю справочник '{$directory->title}' для категории '{$category->name}'");
                            try {
                                $category->directoryItems()->sync($attachedItems);
                            } catch (\Exception $exception) {
                                report($exception);
                                Log::channel('console')->error($exception->getMessage());
                            }
                            try {
                                Storage::disk('json')->delete("wildberries_tnved_$category->id.json");
                            } catch (\Exception $exception) {
                                report($exception);
                                Log::channel('console')->error($exception->getMessage());
                            }
                            $bar->advance();
                        });
                    }
                });
            });
            $bar->finish();
        }
    }

    /**
     *
     * @return void
     */
    private function updateElementsForDirectories()
    {
        $this->line('Начинаем получать справочники по директориям');

        $wbDirectoryQuery = WbDirectory::select()->whereNotIn('slug', ['/tnved', '/wbsizes', '/factories', '/subjects', '/ext']);

        $bar = $this->output->createProgressBar($wbDirectoryQuery->count());
        $bar->start();

        foreach ($wbDirectoryQuery->get() as $directory) {
            $directoryItemsData = [];
            $this->line(" Обновляем элементы для директории $directory->title");
            try {
                $directoryStream = $this->wbClient->getJsonStream($directory->requestUrl2 . '?top=1000000000000');
            } catch (\Exception $e) {
                Log::channel('console')->error($e->getMessage());
                continue;
            }
            $jsonService = new JsonService();
            $jsonService->saveJsonToFile('wildberries_directories', $directoryStream);
            $path = Storage::disk('json')->path('wildberries_directories.json');
            try {
                $items = Items::fromFile($path, ['pointer' => '/data']);
            } catch (\Exception $exception) {
                continue;
            }

            $counter = 0;
            foreach ($items as $item) {
                try {
                    if (isset($item->key) && isset($item->translate)) {
                        if (!in_array(Str::limit(trim(preg_replace("/\r|\n/", "", $item->key)),
                            240, '...'), array_column($directoryItemsData, 'title'))) {
                            $directoryItemsData[] = [
                                'wb_directory_id' => $directory->id,
                                'title' => Str::limit(trim(preg_replace("/\r|\n/", "", $item->key)), 250, '...'),
                                'translation' => Str::limit(trim(preg_replace("/\r|\n/", "", $item->translate)), 250, '...'),
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    report($e);
                    Log::channel('console')->error($e->getMessage());
                }
                if ($counter++ >= 500) {
                    try {
                        $this->updateDirectoryItemsData($directoryItemsData);
                    } catch (\Exception $e) {
                        report($e);
                        Log::channel('console')->error($e->getMessage());
                    } finally {
                        $counter = 0;
                        $directoryItemsData = [];
                    }
                }
            }
            $this->updateDirectoryItemsData($directoryItemsData);
            $bar->advance();
        }

        $bar->finish();

        try {
            Storage::disk('json')->delete('wildberries_directories.json');
        } catch (\Exception $exception) {
            report($exception);

        }
    }

    /**
     * Get directory list
     *
     * @throws \Exception
     */
    private function getDirectories()
    {
        $this->info(__("Get directory list and data"));
        $directories = $this->wbClient->getDirectoryList();
        if (isset($directories['data']) && !empty($directories['data'])) {
            foreach ($directories['data'] as $slug => $qty) {
                WbDirectory::updateOrCreate(
                    [
                        'title' => ucfirst(preg_replace("/[^a-zA-Z0-9]+/", "", $slug)),
                        'slug' => $slug,
                    ],
                    ['qty' => $qty]
                );
            }

        }
    }

    /**
     * @return void
     */
    private function downloadExtFeatures()
    {
        $this->alert("Начинаю скачивать в файловую систему данные по характеристикам (опциям)");
        $bar = $this->output->createProgressBar(WbFeature::count());
        $bar->start();

        WbFeature::chunk(100, function ($features) use ($bar,) {
            run(function () use ($features, $bar) {
                foreach ($features as $feature) {
                    go(function () use ($feature, $bar) {
                        try {
                            $response = $this->wbClient->getDirectoryExt($feature->title);
                        } catch (\Exception $e) {
                            Log::channel('console')->error($e->getMessage());
                            return;
                        }

                        try {
                            Storage::disk('json')->delete("wildberries_ext_$feature->id.json");
                        } catch (Exception $exception) {

                        }
                        Storage::disk('json')->put("wildberries_ext_$feature->id.json", '');
                        $path = Storage::disk('json')->path("wildberries_ext_$feature->id.json");

                        if ($response) {
                            while (!$response->eof()) {
                                $chunk = $response->read(8192);
                                File::append($path, $chunk);
                            }
                        }

                        $bar->advance();

                    });
                }
            });
        });

        $bar->finish();
        $this->info("\n\n");
    }

    /**
     *
     * @param $extDirectoryId
     * @return void
     */
    private function fillExtOptionsToDb($extDirectoryId)
    {
        $this->alert("Начинаю загружать в базу данных опции из файловой системы");
        $filesChunked = $this->getFilesForParsingExt()->chunk($this->chunks);
        $bar = $this->output->createProgressBar($this->getFilesForParsingExt()->count());
        $bar->start();

        foreach ($filesChunked as $chunk) {
            run(function () use ($extDirectoryId, $chunk, $bar) {
                foreach ($chunk as $file) {
                    $connection = $this->findFreeSocket();
                    go(function () use ($extDirectoryId, $file, $bar, $connection) {
                        $feature = DB::connection($connection)->table('wb_features')->where('id', $file)->first();
                        DB::connection($connection)->table('wb_feature_directory_items')->where('feature_id', '=', $file)->delete();

                        if (!$feature) {
                            $bar->advance();
                            return;
                        }

                        $path = Storage::disk('json')->path("wildberries_ext_$file.json");
                        try {
                            $items = Items::fromFile($path, ['pointer' => '/data']);
                        } catch (\Exception $exception) {
                            $bar->advance();
                            return;
                        }
                        if (!$items) {
                            $bar->advance();
                            return;
                        }

                        $writeFeatures = [];
                        foreach ($items as $item) {
                            if (!isset($item->key) || !preg_match("/^[a-zA-Zа-яА-Я0-9]+/", $item->key)) {
                                continue;
                            }
                            try {
                                $directoryItem = DB::connection($connection)->table('wb_directory_items')->where('title', $item->key)
                                    ->where('translation', '=', $item->translate)
                                    ->where('wb_directory_id', '=', $extDirectoryId)
                                    ->first();
                            } catch (\Exception $exception) {
                                $this->line($exception->getMessage());
                                continue;
                            }
                            if (!$directoryItem || !isset($directoryItem->id)) {
                                continue;
                            }

                            $writeFeatures[] = [
                                'item_id' => $directoryItem->id,
                                'feature_id' => $feature->id
                            ];

                            if (count($writeFeatures) >= 100) {
                                try {
                                    ModelHelper::transaction(function () use ($writeFeatures, $connection) {
                                        DB::connection($connection)->table('wb_feature_directory_items')->insertOrIgnore(
                                            $writeFeatures
                                        );
                                    }, $connection);
                                } catch (\Exception $exception) {
                                    $this->line($exception->getMessage());
                                }
                                $writeFeatures = [];
                            }
                            try {
                                ModelHelper::transaction(function () use ($writeFeatures, $connection) {
                                    DB::connection($connection)->table('wb_feature_directory_items')->insertOrIgnore(
                                        $writeFeatures
                                    );
                                }, $connection);
                            } catch (\Exception $exception) {
                                $this->line($exception->getMessage());
                            }
                        }
                        try {
                            Storage::disk('json')->delete("wildberries_ext_$feature->id.json");
                        } catch (Exception $exception) {

                        }
                        $bar->advance();
                        $this->connections[$connection] = true;
                    });
                }
            });
        }

        $bar->finish();
        $this->info("\n\n");
    }

    private function findFreeSocket(): int|string
    {
        foreach ($this->connections as $key => $value) {
            if ($value) {
                $this->connections[$key] = false;
                return $key;
            }
        }
        throw new \Exception('Not find free socket!');
    }

    private function openAdditionalConnection(): array
    {
        $array = [];
        for ($i = 0; $i < $this->chunks; $i++) {
            Config::set("database.connections.mysql_wb_$i", [
                'driver' => config('database.connections.mysql.driver'),
                "host" => config('database.connections.mysql.host'),
                "database" => config('database.connections.mysql.database'),
                "username" => config('database.connections.mysql.username'),
                "password" => config('database.connections.mysql.password'),
                "port" => config('database.connections.mysql.port'),
                'charset' => config('database.connections.mysql.charset'),
                'collation' => config('database.connections.mysql.collation'),
                'prefix' => config('database.connections.mysql.prefix'),
                'strict' => config('database.connections.mysql.strict'),
            ]);
            $array["mysql_wb_$i"] = true;
        }
        return $array;
    }

    private function createDirectoriesFromFeatures($extDirectoryId)
    {
        $this->alert("Загружаем директории из загруженных файлов, которые изначально не отдал wildberries по апи");
        $filesChunked = $this->getFilesForParsingExt()->chunk($this->chunks);
        $bar = $this->output->createProgressBar($this->getFilesForParsingExt()->count());
        $bar->start();

        foreach ($filesChunked as $chunk) {
            run(function () use ($extDirectoryId, $chunk, $bar) {
                foreach ($chunk as $file) {
                    go(function () use ($extDirectoryId, $file, $bar) {
                        $connection = $this->findFreeSocket();
                        $feature = DB::connection($connection)->table('wb_features')->where('id', $file)->first();
                        if (!$feature) {
                            $bar->advance();
                            return;
                        }
                        $path = Storage::disk('json')->path("wildberries_ext_$file.json");
                        try {
                            $items = Items::fromFile($path, ['pointer' => '/data', 'debug' => true]);
                        } catch (\Exception $exception) {
                            $bar->advance();
                            return;
                        }
                        if (!$items) {
                            $bar->advance();
                            return;
                        }
                        foreach ($items as $item) {
                            if (!isset($item->key) || !preg_match("/^[a-zA-Zа-яА-Я0-9]+/", $item->key)) {
                                continue;
                            }

                            $title = escapeRawQueryString($item->key);
                            $translate = escapeRawQueryString($item->translate);
                            $createdAt = Carbon::now()->format('Y-m-d H:i:s');
                            $writeDirectories = [
                                'wb_directory_id' => $extDirectoryId,
                                'title' => $title,
                                'translation' => $translate,
                                'created_at' => $createdAt,
                                'updated_at' => $createdAt,
                            ];
                            DB::connection($connection)->table('wb_directory_items')->insertOrIgnore($writeDirectories);
                        }
                        $bar->advance();
                        $this->connections[$connection] = true;
                    });
                }
            });
        }
        $bar->finish();
        $this->info("\n\n");
    }

    /**
     * Get all files with ext options
     * @return \Illuminate\Support\Collection
     */
    private function getFilesForParsingExt()
    {
        return collect(File::allFiles(Storage::disk('json')->path("/")))
            ->filter(function ($file) {
                return preg_match('/wildberries_ext_(\d+)\.json/', $file->getBaseName());
            })
            ->sortBy(function ($file) {
                return (int)$file->getSize();
            })
            ->map(function ($file) {
                return (int)filter_var($file->getBaseName(), FILTER_SANITIZE_NUMBER_INT);
            });
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function createOrUpdateWbFeatures(): mixed
    {
        $extDirectory = WbDirectory::firstWhere('slug', '/ext');
        $this->info("\n\n");
        $this->alert("Создаю характеристики для типов товаров из словаря /ext");

        if ($extDirectory) {
            $categories = WbCategory::select();
            $bar = $this->output->createProgressBar($categories->count());
            $bar->start();

            $categories->chunk(100, function ($categoryList) use ($extDirectory, $bar) {
                run(function () use ($extDirectory, $bar, $categoryList) {
                    foreach ($categoryList as $category) {
                        go(function () use ($extDirectory, $bar, $categoryList, $category) {
                            if (isset($category->data->addin)) {
                                foreach ($category->data->addin as $addin) {
                                    if (isset($addin->dictionary) && $addin->dictionary === $extDirectory->slug) {
                                        ModelHelper::transaction(function () use ($addin, $extDirectory) {
                                            WbFeature::updateOrCreate(
                                                ['title' => $addin->type],
                                                [
                                                    'directory_id' => $extDirectory->id,
                                                    $addin->type,
                                                ]
                                            );
                                        });
                                    }
                                }
                            }
                            $bar->advance();
                        });
                    }
                });
            });
            $bar->finish();
        } else {
            $this->error('Нет словаря с \ext');
        }
        $this->info("\n\n");
        return $extDirectory->id;
    }

    /**
     *
     * @param $directoryItemsData
     * @return void
     */
    private function updateDirectoryItemsData($directoryItemsData)
    {
        if ($directoryItemsData) {
            WbDirectoryItem::upsert(
                $directoryItemsData, ['wb_directory_id', 'title'], ['translation']
            );
        }
    }
}
