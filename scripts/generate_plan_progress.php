<?php
$plansDir = __DIR__ . '/../.cursor/plans';
$files = glob($plansDir . '/*.plan.md');
function countBoxes($content)
{
    $checked = preg_match_all('/- \[x\]/i', $content, $m);
    $unchecked = preg_match_all('/- \[ \]/', $content, $m2);
    return [$checked, $unchecked];
}
function evidenceFor($filename)
{
    $f = strtolower(basename($filename));
    $ev = [];
    $add = function ($arr) use (&$ev) {
        foreach ($arr as $x) {
            $ev[] = $x;
        }
    };
    if (strpos($f, 'infinite_scroll') !== false || strpos($f, 'floating_action_button') !== false) {
        $add([
            '[PostFeed.vue](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Components/PostFeed.vue)',
            '[Pages/Posts/Index.vue](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Pages/Posts/Index.vue)',
        ]);
    }
    if (strpos($f, 'realtime') !== false || strpos($f, 'pusher') !== false || strpos($f, 'notifications') !== false) {
        $add([
            '[broadcasting.php](file:///d:/PROJECT/LARAVEL/noteds/config/broadcasting.php)',
            '[channels.php](file:///d:/PROJECT/LARAVEL/noteds/routes/channels.php)',
            '[bootstrap.js](file:///d:/PROJECT/LARAVEL/noteds/resources/js/bootstrap.js)',
            '[Utils/echo.js](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Utils/echo.js)',
        ]);
    }
    if (strpos($f, 'repost') !== false) {
        $add([
            '[RepostController.php](file:///d:/PROJECT/LARAVEL/noteds/app/Http/Controllers/RepostController.php)',
            '[Repost.php](file:///d:/PROJECT/LARAVEL/noteds/app/Models/Repost.php)',
            '[RepostButton.vue](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Components/Repost/RepostButton.vue)',
        ]);
    }
    if (strpos($f, 'follow') !== false) {
        $add([
            '[FollowController.php](file:///d:/PROJECT/LARAVEL/noteds/app/Http/Controllers/FollowController.php)',
            '[Follow.php](file:///d:/PROJECT/LARAVEL/noteds/app/Models/Follow.php)',
        ]);
    }
    if (strpos($f, 'comment') !== false) {
        $add([
            '[CommentController.php](file:///d:/PROJECT/LARAVEL/noteds/app/Http/Controllers/CommentController.php)',
            '[Comment.php](file:///d:/PROJECT/LARAVEL/noteds/app/Models/Comment.php)',
            '[CommentThread.vue](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Components/CommentThread.vue)',
        ]);
    }
    if (strpos($f, 'bookmark') !== false) {
        $add([
            '[BookmarkController.php](file:///d:/PROJECT/LARAVEL/noteds/app/Http/Controllers/BookmarkController.php)',
            '[Bookmark.php](file:///d:/PROJECT/LARAVEL/noteds/app/Models/Bookmark.php)',
            '[TagList.vue](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Components/Bookmarks/TagList.vue)',
        ]);
    }
    if (strpos($f, 'voting') !== false || strpos($f, 'vote') !== false) {
        $add([
            '[VoteController.php](file:///d:/PROJECT/LARAVEL/noteds/app/Http/Controllers/VoteController.php)',
            '[PostVote.php](file:///d:/PROJECT/LARAVEL/noteds/app/Models/PostVote.php)',
            '[CommentVote.php](file:///d:/PROJECT/LARAVEL/noteds/app/Models/CommentVote.php)',
            '[VoteButton.vue](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Components/VoteButton.vue)',
        ]);
    }
    if (strpos($f, 'groups') !== false || strpos($f, 'communities') !== false) {
        $add([
            '[ConversationController.php](file:///d:/PROJECT/LARAVEL/noteds/app/Http/Controllers/Messaging/ConversationController.php)',
            '[Conversation.php](file:///d:/PROJECT/LARAVEL/noteds/app/Models/Conversation.php)',
            '[channels.php](file:///d:/PROJECT/LARAVEL/noteds/routes/channels.php#L47-L78)',
        ]);
    }
    if (strpos($f, 'messaging') !== false || strpos($f, 'direct_messaging') !== false || strpos($f, 'chat') !== false) {
        $add([
            '[MessageController.php](file:///d:/PROJECT/LARAVEL/noteds/app/Http/Controllers/Messaging/MessageController.php)',
            '[Message.php](file:///d:/PROJECT/LARAVEL/noteds/app/Models/Message.php)',
            '[Components/Messaging](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Components/Messaging)',
        ]);
    }
    if (strpos($f, 'throttling') !== false || strpos($f, 'rate') !== false) {
        $add([
            '[RateLimiter.md](file:///d:/PROJECT/LARAVEL/noteds/docs/RateLimiter.md)',
        ]);
    }
    if (strpos($f, 'faq') !== false || strpos($f, 'documentation') !== false || strpos($f, 'docs') !== false) {
        $add([
            '[Admin/FaqController.php](file:///d:/PROJECT/LARAVEL/noteds/app/Http/Controllers/Admin/FaqController.php)',
            '[Admin/DocumentationController.php](file:///d:/PROJECT/LARAVEL/noteds/app/Http/Controllers/Admin/DocumentationController.php)',
            '[Pages/Admin/Faqs](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Pages/Admin/Faqs)',
            '[Pages/Admin/Documentations](file:///d:/PROJECT/LARAVEL/noteds/resources/js/Pages/Admin/Documentations)',
        ]);
    }
    return $ev;
}
$md = "# Ringkasan Progres Plans\n\n| Plan | Selesai | Belum | Progres |\n|---|---:|---:|---:|\n";
foreach ($files as $file) {
    $content = file_get_contents($file);
    [$c, $u] = countBoxes($content);
    $progress = ($c + $u) > 0 ? round(($c / ($c + $u)) * 100) . '%' : 'N/A';
    $md .= '| ' . basename($file) . ' | ' . $c . ' | ' . $u . ' | ' . $progress . " |\n";
    $ev = evidenceFor($file);
    if (!empty($ev)) {
        $md .= "\nEvidence:\n";
        foreach ($ev as $link) {
            $md .= '- ' . $link . "\n";
        }
        $md .= "\n";
    }
}
file_put_contents($plansDir . '/PROGRESS_SUMMARY.md', $md);
