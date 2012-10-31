/* Помощники */
dojo.provide("core.helper.Form");
require([], function(){
    core.helper.Form = function (){};
    dojo.declare("core.helper.Form", null, {
        checkboxCheckedValues: function(/*Array|NodeCheckbox*/ field) {
            // Проверить тип ноды - checkbox
            var checked = [];
            if (field) {
                if(field.type == 'checkbox')
                if('length' in field) {
                    for(var i = 0; i < field.length; i ++) {
                        this._checked(field[i]);
                    }
                } else {
                    this._checked(field);
                }
            }

            this._checked = function(field) {
                if(field.type == 'checkbox') {
                    if(field.checked) checked.push(field.value);
                }
            };

            return checked;
        }
    });
});