<?php

namespace App\DTO\Common;

class CommonProductDashboardCreateDTO
{
    private array $badSegment;
    private array $normalSegment;
    private array $goodSegment;

    public function __construct(
        private int $userId,
        private int $account_id,
        private int $marketplaceId,
        private string $dashboardType,
        private array $data
    ) {
        if (!in_array($dashboardType, array_keys(config('model.dashboard.type')))) {
            throw new \Exception('Undefined dashboard type.');
        }

        $this->badSegment = $data['bad'];
        $this->normalSegment = $data['normal'];
        $this->goodSegment = $data['good'];
    }

    /**
     * @return int
     */
    public function getAccountId(): int
    {
        return $this->account_id;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getMarketplaceId(): int
    {
        return $this->marketplaceId;
    }

    /**
     * @return string
     */
    public function getDashboardType(): string
    {
        return $this->dashboardType;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getBadSegment(): array
    {
        return $this->badSegment;
    }

    /**
     * @return array
     */
    public function getNormalSegment(): array
    {
        return $this->normalSegment;
    }

    /**
     * @return array
     */
    public function getGoodSegment(): array
    {
        return $this->goodSegment;
    }

    public function toArray(): array
    {
        return [
            'dashboard_type' => $this->getDashboardType(),
            'good_segment' => $this->getGoodSegment(),
            'normal_segment' => $this->getNormalSegment(),
            'bad_segment' => $this->getBadSegment(),
            'marketplace_platform_id' => $this->getMarketplaceId(),
            'user_id' => $this->getUserId(),
            'account_id' => $this->getAccountId(),
        ];
    }

}
