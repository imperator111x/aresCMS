<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FormController extends Controller
{
    public function index(): View
    {
        $forms = Form::query()->withCount('submissions')->orderByDesc('updated_at')->paginate(15);

        return view('admin.forms.index', compact('forms'));
    }

    public function create(): View
    {
        $form = new Form([
            'is_active' => true,
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true],
            ],
            'success_message' => __('Thank you! Your message has been sent.'),
        ]);

        return view('admin.forms.create', compact('form'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['fields'] = $this->normalizeFields($request->input('fields'));
        Form::query()->create($data);

        return redirect()->route('admin.forms.index')
            ->with('success', __('Form created successfully.'));
    }

    public function edit(Form $form): View
    {
        return view('admin.forms.edit', compact('form'));
    }

    public function submissions(Form $form): View
    {
        $submissions = FormSubmission::query()
            ->where('form_id', $form->id)
            ->latest('id')
            ->paginate(20);

        return view('admin.forms.submissions', compact('form', 'submissions'));
    }

    public function clearSubmissions(Form $form): RedirectResponse
    {
        FormSubmission::query()
            ->where('form_id', $form->id)
            ->delete();

        return redirect()->route('admin.forms.submissions', $form)
            ->with('success', __('Submissions cleared successfully.'));
    }

    public function update(Request $request, Form $form): RedirectResponse
    {
        $data = $this->validatedData($request, $form);
        $data['fields'] = $this->normalizeFields($request->input('fields'));
        $form->update($data);

        return redirect()->route('admin.forms.index')
            ->with('success', __('Form updated successfully.'));
    }

    public function destroy(Form $form): RedirectResponse
    {
        $form->delete();

        return redirect()->route('admin.forms.index')
            ->with('success', __('Form deleted successfully.'));
    }

    private function validatedData(Request $request, ?Form $form = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => [
                'required',
                'string',
                'max:190',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('forms', 'slug')->ignore($form?->id),
            ],
            'is_active' => ['nullable', 'boolean'],
            'recipient_email' => ['nullable', 'email', 'max:190'],
            'success_message' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @param mixed $fields
     * @return array<int, array<string, mixed>>
     */
    private function normalizeFields(mixed $fields): array
    {
        if (! is_array($fields)) {
            return [];
        }

        $allowedTypes = ['text', 'email', 'textarea'];
        $result = [];
        foreach ($fields as $field) {
            if (! is_array($field)) {
                continue;
            }

            $name = strtolower(trim((string) ($field['name'] ?? '')));
            $name = preg_replace('/[^a-z0-9_]/', '', $name) ?? '';
            $label = trim((string) ($field['label'] ?? ''));
            $type = trim((string) ($field['type'] ?? 'text'));
            if ($name === '' || $label === '' || ! in_array($type, $allowedTypes, true)) {
                continue;
            }

            $result[] = [
                'name' => $name,
                'label' => $label,
                'type' => $type,
                'required' => ! empty($field['required']),
            ];
        }

        return $result;
    }
}

