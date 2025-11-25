<?php

namespace App\Services;

use App\Models\VirusScan;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ClamAVService
{
    protected string $clamdSocket;
    protected string $clamdHost;
    protected int $clamdPort;
    protected int $timeout;
    protected string $quarantinePath;

    public function __construct()
    {
        $this->clamdSocket = config('clamav.socket_path', '/var/run/clamav/clamd.ctl');
        $this->clamdHost = config('clamav.host', '127.0.0.1');
        $this->clamdPort = config('clamav.port', 3310);
        $this->timeout = config('clamav.timeout', 30);
        $this->quarantinePath = storage_path('app/quarantine');
        
        // Ensure quarantine directory exists
        if (!file_exists($this->quarantinePath)) {
            mkdir($this->quarantinePath, 0755, true);
        }
    }

    /**
     * Scan a file for viruses
     *
     * @param string $filePath Full path to the file
     * @param string|null $fileName Original file name
     * @param User|null $user User who triggered the scan
     * @param string $scanType Type of scan (realtime, scheduled, manual)
     * @param string|null $noteId Related note ID if applicable
     * @return VirusScan
     */
    public function scanFile(
        string $filePath,
        ?string $fileName = null,
        ?User $user = null,
        string $scanType = 'realtime',
        ?string $noteId = null
    ): VirusScan {
        $startTime = microtime(true);
        $fileName = $fileName ?? basename($filePath);
        
        // Create scan record
        $scan = VirusScan::create([
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => mime_content_type($filePath) ?? 'unknown',
            'file_size' => file_exists($filePath) ? filesize($filePath) : null,
            'scan_status' => 'scanning',
            'scanned_by_user_id' => $user?->id,
            'note_id' => $noteId,
            'scan_type' => $scanType,
        ]);

        try {
            // Check if file exists
            if (!file_exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }

            // Perform scan
            $result = $this->performScan($filePath);
            $scanDuration = (microtime(true) - $startTime) * 1000;

            // Parse result
            if ($result['infected']) {
                $scan->update([
                    'scan_status' => 'infected',
                    'scan_result' => $result['output'],
                    'threat_name' => $result['threat_name'] ?? 'Unknown threat',
                    'threat_details' => $result['threat_details'] ?? null,
                    'scan_duration_ms' => (int) $scanDuration,
                ]);

                // Auto-quarantine if enabled
                if (config('clamav.auto_quarantine', true)) {
                    $this->quarantineFile($scan);
                }

                Log::warning('Virus detected', [
                    'scan_id' => $scan->id,
                    'file_path' => $filePath,
                    'threat' => $result['threat_name'],
                ]);
            } else {
                $scan->update([
                    'scan_status' => 'clean',
                    'scan_result' => $result['output'],
                    'scan_duration_ms' => (int) $scanDuration,
                ]);
            }
        } catch (\Exception $e) {
            $scanDuration = (microtime(true) - $startTime) * 1000;
            $scan->update([
                'scan_status' => 'error',
                'error_message' => $e->getMessage(),
                'scan_duration_ms' => (int) $scanDuration,
            ]);

            Log::error('Virus scan error', [
                'scan_id' => $scan->id,
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }

        return $scan->fresh();
    }

    /**
     * Perform actual virus scan using ClamAV
     *
     * @param string $filePath
     * @return array
     */
    protected function performScan(string $filePath): array
    {
        $connectionType = config('clamav.connection_type', 'socket'); // socket or tcp

        if ($connectionType === 'socket') {
            return $this->scanViaSocket($filePath);
        } else {
            return $this->scanViaTcp($filePath);
        }
    }

    /**
     * Scan via ClamAV socket
     *
     * @param string $filePath
     * @return array
     */
    protected function scanViaSocket(string $filePath): array
    {
        // Use clamdscan command-line tool
        $command = [
            'clamdscan',
            '--no-summary',
            '--fdpass',
            $filePath,
        ];

        $process = new Process($command);
        $process->setTimeout($this->timeout);
        
        try {
            $process->mustRun();
            $output = $process->getOutput();
            $exitCode = $process->getExitCode();

            // ClamAV returns 0 for clean, 1 for infected
            $infected = $exitCode === 1;
            $threatName = null;
            $threatDetails = null;

            if ($infected) {
                // Parse threat name from output
                // Format: "file_path: ThreatName FOUND"
                if (preg_match('/: (.+?) FOUND/', $output, $matches)) {
                    $threatName = trim($matches[1]);
                }
                $threatDetails = $output;
            }

            return [
                'infected' => $infected,
                'output' => $output,
                'threat_name' => $threatName,
                'threat_details' => $threatDetails,
            ];
        } catch (ProcessFailedException $e) {
            // If clamdscan is not available, try clamscan as fallback
            return $this->scanViaClamscan($filePath);
        }
    }

    /**
     * Scan via ClamAV TCP connection
     *
     * @param string $filePath
     * @return array
     */
    protected function scanViaTcp(string $filePath): array
    {
        // Use clamdscan with TCP connection
        $command = [
            'clamdscan',
            '--no-summary',
            '--host=' . $this->clamdHost,
            '--port=' . $this->clamdPort,
            $filePath,
        ];

        $process = new Process($command);
        $process->setTimeout($this->timeout);
        
        try {
            $process->mustRun();
            $output = $process->getOutput();
            $exitCode = $process->getExitCode();

            $infected = $exitCode === 1;
            $threatName = null;
            $threatDetails = null;

            if ($infected) {
                if (preg_match('/: (.+?) FOUND/', $output, $matches)) {
                    $threatName = trim($matches[1]);
                }
                $threatDetails = $output;
            }

            return [
                'infected' => $infected,
                'output' => $output,
                'threat_name' => $threatName,
                'threat_details' => $threatDetails,
            ];
        } catch (ProcessFailedException $e) {
            // Fallback to clamscan
            return $this->scanViaClamscan($filePath);
        }
    }

    /**
     * Fallback: Scan via clamscan (standalone, slower)
     *
     * @param string $filePath
     * @return array
     */
    protected function scanViaClamscan(string $filePath): array
    {
        $command = [
            'clamscan',
            '--no-summary',
            '--infected',
            $filePath,
        ];

        $process = new Process($command);
        $process->setTimeout($this->timeout * 2); // clamscan is slower, allow more time
        
        try {
            $process->mustRun();
            $output = $process->getOutput();
            $exitCode = $process->getExitCode();

            $infected = $exitCode === 1;
            $threatName = null;
            $threatDetails = null;

            if ($infected) {
                if (preg_match('/: (.+?) FOUND/', $output, $matches)) {
                    $threatName = trim($matches[1]);
                }
                $threatDetails = $output;
            }

            return [
                'infected' => $infected,
                'output' => $output,
                'threat_name' => $threatName,
                'threat_details' => $threatDetails,
            ];
        } catch (ProcessFailedException $e) {
            throw new \Exception("ClamAV scan failed: " . $e->getMessage());
        }
    }

    /**
     * Quarantine an infected file
     *
     * @param VirusScan $scan
     * @return bool
     */
    public function quarantineFile(VirusScan $scan): bool
    {
        if (!$scan->isInfected() || $scan->isQuarantined()) {
            return false;
        }

        try {
            $filePath = $scan->file_path;
            
            if (!file_exists($filePath)) {
                throw new \Exception("File not found for quarantine: {$filePath}");
            }

            // Generate quarantine path
            $quarantineFileName = Str::uuid() . '_' . $scan->file_name;
            $quarantinePath = $this->quarantinePath . '/' . $quarantineFileName;

            // Move file to quarantine
            if (!rename($filePath, $quarantinePath)) {
                throw new \Exception("Failed to move file to quarantine");
            }

            // Update scan record
            $scan->update([
                'quarantine_path' => $quarantinePath,
                'is_quarantined' => true,
                'quarantined_at' => now(),
                'scan_status' => 'quarantined',
            ]);

            Log::warning('File quarantined', [
                'scan_id' => $scan->id,
                'original_path' => $filePath,
                'quarantine_path' => $quarantinePath,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Quarantine failed', [
                'scan_id' => $scan->id,
                'error' => $e->getMessage(),
            ]);

            $scan->update([
                'error_message' => 'Quarantine failed: ' . $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Restore a quarantined file
     *
     * @param VirusScan $scan
     * @param string $restorePath
     * @return bool
     */
    public function restoreFile(VirusScan $scan, string $restorePath): bool
    {
        if (!$scan->isQuarantined()) {
            return false;
        }

        try {
            $quarantinePath = $scan->quarantine_path;
            
            if (!file_exists($quarantinePath)) {
                throw new \Exception("Quarantined file not found: {$quarantinePath}");
            }

            // Ensure directory exists
            $restoreDir = dirname($restorePath);
            if (!file_exists($restoreDir)) {
                mkdir($restoreDir, 0755, true);
            }

            // Move file back
            if (!rename($quarantinePath, $restorePath)) {
                throw new \Exception("Failed to restore file from quarantine");
            }

            // Update scan record
            $scan->update([
                'file_path' => $restorePath,
                'quarantine_path' => null,
                'is_quarantined' => false,
                'quarantined_at' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Restore failed', [
                'scan_id' => $scan->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete a quarantined file permanently
     *
     * @param VirusScan $scan
     * @return bool
     */
    public function deleteQuarantinedFile(VirusScan $scan): bool
    {
        if (!$scan->isQuarantined()) {
            return false;
        }

        try {
            $quarantinePath = $scan->quarantine_path;
            
            if (file_exists($quarantinePath)) {
                unlink($quarantinePath);
            }

            $scan->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('Delete quarantined file failed', [
                'scan_id' => $scan->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if ClamAV is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            $process = new Process(['clamdscan', '--version']);
            $process->setTimeout(5);
            $process->mustRun();
            return true;
        } catch (\Exception $e) {
            try {
                $process = new Process(['clamscan', '--version']);
                $process->setTimeout(5);
                $process->mustRun();
                return true;
            } catch (\Exception $e2) {
                return false;
            }
        }
    }

    /**
     * Get scan statistics
     *
     * @param array $filters
     * @return array
     */
    public function getStatistics(array $filters = []): array
    {
        $query = VirusScan::query();

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $total = $query->count();
        $clean = (clone $query)->where('scan_status', 'clean')->count();
        $infected = (clone $query)->where('scan_status', 'infected')->count();
        $quarantined = (clone $query)->where('is_quarantined', true)->count();
        $errors = (clone $query)->where('scan_status', 'error')->count();
        $pending = (clone $query)->where('scan_status', 'pending')->count();

        $avgScanTime = (clone $query)
            ->whereNotNull('scan_duration_ms')
            ->where('scan_status', '!=', 'pending')
            ->avg('scan_duration_ms');

        return [
            'total' => $total,
            'clean' => $clean,
            'infected' => $infected,
            'quarantined' => $quarantined,
            'errors' => $errors,
            'pending' => $pending,
            'avg_scan_time_ms' => $avgScanTime ? round($avgScanTime, 2) : null,
            'infection_rate' => $total > 0 ? round(($infected / $total) * 100, 2) : 0,
        ];
    }
}

