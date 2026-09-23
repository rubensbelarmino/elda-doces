/**
 * ============================================================================
 * JAVASCRIPT PRINCIPAL (FRONTEND CLIENT-SIDE) — ELDA BOLOS E DOCES
 * ============================================================================
 * Codigo Vanilla JS com foco em acessibilidade, mascaras de entrada,
 * prefetching preditivo e pre-carregamento antecipado de imagens.
 * ============================================================================
 */
(() => {
  'use strict';

  const prefetched = new Set();

  
  /**
   * Injeta <link rel="prefetch"> para rotas criticas assim que o usuario interage com o link.
   * @param {string} href
   */
  const prefetchPage = (href) => {
    if (!href || prefetched.has(href) || !/^\/(entrar|criar-conta|carrinho)$/.test(href)) return;
    prefetched.add(href);
    const link = document.createElement('link');
    link.rel = 'prefetch';
    link.as = 'document';
    link.href = href;
    document.head.appendChild(link);
  };

  document.querySelectorAll('a[href="/entrar"], a[href="/criar-conta"], a[href="/carrinho"]').forEach((anchor) => {
    ['pointerenter', 'focusin', 'touchstart'].forEach((eventName) => {
      anchor.addEventListener(eventName, () => prefetchPage(anchor.getAttribute('href')), { once: true, passive: true });
    });
  });

  const toggle = document.querySelector('[data-menu-toggle]');
  const menu = document.querySelector('[data-menu]');

  /**
   * Fecha o menu de navegacao mobile e restaura o foco e scroll da pagina.
   */
  const closeMenu = () => {
    toggle?.setAttribute('aria-expanded', 'false');
    menu?.classList.remove('is-open');
    document.body.classList.remove('menu-open');
  };

  toggle?.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', String(!open));
    menu?.classList.toggle('is-open', !open);
    document.body.classList.toggle('menu-open', !open);
  });

  menu?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && menu?.classList.contains('is-open')) {
      closeMenu();
    }
  });

  document.addEventListener('click', (e) => {
    if (menu?.classList.contains('is-open') && !menu.contains(e.target) && !toggle?.contains(e.target)) {
      closeMenu();
    }
  });

  document.querySelectorAll('.toast button').forEach((button) => {
    button.addEventListener('click', () => button.closest('.toast')?.remove());
  });

  window.setTimeout(() => {
    document.querySelectorAll('.toast').forEach((toast) => toast.classList.add('toast-out'));
  }, 4800);

  document.querySelectorAll('[data-auto-submit]').forEach((select) => {
    select.addEventListener('change', () => select.form?.submit());
  });

  const phone = document.querySelector('input[name="phone"]');
  phone?.addEventListener('input', () => {
    const digits = phone.value.replace(/\D/g, '').slice(0, 11);
    phone.value = digits.length > 10
      ? digits.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3')
      : digits.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
  });

  const zip = document.querySelector('input[name="zip"]');
  zip?.addEventListener('input', () => {
    const digits = zip.value.replace(/\D/g, '').slice(0, 8);
    zip.value = digits.replace(/(\d{5})(\d{0,3})/, '$1-$2');
  });

  const cpf = document.querySelector('input[name="cpf"]');
  cpf?.addEventListener('input', () => {
    const digits = cpf.value.replace(/\D/g, '').slice(0, 11);
    cpf.value = digits
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
  });

  const preloadedImages = new Set();

  const preloadImageSrc = (src) => {
    if (!src || preloadedImages.has(src) || src.startsWith('data:')) return;
    preloadedImages.add(src);
    try {
      const link = document.createElement('link');
      link.rel = 'preload';
      link.as = 'image';
      link.href = src;
      document.head.appendChild(link);
    } catch {}
    const pre = new Image();
    pre.src = src;
  };

  const markImageLoaded = (img) => {
    img.classList.add('is-loaded');
  };

  const applyImageSource = (img) => {
    const filename = img.dataset.base64Image;
    if (!img.src || img.src.startsWith('data:image/svg') || img.src.startsWith('data:image/webp')) {
      if (filename && /^[a-zA-Z0-9._-]+\.(jpg|jpeg|png|webp)$/i.test(filename)) {
        img.src = `/assets/images/${encodeURIComponent(filename)}`;
      }
    }
  };

  const setupImage = (img) => {
    applyImageSource(img);

    if (img.complete && img.naturalWidth > 0) {
      markImageLoaded(img);
    } else {
      img.addEventListener('load', () => markImageLoaded(img), { once: true });
      img.addEventListener('error', () => {
        const filename = img.dataset.base64Image;
        if (filename && !img.dataset.apiFallback) {
          img.dataset.apiFallback = '1';
          fetch(`/api/imagens/${encodeURIComponent(filename)}`)
            .then((r) => (r.ok ? r.json() : Promise.reject()))
            .then(({ data }) => {
              if (data) {
                img.src = data;
                markImageLoaded(img);
              }
            })
            .catch(() => {
              img.closest('.product-image, .detail-image, .cart-line, .checkout-line, .product-cell')?.classList.add('image-error');
            });
        }
      }, { once: true });
    }
  };

  const imageElements = [...document.querySelectorAll('img[data-base64-image], .product-image img, .detail-image img, .cart-line img, .checkout-line img, .product-cell img')];
  imageElements.forEach(setupImage);

  // Eagerly preload top/priority images
  imageElements
    .filter((img) => img.dataset.imagePriority === 'high' || img.getAttribute('loading') === 'eager')
    .forEach((img) => {
      if (img.loading === 'lazy') img.loading = 'eager';
      if (img.src) preloadImageSrc(img.src);
    });

  // Anticipatory IntersectionObserver: preload images 1500px in advance before scrolling into view
  if ('IntersectionObserver' in window) {
    const anticipatoryObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const img = entry.target;
        if (img.loading === 'lazy') {
          img.loading = 'eager';
        }
        if (img.src && !img.complete) {
          preloadImageSrc(img.src);
        }
        observer.unobserve(img);
      });
    }, { rootMargin: '1500px 0px' });

    imageElements.forEach((img) => {
      if (!img.complete) {
        anticipatoryObserver.observe(img);
      }
    });
  }

  // Anticipatory Idle Preloader: proactively warm up remaining images in background during idle time
  const idleQueue = [...imageElements].filter((img) => !img.complete);
  /**
   * Fila ociosa: pre-carrega proativamente as imagens dos produtos durante periodos ociosos da CPU.
   * @param {IdleDeadline} deadline
   */
  const drainIdlePreload = (deadline) => {
    while (idleQueue.length > 0 && (!deadline || deadline.timeRemaining() > 8)) {
      const nextImg = idleQueue.shift();
      if (nextImg && nextImg.src && !nextImg.complete && !preloadedImages.has(nextImg.src)) {
        preloadImageSrc(nextImg.src);
      }
    }
    if (idleQueue.length > 0) {
      if ('requestIdleCallback' in window) {
        window.requestIdleCallback(drainIdlePreload, { timeout: 2000 });
      } else {
        window.setTimeout(drainIdlePreload, 250);
      }
    }
  };

  if ('requestIdleCallback' in window) {
    window.requestIdleCallback(drainIdlePreload, { timeout: 1500 });
  } else {
    window.setTimeout(drainIdlePreload, 300);
  }

  document.querySelector('[data-copy-pix]')?.addEventListener('click', async (event) => {
    const input = document.querySelector('#pix-code');
    if (!input) return;
    try {
      await navigator.clipboard.writeText(input.value);
      event.currentTarget.textContent = 'Código copiado ✓';
    } catch {
      input.select();
      document.execCommand('copy');
      event.currentTarget.textContent = 'Código copiado ✓';
    }
  });

  const codeInput = document.querySelector('.code-input');
  codeInput?.addEventListener('input', () => {
    codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, 6);
    if (codeInput.value.length === 6) {
      codeInput.form?.querySelector('button[type="submit"]')?.focus();
    }
  });

  const countdown = document.querySelector('[data-countdown]');
  if (countdown) {
    const tick = () => {
      const remaining = Math.max(0, Number(countdown.dataset.expires) - Math.floor(Date.now() / 1000));
      countdown.textContent = `${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')}`;
      if (remaining === 0) {
        countdown.closest('.code-help')?.classList.add('expired');
      }
    };
    tick();
    window.setInterval(tick, 1000);
  }

  const pixCard = document.querySelector('.pix-success[data-order-number][data-status="pending"]');
  if (pixCard) {
    const orderNumber = pixCard.dataset.orderNumber;
    let isChecking = false;
    const pollTimer = window.setInterval(async () => {
      if (isChecking) return;
      isChecking = true;
      try {
        const res = await fetch('/api/pedidos/' + encodeURIComponent(orderNumber) + '/status');
        if (res.ok) {
          const data = await res.json();
          if (data.status === 'approved') {
            window.clearInterval(pollTimer);
            window.location.reload();
          }
        }
      } catch (err) {
      } finally {
        isChecking = false;
      }
    }, 3000);
  }

  const fileInput = document.getElementById('product_image_file');
  const previewImg = document.getElementById('product_preview_img');
  const previewName = document.getElementById('product_preview_name');
  const previewBadge = document.getElementById('product_preview_badge');
  const selectImage = document.getElementById('product_image_select');
  const dropzone = document.getElementById('product_dropzone');

  if (fileInput && previewImg) {
    const handleFile = (file) => {
      if (!file) return;

      const validTypes = ['image/jpeg', 'image/png'];
      const isExtValid = /\.(jpe?g|png)$/i.test(file.name);
      if (!validTypes.includes(file.type) && !isExtValid) {
        alert('Formato de imagem inválido. Selecione apenas fotos nos formatos JPG ou PNG.');
        fileInput.value = '';
        return;
      }

      if (file.size > 4 * 1024 * 1024) {
        alert('O arquivo selecionado é muito grande. O limite máximo permitido é 4MB.');
        fileInput.value = '';
        return;
      }

      const reader = new FileReader();
      reader.onload = (e) => {
        previewImg.src = e.target.result;
        previewImg.classList.add('is-loaded');
      };
      reader.readAsDataURL(file);

      if (previewName) {
        const sizeKb = (file.size / 1024).toFixed(0);
        previewName.textContent = `${file.name} (${sizeKb} KB)`;
      }
      if (previewBadge) {
        previewBadge.textContent = 'Nova foto selecionada';
        previewBadge.classList.add('badge-new');
      }
      if (selectImage) {
        selectImage.value = '';
      }
    };

    fileInput.addEventListener('change', (e) => {
      const file = e.target.files && e.target.files[0];
      handleFile(file);
    });

    if (dropzone) {
      ['dragenter', 'dragover'].forEach((evtName) => {
        dropzone.addEventListener(evtName, (e) => {
          e.preventDefault();
          e.stopPropagation();
          dropzone.classList.add('dragover');
        });
      });

      ['dragleave', 'drop'].forEach((evtName) => {
        dropzone.addEventListener(evtName, (e) => {
          e.preventDefault();
          e.stopPropagation();
          dropzone.classList.remove('dragover');
        });
      });

      dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt && dt.files;
        if (files && files.length > 0) {
          fileInput.files = files;
          handleFile(files[0]);
        }
      });
    }

    if (selectImage) {
      selectImage.addEventListener('change', () => {
        const val = selectImage.value;
        if (val) {
          fileInput.value = '';
          previewImg.src = `/assets/images/${encodeURIComponent(val)}`;
          previewImg.classList.add('is-loaded');
          if (previewName) previewName.textContent = val;
          if (previewBadge) {
            previewBadge.textContent = 'Da galeria';
            previewBadge.classList.remove('badge-new');
          }
        }
      });
    }
  }

  document.querySelectorAll('.password-toggle').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const input = btn.closest('.password-wrapper')?.querySelector('input');
      if (!input) return;
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      btn.classList.toggle('is-visible', isPassword);
      btn.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Ver senha');
      btn.setAttribute('title', isPassword ? 'Ocultar senha' : 'Ver senha');
      input.focus();
    });
  });
})();
