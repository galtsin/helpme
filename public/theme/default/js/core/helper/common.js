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
        },
        parseDataActionParams: function(node, keysArray){
            if(node.getAttribute('data-action')) {
                keysArray.unshift('method');
                return this.arrayInObject(node.getAttribute('data-action').split('/'), keysArray);
            }
            return null;
        }
    };
});