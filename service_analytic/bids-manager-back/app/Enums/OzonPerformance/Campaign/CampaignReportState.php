<?php

namespace App\Enums\OzonPerformance\Campaign;

use App\Enums\EnumCast;
use Illuminate\Contracts\Database\Eloquent\Castable;
use MyCLabs\Enum\Enum;

/**
 * @method static CampaignReportState NOT_STARTED()
 * @method static CampaignReportState IN_PROGRESS()
 * @method static CampaignReportState ERROR()
 * @method static CampaignReportState OK()
 */
class CampaignReportState extends Enum implements Castable
{

    use EnumCast;

    /**
     * Запрос ожидает выполнения
     */
    private const NOT_STARTED = 'NOT_STARTED';

    /**
     * Запрос выполняется в данный момент
     */
    private const IN_PROGRESS = 'IN_PROGRESS';

    /**
     * Выполнение запроса завершилось ошибкой
     */
    private const ERROR = 'ERROR';

    /**
     * Запрос успешно выполнен
     */
    private const OK = 'OK';


    public static function UNCOMPLETED_STATES(): array
    {
        return [
            CampaignReportState::NOT_STARTED(),
            CampaignReportState::IN_PROGRESS()
        ];
    }


    public static function COMPLETED_STATES(): array
    {
        return [
            CampaignReportState::ERROR(),
            CampaignReportState::OK()
        ];
    }
}
