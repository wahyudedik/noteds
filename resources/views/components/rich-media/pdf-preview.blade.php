@props(['pdfUrl', 'filename' => null])

<div class="mb-6 bg-gradient-to-r from-red-50 via-pink-50 to-rose-50 rounded-xl border-2 border-red-200 p-6 shadow-lg">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center shadow-md">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">📄 PDF Preview</h3>
                @if($filename)
                    <p class="text-xs text-gray-600 truncate">{{ $filename }}</p>
                @endif
            </div>
        </div>
        <a href="{{ $pdfUrl }}" download
            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 transition-all duration-200 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download
        </a>
    </div>
    
    <div 
        x-data="{
            currentPage: 1,
            totalPages: 1,
            scale: 1.0,
            loading: true,
            error: false,
            pdfDoc: null,
            init() {
                // Load PDF using PDF.js
                this.loadPDF();
            },
            async loadPDF() {
                if (typeof pdfjsLib === 'undefined') {
                    this.error = true;
                    this.loading = false;
                    return;
                }
                try {
                    // Load PDF using PDF.js
                    const loadingTask = pdfjsLib.getDocument('{{ $pdfUrl }}');
                    const pdf = await loadingTask.promise;
                    this.pdfDoc = pdf;
                    this.totalPages = pdf.numPages;
                    this.loading = false;
                    this.renderPage(1);
                } catch (error) {
                    console.error('Error loading PDF:', error);
                    this.error = true;
                    this.loading = false;
                }
            },
            async renderPage(pageNum) {
                if (this.loading || this.error || !this.pdfDoc || typeof pdfjsLib === 'undefined') return;
                try {
                    const page = await this.pdfDoc.getPage(pageNum);
                    const viewport = page.getViewport({ scale: this.scale });
                    
                    const canvas = this.$refs.pdfCanvas;
                    const context = canvas.getContext('2d');
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;
                    
                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    await page.render(renderContext).promise;
                    this.currentPage = pageNum;
                } catch (error) {
                    console.error('Error rendering page:', error);
                    this.error = true;
                }
            },
            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.renderPage(this.currentPage + 1);
                }
            },
            prevPage() {
                if (this.currentPage > 1) {
                    this.renderPage(this.currentPage - 1);
                }
            },
            zoomIn() {
                this.scale = Math.min(this.scale + 0.25, 3.0);
                this.renderPage(this.currentPage);
            },
            zoomOut() {
                this.scale = Math.max(this.scale - 0.25, 0.5);
                this.renderPage(this.currentPage);
            }
        }"
        class="relative bg-gray-900 rounded-lg overflow-hidden shadow-xl">
        <!-- PDF Controls -->
        <div class="flex items-center justify-between px-4 py-3 bg-gray-800 border-b border-gray-700">
            <div class="flex items-center gap-2">
                <button 
                    @click="prevPage()"
                    :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-700'"
                    class="p-2 text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <span class="text-sm text-gray-300 font-mono px-3">
                    Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                </span>
                <button 
                    @click="nextPage()"
                    :disabled="currentPage === totalPages"
                    :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-700'"
                    class="p-2 text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    @click="zoomOut()"
                    class="p-2 text-white rounded-lg hover:bg-gray-700 transition-colors"
                    title="Zoom Out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                    </svg>
                </button>
                <span class="text-sm text-gray-300 font-mono px-2">
                    <span x-text="Math.round(scale * 100)"></span>%
                </span>
                <button 
                    @click="zoomIn()"
                    class="p-2 text-white rounded-lg hover:bg-gray-700 transition-colors"
                    title="Zoom In">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- PDF Canvas -->
        <div class="relative bg-gray-900 min-h-[500px] flex items-center justify-center overflow-auto">
            <div x-show="loading" class="absolute inset-0 flex items-center justify-center">
                <div class="text-center text-gray-400">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500 mx-auto mb-2"></div>
                    <p>Loading PDF...</p>
                </div>
            </div>
            <div x-show="error" class="text-center text-gray-400 p-8">
                <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mb-4">Unable to load PDF preview. Please download the file to view.</p>
                <a href="{{ $pdfUrl }}" download class="inline-block px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Download PDF
                </a>
            </div>
            <canvas 
                x-ref="pdfCanvas"
                x-show="!loading && !error"
                class="max-w-full h-auto shadow-2xl"
            ></canvas>
        </div>
    </div>
</div>
