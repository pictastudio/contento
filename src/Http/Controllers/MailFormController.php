<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\{IndexMailFormRequest, StoreMailFormRequest};
use PictaStudio\Contento\Http\Resources\MailFormResource;
use PictaStudio\Contento\Models\MailForm;

use function PictaStudio\Contento\Helpers\Functions\{query, resolve_model};

class MailFormController extends BaseController
{
    public function index(IndexMailFormRequest $request): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', resolve_model('mail_form'));

        $validated = $request->validated();
        $forms = query('mail_form');

        $this->applyArrayFilters($forms, $validated, [
            'id' => 'id',
        ]);
        $this->applyExactFilters($forms, $validated, [
            'name' => 'name',
            'slug' => 'slug',
            'email_to' => 'email_to',
            'newsletter' => 'newsletter',
        ]);
        $this->applyDateRangeFilters($forms, $validated, [
            'created_at' => ['start' => 'created_at_start', 'end' => 'created_at_end'],
            'updated_at' => ['start' => 'updated_at_start', 'end' => 'updated_at_end'],
        ]);
        $this->applySorting($forms, $validated);

        return MailFormResource::collection(
            $forms->paginate($this->resolvePerPage($validated))
                ->appends($request->query())
        );
    }

    public function show(MailForm $mailForm): JsonResource
    {
        $this->authorizeIfConfigured('view', $mailForm);

        return MailFormResource::make($mailForm);
    }

    public function store(StoreMailFormRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', resolve_model('mail_form'));

        $form = query('mail_form')->create($request->validated());

        return MailFormResource::make($form);
    }

    public function update(StoreMailFormRequest $request, MailForm $mailForm): JsonResource
    {
        $this->authorizeIfConfigured('update', $mailForm);

        $mailForm->update($request->validated());

        return MailFormResource::make($mailForm);
    }

    public function destroy(MailForm $mailForm): Response
    {
        $this->authorizeIfConfigured('delete', $mailForm);

        $mailForm->delete();

        return response()->noContent();
    }
}
