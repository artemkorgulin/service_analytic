import qs from 'qs';

export function sortTableData(data, subjectsTotal, brandsTotal) {
    const tableData = [];
    subjectsTotal.forEach(subject => {
        const row = {
            subject_name: subject.subject_name,
        };
        brandsTotal.forEach(brand => {
            const brandSubjectData = data[brand.brand_id].table[subject.subject_id];
            const brandCell = getBrandSubjectTableCell(brandSubjectData, brand.brand_id);
            Object.entries(brandCell).forEach(([key, value]) => {
                row[key] = value;
            });
        });
        tableData.push(row);
    });
    if (!tableData.length) {
        [...Array(3)].forEach(() => {
            tableData.push({
                subject_name: '',
            });
        });
    }

    return { tableData };
}

export function sortPinnedBottomData(data, brandsTotal) {
    return brandsTotal.reduce((acc, el) => {
        const brandCell = getBrandSubjectTableCell(data[el.brand_id].avg, el.brand_id);
        Object.entries(brandCell).forEach(([key, value]) => {
            acc[key] = value;
        });
        return acc;
    }, { subject_name: 'Всего' });
}

function getBrandSubjectTableCell(data, brand_id) {
    return {
        [`sku_count_${brand_id}`]: data?.sku_count || 0,
        [`take_${brand_id}`]: data?.['take'] || 0,
        [`suppliers_count_${brand_id}`]: data?.suppliers_count || 0,
        [`avg_take_${brand_id}`]: data?.avg_take || 0,
    };
}

export function getSubjectsAndBrands(data, brandsOrder) {
    const subjectsTotal = [];
    const brandsTotal = [];
    Object.values(data).forEach(brand => {
        const brand_empty = !brand.table;
        if (!brand.table) {
            brand.table = {};
        }
        Object.values(brand.table).forEach(brandSubject => {
            const subjectsIndex = subjectsTotal.findIndex(
                el => brandSubject.subject_id === el.subject_id
            );
            if (subjectsIndex === -1) {
                const { subject_id, subject_name } = brandSubject;
                subjectsTotal.push({
                    subject_id,
                    subject_name,
                });
            }
        });

        if (brandsTotal.findIndex(el => el.brand_id === brand.avg.brand_id) === -1) {
            brandsTotal.push({
                brand_id: brand.avg.brand_id,
                brand: brand.avg.brand_name,
                brand_empty,
            });
        }
    });
    brandsTotal.sort((a, b) => brandsOrder.indexOf(a.brand_id) - brandsOrder.indexOf(b.brand_id));
    return { subjectsTotal, brandsTotal };
}

export function getValuesRange(data) {
    const rangeByBrands = Object.values(data).map(el => el.range);
    const basicEl = getBasicRangeElement(rangeByBrands);

    return rangeByBrands.reduce((acc, current) => {
        if (current) {
            Object.keys(current.max).forEach(el => {
                const currentVal = Number(current.max[el]) || 0;
                const accumulatedVal = Number(acc.max[el]) || 0;
                acc.max[el] =
                    currentVal > accumulatedVal
                        ? currentVal
                        : accumulatedVal;
            });
            Object.keys(current.min).forEach(el => {
                const currentVal = Number(current.min[el]) || 0;
                const accumulatedVal = Number(acc.min[el]) || 0;
                acc.min[el] =
                    currentVal < accumulatedVal && currentVal !== 0
                        ? currentVal
                        : accumulatedVal;
            });
        }
        return acc;
    }, basicEl);
}

function getBasicRangeElement(rangeByBrands) {
    const basicElement = {
        min: { sku_count: 0, take: 0, suppliers_count: 0, avg_take: 0 },
        max: { sku_count: 0, take: 0, suppliers_count: 0, avg_take: 0 },
    };
    return rangeByBrands.find(el => typeof el === 'object' && el !== null) || basicElement;
}

export function getCategoryAnalysisParams(state) {
    const brands = Object.values(state.brandsFrontend);
    const data = { ...state.userSearchParams, brands: brands };
    return {
        params: qs.stringify(data, {
            arrayFormat: 'brackets',
            encode: false,
        }),
        brands,
    };
}
