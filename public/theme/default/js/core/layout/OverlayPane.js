define([
    "dojo/_base/declare",
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/dom-class",
    "dojo/dom-geometry",
    "dojo/_base/lang",
    "dojo/dom-style",
    "dijit/layout/_LayoutWidget"
], function(declare, dom, domConstruct, domClass, domGeometry, lang, domStyle, _LayoutWidget){

    var OverlayPane = declare("OverlayPane", [_LayoutWidget], {
        // Задержка перед удалением оверлея. Визуальное восприятие
        delay: 600,
        // Созданный внутренний DOM узел
        overlayNode: null,
        buildRendering: function(){
            this.inherited(arguments);
            this.overlayNode = domConstruct.create('div', {
                style: {position: 'absolute'},
                class: 'loader'
            });
        },
        /**
         * Отобразить Загрузчик
         */
        show: function(){
            domConstruct.place(this.overlayNode, this.domNode, 'first');
            this.resize(); // Пересчитать размер
        },
        /**
         * Скорректировать размеры Загрузчика
         * @private
         */
        layout: function(){
            var geometryDomNode = domGeometry.getContentBox(this.domNode);
            domStyle.set(this.overlayNode, 'height', geometryDomNode.h + 'px');
            domStyle.set(this.overlayNode, 'width', geometryDomNode.w + 'px');
        },
        /**
         * Удалить DOM узел загрузчика из общего DOM-дерева
         */
        hide: function(){
            var _OverlayPane = this;
            setTimeout(function(){
                domConstruct.destroy(_OverlayPane.overlayNode);
            }, this.delay);
        }
    });

    return OverlayPane;
});