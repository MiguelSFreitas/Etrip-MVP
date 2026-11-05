/**
 * tema.js
 */

document.addEventListener('DOMContentLoaded', function () {
    const botao = document.getElementById('toggle-tema');
    if (!botao) return;

    function atualizarIcone() {
        const escuro = document.documentElement.getAttribute('data-theme') === 'dark';
        botao.textContent = escuro ? '☀️' : '🌙';
    }

    atualizarIcone();

    botao.addEventListener('click', function () {
        const estaEscuro = document.documentElement.getAttribute('data-theme') === 'dark';

        if (estaEscuro) {
            document.documentElement.removeAttribute('data-theme');
            localStorage.setItem('etrip-tema', 'light');
        } else {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('etrip-tema', 'dark');
        }

        atualizarIcone();
    });
});