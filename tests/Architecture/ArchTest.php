<?php

declare(strict_types=1);

arch('todos os arquivos usam strict types')
    ->expect('app')
    ->toUseStrictTypes();

arch('sem debug no código de produção')
    ->expect('app')
    ->not->toUse(['var_dump', 'dd', 'dump', 'die', 'print_r', 'var_export']);

arch('controllers não acessam banco direto')
    ->expect('app\controller')
    ->not->toUse(['PDO', 'mysqli', 'pg_connect']);

arch('sem funções perigosas no código')
    ->expect('app')
    ->not->toUse([
        'eval',
        'exec',
        'shell_exec',
        'system',
        'passthru',
        'proc_open',
        'popen',
        'base64_decode',
    ]);

arch('controllers devem ser classes finais')
    ->expect('app\controller')
    ->toBeFinal()
    ->ignoring('app\controller\Base');

arch('middlewares devem ser classes finais')
    ->expect('app\middleware')
    ->toBeFinal();

arch('controllers devem estender Base')
    ->expect('app\controller')
    ->toExtend('app\controller\Base')
    ->ignoring('app\controller\Base');

arch('middleware não acessa banco direto')
    ->expect('app\middleware')
    ->not->toUse(['PDO', 'mysqli', 'pg_connect']);