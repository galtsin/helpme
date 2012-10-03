dojo.provide("app.model.Level");
require(["app/Model"], function(Model){
    app.model.Level = function(){};
    dojo.declare("app.model.Level", Model, {
        _createEntity: function(identity){
            var k = function(id){
                this.getInfo = function(){
                    return Sandbox.Layout.contentLoader('manager/counseling-structure/edit-line', {line: 14});
                };
            };
            return new k(identity);
        }
    });
});