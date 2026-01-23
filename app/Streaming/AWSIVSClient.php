<?php

namespace App\Streaming;

class AWSIVSClient
{
    public function createStream(array $config): array
    {
        if (class_exists('\\Aws\\Ivs\\IvsClient')) {
            try {
                $client = new \Aws\Ivs\IvsClient([
                    'version' => 'latest',
                    'region' => $config['region'] ?? 'us-east-1',
                    'credentials' => [
                        'key' => $config['access_key'] ?? null,
                        'secret' => $config['secret_key'] ?? null,
                    ],
                ]);
                $channel = $client->createChannel(['name' => $config['name'] ?? 'noteds-stream']);
                return ['channel' => $channel->toArray()];
            } catch (\Throwable $e) {
                return ['error' => $e->getMessage()];
            }
        }
        return [
            'warning' => 'aws-sdk-php not installed; using static config',
        ];
    }

    public function getStreamStatus(array $config): array
    {
        if (class_exists('\\Aws\\Ivs\\IvsClient') && isset($config['channel_arn'])) {
            try {
                $client = new \Aws\Ivs\IvsClient([
                    'version' => 'latest',
                    'region' => $config['region'] ?? 'us-east-1',
                ]);
                $res = $client->getStream(['channelArn' => $config['channel_arn']]);
                return $res->toArray();
            } catch (\Throwable $e) {
                return ['error' => $e->getMessage()];
            }
        }
        return ['status' => 'unknown'];
    }

    public function stopStream(array $config): array
    {
        if (class_exists('\\Aws\\Ivs\\IvsClient') && isset($config['channel_arn'])) {
            try {
                $client = new \Aws\Ivs\IvsClient([
                    'version' => 'latest',
                    'region' => $config['region'] ?? 'us-east-1',
                ]);
                $client->stopStream(['channelArn' => $config['channel_arn']]);
                return ['success' => true];
            } catch (\Throwable $e) {
                return ['error' => $e->getMessage()];
            }
        }
        return ['status' => 'noop'];
    }
}
