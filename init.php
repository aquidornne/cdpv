<?php
session_start();

global $config;

function __autoload($classe)
{
    include_once 'php/' . $classe . ".php";
}

$system = new SystemIntegration(
    array(
        'clientSecretKey' => '7d581fbf82cd0e4ff4df0bdd8dd5f35a',
        'sandbox' => TRUE
    )
);

include_once 'php/Tools.php';
include_once 'forms.php';

define('_PROJECT_', 'http://' . Tools::getHttpHost() . ((Tools::getServerName() == 'localhost') ? '/SERVICOS/cdpv/' : '/clientes/cdpv/'));
define('_SYSTEM_FILES_', 'http://' . Tools::getHttpHost() . ((Tools::getServerName() == 'localhost') ? '/SERVICOS/cdpv/system/files/' : '/clientes/cdpv/system/files/'));
define('_PATH_PROJECT_', $_SERVER['DOCUMENT_ROOT'] . ((Tools::getServerName() == 'localhost') ? '/SERVICOS/cdpv/' : '/clientes/cdpv/'));

$config = array(
    'title' => 'CDPV',
    'description' => 'Empresa líder em treinamentos comerciais',
    'keywords' => 'treinamento de vendas, cdpv',
    'rights' => '',
    'menu' => 0
);

$states = array("AC" => "Acre", "AL" => "Alagoas", "AM" => "Amazonas", "AP" => "Amapá", "BA" => "Bahia", "CE" => "Ceará", "DF" => "Distrito Federal", "ES" => "Espírito Santo", "GO" => "Goiás", "MA" => "Maranhão", "MT" => "Mato Grosso", "MS" => "Mato Grosso do Sul", "MG" => "Minas Gerais", "PA" => "Pará", "PB" => "Paraíba", "PR" => "Paraná", "PE" => "Pernambuco", "PI" => "Piauí", "RJ" => "Rio de Janeiro", "RN" => "Rio Grande do Norte", "RO" => "Rondônia", "RS" => "Rio Grande do Sul", "RR" => "Roraima", "SC" => "Santa Catarina", "SE" => "Sergipe", "SP" => "São Paulo", "TO" => "Tocantins");;
?>