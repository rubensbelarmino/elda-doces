/**
 * ==========================================================================
 * ELDA BOLOS E DOCES — INTERATIVIDADE JAVASCRIPT VANILLA
 * Projeto Acadêmico: Entrega 5 — Recursos de Interface e UX Acessível
 * ==========================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  // 1. MENU MOBILE ACESSÍVEL (DRAWER)
  const menuToggle = document.querySelector('[data-menu-toggle]');
  const mainNav = document.querySelector('[data-menu]');

  if (menuToggle && mainNav) {
    menuToggle.addEventListener('click', () => {
      const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
      menuToggle.setAttribute('aria-expanded', String(!isExpanded));
      mainNav.classList.toggle('is-open', !isExpanded);
      document.body.classList.toggle('menu-open', !isExpanded);

      if (!isExpanded) {
        const firstLink = mainNav.querySelector('a');
        firstLink?.focus();
      }
    });

    // Fechar ao pressionar a tecla ESC
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mainNav.classList.contains('is-open')) {
        menuToggle.setAttribute('aria-expanded', 'false');
        mainNav.classList.remove('is-open');
        document.body.classList.remove('menu-open');
        menuToggle.focus();
      }
    });

    // Fechar ao clicar em qualquer link do menu
    mainNav.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        menuToggle.setAttribute('aria-expanded', 'false');
        mainNav.classList.remove('is-open');
        document.body.classList.remove('menu-open');
      });
    });
  }

  // 2. FEEDBACK TÁTIL AO ADICIONAR ITEM À SACOLA
  const buyButtons = document.querySelectorAll('.buy-button');
  buyButtons.forEach(button => {
    button.addEventListener('click', () => {
      const productTitle = button.closest('.product-card')?.querySelector('.product-title')?.textContent || 'item';
      
      // Animação temporária do botão
      const originalText = button.innerHTML;
      button.innerHTML = '✓';
      button.style.backgroundColor = 'var(--green)';
      
      setTimeout(() => {
        button.innerHTML = originalText;
        button.style.backgroundColor = '';
      }, 1200);

      // Atualização anunciada para leitores de tela
      const a11yAnnounce = document.getElementById('a11y-announce');
      if (a11yAnnounce) {
        a11yAnnounce.textContent = `${productTitle} adicionado com sucesso à sua sacola.`;
      }
    });
  });
});
