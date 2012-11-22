define([
    "dojo/_base/declare", // declare
    "dojo/dom",
    "dojo/query",
    "dojo/store/Memory"
], function(declare, dom, query, storeMemory){

    return declare(null, {
        constructor: function(){
            this._store = new storeMemory({
                    idProperty: 'method',
                    data: []
            });
        },
        //invocation, summons, defiance
        // method/:paramName1/:paramName2
        register: function(/* String */callRequest, fn) {
            if(callRequest.length > 0){

                var action = {
                    request: callRequest,
                    fn: fn,
                    params: []
                };

                var parts = action.request.split('/:');

                if(parts.length > 0){
                    action.method = parts.shift();
                    action.params = parts;
                } else {
                    action.method = action;
                }

                this._store.add(action);
            }
        },
        // Вызвать обработчик
        onApply: function(method, args){
            var action = this._store.get(method);
            if(action){
                action.fn.apply(this, [args]);
            }
        },
        // Преобразование массива в объект
        parsing: function(/*String|Node*/refNode) {
            var that = this;
            query('[data-action]', dom.byId(refNode)).on('click', function(event){
                event.preventDefault();
                that.dispatch(event.target.getAttribute('data-action'));
            });
        },
        // Action dispatcher
        // Существуют 2 вида маршрута:
        // 1. Страница (router)
        // 2. Подписка на действие (subscription)
        // method/paramValue1/paramValue2
        dispatch: function(/*String*/ request) {
            var parts = request.split('/');
            var action = this._store.get(parts[0]);
            if(action){
                var obj = {};
                for(var i = 0, len = action.params.length; i < len; i ++) {
                    obj[action.params[i]] = parts[i + 1];
                }
                this.onApply(action.method, obj);
            }
        }
    });
});