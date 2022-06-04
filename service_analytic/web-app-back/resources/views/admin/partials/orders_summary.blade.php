<h4>Общая информация по заказам</h4>
<ul>
    @if($is_free)
        <li>
            На бесплатном тарифе
        </li>
    @else
        <li>
            На платном тарифе
        </li>
    @endif

    @if($tariff)
        <li>
            <b>Последний оформленный тариф:</b> {{$tariff['name']}}
        </li>
    @endif
</ul>

@if($services)
    <br>

    <h4>Услуги в заказах</h4>

    <ul>
    @foreach($services as $service)
        <li>
            {{$service['name']}}
            @if($service['countable'])
                <b>{{$service['amount']}} sku</b>
            @endif
            <ul>
                @foreach($service['periods'] as $period)
                    <li>
                        @if($service['countable'])
                            Действует <b>{{$period['ordered']}} sku</b> на период {{$period['start_at']->format('Y-n-j')}} - {{$period['end_at']->format('Y-n-j')}}
                        @else
                            Действует на период {{$period['start_at']->format('Y-n-j')}} - {{$period['end_at']->format('Y-n-j')}}
                        @endif
                    </li>
                @endforeach
            </ul>
        </li>
    @endforeach
    </ul>
@endif
