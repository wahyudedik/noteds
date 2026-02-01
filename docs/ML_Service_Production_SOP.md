# SOP Deploy Python ML Service (Ubuntu + aapanel)

## Prasyarat
- VPS Ubuntu 22.04/24.04 dengan aapanel
- Domain dan DNS terarah ke server
- Python 3.9+ terinstal

## Instalasi Python & Virtualenv
```bash
sudo apt update
sudo apt install -y python3.10 python3.10-venv python3-pip
cd /opt/python-ml-service
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

## Struktur Proyek
- Path repo: `d:\PROJECT\LARAVEL\noteds\python-ml-service`
- Entrypoint: `run.py` atau `app/api/main.py` (FastAPI/Flask)

## Konfigurasi Environment
Salin `.env.example` menjadi `.env` dan sesuaikan:
```
ML_MODEL_PATH=/opt/models/latest.pkl
REDIS_URL=redis://127.0.0.1:6379/0
PROMETHEUS_ENABLED=true
```

## Supervisor (Process Management)
File: `/etc/supervisor/conf.d/python-ml-service.conf`
```
[program:python-ml-service]
directory=/opt/python-ml-service
command=/opt/python-ml-service/venv/bin/python run.py
autostart=true
autorestart=true
stderr_logfile=/var/log/python-ml-service.err.log
stdout_logfile=/var/log/python-ml-service.out.log
user=www-data
environment=ENV=production
```
Reload:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status python-ml-service
```

## Nginx Reverse Proxy (aapanel)
Server block:
```
server {
    listen 80;
    server_name ml.example.com;
    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```
Aktifkan SSL (Let’s Encrypt) melalui aapanel, lalu redirect HTTP→HTTPS.

## Prometheus & Grafana
- Ekspos metrik:
  - Tambahkan endpoint `/metrics` di service (via `prometheus_client`)
```python
from prometheus_client import Counter, generate_latest, CONTENT_TYPE_LATEST
REQUESTS = Counter('ml_requests_total', 'Total requests')
@app.get('/metrics')
def metrics():
    return Response(generate_latest(), media_type=CONTENT_TYPE_LATEST)
```
- Prometheus job:
```
- job_name: 'python-ml-service'
  static_configs:
    - targets: ['ml.example.com']
```
- Grafana:
  - Tambahkan datasource Prometheus dan import dashboard ML

## Redis Cache (Cache-Aside)
- Gunakan Redis untuk caching prediksi yang mahal
- Pola:
  - Cek cache → jika miss, hitung prediksi → simpan → kembalikan

## Keamanan
- Batasi rate via Nginx (limit_req) jika perlu
- Validasi input API (pydantic/FastAPI validators)
- Logging terstruktur (JSON) dan rotasi log

## Troubleshooting
- Service down:
  - `sudo supervisorctl status python-ml-service`
  - Cek log di `/var/log/python-ml-service.*.log`
- SSL error:
  - Verifikasi sertifikat di aapanel, cek chain
- Metrik tidak muncul:
  - Pastikan endpoint `/metrics` dapat diakses dan terdaftar di Prometheus

## Deployment Checklist
- [ ] Virtualenv aktif dan dependencies terpasang
- [ ] Supervisor berjalan dan autostart aktif
- [ ] Nginx reverse proxy + SSL OK
- [ ] Redis terpasang dan dapat diakses
- [ ] Prometheus job terkonfigurasi, Grafana dashboard siap
