<?php

namespace App\Modules\Public\Services;

use App\Models\FaqItem;
use App\Models\SiteStat;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Collection;

class PublicContentService
{
    public function getTestimonials(): Collection
    {
        return Testimonial::where('is_active', true)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getFaq(): Collection
    {
        return FaqItem::where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    public function getStats(): Collection
    {
        return SiteStat::where('is_active', true)
            ->get();
    }
}
