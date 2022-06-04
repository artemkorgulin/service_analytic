import { onGlobalSetup, provide } from '@nuxtjs/composition-api';
import { plural } from '~utils/text.utils.js';
import { formatDateTime } from '~utils/date-time.utils.js';
import { splitThousands } from '~utils/numbers.utils.js';

export default () => {
    onGlobalSetup(() => {
        provide('plural', plural);
        provide('formatDateTime', formatDateTime);
        provide('splitThousands', splitThousands);
    });
};
