/* Менеджер слоев */
dojo.provide("core.sandbox.Layout");
require(["core/resources/Ajax", "core/sandbox/layout/Dialog"], function(Ajax){
    core.sandbox.Layout = function(){};
    dojo.declare("core.sandbox.Layout", null, {
        constructor: function(){
            this.container = [];
            this.Ajax = new Ajax();
        },
        // Отправить данные на сервер.
        dataSender: function(url, data){
            data['format'] = "json";
            return this.Ajax.xhr(this.urlCorrect(url), data, "POST", "json");
        },
        // Загрузить данные с сервера JSON-данные
        dataLoader: function(url, params){
            params.format = 'json';
            return this.Ajax.xhr(this.urlCorrect(url), params, "GET", "json");
        },
        // Загрузить удаленный контент HTML-данные
        contentLoader: function(url, params){
            params.format = 'html';
            return this.Ajax.xhr(this.urlCorrect(url), params, "GET", "text");
        },
        // Очистить контейнер с контентом
        clear: function(node){
            while(node.firstChild) {
                node.removeChild(node.firstChild);
            }
            node.innerHTML = "";
        },
        // Добавить контент в контейнер
        placeContent: function(node, content) {
            node.innerHTML = content;
        },
        urlCorrect: function(url) {
            return appConfig.baseUrl + '/' + url;
        }
    });
});