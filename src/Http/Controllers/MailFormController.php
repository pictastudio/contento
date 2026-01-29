<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Http\Resources\Json\{AnonymousResourceCollection, JsonResource};
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use PictaStudio\Contento\Http\Requests\SaveMailFormRequest;
use PictaStudio\Contento\Http\Resources\MailFormResource;
use PictaStudio\Contento\Models\MailForm;

class MailFormController extends Controller
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
        return new MailFormResource($mailForm);
    }

    public function store(SaveMailFormRequest $request): JsonResource
    {
        $form = MailForm::create($request->validated());

        return new MailFormResource($form);
    }

    public function update(SaveMailFormRequest $request, MailForm $mailForm): JsonResource
    {
        $mailForm->update($request->validated());

        return new MailFormResource($mailForm);
    }

    public function destroy(MailForm $mailForm): Response
    {
        $mailForm->delete();

        return response()->noContent();
    }
}
