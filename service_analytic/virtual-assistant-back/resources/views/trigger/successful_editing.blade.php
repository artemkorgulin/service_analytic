@include('layouts.trigger.head')
<body>
<div>
    Добрый день!
</div>
<div>
    Вы успешно отредактировали товар {{ $product->sku }} {{ $product->name }}
</div>
@include('layouts.trigger.footer')
</body>
</html>
