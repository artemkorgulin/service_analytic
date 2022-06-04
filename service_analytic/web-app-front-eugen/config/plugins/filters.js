import Vue from 'vue';

import { plural } from '~utils/text.utils.js';

import { formatDateTime } from '~utils/date-time.utils.js';
import { splitThousands } from '~utils/numbers.utils.js';

Vue.filter('plural', plural);
Vue.filter('formatDateTime', formatDateTime);
Vue.filter('splitThousands', splitThousands);
