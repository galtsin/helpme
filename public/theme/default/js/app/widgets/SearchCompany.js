define([
    "dojo/_base/declare", // declare
    "dijit/Dialog",
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/_base/array",
    "dojo/_base/lang",
    "dojo/query",
    "core/layout/Overlay",
    "core/Ajax",
    "dojo/dom-style"
], function(declare, DialogBox, dom, domConstruct, array, lang, query, Overlay, Ajax, domStyle){
    var SearchCompany = declare([DialogBox], {
        onShow: function(){
            // Url загрузка
            // Создать DIV
            var container = domConstruct.create('div');
            domStyle.set(container, {
                width: '250px',
                height: '100px',
                background: '#fff'
            });
            this.set('content', container);
            var v = new Overlay({
               domNode: this.containerNode
            });
            var that = this;
            Ajax.load('http://192.168.1.51/default/index/ajax', {
                handleAs: 'text',
                overlay: v
            }).then(function(response){
                that.set('content', response);
            });
        }
    });



    return SearchCompany;
});