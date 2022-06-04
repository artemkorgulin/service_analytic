<?php

namespace App\Services;

use App\Models\TariffActivity;
use App\Models\User;
use Illuminate\Support\Facades\App;
use App\Services\V2\PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use App\Models\OldTariff;

class FtpService
{
    private string $disk;
    private string $prefix;

    public function __construct($disk = null)
    {
        $this->disk = $disk ?? 'ftp';
        $this->prefix = App::environment() != 'production' ? '/'. App::environment() : '';
    }

    /**
     * @param $userInfo
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function putInvoiceToFtp($userInfo): void
    {
        $date = new \DateTime(now());
        $date = $date->format('Y-m-d');
        $content = json_encode($userInfo, JSON_UNESCAPED_UNICODE);

        Storage::disk($this->disk)->put($this->prefix . '/input/invoice/U00000000'.$userInfo['orderId'].'_'.$date.'.json',
            $content);
    }

    /**
     * @param  Order  $order
     * @throws \Exception
     */
    public function putCardToFtp(Order $order): void
    {
        $sumTariffPrice = 0;
        $tariffs = self::getTariffs($order);

        foreach ($tariffs as $key => $tariff) {
            $tariffs[$key]['tariff_price'] = CalculateService::calculatePriceTariffsByDiscount(
                [$tariff['id']],
                $order->period,
                $order->promocodeUser
            );
            $sumTariffPrice += $tariffs[$key]['tariff_price'];
        }

        $str = new num2Str();
        $user = User::find($order->user_id);
        $this->userInfo['userId'] = $user->id;
        $this->userInfo['userEmail'] = $user->email;
        $date = new \DateTime(now());
        $this->userInfo['date'] = $date->format('d.m.Y');
        $this->userInfo['orderId'] = 'U00000000'.$order->id;
        $this->userInfo['period'] = $order->period;
        $this->userInfo['strSumTariffPrice'] = $str->num2str($sumTariffPrice);
        $this->userInfo['tariffPrice'] = $sumTariffPrice;
        $this->userInfo['tariffs'] = $tariffs;

        $content = json_encode($this->userInfo, JSON_UNESCAPED_UNICODE,);

        $file = Storage::disk($this->disk)->put($this->prefix . '/input/card/U00000000'.$order->id.'_'.$this->userInfo['date'].'.json',
            $content);
    }

    public function loadBankDirectory()
    {
        // Todo  Перемещать обработаныефайлы в архив
        $orders = [];
        $files = Storage::disk($this->disk)->files('/output/invoice');
        foreach ($files as $file) {
            $content = Storage::disk('ftp')->get($file);
            $data = json_decode($content, true);
            $orderId = isset($data['orderId']) ? substr($data['orderId'], -4) : null;
            $amount = $data['summa'] ?? null;
            $status = $data['status'] ?? null;

            $order = Order::whereId($orderId) // 1003 local, 1184 dev, 1046 test
            ->where('status', '=', 'pending')
                ->where('amount', '=', $amount)
                ->first();

            if ($order && $status === 'active') {

                $user = User::whereId($order->user_id)->first();

                (new PaymentService($user, $order))->updateOrderInBank();

                $orders[] = $orderId;
            }
        }

        return $orders;
    }

    /**
     * @param  string  $directory
     * @return array
     */
    public function gatAllDirectories(string $directory): array
    {
        return Storage::disk($this->disk)->allDirectories($directory);
    }

    /**
     * @param  string  $source
     * @param  string  $destination
     * @param  string|null $except
     */
    public function copyDirectory(string $source, string $destination, ?string $except = null): void
    {
        if ($files = Storage::disk($this->disk)->files($source)) {
            foreach ($files as $file) {
                self:: copyFile($file, $destination);
            }
        }
        if ($directories = Storage::disk('ftp')->directories($source)) {
            foreach ($directories as $directory) {
                if ($except && $directory == $except) {
                    continue;
                }

                self:: copyDirectory($directory, $destination);
            }
        }
    }

    /**
     * @param  string  $source
     * @param  string  $destination
     */
    public function copyFile(string $source, string $destination): void
    {
        if (!Storage::disk($this->disk)->exists($destination.$source)) {
            Storage::disk($this->disk)->copy($source, $destination.$source);
        }
    }

    /**
     * @param $directory string
     */
    public function clearDirectory(string $directory): void
    {
        $files = Storage::disk($this->disk)->files($directory);
        Storage::disk($this->disk)->delete($files);
    }

    /**
     * @param $directory string
     * @param $except string|null
     */
    public function clearAllDirectory(string $directory, ?string $except = null): void
    {
        $directories = self::gatAllDirectories($directory);

        foreach ($directories as $directory) {
            if ($except && $directory == $except) {
                continue;
            }

            $files = Storage::disk($this->disk)->files($directory);
            Storage::disk($this->disk)->delete($files);
        }
    }

    /**
     * @param $directory string
     */
    public function deleteDirectory(string $directory): void
    {
        Storage::disk($this->disk)->deleteDirectory($directory);
    }

    public function getTariffs($order)
    {
        $idstariffActivitys = TariffActivity::select('tariff_id')
            ->where('order_id', '=', $order->id)
            ->groupBy('tariff_id')->get();

        return OldTariff::whereIn('id', $idstariffActivitys)->get()->toArray();
    }

    /**
     * @param  string  $name
     * @return \Generator
     * @throws \Exception
     */
    public function getRows(string $name, $connection): \Generator
    {
        $handle = Storage::disk($connection)->readStream($name);
        if (!$handle) {
            throw new \Exception();
        }
        while (!feof($handle)) {
            yield fgets($handle);
        }
        fclose($handle);
    }
}
