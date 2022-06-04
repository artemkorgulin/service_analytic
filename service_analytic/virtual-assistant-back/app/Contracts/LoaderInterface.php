<?php
namespace App\Contracts;

use Illuminate\Support\MessageBag;

/**
 * Interface LoaderInterface
 *
 * Предоставляет интерфейс загрузчика данных из файла
 *
 * @todo - по идее надо бы переделать
 * @package App\Contracts
 */
interface LoaderInterface
{
    /**
     * Загружает данные из файла
     *
     * @param string $filePath - путь к файлу
     * @param bool   $containsTitles - содержит ли заголовки
     * @return int
     */
    public function load(string $filePath, bool  $containsTitles): int;

    /**
     * Получить ошибки загрузчика в виде MessageBag
     *
     * @return MessageBag
     */
    public function getErrors(): MessageBag;
}
