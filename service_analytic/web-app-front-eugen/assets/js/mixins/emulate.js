export default {
    data() {
        return {
            demoData: [],
            emulateDate: [
                '22.11.2021',
                '23.11.2021',
                '24.11.2021',
                '25.11.2021',
                '26.11.2021',
                '27.11.2021',
                '28.11.2021',
                '29.11.2021',
                '30.11.2021',
                '1.12.2021',
                '2.12.2021',
                '3.12.2021',
                '4.12.2021',
                '5.12.2021',
            ],
        };
    },
    methods: {
        emulateRevAndSales() {
            const revenue = [
                '40280',
                '33072',
                '31800',
                '27560',
                '28832',
                '39008',
                '36888',
                '35616',
                '13144',
                '107696',
                '89888',
                '133984',
                '92008',
                '89464',
            ];

            const sales = [
                '95',
                '78',
                '75',
                '65',
                '68',
                '92',
                '87',
                '84',
                '31',
                '254',
                '212',
                '316',
                '217',
                '211',
            ];
            const res = [
                { title: 'Продажи', graph: {} },
                { title: 'Выручка', graph: {} },
            ];
            this.emulateDate.forEach((item, index) => {
                /* eslint-disable */
                let [day, month, year] = item.split('.');
                day = day.length === 1 ? `0${day}` : day;
                item = [year, month, day].join('-');
                res[0].graph[item] = Number(sales[index]);
                res[1].graph[item] = Number(revenue[index]);
            });

            return res;
        },
        fdormatDate(item) {
            /* eslint-disable */
            let [day, month, year] = item.split('.');
            day = day.length === 1 ? `0${day}` : day;

            return [year, month, day].join('-');
        },
        emulatePositions(req) {
            const res = [];
            const categories = [
                'Красота и личная гигиена/Уход за волосами',
                'Красота и личная гигиена/Уход за волосами/Восстановление/Шампуни',
                'Красота и личная гигиена/Уход за волосами/Для окрашенных волос/Шампуни',
                'Красота и личная гигиена/Уход за волосами/Для укрепления волос/Шампуни',
                'Красота и личная гигиена/Профессиональная косметика',
                'Красота и личная гигиена/Профессиональная косметика/Шампуни',
            ];

            const requests = [
                'шампунь для волос',
                'безсульфатный шампунь',
                'шампунь для блондинок',
                'стойкий цвет',
                'антижелтый шампунь',
                'профессиональный шампунь',
                'оттеночный шампунь',
                'шампунь фиолетовый',
                'профессиональный шампунь для волос',
                'шампунь для жирных волос',
                'шампунь против перхоти',
                'шампунь против выпадения волос',
            ];

            const categoryData1 = [
                '1524',
                '1429',
                '1405',
                '1256',
                '1012',
                '986',
                '1118',
                '1348',
                '1406',
                '846',
                '620',
                '481',
                '365',
                '339',
            ];
            const categoryData2 = [
                '564',
                '501',
                '420',
                '437',
                '416',
                '379',
                '434',
                '481',
                '402',
                '82',
                '48',
                '37',
                '21',
                '16',
            ];
            const categoryData3 = [
                '103',
                '156',
                '118',
                '94',
                '106',
                '106',
                '101',
                '113',
                '98',
                '19',
                '14',
                '7',
                '2',
                '2',
            ];
            const categoryData4 = [
                '248',
                '304',
                '261',
                '213',
                '298',
                '321',
                '314',
                '301',
                '287',
                '48',
                '21',
                '24',
                '19',
                '16',
            ];
            const categoryData5 = [
                '2477',
                '2515',
                '2626',
                '1922',
                '1452',
                '1486',
                '1300',
                '1263',
                '1421',
                '583',
                '143',
                '253',
                '258',
                '194',
            ];
            const categoryData6 = [
                '438',
                '376',
                '398',
                '368',
                '376',
                '682',
                '474',
                '572',
                '388',
                '96',
                '85',
                '47',
                '61',
                '63',
            ];
            const request1 = [
                '55',
                '43',
                '46',
                '42',
                '45',
                '54',
                '68',
                '57',
                '64',
                '32',
                '28',
                '29',
                '18',
                '16',
            ];
            const request2 = [
                '189',
                '191',
                '218',
                '201',
                '198',
                '167',
                '211',
                '212',
                '207',
                '102',
                '97',
                '100',
                '91',
                '82',
            ];
            const request3 = [
                '62',
                '49',
                '64',
                '51',
                '58',
                '52',
                '34',
                '47',
                '38',
                '19',
                '12',
                '8',
                '7',
                '4',
            ];
            const request4 = [
                '201',
                '267',
                '291',
                '292',
                '312',
                '238',
                '276',
                '211',
                '248',
                '92',
                '84',
                '79',
                '81',
                '67',
            ];
            const request5 = [
                '378',
                '316',
                '364',
                '313',
                '324',
                '367',
                '402',
                '378',
                '391',
                '128',
                '108',
                '108',
                '97',
                '94',
            ];
            const request6 = [
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '68',
                '54',
                '61',
                '48',
                '44',
            ];
            const request7 = [
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '77',
                '74',
                '67',
                '71',
                '62',
            ];
            const request8 = [
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '31',
                '18',
                '22',
                '17',
                '12',
            ];
            const request9 = [
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '91',
                '73',
                '67',
                '66',
                '64',
            ];
            const request10 = [
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '102',
                '98',
                '92',
                '81',
                '82',
            ];
            const request11 = [
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '81',
                '69',
                '73',
                '78',
                '74',
            ];
            const request12 = [
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '72',
                '68',
                '67',
                '61',
                '63',
            ];

            const categoryData = [
                categoryData1,
                categoryData2,
                categoryData3,
                categoryData4,
                categoryData5,
                categoryData6,
            ];

            const reqData = [
                request1,
                request2,
                request3,
                request4,
                request5,
                request6,
                request7,
                request8,
                request9,
                request10,
                request11,
                request12,
            ];

            if (!req) {
                categories.forEach((item, index) => {
                    this.emulateDate.forEach((date, index2) => {
                        res.push({
                            category: item,
                            date: this.fdormatDate(date),
                            position: categoryData[index][index2],
                        });
                    });
                });
            } else {
                requests.forEach((item, index) => {
                    this.emulateDate.forEach((date, index2) => {
                        res.push({
                            search: item,
                            createdAt: this.fdormatDate(date),
                            position: reqData[index][index2],
                        });
                    });
                });
            }
            return res;
        },
    },
};
