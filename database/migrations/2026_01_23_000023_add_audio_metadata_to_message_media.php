<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_media', function (Blueprint $table) {
            if (!Schema::hasColumn('message_media', 'transcript')) {
                $table->text('transcript')->nullable()->after('duration');
            }
            if (!Schema::hasColumn('message_media', 'transcript_language')) {
                $table->string('transcript_language')->nullable()->after('transcript');
            }
            if (!Schema::hasColumn('message_media', 'audio_codec')) {
                $table->string('audio_codec')->nullable()->after('transcript_language');
            }
            if (!Schema::hasColumn('message_media', 'sample_rate')) {
                $table->unsignedInteger('sample_rate')->nullable()->after('audio_codec');
            }
            if (!Schema::hasColumn('message_media', 'bitrate')) {
                $table->unsignedInteger('bitrate')->nullable()->after('sample_rate');
            }
            if (!Schema::hasColumn('message_media', 'channels')) {
                $table->unsignedTinyInteger('channels')->nullable()->after('bitrate');
            }
            if (!Schema::hasColumn('message_media', 'waveform')) {
                $table->json('waveform')->nullable()->after('channels');
            }
            if (!Schema::hasColumn('message_media', 'is_transcribed')) {
                $table->boolean('is_transcribed')->default(false)->after('waveform');
            }
            if (!Schema::hasColumn('message_media', 'transcription_confidence')) {
                $table->float('transcription_confidence')->nullable()->after('is_transcribed');
            }
            if (!Schema::hasColumn('message_media', 'amplitude_stats')) {
                $table->json('amplitude_stats')->nullable()->after('transcription_confidence');
            }
            $table->index('is_transcribed');
            $table->index('transcript_language');
        });
    }

    public function down(): void
    {
        Schema::table('message_media', function (Blueprint $table) {
            $table->dropIndex(['message_media_is_transcribed_index']);
            $table->dropIndex(['message_media_transcript_language_index']);
            $table->dropColumn([
                'transcript',
                'transcript_language',
                'audio_codec',
                'sample_rate',
                'bitrate',
                'channels',
                'waveform',
                'is_transcribed',
                'transcription_confidence',
                'amplitude_stats',
            ]);
        });
    }
};
