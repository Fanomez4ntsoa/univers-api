<?php

namespace App\Modules\Ecosystem\Services;

use App\Models\JobApplication;
use App\Models\JobOffer;
use Illuminate\Database\Eloquent\Collection;

class JobService
{
    /**
     * List active, non-expired jobs with optional filters.
     */
    public function listActive(?string $category = null, ?string $city = null, ?string $contractType = null): Collection
    {
        $query = JobOffer::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('user:id,username,display_name,avatar_url,company_name');

        if ($category) {
            $query->where('category', $category);
        }
        if ($city) {
            $query->where('city', 'like', "%{$city}%");
        }
        if ($contractType) {
            $query->where('contract_type', $contractType);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function create(int $userId, array $data): JobOffer
    {
        $data['user_id'] = $userId;
        $data['is_active'] = true;
        $data['expires_at'] = now()->addDays(60);

        return JobOffer::create($data)->load('user:id,username,display_name,avatar_url,company_name');
    }

    public function find(int $id): JobOffer
    {
        return JobOffer::with('user:id,username,display_name,avatar_url,company_name')
            ->findOrFail($id);
    }

    public function update(int $userId, int $id, array $data): JobOffer
    {
        $job = JobOffer::findOrFail($id);

        if ($job->user_id !== $userId) {
            abort(403, 'Vous ne pouvez modifier que vos propres offres.');
        }

        $job->update($data);

        return $job->fresh()->load('user:id,username,display_name,avatar_url,company_name');
    }

    public function delete(int $userId, int $id): void
    {
        $job = JobOffer::findOrFail($id);

        if ($job->user_id !== $userId) {
            abort(403, 'Vous ne pouvez supprimer que vos propres offres.');
        }

        $job->delete();
    }

    /**
     * Apply to a job. Blocked if already applied.
     */
    public function apply(int $userId, int $jobId, ?string $message): JobApplication
    {
        $job = JobOffer::findOrFail($jobId);

        $existing = JobApplication::where('job_id', $jobId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            abort(422, 'Vous avez déjà postulé à cette offre.');
        }

        $application = JobApplication::create([
            'job_id'  => $jobId,
            'user_id' => $userId,
            'message' => $message,
            'status'  => 'pending',
        ]);

        $job->increment('applications_count');

        return $application->load('user:id,username,display_name,avatar_url');
    }

    /**
     * List applications for a job. Only the job owner can see them.
     */
    public function listApplications(int $userId, int $jobId): Collection
    {
        $job = JobOffer::findOrFail($jobId);

        if ($job->user_id !== $userId) {
            abort(403, 'Seul le créateur de l\'offre peut voir les candidatures.');
        }

        return JobApplication::where('job_id', $jobId)
            ->with('user:id,username,display_name,avatar_url,email')
            ->orderByDesc('created_at')
            ->get();
    }
}
