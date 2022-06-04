# Vue/Nuxt Style Guide

При создании компонентов используются однофайловые (SFC) компоненты с расширением `.vue`.

[Официальный гайд](https://ru.vuejs.org/v2/style-guide/) обязателен к прочтению и его нужно придерживаться. Далее будет приведет список правил, уточняющих или переопределяющих принципов.

# 1. Имена компонентов

1. Имена однофайловых компонентов (SFC) должны всегда быть написаны в PascalCase.
2. Уникальные компоненты в своем название должны иметь приставку `The`.

    *Пример:* `TheHeader`.

3. Дочерние компоненты, тесно связанные с родителями, должны включать имя родительского компонента в качестве префикса. Если дочерних компонентов больше одного, необходимо объединять их в папку с именем родительского компонента.

    *Пример:* `HelpMenu`, `HelpMenuBar`, `HelpMenuItem`

4. Подробнее о структуре и организации файлов можно ознакомиться в гайде ["Как устроен Nuxt проект"](../Nuxt%20js%208d5bd6d2e135462a892a808624db50ac/%D0%9A%D0%B0%D0%BA%20%D1%83%D1%81%D1%82%D1%80%D0%BE%D0%B5%D0%BD%20Nuxt%20%D0%BF%D1%80%D0%BE%D0%B5%D0%BA%D1%82%200fc096bf201b4e459d59f827e2f9673e.md)

---

# 2. Общие требования к компонентам

1. Порядок секций в однофайловых компонентах должен быть следующий:

    ```jsx
    <template></template>

    <script></script>

    <style></style>
    ```

2. При использовании **css modules,** имя класса корневого элемента в компоненте должно совпадать с названием компонента и быть написано в формате PascalCase. Остальные классы должны быть максимально лаконичными и, по возможности, состоять из одного слова. Если есть необходимость использовать более одного слова в имени класса, имя пишется в формате camelCase
3. События, которые эмитятся из компонента, должны быть простыми и понятными.

    **Хорошо:**

    ```html
    <VButton @click="onClick" />

    <FilterApp
    		@values-change="onValuesChange"
    		@sort-change="onSortChange"
    />
    ```

    **Плохо:**

    ```html
    <VButton @na-menya-najali="onMySwagBitchVIPButtonClick" />

    //Измениться может много состояний, нет конкретики
    <FilterApp
    		@change="onValuesChange"
    		@sort-fast-update-now="onSortChange"
    />
    ```

4. В UI-компонентах, а так же компонентах близких к ним (как правило те, что лежат в common и используются во многих местах проекта), не рекомендуется использовать **css modules**, а вместо этого использовать обычный БЭМ.
5. Вся информация приходит в компонент только из props. Следует придерживаться одностороннего потока данных, и, где это возможно, избегать использования watch. Исключения составляют v-model.

---

# 3. Требования к секции `<template>`

1. При использовании компонентов их имена должны быть написаны в **PascalCase**.

    **Хорошо:**

    ```html
    <MyComponent />

    <MyComponent>
    		/* Some code */
    </MyComponent>
    ```

    **Плохо:**

    ```jsx
    <my-component />

    <my-component>
    		/* Some code */
    </my-component>
    ```

2. Компоненты без содержимого всегда должны быть самозакрывающимися.

    **Хорошо:**

    ```html
    <MyComponent />
    ```

    **Плохо:**

    ```html
    <MyComponent></MyComponent>
    ```

3. Обязательно использование сокращённой запись директив.

    **Хорошо:**

    ```html
    <MyComponent 
    		:value="1" 
    		@click="onClick"
    />
    ```

    **Плохо:**

    ```html
    <MyComponent v-bind:value="1" v-on:click="onClick" />
    ```

4. Стиль написания **атрибутов** должен соответствовать приведенному ниже:

    ```html
    <MyComponent :name="name" />

    <MyComponent
    		:name="name"
    		:value="value"
    />

    <MyComponent :name="name"
    						 :value="value">
    		<template #slotname></template>
    </MyComponent>
    ```

5. Атрибут класса компонента должен указываться первым. Исключения - случаи противоречащие линтеру. Порядок атрибутов компонента должен соответствовать [официальному гайду](https://ru.vuejs.org/v2/style-guide/). Этот порядок так же регулируется линтером.
6. Классы-модификаторы для **css-modules** необходимо начинать с нижнего подчеркивания  `_`. Они не должны быть глобальными, т.е. иметь привязку к `$style` текущего компонента. 

    *Пример:* `{[$style._active]: isActive}`

---

# 4. Требования к секции `<script>`

1. При импорте функций/библиотек/компонентов/запросов необходимо придерживаться следующей последовательности:

    ```json
    1) библиотеки
    2) функции

    /* пустая строка */

    3) запросы

    /* пустая строка */

    4) компоненты
    ```

    В случае большого числа импортов допускается дополнительно разграничивать секции комментариями.

2. Порядок свойств компонента должен соответствовать [официальному гайду](https://ru.vuejs.org/v2/style-guide/). Также этот порядок регулируется линтером.
3. Для всех `props` у каждого компонента должен быть указывать тип. Для объектов и массивов обязательно возвращать дефолтное значение в виде пустого объекта или массива.
4. Стиль написания **методов** должен соответствовать приведенному ниже:

    ```json
    methods: {
    		func1() {
    				/* Some Code */
    		},

    		func2() {
    				/* Some Code */
    		},
    }
    ```

5. Методы-обработчики событий, переданные в `props`, или обработчики emit-событий должны быть названы через `on`.

    ```jsx
    onFormSubmit
    onFilterChange
    ```

6. Методы-обработчики событий внутри компонента должны быть названы через `handle`.

    ```jsx
    handleFormSubmit
    handleFilterChange
    ```

7. Свойства компонента необходимо разграничивать пустой строкой.

    ```jsx
    export default {
    		props: {
    				/* Some code */
    		},

    		computed: {
    				/* Some code */
    		},
    	
    		methods: {
    				/* Some code */
    		},
    }
    ```

---

# Требования к секции `<style>`

1. Секция `<style>` должна быть написана с использование **scss** и/или **css modules**. `<style lang="scss" module>`
2. В случае использования **css modules**, все классы должны располагаться на уровне класса корневого элемента (Не считая ховеров и состояний).