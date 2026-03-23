<?php

namespace PictaStudio\Contento\Actions\Faqs;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class UpsertMultipleFaqs
{
    public function handle(array $faqs): Collection
    {
        return DB::transaction(function () use ($faqs): Collection {
            $upsertedFaqs = new Collection;
            $faqModelClass = resolve_model('faq');

            foreach ($faqs as $faqPayload) {
                $faqId = array_key_exists('id', $faqPayload) && filled($faqPayload['id'])
                    ? (int) $faqPayload['id']
                    : null;
                $tagIdsProvided = array_key_exists('tag_ids', $faqPayload);
                $tagIds = Arr::pull($faqPayload, 'tag_ids', []);

                if ($faqId !== null) {
                    $faq = $faqModelClass::query()
                        ->withoutGlobalScopes()
                        ->whereKey($faqId)
                        ->firstOrFail();

                    $faq->fill(collect($faqPayload)->except('id')->all());
                    $faq->save();

                    if ($tagIdsProvided) {
                        $faq->contentTags()->sync($tagIds ?? []);
                    }

                    $upsertedFaqs->push($faq->refresh());

                    continue;
                }

                $faq = query('faq')->create($faqPayload);

                if ($tagIdsProvided) {
                    $faq->contentTags()->sync($tagIds ?? []);
                }

                $upsertedFaqs->push($faq->refresh());
            }

            return $upsertedFaqs;
        });
    }
}
