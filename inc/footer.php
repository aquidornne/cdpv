<section id="about" class="theme-type-style-02">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-2 pdt-20 pdb-20"><a href="https://www.cdpv.com.br/" title="CDPV"><img src="<?php echo _PROJECT_; ?>img/logo_cpdv_branca.PNG" alt="Logo CDPV" class="img-responsive"></a></div>
            <div class="col-xs-12 col-md-4 links pdb-20">
                <h2>Institucional</h2>
                <ul class="list-site-02">
                    <li><a href="#">Quem Somos</a></li>
                    <li><a href="#">Próximos Eventos</a></li>
                    <li><a href="#">Educação Corporativa</a></li>
                    <li><a href="#" target="_blank">Aluguel de Salas</a></li>
                    <li><a href="#">RH Vendas - Recrutamento e Seleção</a></li>
                    <li><a href="#">Trade Marketing</a></li>
                    <li><a href="#">Outros Serviços</a></li>
                    <li><a href="#">Política de Privacidade</a></li>
                    <li><a href="#">Política de Satisfação Garantida</a></li>
                </ul>
            </div>
            <div class="col-xs-12 col-md-6 pdd-20 white">
                <h2>(21) 2112-9999</h2>
                <p>Demais capitais, ligue 4007-1037.</p>
                <p>Recepção do Centro de Eventos: (21) 2516-3732</p>
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <p><b>Escritório Central:</b><br>Av. Rio Branco, 20 / 16ª andar <br> Centro – Rio de Janeiro – RJ <br> CEP: 20.090-000</p>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <p><b>Centro de Eventos:</b><br>Av. Rio Branco, 81 / 7º andar <br> Centro – Rio de Janeiro – RJ <br> CEP: 20.040-004</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-12 text-center white">
                <p>CDPV Ltda. CNPJ 05.877.927/0001-28<br>
                    Grupo CDPV – desde 2003 contribuindo com o desenvolvimento de pessoas e empresas. © Copyright 2018.
                </p>
            </div>
        </div>
    </div>
</section>

</div>

<a href="#" id="go-top" class="home-btn"></a>

</body>
</html>

<script>
	<?php if(isset($_GET['success']) AND !empty($_GET['success'])){ ?>
		<?php if($_GET['success'] == 1){ ?>
			$(document).ready(function(){ toastr.success('Contato enviado com sucesso!'); });
		<?php }else{ ?>
			$(document).ready(function(){ toastr.error('Ocorreu algum erro, tente outra forma de contato.'); });
		<?php } ?>
	<?php } ?>

    $('.fancybox').fancybox();

    $('.btn_contact').on('click', function (e){
        e.preventDefault();

        var submit = true;
        var data = $('.form_contact input, .form_contact textarea, .form_contact select').serialize();

        $('.form_contact input, .form_contact textarea, .form_contact select').each(function (){
            if (parseInt($(this).attr('validation')) == 1 && $(this).val() == "") {
                $(this).parent().addClass('error');
                submit = false;
            } else {
                $(this).parent().removeClass('error');
            }
        });

        if (submit == true) {
			$('#form_contact').submit();
        } else {
            toastr.error('Preencha os campos obrigatórios');
        }
    });

    $('.btn_comment').on('click', function (e){
        e.preventDefault();

        var submit = true;
        var data = $('.form_comment input, .form_comment textarea, .form_comment select').serialize();

        $('.form_comment input, .form_comment textarea, .form_comment select').each(function (){
            if (parseInt($(this).attr('validation')) == 1 && $(this).val() == "") {
                $(this).parent().addClass('error');
                submit = false;
            } else {
                $(this).parent().removeClass('error');
            }
        });

        if (submit == true) {
            $('#form_comment').submit();
        } else {
            toastr.error('Preencha os campos obrigatórios');
        }
    });

    wow = new WOW(
        {
            animateClass: 'animated',
            offset: 100,
            callback: function (box) {
                console.log("WOW: animating <" + box.tagName.toLowerCase() + ">")
            }
        }
    );
    wow.init();

    $('.go-site').on('click', function () {
        if ($(document).width() < 991) {
            $('html, body').animate({scrollTop: ($('#services').offset().top)}, 'show');
            app.disableMenu();
        } else {
            $('html, body').animate({scrollTop: ($('#services').offset().top - $('header').height())}, 'show');
        }
    });
</script>