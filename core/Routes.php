<?php

require_once 'Http.php';

Http::get('/produto/find/{id}', 'ProdutoController@find');
Http::put('/produtos/add', 'ProdutoController@add');
