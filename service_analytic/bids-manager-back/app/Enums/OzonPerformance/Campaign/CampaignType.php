<?php

namespace App\Enums\OzonPerformance\Campaign;

use MyCLabs\Enum\Enum;

/**
 * @method static CampaignType SKU()
 * @method static CampaignType BANNER()
 * @method static CampaignType SIS()
 * @method static CampaignType BRAND_SHELF()
 * @method static CampaignType BOOSTING_SKU()
 * @method static CampaignType ACTION()
 * @method static CampaignType ACTION_CAS()
 * @method static CampaignType VIDEO_BANNER()
 * @method static CampaignType SEARCH_PROMO()
 * @method static CampaignType EXTERNAL_GOOGLE()
 * @method static CampaignType PROMO_PACKAGE()
 * @method static CampaignType PROMO_PACKAGE_SERVICE()
 */
class CampaignType extends Enum
{

    /**
     * Реклама товаров в спонсорских полках
     * с размещением на карточках товаров, в поиске или категории
     */
    private const SKU = 'SKU';

    /**
     * Баннерная рекламная кампания
     */
    private const BANNER = 'BANNER';

    /**
     * Реклама магазина
     */
    private const SIS = 'SIS';

    /**
     * Брендовая полка
     */
    private const BRAND_SHELF = 'BRAND_SHELF';

    /**
     * Повышение товаров в каталоге
     */
    private const BOOSTING_SKU = 'BOOSTING_SKU';

    /**
     * Рекламная кампания для акций продавца
     */
    private const ACTION = 'ACTION';

    /**
     * Рекламная кампания для акции
     */
    private const ACTION_CAS = 'ACTION_CAS';

    /**
     * Видео баннерная рекламная кампания(?)
     * @notice В документации отсутствует
     */
    private const VIDEO_BANNER = 'VIDEO_BANNER';

    /**
     * Продвижение в промо(?)
     * @notice В документации отсутствует
     */
    private const SEARCH_PROMO = 'SEARCH_PROMO';

    /**
     * Реклама в google(?)
     * @notice В документации отсутствует
     */
    private const EXTERNAL_GOOGLE = 'EXTERNAL_GOOGLE';

    /*
     * @notice В документации отсутствует
     */
    private const PROMO_PACKAGE = 'PROMO_PACKAGE';

    /**
     * @notice В документации отсутствует
     */
    private const PROMO_PACKAGE_SERVICE = 'PROMO_PACKAGE_SERVICE';


}
