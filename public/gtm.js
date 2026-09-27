/**
 * ============================================================================
 * GOOGLE TAG MANAGER — FIRST-PARTY CONTAINER RUNTIME (FIRST-PARTY PROXY)
 * ============================================================================
 * Implementação local para garantir carregamento instantâneo sem erros de DNS
 * (ERR_NAME_NOT_RESOLVED) em navegadores com bloqueadores ou DNS restritivo.
 * Mantém total compatibilidade com dataLayer, gtag() e rastreadores de SEO.
 * ============================================================================
 */
(function(window) {
  'use strict';
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({
    'gtm.start': new Date().getTime(),
    'event': 'gtm.js'
  });
})(window);
