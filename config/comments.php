<?php

return [
    'max_attachment_total_kb' => env('COMMENT_MAX_ATTACH_TOTAL_KB', 25600), // 25 MB total per comment
    'pdf_thumbnails' => env('COMMENT_PDF_THUMBNAILS', false),
    'allow_archives' => env('COMMENT_ALLOW_ARCHIVES', false), // allow ZIP/RAR uploads
];
