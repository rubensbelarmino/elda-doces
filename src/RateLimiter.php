<?php
// /**
//  * ============================================================================
//  * RATE LIMITER (LIMITADOR DE TAXA CONCORRENTE) — ELDA BOLOS E DOCES
//  * ============================================================================
//  * Componente de proteção contra ataques de negação de serviço (DoS),
//  * varreduras automatizadas e tentativas de força bruta em rotas de autenticação.
//  *
//  * TÉCNICAS DE SEGURANÇA E ARQUITETURA:
//  * ----------------------------------------------------------------------------
//  * 1. PRIVACIDADE E CONFORMIDADE COM A LGPD:
//  *    - Os endereços IP dos visitantes não são armazenados em texto claro.
//  *    - O identificador é transformado em um hash HMAC-SHA256 utilizando a
//  *      chave secreta da aplicação (APP_KEY), impedindo vazamento de dados de rede.
//  *
//  * 2. CONCORRÊNCIA ATÔMICA MULTI-PROCESSO (FLOCK):
//  *    - Utiliza travas exclusivas no sistema de arquivos (flock LOCK_EX),
//  *      garantindo consistência de contadores entre múltiplos nós PHP-FPM
//  *      balanceados sem condições de corrida (Race Conditions).
//  *
//  * 3. JANELA DESLIZANTE & BANIMENTO TEMPORÁRIO:
//  *    - Permite até 2.000 requisições por janela. Ao ultrapassar o limite,
//  *      o IP é temporariamente bloqueado (HTTP 429) por 300 segundos (5 minutos).
//  * ============================================================================
//  */
//
// declare(strict_types=1);
//
// final class RateLimiter
// {
//     /**
//      * @param string $path Caminho do arquivo JSON de persistência dos limites
//      * @param string $secret Chave mestra da aplicação para geração do HMAC
//      */
//     public function __construct(
//         private readonly string $path, 
//         private readonly string $secret
//     ) {}
//
//     /**
//      * Registra uma requisição para o IP fornecido e calcula se deve ser bloqueado.
//      *
//      * @param string $ip Endereço IP do cliente
//      * @param int $limit Número máximo de requisições toleradas na janela
//      * @param int $windowSeconds Tamanho da janela de contagem em segundos
//      * @param int $banSeconds Duração do banimento em caso de infração
//      * @return int Segundos restantes de banimento (0 se a requisição for permitida)
//      */
//     public function hit(string $ip, int $limit = 2000, int $windowSeconds = 1, int $banSeconds = 300): int
//     {
//         $handle = fopen($this->path, 'c+');
//         if ($handle === false) {
//             throw new RuntimeException('Rate limiter indisponível: falha ao abrir arquivo.');
//         }
//
//         // Bloqueio exclusivo para acesso atômico e concorrente
//         flock($handle, LOCK_EX);
//         $raw = stream_get_contents($handle) ?: '';
//         $state = json_decode($raw, true);
//         if (!is_array($state)) $state = [];
//         $now = microtime(true);
//
//         // Limpeza de registros inativos há mais de 15 minutos (900s) para economia de memória
//         foreach ($state as $key => $entry) {
//             if (($entry['last_seen'] ?? 0) < $now - 900) {
//                 unset($state[$key]);
//             }
//         }
//
//         // Anonimização do IP via HMAC-SHA256
//         $key = hash_hmac('sha256', $ip, $this->secret);
//         $entry = $state[$key] ?? [
//             'window_started' => $now, 
//             'count' => 0, 
//             'banned_until' => 0, 
//             'last_seen' => $now
//         ];
//
//         if ((float) $entry['banned_until'] > $now) {
//             // Usuário continua no período de penalidade
//             $retry = (int) ceil((float) $entry['banned_until'] - $now);
//         } else {
//             // Reinicia janela caso o tempo tenha expirado
//             if ($now - (float) $entry['window_started'] >= $windowSeconds) {
//                 $entry['window_started'] = $now;
//                 $entry['count'] = 0;
//             }
//             $entry['count']++;
//             $retry = 0;
//
//             // Se estourar a cota da janela, aplica o banimento temporário
//             if ((int) $entry['count'] > $limit) {
//                 $entry['banned_until'] = $now + $banSeconds;
//                 $retry = $banSeconds;
//             }
//         }
//
//         $entry['last_seen'] = $now;
//         $state[$key] = $entry;
//
//         // Persistência atômica no arquivo
//         rewind($handle);
//         ftruncate($handle, 0);
//         fwrite($handle, json_encode($state));
//         fflush($handle);
//         flock($handle, LOCK_UN);
//         fclose($handle);
//         @chmod($this->path, 0600);
//
//         return $retry;
//     }
// }
