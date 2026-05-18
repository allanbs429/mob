<?php


declare(strict_types=1);

use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

test('preRegister com dados validos retorna 200 com status true', function () {
    $request = (new RequestFactory())
        ->createRequest('POST', '/authentication/preregister')
        ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
        ->withParsedBody([
            'nome' => 'Ryan',
            'sobrenome' => 'Souza Vasques',
            'cpf' => '999.999.999-99',
            'rg' => '98765',
            'senhaCadastro' => '123456',
            'email' => 'ryansouza@gmail.com',
            'telefone' => '6999999999'
        ]);

    $response = (new ResponseFactory())->createResponse();

    $result = (new app\controller\Login())->preRegister($request, $response);

    $result->getBody()->rewind();


    $json = json_decode($result->getBody()->getContents(), true);
    #Capturamos o codigo de resposta e o status do json
    #Foi criado.
    expect($result->getStatusCode())->toBe(201);

    expect($json['status'])->toBeTrue();

    expect($json['msg'])->toContain('Usuário criado com sucesso');


});
