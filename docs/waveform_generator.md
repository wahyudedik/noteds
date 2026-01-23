# Waveform Generator

## Overview
- Server-side waveform generation for audio messages without client-provided waveform
- Asynchronous via queue job: WaveformGeneratorJob
- Engine: ffmpeg (if configured) or default fallback generator

## Configuration
- Config file: config/waveform.php
- env:
  - WAVEFORM_ENGINE=ffmpeg|default
  - FFMPEG_PATH, FFPROBE_PATH

## Job Behavior
- Loads MessageMedia, checks audio and waveform absence
- If ffprobe available: extracts basic duration metadata, generates default waveform
- Fallback: creates synthetic waveform and amplitude_stats

## Caching
- Waveform stored in message_media.waveform for reuse

## Monitoring
- Add queue monitoring; alert on repeated failures
