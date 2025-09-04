<?php

require_once 'Http.php';

Http::get('/cliente', 'ClienteController@show', 'Authentication@basic');
Http::get('/cliente/{id}', 'ClienteController@findById', 'Authentication@bearer');
Http::post('/cliente', 'ClienteController@add');
Http::patch('/cliente/{id}', 'ClienteController@update');

Http::get('/produto/find/{id}', 'ProdutoController@find', 'Authentication@bearer');
Http::put('/produtos/edit/{id}', 'ProdutoController@edit', 'Authentication@basic');
Http::post('/produtos/add', 'ProdutoController@add');
