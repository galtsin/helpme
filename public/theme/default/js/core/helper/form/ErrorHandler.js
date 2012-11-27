define([
    "dojo/_base/declare", // declare
    "dojo/dom",
    "dojo/_base/array",
    "dojo/_base/lang",
    "dojo/query"
], function(declare, dom, array, lang, query){
    var ErrorHandler = declare(null,{
        constructor: function(formNode){
            this._formNode = formNode;
            this._responseMessages = {};
            this._anchors = [];
            this._tooltip = new Tooltip();

            if(!formNode) throw new Error('Не выбран узел node');
            this._init = {
                node: formNode,
                tooltip: new Tooltip()
            };

        }
    });

    return ErrorHandler;
});