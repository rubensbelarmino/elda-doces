<?php
// /**
//  * ============================================================================
//  * SERVIÇO DE E-MAILS TRANSACIONAIS (MAILER) — ELDA BOLOS E DOCES
//  * ============================================================================
//  * Gerencia o disparo de e-mails transacionais de autenticação (código 2FA)
//  * e notificações com suporte a múltiplos provedores de transporte (SMTP, Mail, Log).
//  *
//  * ARQUITETURA E SEGURANÇA:
//  * ----------------------------------------------------------------------------
//  * 1. SUPORTE A TLS & AUTENTICAÇÃO SMTP:
//  *    - Cliente SMTP nativo via sockets seguros com suporte a STARTTLS
//  *      (compatível com Gmail, SendGrid, Mailgun e servidores corporativos).
//  *
//  * 2. MODO DESENVOLVIMENTO (LOG LOCAL ISOLADO):
//  *    - Em ambiente local, salva os e-mails como arquivos HTML em storage/mail/
//  *      com permissões restritas (0600), impedindo que dados de 2FA vazem publicamente.
//  *
//  * 3. TEMPLATES HTML RESPONSIVOS:
//  *    - Estrutura em tabelas com estilos inline, compatível com clientes móveis
//  *      e leitores de tela para máxima acessibilidade.
//  * ============================================================================
//  */
//
// declare(strict_types=1);
//
// final class Mailer
// {
//     /**
//      * @param string $transport Mecanismo de entrega: 'smtp', 'mail' ou 'log'
//      * @param string $fromEmail E-mail remetente
//      * @param string $fromName Nome amigável do remetente
//      * @param string $logDirectory Diretório para armazenamento de logs em desenvolvimento
//      */
//     public function __construct(
//         private readonly string $transport,
//         private readonly string $fromEmail,
//         private readonly string $fromName,
//         private readonly string $logDirectory,
//     ) {}
//
//     /**
//      * Fábrica estática que inicializa o Mailer a partir das variáveis de ambiente (.env).
//      */
//     public static function fromEnvironment(string $storagePath): self
//     {
//         $fromEmail = trim(getenv('MAIL_FROM') ?: 'seguranca@doceatelier.local');
//         if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
//             throw new RuntimeException('MAIL_FROM inválido.');
//         }
//
//         return new self(
//             strtolower(getenv('MAIL_TRANSPORT') ?: 'log'),
//             $fromEmail,
//             getenv('MAIL_FROM_NAME') ?: 'Elda Bolos e Doces',
//             $storagePath . '/mail',
//         );
//     }
//
//     /**
//      * Envia o código de 6 dígitos da verificação em duas etapas (2FA).
//      */
//     public function sendTwoFactorCode(array $user, string $code, int $expiresMinutes): void
//     {
//         $subject = 'Seu código de acesso — Elda Bolos e Doces';
//         $name = htmlspecialchars((string) $user['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
//         $safeCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
//
//         $html = <<<HTML
// <!doctype html>
// <html lang="pt-BR">
// <head><meta charset="utf-8"></head>
// <body style="margin:0;background:#f4ece5;font-family:Arial,sans-serif;color:#2b201f">
// <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:40px 16px;background:#f4ece5">
//   <tr>
//     <td align="center">
//       <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="max-width:560px;background:#fffdf9;border-collapse:collapse">
//         <tr>
//           <td style="padding:30px 38px;background:#51152b;color:#fff">
//             <div style="font-family:Georgia,serif;font-size:28px">Elda <em style="color:#edc2bb">Bolos e Doces</em></div>
//             <div style="margin-top:6px;font-size:9px;letter-spacing:3px">SEGURANÇA DA CONTA</div>
//           </td>
//         </tr>
//         <tr>
//           <td style="padding:42px 38px">
//             <p style="margin:0 0 12px;color:#8a3e57;font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase">Verificação em duas etapas</p>
//             <h1 style="margin:0 0 18px;font:36px/1.15 Georgia,serif">Olá, {$name}.</h1>
//             <p style="margin:0 0 26px;color:#6f615d;font-size:15px;line-height:1.7">Use o código abaixo para concluir seu acesso com segurança. Ele expira em {$expiresMinutes} minutos.</p>
//             <div style="padding:22px;text-align:center;background:#f4e6dd;border:1px solid #e7d0c6;font:bold 34px/1 Arial,sans-serif;letter-spacing:12px;color:#8d2949">{$safeCode}</div>
//             <p style="margin:26px 0 0;color:#8b7d78;font-size:12px;line-height:1.6">Se você não solicitou este código, ignore esta mensagem. Nunca compartilhe este código com ninguém.</p>
//           </td>
//         </tr>
//         <tr>
//           <td style="padding:20px 38px;background:#2f1720;color:#cdb9b3;font-size:10px">
//             © Elda Bolos e Doces · Sua conta protegida com cuidado em Sorocaba.
//           </td>
//         </tr>
//       </table>
//     </td>
//   </tr>
// </table>
// </body>
// </html>
// HTML;
//
//         $this->send((string) $user['email'], $subject, $html);
//     }
//
//     /**
//      * Envia o código aleatório com caracteres do teclado para redefinição de senha.
//      */
//     public function sendPasswordResetCode(array $user, string $code, int $expiresMinutes): void
//     {
//         $subject = 'Código para redefinição de senha — Elda Bolos e Doces';
//         $name = htmlspecialchars((string) ($user['name'] ?? 'Cliente'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
//         $safeCode = htmlspecialchars($code, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
//
//         $html = <<<HTML
// <!doctype html>
// <html lang="pt-BR">
// <head><meta charset="utf-8"></head>
// <body style="margin:0;background:#f4ece5;font-family:Arial,sans-serif;color:#2b201f">
// <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:40px 16px;background:#f4ece5">
//   <tr>
//     <td align="center">
//       <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="max-width:560px;background:#fffdf9;border-collapse:collapse">
//         <tr>
//           <td style="padding:30px 38px;background:#51152b;color:#fff">
//             <div style="font-family:Georgia,serif;font-size:28px">Elda <em style="color:#edc2bb">Bolos e Doces</em></div>
//             <div style="margin-top:6px;font-size:9px;letter-spacing:3px">RECUPERAÇÃO DE ACESSO</div>
//           </td>
//         </tr>
//         <tr>
//           <td style="padding:42px 38px">
//             <p style="margin:0 0 12px;color:#8a3e57;font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase">Redefinição de senha</p>
//             <h1 style="margin:0 0 18px;font:34px/1.15 Georgia,serif">Olá, {$name}.</h1>
//             <p style="margin:0 0 20px;color:#6f615d;font-size:15px;line-height:1.7">Recebemos uma solicitação para redefinir a senha da sua conta na Elda Bolos e Doces.</p>
//             <p style="margin:0 0 26px;color:#6f615d;font-size:15px;line-height:1.7">Copie o código de segurança abaixo e informe na tela de redefinição. Ele expira em {$expiresMinutes} minutos:</p>
//             <div style="padding:20px;text-align:center;background:#f4e6dd;border:1px solid #e7d0c6;font:bold 26px/1.2 'Courier New',Courier,monospace;letter-spacing:4px;color:#8d2949;word-break:break-all">{$safeCode}</div>
//             <p style="margin:26px 0 0;color:#8b7d78;font-size:12px;line-height:1.6">Se você não solicitou esta redefinição de senha, ignore esta mensagem. Sua conta permanece protegida.</p>
//           </td>
//         </tr>
//         <tr>
//           <td style="padding:20px 38px;background:#2f1720;color:#cdb9b3;font-size:10px">
//             © Elda Bolos e Doces · Segurança e privacidade em Sorocaba.
//           </td>
//         </tr>
//       </table>
//     </td>
//   </tr>
// </table>
// </body>
// </html>
// HTML;
//
//         $this->send((string) $user['email'], $subject, $html);
//     }
//
//     /**
//      * Envia mensagem de teste de conectividade do serviço de e-mail.
//      */
//     public function sendDeliveryTest(string $to): void
//     {
//         $subject = 'E-mail configurado com sucesso — Elda Bolos e Doces';
//         $html = <<<HTML
// <!doctype html>
// <html lang="pt-BR">
// <head><meta charset="utf-8"></head>
// <body style="margin:0;background:#f4ece5;font-family:Arial,sans-serif;color:#2b201f">
// <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:40px 16px;background:#f4ece5">
//   <tr>
//     <td align="center">
//       <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="max-width:560px;background:#fffdf9;border-collapse:collapse">
//         <tr>
//           <td style="padding:30px 38px;background:#51152b;color:#fff;font:28px Georgia,serif">
//             Elda <em style="color:#edc2bb">Bolos e Doces</em>
//           </td>
//         </tr>
//         <tr>
//           <td style="padding:42px 38px">
//             <p style="margin:0 0 12px;color:#8a3e57;font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase">Configuração concluída</p>
//             <h1 style="margin:0 0 18px;font:36px/1.15 Georgia,serif">O correio está funcionando. ✦</h1>
//             <p style="margin:0;color:#6f615d;font-size:15px;line-height:1.7">Este é um teste real de entrega. Os códigos 2FA agora serão enviados diretamente para a caixa de entrada cadastrada.</p>
//           </td>
//         </tr>
//         <tr>
//           <td style="padding:20px 38px;background:#2f1720;color:#cdb9b3;font-size:10px">
//             © Elda Bolos e Doces · Segurança com cuidado em cada detalhe.
//           </td>
//         </tr>
//       </table>
//     </td>
//   </tr>
// </table>
// </body>
// </html>
// HTML;
//
//         $this->send($to, $subject, $html);
//     }
//
//     /**
//      * Roteador de entrega: despacha para o driver configurado.
//      */
//     private function send(string $to, string $subject, string $html): void
//     {
//         if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
//             throw new RuntimeException('Destinatário de e-mail inválido.');
//         }
//
//         switch ($this->transport) {
//             case 'smtp': 
//                 $this->sendLog($to, $subject, $html);
//                 $this->sendSmtp($to, $subject, $html); 
//                 break;
//             case 'mail': 
//                 $this->sendNative($to, $subject, $html); 
//                 break;
//             case 'log': 
//                 $this->sendLog($to, $subject, $html); 
//                 break;
//             default: 
//                 throw new RuntimeException('Transporte de e-mail desconhecido.');
//         }
//     }
//
//     /**
//      * Envia via função mail() nativa do PHP (para servidores com Postfix/Sendmail local).
//      */
//     private function sendNative(string $to, string $subject, string $html): void
//     {
//         $headers = [
//             'MIME-Version: 1.0',
//             'Content-Type: text/html; charset=UTF-8',
//             'From: ' . $this->headerValue($this->fromName) . ' <' . $this->fromEmail . '>',
//             'X-Mailer: EldaBolos-PHP',
//         ];
//         if (!mail($to, $subject, $html, implode("\r\n", $headers))) {
//             throw new RuntimeException('O servidor de e-mail local recusou a mensagem.');
//         }
//     }
//
//     /**
//      * Grava o e-mail em disco para ambiente de teste/desenvolvimento local.
//      */
//     private function sendLog(string $to, string $subject, string $html): void
//     {
//         if (!is_dir($this->logDirectory) && !mkdir($this->logDirectory, 0770, true) && !is_dir($this->logDirectory)) {
//             throw new RuntimeException('Não foi possível criar a caixa de e-mail local.');
//         }
//         $filename = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.html';
//         $content = "<!-- To: {$to} | Subject: {$subject} -->\n" . $html;
//         if (file_put_contents($this->logDirectory . '/' . $filename, $content, LOCK_EX) === false) {
//             throw new RuntimeException('Não foi possível registrar o e-mail local.');
//         }
//         @chmod($this->logDirectory . '/' . $filename, 0600);
//         file_put_contents($this->logDirectory . '/latest', $filename, LOCK_EX);
//         @chmod($this->logDirectory . '/latest', 0600);
//     }
//
//     /**
//      * Envia via conexão direta SMTP com suporte a STARTTLS e criptografia.
//      */
//     private function sendSmtp(string $to, string $subject, string $html): void
//     {
//         $host = getenv('SMTP_HOST') ?: '';
//         $port = (int) (getenv('SMTP_PORT') ?: 587);
//         $encryption = strtolower(getenv('SMTP_ENCRYPTION') ?: 'tls');
//         if ($host === '') throw new RuntimeException('SMTP_HOST não configurado.');
//
//         $remote = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
//         $socket = @stream_socket_client($remote, $errorCode, $errorMessage, 10, STREAM_CLIENT_CONNECT);
//         if (!is_resource($socket)) throw new RuntimeException('Falha ao conectar ao SMTP: ' . $errorMessage);
//         stream_set_timeout($socket, 10);
//
//         try {
//             $this->expect($socket, [220]);
//             $hostname = gethostname() ?: 'elda-doces.local';
//             $this->command($socket, 'EHLO ' . $hostname, [250]);
//
//             if ($encryption === 'tls') {
//                 $this->command($socket, 'STARTTLS', [220]);
//                 if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
//                     throw new RuntimeException('Não foi possível ativar TLS no SMTP.');
//                 }
//                 $this->command($socket, 'EHLO ' . $hostname, [250]);
//             }
//
//             $username = getenv('SMTP_USERNAME') ?: '';
//             if ($username !== '') {
//                 $this->command($socket, 'AUTH LOGIN', [334]);
//                 $this->command($socket, base64_encode($username), [334]);
//                 $this->command($socket, base64_encode(getenv('SMTP_PASSWORD') ?: ''), [235]);
//             }
//
//             $this->command($socket, 'MAIL FROM:<' . $this->fromEmail . '>', [250]);
//             $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
//             $this->command($socket, 'DATA', [354]);
//
//             $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
//             $message = "From: {$this->headerValue($this->fromName)} <{$this->fromEmail}>\r\nTo: <{$to}>\r\nSubject: {$encodedSubject}\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n{$html}";
//             $message = preg_replace('/(?m)^\./', '..', $message) ?? $message;
//
//             fwrite($socket, $message . "\r\n.\r\n");
//             $this->expect($socket, [250]);
//             $this->command($socket, 'QUIT', [221]);
//         } finally {
//             fclose($socket);
//         }
//     }
//
//     private function command($socket, string $command, array $expected): void
//     {
//         fwrite($socket, $command . "\r\n");
//         $this->expect($socket, $expected);
//     }
//
//     private function expect($socket, array $expected): void
//     {
//         $response = '';
//         do {
//             $line = fgets($socket, 1024);
//             if ($line === false) throw new RuntimeException('SMTP encerrou a conexão inesperadamente.');
//             $response .= $line;
//         } while (isset($line[3]) && $line[3] === '-');
//
//         $code = (int) substr($response, 0, 3);
//         if (!in_array($code, $expected, true)) {
//             throw new RuntimeException('Resposta SMTP inesperada: ' . trim($response));
//         }
//     }
//
//     private function headerValue(string $value): string
//     {
//         return trim(str_replace(["\r", "\n"], '', $value));
//     }
// }
