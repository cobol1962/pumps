api = {
        call: function(endpoint, cb, params, ajaxExtend) {
      //      $.LoadingOverlay("show");
            var ths = this;

            cb = cb || function(res) {};
            params = params || {};
            ajaxExtend = ajaxExtend || {};

            // Extend the data object load sent with API Ajax request.
            var data = {};

            for (var prop in params) {
                if (params.hasOwnProperty(prop)) {
                 data[prop] = params[prop];
              }
            }


               var apiAjax = {
                type: "POST",
                dataType: "json",
                async: false,
                data: data,
                success: function(res) {
                  if (res.status == "ok") {
                      cb(res);
                  } else {
                    cb(res);
                  }
                },
                error: function(e) {

                }
              };

              apiAjax.url =  "http://samygroups.co.uk/api/" + endpoint;
              for (var prop in ajaxExtend) {
                if (ajaxExtend.hasOwnProperty(prop)) {
                  apiAjax[prop] = ajaxExtend[prop];
                }
              }
              // Make the call.
              $.ajax(apiAjax).fail(function(jqXHR, textStatus, errorThrown) {

                              //    $("#number_no_exists").html(  $("#number_no_exists").html() + "<br>" + JSON.stringify(data));
                /*    alert( JSON.stringify(jqXHR) );
                    alert( textStatus );
                    alert( errorThrown );*/
                });
        },
        isEmpty: function(obj) {
          for(var key in obj) {
            if(obj.hasOwnProperty(key))
              return false;
          }
          return true;
    }
}
