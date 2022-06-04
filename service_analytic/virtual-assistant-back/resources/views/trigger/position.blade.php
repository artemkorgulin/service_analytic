@include('layouts.trigger.head')
<body>
<div>
    Добрый день!
</div>
<div>
    Обратите внимание! Позиция некоторых ваших товаров в поисковой выдаче на OZON сильно упала!
</div>
<div>
    <table>
        <tr style="background-color: #dddddd;">
            <th>Код товара</th><th>Наименование</th><th>Текущая позиция</th><th>Предыдущая позиция</th><th>Категория</th>
        </tr>
        @foreach ($products as $product)
            <tr>
                <td><a href="{{ $product['model']->url }}">{{ $product['model']->sku }}</a></td>
                <td>{{ $product['model']->name }}</td>
                <td>{{ $product['current_position'] }}</td>
                <td>{{ $product['previous_position'] }}</td>
                <td>{{ $product['model']->category->name }}</td>
            </tr>
        @endforeach
    </table>
</div>
@include('layouts.trigger.footer')
</body>
</html>
