<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use PictaStudio\Contento\Http\Requests\SaveMailFormRequest;
use PictaStudio\Contento\Http\Resources\MailFormResource;
use PictaStudio\Contento\Models\MailForm;

class MailFormController extends BaseController
{
    public function __construct()
    {
        $this->authorizeResource(MailForm::class, 'mail_form');
    }

    public function index(): AnonymousResourceCollection
    {
        $forms = MailForm::paginate();

        return MailFormResource::collection($forms);
    }

    public function show(MailForm $mailForm): JsonResource
    {
        return MailFormResource::make($mailForm);
    }

    public function store(SaveMailFormRequest $request): JsonResource
    {
        $form = MailForm::create($request->validated());

        return MailFormResource::make($form);
    }

    public function update(SaveMailFormRequest $request, MailForm $mailForm): JsonResource
    {
        $mailForm->update($request->validated());

        return MailFormResource::make($mailForm);
    }

    public function destroy(MailForm $mailForm): Response
    {
        $mailForm->delete();

        return response()->noContent();
    }
}
