function ЕditTransport() {
    this.initCalendar = function() {
        $.datepicker.regional['ru'] = {
            closeText: 'Закрыть',
            prevText: '&#x3c;Пред',
            nextText: 'След&#x3e;',
            currentText: 'Сегодня',
            monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
            monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'],
            dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
            dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
            dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
            dateFormat: 'dd.mm.yy',
            firstDay: 1,
            isRTL: false,
        };
        $.datepicker.setDefaults($.datepicker.regional['ru']);
		
        $( "#TransportForm_date_close" ).datetimepicker({
            dateFormat: 'dd-mm-yy',
            timeFormat: 'HH:mm',
        });
        $( "#TransportForm_date_from" ).datetimepicker({
            dateFormat: 'dd-mm-yy',
            timeFormat: 'HH:mm',
        });
        
        $( "#TransportForm_date_to" ).datetimepicker({
            dateFormat: 'dd-mm-yy',
            timeFormat: 'HH:mm',
        });
		
		$( "#TransportForm_date_to_customs_clearance_RF" ).datetimepicker({
            dateFormat: 'dd-mm-yy',
            timeFormat: 'HH:mm',
        });
		
    };
    
    this.showFieldsForInternational = function(){
	    if($('#TransportForm_type').val() == 0){
           $('#TransportForm_customs_clearance_EU').parent().removeClass('hide');
           $('#TransportForm_customs_clearance_RF').parent().removeClass('hide');
           $('#TransportForm_date_to_customs_clearance_RF').parent().removeClass('hide');
           $('#TransportForm_currency').val(2);
        } else {
           $('#TransportForm_customs_clearance_EU').parent().addClass('hide');
           $('#TransportForm_customs_clearance_RF').parent().addClass('hide');
           $('#TransportForm_date_to_customs_clearance_RF').parent().addClass('hide');
           $('#TransportForm_currency').val(0);
        }
	};
	
    this.initRateEditor = function(){
        $("#rates-all").on('dblclick', 'li span.price', function () {     
            $(this).parent().addClass("clicked");
            var origPrice = $(this).text();
            $(this).attr('pval', origPrice)
            $(this).text("");
            $('<input>', {
                type: 'text',
                value: origPrice,
            }).appendTo(this).focus();
        });
        
        $("#rates-all").on('focusout', 'li span.price > input', function () {
            var newVal = $(this).val();
            var parent = $(this).parent();
            if (newVal == '') newVal = parent.attr('pval');
            parent.text(newVal);
            parent.next().val(newVal);
            $(this).remove(); 
        });
        
        $("#rates-all").on('click', 'li span.del-row', function () {
            $(this).parent().remove();
        });
        /*************************************/
        /*$("#points-all").on('dblclick', 'li span.p-point', function () {     
            $(this).parent().addClass("clicked");
            var origPrice = $(this).text();
            $(this).attr('pval', origPrice)
            $(this).text("");
            $('<input>', {
                type: 'text',
                value: origPrice,
            }).appendTo(this).focus();
        });
        
        $("#points-all").on('focusout', 'li span.p-point > input', function () {
            var newVal = $(this).val();
            var parent = $(this).parent();
            if (newVal == '') newVal = parent.attr('pval');
            parent.text(newVal);
            parent.next().val(newVal);
            $(this).remove(); 
        });
        
        $("#points-all").on('click', 'li span.del-row', function () {
            $(this).parent().remove();
        });
        */
       
       
        /* press Enter button*/
        $(document).keypress(function(e) {
            if(e.which == 13) {
                var element = $( ".clicked" );
                element.find( "input" ).focusout();
                element.removeClass("clicked");
            }
        });
        
         /* tooltip for points */
        
        
    };
}
    