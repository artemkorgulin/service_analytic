/api/adm/v1/keywords GET список всех ключевых слов. Нужен для вывода списка, поиска и получения id слова для привязки к компаниям. Параметры запроса
search - строка для поиска ключевых слов. Поиск идёт по вхождению т.е. могут быть символы как перед строкой так и после неё.

/api/adm/v2/campaign/{campaign}/keywords GET список ключевых слов по компании. Параметры запроса
group_id - id группы, если нужны ключевые слова по ней
good_id - id товара, если ключевые слова по товару

/api/adm/v2/campaign/{campaign}/keywords POST привязать или создать новые ключевые слова к товару или группе. Если такого слова ещё нет в базе, метод создаст его и  привяжет к компании, товару или группе.
Параметры запроса
keywords массив для массового добавления. Одиночное добавление также делаем через этот массив. С ключами
keyword_id - id ключевого слова
keyword_name - название ключевого слова если оно новое. Должно быть указано одно из двух полей или keyword_id или keyword_name.
campaign_good_id - id товара в рекламной компании. Важно это не id самого товара, а идентификатор после добавления в рекламную компанию.
group_id - id группы. Обязательно указывать одно из двух либо товар либо группа.
bid - ставка по умолчанию будет 35

/api/adm/v2/campaign/{campaign}/keywords DELETE удалить привязку к рекламной компании. Параменты запроса
keyword_ids - массив id ключевых слов, которые надо отвязать.
group_id - id группы
good_id - id товара. Обязательно указывать одно из двух либо товар либо группа.

/api/adm/v2/campaign/{campaign}/keywords PUT обновить ставки по ключевым словам.
Параметры
keyword_ids - массив id ключевых слов, которые надо изменить. максимум 100 слов за запрос.
bid - новая ключевая ставка в диапазоне от 35 до 2000.



/api/adm/v2/stop-words GET список всех минус слов. Нужен для вывода списка, поиска и получения id слова для привязки к компаниям. Параметры запроса
search - строка для поиска минус слов. Поиск идёт по вхождению т.е. могут быть символы как перед строкой так и после неё.

/api/adm/v2/campaign/{campaign}/stop-words GET список минус слов по компании. Параметры запроса
group_id - id группы, если нужны минус слова по ней
good_id - id товара, если минус слова по товару

/api/adm/v2/campaign/{campaign}/stop-words POST привязать или создать новые минус слова к товару или группе. Если такого слова ещё нет в базе, метод создаст его и  привяжет к компании, товару или группе.
Параметры запроса
stop_words массив для массового добавления. Одиночное добавление также делаем через этот массив. С ключами
stop_word_id - id ключевого слова
stop_word_name - название минус слова если оно новое. Должно быть указано одно из двух полей или stop_word_id или stop_word_name.
campaign_good_id - id товара в рекламной компании. Важно это не id самого товара, а идентификатор после добавления в рекламную компанию.
group_id - id группы. Обязательно указывать одно из двух либо товар либо группа.

/api/adm/v2/campaign/{campaign}/stop-words DELETE удалить привязку к рекламной компании. Параметры запроса
stop_word_ids - массив id ключевых слов, которые надо отвязать.
group_id - id группы
campaign_good_id - id товара. Обязательно указывать одно из двух либо товар либо группа.


api/v2/campaigns GET там будет список кампаний. С элементами campaigns массив кампаний и  filters для выпадающих списков в фильтрах с id, типа statuses, strategy_types и т.д. где будет id и название.

api/v2/campaigns POST создание новой кампании.

api/v2/campaigns/{id} PUT обновление кампании

api/v2/campaigns/{id} DELETE удаление кампании, точнее пометка её удалённой.


api/v2/campaigns GET список кампаний. 

api/v2/campaigns POST создание новой кампании.

api/v2/campaigns/{id} PUT обновление кампании

Для метода api/v2/campaigns
Могут быть следующие фильтры
campaign_ids массив id кампаний выбранных
campaign_payment_type_id показы или клики id
campaign_status_ids массив id статусов
campaign_type_ids массив id типов рекламной кампании
campaign_page_type_id из массива campaign_page_types id
campaign_placement_id поиск или карточка id
campaign_budget_start общий бюджет от, число
campaign_budget_end общий бюджет до число
campaign_strategy_type_id тип стратегии id

В ответ возвращаются массивы в data
campaigns данные по кампаниям, там ещё информация есть в relations например
campaigns.*.campaignStatus.name

filters - данные из всех связанных таблиц с идентификаторами и названиями для фильтров

total_statistic - итоговая статистика по всем выбранным кампаниям для шапки

Для методов api/v2/campaigns POST и api/v2/campaigns/{id} PUT 
можно передавать пока такие параметры
name
campaign_status_id
budget
type_id
page_type_id
placement_id
payment_type_id
start_date
campaign_strategy_type_id - только для создания новой кампании пока, привяжет к кампании стратегию.
end_date
