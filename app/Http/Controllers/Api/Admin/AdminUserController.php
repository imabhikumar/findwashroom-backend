<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUserStoreRequest;
use App\Http\Requests\Admin\AdminUserSuspendRequest;
use App\Http\Requests\Admin\AdminUserUpdateRequest;
use App\Http\Resources\Admin\AdminUserCollection;
use App\Http\Resources\Admin\AdminUserResource;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminUserService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->service->paginate($request);

        return response()->json([
            'success' => true,
            'message' => 'Users fetched successfully.',
            'data' => AdminUserResource::collection($paginator->getCollection()),
            'meta' => [
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return $this->successResponse('User fetched successfully.', new AdminUserResource(
            $this->service->findOrFail($id)
        ));
    }

    public function store(AdminUserStoreRequest $request): JsonResponse
    {
        return $this->successResponse(
            'User created successfully.',
            new AdminUserResource($this->service->create($request->validated(), $request)),
            201
        );
    }

    public function update(AdminUserUpdateRequest $request, int $id): JsonResponse
    {
        return $this->successResponse(
            'User updated successfully.',
            new AdminUserResource($this->service->update($id, $request->validated(), $request))
        );
    }

    public function suspend(AdminUserSuspendRequest $request, int $id): JsonResponse
    {
        return $this->successResponse(
            'User suspended successfully.',
            new AdminUserResource($this->service->changeStatus($id, 'suspended', $request->validated('reason'), $request))
        );
    }

    public function ban(AdminUserSuspendRequest $request, int $id): JsonResponse
    {
        return $this->successResponse(
            'User banned successfully.',
            new AdminUserResource($this->service->changeStatus($id, 'banned', $request->validated('reason'), $request))
        );
    }

    public function reactivate(Request $request, int $id): JsonResponse
    {
        return $this->successResponse(
            'User reactivated successfully.',
            new AdminUserResource($this->service->changeStatus($id, 'active', null, $request))
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->delete($id, $request);

        return $this->successResponse('User deleted successfully.');
    }
}