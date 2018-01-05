var app;

$(function () {

    if ($('html').hasClass('no-app')) return;

    app = {
        pageWidth: $(window).width(),
        pageHeight: $(window).height(),
        isMobile: false,
        bodyHeight: '',

        init: function () {
            var isMobile = (/Android|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)); //iPad|
            if ((/iPad/i.test(navigator.userAgent))) {
                isMobile = false;
            }

            if (isMobile) {
                $('body').addClass('isMobile');
                app.isMobile = true;
            }

            app.setEvents();
            app.setMask();
        },
        setEvents: function () {
            $(window)
                .on('scroll', app.onScroll)
                .on('load', app.onLoad)
                .on('ready', app.onReady)
                .on('resize', app.onResize);
            $(document)
                .on('scroll', app.onScroll)
                .on('load', app.onLoad)
                .on('ready', app.onReady)
                .on('resize', app.onResize);

            $('a[href="#"]').click(function (e) {
                e.preventDefault();
            });

            $(document).on('click', '.nav-menu li a', function (e) {
                if (!$(this).hasClass('no-default')) {
                    e.preventDefault();

                    var menuClick = $(this);

                    if ($(document).width() < 991) {
                        $('html, body').animate({scrollTop: ($('#' + $(this).attr('menu')).offset().top)}, 'show');
                        app.disableMenu();
                    } else {
                        $('html, body').animate({scrollTop: ($('#' + $(this).attr('menu')).offset().top - $('header').height())}, 'show');
                    }

                    $('.nav-menu li a').each(function () {
                        if ($(this).attr('menu') != menuClick.attr('menu')) {
                            $(this).parent().removeClass('active');
                        } else {
                            $(this).parent().addClass('active');
                        }

                        console.log('menu', $(this).attr('menu'));
                        console.log('menuClick', menuClick.attr('menu'));
                    });
                }
            });

            $('#go-top').on('click', function () {
                $('html,body').animate({scrollTop: ($('body').offset().top)}, 1000);
            });

            $('#responsive-menu').on('click', function () {
                if ($('header').hasClass('active')) {
                    app.disableMenu();
                } else {
                    app.activeMenu();
                }
            });

            app.adaptVerticalText();
            app.adaptContainerGeneral();
            app.accordionList();
        },
        activeMenu: function () {
            $('header').animate({left: "0"}, 1000);
            $('header').addClass('active');
            $('#responsive-menu').removeClass('type-02');
            $('#responsive-menu').addClass('type-01');
        },
        disableMenu: function () {
            $('header').animate({left: "-100%"}, 1000);
            $('header').removeClass('active');
            $('#responsive-menu').addClass('type-02');
            $('#responsive-menu').removeClass('type-01');
        },
        adaptVerticalText: function () {
            $('.block-vertical-center').each(function () {
                $(this).animate({
                    'top': '50%',
                    'margin-top': '-' + ($(this).height() / 2)
                }, 1000);
            });
        },
        adaptContainerGeneral: function () {
            if ($(window).width() >= 991) {
                $('.anim-slider01').height((($(window).height() - $('header').height()) - ((($(window).height() - $('header').height()) / 100) * 15)));
                $('.container-general').css('top', $('header').height());
            }else {
                $('.anim-slider01').height(($(window).height()));
                $('.container-general').css('top', 0);
            }
        },
        accordionList: function () {
            var divs = $('.accordion>div').hide();

            $('.accordion>h3').click(function () {

                $(this).not(this).removeClass('active');
                $(this).toggleClass('active');
                divs.not($(this).next()).slideUp();
                $(this).next().slideToggle();
                return false;
            });
        },
        onResize: function () {
            if ($(document).width() >= 991) {
                app.activeMenu();
            }
            app.adaptVerticalText();
            app.adaptContainerGeneral();
        },
        onScroll: function () {
            if ($(document).scrollTop() > app.pageHeight) {
                $('#go-top').fadeIn('show');
            } else {
                $('#go-top').fadeOut('show');
            }
        },
        onReady: function (ev) {
            app.pageLoading(true);
        },
        onLoad: function (ev) {
            app.pageLoading(false);
        },
        pageLoading: function (what) {
            if (what) {
                $('#loadingPage').fadeIn('fast');
            } else {
                $('#loadingPage').fadeOut('fast');
            }

            var top = ((this.bodyHeight != '') ? (this.bodyHeight / 2) : (app.pageHeight / 2));
            $('.loading', '#loadingPage').css('top', top);
        },
        setMask: function () {
            $(".mask_cpf").mask("999.999.999-99");
            $(".mask_cnpj").mask("99.999.999/9999-99");
            $('.mask_phone').mask("(99) 9999-9999");

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

            $(".mask_date").mask("99-99-9999");
            $(".mask_hour").mask("99:99");
        }
    };
    app.init();
});