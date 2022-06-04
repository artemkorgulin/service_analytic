<?php

namespace App\Contracts\Repositories;

interface SegmentationRepositoryInterface
{
    /**
     * @return void
     */
    public function initObjects(): void;

    /**
     * @return array
     */
    public function getOptimizationSegmentsByProducts(): array;

    /**
     * @return array
     */
    public function getOptimizationSegmentsByCategory(): array;

    /**
     * @return array
     */
    public function getOptimizationSegmentsByBrand(): array;

    /**
     * @return array
     */
    public function getRevenueSegmentsByProducts(): array;

    /**
     * @return array
     */
    public function getRevenueSegmentsByCategory(): array;

    /**
     * @return array
     */
    public function getRevenueSegmentsByBrand(): array;

    /**
     * @return array
     */
    public function getOrderedSegmentsByProducts(): array;

    /**
     * @return array
     */
    public function getOrderedSegmentsByCategory(): array;

    /**
     * @return array
     */
    public function getOrderedSegmentsByBrand(): array;
}
