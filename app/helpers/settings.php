<?php

declare(strict_types=1);

session_start();

define('ROOT', dirname(__FILE__, 3));
#DIRETÓRIO DAS VIEWS
define('DIR_VIEWS', ROOT . '/app/view');
#EXTENSÃO PADRÃO DAS VIEWS
define('EXT_VIEWS', '.html');
#Chave secreta para geração de tokens
define('SECRET_KEY', '2aa44e53-0c02-48ad-98f1-167fabb5cd67');