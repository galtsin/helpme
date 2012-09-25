/* Помощники */
dojo.provide("core.sandbox.helpers.Form");
require([], function(){
    core.sandbox.helpers.Form = function(){};
    dojo.declare("core.sandbox.helpers.Form", null, {
        getCheckedValues: function(/*Array|Node*/ node) {
            // Проверить тип ноды - checkbox
            var checked = [];
            if (node != undefined) {
                // isArray?
                if('length' in node) {
                    for(var i = 0; i < node.length; i ++) {
                        if(true === node[i].checked) checked.push(node[i].value);
                    }
                } else {
                    if(node.checked) checked.push(node.value);
                }
            }
            return checked;
        }
    });
});