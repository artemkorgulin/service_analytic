<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Integration1cController extends Controller
{
    private $directory;

    /**
     *
     */
    public function  __construct()
    {
        $this->directory = "/";
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Создать файл
     * @param $name
     */
    public function makeFile($name)
    {
        //
    }

    /**
     * Загрузить файл
     * @param $file
     */
    public function putFile($file)
    {
        $faker = \Faker\Factory::create('ru_RU');
        $date = new \DateTime(now());
        $date = $date->format('Y-m-d');

        $objData = new \stdClass();

        $objData->id = 15;
        $objData->date = $date;
        $objData->inn =  $faker->unique()->numberBetween(6434451568, 7785067377);
        $objData->kpp = $faker->unique()->numerify('#########');
        $objData->invoice = 'U_1000';
        $objData->amount = 1990; //$faker->unique()->numerify('####');
        $objData->description = 'Оптимизация контента карточек товара, отслеживание рейтинга и динамики позиций в категории 30 SKU'; //$faker->randomElement(['тариф1', 'тариф2', 'тариф3', 'тариф4', 'тариф5']);
        $objData->name = 'Оплата за  ' .$objData->description;

        $content = json_encode($objData, JSON_UNESCAPED_UNICODE,);
        Storage::disk('ftp')->put('/output/invoice/' . $objData->invoice . '_'. $date .'.json', $content, 'public');
        $files = Storage::disk('ftp')->directories('/output/invoice/');
        dump($files);
        dump('/output/invoice/' . $objData->invoice . '_'. $date .'.json');

        $file = Storage::disk('ftp')->get('/output/invoice/' . $objData->invoice . '_'. $date .'.json');
        dd($file);
    }

    /**
     * Получить файл
     * @param $file
     */
    public function getFile($file)
    {
        //
    }

    /**
     * Загрузить файл
     * @param $file
     */
    public function downloadFile($file)
    {

    }

    /**
     * @param $file
     */
    public function moveFile($file)
    {
        //
    }

    /**
     * @param $file
     */
    public function removeFile($file)
    {
        $files = Storage::disk('ftp')->deleteDirectory('/new');
    }

    /**
     * Создание директории в FTP хранилище
     * /output/invoice
     * @param $directory
     */
    public function makeDirectory($directory)
    {
        //$files = Storage::disk('ftp')->makeDirectory('/output/invoice');
        Storage::disk('ftp')->makeDirectory($directory);
    }

    /**
     * Прочитать содержимое директории
     * @param $directory
     */
    public function readDirectory($directory)
    {
        // /output/
        $files = Storage::disk('ftp')->directories($directory);

        return $files;
    }

    /**
    * Удалить директорию
    * @param $directory
    */
    public function removeDirectory($directory)
    {
        Storage::disk('ftp')->deleteDirectory($directory);

    }
}
