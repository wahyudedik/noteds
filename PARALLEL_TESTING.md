# Parallel Testing Guide

Run tests in parallel:
```bash
composer test:parallel
```
Flags:
- `--processes=N` to set worker count
- `--recreate-databases` ensures clean state

Recommendations:
- Use `RefreshDatabase` and SQLite for speed
- Avoid shared global state; prefer per-test setup
- Use `lockForUpdate` for critical increments

Reporting:
- Use the output timings and CI summary to monitor performance
