dojo.provide("app.model.Line");
require(["app/Model"], function(Model){
    app.model.Line = function(){};
    dojo.declare("app.model.Line", Model, {
        _createEntity: function(identity){

            //Сущность
            var Entity = function(identity){
                var id = identity;
                var that = this;
                this.getInfo = function(){
                    return Sandbox.Layout.contentLoader('manager/counseling-structure/edit-line', {line: id});
                };
                this.saveInfo = function(){

                };
                this.getLevels = function(){
                    return Sandbox.Layout.contentLoader('manager/counseling-structure/get-levels', {line: id});
                };
                this.getTariffs = function(){

                };
            };

            return new Entity(identity);
        }
    });
});