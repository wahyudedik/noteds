<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\NoteDownload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    /**
     * Export note to PDF.
     */
    public function exportPdf(Note $note): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        // Check if user has purchased this note (for paid notes)
        if ($note->price > 0 && !auth()->user()->hasPurchasedNote($note->id)) {
            return redirect()->route('marketplace.show', $note)
                ->with('error', 'Anda harus membeli note ini terlebih dahulu untuk mengexport.');
        }

        // Generate HTML with proper styling for PDF
        $html = view('exports.note-pdf', compact('note'))->render();
        
        // Track download
        $this->trackDownload($note, 'export_pdf', 'pdf');

        // Return HTML that can be printed to PDF by browser
        // Note: For actual PDF generation, install barryvdh/laravel-dompdf or wkhtmltopdf
        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="' . Str::slug($note->title) . '.html"');
    }

    /**
     * Export note to DOCX.
     */
    public function exportDocx(Note $note): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        // Check if user has purchased this note (for paid notes)
        if ($note->price > 0 && !auth()->user()->hasPurchasedNote($note->id)) {
            return redirect()->route('marketplace.show', $note)
                ->with('error', 'Anda harus membeli note ini terlebih dahulu untuk mengexport.');
        }

        // Generate formatted text content
        $content = "# {$note->title}\n\n";
        if ($note->summary) {
            $content .= "Summary: {$note->summary}\n\n";
        }
        $content .= "---\n\n";
        $content .= strip_tags($note->content);
        
        // Track download
        $this->trackDownload($note, 'export_docx', 'docx');

        // Return as plain text (can be opened in Word and saved as DOCX)
        // Note: For actual DOCX generation, install phpoffice/phpword
        return response($content)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . Str::slug($note->title) . '.txt"');
    }

    /**
     * Export note to Markdown.
     */
    public function exportMarkdown(Note $note): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        // Check if user has purchased this note (for paid notes)
        if ($note->price > 0 && !auth()->user()->hasPurchasedNote($note->id)) {
            return redirect()->route('marketplace.show', $note)
                ->with('error', 'Anda harus membeli note ini terlebih dahulu untuk mengexport.');
        }

        $markdown = "# {$note->title}\n\n";
        $markdown .= strip_tags($note->content);
        
        // Track download
        $this->trackDownload($note, 'export_markdown', 'markdown');

        return response($markdown)
            ->header('Content-Type', 'text/markdown')
            ->header('Content-Disposition', 'attachment; filename="' . Str::slug($note->title) . '.md"');
    }

    /**
     * Track download for analytics.
     */
    private function trackDownload(Note $note, string $downloadType, string $format): void
    {
        NoteDownload::create([
            'user_id' => auth()->id(),
            'note_id' => $note->id,
            'download_type' => $downloadType,
            'format' => $format,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
