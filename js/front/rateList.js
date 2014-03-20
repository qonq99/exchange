var rateList = {
    init : function(){
        this.container = $("#rates");
        var element = $( "#rate-price" );
        if(typeof(rateList.data.socket) !== 'undefined'){
            rateList.data.socket.on('setRate', function (data) {
                var initPrice = parseInt($('#rate-price').attr('init'));
                if(data.transportId == rateList.data.transportId) {
                    //var element = rateList.createElement(initPrice, data.date, data.name, data.price, data.surname);
                    
                    var price = data.price;
                    if(rateList.data.nds) {
                          price = Math.ceil(price * (100 + rateList.data.nds*100) / 100);
                    }
                    
                    var element = rateList.createElement(initPrice, data.date, price, '', data.company, data.name , data.surname);
                    //this.createElement(initPrice, rate.time, price, id, rate.company, rate.name, rate.surname);
                    $('#rates').prepend(element);
                }
            });

            rateList.data.socket.on('loadRates', function (data) {
                var obj = {
                    price: data.price,
                    time: data.date,
                    company: data.company,
                    name: data.name,
                    surname: data.surname,
                    with_nds: 0,
                    //userId: rateList.data.userId,
                    //transportId: rateList.data.transportId
                };
                rateList.add(obj);
            });

            /*rateList.data.socket.on('errorRate', function (data) {
                var error = $('#t-error');
                error.css('display', 'block');
                error.html('Ставка с ценой "' + data.price + '" уже была сделана');
            });*/
            /****** Сообещение *********/
            /*rateList.data.socket.on('onlineEvent', function (data) {
                $.onlineEvent({ msg : data.msg, className : 'classic', sticked:true, position:{right:0,bottom:0}, time:10000});
            });*/

            $( "#rate-up" ).on('click', function() {
                if($('#rate-down').hasClass('disabled'))$('#rate-down').removeClass('disabled');
                var newRate = parseInt(element.val()) + rateList.data.priceStep;// + rateList.data.priceStep * rateList.data.nds;
                element.val(newRate);
            });

            $( "#rate-up" ).mousedown(function(e) {
                clearTimeout(this.downTimer);
                this.downTimer = setInterval(function() {
                    $( "#rate-up" ).trigger('click');                
                }, 150);
            }).mouseup(function(e) {
                clearInterval(this.downTimer);
            });

            $( "#rate-down" ).on('click', function() {              
                var step = rateList.data.priceStep;// + rateList.data.priceStep * rateList.data.nds;
                var newRate = element.val() - step;
                if(newRate > 0) element.val(newRate);
                if( (newRate - step) <= 0 ) {
                    $(this).addClass('disabled');
                }
            });

            $( "#rate-down" ).mousedown(function(e) {
                clearTimeout(this.downTimer);
                this.downTimer = setInterval(function() {
                    $( "#rate-down" ).trigger('click');                
                }, 150);
            }).mouseup(function(e) {
                clearInterval(this.downTimer);
            });

            $( ".r-submit" ).click(function() {
                if(!$(this).hasClass('disabled')) {
                    $('#setPriceVal').text(parseInt($( "#rate-price" ).val()));
                    $("#addRate").dialog("open");
                }
            });

            $('#setRateBtn').live('click', function() {
                $('#addRate').dialog('close');

                if(rateList.data.defaultRate) $('#rates').html('');
                $('#t-error').html('');
           
                var price = parseInt($('#rate-price').val());
                if(rateList.data.nds) {
                    price = price * 100/(100 + rateList.data.nds*100);
                }
                        //console.log('цена = ' + price);
                //console.log('before - ' + price);
                //if(price%10 != 0) price = Math.round(price/10) * 10;
                //console.log('after - ' + price);
                
                
                // убрать аттрибут init !!!
                $(this).attr('init', price);

                var time = getTime();
                //console.log(time);

                /*var obj = {
                    price: price,
                    date: time,
                    name: rateList.data.name,
                    surname: rateList.data.surname,
                    userId: rateList.data.userId,
                    transportId: rateList.data.transportId
                };*/

                rateList.data.socket.emit('setRate',{
                    transportId: rateList.data.transportId,
                    userId: rateList.data.userId,
                    company: rateList.data.company,
                    name : rateList.data.name, 
                    surname: rateList.data.surname,
                    price : price,
                });   
            });

            $('#rate-price').blur(function(){
                var inputVal = parseInt($(this).val());

                //if(inputVal < parseInt($(this).attr('init'))) {
                    var kratnoe = rateList.data.priceStep;
                    var residue = inputVal % kratnoe;
                    if(residue != 0) {
                        if((inputVal - residue) > 0) $(this).val(inputVal - residue);
                        else $(this).val(kratnoe);
                        inputVal = parseInt($(this).val());
                    }
                    
                    if((parseInt($(this).val()) - kratnoe) <= 0) $('#rate-down').addClass('disabled');

                    /* if(inputVal - kratnoe < kratnoe){
                        $('#rate-down').addClass('disabled');
                    }

                    if(inputVal < parseInt($(this).attr('init'))){
                        $('#rate-up').removeClass('disabled');
                        $('.r-submit').removeClass('disabled');
                    }*/
               /* } else {
                    $(this).val($(this).attr('init'));
                    //if(!rateList.data.defaultRate) $('.r-submit').addClass('disabled');
                }*/
            });

            $(document).keypress(function(e) {
                if (e.which == 13) {
                    $( "#rate-price" ).trigger('blur');
                }
            });
        } else { // load with ajax rates for admin and logist
            rateList.load(this.container);
        }        
    },
    update : function(posts, price, userName) {
        if (this.container.length > 0) {
            price = typeof price !== 'undefined' ? price : '';
            $.ajax({
                type: 'POST',
                url: '/transport/updateRates',
                dataType: 'json',
                data:{
                    id: this.data.transportId,
                    newRate: price,
                    step: this.data.step,
                },
                success: function(rates) {
                    if(rates.all.length) {
                        rateList.data.socket.emit('setRate',{
                             userName : userName, 
                             price : price
                        });   
                    } else {
                        rateList.container.html('<span>Нет предложений</span>');
                    }
            }});
        }
    },
    load : function(posts) {
        if (this.container.length > 0) {
            $.ajax({
                type: 'POST',
                url: '/transport/updateRates',
                dataType: 'json',
                data:{
                    id: this.data.transportId,
                    newRate: '',
                    step: this.data.step,
                },
                success: function(rates) {
                    /*
                    if(rates.error) {
                        var error = $('#t-error');
                        error.css('display', 'block');
                        error.html('Ставка с ценой "' + $( "#rate-price" ).val() + '" уже была сделана');
                    }
                    */
                   
                    if(rates.all.length) {
                        var container = $("#rates");
                        var height = 49;
                        var count = 0;
                        var scrollBefore = container.scrollTop();
                        if(scrollBefore) count = scrollBefore/height;
                        
                        rateList.container.html('');
                        var initPrice = parseInt($('#rate-price').attr('init'));
                        $.each( rates.all, function( key, value ) {
                            rateList.add(value, initPrice);
                        });

                        if(scrollBefore){
                            container.scrollTop(height * (count + 1));
                        }
                        
                        if(rates.price) {
                            var value = parseInt(rates.price);// - (rateList.data.priceStep + rateList.data.priceStep * rateList.data.nds);
                            if(rateList.data.nds){
                               value += value * rateList.data.nds;
                            }
                            var step = rateList.data.priceStep + rateList.data.priceStep * rateList.data.nds;
                            
                            var price = $("#rate-price");
                            /*if(price.val() > value && value > 0) {
                                price.val(value);
                                price.attr('init', value);
                                $( "#rate-up" ).addClass('disabled');
                            }*/
                            
                            //var prevValue = value - (rateList.data.priceStep + rateList.data.priceStep * rateList.data.nds);         
                            //if(prevValue < 0) $( "#rate-down" ).addClass('disabled');
                           // $('#last-rate').html('<span>' + rates.price + rateList.data.currency + '</span>');

                            /*if(prevValue <= 0) {
                                //$('.r-submit').addClass('disabled');
                                $('.r-block').slideUp("slow");
                                $('.r-submit').slideUp("slow");
                                $('#t-container').html('<span class="t-closed">Перевозка закрыта</span>');
                                rateList.data.status = true;
                            }*/
                        } 
                    } else {
                        rateList.container.html('<span>Нет предложений</span>');
                    }
            }});
        }
    },
    add : function(rate, initPrice) {
        var time = '';
        var id = 0;
        var price = parseInt(rate.price);
        price = Math.ceil(price + price * this.data.nds);

        if (rate.id) id = rate.id;
        var element = this.createElement(initPrice, rate.time, price, id, rate.company, rate.name, rate.surname, parseInt(rate.with_nds), parseInt(rate.price));
        
        this.container.prepend(element);
    },
    createElement : function(initPrice, date, price, id, company, name, surname, nds, ratePrice) {
        if(initPrice < price){
            $('#rate-price').attr('init', price);
        }
        var newElement = "<div class='rate-one'>";
        
        if(id) {
            newElement = "<div id='" + id + "' class='rate-one'>";
        } 
        
        newElement += "<div class='r-o-container'>" + 
                "<span>" + date + "</span>" + 
                "<div class='r-o-user'>" + company;
        
        newElement += "</div>" +
            "</div>"
        ;
        
        if(nds){
            var withNds = Math.ceil(ratePrice + ratePrice * rateList.data.ndsValue);
            newElement += "<div class='price-container'>" + 
                "<div class='r-o-price'>" + price + rateList.data.currency + 
                "</div>" +
                "<div class='r-o-nds'>" + '(c НДС: ' + withNds + rateList.data.currency + ') '+ 
                "</div>" +
            "</div>";
        } else {
            newElement += "<div class='r-o-price'>" + price + rateList.data.currency + "</div>";
        }
        newElement += "</div>";
        
        return newElement;
    },
    getContainerHeight : function(){
        var h=0;
        this.container.find('.rate-one').each(function(k){
            h += $(this).outerHeight();
        });
        return h;
    }
};