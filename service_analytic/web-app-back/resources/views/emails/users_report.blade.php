<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html lang="ru">

<head>
    <title>Отчёт по пользователям SellerExpert за {{$reportDate}}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="user-scalable=yes, minimum-scale=0.1, width=600px">
    <style type="text/css" data-hse-inline-css="true">
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700&display=swap');
    </style>
</head>

<body bgcolor="#e5e5e5" style="padding: 0; margin: 0; border: none">
<table bgcolor="#e5e5e5" border="0" cellpadding="0" cellspacing="0" width="100%"
       style="margin: 0; padding: 0; border: none">
    <tr style="padding: 0; margin: 0; border: none;">
        <td style="padding: 0; margin: 0; border: none;"></td>
        <td width="600" align="center" style="padding: 0; margin: 0; border: none;">

            <table border="0" cellpadding="0" cellspacing="0" width="600" bgcolor="#f9f9f9"
                   style="margin: 0; padding: 0; border: none">
                <tr style="padding: 0; margin: 0; border: none;">
                    <td style="padding: 30px 213px 20px 213px; margin: 0; border: none;">
                        <img src="{{asset('images/logo-beta.png')}}" alt="SellerExpert" width="174" height="61"
                             border="0" style="border:0; outline:none; text-decoration:none; display:block;">
                    </td>
                </tr>
                <tr style="padding: 0; margin: 0; border: none;">
                    <td style="padding: 0; margin: 0; border: none;">
                        <table border="0" cellpadding="0" cellspacing="0" width="600" bgcolor="#ffffff"
                               style="margin: 0; padding: 0; border: none">
                            <tr style="padding: 0; margin: 0; border: none;">
                                <td style="padding: 0; margin: 0; border: none;">
                                    <img src="{{asset('images/table-top-600-53.jpg')}}" alt="верхняя тень" width="600"
                                         height="53" border="0"
                                         style="border:0; outline:none; text-decoration:none; display:block;">
                                </td>
                            </tr>
                            <tr style="padding: 0; margin: 0; border: none;">
                                <td style="padding: 0; margin: 0; border: none;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="600" bgcolor="#ffffff" style="margin: 0; padding: 0; border: none">
                                        <tr style="padding: 0; margin: 0; border: none;">
                                            <td style="padding: 0; margin: 0; border: none;">
                                                <img src="{{asset('images/table-left-32-311.jpg')}}" alt="левая тень" width="32" height="311" border="0" style="border:0; outline:none; text-decoration:none; display:block;">
                                            </td>
                                            <td style="padding: 0 77px 0 77px; margin: 0; border: none;">
                                                <table border="0" cellpadding="0" cellspacing="0" width="382" bgcolor="#ffffff" align="center" style="margin: 0; padding: 0; border: none; text-align: center">
                                                    <tr style="padding: 0; margin: 0; border: none;">
                                                        <td style="padding: 0; margin: 0; border: none;">
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               Учетных записей всего: {{$report->countUsers}}
                                                            </span><br>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               Учетных записей хотя бы с одним аккаунтом всего: {{$report->countUsersAccount}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               Оплат по карте всего: {{$report->paidCard}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               Оплат по счету всего: {{$report->paidBank}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               Сумма оплат всего: {{$report->sumAmount}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               Аккаунтов OZON Seller всего: {{$report->countAcountOZ}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               Аккаунтов WB всего: {{$report->countAcountWB}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               Товаров в отслеживании OZON всего: {{$report->countOzProducts}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               Товаров в отслеживании WB всего: {{$report->countWbProducts}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               Товаров на аккаунтах OZON всего: {{$report->countOzProductsTmp}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               Товаров на аккаунтах WB всего: {{$report->countWbProductsTmp}}
                                                            </span><br>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               выполнило первый логин_600: {{$report->login600}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               подключило хотя бы 1 аккаунт_600: {{$report->account600}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               оплатило тариф_600: {{$report->paid600}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               выполнило первый логин_800: {{$report->login800}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               подключило хотя бы 1 аккаунт_800: {{$report->account800}}
                                                            </span>
                                                            <span style="text-align: left; font-family: 'Manrope', Arial, Helvetica, sans-serif !important; font-weight: 400; font-size: 16px; line-height: 24px; color: #2f3640; border:0; outline:none; text-decoration:none; display: block;">
                                                               оплатило тариф_800: {{$report->paid800}}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td style="padding: 0; margin: 0; border: none;">
                                                <img src="{{asset('images/table-right-33-311.jpg')}}" alt="правая тень" width="33" height="311" border="0" style="border:0; outline:none; text-decoration:none; display:block;">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr style="padding: 0; margin: 0; border: none;">
                                <td style="padding: 0; margin: 0; border: none;">
                                    <img src="{{asset('images/table-bottom-600-52.jpg')}}" alt="нижняя тень" width="600" height="52" border="0" style="border:0; outline:none; text-decoration:none; display:block;">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
        <td style="padding: 0; margin: 0; border: none;"></td>
    </tr>

</table>
</body>

</html>
