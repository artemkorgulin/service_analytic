<div>
    <b>Начало:</b>
    <span title='{{$promocode->start_at}}'>
        {{$promocode->start_at->diffForHumans()}}
    </span>
</div>
<div>
    <b>Окончание:</b>
    <span title='{{$promocode->end_at}}'>
        {{$promocode->end_at->diffForHumans()}}
    </span>
</div>
