<?php

namespace App\Modules\Public\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Public\Services\PublicContentService;
use Illuminate\Http\JsonResponse;

class PublicContentController extends Controller
{
    public function __construct(private PublicContentService $contentService) {}

    public function testimonials(): JsonResponse
    {
        return response()->json($this->contentService->getTestimonials());
    }

    public function faq(): JsonResponse
    {
        return response()->json($this->contentService->getFaq());
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->contentService->getStats());
    }
}
