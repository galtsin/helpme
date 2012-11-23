define([
    "dojo/_base/declare", // declare
    "dojo/dom",
    "dojo/_base/array",
    "dojo/_base/lang",
    "dojo/query",
    "dojo/router",
    "dojo/store/Memory"
], function(declare, dom, array, lang, query, router, storeMemory){

    var Dispatcher = declare(null, {
        constructor: function(){
            this._store = new storeMemory({
                    idProperty: 'action',
                    data: []
            });
        },
        /**
         * @param route
         * route - Строка с маршрутом вида: action/:paramName1/:paramName2
         * Например: getUsers/:name/:sort
         * В дальнейшем этот маршрут может быть вызван 2мя способами:
         * 1. self.dispatcher('getUsers/alex/asc')
         * 2. self.onApply('getUsers', {name: alex, sort: asc});
         * @param fn
         * Callback-функция, вызываемая при запросе роутера
         * @param useRouter
         * Метка на использование роутера
         * true - использовать/false - еиспользовать
         */
        register: function(/*String*/route, fn, /*Bool*/useRouter) {
            if(route.length > 0){
                var action = {
                    route: route,
                    fn: fn,
                    vars: [],
                    useRouter: useRouter || false
                };
                if(true === useRouter){
                    router.register(route, fn);
                }
                var parts = action.route.split('/:');
                if(parts.length > 0){
                    action.action = parts.shift();
                    action.vars = parts;
                } else {
                    action.action = route;
                }
                this._store.add(action);
            }
        },
        /**
         * Вызов функции с параметрами. Используется только для action.useRouter = false;
         * Например: onApply('getUsers', {name: alex, sort: asc})
         * @param act
         * @param params
         */
        onApply: function (/*String*/act, /*Object*/params){
            var action = this._store.get(act);
            if(action){
                action.fn.apply(this, [lang.mixin({params: params}, action)]);
            }
        },
        /**
         * Распарсить DOM на наличие действий-маршрутизаторов и повесить на них событие click
         * Можно использовать собственный парсер, но необходимо будет самостоятельно передавать строку-запрос в диспетчер
         * @param refNode
         */
        parsing: function(/*String|Node*/refNode) {
            var that = this;
            query('[data-action]', dom.byId(refNode)).on('click', function(event){
                event.preventDefault();
                that.dispatch(event.target.getAttribute('data-action'));
            });
        },
        /**
         * Вызов функции по запросу на основании ранее установленного маршрута (action.route)
         * method/paramValue1/paramValue2
         * Например: dispatch('getUsers/alex/asc') // route: getUsers/:name/:sort
         * @param request
         */
        dispatch: function(/*String*/ request) {
            var parts = request.split('/');
            var action = this._store.get(parts[0]);
            if(action){
                if(true === action.useRouter){
                    router.go(request);
                } else {
                    var params = {};
                    array.forEach(action.vars, function(item, i){
                        params[item] = parts[i + 1];
                    });
                    action.fn.apply(this, [lang.mixin({request: request, params: params}, action)]);
                }
            }
        },
        /**
         * Активировать диспетчер
         * Вызывается в обязательном порядке, после регистрации всех действий
         */
        startup: function(){
            // Активировать роутер
            router.startup();
        }
    });

    return new Dispatcher();
});