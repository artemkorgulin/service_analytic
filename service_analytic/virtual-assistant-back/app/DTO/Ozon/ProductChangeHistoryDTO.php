<?php

namespace App\DTO\Ozon;

use App\DTO\Ozon\Contracts\DTOInterface;
use App\Repositories\Ozon\OzonProductChangeHistoryRepository;
use App\Repositories\Ozon\OzonProductFeatureRepository;


class ProductChangeHistoryDTO implements DTOInterface
{
    private int $productId;
    private string $name;
    private ?int $statusId;
    private ?int $taskId;
    private ?bool $isSend;
    private ?array $requestData;
    private ?array $responseData;

    public function __construct(
        int $productId,
        string $name,
        ?int $statusId,
        ?bool $isSend = true,
        ?int $taskId,
        ?array $requestData,
        ?array $responseData
    ) {
        $this->productId = $productId;
        $this->name = $name;
        $this->statusId = $statusId ?? OzonProductChangeHistoryRepository::PRODUCT_CHANGE_HISTORY_STATUS_MODERATION_ID;
        $this->taskId = $taskId ?? null;
        $this->isSend = $isSend ?? false;
        $this->requestData = $requestData ?? null;
        $this->responseData = $responseData ?? null;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->getProductId(),
            'name' => $this->getName(),
            'status_id' => $this->getStatusId(),
            'task_id' => $this->getTaskId(),
            'is_send' => $this->getIsSend(),
            'request_data' => $this->getRequestData(),
            'response_data' => $this->getResponseData(),
        ];
    }

    /**
     * @return string|null
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    /**
     * @return string|null
     */
    public function getRequestData(): ?array
    {
        return $this->requestData;
    }

    /**
     * @return bool
     */
    public function getIsSend(): ?bool
    {
        return $this->isSend;
    }

    /**
     * @return int
     */
    public function getTaskId(): ?int
    {
        return $this->taskId;
    }

    /**
     * @return int
     */
    public function getStatusId(): ?int
    {
        return $this->statusId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }


}
