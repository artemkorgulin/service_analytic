@include('layouts.trigger.head')
<body>
<div>
    Добрый день!
</div>
<div>
    Обратите внимание! Изменились описания конкурентов в ТОП-36 категории ваших товаров.
</div>
<div>
    <table>
        <tr style="background-color: #dddddd;">
            <th>Код товара</th><th>Наименование</th><th>Количество отзывов у вас</th>
            <th>Минимум отзывов у конкурентов в ТОП-36</th><th>Количество фотографий у вас</th>
            <th>Минимум фотографий у конкурентов в ТОП-3</th><th>Категория</th>
        </tr>
        @foreach ($products as $product)
            <tr>
                <td><a href="{{ $product['model']->url }}">{{ $product['model']->sku }}</a></td>
                <td>{{ $product['model']->name }}</td>
                <td>{{ $product['model']->count_reviews }}</td>
                <td>{{ $product['history']->min_reviews }}</td>
                <td>{{ $product['model']->count_photos }}</td>
                <td>{{ $product['history']->min_photos }}</td>
                <td>{{ $product['model']->category->name }}</td>
            </tr>
        @endforeach
    </table>
</div>
@include('layouts.trigger.footer')
</body>
</html>
