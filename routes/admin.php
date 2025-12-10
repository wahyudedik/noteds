<?php

// Admin Routes - Khusus untuk role Admin saja
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('permission:view-admin-dashboard');

    // ==================== USER MANAGEMENT ====================
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])
            ->name('index')
            ->middleware('permission:manage-users');
        
        Route::get('/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'show'])
            ->name('show')
            ->middleware('permission:manage-users');
        
        Route::post('/{user}/verify', [\App\Http\Controllers\Admin\AdminUserController::class, 'verify'])
            ->name('verify')
            ->middleware('permission:manage-users');
        
        Route::post('/{user}/reject-verification', [\App\Http\Controllers\Admin\AdminUserController::class, 'rejectVerification'])
            ->name('reject-verification')
            ->middleware('permission:manage-users');
        
        Route::post('/{user}/ban', [\App\Http\Controllers\Admin\AdminUserController::class, 'ban'])
            ->name('ban')
            ->middleware('permission:manage-users');
        
        Route::post('/{user}/unban', [\App\Http\Controllers\Admin\AdminUserController::class, 'unban'])
            ->name('unban')
            ->middleware('permission:manage-users');
        
        Route::post('/{user}/verify-kyc', [\App\Http\Controllers\Admin\AdminUserController::class, 'verifyKyc'])
            ->name('verify-kyc')
            ->middleware('permission:manage-users');
        
        Route::post('/{user}/promote-to-seller', [\App\Http\Controllers\Admin\AdminUserController::class, 'promoteToSeller'])
            ->name('promote-to-seller')
            ->middleware('permission:manage-users');
        
        Route::post('/{user}/demote-to-buyer', [\App\Http\Controllers\Admin\AdminUserController::class, 'demoteTobuyer'])
            ->name('demote-to-buyer')
            ->middleware('permission:manage-users');
        
        Route::delete('/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:delete-users');
    });

    // ==================== NOTE MANAGEMENT ====================
    Route::prefix('data-management/notes')->name('notes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminNoteController::class, 'index'])
            ->name('index')
            ->middleware('permission:manage-notes');
        
        Route::get('/{note}', [\App\Http\Controllers\Admin\AdminNoteController::class, 'show'])
            ->name('show')
            ->middleware('permission:manage-notes');
        
        Route::post('/{note}/approve', [\App\Http\Controllers\Admin\AdminNoteController::class, 'approve'])
            ->name('approve')
            ->middleware('permission:manage-notes');
        
        Route::post('/{note}/reject', [\App\Http\Controllers\Admin\AdminNoteController::class, 'reject'])
            ->name('reject')
            ->middleware('permission:manage-notes');
        
        Route::post('/{note}/block', [\App\Http\Controllers\Admin\AdminNoteController::class, 'block'])
            ->name('block')
            ->middleware('permission:manage-notes');
        
        Route::post('/{note}/unblock', [\App\Http\Controllers\Admin\AdminNoteController::class, 'unblock'])
            ->name('unblock')
            ->middleware('permission:manage-notes');
        
        Route::post('/{note}/feature', [\App\Http\Controllers\Admin\AdminNoteController::class, 'feature'])
            ->name('feature')
            ->middleware('permission:manage-notes');
        
        Route::post('/{note}/unfeature', [\App\Http\Controllers\Admin\AdminNoteController::class, 'unfeature'])
            ->name('unfeature')
            ->middleware('permission:manage-notes');
        
        Route::delete('/{note}', [\App\Http\Controllers\Admin\AdminNoteController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:delete-notes');
    });

    // ==================== TRANSACTION MANAGEMENT ====================
    Route::prefix('data-management/transactions')->name('transactions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminTransactionController::class, 'index'])
            ->name('index')
            ->middleware('permission:manage-transactions');
        
        Route::get('/{transaction}', [\App\Http\Controllers\Admin\AdminTransactionController::class, 'show'])
            ->name('show')
            ->middleware('permission:manage-transactions');
        
        Route::post('/{transaction}/refund', [\App\Http\Controllers\Admin\AdminTransactionController::class, 'refund'])
            ->name('refund')
            ->middleware('permission:manage-transactions');
        
        Route::post('/{transaction}/mark-completed', [\App\Http\Controllers\Admin\AdminTransactionController::class, 'markCompleted'])
            ->name('mark-completed')
            ->middleware('permission:manage-transactions');
        
        Route::post('/{transaction}/mark-failed', [\App\Http\Controllers\Admin\AdminTransactionController::class, 'markFailed'])
            ->name('mark-failed')
            ->middleware('permission:manage-transactions');
        
        Route::get('/export/csv', [\App\Http\Controllers\Admin\AdminTransactionController::class, 'export'])
            ->name('export')
            ->middleware('permission:export-transactions');
    });

    // ==================== WITHDRAWAL MANAGEMENT ====================
    Route::prefix('data-management/withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminWithdrawalController::class, 'index'])
            ->name('index')
            ->middleware('permission:manage-withdrawals');
        
        Route::get('/{withdrawal}', [\App\Http\Controllers\Admin\AdminWithdrawalController::class, 'show'])
            ->name('show')
            ->middleware('permission:manage-withdrawals');
        
        Route::post('/{withdrawal}/approve', [\App\Http\Controllers\Admin\AdminWithdrawalController::class, 'approve'])
            ->name('approve')
            ->middleware('permission:manage-withdrawals');
        
        Route::post('/{withdrawal}/reject', [\App\Http\Controllers\Admin\AdminWithdrawalController::class, 'reject'])
            ->name('reject')
            ->middleware('permission:manage-withdrawals');
        
        Route::post('/{withdrawal}/mark-transferred', [\App\Http\Controllers\Admin\AdminWithdrawalController::class, 'markTransferred'])
            ->name('mark-transferred')
            ->middleware('permission:manage-withdrawals');
        
        Route::post('/{withdrawal}/mark-disputed', [\App\Http\Controllers\Admin\AdminWithdrawalController::class, 'markDisputed'])
            ->name('mark-disputed')
            ->middleware('permission:manage-withdrawals');
        
        Route::post('/bulk-approve', [\App\Http\Controllers\Admin\AdminWithdrawalController::class, 'bulkApprove'])
            ->name('bulk-approve')
            ->middleware('permission:manage-withdrawals');
        
        Route::get('/export/csv', [\App\Http\Controllers\Admin\AdminWithdrawalController::class, 'export'])
            ->name('export')
            ->middleware('permission:export-withdrawals');
    });

    // ==================== FORUM MODERATION ====================
    Route::prefix('data-management/forum')->name('forum.')->group(function () {
        Route::get('/discussions', [\App\Http\Controllers\Admin\AdminForumController::class, 'discussions'])
            ->name('discussions')
            ->middleware('permission:moderate-forum');
        
        Route::get('/comments', [\App\Http\Controllers\Admin\AdminForumController::class, 'comments'])
            ->name('comments')
            ->middleware('permission:moderate-forum');
        
        Route::get('/flagged', [\App\Http\Controllers\Admin\AdminForumController::class, 'flagged'])
            ->name('flagged')
            ->middleware('permission:moderate-forum');
        
        Route::delete('/discussion/{discussion}', [\App\Http\Controllers\Admin\AdminForumController::class, 'deleteDiscussion'])
            ->name('delete-discussion')
            ->middleware('permission:moderate-forum');
        
        Route::post('/discussion/{discussion}/lock', [\App\Http\Controllers\Admin\AdminForumController::class, 'lockDiscussion'])
            ->name('lock-discussion')
            ->middleware('permission:moderate-forum');
        
        Route::post('/discussion/{discussion}/unlock', [\App\Http\Controllers\Admin\AdminForumController::class, 'unlockDiscussion'])
            ->name('unlock-discussion')
            ->middleware('permission:moderate-forum');
        
        Route::post('/comment/{comment}/approve', [\App\Http\Controllers\Admin\AdminForumController::class, 'approveComment'])
            ->name('approve-comment')
            ->middleware('permission:moderate-forum');
        
        Route::post('/comment/{comment}/reject', [\App\Http\Controllers\Admin\AdminForumController::class, 'rejectComment'])
            ->name('reject-comment')
            ->middleware('permission:moderate-forum');
        
        Route::delete('/comment/{comment}', [\App\Http\Controllers\Admin\AdminForumController::class, 'deleteComment'])
            ->name('delete-comment')
            ->middleware('permission:moderate-forum');
        
        Route::post('/flag/{flag}/resolve', [\App\Http\Controllers\Admin\AdminForumController::class, 'resolveFlag'])
            ->name('resolve-flag')
            ->middleware('permission:moderate-forum');
        
        Route::post('/flag/{flag}/dismiss', [\App\Http\Controllers\Admin\AdminForumController::class, 'dismissFlag'])
            ->name('dismiss-flag')
            ->middleware('permission:moderate-forum');
    });

    // ==================== REPORTS ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/revenue', [\App\Http\Controllers\Admin\AdminReportController::class, 'revenue'])
            ->name('revenue')
            ->middleware('permission:view-reports');
        
        Route::get('/users', [\App\Http\Controllers\Admin\AdminReportController::class, 'users'])
            ->name('users')
            ->middleware('permission:view-reports');
        
        Route::get('/note-performance', [\App\Http\Controllers\Admin\AdminReportController::class, 'notePerformance'])
            ->name('note-performance')
            ->middleware('permission:view-reports');
        
        Route::get('/affiliate', [\App\Http\Controllers\Admin\AdminReportController::class, 'affiliate'])
            ->name('affiliate')
            ->middleware('permission:view-reports');
        
        Route::get('/export-pdf', [\App\Http\Controllers\Admin\AdminReportController::class, 'exportPdf'])
            ->name('export-pdf')
            ->middleware('permission:export-reports');
    });

    // ==================== SETTINGS ====================
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])
            ->name('index')
            ->middleware('permission:manage-settings');
        
        Route::post('/general', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateGeneral'])
            ->name('update-general')
            ->middleware('permission:manage-settings');
        
        Route::post('/payment', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updatePayment'])
            ->name('update-payment')
            ->middleware('permission:manage-settings');
        
        Route::post('/affiliate', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateAffiliate'])
            ->name('update-affiliate')
            ->middleware('permission:manage-settings');
        
        Route::post('/share-to-earn', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateShareToEarn'])
            ->name('update-share-to-earn')
            ->middleware('permission:manage-settings');
        
        Route::post('/points', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updatePoints'])
            ->name('update-points')
            ->middleware('permission:manage-settings');
        
        Route::post('/email', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateEmail'])
            ->name('update-email')
            ->middleware('permission:manage-settings');
        
        Route::post('/security', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateSecurity'])
            ->name('update-security')
            ->middleware('permission:manage-settings');
    });
});
