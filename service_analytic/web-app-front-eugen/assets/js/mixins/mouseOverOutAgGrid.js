import { getCoords } from '~/assets/js/utils/helpers';

export default {
    data() {
        return {
            modalCoords: {
                top: null,
                left: null,
            },
            modalState: false,
            imgSrc: null,
        };
    },
    methods: {
        cellMouseOver(params) {
            const {
                colDef: { field },
                value,
                event,
            } = params;

            const methods = {
                image_middle: () => {
                    this.imgSrc = value;
                    const elCell = event.target.closest('.ag-cell');
                    const { top, left } = getCoords(elCell, true);

                    this.modalCoords = {
                        top,
                        left: elCell.offsetWidth + left,
                    };
                    this.modalState = true;
                },
            };

            if (field in methods) methods[field]();
        },
        cellMouseOut() {
            this.modalState = false;
        },
    },
};
