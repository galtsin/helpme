define([
    "dojo/_base/declare",
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/dom-class",
    "dojo/dom-geometry",
    "dojo/_base/lang",
    "dojo/dom-style",
    "dijit/_WidgetBase"
], function(declare, dom, domConstruct, domClass, domGeometry, lang, domStyle, _WidgetBase){

    var Overlay = declare(null, {
        // Задержка перед удалением оверлея. Визуальное восприятие
        delay: 600,
        // Родительский DOM узел
        domNode: null,
        // Созданный внутренний DOM узел
        overlayNode: null,
        /**
         * Инициализация
         * @param options
         */
        constructor: function(options){
            lang.mixin(this, options);
            if(!this.domNode) throw new Error('Не выбран узел родительский узел');

            this.overlayNode = domConstruct.create('div');
            domStyle.set(this.overlayNode, {position: 'relative', width: 'auto'});

            var overlayLoading = domConstruct.create('div');
            domClass.add(overlayLoading, 'loader');
            domConstruct.place(overlayLoading, this.overlayNode, 'first');

        },
        /**
         * Отобразить Загрузчик
         */
        show: function(){
            domConstruct.place(this.overlayNode, this.domNode, 'first');
            this._resize(); // Пересчитать новый размер
        },
        /**
         * Скорректировать размеры Загрузчика
         * @private
         */
        _resize: function(){
            var geometryDomNode = domGeometry.getContentBox(this.domNode);
            //if(geometryDomNode.h > domStyle.get(this.overlayNode.childNodes[0], 'height')){
                domStyle.set(this.overlayNode.childNodes[0], 'height', geometryDomNode.h + 'px');
            //}
            //if(geometryDomNode.w > domStyle.get(this.overlayNode.childNodes[0], 'width')){
                domStyle.set(this.overlayNode.childNodes[0], 'width', geometryDomNode.w + 'px');
            //}
        },
        /**
         * Удалить DOM узел загрузчика из общего DOM-дерева
         */
        hide: function(){
            var Overlay = this;
            setTimeout(function(){
                domConstruct.destroy(Overlay.overlayNode);
            }, this.delay);
        },
        setDefaultSize: function(){

        }
    });

    return Overlay;
});