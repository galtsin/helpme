define([
    "require",
    "dojo/_base/declare",
    "dojo/_base/array",
    "dojo/_base/lang",
    "dojo/hash",
    "dojo/topic",
    "dojo/store/Memory",
    "dijit/_WidgetBase",
    "dijit/_Contained"
], function(require, declare, array, lang, hash, topic, storeMemory, _WidgetBase, _Container){
    return declare("SimpleBreadcrumbs", [_WidgetBase, _Container], {
        store: null,
        breadcrumbStack: null,
        routes: null,
        postMixInProperties: function(){
            this.store = new storeMemory({idProperty: 'hash'});
            this.breadcrumbStack = [];
            this.routes = [];
        },
        postCreate: function(){
            var _SimpleBreadcrumbsWidget = this;
            topic.subscribe("/dojo/hashchange", function(changedHash){
                _SimpleBreadcrumbsWidget.go(changedHash);
            });
        },
        /**
         * Зарегистрировать маршрут
         * @param route
         * @param createTitleCallback
         */
        registerRoute: function(/*String*/route, /*Function*/createTitleCallback){
            var Route = {
                params: [],
                pattern: null,
                callback: createTitleCallback || null
            };
            // Преобразовать в регулярное выражение
            Route.pattern  = route.replace(/:[\w-]+/g, function(param){
                Route.params.push(param.substring(1));
                return '([0-9a-z_-]+)';
            });
            this.routes.push(Route);
        },
        /**
         * Перейти на хэш-указатель
         * @param hash
         */
        go: function(hash){
            var Event = {hash: hash, params: {}};
            var currentRoute = this._searchRoute(hash);
            if(currentRoute){
                var hashParams = hash.match(new RegExp(currentRoute.pattern, 'i')).slice(1);
                array.forEach(currentRoute.params, function(param, index){
                    Event.params[param] = hashParams[index];
                });
                if(!this.store.get(hash)) this.store.add({hash: hash, title: currentRoute.callback ? currentRoute.callback(Event) : hash});
                this.refresh(Event);
            }
        },
        /**
         * Найти маршрут
         * @param hash
         * @return {*}
         * @private
         */
        _searchRoute: function(hash){
            var route = null;
            array.some(this.routes, function(Route){
                var Regexp = new RegExp(Route.pattern, 'i');
                if(hash.search(Regexp) > -1) { route = Route; return true}
                return false;
            });
            return route;
        },
        /**
         * Обновить DOM-дерево навигации
         * @param event
         */
        refresh: function(event){
            if(this.breadcrumbStack.indexOf(event.hash) > -1) {
                this.breadcrumbStack = this.breadcrumbStack.slice(0, this.breadcrumbStack.indexOf(event.hash) + 1);
            } else {
                this.breadcrumbStack.push(event.hash);
            }
            var html = '';
            var _SimpleBreadcrumbsWidget = this;
            array.forEach(this.breadcrumbStack, function(hash, index){
                var breadcrumb = _SimpleBreadcrumbsWidget.store.get(hash);
                if(index == _SimpleBreadcrumbsWidget.breadcrumbStack.length - 1) {
                    html += '<strong><em>' + breadcrumb.title + '</em></strong>';
                } else {
                    html += '<a href="#' + breadcrumb.hash + '">' + breadcrumb.title + '</a> / ' ;
                }
            });
            this.domNode.innerHTML = html;
        }
    });
});