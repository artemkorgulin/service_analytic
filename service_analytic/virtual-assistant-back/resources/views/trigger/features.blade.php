@include('layouts.trigger.head')
<body>
<div>
    Добрый день!
</div>
<div>
    Обратите внимание! У некоторых ваших товаров на OZON появились новые возможности для улучшения описания товара!
</div>
<div>
    <table>
        <tr style="background-color: #dddddd;">
            <th>Код товара</th><th>Наименование</th>
            <th>Новые характеристики</th><th>Характеристики у которых изменились доступные значения</th>
        </tr>
        @foreach ($products as $product)
            <tr>
                <td><a href="{{ $product['model']->url }}">{{ $product['model']->sku }}</a></td>
                <td>{{ $product['model']->name }}</td>
                <td>{{ $product['new'] }}</td>
                <td>{{ $product['updated'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
@include('layouts.trigger.footer')
</body>
</html>
