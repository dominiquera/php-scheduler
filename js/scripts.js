var consultant_scheduler_titles = {'start_time': 'Start time','end_time': 'End time','add_interval': 'Add interval','data_saved': 'Data saved successfully'};
var _admin_consultant_scheduler = function () {

    this.init = function()
    {
        jQuery('.cs_day').on('click',function(){

            var cs_key = jQuery(this).val();

            if(jQuery(this).is(':checked'))
            {
                var lineClass = '.hours_interval_line_' + cs_key;

                if(jQuery(lineClass).html() == '')
                {
                     AdminConsultantScheduler.addHourInterval(lineClass,cs_key,{});
                }

                jQuery('.hours_interval_' + cs_key).slideDown();
            }
            else
            {
                jQuery('.hours_interval_' + cs_key).slideUp();
            }
        });
    }

    this.ID = function (length) {
        if (!length) {
            length = 8
        }
        var str = ''
        for (var i = 1; i < length + 1; i = i + 8) {
            str += Math.random().toString(36).substr(2, 10)
        }
        return ('_' + str).substr(0, length)
    }

    this.getHoursSelect= function(key,t,s)
    {
        var hours = '<select class="ui dropdown" name="cs_hours[' + key + '][' + t +'][]">';
        for(var i = 0; i < 24; i++)
        {
            var v = (i < 10 ? '0' + i : i);
            hours = hours +  '<option ' + (s == i ? 'selected' : '') + ' value="' + v  + '">' +  v + '</option>';
        }
        hours = hours + '</select>';

        return hours;
    }

    this.getMinutesSelect = function(key,t,s)
    {
        var minutes = '<select class="ui dropdown"  name="cs_minutes[' + key + '][' + t +'][]">';
        for(var i = 0; i < 60; i++)
        {
            var v = (i < 10 ? '0' + i : i);
            minutes = minutes +  '<option ' + (s == i ? 'selected' : '') + ' value="' + v  + '">' +  v + '</option>';
        }
        minutes = minutes + '</select>';

        return minutes;
    }

    this.addHourInterval = function (obj,key,sdata)
    {

        var lineHtml = '<div class="cs_line_wrapper ui segment">';

        var s_h,e_h,s_m,e_m;


        if(sdata != undefined)
        {
            if(sdata.begin != undefined)
            {
                var _begin = sdata.begin.split(':');
                s_h = _begin[0];
                s_m = _begin[1];
            }

            if(sdata.end != undefined)
            {
                var _end = sdata.end.split(':');
                e_h = _end[0];
                e_m = _end[1];
            }

        }

        lineHtml = lineHtml  + '<label>' + consultant_scheduler_titles.start_time + '</label>&nbsp;'  + AdminConsultantScheduler.getHoursSelect(key,'s',s_h) + '&nbsp;:&nbsp;' +  AdminConsultantScheduler.getMinutesSelect(key,'s',s_m);
        lineHtml = lineHtml + '&nbsp;&nbsp;';
        lineHtml = lineHtml  + '<label>' + consultant_scheduler_titles.end_time + '</label>&nbsp;'  + AdminConsultantScheduler.getHoursSelect(key,'e',e_h) + '&nbsp;:&nbsp;' +  AdminConsultantScheduler.getMinutesSelect(key,'e',e_m);

        lineHtml = lineHtml + '&nbsp;&nbsp;&nbsp;<button class="ui icon button" onclick="javascript:return AdminConsultantScheduler.removeHourInterval(this);"><i class="trash alternate icon"></i></button></div>';

        jQuery(obj).append(lineHtml );

          return false;

    }


    this.removeHourInterval = function(obj)
    {
        jQuery(obj).parent('.cs_line_wrapper').remove();
        return false;
    }

    this.removeExcludeDate = function(obj)
    {
        jQuery(obj).parent('.cs_exclude_date_wrapper').remove();
        return false;
    }

    this.addExcludeDate =  function()
    {

         jQuery('#exclude_dates').append('<div class="cs_exclude_date_wrapper ui segment"><span class="ui input"><input type="date" class="cs_exclude_date" name="cs_exclude_dates[]" /></span>&nbsp;<button class="ui icon button" onclick="javascript:return AdminConsultantScheduler.removeExcludeDate(this);"><i class="trash alternate icon"></i></button></div>');
          return false;
    }

    this.removeSingleDate = function(obj)
    {
        jQuery(obj).parent('.cs_single_date_wrapper').remove();
          return false;
    }

    this.addSingleDate =  function()
    {
        var newID = AdminConsultantScheduler.ID();
        var intervalBtn = '<button class="ui primary left labeled icon button button-primary " onclick="javascript:return AdminConsultantScheduler.addHourInterval(\'#' + newID + '\',\'' +  newID + '\',{});"  ><i class="plus icon"></i>' + consultant_scheduler_titles.add_interval +'</button><br />';
        jQuery('#single_dates').append('<div class="cs_single_date_wrapper ui segment"><span class="ui input"><input type="date" class="cs_single_date" name="cs_single_dates[' + newID + ']" /></span>&nbsp;<input type="button" onclick="javascript:return AdminConsultantScheduler.removeSingleDate(this);" class="ui button button-secondary" value="-" /><br /><div class="cs_single_date_hours ui segments" id="' + newID + '"></div><br />'  + intervalBtn +  '</div>');
        AdminConsultantScheduler.addHourInterval(jQuery('#' + newID),newID,{});
        return false;
    }

    this.updateData = function()
    {
       jQuery('#cs_msg').remove();
        $("html, body").animate({ scrollTop: 0 }, 600);
       jQuery.post(cs_ajax,
       jQuery('#cs_form').serialize(),
        function(response) {

          jQuery('#cs_form').before('<div id="cs_msg" class="ui positive message"><i class="close icon"></i><div class="header">' + consultant_scheduler_titles.data_saved + '</div></div>');
        });

        return false;
    }


}

var AdminConsultantScheduler = new _admin_consultant_scheduler();

jQuery(function(){
    AdminConsultantScheduler.init();
});
