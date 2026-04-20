<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PictaStudio\Contento\Actions\Faqs\UpsertMultipleFaqs;
use PictaStudio\Contento\Http\Requests\{IndexFaqRequest, StoreFaqRequest, UpsertMultipleFaqRequest};
use PictaStudio\Contento\Http\Resources\FaqResource;
use PictaStudio\Contento\Models\Faq;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class FaqController extends BaseController
{
    public function index(IndexFaqRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('faq'));

        $validated = $request->validated();
        $faqs = query('faq');
        $this->removeImplicitScopesOverriddenByExplicitFilters(
            $faqs,
            $validated,
            supportsActiveScope: true,
            dateRangeColumns: ['visible_date_from' => 'visible_date_range', 'visible_date_to' => 'visible_date_range']
        );

        $this->applyArrayFilters($faqs, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($faqs, $validated, [
            'faq_category_id' => 'faq_category_id',
            'active' => 'active',
            'sort_order' => 'sort_order',
            'visible_date_from' => 'visible_date_from',
            'visible_date_to' => 'visible_date_to',
        ]);
        $this->applyTextFilters($faqs, $validated, [
            'slug' => 'slug',
        ]);
        $this->applyDateRangeFilters($faqs, $validated, [
            'visible_date_from' => ['start' => 'visible_date_from_start', 'end' => 'visible_date_from_end'],
            'visible_date_to' => ['start' => 'visible_date_to_start', 'end' => 'visible_date_to_end'],
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($faqs, $validated, 'sort_order', 'asc');

        return FaqResource::collection(
            $faqs->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function show(Faq $faq): JsonResource
    {
        $this->authorizeIfConfigured('view', $faq);

        return FaqResource::make($faq);
    }

    public function store(StoreFaqRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('faq'));

        $faq = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $tagIdsProvided = array_key_exists('tag_ids', $validated);
            $tagIds = $validated['tag_ids'] ?? [];
            unset($validated['tag_ids']);

            $faq = query('faq')->create($validated);

            if ($tagIdsProvided) {
                $faq->contentTags()->sync($tagIds ?? []);
            }

            return $faq->refresh();
        });

        return FaqResource::make($faq);
    }

    public function update(StoreFaqRequest $request, Faq $faq): JsonResource
    {
        $this->authorizeIfConfigured('update', $faq);

        $faq = DB::transaction(function () use ($request, $faq) {
            $validated = $request->validated();
            $tagIdsProvided = array_key_exists('tag_ids', $validated);
            $tagIds = $validated['tag_ids'] ?? [];
            unset($validated['tag_ids']);

            $faq->update($validated);

            if ($tagIdsProvided) {
                $faq->contentTags()->sync($tagIds ?? []);
            }

            return $faq->refresh();
        });

        return FaqResource::make($faq);
    }

    public function upsertMultiple(UpsertMultipleFaqRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $faqs = collect($validated['faqs']);
        $faqIds = $faqs
            ->pluck('id')
            ->filter(fn (mixed $faqId): bool => filled($faqId))
            ->map(fn (mixed $faqId): int => (int) $faqId)
            ->unique()
            ->values()
            ->all();

        $existingFaqs = $request->existingFaqs();

        if ($existingFaqs->count() !== count($faqIds)) {
            $missingIds = collect($faqIds)
                ->diff($existingFaqs->keys())
                ->values()
                ->all();

            throw ValidationException::withMessages([
                'faqs' => [
                    'Some faqs are not available for update: ' . implode(', ', $missingIds),
                ],
            ]);
        }

        $needsCreateAuthorization = false;

        foreach ($faqs as $faqPayload) {
            $faqId = $faqPayload['id'] ?? null;

            if (filled($faqId)) {
                $this->authorizeIfConfigured('update', $existingFaqs->get((int) $faqId));

                continue;
            }

            $needsCreateAuthorization = true;
        }

        if ($needsCreateAuthorization) {
            $this->authorizeIfConfigured('create', resolve_model('faq'));
        }

        /** @var Collection<int, Faq> $upsertedFaqs */
        $upsertedFaqs = app(UpsertMultipleFaqs::class)
            ->handle($faqs->all());

        return FaqResource::collection($upsertedFaqs);
    }

    public function destroy(Faq $faq): Response
    {
        $this->authorizeIfConfigured('delete', $faq);

        $faq->delete();

        return response()->noContent();
    }
}
