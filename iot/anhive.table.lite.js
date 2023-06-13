/** 
 * @file anhive.table.lite.js
 * @brief .
 * @author       AnHive Co., Ltd. anhive@gmail.com
 * @date         2017~2023
**/
var new_row = function(table){
    var tbl = document.querySelector('table[id="'+table+'"]');
    var a = tbl.querySelector('[data-form="sample"]')
    var cl = a.cloneNode(true);
    
    cl.style.display=""
    cl.setAttribute("data-form", "cloned")
    tbl.appendChild(cl)
    return cl;
}

var set_row = function(cl, data){
    tds = cl.getElementsByClassName('data-text');
    for (var oi = 0; oi < tds.length; oi += 1) {
        if (data[tds[oi].getAttribute("data-id")] !== undefined){
            if(oi == 7){
                var url = data[tds[7].getAttribute("data-id")];
                var img = document.createElement('img');
                img.src = url;
                img.style.width = '200px';
                img.style.height = 'auto';
                tds[oi].innerHTML = '';
                tds[oi].appendChild(img);
            }
            else{
                tds[oi].innerHTML = data[tds[oi].getAttribute("data-id")];
            }
        }
        else
            tds[oi].innerHTML = '';
    }
}

var fill_table = function(table, rows){
        for (ui=0;ui<rows.length;ui++) {
            cl = new_row(table)
            set_row(cl, rows[ui])
        }
}

var fill_column = function(table, col, value){
        obj = document.getElementById(table)
        tds = obj.getElementsByClassName('data-text');
        for (ui=0;ui<tds.length;ui++) {
            if ( (tds[ui].getAttribute("data-id") == col)
                 && (tds[ui].innerHTML == ""))
                tds[ui].innerHTML =  value
            continue;
        }
}

var reset_table = function(table){
    var tbl = document.querySelector('table[id="'+table+'"]');
    while(trs = tbl.querySelector('[data-form="cloned"]')){
        trs.parentNode.removeChild(trs)
    }
}

var get_celldataintable = function(key, obj, intag){
    while (obj.nodeName != intag.toUpperCase())
        obj = obj.parentNode
        
    tds = obj.getElementsByClassName('data-text');
    for (var oi = 0; oi < tds.length; oi += 1) {
        if (tds[oi].getAttribute("data-id") == key)
            return tds[oi].innerHTML
    }
    return "";
}
    
var tabletojson = function(table) {
    var rows = [];
    var tbl = document.querySelector('table[id="'+table+'"]');
    idx = 0;
    trs = tbl.getElementsByTagName("tr")
    for (ti = 0; ti < trs.length; ti++) {
        if (trs[ti].getAttribute("data-form") != "cloned") continue;
        tds = trs[ti].getElementsByClassName('data-text');
        rows[idx] = {}
        for (var oi = 0; oi < tds.length; oi += 1) {
            rows[idx][tds[oi].getAttribute("data-id")] = tds[oi].innerHTML
        }
        idx++;
    }
    return JSON.stringify(rows)
}

var rowtojson  = function(obj){
    while(obj.nodeName!="TR") obj=obj.parentNode
    tds = obj.getElementsByClassName('data-text');
    data = {}
    for (var oi = 0; oi < tds.length; oi += 1) {
        if (tds[oi].getAttribute("data-id") !== undefined)
            data[tds[oi].getAttribute("data-id")] = tds[oi].innerHTML
    }
    
	rows = [data];
    return JSON.stringify(rows);
}

var divtojson = function(divId) {
    var div = document.getElementById(divId);
    var inputs = div.getElementsByTagName('input');
    var data = {};
  
    for (var i = 0; i < inputs.length; i++) {
      var input = inputs[i];
      var inputId = input.getAttribute('id');
      var inputType = input.getAttribute('type');

      if (inputType === 'text') {
        var inputValue = input.value;
        data[inputId] = inputValue;
      }
    }
    rows = [data]

    //return JSON.stringify(rows);
    return rows;
}
