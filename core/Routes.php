<?php

require_once 'Http.php';

Http::get('/sistema/current-date', 'SistemaController@currentDate');

Http::get('/config/smtp', 'SmtpController@find');
Http::get('/cnpj/find/{cnpj}/{completa}/{atualizar}', 'CnpjController@find');
Http::get('/release/{servico}/{versao}', 'ReleaseController@find');

Http::get('/licenca/update-hd/{cnpj}/{hd}', 'LicencaController@updateHd');
Http::get('/licenca/initial-contract/{cnpj}/{hd}', 'LicencaController@findInitial');
Http::get('/licenca/find/{cnpj}/{hd}/{version}', 'LicencaController@find');

Http::get('/cliente/find/{cnpj}', 'ClienteController@find');
Http::put('/cliente/emissores', 'ClienteController@updateEmissores');
