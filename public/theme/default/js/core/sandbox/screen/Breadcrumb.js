/* Навигатор - хлебные крошки */
dojo.provide("core.sandbox.screen.Breadcrumb");
require([], function(){
    core.sandbox.screen.Breadcrumb = function(){};
    dojo.declare("core.sandbox.screen.Breadcrumb", null, {

        constructor: function(screenInstance){
            this._screenInstance = screenInstance;
            this._breadcrumb = [];
            return this;
        },

        // Добавить ссылку на Screen в стек
        push: function(screenName, label){
            this._breadcrumb.push({screen: screenName, label: label});
        },

        // Извлечь ссылку на Screen из стека
        pop: function(){
          return this._breadcrumb.pop();
        },

        // Переход на указанную ссылку в стеке ссылок
        // При переходе по цепочке назад - путь усекается и удаляется
        go: function(screenName){
            for(var i = 0; i <= this._breadcrumb.length; i ++) {
                var breadcrumb = this._breadcrumb.pop();
                if(breadcrumb.screen === screenName) {
                    return this._screenInstance.get(breadcrumb.screen);
                }
            }
        },

        // Построение хлебных крошек
        build: function(callback){
            callback(this._breadcrumb);
        },

        _isExists: function(screenName){
            return false;
        }
    });
});