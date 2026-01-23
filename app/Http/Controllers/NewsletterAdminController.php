<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NewsletterAdminController extends Controller
{
    public function subscribersPage()
    {
        return Inertia::render('Newsletter/AdminSubscribers');
    }

    public function listSubscribers(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $query = DB::table('newsletter_subscribers')->orderByDesc('id')->limit(200);
        if ($q) $query->where('email', 'like', '%'.$q.'%');
        return response()->json(['subscribers' => $query->get()]);
    }

    public function saveTemplate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'category' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'html' => ['required', 'string'],
        ]);
        $id = DB::table('newsletter_templates')->insertGetId([
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'description' => $data['description'] ?? null,
            'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
            'html' => $data['html'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['id' => $id], 201);
    }

    public function categories(Request $request): JsonResponse
    {
        $rows = DB::table('newsletter_template_categories')->orderBy('name')->get();
        return response()->json(['categories' => $rows]);
    }

    public function saveCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);
        $id = DB::table('newsletter_template_categories')->insertGetId([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['id' => $id], 201);
    }

    public function clientsIndex(Request $request): JsonResponse
    {
        $rows = DB::table('newsletter_clients')->orderBy('name')->get();
        return response()->json(['clients' => $rows]);
    }

    public function clientsSave(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer'],
            'name' => ['required', 'string'],
            'branding' => ['nullable', 'array'],
            'variables' => ['nullable', 'array'],
        ]);
        if (!empty($data['id'])) {
            DB::table('newsletter_clients')->where('id', $data['id'])->update([
                'name' => $data['name'],
                'branding' => isset($data['branding']) ? json_encode($data['branding']) : null,
                'variables' => isset($data['variables']) ? json_encode($data['variables']) : null,
                'updated_at' => now(),
            ]);
            return response()->json(['message' => 'Updated']);
        }
        $id = DB::table('newsletter_clients')->insertGetId([
            'name' => $data['name'],
            'branding' => isset($data['branding']) ? json_encode($data['branding']) : null,
            'variables' => isset($data['variables']) ? json_encode($data['variables']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['id' => $id], 201);
    }

    public function createCampaign(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'template_id' => ['required', 'integer'],
            'scheduled_at' => ['nullable', 'date'],
        ]);
        $id = DB::table('newsletter_campaigns')->insertGetId([
            'name' => $data['name'],
            'template_id' => $data['template_id'],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => $data['scheduled_at'] ? 'scheduled' : 'sending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['id' => $id], 201);
    }

    public function sendCampaign(Request $request, $campaignId): JsonResponse
    {
        \App\Jobs\SendNewsletterJob::dispatch((int) $campaignId);
        return response()->json(['message' => 'Queued']);
    }

    public function suppressionIndex(): \Illuminate\Http\JsonResponse
    {
        $rows = DB::table('newsletter_suppression_list')->orderByDesc('id')->limit(500)->get();
        return response()->json(['rows' => $rows]);
    }

    public function suppressionStore(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        DB::table('newsletter_suppression_list')->updateOrInsert(['email' => $data['email']], ['reason' => 'manual', 'created_at' => now(), 'updated_at' => now()]);
        return response()->json(['message' => 'Added']);
    }

    public function suppressionDelete($id): \Illuminate\Http\JsonResponse
    {
        DB::table('newsletter_suppression_list')->where('id', (int) $id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
