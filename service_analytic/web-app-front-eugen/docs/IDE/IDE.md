# Настройка редакторов


# WebStorm
[settings.zip](%D0%9D%D0%B0%D1%81%D1%82%D1%80%D0%BE%D0%B8%CC%86%D0%BA%D0%B0%20%D1%80%D0%B5%D0%B4%D0%B0%D0%BA%D1%82%D0%BE%D1%80%D0%BE%D0%B2%20155a44037c814e3d8c2ad19628403641/settings.zip)

## Общие настройки

- Preferences > Editor > CodeStyle

Hard wrap at: **100**

- Preferences > Editor > General > Other

Strip trailing spaces on save: **All**

## JavaScript

**Tabs & Indents**

- Tab Size: **4**
- Indet: **4**
- Continuation Indent: **4**

**Spaces**

- Before Parentheses → In function expression - **убрать галочку**

**Punctuation**

- **Use** semicolon to terminate statements **always**
    - Use **single** quotes **always**
- Trailing comma **add when multiline**

---

## SCSS / CSS

**Tabs & Indents**

- Tab Size: **4**
- Indet: **4**
- Continuation Indent: **4**

**Other**

- Braces placement: **End of line**
- Align values: **don't align**
- Quote marks: **Double**
- Enforce on foramt: **Поставить галочку**
- HEX Colors
    - Convert hex colors to: **Lower case**
    - Convert hex colors format to: **Short format**

---

## HTML

**Tabs & Indents**

- Tab Size: **4**
- Indet: **4**
- Continuation Indent: **4**

**Other**

- Spaces: **In empty tag**

---

## Настройки шаблонов

**Стандартный шаблон Vue.js**

Preferences > Editor > File and Code Templates

В списке выбираем **Vue Single File Component** заменяем содержимое на:

```jsx
<template>
		<div :class="#[[$style]]#.${COMPONENT_NAME}">
				#[[$END$]]#
		</div>
</template>

<script>
		export default {
				name: '${COMPONENT_NAME}'
		};
</script>

<style lang="scss" module>
		.${COMPONENT_NAME} {
				//
		}
</style>
```

При создание vue-файла автоматически будет прописан каркас компонента

---

**Live template Vue.js**

В Webstorm’е можно добавлять свои шорткаты, которые помогут вам ускорить разработку.

Пример:

1. Переходим в Preferences > Editor > Live Templates
2. Ищем vue, нажимаем на +
3. В abbreviation пишем vue (или любое другое имя), в template text вставляем

```jsx
<template>
		<div :class="$style.$name$">
		
		</div>
</template>

<script>
		export default {
				name: '$name$',

				$END$
		}
</script>

<style lang="scss" module>
		.$name$ {
				//
		}
</style>
```

- Снизу в applicable выбирем vue. Жмем на Edit variables, и в поле Default value пишем fileNameWithoutExtension()

Теперь если в своей vue-файле вы введете vue и нажмете **tab**, то увидите каркас компонента.

Также рекомендую всем добавить шорткат vc.

`<div :class="$style.$class$"></div>`

Это позволит ускорить написание модульных классов

# VS Code

Альтернативой Webstorm’у является использование VS Code. На данном этапе мне не удалось полностью добиться одинакового форматирования в этих двух редакторах, поэтому ниже приведен пример, который позволяет лишь максимально приблизить форматирование.

## Список плагинов

1. DotENV
2. EditorConfig
3. ESLint
4. Git Graph
5. Prettier
6. stylelint
7. Vetur

---

## Settings.json

```json
{
  "editor.codeActionsOnSave": {
    "source.fixAll.eslint": true,
    "source.fixAll.stylelint": true,
  },
   "eslint.workingDirectories": [{ "mode": "auto" }],
		"editor.rulers": [
				100
		],
		"vetur.format.options.tabSize": 4,
		"vetur.format.scriptInitialIndent": true,
		"vetur.format.styleInitialIndent": true,
		"vetur.format.defaultFormatter.html": "js-beautify-html",
		"vetur.format.defaultFormatterOptions": {
				"js-beautify-html": {
						"wrap_attributes": "force-aligned"
				},
				"prettyhtml": {
						"wrapAttributes": true
				}
		},
		
		"scssFormatter.tabWidth": 4,
		"scssFormatter.singleQuote": true,
		"scss.validate": false,
		"files.autoSave": "off",
		"explorer.confirmDelete": false,`
		"[javascript]": {
				"editor.defaultFormatter": "esbenp.prettier-vscode"
		},
		"[vue]": {
				"editor.defaultFormatter": "octref.vetur"
		},
		"[json]": {
				"editor.defaultFormatter": "esbenp.prettier-vscode"
		},
		"[jsonc]": {
				"editor.defaultFormatter": "esbenp.prettier-vscode"
		}
}
```

---

## Шорткаты

Для добавления шорткатов нужно перейти Code > Preferences > User Snippets, выбрать vue-html.json и добавить следующую запись:

```json
{
		"Add module class": {
				"prefix": "vc",
				"body": [
						"<div :class=\"\\$style.$1\"></div>"
				]
		}
}
```

---
