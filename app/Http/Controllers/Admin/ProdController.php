<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProdRequest;
use App\Services\ProdService;
use Illuminate\Http\Request;
use App\Http\Requests\FileUploadRequest;  
use App\Http\Requests\ImageUploadRequest;
use Illuminate\Support\Facades\Redis;

class ProdController extends Controller
{
    protected ProdService $prods;

    public function __construct(ProdService $prods)
    {
        $this->prods = $prods;
    }

    public function index(Request $request, ?int $catid = null)
    {
        $perPage = $request->input('per_page', 15);

       
        $postedCatId = $request->input('catid'); 
        if (!empty($postedCatId)) 
        { 
            $catid = (int) $postedCatId; 
        }


        $fields = [
            'id', 'catid', 'name',
            'filer', 'mime', 'sizer', 'extension',
            'img', 'img2'
        ];

        // Controller decides: here we want Cat info but only counts for Orders and Tags
        $prods = $this->prods->paginate(
            $perPage,
            $fields,
            'id',
            'desc',
            $catid,
            true,   // withCat
            false,  // withOrders
            false,   // numOrders
            false,  // withTags
            true    // numTags
        );
        $cats = $this->prods->getCategoriesForDropdown($catid);
        return view('admin.prods.index', compact('prods', 'catid' , 'cats'));
    }

    public function indexJson(Request $request, ?int $catid = null)
    {
        $perPage = $request->input('per_page', 15);
        $fields = [
            'id', 'catid', 'name',
            'filer', 'mime', 'sizer', 'extension',
            'img', 'img2'
        ];

        if ($catid) {
            $prods = $this->prods->paginate(
                $perPage,
                $fields,
                'id',
                'desc',
                $catid,
                true,   // withCat
                false,  // withOrders
                true,   // numOrders
                false,  // withTags
                true    // numTags
            );
        } else {
            $prods = $this->prods->select(
                $fields,
                'id',
                'desc',
                null,
                true,   // withCat
                false,  // withOrders
                false,   // numOrders
                false,  // withTags
                false    // numTags
            );
        }

        return response()->json($prods);
    }

    public function indexRedisJson(Request $request, ?int $catid = null)
    {
        $prods = [];
        $cursor = null;

        do {
            [$cursor, $keys] = Redis::scan($cursor, [
                'match' => '*prod:*',
                'count' => 100,
            ]);

            foreach ($keys as $key) {
                $cleanKey = str_replace(config('database.redis.options.prefix'), '', $key);
                $value = Redis::get($cleanKey);

                if ($value !== null) {
                    $decoded = json_decode($value, true);
                    if ($decoded) {
                        if ($catid) {
                            if (isset($decoded['catid']) && (int)$decoded['catid'] === (int)$catid) {
                                $prods[] = $decoded;
                            }
                        } else {
                            $prods[] = $decoded;
                        }
                    }
                }
            }
        } while ($cursor != 0);

        return response()->json([
            'total' => count($prods),
            'data' => $prods,
            'catid' => $catid,
        ]);
    }

    public function create(?int $catid = null)
    {
        $cats = $this->prods->getCategoriesForDropdown($catid);
        return view('admin.prods.create', compact('catid', 'cats'));
    }

    public function edit(int $id)
    {
        $prod = $this->prods->findById($id);
        $cats = $this->prods->getCategoriesForDropdown($prod->catid);
        return view('admin.prods.edit', compact('prod', 'cats'));
    }

    public function store(ProdRequest $prodRequest, FileUploadRequest $fileRequest, ImageUploadRequest $imageRequest)
    {
        $data = $prodRequest->validated();

        $fileFolder   = 'uploads/prods/file';
        $imageFolder  = 'uploads/prods/img';
        $thumbFolder  = 'uploads/prods/img/thumb';
        $baseFileName = $data['name'] ?? 'prod';

        $this->prods->create( $data, $prodRequest, $fileRequest, $imageRequest, $fileFolder, $imageFolder, $thumbFolder, $baseFileName );
        
        return redirect()->route('admin.prods.index', ['catid' => $data['catid']]) 
        ->with('success', 'Product created successfully.');
        
    }

    public function update(int $id, ProdRequest $prodRequest, FileUploadRequest $fileRequest, ImageUploadRequest $imageRequest)
    {
        $data = $prodRequest->validated();

        $fileFolder   = 'uploads/prods/file';
        $imageFolder  = 'uploads/prods/img';
        $thumbFolder  = 'uploads/prods/img/thumb';
        $baseFileName = $data['name'] ?? 'prod';

        $this->prods->update(
            $id, $data, $prodRequest,$fileRequest, $imageRequest, $fileFolder, $imageFolder, $thumbFolder, $baseFileName
        );

        return redirect()->route('admin.prods.index', ['catid' => $data['catid']]) 
        ->with('success', 'Product updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->prods->delete($id);

        return redirect()->route('admin.prods.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function destroyMany(Request $request)
    {
        $ids = $request->input('ids', []);
        $this->prods->deleteMany($ids);

        return redirect()->route('admin.prods.index')
            ->with('success', 'Selected products deleted successfully.');
    }
}
