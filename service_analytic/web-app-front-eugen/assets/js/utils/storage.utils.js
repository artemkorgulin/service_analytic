//
//  функция для чтения данных из localStorage
//  принимает в качестве аргумента объект который содержит:
//  <clean> - если установлено то игнорируем и очищаем localStorage;
//  <key> - ключ по которому нужные данные хранятся в localStorage;
//  <lifetime> - время в секундах, в течении которого данные стоит считать актуальными,
//               если установлено <0>, считается что данные актуальны всегда;
//  <callBack> - функция обратного вызова;
//
const getStorage = ({ clean, key, lifetime, callBack }) => {
    let storage = localStorage.getItem(key);

    if (storage) {
        const nowTimestamp = Number(new Date());
        storage = JSON.parse(storage);

        // <lifetime * 1000> - преобразовываем секунды в миллисекунды
        if (!clean && (nowTimestamp - storage.timestamp <= lifetime * 1000 || !lifetime)) {
            callBack(storage.data);
        } else {
            localStorage.removeItem(key);
            callBack(false);
        }
    } else {
        callBack(false);
    }
};

//
//  функция для сохранения данных в localStorage
//  принимает в качестве аргумента объект который содержит:
//  <key> - ключ по которому нужные данные хранятся в localStorage;
//  <data> - объект или массив данных который необходимо сохранить в localStorage
//
const setStorage = ({ data, key }) => {
    localStorage.setItem(
        key,
        JSON.stringify({
            timestamp: Number(new Date()),
            data,
        })
    );
};

export { getStorage, setStorage };
