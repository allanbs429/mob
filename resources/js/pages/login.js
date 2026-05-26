import Swal from "sweetalert2";
import Validate from "../components/validate.js";
import Request from "../components/requests.js";
Inputmask('999.999.999-99').mask('#cpf');
Inputmask('(99) 9999-9999').mask('#telefone');

const mdPreRegister = document.getElementById('mdPreRegister');
const buttonPreRegister = document.getElementById('buttonPreRegister');
const buttonLogin = document.getElementById('buttonLogin');

mdPreRegister.addEventListener('click', () => {
    $('#modalPreRegisterUser').modal('show');
});

buttonLogin.addEventListener('click', async () => {
    const validou = Validate.SetForm('form').Validate();
    if (!validou) {
        Swal.fire({
            icon: 'error',
            title: 'Ops...',
            text: 'Preencha seu login e senha!',
            timer: 2500,
            timerProgressBar: true
        });
        return;
    }

    const requests = new Request();
    const originalText = buttonLogin.textContent;

    try {
        buttonLogin.textContent = 'Autenticando, por favor aguarde...';
        buttonLogin.disabled = true;

        const response = await requests.setForm('form').post('/authentication/authenticate');

        if (!response.status) {
            Swal.fire({
                icon: 'error',
                title: 'Ops...',
                text: response.msg,
                timer: 2500,
                timerProgressBar: true
            });
            return;
        }

        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: response.msg,
            timer: 1500,
            timerProgressBar: true
        }).then(() => {
            window.location.href = '/home';
        });

    } catch (error) {
        let texto = 'Ocorreu um erro ao autenticar. Tente novamente!';

        if (error.message?.includes('403')) {
            texto = 'Verifique seu login e senha ou seu acesso ainda não foi liberado pelo administrador.';
        } else if (error.message?.includes('429')) {
            texto = 'Muitas tentativas. Tente novamente em alguns minutos.';
        } else if (error.message?.includes('500')) {
            texto = 'Erro interno. Tente novamente mais tarde.';
        }

        Swal.fire({
            icon: 'error',
            title: 'Ops...',
            text: texto,
            timer: 2500,
            timerProgressBar: true
        });
    } finally {
        buttonLogin.disabled = false;
        buttonLogin.textContent = originalText;
    }
});

buttonPreRegister.addEventListener('click', async () => {
    const validou = Validate.SetForm('form').Validate();
    if (!validou) {
        Swal.fire({
            icon: 'error',
            title: 'Ops...',
            text: 'Preencha os campos corretamente!',
            timer: 2500,
            timerProgressBar: true
        });
        return;
    }

    const requests = new Request();
    const originalText = buttonPreRegister.textContent;

    try {
        buttonPreRegister.textContent = 'Cadastrando, por favor aguarde...';
        buttonPreRegister.disabled = true;

        const response = await requests.setForm('form').post('/authentication/preregister');

        if (!response.status) {
            Swal.fire({
                icon: 'error',
                title: 'Ops...',
                text: response.msg,
                timer: 2500,
                timerProgressBar: true
            });
            return;
        }

        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: response.msg,
            timer: 2500,
            timerProgressBar: true
        }).then(() => {
            $('#modalPreRegisterUser').modal('hide');
        });

    } catch (error) {
        const texto = error.data?.msg
            || error.message
            || 'Ocorreu um erro ao cadastrar o usuário!';

        Swal.fire({
            icon: 'error',
            title: 'Ops...',
            text: texto,
            timer: 2500,
            timerProgressBar: true
        });
    } finally {
        buttonPreRegister.disabled = false;
        buttonPreRegister.textContent = originalText;
    }



    /* Estrelas */
    const starsEl = document.getElementById('stars');
    for (let i = 0; i < 80; i++) {
        const s = document.createElement('div');
        s.className = 'star';
        const size = (Math.random() * 2.5 + 0.5).toFixed(1) + 'px';
        s.style.cssText = [
            `left:${(Math.random() * 100).toFixed(1)}%`,
            `top:${(Math.random() * 55).toFixed(1)}%`,
            `width:${size}`, `height:${size}`,
            `--d:${(Math.random() * 3 + 2).toFixed(1)}s`,
            `animation-delay:${(Math.random() * 3).toFixed(1)}s`
        ].join(';');
        starsEl.appendChild(s);
    }

    /* Vagalumes DOM */
    const scene = document.querySelector('.scene');
    for (let i = 0; i < 12; i++) {
        const f = document.createElement('div');
        f.className = 'firefly';
        f.style.cssText = [
            `left:${(Math.random() * 90 + 5).toFixed(1)}%`,
            `bottom:${(Math.random() * 35 + 5).toFixed(1)}%`,
            `--d:${(Math.random() * 4 + 3).toFixed(1)}s`,
            `--x:${((Math.random() - 0.5) * 80).toFixed(0)}px`,
            `--y:${((Math.random() - 0.5) * 50).toFixed(0)}px`,
            `animation-delay:${(Math.random() * 5).toFixed(1)}s`
        ].join(';');
        scene.appendChild(f);
    }

    /* ─── Controle do modal ───────────────────────────────────────────
     * O login.js usa  $('#modalPreRegisterUser').modal('show'/'hide').
     * Como o Bootstrap JS não é carregado aqui, o shim abaixo
     * sobrescreve $.fn.modal e redireciona para controle via classe CSS.
     * Nenhuma alteração é necessária no login.js.
     * ─────────────────────────────────────────────────────────────── */
    const modalOverlay = document.getElementById('modalPreRegisterUser');

    function openPreRegisterModal() {
        modalOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closePreRegisterModal() {
        modalOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    /* Botão ✕ */
    modalOverlay.querySelector('.close-btn').addEventListener('click', closePreRegisterModal);
    /* Clique no overlay escuro */
    modalOverlay.addEventListener('click', function (e) {
        if (e.target === modalOverlay) closePreRegisterModal();
    });
    /* ESC */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePreRegisterModal();
    });


    (function waitForJQuery() {
        if (window.$ && window.$.fn) {
            window.$.fn.modal = function (action) {
                const el = this[0];
                if (el && el.id === 'modalPreRegisterUser') {
                    if (action === 'show') openPreRegisterModal();
                    if (action === 'hide') closePreRegisterModal();
                }
                return this;
            };
        } else {
            setTimeout(waitForJQuery, 50);
        }
    })();


});