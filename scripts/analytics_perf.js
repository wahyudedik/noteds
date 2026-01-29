#!/usr/bin/env node
import fs from 'fs';
import path from 'path';

const root = process.cwd();
const buildDir = path.join(root, 'public', 'build', 'assets');
const files = fs.existsSync(buildDir) ? fs.readdirSync(buildDir) : [];
const analyticsChunks = files.filter(f => /^Analytics-.*\.js$/.test(f));
const sizes = analyticsChunks.map(f => {
    const full = path.join(buildDir, f);
    const stat = fs.statSync(full);
    return { file: f, bytes: stat.size };
});
const total = sizes.reduce((a, b) => a + b.bytes, 0);
const report = {
    timestamp: new Date().toISOString(),
    chunks: sizes,
    total_bytes: total,
};
const out = path.join(root, 'storage', 'logs', 'analytics_perf_report.json');
fs.mkdirSync(path.dirname(out), { recursive: true });
fs.writeFileSync(out, JSON.stringify(report, null, 2));
console.log(`Analytics perf report: ${out}`);
console.log(`Total JS bytes for analytics chunks: ${total}`);
