<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatRequest;
use App\Http\Requests\FileUploadRequest;
use App\Services\CatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;



class CatController extends Controller
{
    protected CatService $cats;

    public function __construct(CatService $cats)
    {
        $this->cats = $cats;
    }

    /**
     * Show paginated list (HTML view)
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $cats = $this->cats->paginate($perPage);

        return view('admin.cats.index', compact('cats'));
    }

    /**
     * Show paginated list (AJAX/JSON)
     */
    public function indexJson(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $cats = $this->cats->paginate($perPage);

        return response()->json($cats);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.cats.create');
    }


    public function edit(int $id)
    {
        $cat = $this->cats->findById($id);
        return view('admin.cats.edit', compact('cat'));
    }

    public function store(CatRequest $catRequest, FileUploadRequest $fileRequest)
    {
        $data = $catRequest->validated();
        \Log::info('Type of $data in CatService::create', [gettype($data)]);
        // You don’t need to pass custom mime types anymore,
        // because FileUploadRequest already enforces them.
        $this->cats->create($data, $fileRequest);

        return redirect()->route('admin.cats.index')
            ->with('success', 'Cat created successfully.');
    }

    /**
     * Update cat
     */
    public function update(int $id, CatRequest $catRequest, FileUploadRequest $fileRequest)
    {
        $data = $catRequest->validated();
        \Log::info('LINE 78 Type of $data in CatService::create', [gettype($data)]);
        $customMimeTypes = ['text/html', 'text/htm'];

        $this->cats->update($id, $data, $fileRequest, $customMimeTypes);

        return redirect()->route('admin.cats.index')
            ->with('success', 'Cat updated successfully.');
    }


    /**
     * Delete single cat
     */
    public function destroy(int $id)
    {
        $this->cats->delete($id);

        return redirect()->route('admin.cats.index')
            ->with('success', 'Cat deleted successfully.');
    }

    /**
     * Delete many cats
     */
    public function destroyMany(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->cats->deleteMany($ids);

        return redirect()->route('admin.cats.index')
            ->with('success', 'Selected cats deleted successfully.');
    }
}
