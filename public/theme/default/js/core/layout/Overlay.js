define([
    "dojo/_base/declare",
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/dom-class",
    "dojo/dom-geometry",
    "dojo/_base/lang",
    "dojo/dom-style"
], function(declare, dom, domConstruct, domClass, domGeometry, lang, domStyle){

    var Overlay = declare(null, {
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
            domStyle.set(this.overlayNode, {position: 'relative'});

            var overlayLoading = domConstruct.create('div');
            domClass.add(overlayLoading, 'loader');
            domConstruct.place(overlayLoading, this.overlayNode, 'first');

        },
        /**
         * Отобразить Загрузчик
         */
        show: function(){
            this._resize();
            domConstruct.place(this.overlayNode, this.domNode, 'first');
        },
        /**
         * Скорректировать размеры Загрузчика
         * @private
         */
        _resize: function(){
            var geometryNode = domGeometry.getContentBox(this.domNode);
            domStyle.set(this.overlayNode.childNodes[0], {
                width:      geometryNode.w + 'px',
                height:     geometryNode.h + 'px'
            });
        },
        /**
         * Удалить DOM узел загрузчика из общего DOM-дерева
         */
        hide: function(){
            domConstruct.destroy(this.overlayNode);
        }
    });

    return Overlay;
});