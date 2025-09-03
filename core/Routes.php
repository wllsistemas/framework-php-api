<?php

require_once 'Http.php';

Http::get('/produto/find/{id}', 'ProdutoController@find', 'Authentication@bearer');
Http::put('/produtos/edit/{id}', 'ProdutoController@edit', 'Authentication@basic');
Http::post('/produtos/add', 'ProdutoController@add');
