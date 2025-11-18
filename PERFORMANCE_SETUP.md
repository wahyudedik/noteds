# Performance Optimization Setup Guide

## 1. Redis Cache Configuration

### Installation
```bash
# Ubuntu/Debian
sudo apt-get install redis-server

# macOS (Homebrew)
brew install redis

# Windows (WSL or use Redis for Windows)
```

### Configuration
Add to your `.env` file: 
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis
REDIS_CACHE_DB=1

# Set cache driver to redis
CACHE_STORE=redis
```

### Verify Redis Connection
```bash
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
```

## 2. CDN Configuration

### Setup CDN Provider
Choose a CDN provider (Cloudflare, AWS CloudFront, etc.) and configure:

1. **Cloudflare** (Recommended for free tier):
   - Sign up at cloudflare.com
   - Add your domain
   - Update DNS nameservers
   - Enable CDN

2. **AWS CloudFront**:
   - Create CloudFront distribution
   - Point to your S3 bucket or origin server
   - Get distribution URL

### Configuration
Add to your `.env` file:
```env
# CDN URL (without trailing slash)
CDN_URL=https://cdn.yourdomain.com

# If not set, will fallback to APP_URL
```

### Usage in Code
```php
// In Blade templates
<img src="{{ asset_cdn('images/logo.png') }}" alt="Logo">

// In PHP
$url = cdn_url('storage/images/photo.jpg');
```

## 3. Image Processing Setup

### Installation
```bash
composer require intervention/image
```

### PHP Extension Requirements
```bash
# Ubuntu/Debian
sudo apt-get install php-gd

# macOS
brew install php-gd

# Or use Imagick (better quality)
sudo apt-get install php-imagick
```

### Configuration
The `ImageProcessingService` is already created. Use it in your controllers:

```php
use App\Services\ImageProcessingService;

// In controller
public function store(Request $request, ImageProcessingService $imageService)
{
    if ($request->hasFile('thumbnail')) {
        $paths = $imageService->processNoteThumbnail(
            $request->file('thumbnail'),
            $note->id
        );
        
        // $paths contains: ['original', 'thumbnail', 'medium', 'large']
        $note->thumbnail = $paths['medium']; // Use medium as default
    }
}
```

### Usage in Views
```blade
{{-- Get optimized image URL --}}
<img src="{{ Storage::url($note->thumbnail) }}" 
     srcset="{{ Storage::url(str_replace('_medium', '_thumbnail', $note->thumbnail)) }} 300w,
             {{ Storage::url($note->thumbnail) }} 600w,
             {{ Storage::url(str_replace('_medium', '_large', $note->thumbnail)) }} 1200w"
     sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
     loading="lazy"
     alt="{{ $note->title }}">
```

## 4. Laravel Telescope Query Monitoring

### Installation
Telescope is already installed. Ensure it's configured:

### Configuration
Check `config/telescope.php`:
- `enabled` should be `true` (or set via `TELESCOPE_ENABLED=true` in `.env`)
- `QueryWatcher` is enabled by default
- `slow` query threshold is set to 100ms

### Access Telescope
1. Visit `/telescope` in your browser
2. Navigate to "Queries" tab to see all database queries
3. Check "Slow Queries" for queries taking > 100ms
4. Use "Requests" tab to see full request/response cycle

### Environment Variables
```env
TELESCOPE_ENABLED=true
TELESCOPE_QUERY_WATCHER=true
```

### Production Considerations
```env
# Disable Telescope in production for security
TELESCOPE_ENABLED=false

# Or restrict access via middleware
# Only allow specific IPs or users
```

## Performance Monitoring

### Check Cache Status
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Monitor Redis
```bash
redis-cli
> INFO stats
> KEYS *
> GET "laravel_cache:marketplace_featured_notes_grid"
```

### Database Query Optimization
1. Use Telescope to identify slow queries
2. Check for N+1 query problems
3. Add indexes for frequently queried columns (already done)
4. Use eager loading (`with()`) instead of lazy loading

### Image Optimization Tips
1. Use WebP format for better compression
2. Implement lazy loading (already done)
3. Use responsive images with srcset
4. Compress images before upload
5. Consider using image CDN (Cloudinary, Imgix)

## Testing Performance

### Before Optimization
```bash
# Check page load time
curl -w "@curl-format.txt" -o /dev/null -s https://yourdomain.com/marketplace
```

### After Optimization
- Check browser DevTools Network tab
- Use Lighthouse for performance score
- Monitor server response times
- Check Redis cache hit rates

## Troubleshooting

### Redis Connection Failed
```bash
# Check if Redis is running
redis-cli ping
# Should return: PONG

# Check Redis logs
tail -f /var/log/redis/redis-server.log
```

### CDN Not Working
1. Verify CDN_URL in `.env`
2. Check DNS propagation
3. Clear browser cache
4. Verify CORS settings if needed

### Image Processing Errors
1. Check PHP GD or Imagick extension is installed
2. Verify file permissions on storage directory
3. Check available disk space
4. Review error logs: `storage/logs/laravel.log`

