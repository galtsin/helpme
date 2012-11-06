define([], function(){
    /**
     * Помошник формы
     */
    return {
        // Преобразование массива в объект
        arrayInObject: function(sourceArray, keysArray) {
            var obj = {};
            for(var i = 0, len = keysArray.length; i < len; i ++) {
                obj[keysArray[i]] = sourceArray[i];
            }
            return obj;
        }
    };
});