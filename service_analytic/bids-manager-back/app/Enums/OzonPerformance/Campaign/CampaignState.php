<?php

namespace App\Enums\OzonPerformance\Campaign;

use MyCLabs\Enum\Enum;

/**
 * @method static CampaignState CAMPAIGN_STATE_RUNNING()
 * @method static CampaignState CAMPAIGN_STATE_PLANNED()
 * @method static CampaignState CAMPAIGN_STATE_STOPPED()
 * @method static CampaignState CAMPAIGN_STATE_INACTIVE()
 * @method static CampaignState CAMPAIGN_STATE_ARCHIVED()
 * @method static CampaignState CAMPAIGN_STATE_MODERATION_DRAFT()
 * @method static CampaignState CAMPAIGN_STATE_MODERATION_IN_PROGRESS()
 * @method static CampaignState CAMPAIGN_STATE_MODERATION_FAILED()
 * @method static CampaignState CAMPAIGN_STATE_FINISHED()
 */
class CampaignState extends Enum
{

    /**
     * Активная кампания
     */
    private const CAMPAIGN_STATE_RUNNING = 'CAMPAIGN_STATE_RUNNING';

    /**
     * Кампания, сроки проведения которой ещё не наступили
     */
    private const CAMPAIGN_STATE_PLANNED = 'CAMPAIGN_STATE_PLANNED';

    /**
     * Кампания, сроки проведения которой завершились
     */
    private const CAMPAIGN_STATE_STOPPED = 'CAMPAIGN_STATE_STOPPED';

    /**
     * Кампания, остановленная владельцем
     */
    private const CAMPAIGN_STATE_INACTIVE = 'CAMPAIGN_STATE_INACTIVE';

    /**
     * Архивная кампания
     */
    private const CAMPAIGN_STATE_ARCHIVED = 'CAMPAIGN_STATE_ARCHIVED';

    /**
     * Отредактированная кампания до отправки на модерацию
     */
    private const CAMPAIGN_STATE_MODERATION_DRAFT = 'CAMPAIGN_STATE_MODERATION_DRAFT';

    /**
     * Кампания, отправленная на модерацию
     */
    private const CAMPAIGN_STATE_MODERATION_IN_PROGRESS = 'CAMPAIGN_STATE_MODERATION_IN_PROGRESS';

    /**
     * Кампания, непрошедшая модерацию
     */
    private const CAMPAIGN_STATE_MODERATION_FAILED = 'CAMPAIGN_STATE_MODERATION_FAILED';

    /**
     * Кампания завершена, дата окончания в прошлом,
     * такую кампанию нельзя изменить,
     * можно только клонировать или создать новую
     */
    private const CAMPAIGN_STATE_FINISHED = 'CAMPAIGN_STATE_FINISHED';
}
