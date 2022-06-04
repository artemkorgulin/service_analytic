@include('layouts.trigger.head')
<body>
<div>
    Добрый день!
</div>
<div>
    Обратите внимание! Некоторые ваши товары сняты с продажи на OZON!
</div>
<div>
    <table>
        <tr style="background-color: #dddddd;">
            <th>Код товара</th><th>Наименование</th><th>Последнее редактирование в системе</th>
        </tr>
        @foreach ($products as $product)
            <tr>
                <td><a href="{{ $product['model']->url }}">{{ $product['model']->sku }}</a></td>
                <td>{{ $product['model']->name }}</td>
                <td>{{ $product['model']->updated_at->format('d.m.Y') }}</td>
            </tr>
        @endforeach
    </table>
</div>
@include('layouts.trigger.footer')
</body>
</html>
