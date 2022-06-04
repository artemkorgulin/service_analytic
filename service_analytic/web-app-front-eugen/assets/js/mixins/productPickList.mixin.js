/* eslint-disable sonarjs/no-nested-switch */
export default {
    methods: {
        getPanelIndexByFieldName(name, marketplace) {
            if (marketplace === 'wildberries') {
                switch (name) {
                    case 'title':
                    case 'packageSet':
                        return [0];

                    case 'purpose':
                    case 'direction':
                        return [1];

                    default:
                        return [2];
                }
            } else {
                switch (name) {
                    case 'title':
                        return [0];

                    default:
                        return [1];
                }
            }
        },
        getFieldNameBySectionIndex(index, indexInsideSection, marketplace) {
            if (marketplace === 'wildberries') {
                switch (index) {
                    case 1:
                        switch (indexInsideSection) {
                            case 0:
                                return 'title';
                            default:
                                return 'packageSet';
                        }

                    case 2:
                        switch (indexInsideSection) {
                            case 0:
                                return 'purpose';
                            default:
                                return 'direction';
                        }

                    default:
                        return 'description';
                }
            } else {
                switch (index) {
                    case 1:
                        return 'title';

                    default:
                        return 'description';
                }
            }
        },
    },
};
