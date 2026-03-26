<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Complaint\StoreComplaintRequest;
use App\Http\Controllers\Controller;
use App\Services\ComplaintService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ComplaintController extends Controller
{
    public function __construct(private readonly ComplaintService $complaintService)
    {
    }

    public function store(StoreComplaintRequest $request)
    {
        try {
            $payload = $request->validated();

            if ($request->hasFile('evidence')) {
                $payload['evidence_image_path'] = $request->file('evidence')->store('complaints', 'public');
            }

            $complaint = $this->complaintService->create((int) auth()->id(), $payload);
            return $this->successResponse('Complaint submitted successfully.', $complaint);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        } catch (BadRequestHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }
}
