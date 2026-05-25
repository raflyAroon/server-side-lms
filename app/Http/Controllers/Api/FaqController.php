<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use App\Traits\Cacheable;

class FaqController extends Controller
{
    use Cacheable;

    public function index()
    {
        $faqs = $this->rememberFaqs();
        return response()->json($faqs);
    }

    public function store(Request $request)
    {
        $this->authorize('admin');
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'display_order' => 'nullable|integer',
        ]);
        $faq = Faq::create($request->all());
        $this->forgetFaqs();
        return response()->json($faq, 201);
    }

    public function update(Request $request, Faq $faq)
    {
        $this->authorize('admin');
        $faq->update($request->only(['question', 'answer', 'display_order']));
        $this->forgetFaqs();
        return response()->json($faq);
    }

    public function destroy(Faq $faq)
    {
        $this->authorize('admin');
        $faq->delete();
        $this->forgetFaqs();
        return response()->json(['message' => 'Deleted']);
    }
}