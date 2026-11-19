// Local stand-in for the production host, used for Lighthouse runs (see docs/reference/SEO_CHECKLIST.md).
// Serves static files straight from public/ (like Apache/LiteSpeed), brotli-compresses text and sends the production cache headers.
// Only PHP pages go to `php artisan serve` on :8000. Usage: node scripts/lighthouse-proxy.mjs, then test http://127.0.0.1:8001/
import http from 'node:http'; import zlib from 'node:zlib'; import fs from 'node:fs'; import path from 'node:path';
const ROOT = path.resolve(process.argv[2] || 'public');   // run from the project root
const MIME = { '.css': 'text/css', '.js': 'text/javascript', '.webp': 'image/webp', '.woff2': 'font/woff2', '.mp4': 'video/mp4', '.svg': 'image/svg+xml', '.png': 'image/png', '.jpg': 'image/jpeg', '.ico': 'image/x-icon', '.json': 'application/json' };
const TEXT = /text\/html|text\/css|javascript|svg|json|xml|text\/plain/;
const send = (req, res, status, h, stream) => {
  const br = TEXT.test(String(h['content-type'] || '')) && (req.headers['accept-encoding'] || '').includes('br');
  if (br) { delete h['content-length']; h['content-encoding'] = 'br'; h['vary'] = 'Accept-Encoding'; res.writeHead(status, h); stream.pipe(zlib.createBrotliCompress({ params: { [zlib.constants.BROTLI_PARAM_QUALITY]: 5 } })).pipe(res); }
  else { res.writeHead(status, h); stream.pipe(res); }
};
http.createServer((req, res) => {
  const url = decodeURI(req.url.split('?')[0]);
  if (/^\/(build|storage|images|videos)\//.test(url)) {
    const file = path.join(ROOT, url);
    if (file.startsWith(ROOT) && fs.existsSync(file) && fs.statSync(file).isFile()) {
      const s = fs.statSync(file);
      return send(req, res, 200, { 'content-type': MIME[path.extname(file)] || 'application/octet-stream', 'content-length': s.size, 'cache-control': 'public, max-age=31536000, immutable' }, fs.createReadStream(file));
    }
  }
  const headers = { ...req.headers, host: '127.0.0.1:8000' }; delete headers['accept-encoding'];
  const up = http.request({ host: '127.0.0.1', port: 8000, path: req.url, method: req.method, headers }, (u) => send(req, res, u.statusCode, { ...u.headers }, u));
  up.on('error', () => { res.writeHead(502); res.end(); }); req.pipe(up);
}).listen(8001, '127.0.0.1', () => console.log('proxy up'));
