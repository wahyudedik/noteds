<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DisableProtectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'protection_disable_right_click' => 0,
            'protection_disable_keyboard_shortcuts' => 0,
            'protection_disable_copy_paste' => 0,
            'protection_disable_drag_drop' => 0,
            'protection_disable_print' => 0,
            'protection_disable_view_source' => 0,
            'protection_detect_devtools' => 0,
            'protection_disable_screenshot' => 0,
            'protection_disable_image_saving' => 0,
            'protection_disable_console' => 0,
            'protection_monitor_clipboard' => 0,
            'protection_disable_print_screen' => 0,
            'protection_disable_snipping_tool' => 0,
            'protection_detect_window_blur' => 0,
            'protection_detect_visibility_change' => 0,
            'protection_clear_clipboard_periodic' => 0,
            'protection_blur_overlay' => 0,
            'protection_disable_f12' => 0,
            'protection_disable_devtools_shortcuts' => 0,
            'protection_detect_ai_bots' => 0,
            'protection_detect_headless' => 0,
            'protection_detect_mouse_movement' => 0,
            'protection_detect_click_pattern' => 0,
            'protection_detect_screen_recording' => 0,
        ];

        foreach ($settings as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'content_protection']
            );
        }

        $this->command->info('All protection settings have been disabled!');
    }
}
