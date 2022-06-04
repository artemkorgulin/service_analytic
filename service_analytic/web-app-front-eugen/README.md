# BEFORE SETUP

## DEV ENVS

```
API_URL=https://back.dev.sellerexpert.ru
FRONT_HOST=localhost
FRONT_PORT=4000
ENABLE_PROXY=true
ENABLE_WARNINGS=true
ENABLE_YANDEX_METRIKA=false
ENABLE_SENTRY=true
SENTRY_DSN=https://adcc9a7e6a494b65aaa5717c52586668@o1001141.ingest.sentry.io/5960646
```

## PROD ENVS

```
API_URL=https://back.dev.sellerexpert.ru
FRONT_PORT=4000
ENABLE_PROXY=true // until CORS will be configured on the backend side
// TODO add API_URL_SERVER
```

### Variations of API_URL:

```
http://192.168.1.177:81 // local network
```

## Accout with data:

```
toyij39180@tripaco.com
testfrom1904
```

# NUXT PROJECT SETUP

```bash
# install dependencies
$ yarn install

# serve with hot reload at localhost:3000
$ yarn dev

# build for production and launch server
$ yarn build
$ yarn start

```

For detailed explanation on how things work, check out the [documentation](https://nuxtjs.org).

# TEST

to run cypress chrome test:

```
yarn cy:run:e2e // cypress open --browser chrome
```

# SFC basic structure

```
<template>
    <div :class="$style.FILENAME">
    </div>
</template>

<script>
export default {
    name: 'FILENAME',
};
</script>

<style lang="scss" module>
    .FILENAME {
        //
    }
</style>
```

# ICONS

can be found at /\_icons route

# Filters

Filters can be setted in 'frontends/lk_broker/plugins/filters.js'

call with params:

```
this.$options.filters.<name-of-filter>(param1,param2)
```

# ImgProxy

this.$img.getSecure(imgUrl,params)"

# Router Extras

docs:
https://github.com/nuxt-community/router-extras-module

## Syntax Highlighting

### Visual Studio Code

Install Vetur extension and define custom block
Add <router> to vetur.grammar.customBlocks in VSCode settings

```
"vetur.grammar.customBlocks": {
    "docs": "md",
    "i18n": "json",
    "router": "js"
}
```

Execute command > Vetur: Generate grammar from vetur.grammar.customBlocks in VSCode
Restart VSCode and enjoy awesome

### PhpStorm/WebStorm

Place cursor right after tag
Right click on cursor and choose "Show context actions"
Select Inject language or reference
Select "JSON5" for JavaScript or "Yaml" for YAML

# NOTIFY MODULE

```
this.$notify.create({
    message: 'Oops! Error here',
    type: 'negative',
});
this.$notify.create({
    message: 'Just info',
    type: 'info',
});
this.$notify.create({
    message: 'Success.',
    type: 'positive',
});
```
