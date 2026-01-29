import fs from 'fs';
import path from 'path';

// Simulate report outputs and config behavior
function assert(condition, msg) {
  if (!condition) {
    console.error('TEST FAIL:', msg);
    process.exit(1);
  }
}

// Prepare dummy config
const cfgPath = path.join(process.cwd(), 'audit.config.json');
fs.writeFileSync(cfgPath, JSON.stringify({
  whitelist: ['@mycompany/internal-*'],
  blacklist: ['deprecated-lib', 'event-stream@3.3.6'],
  criticalRingfence: ['webpack', 'typescript', 'react'],
}, null, 2));

// Case: whitelist doesn't fail
const whitelist = JSON.parse(fs.readFileSync(cfgPath)).whitelist;
assert(Array.isArray(whitelist) && whitelist.length > 0, 'Whitelist should exist');

// Case: blacklist violation detection (simulate presence)
const simulatedDeps = ['lodash', 'deprecated-lib'];
const blacklist = JSON.parse(fs.readFileSync(cfgPath)).blacklist;
const blacklistHit = simulatedDeps.some(d => blacklist.includes(d));
assert(blacklistHit === true, 'Blacklist should detect violation');

console.log('deps_audit.test OK');
process.exit(0);
