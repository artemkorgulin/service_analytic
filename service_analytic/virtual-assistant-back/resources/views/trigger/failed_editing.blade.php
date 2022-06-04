@include('layouts.trigger.head')
<body>
<div>
    Добрый день!
</div>
<div>
    Произошла ошибка при отправке в Ozon данных товара {{ $product->sku }}  {{ $product->name }}.
    <br>Одно или несколько значений характеристик не приняты модератором.
    <br><br>
    Характеристики, не прошедшие проверку:
    <ul>
    @foreach($product->errorCharacteristics as $feature)
            <li>{{ $feature['name'] }}</li>
    @endforeach
    </ul>
    <br>
    Перейдите в сервис ВП и попробуйте ещё раз, проверьте правильность вводимых данных.
</div>
@include('layouts.trigger.footer')
</body>
</html>
