#!/usr/bin/env node
import { execSync } from 'child_process';
import os from 'os';
import path from 'path';
import fs from 'fs';

function getDiskFree() {
  try {
    // Windows PowerShell
    const out = execSync('wmic logicaldisk get size,freespace,caption', { stdio: ['ignore', 'pipe', 'ignore'] }).toString();
    const lines = out.trim().split(/\r?\n/).slice(1);
    let total = 0, free = 0;
    for (const line of lines) {
      const parts = line.trim().split(/\s+/);
      if (parts.length >= 3) {
        const caption = parts[0];
        const freespace = parseInt(parts[1], 10);
        const size = parseInt(parts[2], 10);
        if (!isNaN(freespace)) free += freespace;
        if (!isNaN(size)) total += size;
      }
    }
    return { total, free };
  } catch {
    return { total: 0, free: 0 };
  }
}

const before = getDiskFree();
let attempts = 0;
let success = false;
let droppedCount = 0;
while (attempts < 3 && !success) {
  attempts++;
  try {
    const out = execSync('php artisan backup:cleanup', { stdio: ['ignore', 'pipe', 'pipe'] }).toString();
    const m = out.match(/Dropped_count=(\d+)/);
    if (m) droppedCount = parseInt(m[1], 10);
    success = true;
  } catch (e) {
    if (attempts >= 3) {
      console.error('backup:cleanup failed after 3 attempts');
      process.exit(1);
    }
  }
}
const after = getDiskFree();
const deltaFree = after.free - before.free;
const percentIncrease = before.total > 0 ? (deltaFree / before.total) * 100 : 0;

const logPath = path.join(process.cwd(), 'storage', 'logs', 'cleanup_run.json');
fs.mkdirSync(path.dirname(logPath), { recursive: true });
fs.writeFileSync(logPath, JSON.stringify({
  timestamp: new Date().toISOString(),
  droppedCount,
  before,
  after,
  percentIncrease,
}, null, 2));

console.log(`Cleanup finished. Dropped tables: ${droppedCount}. Disk free increase: ${percentIncrease.toFixed(2)}%`);
if (percentIncrease < 5) {
  console.warn('Disk free increase below 5% threshold.');
}
