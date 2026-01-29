<?php

namespace PictaStudio\Contento\Http\Controllers;

use Illuminate\Routing\Controller;
use PictaStudio\Contento\Models\MailForm;
use PictaStudio\Contento\Http\Resources\MailFormResource;
use PictaStudio\Contento\Http\Requests\SaveMailFormRequest;

class MailFormController extends Controller
{
    public function index()
    {
        $forms = MailForm::paginate();
        return MailFormResource::collection($forms);
    }

    public function show($id)
    {
        $form = MailForm::where('id', $id)->orWhere('slug', $id)->firstOrFail();
        return new MailFormResource($form);
    }

    public function store(SaveMailFormRequest $request)
    {
        $form = MailForm::create($request->validated());
        return new MailFormResource($form);
    }

    public function update(SaveMailFormRequest $request, $id)
    {
        $form = MailForm::findOrFail($id);
        $form->update($request->validated());
        return new MailFormResource($form);
    }

    public function destroy($id)
    {
        $form = MailForm::findOrFail($id);
        $form->delete();
        return response()->noContent();
    }
}
