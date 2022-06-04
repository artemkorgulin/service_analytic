export const appFunctions = {
    // Instruction for usage and examples - https://github.com/toddmotto/foreach
    forEach: function (objects, callbackFunction, scope) {
        if (Object.prototype.toString.call(objects) === '[object Object]') {
            for (let item in objects) {
                if (Object.prototype.hasOwnProperty.call(objects, item)) {
                    callbackFunction.call(scope, objects[item], item, objects);
                }
            }
        } else {
            for (let i = 0, len = objects.length; i < len; i++) {
                callbackFunction.call(scope, objects[i], i, objects);
            }
        }
    }
};
