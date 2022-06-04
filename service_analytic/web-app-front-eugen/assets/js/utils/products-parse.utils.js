const productsParse = ({ data, marketplace }) => {
    const newData = marketplace === 'ozon' ? data.data : data;

    switch (marketplace) {
        case 'ozon':
            newData.items = newData.data.map(item => {
                item.fbo = {
                    art: item.sku.fbo,
                    quantity: item.quantity.fbo,
                    link: item.product_ozon_url,
                };

                item.fbs = {
                    art: item.sku.fbs,
                    quantity: item.quantity.fbs,
                    link: item.product_ozon_url,
                };

                return item;
            });
            break;
        case 'wildberries':
            newData.items = newData.data.map(item => {
                const newItem = item;
                newItem.photo_url = newItem.image;

                return newItem;
            });
            break;
    }

    return newData;
};

export { productsParse };
