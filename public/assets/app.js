(() => {
  'use strict';

  const prefetched = new Set();

  
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

  const imageRequests = new Map();
  const readSession = (key) => { try { return window.sessionStorage.getItem(key); } catch { return null; } };
  const writeSession = (key, value) => { try { window.sessionStorage.setItem(key, value); } catch {  } };

  
  const loadBase64Image = (img) => {
    if (img.dataset.imageRequested === '1') return;
    img.dataset.imageRequested = '1';
    const filename = img.dataset.base64Image;
    if (!filename || !/^[a-zA-Z0-9._-]+\.(jpg|jpeg|png|webp)$/i.test(filename)) return;

    const key = `doce:image:${filename}`;
    const cached = readSession(key);
    if (cached) {
      img.src = cached;
      img.classList.add('is-loaded');
      return;
    }

    if (!imageRequests.has(filename)) {
      imageRequests.set(filename, fetch(`/api/imagens/${encodeURIComponent(filename)}`, { 
        credentials: 'same-origin', 
        cache: 'no-store' 
      })
      .then((response) => {
        if (!response.ok) throw new Error('Imagem indisponível');
        return response.json();
      })
      .then(({ data }) => {
        if (typeof data !== 'string' || !data.startsWith('data:image/')) throw new Error('Imagem inválida');
        writeSession(key, data);
        return data;
      }));
    }

    imageRequests.get(filename)
      .then((data) => { 
        img.src = data; 
        img.classList.add('is-loaded'); 
      })
      .catch(() => {
        img.closest('.product-image, .detail-image, .cart-line, .checkout-line, .product-cell')?.classList.add('image-error');
      });
  };

  const imageElements = [...document.querySelectorAll('img[data-base64-image]')];

  imageElements.filter((img) => img.dataset.imagePriority === 'high').forEach(loadBase64Image);

  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        loadBase64Image(entry.target);
        observer.unobserve(entry.target);
      });
    }, { rootMargin: '420px 0px' });

    imageElements.filter((img) => img.dataset.imagePriority !== 'high').forEach((img) => imageObserver.observe(img));
  } else {
    imageElements.forEach(loadBase64Image);
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
})();
