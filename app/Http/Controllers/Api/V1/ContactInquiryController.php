<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactInquiry\StoreContactInquiryRequest;
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
        $inquiry = ContactInquiry::create([
            ...$request->validated(),
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
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return ContactInquiryResource::collection($inquiries);
    }

    public function destroy(ContactInquiry $inquiry): JsonResponse
    {
        $inquiry->delete();

        return response()->json([
            'message' => 'Inquiry berhasil dihapus.',
        ]);
    }
}
