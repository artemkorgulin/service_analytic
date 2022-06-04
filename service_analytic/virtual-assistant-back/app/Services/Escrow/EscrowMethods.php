<?php

namespace App\Services\Escrow;

use _PHPStan_e04cc8dfb\Nette\Neon\Exception;
use App\Models\EscrowCertificate;
use App\Models\EscrowHash;
use App\Models\EscrowHistory;
use App\Models\OzProduct;
use App\Models\WbProduct;
use App\Repositories\Wildberries\WildberriesRepository;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class EscrowMethods
{
    const DEFAULT_ESCROW_LIMIT = 3;

    private $iregStorage;

    private $idataImages = [];

    public function __construct()
    {
        $this->iregStorage = config('env.ireg_storage');
    }

    /**
     * Get escrow limit
     * @return int
     */
    public function getEscrowLimit(): int
    {
        $limit = UserService::getEscrowSku();
        return $limit ?? self::DEFAULT_ESCROW_LIMIT;
    }

    /**
     * Get remain escrows limit for current user
     *
     * @return int
     */
    public function getRemainEscrowLimit(): int
    {
        $limit = $this->getEscrowLimit() - EscrowHistory::countForAllUserAccounts();
        if ($limit < 0) {
            $limit = 0;
        }
        return intval($limit);
    }


    /**
     * Get check supplier
     *
     * @return string
     */
    private function getCheckSupplier() : string
    {

        $providerOzon = UserService::getCurrentAccountOzonId();
        $providerWB = UserService::getCurrentAccountWildberriesId();
        if ($providerOzon>0 && !$providerWB) {
            return "Ozon";
        } elseif ($providerWB>0 && !$providerOzon) {
            return "Wildberries";
        }
    }
    
    /**
     * Get check images
     * @param $product_id
     * @return array
     */
    private function checkImages($product_id) : array
    {

        $supplier = $this->getCheckSupplier();

        if ($supplier=="Ozon") {
            /**
             * @var OzProduct $product
             */
            $images = [];
            $imagesOz = [];
            $product = OzProduct::find($product_id);
            $imagesOz = $this->getEscrowImagesForOzon($product);

            $this->idataImages = $imagesOz;

            foreach ($imagesOz["images"] as $imagefield) {
                $images[] = $imagefield;
            }
        }
        if ($supplier=="Wildberries") {
            /**
             * @var WbProduct $product
             */
            $product = WbProduct::find($product_id);
            $images = $this->getImagesByNmId($product,$product->nmid);
            $this->idataImages = $images;
        }

        return $images;
    }

    /**
     * Get escrow limit  product card
     * @param $product_id
     * @return int
     */
    public function getEscrowLimitProduct($product_id): int
    {
        $images = $this->getCheckSupplier($product_id);

        return sizeof($images);
    }


    /**
     * Get images protected product
     * @param $product_id
     * @return int
     */
    public function getDevideRemainEscrowProtectedProduct($product_id): int
    {
        $images = $this->getCheckSupplier($product_id);

        return sizeof($this->idataImages["imageHashes"]);
    }

    /**
     * Get remain escrows limit for current user product card
     * @param $product_id
     * @return int
     */
    public function getRemainEscrowLimitProduct($product_id): int
    {
        $images = $this->getCheckSupplier($product_id);

        $limit = sizeof($images) - sizeof($this->idataImages["imageHashes"]);
        if ($limit < 0) {
            $limit = 0;
        }

        return intval($limit);
    }

    /**
     * Create zip for product images
     * @param $images
     * @param $sku
     * @return string|null
     * @throws \Exception
     */
    public function makeImagesZip($images, $sku): ?string
    {
        $paths = $basenamePaths = [];
        foreach ($images as $image) {
            $imageName = basename($image);
            $imageBinary = Http::get($image)->getBody();
            Storage::disk($this->iregStorage)->put($imageName, $imageBinary);
            $path = Storage::disk($this->iregStorage)->path($imageName);
            $paths[] = $path;
            $basenamePaths[] = basename($path);
        }
        $zipPath = $sku . '.zip';
        $zipBool = $this->makeZipWithFiles($zipPath, $paths);
        Storage::disk($this->iregStorage)->delete($basenamePaths);
        if ($zipBool) {
            return Storage::disk($this->iregStorage)->path($zipPath);
        } else {
            throw new \Exception("Не удалось сформировать архив для депонирования");
        }
    }

    /**
     * Remove file from ireg folder
     * @param $fileName
     * @return void
     */
    public function removeZip($fileName): void
    {
        $fileName = basename($fileName);
        Storage::disk($this->iregStorage)->delete($fileName);
    }

    /**
     * Make zip from paths
     * @param string $zipPathAndName
     * @param array $filesAndPaths
     * @return bool
     */
    protected function makeZipWithFiles(string $zipPathAndName, array $filesAndPaths): bool
    {
        $path = Storage::disk($this->iregStorage)->getAdapter()->getPathPrefix() . $zipPathAndName;
        try {
            $zip = new ZipArchive;
            $zip->open($path, ZipArchive::CREATE);
            foreach ($filesAndPaths as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        } catch (\Exception $exception) {
            report($exception);
            return false;
        }
        return true;
    }

    /**
     * Get escrow percent for any account
     * @param $product
     * @return int
     */
    public function getEscrowPercent($product, $images): int
    {
        if (!$images) {
            return 0;
        }
        $imagesCount = count($images);
        if (!$imagesCount) {
            return 0;
        }
        $hashes = $product->escrowHash()->pluck('image_hash')->toArray();
        $intersect = array_intersect($hashes, $images);
        $intersectCount = count($intersect);
        $percent = ($intersectCount / $imagesCount) * 100;
        return intval($percent);
    }


    /**
     * Get escrow images for ozon account
     * @param OzProduct $product
     * @return array
     */
    public function getEscrowImagesForOzon(OzProduct $product): array
    {
        if (!$product->images || $product->images[0] === "") {
            return 0;
        }
        $imageHashes = $this->getImageHashes($product->images);
        if (!$imageHashes) {
            return 0;
        }
        $hashesArray = [];
        foreach ($imageHashes as $hash) {
            $hashesArray[] = current($hash);
        }

        return ["images"=>$product->images,"imageHashes"=>$hashesArray];
    }


    /**
     * Get escrow percent for ozon account
     * @param OzProduct $product
     * @return int
     */
    public function getEscrowPercentForOzon(OzProduct $product): int
    {
        if (!$product->images || $product->images[0] === "") {
            return 0;
        }
        $imageHashes = $this->getImageHashes($product->images);
        if (!$imageHashes) {
            return 0;
        }
        $hashesArray = [];
        foreach ($imageHashes as $hash) {
            $hashesArray[] = current($hash);
        }
        $percent = $this->getEscrowPercent($product, $hashesArray);
        return $percent;
    }

    /**
     * Get escrow percent for wildberries account
     * @param WbProduct $product
     */
    public function getEscrowPercentForNomenclature(WbProduct $product, $nmId): int
    {
        $images = $this->getImagesByNmId($product, $nmId);
        if (!count($images)) {
            return 0;
        }
        $imageHashes = [];
        foreach ($images as $image) {
            $hash = $this->getImageHash($image);
            if ($hash) {
                $imageHashes[] = current($hash);
            }
        }
        $imagesCount = count($images);
        if (!$imagesCount) {
            return 0;
        }
        $hashes = $product->escrowHash()
            ->where('nmid', $nmId)
            ->pluck('image_hash')
            ->toArray();
        $intersect = array_intersect($hashes, $imageHashes);
        $intersectCount = count($intersect);
        $percent = ($intersectCount / $imagesCount) * 100;

        return (int)$percent;
    }

    /**
     * Get last escrow percent for any account
     * @return int
     */
    public function getEscrowLastPercent(): int
    {
        $remainEscrowLimit = $this->getRemainEscrowLimit();
        $sku = UserService::getSku();
        if ($remainEscrowLimit <= 0 || $sku <= 0) {
            return 0;
        }

        return (int)($remainEscrowLimit / $sku * 100);
    }

    /**
     * Get images by nmid wildberries
     * @param $product
     * @param $nmid
     * @return array|null
     */
    public function getImagesByNmId($product, $nmid): ?array
    {
        $images = [];
        try {
            foreach ($product->data_nomenclatures as $data) {
                if ($data->nmId != $nmid) {
                    continue;
                }
                foreach ($data->addin as $addin) {
                    if ($addin->type !== 'Фото') {
                        continue;
                    }
                    foreach ($addin->params as $param) {
                        $images[] = $param->value;
                    }
                }
            }
        } catch (\Exception $exception) {
            report($exception);
            return [];
        }
        return $images;
    }

    /**
     * Get all images for product
     * @param $product
     * @return array|null
     */
    public function getAllOzonImages($product): ?array
    {
        return $product->images;
    }

    /**
     * Get image hash from url or path of image
     * @param $image
     * @return array|null
     */
    protected function getImageHash($image): array|null
    {
        $basename = basename($image);
        try {
            $hash = hash_file('sha512', $image);
        } catch (\Exception $exception) {
            Log::error('Escrow hash file error: ' . $exception->getMessage());
            return null;
        }
        return [$basename => $hash];
    }

    /**
     * Process array of images to hashes
     * @param $images
     * @param $hashes
     * @return array|null
     */
    public function getImageHashes($images, $hashes = []): array|null
    {
        if (!isset($images)) {
            return null;
        }
        foreach ($images as $image) {
            $hash = self::getImageHash($image);
            if (!empty($hash)) {
                $hashes[] = $hash;
            }
        }
        return $hashes;
    }

    /**
     * Save history and hashes
     *
     * @param $productId
     * @param $hashes
     * @param $escrowRequest
     * @return mixed
     */
    protected function saveEscrowResult($productId, $hashes, $escrowRequest): mixed
    {
        $nmid = $escrowRequest->nmid ?? null;
        EscrowHistory::create([
            'account_id' => UserService::getAccountId(),
            'product_id' => $productId,
            'copyright_holder' => $escrowRequest->copyright_holder,
            'full_name' => $escrowRequest->full_name,
            'email' => $escrowRequest->email,
            'nmid' => $nmid,
        ]);
        foreach ($hashes as $item) {
            if (!$item) {
                return null;
            }
            $name = array_key_first($item);
            $hash = $item[$name];
            $data = [
                'image_hash' => $hash,
                'product_id' => $productId,
                'nmid' => $nmid,
                'name' => $name
            ];
            $hashModel = EscrowHash::where('image_hash', $hash)->where('product_id', $productId);
            if (!empty($nmid)) {
                $hashModel->where('nmid', $nmid);
            }
            if ($hashModel->count()) {
                $hashModel->update(['name' => $name]);
            } else {
                EscrowHash::create($data);
            }
        }
        return $hashes;
    }

    /**
     * Return old rows from escrow histories table
     * @return void
     */
    public function clearEscrowHistory()
    {
        EscrowHistory::where('created_at', '<', Carbon::now()->subMonth()->toDateString())
            ->delete();
    }

    /**
     * Create DB rows with certificates for product
     * @param $certificates
     * @param $product
     * @param $escrowRequest
     * @return void
     */
    public function saveCertificates($certificates, $product, $escrowRequest)
    {
        $data = [];
        foreach ($certificates as $certificate) {
            $data[] = [
                'product_id' => $product->id,
                'lang' => Str::contains($certificate, '/ru/') ? 'ru' : 'en',
                'link' => $certificate,
                'full_name' => $escrowRequest->full_name,
                'copyright_holder' => $escrowRequest->copyright_holder,
                'email' => $escrowRequest->email,
                'nmid' => $escrowRequest->nmid ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        $query = EscrowCertificate::where('product_id', $product->id);
        if (isset($escrowRequest->nmid)) {
            $query->where('nmid', $escrowRequest->nmid);
        }
        $query->delete();
        EscrowCertificate::insert($data);
    }
}
