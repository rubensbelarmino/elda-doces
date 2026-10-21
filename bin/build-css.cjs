const fs = require('node:fs');
const path = require('node:path');
const CleanCSS = require('clean-css');

const assets = path.join(__dirname, '..', 'public', 'assets');
const output = new CleanCSS({ level: 0, rebase: false }).minify(
  fs.readFileSync(path.join(assets, 'app.css'), 'utf8').replace(/\r\n/g, '\n'),
);
if (output.errors.length || output.warnings.length) {
  throw new Error([...output.errors, ...output.warnings].join('\n'));
}
const expected = output.styles + '\n';
const target = path.join(assets, 'app.min.css');
if (process.argv.includes('--check')) {
  if (fs.readFileSync(target, 'utf8').replace(/\r\n/g, '\n') !== expected) {
    throw new Error('app.min.css está desatualizado. Execute npm run build:css.');
  }
  console.log('CSS fonte e minificado sincronizados.');
} else {
  fs.writeFileSync(target, expected);
  console.log('app.min.css gerado a partir de app.css.');
}
