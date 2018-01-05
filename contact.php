<?php
require('init.php');
global $page_current;
$page_current = 'contact';
?>

<?php include_once('inc/header.php'); ?>

    <main>
        <section id="" class="theme-type-style-03">
            <div class="container pdt-50 pdb-50">
                <div class="form_contact">
                    <h2 class="style-01 mgb-30" title="Informe seus dados para agendamento">Deixe-nos uma mensagem</h2>
                    <p>Insira as informações solicitadas abaixo para recebermos seu contato em nosso e-mail.</p>
                    <form id="form_contact" method="POST">
                        <div class="" class="wow fadeInUp" data-wow-duration="1s">
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" placeholder="Nome" validation="1">
                            </div>
                            <div class="form-group">
                                <input type="text" name="email_form" class="form-control" placeholder="E-mail" validation="1">
                            </div>
                            <div class="form-group">
                                <input type="text" name="phone" class="form-control mask_cellular" placeholder="Telefone" validation="1">
                            </div>
                            <div class="form-group">
                                <textarea name="message" rows="5" class="form-control" placeholder="Mensagem" validation="1"></textarea>
                            </div>

                            <input type="hidden" name="form_type" value="contact">
                            <div class="row">
                                <div class="col-xs-12 col-md-12">
                                    <button type="button" class="btn btn_contact btn-site">Enviar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

<?php include_once('inc/footer.php'); ?>