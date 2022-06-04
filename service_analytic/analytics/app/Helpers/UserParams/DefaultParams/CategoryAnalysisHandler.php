<?php

namespace App\Helpers\UserParams\DefaultParams;

class CategoryAnalysisHandler implements HandlerInterface
{
    /**
     * @param  int  $userId
     * @return array
     */
    public function getParams(int $userId): array
    {
        return [
            'brands' => [5772, 6364],
            'start_date' => date("Y-m-d", strtotime("-31 days")),
            'end_date' => date("Y-m-d", strtotime("-1 days")),
        ];
    }
}
