/* Набор базовых функций */
dojo.provide("core.sandbox.Base");
require([], function(){
    core.sandbox.Base = function(){};
    dojo.declare("core.sandbox.Base", null, {
        constructor: function() {

        },
        // Очистить данные от перевода строк
        clearnl: function(text){
            return text.replace(/[\n\r\t]*/g, '');
        },
        // Показать элемент
        show: function(element) {
            if(dojo.hasClass(element, "hidden")) dojo.removeClass(element, "hidden");
        },
        // Скрыть элемент
        hide: function(element) {
            if(!dojo.hasClass(element, "hidden")) dojo.addClass(element, "hidden");
        },
        // Переключить элемент
        toggle: function(node) {
            dojo.hasClass(node, "hidden") ? dojo.removeClass(node, "hidden") : dojo.addClass(node, "hidden");
        },
        // Распарсить JavaScript код в подгружаемых html-блоках
        jsParse: function(content) {
            clearnl(content);
            return content.match(/<script type="text\/javascript">([.\s\S]*)<\/script>/)[1];
        },
        // Выполнить JavaScript
        jsExecute: function(jsContent) {
            if(jsContent.length > 0) {
                try{
                    jsContent.replace(/[\s]*/g,'');
                    eval(jsContent);
                } catch(ex) {}
            }
        },
        //Создание объекта на основе индексов массива
        indexing: function(argsArray, argsIndex){
            var argsObject = {};
            for(var i = 0; i < argsIndex.length; i ++) {
                argsObject[argsIndex[i]] = argsArray[i];
            }
            return argsObject;
        }
    });
});