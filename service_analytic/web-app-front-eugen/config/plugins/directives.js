import Vue from 'vue';

import TouchPan from '~directives/touch-pan';
import TouchSwipe from '~directives/touch-swipe';
import TouchHold from '~directives/touch-hold';
import TouchRepeat from '~directives/touch-repeat';

Vue.directive('touch-pan', TouchPan);
Vue.directive('touch-swipe', TouchSwipe);
Vue.directive('touch-hold', TouchHold);
Vue.directive('touch-repeat', TouchRepeat);
