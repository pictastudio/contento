<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\StoreMailFormRequest;
use PictaStudio\Contento\Http\Resources\MailFormResource;
use PictaStudio\Contento\Models\MailForm;

class MailFormController extends BaseController
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorizeIfConfigured('viewAny', MailForm::class);

        $forms = MailForm::paginate();

        return MailFormResource::collection($forms);
    }

    public function show(MailForm $mailForm): JsonResource
    {
        $this->authorizeIfConfigured('view', $mailForm);

        return MailFormResource::make($mailForm);
    }

    public function store(StoreMailFormRequest $request): JsonResource
    {
        $this->authorizeIfConfigured('create', MailForm::class);

        $form = MailForm::create($request->validated());

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
