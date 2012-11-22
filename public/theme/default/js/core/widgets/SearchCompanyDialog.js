define([
    "require",
    "dojo/_base/declare", // declare
    "dojo/dom",
    "dojo/html",
    "dojo/dom-construct",
    "dojo/dom-attr",
    "dojo/dom-form",
    "dojo/_base/array",
    "dojo/on",
    "dojo/_base/lang",
    "dojo/query",
    "dojo/store/Memory"
], function(require, declare, dom, domHtml, domConstruct, domAttr, domForm, array, on, lang, query, storeMemory){
    return declare(null, {
        /**
         * Инициализация Виджета
         * @param LayoutInstance
         * @param DialogInstance
         * @param HandlebarsInstance
         */
        constructor: function(LayoutInstance, DialogInstance, HandlebarsInstance){
            this.Layout = LayoutInstance;
            this.Handlebars = HandlebarsInstance;
            this.DialogBox = DialogInstance;
        },
        load: function(){
            var that = this;
            // Загрузка содержимого
            that.Layout.load({
                url:    that.Layout.baseUrl('manager/billing/search-company'),
                format: 'html'
            }).then(function(response){
                    that.DialogBox.set('title', 'Поиск компании');
                    that.DialogBox.set('content', response);
                    that.DialogBox.show();
            }).then(function(){
                var formNode = dom.byId('search-company');
                on(formNode['send'], 'click', function(){
                    that._searchRequest(formNode);
                });
            });
        },
        /**
         * Поиск результатов
         * @param formNode
         * @private
         */
        _searchRequest: function(formNode){
            var that = this;
            that.Layout.load({
                url: that.Layout.baseUrl('service/query/company'),
                format: 'json',
                args: {'filters[equal][inn][]': formNode['company[inn]'].value, 'filters[equal][kpp][]': formNode['company[kpp]'].value}
            }).then(function(response){
                    that._showRequestResults(new storeMemory({data: response.data}));
            });
        },
        /**
         * Отобразить результаты поиска
         * @param storeResults
         * @private
         */
        _showRequestResults: function(storeResults){
            var that = this;
            if(storeResults.data.length > 0) {
                var tbody = query('tbody', dom.byId('search-results'))[0];
                domHtml.set(tbody, that.Handlebars.compile(dojo.byId("search-results-items").innerHTML)({items: storeResults.data}));
                query('a', tbody).on('click', function(event){
                    event.preventDefault();
                    that.DialogBox.hide();
                    var selectCompany = event.target.getAttribute('data-action').split('/')[1];
                    that.onSelect(storeResults.get(selectCompany));
                });
            }
        },
        onSelect: function(data){
            // callback
        }
    });
});