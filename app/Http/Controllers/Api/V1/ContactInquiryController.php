<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactInquiry\StoreContactInquiryRequest;
use App\Http\Requests\ContactInquiry\UpdateContactInquiryRequest;
use App\Http\Resources\ContactInquiryResource;
use App\Mail\NewContactInquiry;
use App\Models\ContactInquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class ContactInquiryController extends Controller
{
    public function store(StoreContactInquiryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        // Drop camelCase alias if present — DB uses snake_case.
        unset($validated['projectType']);

        $inquiry = ContactInquiry::create([
            ...$validated,
            'status' => ContactInquiry::STATUS_NEW,
        ]);

        Mail::to(config('mail.from.address'))->send(new NewContactInquiry($inquiry));

        return (new ContactInquiryResource($inquiry))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $inquiries = ContactInquiry::query()
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->when($request->string('search')->trim()->toString(), fn ($query, string $search) => $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('company', 'like', "%{$search}%")->orWhere('message', 'like', "%{$search}%")))
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return ContactInquiryResource::collection($inquiries);
    }

    public function update(UpdateContactInquiryRequest $request, ContactInquiry $inquiry): ContactInquiryResource
    {
        $inquiry->update(['status' => $request->validated('status')]);

        return new ContactInquiryResource($inquiry->fresh());
    }

    public function destroy(ContactInquiry $inquiry): JsonResponse
    {
        $inquiry->delete();

        return response()->json([
            'message' => 'Inquiry berhasil dihapus.',
        ]);
    }
}
