<?php

namespace App\Services\Ozon;

use App\Models\OzonProxy;
use App\Services\Interfaces\Ozon\OzonParsingServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;
use \GuzzleHttp\Cookie\CookieJar;

class OzonParsingService implements OzonParsingServiceInterface
{
    private CookieJar $checkAgeCookie;

    public function parseOzonCategory($url, $proxy)
    {
        $client = new Client();
        $cookie = $this->checkAgeCookie ?? $this->checkAgeCaptcha();
        try {
            $response = $client->request('GET', $url, [
                'cookies' => $cookie,
                "proxy" => "http://$proxy->login:$proxy->password@$proxy->ip:$proxy->port_https",
            ]);

            $html = $response->getBody()->getContents();

            $crawler = new Crawler($html);
            $texts = $crawler->filter('.container > div > div')->filter('ol > li')->each(function (Crawler $node, $i) {
                return $node->text();
            });

            unset($texts[sizeof($texts) - 1]);
            return implode('/', $texts);
        } catch (\Exception $exception) {
            report($exception);
            return null;
        }
    }

    /**
     * @return CookieJar
     */
    public function checkAgeCaptcha(): CookieJar
    {
        $request = [
            "link" => "/",
            'birthdate' => '2000-' . mt_rand(10, 12) . '-' . mt_rand(10, 25),
        ];
        $checkAgeUrl = 'https://www.ozon.ru/api/composer-api.bx/_action/setBirthdate';

        $response = Http::post($checkAgeUrl, $request);
        $this->checkAgeCookie = $response->cookies();

        return $this->checkAgeCookie;
    }

    public function updateProductCategory($id, $category)
    {
        if (!empty($category)) {
            DB::table('oz_products')->where('id', $id)->update(['real_category' => $category]);
        }
    }

    public function getRandomProxy()
    {
        return OzonProxy::query()->inRandomOrder()->first();
    }

    public function createCategoryForProduct($product)
    {
        $category = $this->parseOzonCategory($product->url, $this->getRandomProxy());
        $this->updateProductCategory($product->id, $category);
    }
}
