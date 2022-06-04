import Vue from 'vue';
import 'element-ui/lib/theme-chalk/index.css';
import { DatePicker, Select, Autocomplete, Option } from 'element-ui';
import locale from 'element-ui/lib/locale';
import lang from 'element-ui/lib/locale/lang/ru-RU';
import 'element-ui/lib/theme-chalk/icon.css';
import 'element-ui/lib/theme-chalk/date-picker.css';
import 'element-ui/lib/theme-chalk/select.css';
import 'element-ui/lib/theme-chalk/autocomplete.css';
import 'assets/scss/common/_element-ui.scss';

// configure language
locale.use(lang);

// import components
Vue.component(DatePicker.name, DatePicker);
Vue.component(Select.name, Select);
Vue.component(Autocomplete.name, Autocomplete);
Vue.component(Option.name, Option);
