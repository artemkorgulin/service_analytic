<?php

namespace App\DTO\Common;

class CommonProductDashboardUpdateDTO
{
    private array $badSegment;
    private array $normalSegment;
    private array $goodSegment;

    public function __construct(private array $data)
    {
        $this->goodSegment = $data['good'];
        $this->normalSegment = $data['normal'];
        $this->badSegment = $data['bad'];
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
}
