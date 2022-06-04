<?php

namespace App\Http\Requests\V2\Campaign;

use App\Models\StrategyCpo;
use App\Models\StrategyType;
use AnalyticPlatform\LaravelHelpers\Http\Requests\BaseRequest;

class CampaignSaveRequest extends BaseRequest
{
    protected static $ATTRIBUTES = [
        'name' => 'Название рекламной кампании',
        'placement_id' => 'Место размещения',
        'payment_type_id' => 'Тип оплаты',
        'strategy_type_id' => 'Тип стратегии',
        'strategy_status_id' => 'Статус стратегии',
        'page_type_id' => 'Тип страниц рекламной кампании',
        'start_date' => 'Дата начала рекламной кампании',
        'end_date' => 'Дата окончания рекламной кампании',
        'budget' => 'Бюджет',
        'threshold1' => 'Порог принятия решения 1',
        'threshold2' => 'Порог принятия решения 2',
        'threshold3' => 'Порог принятия решения 3',
        'tcpo' => 'Целевое CPO',
        'vr' => 'Коэффициент волатильности',
        'threshold' => 'Порог принятия решения',
        'step' => 'Шаг ставки'
    ];

    protected static $ERROR_MESSAGES = [
        'budget.min' => 'Поле «:attribute» не может быть меньше '.StrategyCpo::MIN_BUDGET,
        'threshold1.min' => 'Поле «:attribute» не может быть меньше '.StrategyCpo::MIN_THRESHOLD,
        'threshold2.min' => 'Поле «:attribute» не может быть меньше Порог принятия решения 1 + '.StrategyCpo::MIN_THRESHOLD,
        'threshold3.min' => 'Поле «:attribute» не может быть меньше Порог принятия решения 2 + '.StrategyCpo::STEP_THRESHOLD,
        'tcpo.min' => 'Поле «:attribute» не может быть меньше '.StrategyCpo::MIN_TCPO,
        'vr.*' => 'Поле «:attribute» должно быть в диапазоне от '.StrategyCpo::MIN_VR.' до '.StrategyCpo::MAX_VR
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:250',
            'placement_id' => 'required|exists:campaign_placements,id',
            'payment_type_id' => 'required|exists:campaign_payment_types,id',
            'strategy_type_id' => 'nullable|exists:strategy_types,id',
            'strategy_status_id' => 'nullable|exists:strategy_statuses,id',
            'page_type_id' => 'nullable|exists:campaign_page_types,id',
            'budget' => (int) $this->input('budget') > 0 ? 'nullable|integer|min:'.StrategyCpo::MIN_BUDGET : 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date'
        ];

        if (!empty((int) $this->input('strategy_type_id')) && (int) $this->input('strategy_type_id') === StrategyType::OPTIMAL_SHOWS) {
            $rules = array_merge([
                'threshold' => 'required|numeric',
                'step' => 'required|numeric',
            ], $rules);
        } elseif (!empty((int) $this->input('strategy_type_id')) && (int) $this->input('strategy_type_id') === StrategyType::OPTIMIZE_CPO) {
            $rules = array_merge([
                'threshold1' => 'required|integer|min:'.StrategyCpo::MIN_THRESHOLD,
                'threshold2' => 'required|integer|min:'.($this->input('threshold1') + StrategyCpo::MIN_THRESHOLD),
                'threshold3' => 'required|integer|min:'.($this->input('threshold2') + StrategyCpo::STEP_THRESHOLD),
                'tcpo' => 'required|integer|min:'.StrategyCpo::MIN_TCPO,
                'vr' => 'required|integer|min:'.StrategyCpo::MIN_VR.'|max:'.StrategyCpo::MAX_VR,
                'horizon' => 'nullable|integer'
            ], $rules);
        }

        return $rules;
    }
}
