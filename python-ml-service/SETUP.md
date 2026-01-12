# Setup Guide - Python ML Service

Panduan lengkap untuk setup dan menjalankan Python ML Service untuk prediksi harga saham Indonesia.

## Daftar Isi

1. [Persyaratan Sistem](#persyaratan-sistem)
2. [Instalasi](#instalasi)
3. [Konfigurasi](#konfigurasi)
4. [Menjalankan Service](#menjalankan-service)
5. [Testing](#testing)
6. [Troubleshooting](#troubleshooting)

## Persyaratan Sistem

### Software yang Diperlukan

- **Python 3.9 atau lebih tinggi** (disarankan Python 3.10+)
- **pip** (Python package manager)
- **Git** (untuk clone repository)
- **Laravel API** harus sudah berjalan (untuk data fetching)

### Hardware yang Disarankan

- **RAM**: Minimal 4GB (disarankan 8GB+ untuk training model)
- **Storage**: Minimal 10GB free space (untuk model files dan data)
- **CPU**: Multi-core processor (training akan lebih cepat dengan lebih banyak core)
- **GPU** (Opsional): NVIDIA GPU dengan CUDA support untuk training lebih cepat

## Instalasi

### 1. Clone atau Navigate ke Directory

Jika service ini sudah ada di project, pastikan Anda berada di directory `python-ml-service`:

```bash
cd python-ml-service
```

### 2. Buat Virtual Environment

Disarankan menggunakan virtual environment untuk isolasi dependencies:

**Windows:**
```bash
python -m venv venv
venv\Scripts\activate
```

**Linux/Mac:**
```bash
python3 -m venv venv
source venv/bin/activate
```

Setelah aktivasi, prompt terminal akan menampilkan `(venv)` di depan.

### 3. Install Dependencies

Install semua package yang diperlukan:

```bash
pip install --upgrade pip
pip install -r requirements.txt
```

**Catatan**: Instalasi TensorFlow mungkin memakan waktu beberapa menit. Jika Anda memiliki GPU NVIDIA, install TensorFlow dengan GPU support:

```bash
pip install tensorflow[and-cuda]
```

### 4. Buat File Konfigurasi

Copy file `.env.example` ke `.env`:

**Windows:**
```bash
copy .env.example .env
```

**Linux/Mac:**
```bash
cp .env.example .env
```

## Konfigurasi

Edit file `.env` dengan konfigurasi yang sesuai:

### Konfigurasi Laravel API

```env
# URL base Laravel API
LARAVEL_API_BASE_URL=http://localhost:8000

# API Key untuk autentikasi (jika diperlukan)
LARAVEL_API_KEY=your_laravel_api_key_here

# Timeout untuk request ke Laravel API (dalam detik)
LARAVEL_API_TIMEOUT=30
```

### Konfigurasi ML Service

```env
# Port untuk ML Service
ML_SERVICE_PORT=8001

# Host untuk ML Service
ML_SERVICE_HOST=0.0.0.0

# API Key untuk mengamankan ML Service (opsional, kosongkan jika tidak diperlukan)
ML_SERVICE_API_KEY=your_ml_service_api_key_here

# Level logging (DEBUG, INFO, WARNING, ERROR)
LOG_LEVEL=INFO
```

### Konfigurasi Storage

```env
# Path untuk menyimpan trained models
MODEL_STORAGE_PATH=./models

# Path untuk data training (opsional)
TRAINING_DATA_PATH=./data

# TTL untuk prediction cache (dalam detik)
PREDICTION_CACHE_TTL=3600
```

### Konfigurasi Database (Opsional)

Jika ML Service perlu akses langsung ke database (bukan melalui Laravel API):

```env
# DB_HOST=localhost
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=your_password
```

## Menjalankan Service

### Development Mode

Gunakan script `run.py`:

```bash
python run.py
```

Atau langsung dengan uvicorn:

```bash
uvicorn app.api.main:app --host 0.0.0.0 --port 8001 --reload
```

Flag `--reload` akan auto-reload saat ada perubahan kode (development only).

### Production Mode

Untuk production, gunakan multiple workers:

```bash
uvicorn app.api.main:app --host 0.0.0.0 --port 8001 --workers 4
```

### Menggunakan Docker

#### Build Image

```bash
docker build -t stock-ml-service .
```

#### Run Container

```bash
docker run -d \
  -p 8001:8001 \
  -v $(pwd)/models:/app/models \
  -v $(pwd)/data:/app/data \
  --env-file .env \
  --name ml-service \
  stock-ml-service
```

#### Menggunakan Docker Compose

```bash
docker-compose up -d
```

Untuk melihat logs:

```bash
docker-compose logs -f
```

## Testing

### 1. Health Check

Test apakah service berjalan dengan baik:

```bash
curl http://localhost:8001/health
```

Response yang diharapkan:
```json
{
  "status": "healthy",
  "timestamp": "2024-01-01T12:00:00"
}
```

### 2. Test Root Endpoint

```bash
curl http://localhost:8001/
```

### 3. Test Training (dengan API Key jika dikonfigurasi)

```bash
curl -X POST "http://localhost:8001/api/ml/train" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your_ml_service_api_key" \
  -d '{
    "stock_code": "BBRI",
    "model_type": "lstm",
    "prediction_horizon": 1,
    "sequence_length": 60,
    "epochs": 50,
    "batch_size": 32
  }'
```

Response:
```json
{
  "model_id": "uuid-here",
  "status": "training",
  "message": "Training started. Check status using /api/ml/status/{model_id}"
}
```

### 4. Check Training Status

```bash
curl http://localhost:8001/api/ml/status/{model_id}
```

### 5. Test Prediction

```bash
curl -X POST "http://localhost:8001/api/ml/predict" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your_ml_service_api_key" \
  -d '{
    "stock_code": "BBRI",
    "model_type": "lstm",
    "model_path": "./models/BBRI_lstm_1_model_id.h5",
    "sequence_length": 60,
    "n_features": 20,
    "prediction_horizon": 1
  }'
```

## Verifikasi Integrasi dengan Laravel

Pastikan Laravel API dapat berkomunikasi dengan ML Service:

1. **Pastikan Laravel API berjalan** di port 8000 (atau sesuai konfigurasi)
2. **Pastikan ML Service berjalan** di port 8001
3. **Test endpoint Laravel** yang memanggil ML Service

Contoh test dari Laravel (dalam `MLIntegrationService`):

```php
$response = Http::withHeaders([
    'X-API-Key' => config('services.ml_service.api_key')
])->post('http://localhost:8001/api/ml/train', [
    'stock_code' => 'BBRI',
    'model_type' => 'lstm',
    'prediction_horizon' => 1
]);
```

## Troubleshooting

### Error: Module not found

**Masalah**: `ModuleNotFoundError: No module named 'app'`

**Solusi**: Pastikan Anda menjalankan dari root directory `python-ml-service/`:

```bash
cd python-ml-service
python run.py
```

### Error: Port already in use

**Masalah**: `Address already in use` atau port 8001 sudah digunakan

**Solusi**: 
- Ganti port di `.env`: `ML_SERVICE_PORT=8002`
- Atau hentikan service yang menggunakan port 8001:
  ```bash
  # Windows
  netstat -ano | findstr :8001
  taskkill /PID <PID> /F
  
  # Linux/Mac
  lsof -ti:8001 | xargs kill
  ```

### Error: Cannot connect to Laravel API

**Masalah**: `ConnectionError` atau `Timeout` saat fetch data dari Laravel

**Solusi**:
1. Pastikan Laravel API berjalan
2. Check `LARAVEL_API_BASE_URL` di `.env`
3. Test Laravel API secara langsung:
   ```bash
   curl http://localhost:8000/api/stocks/BBRI/prices
   ```
4. Pastikan CORS sudah dikonfigurasi di Laravel jika diperlukan

### Error: CUDA/GPU not found

**Masalah**: Warning tentang CUDA saat training

**Solusi**: 
- Ini normal jika tidak ada GPU. Training akan menggunakan CPU (lebih lambat)
- Untuk GPU support, install CUDA toolkit dan cuDNN, lalu install TensorFlow dengan GPU:
  ```bash
  pip install tensorflow[and-cuda]
  ```

### Error: Out of memory saat training

**Masalah**: `ResourceExhaustedError` atau sistem hang saat training

**Solusi**:
1. Kurangi `batch_size` di request training (misalnya dari 32 ke 16 atau 8)
2. Kurangi `sequence_length` (misalnya dari 60 ke 30)
3. Kurangi jumlah data training (years_of_data)
4. Tutup aplikasi lain yang menggunakan banyak RAM
5. Gunakan model yang lebih kecil (misalnya LSTM dengan units lebih sedikit)

### Error: Model file not found

**Masalah**: `FileNotFoundError` saat load model

**Solusi**:
1. Pastikan model sudah selesai training (check status)
2. Pastikan `MODEL_STORAGE_PATH` di `.env` benar
3. Check apakah file model ada di directory `models/`
4. Pastikan path yang diberikan di request prediction benar

### Training terlalu lama

**Solusi**:
1. Kurangi `epochs` (misalnya dari 100 ke 50)
2. Gunakan `EarlyStopping` callback (sudah included)
3. Kurangi jumlah data (years_of_data)
4. Gunakan GPU jika tersedia
5. Kurangi `sequence_length` dan `batch_size`

### Prediction tidak akurat

**Solusi**:
1. Pastikan model sudah training dengan cukup data (minimal 1-2 tahun)
2. Coba train dengan hyperparameters berbeda
3. Coba model type lain (transformer atau cnn_lstm)
4. Pastikan data dari Laravel API lengkap dan akurat
5. Check metrics model setelah training (MAE, RMSE, MAPE)

## Environment Variables Reference

Berikut adalah semua environment variables yang dapat dikonfigurasi:

| Variable | Default | Deskripsi |
|----------|---------|-----------|
| `LARAVEL_API_BASE_URL` | `http://localhost:8000` | URL base Laravel API |
| `LARAVEL_API_KEY` | (kosong) | API key untuk Laravel |
| `LARAVEL_API_TIMEOUT` | `30` | Timeout request (detik) |
| `ML_SERVICE_PORT` | `8001` | Port ML Service |
| `ML_SERVICE_HOST` | `0.0.0.0` | Host ML Service |
| `ML_SERVICE_API_KEY` | (kosong) | API key untuk ML Service |
| `LOG_LEVEL` | `INFO` | Level logging |
| `MODEL_STORAGE_PATH` | `./models` | Path untuk model files |
| `TRAINING_DATA_PATH` | `./data` | Path untuk training data |
| `PREDICTION_CACHE_TTL` | `3600` | Cache TTL (detik) |

## Next Steps

Setelah setup selesai:

1. **Integrate dengan Laravel**: Update `MLIntegrationService` di Laravel untuk menggunakan ML Service
2. **Train initial models**: Train model untuk beberapa stock utama
3. **Setup scheduled training**: Konfigurasi cron job atau Laravel scheduler untuk auto-retrain
4. **Monitor performance**: Setup monitoring untuk model accuracy dan service health
5. **Scale jika diperlukan**: Gunakan load balancer dan multiple workers untuk production

## Support

Jika mengalami masalah yang tidak tercakup di troubleshooting:

1. Check logs di console output
2. Check file logs jika ada (tergantung konfigurasi logging)
3. Pastikan semua dependencies terinstall dengan benar
4. Pastikan Python version sesuai (3.9+)
5. Check dokumentasi di `README.md`

## Catatan Penting

- **Development**: Gunakan `--reload` flag untuk auto-reload
- **Production**: Jangan gunakan `--reload`, gunakan multiple workers
- **Security**: Set `ML_SERVICE_API_KEY` untuk production
- **Performance**: Gunakan GPU untuk training lebih cepat
- **Storage**: Pastikan cukup space untuk model files (bisa mencapai ratusan MB per model)

