<?php
    use App\Models\OzProduct;
    /**
     * @var OzProduct $product
     */
?>
<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Рекомендации</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; }
        </style>
    </head>
    <body>
        <div>
            Добрый день!
        </div>
        <div>
            Для товара {{$product->name}} (sku: {{$product->sku}}) система “Виртуальный помощник” предлагает следующие рекомендации:
            <ul>
                @foreach($product->recomendations_pdf as $recomendation)
                    <li>
                        {{$recomendation['text']}}
                        @if(in_array('items', array_keys($recomendation)))
                            <ul>
                                @foreach($recomendation['items'] as $item)
                                    <li>{{$item}}</li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </body>
</html>
