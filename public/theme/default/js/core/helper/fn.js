define([
    "dojo/_base/array"
], function(array){
    return {
        // Пересечения данных
        enumerable: function(dataA, dataB) {
            var enumerable = {
                expect: [],
                intersect: [],
                distinct: []
            };

            array.forEach(dataA, function(itemA, index){
                enumerable.expect.push(itemA.id);
                array.forEach(dataB, function(itemB){
                    if(index == 0) enumerable.distinct.push(itemB.id);
                    if(itemA.id === itemB.id){
                        enumerable.intersect.push(itemA.id);
                        enumerable.expect.pop();
                        enumerable.distinct.splice(enumerable.distinct.indexOf(itemA.id), 1);
                    }
                });
            });

            return enumerable;
        },
        doPath: function(path, params) {
            return path.replace(/:\w+/g, function(param){
                var _p = param.substring(1);
                if(_p in params){
                    return params[_p];
                }
                return param;
            });
        },
        splitToObject: function(str, delimiter){
            if(!delimiter) {
                delimiter = '/';
            }
            var obj = {};
            var ar = str.split(delimiter);
            array.forEach(ar, function(item, i){
                if(i % 2 == 0) {
                    obj[item] = ar[i + 1];
                }
            });
            return obj;
        }
    }
});