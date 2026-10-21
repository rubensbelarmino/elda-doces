const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { execFileSync } = require('node:child_process');

const root = path.join(__dirname, '..');
const read = (name) => fs.readFileSync(path.join(root, 'public', 'assets', name), 'utf8');

test('o CSS servido corresponde à fonte compilada', () => {
  execFileSync(process.execPath, [path.join(root, 'bin', 'build-css.cjs'), '--check']);
});

for (const name of ['app.css', 'app.min.css']) {
  test(`${name} preserva controles de endereço e senha`, () => {
    const css = read(name).replace(/\s+/g, '');
    for (const selector of [
      '.save-address-wrapper',
      '.save-address-wrapper:hover',
      '.save-address-label',
      '.save-address-labelinput[type="checkbox"]',
      '.save-address-text',
      '.password-wrapper',
      '.password-wrapperinput',
      '.password-toggle',
    ]) {
      assert.ok(css.includes(selector + '{'), `Regra ausente: ${selector}`);
    }
    assert.match(css, /\.password-wrapperinput\{[^}]*padding-right:38px!important/);
    assert.match(css, /\.password-toggle\.eye-closed\{display:none;?\}/);
    assert.match(css, /\.password-toggle\.is-visible\.eye-open\{display:none;?\}/);
    assert.match(css, /\.password-toggle\.is-visible\.eye-closed\{display:block;?\}/);
    assert.match(css, /\.save-address-labelinput\[type="checkbox"\]\{[^}]*width:18px/);
  });
}

test('o layout carrega os assets minificados', () => {
  const layout = fs.readFileSync(path.join(root, 'views', 'layout.php'), 'utf8');
  assert.ok(layout.includes("versioned_asset('app.min.css')"));
  assert.ok(layout.includes("versioned_asset('app.min.js')"));
});
