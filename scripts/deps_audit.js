#!/usr/bin/env node
import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';
import https from 'https';
let nodemailer = null;
try {
  nodemailer = await import('nodemailer');
} catch {}

const root = process.cwd();
const pkgPath = path.join(root, 'package.json');
const pkg = JSON.parse(fs.readFileSync(pkgPath, 'utf8'));

const dependencies = Object.assign({}, pkg.dependencies || {}, pkg.devDependencies || {});
const depNames = Object.keys(dependencies);

const exts = ['.js', '.ts', '.vue'];
function walk(dir) {
  const items = fs.readdirSync(dir, { withFileTypes: true });
  let files = [];
  for (const it of items) {
    if (it.name.startsWith('.')) continue;
    const p = path.join(dir, it.name);
    if (it.isDirectory()) {
      if (['node_modules', 'vendor', 'storage', 'public'].includes(it.name)) continue;
      files = files.concat(walk(p));
    } else {
      if (exts.includes(path.extname(it.name))) files.push(p);
    }
  }
  return files;
}

const files = walk(root);
const usage = {};
for (const dep of depNames) {
  usage[dep] = { used: false, files: [] };
}

const importRegexes = [
  (name) => new RegExp(`from\\s+['"]${name}(/[^'"]+)?['"]`, 'g'),
  (name) => new RegExp(`require\\(\\s*['"]${name}(/[^'"]+)?['"]\\s*\\)`, 'g'),
  (name) => new RegExp(`import\\s+['"]${name}(/[^'"]+)?['"]['"]?`, 'g'),
];

for (const f of files) {
  const content = fs.readFileSync(f, 'utf8');
  for (const dep of depNames) {
    for (const rx of importRegexes) {
      if (rx(dep).test(content)) {
        usage[dep].used = true;
        usage[dep].files.push(path.relative(root, f));
        break;
      }
    }
  }
}

const unused = Object.entries(usage)
  .filter(([_, v]) => !v.used)
  .map(([k]) => k);

// Load audit config
const configPath = path.join(root, 'audit.config.json');
let auditConfig = {};
if (fs.existsSync(configPath)) {
  auditConfig = JSON.parse(fs.readFileSync(configPath, 'utf8'));
}

// Run npm audit
let npmAudit = {};
try {
  const auditJson = execSync('npm audit --json', { stdio: ['ignore', 'pipe', 'ignore'] }).toString();
  npmAudit = JSON.parse(auditJson);
} catch (e) {
  npmAudit = { error: 'npm audit failed', details: e.message };
}

// Summarize vulnerabilities
const advisories = npmAudit.advisories || {};
const vulnCounts = { critical: 0, high: 0, moderate: 0, low: 0 };
const affectedPackages = [];
for (const key in advisories) {
  const adv = advisories[key];
  vulnCounts[adv.severity] = (vulnCounts[adv.severity] || 0) + 1;
  affectedPackages.push({
    module_name: adv.module_name,
    vulnerable_versions: adv.vulnerable_versions,
    severity: adv.severity,
    recommendation: adv.recommendation || adv.fix_available ? 'update' : 'review',
  });
}

// Threshold check
const thresholdOk = (vulnCounts.critical === 0) && (vulnCounts.high <= 3);

// Whitelist/Blacklist enforcement
let blacklistHit = false;
const blacklist = auditConfig.blacklist || [];
for (const pkg of blacklist) {
  if (depNames.includes(pkg) || affectedPackages.find(p => (p.module_name + '@' + (p.vulnerable_versions || '')).includes(pkg))) {
    blacklistHit = true;
    break;
  }
}

// Prepare report
const report = {
  timestamp: new Date().toISOString(),
  used: Object.fromEntries(Object.entries(usage).filter(([_, v]) => v.used)),
  unused,
  audit: {
    summary: vulnCounts,
    affected: affectedPackages,
    thresholdOk,
  }
};

const outPath = path.join(root, 'storage', 'logs', 'deps_audit_report.json');
try {
  fs.mkdirSync(path.dirname(outPath), { recursive: true });
} catch {}
fs.writeFileSync(outPath, JSON.stringify(report, null, 2));
console.log(`Dependency audit written to ${outPath}`);
console.log(`Unused dependencies: ${unused.join(', ') || '(none)'}`);
console.log(`Vulnerability summary: critical=${vulnCounts.critical}, high=${vulnCounts.high}, moderate=${vulnCounts.moderate}, low=${vulnCounts.low}`);

// Slack notification if threshold violated
async function notifySlack(msg) {
  const url = process.env.SLACK_WEBHOOK_URL;
  if (!url) return;
  try {
    const data = JSON.stringify({ text: msg });
    const { hostname, pathname, protocol } = new URL(url);
    const options = {
      hostname,
      path: pathname,
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(data) }
    };
    await new Promise((resolve, reject) => {
      const req = https.request(options, res => {
        res.on('data', () => {});
        res.on('end', resolve);
      });
      req.on('error', reject);
      req.write(data);
      req.end();
    });
  } catch {}
}

// Email notification if threshold violated
async function notifyEmail(subject, text) {
  if (!nodemailer) return;
  const host = process.env.SMTP_HOST;
  const user = process.env.SMTP_USER;
  const pass = process.env.SMTP_PASS;
  const to = process.env.SECURITY_EMAILS;
  if (!host || !user || !pass || !to) return;
  try {
    const transporter = nodemailer.createTransport({
      host, port: parseInt(process.env.SMTP_PORT || '587', 10), secure: false,
      auth: { user, pass }
    });
    await transporter.sendMail({
      from: `"Deps Audit" <${user}>`,
      to,
      subject,
      text
    });
  } catch {}
}

if (!thresholdOk || blacklistHit) {
  const reason = blacklistHit ? 'blacklist violation' : 'threshold exceeded';
  const msg = `Dependency audit failed: ${reason}. critical=${vulnCounts.critical}, high=${vulnCounts.high}`;
  await notifySlack(msg);
  await notifyEmail('Dependency Audit Failed', msg);
  process.exit(1);
}

process.exit(0);
