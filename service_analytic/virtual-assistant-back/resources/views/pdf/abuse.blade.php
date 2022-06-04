<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Бланк жалобы</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }
    </style>
</head>
<body>
<div style="text-align: right;">
    Адресат: {{ $site }}<br>
    Правообладатель: {{ $copyright_holder }} {{ $email }}<br>
    Сервис депонирования: ireg.pro<br><br><br><br><br>
</div>
<div style="text-align: center;">
    <h3>Претензия о нарушении авторских прав на площадке.<h3><br>
</div>
<div style="text-align: justify;">
    Здравствуйте! В результате проверки сервисом защиты прав на результаты интеллектуальной деятельности Ireg были
    выявлены следующие факты нарушения авторских прав:
</div>
<div>
    <ul>
        <li>
            {{ $another_product_link }}<br>
            Правообладателем авторских прав на контент является:
        </li>
        {{ $copyright_holder }} {{ $email }}
        <li>Права Правообладателя подтверждаются:
            {{ $certificate_link }}</li>
        <li>Правообладатель продает товар на площадке, что подтверждается:
            {{ $self_product_link }}.
        </li>
    </ul>
</div>
<div style="text-align: justify;">
    В соответствии с п. 1 ст. 1270 ГК РФ правообладателю принадлежит исключительное право использовать произведение в
    соответствии со ст. 1229 ГК РФ в любой форме и любым не противоречащим закону способом (исключительное право на
    произведение). <br>
    На основании п. 3 ст. 1300 ГК РФ в случае нарушения положений, предусмотренных п. 2 ст. 1300 ГК РФ, правообладатель
    вправе требовать выплаты компенсации в соответствии со ст. 1301 ГК РФ в размере до 5 млн. рублей.<br><br>
</div>
<div style="text-align: justify;">
    В соответствии с п. 3 ст. 1250 ГК РФ отсутствие вины нарушителя не освобождает его от обязанности прекратить
    нарушение интеллектуальных прав, а также не исключает применение в отношении нарушителя мер, направленных на защиту
    таких прав. Таким образом, с момента получения настоящего заявления, вы должным образом уведомлены о нарушении
    исключительных прав, а также о возможном привлечении вас к ответственности в случае отказа от принятия мер согласно
    положению ст.1250 ГК РФ.<br><br>
</div>
<div style="text-align: justify;">
    В связи с тем, что вы являетесь информационным посредником, прошу вас принять меры для прекращения нарушения путем
    блокировки доступа к контенту:
    {{ $another_product_link }}<br><br>
    в течение 10 дней. В этом случае, в соответствии с п.4. статьи. 1253.1 ГК РФ вы не будете привлечены к имущественной
    ответственности за нарушение авторских прав. В случае отказа от добровольного исполнения требований о прекращении
    нарушения исключительных прав, я буду вынужден обратиться в суд с возложением на вас следующих расходов: за
    подготовку претензионного письма 15 000 рублей, за подготовку искового заявления 50 000 рублей и компенсации за
    нарушение авторских прав в размере двукратной стоимости изделий, реализованных на площадке.
</div>
<div>
    <br><br><br><br><br><br><br><br><br><br>
    Подпись ______________________________
</div>
</body>
</html>