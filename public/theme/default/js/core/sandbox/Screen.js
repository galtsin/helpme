/* Менеджер Экранов */
dojo.provide("core.sandbox.Screen");
require(["dojo/store/Memory", "core/sandbox/screen/Breadcrumb"], function(Memory, Breadcrumb){
    core.sandbox.Screen = function(){};
    dojo.declare("core.sandbox.Screen", null, {
        constructor: function(){
            this._screen = new Memory({data:[]});
            this.Breadcrumb = new Breadcrumb(this);
            return this;
        },
        /* добавить экран*/
        /*
        * Экраны используются для загрузки одной законченной части страницы
        * Screen.add(screenName, functionName);
        */
        add: function(name, callback){
            this._screen.add({name: name, callback: callback});
        },
        /* получить экран */
        get: function(name){
            return this._screen.query({name: name})[0];
        }
    });
});