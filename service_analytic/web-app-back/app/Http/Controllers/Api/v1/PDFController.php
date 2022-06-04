<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PDFRequest;
use App\Models\OldTariff;
use App\Models\Order;
use App\Models\Tariff;
use App\Services\num2Str;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Services\CalculateService;
use App\Services\FtpService;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PDFController extends Controller
{
    private mixed $pdf;
    private mixed $invoiceInfo;
    protected FtpService $ftp;
    /**
     * @var void
     */
    public  $request;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(FtpService $ftp)
    {
        $this->pdf = \App::make('dompdf.wrapper');
        $this->pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
        $this->ftp = $ftp;
    }

    /**
     * @param  PDFRequest  $request
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function setInvoiceInfo (PDFRequest $request): void
    {
        $tariffs = $this->getTariffs($request);

        $sumTariffPrice = $request->get('total_price');

        $date = new \DateTime(now());
        $this->invoiceInfo['date'] = $date->format('d.m.Y');
        $this->invoiceInfo['orderId'] = 'U00000000'.$request->get('orderId');
        $this->invoiceInfo['period'] = $request->get('period');
        $this->invoiceInfo['company'] = json_decode(request()->get('company'));
        $str = new num2Str();
        $this->invoiceInfo['strSumTariffPrice'] = $str->num2str($sumTariffPrice);
        $this->invoiceInfo['tariffPrice'] = $sumTariffPrice;
        $this->invoiceInfo['tariffs'] = $tariffs;
    }

    /**
     * @param  PDFRequest  $request
     * @return array
     */
    public function getTariffs(PDFRequest $request ): array
    {
        $tariffs = (array)$request->get('tariff_id');
        $tariffs = is_array($tariffs) ? $tariffs : [$tariffs];

        $tariffs = Tariff::query()->whereIn('id', $tariffs)->get()->toArray();
        $result = [];
        foreach ($tariffs as $key => $tariff) {
            $result[$key]['id'] = $tariff['id'];
            $result[$key]['tariff_id'] = $tariff['id'];
            $result[$key]['name'] = $tariff['name'];
            $result[$key]['description'] = 'Удобные графики для анализа продаж и видимости товаров|Оптимизация карточек под требования маркетплейса|Поисковая SEO-оптимизация карточек|Рекомендации, разработанные специально для ваших товаров';
            $result[$key]['visible'] = $tariff['visible'];
            $result[$key]['price'] = $tariff['price']; //TODO Выяснить требуется ли даныый параметр для 1С
            $result[$key]['price_id'] = 1; //TODO Выяснить требуется ли даныый параметр для 1С
            $result[$key]['created_at'] =  $tariff['created_at'];;
            $result[$key]['updated_at'] =  $tariff['updated_at'];;
            $result[$key]['alias'] =  $tariff['alias'];;
            $result[$key]['sku'] = 0;
            $result[$key]['tariff_price'] = 0;

            $services = $request->all()['services'];
            foreach ($services as $service) {
                $service = json_decode($service);
                $result[$key]['tariff_price'] = 0;
                if ($service->alias === 'semantic') {
                    $result[$key]['sku'] = $service->amount;
                }
                $result[$key]['tariff_price'] = $request->get('begin_price_with_sku_period_promo_discount');
            }
        }

        return $result;
    }

    /**
     * Генерация PDF документа
     * @return mixed
     */
    public function generatePDF(): mixed
    {
        $userInfo = self::getInvoiceInfo();
        $pdf = \PDF::loadView('invoice', $userInfo);

        return $pdf->stream('invoice.pdf');
    }

    /**
     * Получение информации по организации
     * @return mixed
     */
    public function getInvoiceInfo(PDFRequest $request): mixed
    {
        try {
            self::setInvoiceInfo($request);
        } catch (NotFoundExceptionInterface $exception) {
            report($exception);
            return ExceptionHandlerHelper::logAndSendFailResponse($exception);
        }

        return $this->invoiceInfo;
    }

    /**
     * Загрузка PDF файла
     * @return mixed
     * @throws FileNotFoundException
     */
    public function downloadPDF(PDFRequest $request): mixed
    {
        $invoiceInfo = self::getInvoiceInfo($request);
        $pdf = \App::make('dompdf.wrapper');

        $html = view('invoice', $invoiceInfo);
        $pdf->loadHTML($html, 'UTF-8');

        //Загрузка файла на FTP
        $this->ftp->putInvoiceToFtp($invoiceInfo);

        return $pdf->download('invoice.pdf');
    }

    /**
     * Посмотреть превью инвойса
     * @return mixed
     * @throws FileNotFoundException
     */
    public function previewInvoice(PDFRequest $request): mixed
    {
        $invoiceInfo = self::getInvoiceInfo($request);
        return view('invoice', $invoiceInfo);
    }
}
