<?php

namespace App\Console\Commands\Sandbox;

use App\Contracts\Services\FileUploadInterface;
use App\Models\OzProduct;
use AnalyticPlatform\LaravelHelpers\Helpers\ExceptionHandlerHelper;
use AnalyticPlatform\LaravelHelpers\Helpers\ModelHelper;
use Illuminate\Console\Command;

class UploadDataToModelFromFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload-file:to-model
                                        {filepath : The local file path on disk local, for data import.}
                                        {model : Full model name ( App\Models\Brand ).}
                                        {--field-fill : Mapping field on importing file.}
                                        {--file-type : Typing file, csv, xslx. Check handler file in services before usage.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт данных в модель из файла, можно задать структуру
                              которая будет синхронизировать файл и модель по полям в параметре field-fill, где key поле файла а value поле модели.
                               Если оставить поле пустым то возьмутся названия полей из 1 строки файла в нижнем регистре и сопоставятся с моделью.
                               Также нужно учитывать какие есть fillable поля в модели.';

    /**
     * @var mixed
     */
    private mixed $fileService;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info(sprintf('Импортируем файл %s', $this->argument('filepath')));

        try {
            $fileType = ($this->option('file-type') !== false) ? $this->option('file-type') : 'csv';
            $fileCLass = config('file.uploader.import_class.' . $fileType);
            $this->fileService = new $fileCLass;

            if (!in_array(FileUploadInterface::class, class_implements($this->fileService::class))) {
                $this->info(
                    sprintf('Interface FileUploadInterface not implemented in %s.', $this->fileService::class)
                );

                return self::FAILURE;
            }
            $modelClassName = $this->argument('model');

            $structuredData = $this->getImportStructuredData(
                $this->fileService->getFileDataToArray($this->argument('filepath'))
            );

            $this->info(sprintf('Импортируем  %d элементов', count($structuredData)));

            ModelHelper::transaction(function () use ($modelClassName, $structuredData) {
                foreach ($structuredData as $item) {
                    call_user_func($modelClassName . '::create', $item);
                }
            });

        } catch (\Exception $exception) {
            report($exception);
            ExceptionHandlerHelper::logFail($exception);
        }

        $this->info(sprintf('Импорт завершен.'));

        return self::SUCCESS;
    }

    /**
     * @param  array  $data
     * @return array
     */
    protected function getImportStructuredData(array $data)
    {
        $readyData = [];
        $arrayFill = $this->option('field-fill');

        if ($arrayFill) {
            foreach ($data as $dataItem) {
                $mappedItem = [];

                foreach ($arrayFill as $key => $item) {
                    $mappedItem[$item] = $dataItem[$key];
                }

                $readyData[] = $mappedItem;
            }

            return $readyData;
        }

        foreach ($data as $dataItem) {
            $mappedItem = [];
            foreach ($dataItem as $itemKey => $itemValue) {
                $mappedItem[strtolower($itemKey)] = $itemValue;
            }

            $readyData[] = $mappedItem;
        }

        return $readyData;
    }
}
