dojo.provide("core.widgets.SearchCompanyDialog");
require([
    "dojo/_base/lang",
    "dojo/_base/fx"
], function(lang, fx){
    core.widgets.SearchCompanyDialog = function(){};
    dojo.declare("core.widgets.SearchCompanyDialog", null, {
        constructor: function(LayoutInstance, HandlebarsInstance){
            this.Layout = LayoutInstance;
            this.DialogBox = new dijitDialog();
            this.Handlebars = HandlebarsInstance;
            var that = this;
            // Загрузка содержимого
            that.Layout.load({
                url:    appConfig.baseUrl + '/manager/billing/search-company',
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
        _searchRequest: function(formNode){
            var that = this;
            that.Layout.load({
                url: appConfig.baseUrl + '/service/query/company',
                format: 'json',
                args: {'filters[equal][inn][]': formNode['company[inn]'].value, 'filters[equal][kpp][]': formNode['company[kpp]'].value}
            }).then(function(response){
                    that._showRequestResults(new storeMemory({data: response.data}));
                });
        },

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