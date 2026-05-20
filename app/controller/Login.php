<?php

namespace app\controller;

final class Login extends Base
{
    public function login($request, $response)
    {
        try {
            return $this->getTwig()
                ->render($response, $this->setView('login'), [
                    'titulo' => 'Início',
                ])
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }

    public function authenticate($request, $response)
    {
        # Recupera as credenciais enviadas no corpo da requisição
        $form  = $request->getParsedBody();
        $login = $form['login'] ?? null;
        $senha = $form['senha'] ?? null;

        # Bloqueia se algum campo veio vazio
        if (is_null($login) || is_null($senha)) {
            return $this->json($response, ['status' => false, 'msg' => 'Por favor informe seu usuário e senha!', 'id' => 0]);
        }

        # Verifica se a sessão está em "lockout" por excesso de tentativas falhas
        if (isset($_SESSION['login_locked_until']) && $_SESSION['login_locked_until'] > time()) {
            return $this->json($response, ['status' => false, 'msg' => 'Muitas tentativas. Tente novamente em alguns minutos.', 'id' => 0], 429);
        }

        try {
            $qb    = \app\database\DB::select('*')->from('vw_user');
            $login = $qb->createNamedParameter($login);

            $qb->where('cpf = ' . $login)
                ->orWhere('email = '    . $login)
                ->orWhere('telefone = ' . $login);

            $user = $qb->fetchAssociative();

            $dummyHash   = '$2y$10$CwTycUXWue0Thq9StjUM0uJ8.k3.kK1m3Sv7lJ1uG9N9Yvb.MqYsa';
            $senhaValida = password_verify($senha, $user['senha'] ?? $dummyHash);

            if (!$user || !$senhaValida) {
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                if ($_SESSION['login_attempts'] >= 5) {
                    $_SESSION['login_locked_until'] = time() + 900;
                    $_SESSION['login_attempts']     = 0;
                }
                return $this->json($response, ['status' => false, 'msg' => 'Verifique seu e-mail e senha e tente novamente!', 'id' => 0], 403);
            }

            if (!$user['ativo']) {
                return $this->json($response, [
                    'status' => false,
                    'msg'    => 'Seu acesso ainda não foi liberado. Aguarde a aprovação do administrador.',
                    'id'     => 0,
                ], 403);
            }

            unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
            session_regenerate_id(true);

            if (password_needs_rehash($user['senha'], PASSWORD_DEFAULT)) {
                \app\database\DB::connection()->update(
                    'users',
                    [
                        'senha'         => password_hash($senha, PASSWORD_DEFAULT),
                        'atualizado_em' => date('Y-m-d H:i:s'),
                    ],
                    ['id' => $user['id']],
                );
            }

            unset($user['senha']);

            $_SESSION['user']           = $user;
            $_SESSION['user']['logado'] = true;

            $lifetime = (int) (ini_get('session.gc_maxlifetime') ?: 3600);

            $payload = [
                'iat' => time(),
                'exp' => time() + $lifetime,
                'sub' => (string) $user['id'],
            ];

            $jwt      = \Firebase\JWT\JWT::encode($payload, SECRET_KEY, 'HS256');
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

            setcookie('auth_token', $jwt, [
                'expires'  => time() + $lifetime,
                'path'     => '/',
                'domain'   => $_SERVER['HTTP_HOST'],
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            $_SESSION['user']['sessao_criada_em'] = (new \DateTime())->format('Y-m-d H:i:s');
            $_SESSION['user']['sessao_expira_em'] = (new \DateTime())->modify("+{$lifetime} seconds")->format('Y-m-d H:i:s');

            return $this->json($response, [
                'status'           => true,
                'msg'              => 'Seja bem vindo de volta!',
                'id'               => $user['id'],
                'sessao_expira_em' => $_SESSION['user']['sessao_expira_em'],
            ], 200);
        } catch (\PDOException $e) {
            error_log('[auth][DB] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Não foi possível concluir o login. Tente novamente.', 'id' => 0], 500);
        } catch (\UnexpectedValueException | \DomainException $e) {
            error_log('[auth][JWT] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Não foi possível concluir o login. Tente novamente.', 'id' => 0], 500);
        } catch (\Throwable $e) {
            error_log('[auth][GERAL] ' . $e->getMessage());
            return $this->json($response, ['status' => false, 'msg' => 'Erro inesperado. Tente novamente.', 'id' => 0], 500);
        }
    }

    public function logout($request, $response)
    {
        # Limpa todos os dados da sessão em memória
        $_SESSION = [];

        # Destrói o arquivo de sessão no servidor
        session_destroy();

        # Invalida o cookie JWT no browser definindo expiração no passado
        setcookie('auth_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'domain'   => $_SERVER['HTTP_HOST'],
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        # Redireciona para a página de login (302 funciona pois o browser acessa diretamente, não via fetch)
        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }

    public function preRegister($request, $response)
    {
        $form = $request->getParsedBody();

        $nome      = $form['nome']          ?? null;
        $sobrenome = $form['sobrenome']      ?? null;
        $cpf       = $form['cpf']           ?? null;
        $rg        = $form['rg']            ?? null;
        $senha     = $form['senhaCadastro'] ?? null;
        $email     = $form['email']         ?? null;
        $telefone  = $form['telefone']      ?? null;

        $DataUser = [
            'nome'          => $nome,
            'sobrenome'     => $sobrenome,
            'cpf'           => $cpf,
            'rg'            => $rg,
            'senha'         => password_hash($senha, PASSWORD_DEFAULT),
            'criado_em'     => date('Y-m-d H:i:s'),
            'atualizado_em' => date('Y-m-d H:i:s'),
        ];

        try {
            $conn = \app\database\DB::connection();
            $conn->beginTransaction();

            $conn->insert('users', $DataUser);
            $id_usuario = (int) $conn->lastInsertId();

            if (!empty($email)) {
                $conn->insert('contact', [
                    'id_usuario'    => $id_usuario,
                    'tipo'          => 'EMAIL',
                    'contato'       => $email,
                    'criado_em'     => date('Y-m-d H:i:s'),
                    'atualizado_em' => date('Y-m-d H:i:s'),
                ]);
            }

            if (!empty($telefone)) {
                $conn->insert('contact', [
                    'id_usuario'    => $id_usuario,
                    'tipo'          => 'TELEFONE',
                    'contato'       => $telefone,
                    'criado_em'     => date('Y-m-d H:i:s'),
                    'atualizado_em' => date('Y-m-d H:i:s'),
                ]);
            }

            $conn->commit();

            return $this->json($response, [
                'status' => true,
                'msg'    => 'Pré-cadastro realizado com sucesso!',
                'id'     => $id_usuario,
            ], 201);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $conn->rollBack();
            return $this->json($response, [
                'status' => false,
                'msg'    => 'CPF ou contato já cadastrado.',
                'id'     => 0,
            ], 409);
        } catch (\Throwable $e) {
            $conn->rollBack();
            error_log('[preRegister] ' . $e->getMessage());
            return $this->json($response, [
                'status' => false,
                'msg'    => 'Erro ao realizar pré-cadastro. Tente novamente.',
                'id'     => 0,
            ], 500);
        }
    }

    public function google($request, $response)
    {
        $form = $request->getParsedBody();

        $credential          = $form['credential']      ?? null;
        $form_g_csrf_token   = $form['g_csrf_token']    ?? null;
        $cookie_g_csrf_token = $_COOKIE['g_csrf_token'] ?? null;
        $google_client_id    = $_ENV['GOOGLE_CLIENT_ID'] ?? null;

        # Valida presença dos dados obrigatórios
        if (is_null($credential) || is_null($form_g_csrf_token) || is_null($cookie_g_csrf_token)) {
            return $this->json($response, ['status' => false, 'msg' => 'Dados do Google ausentes.', 'id' => 0], 400);
        }

        # Valida o CSRF token do Google (cookie deve bater com o campo do formulário)
        if (!hash_equals($cookie_g_csrf_token, $form_g_csrf_token)) {
            return $this->json($response, ['status' => false, 'msg' => 'Token CSRF inválido.', 'id' => 0], 403);
        }

        try {
            $provider = new \League\OAuth2\Client\Provider\Google([
                'clientId'     => $google_client_id,
                'clientSecret' => '',
                'redirectUri'  => '',
            ]);

            $httpResponse = $provider->getHttpClient()->request(
                'GET',
                'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential),
                ['timeout' => 3, 'connect_timeout' => 2]
            );

            $claims = json_decode((string) $httpResponse->getBody(), true, flags: JSON_THROW_ON_ERROR);

            # Valida que o token foi emitido para o seu app
            # O campo aud pode vir como string ou array dependendo do fluxo OAuth
            $aud     = $claims['aud'] ?? '';
            $audList = is_array($aud) ? $aud : [$aud];

            if (!in_array($google_client_id, $audList, true)) {
                return $this->json($response, ['status' => false, 'msg' => 'Token inválido.', 'id' => 0], 403);
            }

            $email     = $claims['email']       ?? null;
            $nome      = $claims['given_name']  ?? null;
            $sobrenome = $claims['family_name'] ?? null;

            if (is_null($email)) {
                return $this->json($response, ['status' => false, 'msg' => 'E-mail não disponível na conta Google.', 'id' => 0], 400);
            }

            # Busca o usuário na vw_user pelo e-mail do Google
            $query = \app\database\DB::select('*')
                ->from('vw_user')
                ->where('email = :email')
                ->setParameter('email', $email)
                ->fetchAssociative();

            # -------------------------------------------------------------------
            # OPÇÃO 2: Usuário não existe → cria automaticamente com dados Google
            # Entra com ativo = false e só acessa após aprovação do administrador
            # -------------------------------------------------------------------
            if (!$query) {
                $conn = \app\database\DB::connection();
                $conn->beginTransaction();

                $conn->insert('users', [
                    'nome'          => $nome,
                    'sobrenome'     => $sobrenome,
                    'criado_em'     => date('Y-m-d H:i:s'),
                    'atualizado_em' => date('Y-m-d H:i:s'),
                ]);

                $id_usuario = (int) $conn->lastInsertId();

                $conn->insert('contact', [
                    'id_usuario'    => $id_usuario,
                    'tipo'          => 'EMAIL',
                    'contato'       => $email,
                    'criado_em'     => date('Y-m-d H:i:s'),
                    'atualizado_em' => date('Y-m-d H:i:s'),
                ]);

                $conn->commit();

                # Recarrega o usuário recém-criado para ter os dados completos
                # incluindo o campo 'ativo' que vem da view vw_user
                $query = \app\database\DB::select('*')
                    ->from('vw_user')
                    ->where('email = :email')
                    ->setParameter('email', $email)
                    ->fetchAssociative();

                if (!$query) {
                    return $this->json($response, [
                        'status' => false,
                        'msg'    => 'Não foi possível criar o usuário com os dados do Google. Tente novamente.',
                        'id'     => 0,
                    ], 500);
                }
            }

            # -------------------------------------------------------------------
            # Verifica se o usuário está ativo
            # Usuários novos criados via Google entram com ativo = false
            # e só acessam após aprovação do administrador
            # -------------------------------------------------------------------
            if (!$query['ativo']) {
                return $this->json($response, [
                    'status' => false,
                    'msg'    => 'Por enquanto você ainda não está autorizado, por favor aguarde...',
                    'id'     => 0,
                ], 403);
            }

            # -------------------------------------------------------------------
            # Usuário existe e está ativo — cria sessão e redireciona
            # -------------------------------------------------------------------
            session_regenerate_id(true);

            $user = $query;
            unset($user['senha']);

            $_SESSION['user']           = $user;
            $_SESSION['user']['logado'] = true;

            $lifetime = (int) (ini_get('session.gc_maxlifetime') ?: 3600);

            $payload_jwt = [
                'iat' => time(),
                'exp' => time() + $lifetime,
                'sub' => (string) $user['id'],
            ];

            $jwt      = \Firebase\JWT\JWT::encode($payload_jwt, SECRET_KEY, 'HS256');
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;

            setcookie('auth_token', $jwt, [
                'expires'  => time() + $lifetime,
                'path'     => '/',
                'domain'   => $_SERVER['HTTP_HOST'],
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            $_SESSION['user']['sessao_criada_em'] = (new \DateTime())->format('Y-m-d H:i:s');
            $_SESSION['user']['sessao_expira_em'] = (new \DateTime())->modify("+{$lifetime} seconds")->format('Y-m-d H:i:s');


            return $response
                ->withHeader('Location', '/home')
                ->withStatus(302);
        } catch (\Throwable $e) {
            if (isset($conn) && $conn->isTransactionActive()) {
                $conn->rollBack();
            }
            error_log('[auth][GOOGLE] ' . $e->getMessage());
            return $this->json($response, [
                'status' => false,
                'msg'    => 'Falha na autenticação com o Google. Tente novamente.',
                'id'     => 0,
            ], 500);
        }
    }
}