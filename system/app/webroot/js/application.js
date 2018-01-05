var application;

$(function (){

    if ($('html').hasClass('no-js')) return;

    application = {
        windowWidth : $(window).width(),
        windowHeight: $(window).height(),
        isMobile    : false,
        pathname    : window.location.pathname,
        webroot     : '',
        urlbase     : '',
        parent      : window.parent.document,

        init            : function (){
            var isMobile = (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));
            if (isMobile) {
                $('body').addClass('isMobile');
                application.isMobile = true;
            }

            application.setEvents();
            application.pagesBuild();
			application.setMask();
        },
        responsiveIframe: function (){
            var iframe = $('iframe#quoteIframe', application.parent);
            if (iframe.length > 0) {
                iframe.height($('body').height());
            }
        },
        setEvents       : function (){
            $(window)
                .on('scroll', application.onScroll)
                .on('load', application.onLoad)
                .on('ready', application.onReady)
                .on('resize', application.onResize);

            application.setToastr();

            $('*').on('click', function (){
                application.responsiveIframe();
            });

            $('.confirm').confirm({
                text: 'Dados relacionados poderam ser perdidos, você realmente deseja executar esta ação?',
                title: "Confirmação necessária",
                confirmButton: "Sim",
                cancelButton: "Não"
            });

            //- loading padrão para submit form
            $('form').on('submit', function (e){
                application.pageLoading(true);

                if ($.SubmitForm != undefined && $.SubmitForm == false) {
                    application.pageLoading(false);
                }
            });

            $(".chosen-select").chosen({ width:"100%" });

            //- --------------------------------------------------------------------------------------------------------
            //- Buscador de Endereço por CEP na base dos correios

            $('.find_cep').on('keyup', function (e){
                switch (e.keyCode) {
                    case 13:
                        return false;
                        break;
                    case 17:
                        return false;
                        break;
                    case 35:
                        return false;
                        break;
                    case 36:
                        return false;
                        break;
                    case 37:
                        return false;
                        break;
                    case 38:
                        return false;
                        break;
                    case 39:
                        return false;
                        break;
                    case 40:
                        return false;
                        break;
                    case 46:
                        return false;
                        break;
                    case 67:
                        return false;
                        break;
                    case 86:
                        return false;
                        break;
                    default:
                }

                var cep = $(this).val().replace('_', '');

                if (cep.length == 9) {

                    application.pageLoading(true);

                    if($(this).attr('number_parents') != 'undefined' && $(this).attr('number_parents') != undefined){
                        if(parseInt($(this).attr('number_parents')) == 1){
                            var find_cep_parent = $(this).parent();

                        }
                        if(parseInt($(this).attr('number_parents')) == 2){
                            var find_cep_parent = $(this).parent().parent();

                        }
                        if(parseInt($(this).attr('number_parents')) == 3){
                            var find_cep_parent = $(this).parent().parent().parent();

                        }
                        if(parseInt($(this).attr('number_parents')) == 4){
                            var find_cep_parent = $(this).parent().parent().parent().parent();

                        }
                        if(parseInt($(this).attr('number_parents')) == 5){
                            var find_cep_parent = $(this).parent().parent().parent().parent().parent();

                        }
                    }

                    var icon_spinner = $(this).parent().find('.icon-spinner');

                    icon_spinner.css('display', 'inline-block');
                    toastr.info('Consultando informações do cep.');

                    var formData = {
                        cep: cep
                    };

                    $.post(application.webroot + "Webservices/findAddressOfCep/", formData, function (data){
                        //console('dados cep', data);

                        if (parseInt(data.success) == 1) {

                            if(find_cep_parent.find('.find_cep').first().attr('number_parents') != 'undefined' && find_cep_parent.find('.find_cep').first().attr('number_parents') != undefined){
                                find_cep_parent.find('.autocomplete_address').first().val(data.address);
                                find_cep_parent.find('.autocomplete_neighborhood').first().val(data.neighborhood);
                                find_cep_parent.find('.autocomplete_city').first().val(data.city);
                                find_cep_parent.find('.autocomplete_address_completion').first().val(data.address_completion);
                                find_cep_parent.find('.autocomplete_number').first().val(data.number);

                                var state_id = find_cep_parent.find('.autocomplete_state').first().parent().find('select').attr('id');
                                $("#" + state_id + " option").filter(function (){
                                    return $(this).val() == data.state;
                                }).attr('selected', true);
                                //$("#" + state_id).select2('destroy').select2({allowClear: true});

                                find_cep_parent.find('.autocomplete_number').first().focus();
                            }else{
                                $('.autocomplete_address').val(data.address);
                                $('.autocomplete_neighborhood').val(data.neighborhood);
                                $('.autocomplete_city').val(data.city);
                                $('.autocomplete_address_completion').val(data.address_completion);
                                $('.autocomplete_number').val(data.number);

                                var state_id = $(".autocomplete_state").parent().find('select').attr('id');
                                $("#" + state_id + " option").filter(function (){
                                    return $(this).val() == data.state;
                                }).attr('selected', true);
                                //$("#" + state_id).select2('destroy').select2({allowClear: true});

                                $('.autocomplete_number').focus();
                            }

                            toastr.success('Endereço encontrado.');
                        } else {
                            toastr.warning('Endereço não encontrado');
                        }

                        icon_spinner.hide();
                        application.pageLoading(false);
                    }, 'json').fail(function (jqXHR, textStatus, errorThrown){
                        toastr.options = {"closeButton": true, "timeOut": "70000", "progressBar": true}
                        toastr.error('Falha de comunicação com servidor, verifique sua conexão e tente novamente');
                        toastr.options = {}

                        icon_spinner.hide();
                        application.pageLoading(false);
                    });
                }
            });

            $(document).ready(function () {
                $('.i-checks').iCheck({
                    checkboxClass: 'icheckbox_square-green',
                    radioClass: 'iradio_square-green',
                });
            });

            $('.datepicker').datepicker({
                format: 'dd/mm/yy',
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true
            });
            //- --------------------------------------------------------------------------------------------------------

			/*
			$(document).ready(function() {
			  $('.select2').select2();
			});
			*/
        },

        /*
		set_select2: function () {
			$(".select2").each(function (e) {
				if ($(this).is('[multiple]')) {
					$(this).select2({
						allowClear: true,
						placeholder: "selecione os itens que deseja processar",
						tags: true
					});
				}

				if (!$(this).is('[multiple]')) {
					$(this).select2({
						allowClear: true,
						placeholder: "selecione um item",
						tags: true
					});
				}
			});
		},
		*/

        onScroll        : function (){
            //var xPos = $(document).scrollLeft();
            //application.responsiveIframe();
        },
        onLoad          : function (ev){
            application.onResize();
            application.pageLoading(false);
        },
        onReady         : function (ev){
            //application.onLoad(ev);
            application.responsiveIframe();
        },
        onResize        : function (){
            application.windowWidth = $(window).width();
            application.windowHeight = $(window).height();

            var sq = Math.ceil(application.windowWidth * 1.5);
            var leftOffset = Math.ceil(((sq - application.windowWidth ) / 2) * -1);
            var topOffset = Math.ceil(((sq - application.windowHeight ) / 2) * -1);

            application.responsiveIframe();
        },
        pageLoading     : function (what){

            $('#loadingPage').css({'width': application.windowWidth, 'height': application.windowHeight});

            if (what) {
                $('#loadingPage').fadeIn('fast');
            } else {
                $('#loadingPage').fadeOut('fast');
            }
        },
        pagesBuild      : function (){
            // ---------------------------------------------------------------------------------------------------------
            // PolicyAutos

            this.PolicyAuto = function (){

            }
            // ---------------------------------------------------------------------------------------------------------
        },
        scroolMoving    : function (position, time){
            var time = (parseInt(time)) ? time : 800;
            $("html, body").animate({scrollTop: position}, time);
        },
        addYearsinDate  : function (data, year){

            this.validDaysInMonth = function (month, ano){
                if ((month < 8 && month % 2 == 1) || (month > 7 && month % 2 == 0)) return 31;
                if (month != 2) return 30;
                if (ano % 4 == 0) return 29;
                return 28;
            }

            var data = data.split('-');

            var day = parseInt(data[0]);
            var month = parseInt(data[1]);
            var years = parseInt(data[2]) + parseInt(year);

            while (day > this.validDaysInMonth(month, years)) {
                day -= this.validDaysInMonth(month, years);
                month++;
                if (month > 12) {
                    month = 1;
                    years++;
                }
            }

            if (day < 10) day = '0' + day;
            if (month < 10) month = '0' + month;

            return day + "-" + month + "-" + years;
        },
        inlineFieldAlert: function (action, field_type, field, msg, class_type, toastr){

            var arrayClass = ['warning', 'error', 'info', 'success'];
            var icon = '';

            //input
            if (action == 'clean' && field_type == 'input') {
                var obgClass = field.parent();
                obgClass.find('.alert-msg').remove();

                jQuery.each(arrayClass, function (index, value){
                    obgClass.removeClass(value);
                });

                return true;
            }

            //select
            if (action == 'clean' && field_type == 'select') {
                var obgClass = field.parent();
                obgClass.find('.alert-msg').remove();

                jQuery.each(arrayClass, function (index, value){
                    obgClass.removeClass(value);
                });

                return true;
            }

            //input-prepend
            if (action == 'clean' && field_type == 'input-prepend') {
                var obgClass = field.parent().parent();
                obgClass.find('.alert-msg').remove();

                jQuery.each(arrayClass, function (index, value){
                    obgClass.removeClass(value);
                });

                return true;
            }

            //textarea
            if (action == 'clean' && field_type == 'textarea') {
                var obgClass = field.parent().parent();
                obgClass.find('.alert-msg').remove();

                jQuery.each(arrayClass, function (index, value){
                    obgClass.removeClass(value);
                });

                return true;
            }

            //checkbox
            if (action == 'clean' && field_type == 'checkbox') {
                var obgClass = field.parent().parent().parent().parent();
                obgClass.find('.alert-msg').remove();

                jQuery.each(arrayClass, function (index, value){
                    obgClass.removeClass(value);
                });

                return true;
            }

            if (toastr && class_type == 'warning') {
                parent.toastr.warning(msg);
            }
            if (toastr && class_type == 'error') {
                parent.toastr.error(msg);
            }
            if (toastr && class_type == 'info') {
                parent.toastr.info(msg);
            }
            if (toastr && class_type == 'success') {
                parent.toastr.success(msg);
            }

            if (class_type == 'warning') {
                icon = '<i class="icon-remove-sign"></i>';
            }
            if (class_type == 'error') {
                icon = '<i class="icon-remove-sign"></i>';
            }
            if (class_type == 'info') {
                icon = '<i class="icon-remove-sign"></i>';
            }
            if (class_type == 'success') {
                icon = '<i class="icon-ok-sign"></i>';
            }

            //input
            if (field_type == 'input') {
                var obgClass = field.parent();

                obgClass.find('.alert-msg').remove();

                if (msg != '') {
                    obgClass.append('<span class="alert-msg span12">' + icon + '' + msg + '</span>');
                }
                obgClass.addClass(class_type);
            }

            //select
            if (field_type == 'select') {
                var obgClass = field.parent();

                obgClass.find('.alert-msg').remove();

                if (msg != '') {
                    obgClass.append('<span class="alert-msg span12">' + icon + '' + msg + '</span>');
                }
                obgClass.addClass(class_type);
            }

            //input-prepend
            if (field_type == 'input-prepend') {
                var obgClass = field.parent().parent();

                obgClass.find('.alert-msg').remove();

                if (msg != '') {
                    obgClass.append('<span class="alert-msg span12">' + icon + '' + msg + '</span>');
                }
                obgClass.addClass(class_type);
            }

            //textarea
            if (field_type == 'textarea') {
                var obgClass = field.parent().parent();

                obgClass.find('.alert-msg').remove();

                if (msg != '') {
                    obgClass.append('<span class="alert-msg span12">' + icon + '' + msg + '</span>');
                }
                obgClass.addClass(class_type);
            }

            //checkbox
            if (field_type == 'checkbox') {
                var obgClass = field.parent().parent().parent().parent();

                obgClass.find('.alert-msg').remove();

                if (msg != '') {
                    obgClass.append('<span class="alert-msg span12">' + icon + '' + msg + '</span>');
                }
                obgClass.addClass(class_type);
            }
        },
        sendErrorAjax   : function (jqXHR, textStatus, errorThrown){
            //escrever o aqui o código para envio de email com erro
        },
        setToastr       : function (){
            toastr.options = {
                "closeButton"      : true,
                "debug"            : false,
                "newestOnTop"      : true,
                "progressBar"      : true,
                "positionClass"    : "toast-top-right",
                "preventDuplicates": true,
                "onclick"          : null,
                "showDuration"     : "300",
                "hideDuration"     : "1000",
                "timeOut"          : "5000",
                "extendedTimeOut"  : "1000",
                "showEasing"       : "swing",
                "hideEasing"       : "linear",
                "showMethod"       : "fadeIn",
                "hideMethod"       : "fadeOut"
            }
        },
		setMask: function () {
			$(".mask_money").maskMoney({prefix: '', allowNegative: true, thousands: '.', decimal: ',', affixesStay: false});
			$(".card_validity").mask("99/9999");
			$(".credit_card").mask("9999 999 9999 9999");
			$(".mask_cpf").mask("999.999.999-99");
			$(".mask_cnpj").mask("99.999.999/9999-99");
			$(".mask_birthdate").mask("99/99/9999");
			$('.mask_phone').mask("(99) 9999-9999");
			$(".cep").mask("99.999-999");

			$('.mask_cellular').mask("(99) 9999-9999?9").ready(function (event) {
				try {
					var target, phone, element;
					target = (event.currentTarget) ? event.currentTarget : event.srcElement;
					phone = target.value.replace(/\D/g, '');
					element = $(target);
					element.unmask();
					if (phone.length > 10) {
						element.mask("(99) 99999-999?9");
					} else {
						element.mask("(99) 9999-9999?9");
					}
				} catch (e) {

				}
			});

			$(".mask_cep").mask("99999-999");
			/*$(".mask_number").maskNumber();*/

			$(".mask_date").mask("99-99-9999");
			$(".mask_hour").mask("99:99");

		}
    };
    application.init();
});