define([
    "dojo/_base/declare", // declare
    "dijit/Dialog",
    "dojo/dom",
    "dojo/_base/array",
    "dojo/_base/lang",
    "dojo/query",
    "core/Ajax"
], function(declare, DialogBox, dom, array, lang, query, Ajax){
    var SearchCompany = declare([DialogBox], {
        onShow: function(){
            // Url загрузка
            // Создать DIV
            this.set('content', 'Загрузка данных');

            Ajax.load('http://192.168.1.51/default/index/ajax', {
                handleAs: 'text'
            })
        }
    });



    return SearchCompany;
});